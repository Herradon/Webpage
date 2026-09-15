
<?php

session_start();

require_once 'config.php';


/* ==========================================
   COMPROBAR SESIÓN
========================================== */

if (!isset($_SESSION['usuario_id'])) {

    header('Location: login.php');
    exit;
}

$usuarioId = (int) $_SESSION['usuario_id'];

if ($usuarioId <= 0) {

    http_response_code(403);

    $error = 'No tienes permiso para emitir esta factura.';

    goto mostrar_error;
}


/* ==========================================
   OBTENER ID
========================================== */

$id = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$id) {

    http_response_code(400);

    $error = 'ID de factura no válido.';

    goto mostrar_error;
}


try {

    $pdo->beginTransaction();


    /* ==========================================
       1. OBTENER Y BLOQUEAR FACTURA
    ========================================== */

    $stmt = $pdo->prepare("
        SELECT f.*
        FROM facturas f
        INNER JOIN clientes c
            ON c.id = f.cliente_id
        WHERE f.id = ?
          AND c.usuario_id = ?
          AND c.activo = 1
        FOR UPDATE
    ");

    $stmt->execute([
        $id,
        $usuarioId
    ]);

    $factura = $stmt->fetch(PDO::FETCH_ASSOC);


    if (!$factura) {

        throw new Exception(
            'No tienes permiso para emitir esta factura.'
        );
    }


    /* ==========================================
       2. COMPROBAR ESTADO
    ========================================== */

    if ($factura['estado'] !== 'borrador') {

        throw new Exception(
            'Esta factura ya ha sido emitida o no se encuentra en estado borrador.'
        );
    }


    /* ==========================================
       3. OBTENER DATOS PRIVADOS CIFRADOS
    ========================================== */

    $stmt = $pdo->prepare("
        SELECT datos_cifrados
        FROM facturas_privadas
        WHERE factura_id = ?
          AND usuario_id = ?
        LIMIT 1
    ");

    $stmt->execute([
        $id,
        $usuarioId
    ]);

    $privada = $stmt->fetch(PDO::FETCH_ASSOC);


    if (
        !$privada ||
        empty($privada['datos_cifrados'])
    ) {

        throw new Exception(
            'No se han encontrado los datos privados de esta factura.'
        );
    }


    /* ==========================================
       4. CLAVE DE CIFRADO
    ========================================== */

    $claveCifrado = $VIZIUNEAI_FACTURAS_KEY ?? '';

    if (empty($claveCifrado)) {

        throw new Exception(
            'No está configurada la clave de cifrado.'
        );
    }

    if (strlen($claveCifrado) < 32) {

        throw new Exception(
            'La clave de cifrado no es suficientemente segura.'
        );
    }


    $clave = hash(
        'sha256',
        $claveCifrado,
        true
    );


    /* ==========================================
       5. DECODIFICAR CONTENIDO
    ========================================== */

    $contenido = base64_decode(
        $privada['datos_cifrados'],
        true
    );

    if ($contenido === false) {

        throw new Exception(
            'No se pudieron leer los datos privados de la factura.'
        );
    }


    /*
     * Los primeros 16 bytes corresponden al IV
     * utilizado por AES-256-CBC.
     */

    $ivLength = 16;


    if (strlen($contenido) <= $ivLength) {

        throw new Exception(
            'Los datos privados de la factura no son válidos.'
        );
    }


    $iv = substr(
        $contenido,
        0,
        $ivLength
    );


    $datosCifrados = substr(
        $contenido,
        $ivLength
    );


    /* ==========================================
       6. DESCIFRAR
    ========================================== */

    $jsonFactura = openssl_decrypt(
        $datosCifrados,
        'AES-256-CBC',
        $clave,
        OPENSSL_RAW_DATA,
        $iv
    );


    if ($jsonFactura === false) {

        throw new Exception(
            'No se pudieron descifrar los datos privados de la factura.'
        );
    }


    /* ==========================================
       7. CONVERTIR JSON
    ========================================== */

    $datosFactura = json_decode(
        $jsonFactura,
        true
    );


    if (!is_array($datosFactura)) {

        throw new Exception(
            'Los datos privados de la factura no son válidos.'
        );
    }


    /* ==========================================
       8. COMPROBAR LÍNEAS
    ========================================== */

    $lineas = $datosFactura['lineas'] ?? [];


    if (
        !is_array($lineas) ||
        empty($lineas)
    ) {

        throw new Exception(
            'No se puede emitir una factura sin líneas.'
        );
    }


    /* ==========================================
       9. COMPROBAR CLIENTE PRIVADO
    ========================================== */

    $clientePrivado =
        $datosFactura['cliente']
        ?? null;


    if (
        !is_array($clientePrivado) ||
        trim(
            (string) (
                $clientePrivado['nombre_razon_social']
                ?? ''
            )
        ) === ''
    ) {

        throw new Exception(
            'La factura debe tener un cliente válido.'
        );
    }


    /* ==========================================
       10. OBTENER CLIENTE TÉCNICO
    ========================================== */

    $stmt = $pdo->prepare("
        SELECT *
        FROM clientes
        WHERE id = ?
          AND usuario_id = ?
          AND activo = 1
        LIMIT 1
    ");

    $stmt->execute([
        $factura['cliente_id'],
        $usuarioId
    ]);

    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);


    if (!$cliente) {

        throw new Exception(
            'No se ha encontrado el cliente de la factura.'
        );
    }


    /*
     * El nombre/NIF técnico se mantienen como
     * metadatos mínimos de administración.
     *
     * Los conceptos, importes y demás contenido
     * privado permanecen cifrados.
     */

    if (
        trim(
            (string) $cliente['nombre_razon_social']
        ) === ''
    ) {

        throw new Exception(
            'El cliente debe tener nombre o razón social.'
        );
    }


    /* ==========================================
       11. OBTENER EMPRESA EMISORA
    ========================================== */

    $stmt = $pdo->query("
        SELECT *
        FROM empresa_facturacion
        ORDER BY id ASC
        LIMIT 1
        FOR UPDATE
    ");

    $empresa = $stmt->fetch(PDO::FETCH_ASSOC);


    if (!$empresa) {

        throw new Exception(
            'No existe la configuración de la empresa emisora.'
        );
    }


    if (
        trim(
            (string) $empresa['razon_social']
        ) === '' ||
        trim(
            (string) $empresa['nif']
        ) === ''
    ) {

        throw new Exception(
            'La empresa emisora debe tener razón social y NIF.'
        );
    }


    /* ==========================================
       12. DETERMINAR SERIE
    ========================================== */

    $serie = trim(
        (string) $factura['serie']
    );


    if ($serie === '') {

        $serie = trim(
            (string) (
                $empresa['serie_factura']
                ?? ''
            )
        );

        if ($serie === '') {
            $serie = 'A';
        }
    }


    /* ==========================================
       13. OBTENER SIGUIENTE NÚMERO
    ========================================== */

    $ultimoNumero = (int) (
        $empresa['ultima_factura']
        ?? 0
    );

    $nuevoNumero = $ultimoNumero + 1;


    /* ==========================================
       14. COMPROBAR QUE NO EXISTA
    ========================================== */

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


    /* ==========================================
       15. ACTUALIZAR CONTADOR
    ========================================== */

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


    /* ==========================================
       16. ACTUALIZAR DATOS PRIVADOS
    ========================================== */

    /*
     * Guardamos dentro del contenido cifrado
     * la serie y número definitivos.
     *
     * No añadimos los importes ni los conceptos
     * a ninguna columna visible de facturas.
     */

    if (!isset($datosFactura['factura'])) {
        $datosFactura['factura'] = [];
    }


    $datosFactura['factura']['serie'] =
        $serie;

    $datosFactura['factura']['numero'] =
        $nuevoNumero;


    /*
     * Volvemos a convertir a JSON.
     */

    $jsonFacturaActualizado = json_encode(
        $datosFactura,
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES |
        JSON_THROW_ON_ERROR
    );


    /*
     * Generamos un IV nuevo.
     */

    $ivNuevo = random_bytes(16);


    $datosCifradosNuevos = openssl_encrypt(
        $jsonFacturaActualizado,
        'AES-256-CBC',
        $clave,
        OPENSSL_RAW_DATA,
        $ivNuevo
    );


    if ($datosCifradosNuevos === false) {

        throw new Exception(
            'No se pudieron actualizar los datos privados de la factura.'
        );
    }


    /*
     * Guardamos IV + contenido cifrado.
     */

    $contenidoPrivadoNuevo = base64_encode(
        $ivNuevo . $datosCifradosNuevos
    );


    /* ==========================================
       17. GUARDAR DATOS PRIVADOS
    ========================================== */

    $stmt = $pdo->prepare("
        UPDATE facturas_privadas
        SET
            datos_cifrados = ?
        WHERE factura_id = ?
          AND usuario_id = ?
    ");

    $stmt->execute([
        $contenidoPrivadoNuevo,
        $id,
        $usuarioId
    ]);


    if ($stmt->rowCount() !== 1) {

        throw new Exception(
            'No se han podido actualizar los datos privados de la factura.'
        );
    }


    /* ==========================================
       18. EMITIR FACTURA
    ========================================== */

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


    /* ==========================================
       19. CONFIRMAR
    ========================================== */

    $pdo->commit();


    /* ==========================================
       20. VOLVER A VER LA FACTURA
    ========================================== */

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

    goto mostrar_error;
}


/*
|--------------------------------------------------------------------------
| HTML DE ERROR
|--------------------------------------------------------------------------
*/

mostrar_error:


function h($texto)
{
    return htmlspecialchars(
        (string) $texto,
        ENT_QUOTES,
        'UTF-8'
    );
}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Error al emitir factura | ViziuneAI</title>

    <link
        rel="stylesheet"
        href="css/emitir-factura.css"
    >

</head>

<body>

    <main class="emitir-contenedor">

        <section class="emitir-card error-card">

            <div class="emitir-icono error-icono">
                !
            </div>

            <h1>
                No se ha podido emitir la factura
            </h1>

            <p class="emitir-texto">
                <?php echo h($error); ?>
            </p>

            <div class="emitir-acciones">

                <a
                    href="javascript:history.back()"
                    class="boton boton-secundario"
                >
                    ← Volver
                </a>

                <a
                    href="facturas.php"
                    class="boton boton-principal"
                >
                    Ver facturas
                </a>

            </div>

        </section>

    </main>

    <script src="js/emitir-factura.js"></script>

</body>

</html>