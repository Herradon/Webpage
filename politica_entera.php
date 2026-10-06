
<?php

session_start();

require_once 'config.php';

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Política de privacidad | Viziune</title>

    <link
        rel="stylesheet"
        href="css/style.css"
    >
    
    <!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-DPY8CEKPEF"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-DPY8CEKPEF');
</script>

    <style>

        /* ==========================================================
           POLÍTICA DE PRIVACIDAD COMPLETA
        ========================================================== */

        .politica-page {

            min-height: 100vh;

            padding: 120px 20px 60px;

            box-sizing: border-box;

            background:
                radial-gradient(
                    circle at top right,
                    rgba(0, 207, 224, 0.08),
                    transparent 35%
                ),
                #061018;

            color: #ffffff;

        }


        .politica-container {

            width: 100%;

            max-width: 950px;

            margin: 0 auto;

            box-sizing: border-box;

        }


        .politica-card {

            background: #0d1821;

            border: 1px solid rgba(0, 207, 224, 0.18);

            border-radius: 20px;

            padding: 45px;

            box-sizing: border-box;

            box-shadow:
                0 25px 80px rgba(0, 0, 0, 0.35);

        }


        .politica-header {

            padding-bottom: 30px;

            margin-bottom: 35px;

            border-bottom:
                1px solid rgba(255, 255, 255, 0.08);

        }


        .politica-icon {

            display: block;

            margin-bottom: 12px;

            font-size: 42px;

        }


        .politica-header h1 {

            margin: 0 0 12px;

            color: #00cfe0;

            font-size: 34px;

            line-height: 1.2;

        }


        .politica-fecha {

            margin: 0;

            color: #8495a3;

            font-size: 14px;

        }


        .politica-contenido {

            color: #c3d0da;

            font-size: 15px;

            line-height: 1.8;

        }


        .politica-contenido h2 {

            margin: 38px 0 14px;

            color: #ffffff;

            font-size: 21px;

            line-height: 1.35;

        }


        .politica-contenido h3 {

            margin: 25px 0 10px;

            color: #00cfe0;

            font-size: 17px;

        }


        .politica-contenido p {

            margin: 0 0 16px;

        }


        .politica-contenido ul {

            margin:

                0 0 20px 22px;

            padding: 0;

        }


        .politica-contenido li {

            margin-bottom: 9px;

        }


        .politica-contenido strong {

            color: #ffffff;

        }


        .politica-destacado {

            margin: 25px 0;

            padding: 18px 20px;

            border-left:

                3px solid #00cfe0;

            border-radius: 8px;

            background:

                rgba(0, 207, 224, 0.06);

        }


        .politica-contacto {

            margin-top: 35px;

            padding: 22px;

            border:

                1px solid rgba(0, 207, 224, 0.18);

            border-radius: 12px;

            background:

                rgba(0, 207, 224, 0.04);

        }


        .politica-contacto p {

            margin-bottom: 8px;

        }


        .politica-footer {

            margin-top: 40px;

            padding-top: 25px;

            border-top:

                1px solid rgba(255, 255, 255, 0.08);

            text-align: center;

            color: #718390;

            font-size: 13px;

        }


        .politica-volver {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            margin-top: 25px;

            padding: 12px 20px;

            border-radius: 9px;

            background: #00cfe0;

            color: #061018;

            text-decoration: none;

            font-weight: 700;

            transition: 0.2s ease;

        }


        .politica-volver:hover {

            transform: translateY(-1px);

            opacity: 0.92;

        }


        /* ==========================================================
           MÓVIL
        ========================================================== */

        @media (max-width: 700px) {

            .politica-page {

                padding:

                    90px 12px 35px;

            }


            .politica-card {

                padding: 25px 20px;

                border-radius: 15px;

            }


            .politica-header h1 {

                font-size: 27px;

            }


            .politica-icon {

                font-size: 35px;

            }


            .politica-contenido {

                font-size: 14px;

                line-height: 1.7;

            }


            .politica-contenido h2 {

                font-size: 19px;

            }


            .politica-contenido h3 {

                font-size: 16px;

            }

        }

    </style>

</head>


