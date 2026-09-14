<?php

require_once 'config.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    http_response_code(400);
    die('ID de factura no válido.');
}

try {

    $pdo->beginTransaction();

    /*
     * 1. Bloquear la factura
     */
    $stmt = $pdo->prepare("
        SELECT *
        FROM facturas
        WHERE id = ?
        FOR UPDATE
    ");

    $stmt->execute([$id]);

    $factura = $stmt->fetch();

    if (!$factura) {
        throw new Exception('La factura no existe.');
    }

    /*
     * 2. Comprobar que todavía es borrador
     */
    if ($factura['estado'] !== 'borrador') {
        throw new Exception(
            'Esta factura ya ha sido emitida o no se encuentra en estado borrador.'
        );
    }

    /*
     * 3. Obtener las líneas
     */
    $stmt = $pdo->prepare("
        SELECT *
        FROM factura_lineas
        WHERE factura_id = ?
        ORDER BY orden ASC, id ASC
    ");

    $stmt->execute([$id]);

    $lineas = $stmt->fetchAll();

    if (!$lineas) {
        throw new Exception(
            'No se puede emitir una factura sin líneas.'
        );
    }

    /*
     * 4. Obtener cliente
     */
    $stmt = $pdo->prepare("
        SELECT *
        FROM clientes
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([
        $factura['cliente_id']
    ]);

    $cliente = $stmt->fetch();

    if (!$cliente) {
        throw new Exception(
            'No se ha encontrado el cliente de la factura.'
        );
    }

    if (
        trim($cliente['nombre_razon_social']) === '' ||
        trim($cliente['nif']) === ''
    ) {
        throw new Exception(
            'El cliente debe tener nombre/razón social y NIF.'
        );
    }

    /*
     * 5. Obtener empresa
     */
    $stmt = $pdo->query("
        SELECT *
        FROM empresa_facturacion
        ORDER BY id ASC
        LIMIT 1
        FOR UPDATE
    ");

    $empresa = $stmt->fetch();

    if (!$empresa) {
        throw new Exception(
            'No existe la configuración de la empresa emisora.'
        );
    }

    if (
        trim($empresa['razon_social']) === '' ||
        trim($empresa['nif']) === ''
    ) {
        throw new Exception(
            'La empresa emisora debe tener razón social y NIF.'
        );
    }

    /*
     * 6. Determinar serie
     */
    $serie = trim($factura['serie']);

    if ($serie === '') {
        $serie = trim($empresa['serie_factura']);

        if ($serie === '') {
            $serie = 'A';
        }
    }

    /*
     * 7. Obtener siguiente número
     */
    $ultimoNumero = (int) $empresa['ultima_factura'];

    $nuevoNumero = $ultimoNumero + 1;

    /*
     * 8. Comprobar que no exista
     */
    $stmt = $pdo->prepare("
        SELECT id
        FROM facturas
        WHERE serie = ?
          AND numero = ?
        LIMIT 1
    ");

    $stmt->execute([
        $serie,
        $nuevoNumero
    ]);

    if ($stmt->fetch()) {
        throw new Exception(
            'El número de factura ya existe. No se ha realizado ningún cambio.'
        );
    }

    /*
     * 9. Actualizar contador
     */
    $stmt = $pdo->prepare("
        UPDATE empresa_facturacion
        SET
            serie_factura = ?,
            ultima_factura = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $serie,
        $nuevoNumero,
        $empresa['id']
    ]);

    /*
     * 10. Emitir factura
     */
    $stmt = $pdo->prepare("
        UPDATE facturas
        SET
            serie = ?,
            numero = ?,
            estado = 'emitida'
        WHERE id = ?
          AND estado = 'borrador'
    ");

    $stmt->execute([
        $serie,
        $nuevoNumero,
        $id
    ]);

    if ($stmt->rowCount() !== 1) {
        throw new Exception(
            'No se ha podido emitir la factura.'
        );
    }

    /*
     * 11. Confirmar transacción
     */
    $pdo->commit();

    /*
     * 12. Redirigir a la factura
     */
    header(
        'Location: ver_factura.php?id=' .
        urlencode($id) .
        '&emitida=1'
    );

    exit;

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log(
        'Error emitir_factura.php: ' .
        $e->getMessage()
    );

    http_response_code(500);

    $error = $e->getMessage();

    /*
     * Cargar la página HTML de error
     */
    require __DIR__ . '/emitir-factura.html';

    exit;
}