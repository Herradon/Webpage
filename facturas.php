
<?php

session_start();

require_once 'config.php';


/* ==========================================
   COMPROBAR SESIÓN PRINCIPAL
========================================== */

if (!isset($_SESSION['usuario_id'])) {

    header('Location: login.php');
    exit;

}

$usuarioId = (int) $_SESSION['usuario_id'];


/* ==========================================
   COMPROBAR CLAVE DE CIFRADO
========================================== */

$claveCifrado =
    $VIZIUNEAI_FACTURAS_KEY ?? '';


if (!$claveCifrado || strlen($claveCifrado) < 32) {

    error_log(
        'Error: clave de cifrado de facturas no configurada correctamente.'
    );

    $claveCifradoValida = false;

} else {

    $claveCifradoValida = true;

}


/* ==========================================
   GENERAR CLAVE AES-256
========================================== */

$clave = null;

if ($claveCifradoValida) {

    $clave =
        hash(
            'sha256',
            $claveCifrado,
            true
        );

}


/* ==========================================
   FUNCIÓN PARA DESCIFRAR FACTURA PRIVADA
========================================== */

function descifrarDatosFactura(
    $datosCifrados,
    $clave
) {

    try {

        if (
            !$datosCifrados ||
            !$clave
        ) {

            return null;

        }


        if (
            !function_exists(
                'openssl_decrypt'
            )
        ) {

            return null;

        }


        /*
        |--------------------------------------------------------------------------
        | Los datos guardados por guardar_factura.php
        | tienen este formato:
        |
        | BASE64(
        |     IV de 16 bytes
        |     +
        |     datos cifrados
        | )
        |--------------------------------------------------------------------------
        */

        $contenidoPrivado =
            base64_decode(
                $datosCifrados,
                true
            );


        if (
            $contenidoPrivado === false ||
            strlen($contenidoPrivado) <= 16
        ) {

            return null;

        }


        /* ======================================
           EXTRAER IV
        ====================================== */

        $iv =
            substr(
                $contenidoPrivado,
                0,
                16
            );


        /* ======================================
           EXTRAER DATOS CIFRADOS
        ====================================== */

        $datos =
            substr(
                $contenidoPrivado,
                16
            );


        if (
            $datos === false ||
            $datos === ''
        ) {

            return null;

        }


        /* ======================================
           DESCIFRAR AES-256-CBC
        ====================================== */

        $json =
            openssl_decrypt(
                $datos,
                'AES-256-CBC',
                $clave,
                OPENSSL_RAW_DATA,
                $iv
            );


        if ($json === false) {

            return null;

        }


        /* ======================================
           CONVERTIR JSON
        ====================================== */

        return json_decode(
            $json,
            true
        );


    } catch (Throwable $e) {

        error_log(
            'Error descifrando factura en facturas.php: ' .
            $e->getMessage()
        );

        return null;

    }

}


/* ==========================================
   OBTENER FACTURAS DEL USUARIO CONECTADO
========================================== */

$facturas = [];


