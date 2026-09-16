
<?php

session_start();

require_once 'config.php';


/*
|--------------------------------------------------------------------------
| Comprobar que el usuario ha iniciado sesión
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}


$usuarioId = (int) $_SESSION['usuario_id'];


/*
|--------------------------------------------------------------------------
| Acceso gratuito
|--------------------------------------------------------------------------
*/

$_SESSION['suscripcion_activa'] = 1;


$usuarioNombre = $_SESSION['usuario_nombre'] ?? '';
$usuarioEmail = $_SESSION['usuario_email'] ?? '';

$error = '';
$mensaje = '';


/*
|--------------------------------------------------------------------------
| Buscar cliente asociado al usuario
|--------------------------------------------------------------------------
*/

$stmtCliente = $pdo->prepare("
    SELECT
        id,
        usuario_id,
        tipo_persona,
        nombre,
        apellidos,
        nombre_razon_social,
        nombre_comercial,
        nif,
        direccion,
        codigo_postal,
        ciudad,
        provincia,
        pais,
        email,
        telefono,
        web,
        sector_actividad
    FROM clientes
    WHERE usuario_id = ?
      AND activo = 1
    LIMIT 1
");

$stmtCliente->execute([$usuarioId]);

$cliente = $stmtCliente->fetch(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Si no existe cliente
|--------------------------------------------------------------------------
*/

if (!$cliente) {

    $error = 'No se ha encontrado tu perfil de cliente.';

} else {

    $_SESSION['cliente_id'] = (int) $cliente['id'];
}


/*
|--------------------------------------------------------------------------
| Guardar cambios del perfil
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $cliente) {

    $tipoPersona = trim($_POST['tipo_persona'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $nombreRazonSocial = trim($_POST['nombre_razon_social'] ?? '');
    $nombreComercial = trim($_POST['nombre_comercial'] ?? '');
    $nif = trim($_POST['nif'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $codigoPostal = trim($_POST['codigo_postal'] ?? '');
    $ciudad = trim($_POST['ciudad'] ?? '');
    $provincia = trim($_POST['provincia'] ?? '');
    $pais = trim($_POST['pais'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $web = trim($_POST['web'] ?? '');
    $sectorActividad = trim($_POST['sector_actividad'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | Validar tipo de persona
    |--------------------------------------------------------------------------
    */

    $tiposPermitidos = [
        'empresa',
        'autonomo',
        'particular'
    ];

    if (!in_array($tipoPersona, $tiposPermitidos, true)) {

        $error = 'El tipo de persona seleccionado no es válido.';

    } elseif ($nombreRazonSocial === '') {

        $error = 'Introduce tu nombre o razón social.';

    } elseif ($nif === '') {

        $error = 'Introduce tu NIF/CIF.';

    } elseif ($pais === '') {

        $error = 'Introduce tu país.';

    } else {

        try {

            /*
            |--------------------------------------------------------------------------
            | Actualizar cliente
            |--------------------------------------------------------------------------
            */

            $stmtUpdate = $pdo->prepare("
                UPDATE clientes
                SET
                    tipo_persona = ?,
                    nombre = ?,
                    apellidos = ?,
                    nombre_razon_social = ?,
                    nombre_comercial = ?,
                    nif = ?,
                    direccion = ?,
                    codigo_postal = ?,
                    ciudad = ?,
                    provincia = ?,
                    pais = ?,
                    telefono = ?,
                    web = ?,
                    sector_actividad = ?
                WHERE id = ?
                  AND usuario_id = ?
            ");

            $stmtUpdate->execute([
                $tipoPersona,
                $nombre !== '' ? $nombre : null,
                $apellidos !== '' ? $apellidos : null,
                $nombreRazonSocial,
                $nombreComercial !== '' ? $nombreComercial : null,
                $nif,
                $direccion !== '' ? $direccion : null,
                $codigoPostal !== '' ? $codigoPostal : null,
                $ciudad !== '' ? $ciudad : null,
                $provincia !== '' ? $provincia : null,
                $pais,
                $telefono !== '' ? $telefono : null,
                $web !== '' ? $web : null,
                $sectorActividad !== '' ? $sectorActividad : null,
                $cliente['id'],
                $usuarioId
            ]);


            /*
            |--------------------------------------------------------------------------
            | Actualizar nombre de sesión
            |--------------------------------------------------------------------------
            */

            $_SESSION['usuario_nombre'] = $nombreRazonSocial;


            /*
            |--------------------------------------------------------------------------
            | Mensaje de éxito
            |--------------------------------------------------------------------------
            */

            $mensaje = 'Tus datos se han actualizado correctamente.';


            /*
            |--------------------------------------------------------------------------
            | Volver a cargar los datos actualizados
            |--------------------------------------------------------------------------
            */

            $stmtCliente->execute([$usuarioId]);

            $cliente = $stmtCliente->fetch(PDO::FETCH_ASSOC);


        } catch (Throwable $e) {

            $error = 'No se han podido guardar los cambios.';


        }

    }

}


/*
|--------------------------------------------------------------------------
| Preparar información visual
|--------------------------------------------------------------------------
*/

$nombreMostrar = '';

if ($cliente && !empty($cliente['nombre_razon_social'])) {

    $nombreMostrar = $cliente['nombre_razon_social'];

} elseif (!empty($usuarioNombre)) {

    $nombreMostrar = $usuarioNombre;

} else {

    $nombreMostrar = 'Cliente';

}


/*
|--------------------------------------------------------------------------
| Calcular porcentaje de datos completados
|--------------------------------------------------------------------------
|
| Cada uno de los 15 campos vale lo mismo.
|
*/

$camposPerfil = [
    'tipo_persona',
    'nombre',
    'apellidos',
    'nombre_razon_social',
    'nombre_comercial',
    'nif',
    'direccion',
    'codigo_postal',
    'ciudad',
    'provincia',
    'pais',
    'email',
    'telefono',
    'web',
    'sector_actividad'
];


$camposCompletados = 0;


if ($cliente) {

    foreach ($camposPerfil as $campo) {

        if (!empty(trim((string) ($cliente[$campo] ?? '')))) {

            $camposCompletados++;

        }

    }

}


$totalCampos = count($camposPerfil);


$porcentajePerfil = $totalCampos > 0
    ? round(($camposCompletados / $totalCampos) * 100)
    : 0;


/*
|--------------------------------------------------------------------------
| Texto del estado del perfil
|--------------------------------------------------------------------------
*/

if ($porcentajePerfil >= 100) {

    $estadoPerfil = 'Perfil completo';

} elseif ($porcentajePerfil >= 70) {

    $estadoPerfil = 'Perfil casi completo';

} else {

    $estadoPerfil = 'Perfil pendiente de completar';

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

    <title>Mi cuenta | ViziuneAI</title>

    <link
        rel="stylesheet"
        href="css/mi-cuenta.css"
    >

</head>

<body>


    <header class="header" style="position:fixed;">

        <?php include 'menu.php'; ?>

    </header>


    <main class="account-container">


        <!-- =========================================================
             CABECERA
        ========================================================== -->

        <section class="welcome-section">

            <div class="welcome-content">

                <span class="welcome-label">
                    ÁREA DE CLIENTE
                </span>

                <h1>
                    Hola, <?= htmlspecialchars($nombreMostrar) ?>
                </h1>

                <p>
                    Gestiona tu cuenta, consulta tus datos y accede
                    rápidamente a los servicios de ViziuneAI.
                </p>

            </div>

        </section>


        <?php if (!empty($error)): ?>

            <div class="alert-error">

                <?= htmlspecialchars($error) ?>

            </div>

        <?php endif; ?>


        <?php if (!empty($mensaje)): ?>

            <div class="alert-success">

                <?= htmlspecialchars($mensaje) ?>

            </div>

        <?php endif; ?>


        <?php if ($cliente): ?>


            <!-- =====================================================
                 ESTADÍSTICAS
            ====================================================== -->

            <section class="stats-grid">


                <div class="stat-card">

                    <div class="stat-icon">
                        👤
                    </div>

                    <div class="stat-content">

                        <span class="stat-label">
                            Estado de la cuenta
                        </span>

                        <strong>
                            Activa
                        </strong>

                    </div>

                </div>


                <div class="stat-card">

                    <div class="stat-icon">
                        ✓
                    </div>

                    <div class="stat-content">

                        <span class="stat-label">
                            Perfil
                        </span>

                        <strong>
                            <?= $porcentajePerfil ?>%
                        </strong>

                    </div>

                </div>


                <div class="stat-card">

                    <div class="stat-icon">
                        📄
                    </div>

                    <div class="stat-content">

                        <span class="stat-label">
                            Facturación
                        </span>

                        <strong>
                            Disponible
                        </strong>

                    </div>

                </div>


                <div class="stat-card">

                    <div class="stat-icon">
                        🔒
                    </div>

                    <div class="stat-content">

                        <span class="stat-label">
                            Acceso
                        </span>

                        <strong>
                            Seguro
                        </strong>

                    </div>

                </div>


            </section>


            <!-- =====================================================
                 ACCESOS RÁPIDOS
            ====================================================== -->

            <section class="quick-section">

                <div class="section-header">

                    <div>

                        <h2>
                            Accesos rápidos
                        </h2>

                        <p>
                            Accede directamente a las principales
                            funciones de tu área de cliente.
                        </p>

                    </div>

                </div>


                <div class="quick-grid">


                    <a
                        href="facturas.php"
                        class="quick-card"
                    >

                        <div class="quick-icon">
                            📄
                        </div>

                        <div class="quick-content">

                            <h3>
                                Mis facturas
                            </h3>

                            <p>
                                Consulta y gestiona tus facturas
                                desde la plataforma.
                            </p>

                            <span class="quick-link">
                                Ver facturas →
                            </span>

                        </div>

                    </a>


                    <a
                        href="calendario.php"
                        class="quick-card"
                    >

                        <div class="quick-icon">
                            📅
                        </div>

                        <div class="quick-content">

                            <h3>
                                Mis reuniones
                            </h3>

                            <p>
                                Consulta tus reuniones para gestionar
                                mejor tu día a día.
                            </p>

                            <span class="quick-link">
                                Ver reuniones →
                            </span>

                        </div>

                    </a>


                    <div class="quick-card">

                        <div class="quick-icon">
                            🛡️
                        </div>

                        <div class="quick-content">

                            <h3>
                                Seguridad
                            </h3>

                            <p>
                                Mantén protegida tu cuenta y tus
                                datos de acceso.
                            </p>

                            <span class="quick-link muted">
                                Gestión de cuenta
                            </span>

                        </div>

                    </div>


                </div>

            </section>


            <!-- =====================================================
                 DATOS DEL CLIENTE
            ====================================================== -->

            <section class="account-section">


                <div class="section-header">

                    <div>

                        <span class="section-kicker">
                            INFORMACIÓN
                        </span>

                        <h2>
                            Mis datos
                        </h2>

                        <p>
                            Completa tus datos para aumentar el nivel
                            de información de tu perfil.
                        </p>

                    </div>


                    <div class="profile-status">

                        <span class="status-dot"></span>

                        <?= htmlspecialchars($estadoPerfil) ?>

                    </div>

                </div>


                <!-- =================================================
                     PROGRESO
                ================================================== -->

                <div class="profile-progress">

                    <div class="progress-header">

                        <span>
                            Compleción del perfil
                        </span>

                        <strong>
                            <?= $porcentajePerfil ?>%
                        </strong>

                    </div>


                    <div class="progress-bar">

                        <div
                            class="progress-fill"
                            style="width: <?= $porcentajePerfil ?>%;"
                        ></div>

                    </div>

                </div>


                <!-- =================================================
                     FORMULARIO DE DATOS
                ================================================== -->

                <form
                    method="POST"
                    action="mi_cuenta.php"
                    class="client-data-form"
                >


                    <!-- TIPO DE PERSONA -->

                    <div class="data-item">

                        <label for="tipo_persona">
                            Tipo de persona
                        </label>

                        <select
                            id="tipo_persona"
                            name="tipo_persona"
                            required
                        >

                            <option
                                value="particular"
                                <?= ($cliente['tipo_persona'] ?? '') === 'particular'
                                    ? 'selected'
                                    : '' ?>
                            >
                                Particular
                            </option>

                            <option
                                value="autonomo"
                                <?= ($cliente['tipo_persona'] ?? '') === 'autonomo'
                                    ? 'selected'
                                    : '' ?>
                            >
                                Autónomo
                            </option>

                            <option
                                value="empresa"
                                <?= ($cliente['tipo_persona'] ?? '') === 'empresa'
                                    ? 'selected'
                                    : '' ?>
                            >
                                Empresa
                            </option>

                        </select>

                    </div>


                    <!-- NOMBRE -->

                    <div class="data-item">

                        <label for="nombre">
                            Nombre
                        </label>

                        <input
                            type="text"
                            id="nombre"
                            name="nombre"
                            maxlength="100"
                            value="<?= htmlspecialchars(
                                $cliente['nombre'] ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            placeholder="Tu nombre"
                        >

                    </div>


                    <!-- APELLIDOS -->

                    <div class="data-item">

                        <label for="apellidos">
                            Apellidos
                        </label>

                        <input
                            type="text"
                            id="apellidos"
                            name="apellidos"
                            maxlength="150"
                            value="<?= htmlspecialchars(
                                $cliente['apellidos'] ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            placeholder="Tus apellidos"
                        >

                    </div>


                    <!-- NOMBRE / RAZÓN SOCIAL -->

                    <div class="data-item">

                        <label for="nombre_razon_social">
                            Nombre / Razón social
                        </label>

                        <input
                            type="text"
                            id="nombre_razon_social"
                            name="nombre_razon_social"
                            maxlength="255"
                            value="<?= htmlspecialchars(
                                $cliente['nombre_razon_social'] ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            placeholder="Nombre o razón social"
                            required
                        >

                    </div>


                    <!-- NOMBRE COMERCIAL -->

                    <div class="data-item">

                        <label for="nombre_comercial">
                            Nombre comercial
                        </label>

                        <input
                            type="text"
                            id="nombre_comercial"
                            name="nombre_comercial"
                            maxlength="255"
                            value="<?= htmlspecialchars(
                                $cliente['nombre_comercial'] ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            placeholder="Nombre comercial de tu negocio"
                        >

                    </div>


                    <!-- NIF -->

                    <div class="data-item">

                        <label for="nif">
                            NIF / CIF
                        </label>

                        <input
                            type="text"
                            id="nif"
                            name="nif"
                            maxlength="30"
                            value="<?= htmlspecialchars(
                                $cliente['nif'] ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            placeholder="NIF / CIF"
                            required
                        >

                    </div>


                    <!-- EMAIL -->

                    <div class="data-item">

                        <label for="email">
                            Email
                        </label>

                        <input
                            type="email"
                            id="email"
                            value="<?= htmlspecialchars(
                                $cliente['email'] ?: $usuarioEmail,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            readonly
                        >

                    </div>


                    <!-- TELÉFONO -->

                    <div class="data-item">

                        <label for="telefono">
                            Teléfono
                        </label>

                        <input
                            type="tel"
                            id="telefono"
                            name="telefono"
                            maxlength="50"
                            value="<?= htmlspecialchars(
                                $cliente['telefono'] ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            placeholder="Tu teléfono"
                        >

                    </div>


                    <!-- DIRECCIÓN -->

                    <div class="data-item">

                        <label for="direccion">
                            Dirección
                        </label>

                        <input
                            type="text"
                            id="direccion"
                            name="direccion"
                            maxlength="255"
                            value="<?= htmlspecialchars(
                                $cliente['direccion'] ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            placeholder="Calle, número, piso..."
                        >

                    </div>


                    <!-- CÓDIGO POSTAL -->

                    <div class="data-item">

                        <label for="codigo_postal">
                            Código postal
                        </label>

                        <input
                            type="text"
                            id="codigo_postal"
                            name="codigo_postal"
                            maxlength="10"
                            value="<?= htmlspecialchars(
                                $cliente['codigo_postal'] ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            placeholder="Código postal"
                        >

                    </div>


                    <!-- CIUDAD -->

                    <div class="data-item">

                        <label for="ciudad">
                            Ciudad
                        </label>

                        <input
                            type="text"
                            id="ciudad"
                            name="ciudad"
                            maxlength="100"
                            value="<?= htmlspecialchars(
                                $cliente['ciudad'] ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            placeholder="Ciudad"
                        >

                    </div>


                    <!-- PROVINCIA -->

                    <div class="data-item">

                        <label for="provincia">
                            Provincia
                        </label>

                        <input
                            type="text"
                            id="provincia"
                            name="provincia"
                            maxlength="100"
                            value="<?= htmlspecialchars(
                                $cliente['provincia'] ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            placeholder="Provincia"
                        >

                    </div>


                    <!-- PAÍS -->

                    <div class="data-item">

                        <label for="pais">
                            País
                        </label>

                        <input
                            type="text"
                            id="pais"
                            name="pais"
                            maxlength="100"
                            value="<?= htmlspecialchars(
                                $cliente['pais'] ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            placeholder="País"
                        >

                    </div>


                    <!-- WEB -->

                    <div class="data-item">

                        <label for="web">
                            Página web
                        </label>

                        <input
                            type="url"
                            id="web"
                            name="web"
                            maxlength="255"
                            value="<?= htmlspecialchars(
                                $cliente['web'] ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            placeholder="https://www.tuweb.com"
                        >

                    </div>


                    <!-- SECTOR -->

                    <div class="data-item">

                        <label for="sector_actividad">
                            Sector / Actividad
                        </label>

                        <input
                            type="text"
                            id="sector_actividad"
                            name="sector_actividad"
                            maxlength="150"
                            value="<?= htmlspecialchars(
                                $cliente['sector_actividad'] ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            placeholder="Ej. Diseño web, comercio, hostelería..."
                        >

                    </div>


                    <!-- =================================================
                         BOTÓN GUARDAR
                    ================================================== -->

                    <div class="profile-save">

                        <button
                            type="submit"
                            class="save-profile-button"
                        >
                            Guardar mis datos
                        </button>

                    </div>


                </form>


            </section>


            <!-- =====================================================
                 INFORMACIÓN FINAL
            ====================================================== -->

            <section class="account-info">

                <div class="account-info-icon">
                    ✓
                </div>

                <div>

                    <h3>
                        Tu cuenta está activa
                    </h3>

                    <p>
                        Completa tus datos para mantener actualizada
                        tu información de cliente y aprovechar todas
                        las funciones de tu área privada.
                    </p>

                </div>

            </section>


        <?php endif; ?>


    </main>


    <script src="js/mi-cuenta.js"></script>

</body>

</html>