<body>


    <main class="politica-page">

        <div class="politica-container">

            <article class="politica-card">


                <header class="politica-header">

                    <h1>
                        Política de privacidad
                    </h1>

                    <p class="politica-fecha">
                        Última actualización: 22 de septiembre de 2026
                    </p>

                </header>


                <div class="politica-contenido">


                    <p>
                        En Viziune nos comprometemos a proteger la privacidad
                        de las personas que utilizan nuestro sitio web,
                        nuestros servicios y las funcionalidades disponibles
                        a través de la plataforma.
                    </p>


                    <p>
                        Esta Política de Privacidad explica qué datos podemos
                        recopilar, para qué los utilizamos, cómo los protegemos
                        y cuáles son los derechos que puedes ejercer sobre tus
                        datos personales.
                    </p>


                    <div class="politica-destacado">

                        <strong>
                            Importante:
                        </strong>

                        Al utilizar determinadas funcionalidades privadas de
                        Viziune, puede ser necesario disponer de una cuenta
                        de usuario y aceptar previamente las condiciones
                        aplicables al tratamiento de los datos personales.

                    </div>


                    <h2>
                        1. Responsable del tratamiento
                    </h2>

                    <p>
                        El responsable del tratamiento de los datos personales
                        tratados a través de Viziune es:
                    </p>

                    <p>
                        <strong>Viziune SL</strong>
                    </p>

                    <p>
                        La información identificativa y los datos de contacto
                        del responsable podrán consultarse en los canales
                        oficiales de contacto de Viziune.
                    </p>


                    <h2>
                        2. Datos personales que podemos recopilar
                    </h2>

                    <p>
                        Dependiendo de las funcionalidades que utilices,
                        Viziune puede tratar diferentes categorías de datos.
                    </p>

                    <h3>
                        Datos de registro
                    </h3>

                    <ul>

                        <li>
                            Nombre y apellidos, cuando sean facilitados.
                        </li>

                        <li>
                            Dirección de correo electrónico.
                        </li>

                        <li>
                            Credenciales necesarias para acceder a la cuenta.
                        </li>

                    </ul>


                    <h3>
                        Datos facilitados durante el uso de los servicios
                    </h3>

                    <ul>

                        <li>
                            Información que introduzcas voluntariamente en
                            formularios.
                        </li>

                        <li>
                            Mensajes enviados a través del sistema de chat.
                        </li>

                        <li>
                            Solicitudes de información, contacto o presupuesto.
                        </li>

                        <li>
                            Información necesaria para utilizar determinadas
                            herramientas de Viziune.
                        </li>

                    </ul>


                    <h3>
                        Datos técnicos
                    </h3>

                    <p>
                        También pueden registrarse determinados datos técnicos
                        necesarios para garantizar el funcionamiento,
                        seguridad y mantenimiento del sitio web, como
                        información relacionada con el navegador, dispositivo,
                        dirección IP, fecha y hora de acceso o registros
                        técnicos del servidor.
                    </p>


                    <h2>
                        3. Finalidades del tratamiento
                    </h2>

                    <p>
                        Los datos personales podrán ser tratados para las
                        siguientes finalidades:
                    </p>

                    <ul>

                        <li>
                            Crear y gestionar tu cuenta de usuario.
                        </li>

                        <li>
                            Permitir el acceso a las funcionalidades privadas
                            de Viziune.
                        </li>

                        <li>
                            Prestar los servicios solicitados.
                        </li>

                        <li>
                            Atender consultas, solicitudes y comunicaciones.
                        </li>

                        <li>
                            Gestionar solicitudes de contacto o presupuesto.
                        </li>

                        <li>
                            Gestionar y mantener las herramientas disponibles
                            en la plataforma.
                        </li>

                        <li>
                            Mantener la seguridad de la plataforma.
                        </li>

                        <li>
                            Detectar y prevenir usos fraudulentos o indebidos.
                        </li>

                        <li>
                            Cumplir las obligaciones legales que resulten
                            aplicables.
                        </li>

                    </ul>


                    <h2>
                        4. Base jurídica del tratamiento
                    </h2>

                    <p>
                        El tratamiento de los datos personales se realizará,
                        según corresponda, sobre alguna de las bases jurídicas
                        previstas por la normativa aplicable en materia de
                        protección de datos.
                    </p>

                    <ul>

                        <li>
                            El consentimiento de la persona interesada.
                        </li>

                        <li>
                            La ejecución de una relación contractual o de
                            medidas precontractuales.
                        </li>

                        <li>
                            El cumplimiento de obligaciones legales.
                        </li>

                        <li>
                            El interés legítimo del responsable, cuando resulte
                            aplicable y siempre respetando los derechos de las
                            personas afectadas.
                        </li>

                    </ul>


                    <h2>
                        5. Cuenta de usuario
                    </h2>

                    <p>
                        Para acceder a determinadas funcionalidades de
                        Viziune puede ser necesario disponer de una cuenta.
                    </p>

                    <p>
                        El usuario es responsable de proporcionar información
                        correcta y actualizada y de mantener la
                        confidencialidad de sus credenciales de acceso.
                    </p>

                    <p>
                        Si detectas un acceso no autorizado o cualquier
                        incidencia relacionada con tu cuenta, deberás
                        comunicarlo a Viziune a través de los canales
                        disponibles.
                    </p>


                    <h2>
                        6. Comunicaciones con Viziune
                    </h2>

                    <p>
                        Cuando utilices formularios de contacto, solicitudes
                        de presupuesto, herramientas de comunicación o el
                        sistema de chat, los datos proporcionados podrán ser
                        utilizados para responder a tu solicitud y gestionar
                        la relación correspondiente.
                    </p>


                    <h2>
                        7. Sistema de chat y herramientas de inteligencia
                        artificial
                    </h2>

                    <p>
                        Viziune puede incorporar funcionalidades basadas en
                        tecnologías de inteligencia artificial para facilitar
                        determinadas herramientas y servicios.
                    </p>

                    <p>
                        Los mensajes o datos introducidos voluntariamente por
                        el usuario en dichas funcionalidades podrán ser
                        tratados con la finalidad de generar respuestas,
                        prestar el servicio solicitado y mantener el correcto
                        funcionamiento de la plataforma.
                    </p>

                    <p>
                        Cuando resulte necesario utilizar proveedores
                        tecnológicos externos para prestar determinadas
                        funcionalidades, dichos proveedores podrán tratar
                        información siguiendo las instrucciones y condiciones
                        aplicables al servicio.
                    </p>


                    <h2>
                        8. Herramientas de análisis web y SEO
                    </h2>

                    <p>
                        Algunas herramientas de Viziune pueden permitir al
                        usuario introducir una dirección web para realizar
                        determinados análisis técnicos, de contenido o de
                        posicionamiento.
                    </p>

                    <p>
                        La información obtenida mediante estas herramientas
                        será utilizada para proporcionar el resultado
                        solicitado por el usuario y mejorar el funcionamiento
                        de los servicios.
                    </p>


                    <h2>
                        9. Conservación de los datos
                    </h2>

                    <p>
                        Los datos personales se conservarán durante el tiempo
                        necesario para cumplir la finalidad para la que fueron
                        recogidos y, posteriormente, durante los plazos
                        necesarios para atender posibles responsabilidades
                        legales.
                    </p>

                    <p>
                        Cuando los datos ya no sean necesarios, podrán ser
                        eliminados o anonimizados de acuerdo con los
                        procedimientos internos aplicables.
                    </p>


                    <h2>
                        10. Destinatarios de los datos
                    </h2>

                    <p>
                        Los datos personales no se comunicarán a terceros salvo
                        cuando exista una base jurídica que lo permita o resulte
                        necesario para prestar un servicio solicitado.
                    </p>

                    <p>
                        Determinados proveedores tecnológicos pueden actuar
                        como encargados del tratamiento para prestar servicios
                        relacionados con alojamiento, correo electrónico,
                        seguridad, infraestructura, análisis o herramientas
                        tecnológicas.
                    </p>


                    <h2>
                        11. Transferencias internacionales
                    </h2>

                    <p>
                        Algunos proveedores tecnológicos utilizados para el
                        funcionamiento de servicios digitales pueden estar
                        ubicados fuera del Espacio Económico Europeo.
                    </p>

                    <p>
                        Cuando se produzcan transferencias internacionales de
                        datos, se aplicarán las garantías y mecanismos
                        establecidos por la normativa de protección de datos
                        que resulte aplicable.
                    </p>


                    <h2>
                        12. Seguridad
                    </h2>

                    <p>
                        Viziune aplica medidas técnicas y organizativas
                        destinadas a proteger los datos personales frente a
                        accesos no autorizados, pérdida, alteración,
                        divulgación o destrucción.
                    </p>

                    <p>
                        No obstante, ningún sistema conectado a Internet puede
                        garantizar una seguridad absoluta.
                    </p>


                    <h2>
                        13. Derechos de los usuarios
                    </h2>

                    <p>
                        De acuerdo con la normativa aplicable, las personas
                        interesadas pueden ejercer determinados derechos sobre
                        sus datos personales.
                    </p>

                    <ul>

                        <li>
                            Derecho de acceso.
                        </li>

                        <li>
                            Derecho de rectificación.
                        </li>

                        <li>
                            Derecho de supresión.
                        </li>

                        <li>
                            Derecho a la limitación del tratamiento.
                        </li>

                        <li>
                            Derecho a la portabilidad de los datos, cuando
                            resulte aplicable.
                        </li>

                        <li>
                            Derecho de oposición.
                        </li>

                        <li>
                            Derecho a retirar el consentimiento cuando el
                            tratamiento se base en el consentimiento.
                        </li>

                    </ul>


                    <h2>
                        14. Cómo ejercer tus derechos
                    </h2>

                    <p>
                        Para ejercer tus derechos puedes ponerte en contacto
                        con Viziune mediante los canales oficiales de
                        contacto disponibles en el sitio web.
                    </p>

                    <p>
                        La solicitud deberá permitir identificar al solicitante
                        y especificar claramente el derecho que desea ejercer.
                    </p>


                    <h2>
                        15. Cookies y tecnologías similares
                    </h2>

                    <p>
                        Viziune puede utilizar cookies y tecnologías
                        similares necesarias para el funcionamiento del sitio
                        web, la gestión de sesiones, la seguridad y, cuando
                        corresponda, otras funcionalidades.
                    </p>

                    <p>
                        La información específica sobre las cookies utilizadas
                        y las opciones disponibles para el usuario podrá
                        desarrollarse en la correspondiente Política de
                        Cookies.
                    </p>


                    <h2>
                        16. Menores de edad
                    </h2>

                    <p>
                        Los servicios de Viziune están dirigidos a usuarios
                        que puedan utilizarlos de acuerdo con la legislación
                        aplicable.
                    </p>

                    <p>
                        Si se detecta que se han recopilado datos personales de
                        un menor sin la base jurídica o autorización necesaria,
                        se podrán adoptar las medidas correspondientes para
                        eliminar dicha información.
                    </p>


                    <h2>
                        17. Enlaces a terceros
                    </h2>

                    <p>
                        El sitio web puede contener enlaces hacia páginas o
                        servicios de terceros.
                    </p>

                    <p>
                        Viziune no es responsable de las políticas de
                        privacidad, contenidos o prácticas de dichos terceros.
                        Se recomienda revisar sus correspondientes políticas
                        antes de proporcionar información personal.
                    </p>


                    <h2>
                        18. Modificaciones de esta política
                    </h2>

                    <p>
                        Viziune podrá actualizar esta Política de Privacidad
                        cuando resulte necesario para adaptarla a cambios
                        legales, técnicos o relacionados con los servicios
                        ofrecidos.
                    </p>

                    <p>
                        Cuando se produzcan cambios relevantes, se podrá
                        informar a los usuarios mediante los mecanismos
                        disponibles en la plataforma.
                    </p>


                    <h2>
                        19. Contacto
                    </h2>

                    <div class="politica-contacto">

                        <p>
                            <strong>
                                Viziune SL
                            </strong>
                        </p>

                        <p>
                            Para cualquier cuestión relacionada con esta
                            Política de Privacidad o con el tratamiento de
                            datos personales, puedes utilizar los canales
                            oficiales de contacto de Viziune.
                        </p>

                    </div>


                    <h2>
                        20. Autoridad de protección de datos
                    </h2>

                    <p>
                        Si consideras que el tratamiento de tus datos personales
                        no se ajusta a la normativa aplicable, puedes presentar
                        una reclamación ante la autoridad de protección de
                        datos competente.
                    </p>


                </div>


                <footer class="politica-footer">

                    <p>
                        © <?php echo date('Y'); ?> Viziune.
                        Todos los derechos reservados.
                    </p>

                    <a
                        href="index.php"
                        class="politica-volver"
                    >
                        ← Cerrar política
                    </a>
                 

                </footer>


            </article>

        </div>

    </main>


</body>

</html>