try {

    /*
    |--------------------------------------------------------------------------
    | IMPORTANTE
    |--------------------------------------------------------------------------
    |
    | clientes solamente se utiliza aquí como
    | vínculo técnico entre:
    |
    | usuario → factura
    |
    | Los datos reales del cliente de la factura
    | están cifrados en facturas_privadas.
    |
    */

    $stmt = $pdo->prepare("
        SELECT
            f.id,
            f.serie,
            f.numero,
            f.fecha_emision,
            f.estado,
            f.created_at,
            f.updated_at,
            fp.datos_cifrados

        FROM facturas f

        INNER JOIN clientes c
            ON f.cliente_id = c.id

        LEFT JOIN facturas_privadas fp
            ON fp.factura_id = f.id
            AND fp.usuario_id = ?

        WHERE c.usuario_id = ?
          AND c.activo = 1

        ORDER BY
            f.fecha_emision DESC,
            f.id DESC
    ");


    $stmt->execute([
        $usuarioId,
        $usuarioId
    ]);


    $facturasTecnicas =
        $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );


    /* ==========================================
       DESCIFRAR INFORMACIÓN PRIVADA
    ========================================== */

    foreach (
        $facturasTecnicas
        as $factura
    ) {

        $datosPrivados = null;


        if (
            !empty(
                $factura['datos_cifrados']
            )
        ) {

            $datosPrivados =
                descifrarDatosFactura(
                    $factura[
                        'datos_cifrados'
                    ],
                    $clave
                );

        }


        /* ======================================
           DATOS DEL CLIENTE
        ====================================== */

        $nombreCliente = '';

        $nifCliente = '';


        if (
            is_array($datosPrivados) &&
            isset(
                $datosPrivados['cliente']
            )
        ) {

            $clientePrivado =
                $datosPrivados['cliente'];


            $nombreCliente =
                $clientePrivado[
                    'nombre_razon_social'
                ] ?? '';


            $nifCliente =
                $clientePrivado[
                    'nif'
                ] ?? '';

        }


        /* ======================================
           DATOS ECONÓMICOS
        ====================================== */

        $baseImponible = 0;

        $totalIva = 0;

        $totalIrpf = 0;

        $total = 0;


        if (
            is_array($datosPrivados) &&
            isset(
                $datosPrivados['factura']
            )
        ) {

            $datosFactura =
                $datosPrivados['factura'];


            $baseImponible =
                (float) (
                    $datosFactura[
                        'base_imponible'
                    ] ?? 0
                );


            $totalIva =
                (float) (
                    $datosFactura[
                        'total_iva'
                    ] ?? 0
                );


            $totalIrpf =
                (float) (
                    $datosFactura[
                        'total_irpf'
                    ] ?? 0
                );


            $total =
                (float) (
                    $datosFactura[
                        'total'
                    ] ?? 0
                );

        }


        /* ======================================
           FECHA
        ====================================== */

        $fechaEmision =
            $factura['fecha_emision'];


        /*
        |--------------------------------------------------------------------------
        | Si la fecha técnica está vacía,
        | intentamos obtenerla de la información
        | privada.
        |--------------------------------------------------------------------------
        */

        if (
            empty($fechaEmision) &&
            isset($datosFactura) &&
            !empty(
                $datosFactura[
                    'fecha_emision'
                ]
            )
        ) {

            $fechaEmision =
                $datosFactura[
                    'fecha_emision'
                ];

        }


        /* ======================================
           NÚMERO
        ====================================== */

        if (
            $factura['numero'] !== null &&
            $factura['numero'] !== ''
        ) {

            $numeroFactura =
                $factura['serie'] .
                '-' .
                str_pad(
                    $factura['numero'],
                    6,
                    '0',
                    STR_PAD_LEFT
                );

        } else {

            $numeroFactura =
                'BORRADOR';

        }


        /* ======================================
           AÑADIR FACTURA AL LISTADO
        ====================================== */

        $facturas[] = [

            'id' =>
                (int) $factura['id'],

            'serie' =>
                $factura['serie'],

            'numero' =>
                $factura['numero'],

            'numero_factura' =>
                $numeroFactura,

            'fecha_emision' =>
                $fechaEmision,

            'base_imponible' =>
                $baseImponible,

            'total_iva' =>
                $totalIva,

            'total_irpf' =>
                $totalIrpf,

            'total' =>
                $total,

            'estado' =>
                $factura['estado'],

            'nombre_razon_social' =>
                $nombreCliente,

            'nif' =>
                $nifCliente

        ];

    }


} catch (PDOException $e) {

    error_log(
        'Error obteniendo facturas del usuario: ' .
        $e->getMessage()
    );

    $facturas = [];

}


/* ==========================================
   HTML
========================================== */

?>
<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Facturación | ViziuneAI
    </title>

    <link
        rel="stylesheet"
        href="css/facturas.css"
    >

</head>

