
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
|
| La plataforma ya no requiere suscripción.
| Mantenemos esta variable de sesión por compatibilidad
| con otras partes antiguas del sistema.
|
*/

$_SESSION['suscripcion_activa'] = 1;


$usuarioNombre = $_SESSION['usuario_nombre'] ?? '';
$usuarioEmail = $_SESSION['usuario_email'] ?? '';


/*
|--------------------------------------------------------------------------
| Buscar cliente asociado al usuario
|--------------------------------------------------------------------------
*/

$stmtCliente = $pdo->prepare("
    SELECT
        id,
        nombre_razon_social,
        nif,
        direccion,
        codigo_postal,
        ciudad,
        provincia,
        pais,
        email,
        telefono
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

    /*
    |--------------------------------------------------------------------------
    | Guardar cliente en sesión
    |--------------------------------------------------------------------------
    */

    $_SESSION['cliente_id'] = (int) $cliente['id'];

    $error = '';
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
*/

$camposPerfil = [
    'nombre_razon_social',
    'nif',
    'email',
    'telefono',
    'direccion',
    'codigo_postal',
    'ciudad',
    'provincia'
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
                        class="quick-card">
                        

                        <div class="quick-icon">
                            📅
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
                            Datos asociados actualmente a tu cuenta
                            de cliente.
                        </p>

                    </div>


                    <div class="profile-status">

                        <span class="status-dot"></span>

                        <?= htmlspecialchars($estadoPerfil) ?>

                    </div>

                </div>


                <!-- PROGRESO DEL PERFIL -->

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


                <!-- DATOS -->

                <div class="client-data">


                    <div class="data-item">

                        <span>
                            Nombre / Razón social
                        </span>

                        <strong>
                            <?= htmlspecialchars(
                                $cliente['nombre_razon_social'] ?? ''
                            ) ?>
                        </strong>

                    </div>


                    <div class="data-item">

                        <span>
                            NIF / DNI
                        </span>

                        <strong>
                            <?= htmlspecialchars(
                                $cliente['nif'] ?: 'No indicado'
                            ) ?>
                        </strong>

                    </div>


                    <div class="data-item">

                        <span>
                            Email
                        </span>

                        <strong>
                            <?= htmlspecialchars(
                                $cliente['email'] ?: $usuarioEmail
                            ) ?>
                        </strong>

                    </div>


                    <div class="data-item">

                        <span>
                            Teléfono
                        </span>

                        <strong>
                            <?= htmlspecialchars(
                                $cliente['telefono'] ?: 'No indicado'
                            ) ?>
                        </strong>

                    </div>


                    <div class="data-item">

                        <span>
                            Dirección
                        </span>

                        <strong>
                            <?= htmlspecialchars(
                                $cliente['direccion'] ?: 'No indicada'
                            ) ?>
                        </strong>

                    </div>


                    <div class="data-item">

                        <span>
                            Localidad
                        </span>

                        <strong>

                            <?php

                            $localidad = [];

                            if (!empty($cliente['codigo_postal'])) {
                                $localidad[] = $cliente['codigo_postal'];
                            }

                            if (!empty($cliente['ciudad'])) {
                                $localidad[] = $cliente['ciudad'];
                            }

                            if (!empty($cliente['provincia'])) {
                                $localidad[] = $cliente['provincia'];
                            }

                            echo htmlspecialchars(
                                !empty($localidad)
                                    ? implode(', ', $localidad)
                                    : 'No indicada'
                            );

                            ?>

                        </strong>

                    </div>


                    <div class="data-item">

                        <span>
                            País
                        </span>

                        <strong>
                            <?= htmlspecialchars(
                                $cliente['pais'] ?: 'No indicado'
                            ) ?>
                        </strong>

                    </div>


                    <div class="data-item">

                        <span>
                            Estado
                        </span>

                        <strong class="active-value">
                            ● Cuenta activa
                        </strong>

                    </div>


                </div>

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
                        Desde esta área podrás gestionar progresivamente
                        tus servicios, facturas, reuniones y demás
                        información relacionada con ViziuneAI.
                    </p>

                </div>

            </section>


        <?php endif; ?>


    </main>


    <script src="js/mi-cuenta.js"></script>

</body>

</html>