<body>


    <!-- ==========================================
         CABECERA
    ========================================== -->

    <header class="facturacion-header">

        <div class="cabecera-contenido">

            <div>

                <h1>
                    Facturación
                </h1>

                <p>
                    Gestión de tus facturas
                </p>

            </div>


            <div class="cabecera-acciones">

                <a
                    href="index.php"
                    class="boton-secundario"
                >
                    ← Volver
                </a>


                <a
                    href="crear_factura.php"
                    class="boton-principal"
                >
                    + Nueva factura
                </a>

            </div>

        </div>

    </header>


    <!-- ==========================================
         CONTENIDO PRINCIPAL
    ========================================== -->

    <main class="facturacion-contenedor">


        <!-- ==========================================
             ESTADÍSTICAS
        ========================================== -->

        <section class="estadisticas">

            <?php

            $totalFacturas =
                count($facturas);

            $totalEmitidas = 0;

            $totalBorradores = 0;

            $totalAnuladas = 0;

            $importeTotal = 0;


            foreach (
                $facturas
                as $factura
            ) {

                if (
                    $factura['estado']
                    === 'emitida'
                ) {

                    $totalEmitidas++;

                }


                if (
                    $factura['estado']
                    === 'borrador'
                ) {

                    $totalBorradores++;

                }


                if (
                    $factura['estado']
                    === 'anulada'
                ) {

                    $totalAnuladas++;

                }


                if (
                    $factura['estado']
                    !== 'anulada'
                ) {

                    $importeTotal +=
                        (float)
                        $factura['total'];

                }

            }

            ?>


            <div class="estadistica">

                <span class="estadistica-titulo">
                    Facturas
                </span>

                <strong>
                    <?= $totalFacturas ?>
                </strong>

            </div>


            <div class="estadistica">

                <span class="estadistica-titulo">
                    Emitidas
                </span>

                <strong>
                    <?= $totalEmitidas ?>
                </strong>

            </div>


            <div class="estadistica">

                <span class="estadistica-titulo">
                    Borradores
                </span>

                <strong>
                    <?= $totalBorradores ?>
                </strong>

            </div>


            <div class="estadistica">

                <span class="estadistica-titulo">
                    Importe
                </span>

                <strong>

                    <?= number_format(
                        $importeTotal,
                        2,
                        ',',
                        '.'
                    ) ?>

                    €

                </strong>

            </div>

        </section>


        <!-- ==========================================
             FILTROS
        ========================================== -->

        <section class="filtros">

            <div class="campo-busqueda">

                <label for="buscarFactura">
                    Buscar
                </label>

                <input
                    type="search"
                    id="buscarFactura"
                    placeholder="Número, cliente o NIF..."
                    autocomplete="off"
                >

            </div>


            <div class="campo-filtro">

                <label for="filtroEstado">
                    Estado
                </label>

                <select id="filtroEstado">

                    <option value="todos">
                        Todos
                    </option>

                    <option value="emitida">
                        Emitidas
                    </option>

                    <option value="borrador">
                        Borradores
                    </option>

                    <option value="anulada">
                        Anuladas
                    </option>

                </select>

            </div>

        </section>


        <!-- ==========================================
             LISTADO DE FACTURAS
        ========================================== -->

        <section class="tabla-contenedor">

            <div class="tabla-cabecera">

                <h2>
                    Mis facturas
                </h2>

                <span>
                    <?= $totalFacturas ?>
                    registros
                </span>

            </div>


            <?php if (
                !empty($facturas)
            ): ?>


                <div class="tabla-responsive">

                    <table id="tablaFacturas">

                        <thead>

                            <tr>

                                <th>
                                    Número
                                </th>

                                <th>
                                    Cliente
                                </th>

                                <th>
                                    NIF
                                </th>

                                <th>
                                    Fecha
                                </th>

                                <th>
                                    Base
                                </th>

                                <th>
                                    IVA
                                </th>

                                <th>
                                    Total
                                </th>

                                <th>
                                    Estado
                                </th>

                                <th>
                                    Acciones
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                            <?php foreach (
                                $facturas
                                as $factura
                            ): ?>


                                <tr
                                    class="fila-factura"

                                    data-estado="<?= htmlspecialchars(
                                        $factura['estado'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"

                                    data-busqueda="<?= htmlspecialchars(
                                        strtolower(
                                            $factura[
                                                'numero_factura'
                                            ]
                                            . ' '
                                            . $factura[
                                                'nombre_razon_social'
                                            ]
                                            . ' '
                                            . $factura[
                                                'nif'
                                            ]
                                        ),
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                >


                                    <!-- ==============================
                                         NÚMERO
                                    =============================== -->

                                    <td>

                                        <strong>

                                            <?= htmlspecialchars(
                                                $factura[
                                                    'numero_factura'
                                                ],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>

                                        </strong>

                                    </td>


                                    <!-- ==============================
                                         CLIENTE
                                    =============================== -->

                                    <td>

                                        <?= htmlspecialchars(
                                            $factura[
                                                'nombre_razon_social'
                                            ],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    </td>


                                    <!-- ==============================
                                         NIF
                                    =============================== -->

                                    <td>

                                        <?= htmlspecialchars(
                                            $factura[
                                                'nif'
                                            ],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    </td>


                                    <!-- ==============================
                                         FECHA
                                    =============================== -->

                                    <td>

                                        <?php

                                        if (
                                            !empty(
                                                $factura[
                                                    'fecha_emision'
                                                ]
                                            )
                                        ) {

                                            $fecha =
                                                date(
                                                    'd/m/Y',
                                                    strtotime(
                                                        $factura[
                                                            'fecha_emision'
                                                        ]
                                                    )
                                                );

                                        } else {

                                            $fecha =
                                                '—';

                                        }

                                        ?>

                                        <?= htmlspecialchars(
                                            $fecha,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    </td>


                                    <!-- ==============================
                                         BASE
                                    =============================== -->

                                    <td>

                                        <?= number_format(
                                            (float)
                                            $factura[
                                                'base_imponible'
                                            ],
                                            2,
                                            ',',
                                            '.'
                                        ) ?>

                                        €

                                    </td>


                                    <!-- ==============================
                                         IVA
                                    =============================== -->

                                    <td>

                                        <?= number_format(
                                            (float)
                                            $factura[
                                                'total_iva'
                                            ],
                                            2,
                                            ',',
                                            '.'
                                        ) ?>

                                        €

                                    </td>


                                    <!-- ==============================
                                         TOTAL
                                    =============================== -->

                                    <td>

                                        <strong>

                                            <?= number_format(
                                                (float)
                                                $factura[
                                                    'total'
                                                ],
                                                2,
                                                ',',
                                                '.'
                                            ) ?>

                                            €

                                        </strong>

                                    </td>


                                    <!-- ==============================
                                         ESTADO
                                    =============================== -->

                                    <td>

                                        <?php

                                        switch (
                                            $factura[
                                                'estado'
                                            ]
                                        ) {

                                            case 'emitida':

                                                $textoEstado =
                                                    'Emitida';

                                                break;


                                            case 'borrador':

                                                $textoEstado =
                                                    'Borrador';

                                                break;


                                            case 'anulada':

                                                $textoEstado =
                                                    'Anulada';

                                                break;


                                            default:

                                                $textoEstado =
                                                    ucfirst(
                                                        $factura[
                                                            'estado'
                                                        ]
                                                    );

                                        }

                                        ?>


                                        <span
                                            class="estado estado-<?= htmlspecialchars(
                                                $factura[
                                                    'estado'
                                                ],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>"
                                        >

                                            <?= htmlspecialchars(
                                                $textoEstado,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>

                                        </span>

                                    </td>


                                    <!-- ==============================
                                         ACCIONES
                                    =============================== -->

                                    <td>

                                        <div class="acciones-factura">


                                            <a
                                                href="ver_factura.php?id=<?= (int) $factura['id'] ?>"
                                                class="boton-accion"
                                                title="Ver factura"
                                            >
                                                Ver
                                            </a>


                                            <?php if (
                                                $factura[
                                                    'estado'
                                                ]
                                                === 'borrador'
                                            ): ?>


                                                <a
                                                    href="crear_factura.php?id=<?= (int) $factura['id'] ?>"
                                                    class="boton-accion"
                                                    title="Editar factura"
                                                >
                                                    Editar
                                                </a>


                                            <?php endif; ?>


                                            <?php if (
                                                $factura[
                                                    'estado'
                                                ]
                                                === 'emitida'
                                            ): ?>


                                                <a
                                                    href="generar_pdf.php?id=<?= (int) $factura['id'] ?>"
                                                    class="boton-accion"
                                                    target="_blank"
                                                    title="Ver PDF"
                                                >
                                                    PDF
                                                </a>


                                            <?php endif; ?>


                                        </div>

                                    </td>


                                </tr>


                            <?php endforeach; ?>


                        </tbody>

                    </table>

                </div>


            <?php else: ?>


                <!-- ==========================================
                     SIN FACTURAS
                ========================================== -->

                <div class="sin-facturas">

                    <div class="sin-facturas-icono">
                        📄
                    </div>


                    <h3>
                        Todavía no hay facturas
                    </h3>


                    <p>
                        Crea tu primera factura para empezar
                        a utilizar el sistema de facturación.
                    </p>


                    <a
                        href="crear_factura.php"
                        class="boton-principal"
                    >
                        + Crear primera factura
                    </a>

                </div>


            <?php endif; ?>


        </section>


    </main>


    <!-- ==========================================
         JAVASCRIPT
    ========================================== -->

    <script src="js/facturas.js"></script>


</body>

</html>
