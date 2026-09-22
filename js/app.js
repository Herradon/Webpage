/*
|--------------------------------------------------------------------------
| VIZIUNEAI - JAVASCRIPT PRINCIPAL
|--------------------------------------------------------------------------
*/

document.addEventListener("DOMContentLoaded", () => {


    /*
    |--------------------------------------------------------------------------
    | SESIÓN
    |--------------------------------------------------------------------------
    */

    const usuarioLogueado =
        window.usuarioLogueado === true;



    /*
    |--------------------------------------------------------------------------
    | ELEMENTOS
    |--------------------------------------------------------------------------
    */

    const chatForm =
        document.getElementById("chatForm");

    const messageInput =
        document.getElementById("message");

    const chatMessages =
        document.getElementById("chatMessages");

    const assistantName =
        document.getElementById("assistantName");

    const resetChat =
        document.getElementById("resetChat");

    const sendChatEmail =
        document.getElementById("sendChatEmail");

    const chatEmailForm =
        document.getElementById("chatEmailForm");

    const chatNombre =
        document.getElementById("chatNombre");

    const chatEmail =
        document.getElementById("chatEmail");

    const chatFile =
        document.getElementById("chatFile");

    const confirmSendChatEmail =
        document.getElementById("confirmSendChatEmail");

    const chatReunion =
        document.getElementById("chatReunion");

    const reunionModal =
        document.getElementById("reunionModal");

    const reunionModalOverlay =
        document.getElementById("reunionModalOverlay");

    const closeReunionModal =
        document.getElementById("closeReunionModal");

    const confirmReunion =
        document.getElementById("confirmReunion");

    const calendarPrev =
        document.getElementById("calendarPrev");

    const calendarNext =
        document.getElementById("calendarNext");

    const calendarMonth =
        document.getElementById("calendarMonth");

    const calendarDays =
        document.getElementById("calendarDays");

    const chatFechaReunion =
        document.getElementById("chatFechaReunion");

    const selectedDate =
        document.getElementById("selectedDate");

    const chatHoraReunion =
        document.getElementById("chatHoraReunion");

    const contactForm =
        document.getElementById("contactForm");

    const formResult =
        document.getElementById("formResult");



    /*
    |--------------------------------------------------------------------------
    | AGENTES
    |--------------------------------------------------------------------------
    */

    const agentes = {

        "diseño y desarrollo web": {

            nombre:
                "Diseño y Desarrollo Web",

            descripcion:
                "● Diseño y desarrollo de páginas web profesionales, modernas y adaptadas a las necesidades de tu negocio.",

            avatar:
                "img/asset.png"

        },


        "tiendas online": {

            nombre:
                "Tiendas Online",

            descripcion:
                "● Creación y desarrollo de tiendas online para vender productos y servicios por Internet.",

            avatar:
                "img/asset.png"

        },


        "asesor seo y sem": {

            nombre:
                "SEO y SEM",

            descripcion:
                "● Estrategias SEO y SEM para mejorar la visibilidad de tu negocio, atraer tráfico y conseguir clientes.",

            avatar:
                "img/asset.png"

        },


        "asesoramiento web": {

            nombre:
                "Asesor Web",

            descripcion:
                "● Asesoramiento para mejorar, optimizar y hacer crecer la presencia online de tu negocio.",

            avatar:
                "img/asset.png"

        }

    };


    let selectedAgent =
        "diseño y desarrollo web";
    

    
    /*
    |--------------------------------------------------------------------------
    | NEURONAS
    |--------------------------------------------------------------------------
    */
    
    /* ==========================================
   RED NEURONAL - NEURAL CANVAS
========================================== */

document.addEventListener("DOMContentLoaded", function () {

    const canvas = document.getElementById("neural-canvas");

    if (!canvas) {
        console.error("❌ No se encontró #neural-canvas");
        return;
    }

    console.log("✅ #neural-canvas encontrado");

    const ctx = canvas.getContext("2d");

    if (!ctx) {
        console.error("❌ No se pudo obtener el contexto del canvas");
        return;
    }

    let width = 0;
    let height = 0;

    const particleCount = 50;
    const maxDistance = 130;

    const nodeColor = "#00f3ff";
    const lineColor = "0, 243, 255";

    const speed = 0.5;

    let particles = [];


    /* ==========================================
       AJUSTAR CANVAS
    ========================================== */

    function resizeCanvas() {

        const rect = canvas.getBoundingClientRect();

        width = rect.width;
        height = rect.height;

        console.log(
            "Canvas:",
            width,
            "x",
            height
        );

        if (width <= 0 || height <= 0) {
            console.warn(
                "⚠️ El canvas tiene ancho o alto 0"
            );
            return;
        }

        const dpr = window.devicePixelRatio || 1;

        canvas.width = width * dpr;
        canvas.height = height * dpr;

        ctx.setTransform(
            dpr,
            0,
            0,
            dpr,
            0,
            0
        );

        crearParticulas();
    }


    /* ==========================================
       CREAR PARTÍCULAS
    ========================================== */

    function crearParticulas() {

        particles = [];

        for (let i = 0; i < particleCount; i++) {

            particles.push({

                x: Math.random() * width,

                y: Math.random() * height,

                vx:
                    (Math.random() - 0.5)
                    * speed,

                vy:
                    (Math.random() - 0.5)
                    * speed,

                radius:
                    Math.random() * 2 + 2

            });

        }
    }


    /* ==========================================
       ACTUALIZAR PARTÍCULAS
    ========================================== */

    function actualizarParticulas() {

        particles.forEach(function (particle) {

            particle.x += particle.vx;
            particle.y += particle.vy;


            if (
                particle.x <= 0 ||
                particle.x >= width
            ) {

                particle.vx *= -1;

            }


            if (
                particle.y <= 0 ||
                particle.y >= height
            ) {

                particle.vy *= -1;

            }

        });
    }


    /* ==========================================
       DIBUJAR CONEXIONES
    ========================================== */

    function dibujarConexiones() {

        for (
            let i = 0;
            i < particles.length;
            i++
        ) {

            for (
                let j = i + 1;
                j < particles.length;
                j++
            ) {

                const p1 = particles[i];
                const p2 = particles[j];

                const dx =
                    p1.x - p2.x;

                const dy =
                    p1.y - p2.y;

                const distance =
                    Math.sqrt(
                        dx * dx +
                        dy * dy
                    );


                if (
                    distance <
                    maxDistance
                ) {

                    const opacity =
                        1 -
                        (
                            distance /
                            maxDistance
                        );


                    ctx.beginPath();

                    ctx.moveTo(
                        p1.x,
                        p1.y
                    );

                    ctx.lineTo(
                        p2.x,
                        p2.y
                    );

                    ctx.strokeStyle =
                        `rgba(${lineColor}, ${opacity * 0.45})`;

                    ctx.lineWidth = 1;

                    ctx.stroke();
                }
            }
        }
    }


    /* ==========================================
       DIBUJAR NEURONAS
    ========================================== */

    function dibujarParticulas() {

        particles.forEach(function (particle) {

            ctx.beginPath();

            ctx.arc(
                particle.x,
                particle.y,
                particle.radius,
                0,
                Math.PI * 2
            );

            ctx.fillStyle = nodeColor;

            ctx.shadowBlur = 8;

            ctx.shadowColor = nodeColor;

            ctx.fill();

        });

        ctx.shadowBlur = 0;
    }


    /* ==========================================
       ANIMACIÓN
    ========================================== */

    function animar() {

        ctx.clearRect(
            0,
            0,
            width,
            height
        );

        actualizarParticulas();

        dibujarConexiones();

        dibujarParticulas();

        requestAnimationFrame(animar);
    }


    /* ==========================================
       INICIAR
    ========================================== */

    resizeCanvas();

    window.addEventListener(
        "resize",
        resizeCanvas
    );

    animar();

});
    /*
    |--------------------------------------------------------------------------
    | NORMALIZAR TEXTO
    |--------------------------------------------------------------------------
    */

    function normalizarTexto(texto) {

        return String(texto || "")
            .toLowerCase()
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "")
            .trim();

    }



    /*
    |--------------------------------------------------------------------------
    | AGENTE VÁLIDO
    |--------------------------------------------------------------------------
    */

    function obtenerAgenteValido(agent) {

        const normalizado =
            normalizarTexto(agent);


        const aliases = {

            "diseno y desarrollo":
                "diseño y desarrollo web",

            "diseno y desarrollo web":
                "diseño y desarrollo web",

            "tienda online":
                "tiendas online",

            "tiendas online":
                "tiendas online",

            "seo y sem":
                "asesor seo y sem",

            "seo sem":
                "asesor seo y sem",

            "seo":
                "asesor seo y sem",

            "asesor seo y sem":
                "asesor seo y sem",

            "asesor web":
                "asesoramiento web",

            "asesoramiento web":
                "asesoramiento web"

        };


        return aliases[normalizado] ||
            "diseño y desarrollo web";

    }



    /*
    |--------------------------------------------------------------------------
    | ACTUALIZAR AGENTE
    |--------------------------------------------------------------------------
    */

    function actualizarAgente() {

        const agente =
            agentes[selectedAgent];


        if (!agente) {
            return;
        }


        if (assistantName) {

            assistantName.textContent =
                agente.nombre;

        }


        const avatar =
            document.querySelector(
                ".assistant-avatar img"
            );


        if (avatar) {

            avatar.src =
                agente.avatar;

        }


        document
            .querySelectorAll(".agent-button")
            .forEach(button => {

                const buttonAgent =
                    obtenerAgenteValido(
                        button.dataset.agent
                    );


                button.classList.toggle(
                    "active",
                    buttonAgent === selectedAgent
                );

            });

    }



    /*
    |--------------------------------------------------------------------------
    | BOTONES AGENTES
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll(".agent-button")
        .forEach(button => {

            button.addEventListener(
                "click",
                () => {

                    selectedAgent =
                        obtenerAgenteValido(
                            button.dataset.agent
                        );


                    actualizarAgente();

                }
            );

        });



    /*
    |--------------------------------------------------------------------------
    | AÑADIR MENSAJE
    |--------------------------------------------------------------------------
    */

    function añadirMensaje(
        texto,
        tipo = "assistant",
        temporal = false
    ) {

        if (!chatMessages) {
            return null;
        }


        const bubble =
            document.createElement("div");


        bubble.classList.add(
            "message",
            tipo === "user"
                ? "user-message"
                : "assistant-message"
        );


        if (temporal) {

            bubble.classList.add(
                "temporary-message"
            );

        }


        bubble.textContent =
            texto;


        chatMessages.appendChild(
            bubble
        );


        chatMessages.scrollTop =
            chatMessages.scrollHeight;


        return bubble;

    }



    /*
    |--------------------------------------------------------------------------
    | OBTENER CONVERSACIÓN
    |--------------------------------------------------------------------------
    */

    function obtenerConversacion() {

        if (!chatMessages) {
            return "";
        }


        const mensajes =
            chatMessages.querySelectorAll(
                ".message:not(.temporary-message)"
            );


        const conversacion = [];


        mensajes.forEach(mensaje => {

            const esUsuario =
                mensaje.classList.contains(
                    "user-message"
                );


            conversacion.push(

                (
                    esUsuario
                        ? "CLIENTE: "
                        : "ASISTENTE: "
                )

                +

                mensaje.textContent.trim()

            );

        });


        return conversacion.join(
            "\n\n"
        );

    }



    /*
    |--------------------------------------------------------------------------
    | PALABRAS QUE HACEN APARECER EL BOTÓN
    |--------------------------------------------------------------------------
    |
    | IMPORTANTE:
    |
    | Estas palabras SOLO funcionan si el usuario está logueado.
    |--------------------------------------------------------------------------
    */

    const palabrasContacto = [

        "contacto",
        "contactar",
        "quiero contactar",
        "quiero hablar",
        "hablar con vosotros",
        "hablar con ustedes",
        "presupuesto",
        "pedir un presupuesto",
        "quiero pedir un presupuesto",
        "precio",
        "precios",
        "informacion",
        "me interesa",
        "necesito ayuda",
        "quiero contratar"

    ];



    /*
    |--------------------------------------------------------------------------
    | COMPROBAR SOLICITUD DE CONTACTO
    |--------------------------------------------------------------------------
    */

    function comprobarSolicitudContacto(texto) {

        /*
        |--------------------------------------------------------------------------
        | SI NO ESTÁ LOGUEADO
        |--------------------------------------------------------------------------
        |
        | El botón ni siquiera existe en el HTML.
        |--------------------------------------------------------------------------
        */

        if (!usuarioLogueado) {

            return;

        }


        /*
        |--------------------------------------------------------------------------
        | SI ESTÁ LOGUEADO PERO NO EXISTE EL BOTÓN
        |--------------------------------------------------------------------------
        */

        if (!sendChatEmail) {

            return;

        }


        const textoNormalizado =
            normalizarTexto(texto);


        const solicitaContacto =
            palabrasContacto.some(
                palabra =>
                    textoNormalizado.includes(
                        normalizarTexto(palabra)
                    )
            );


        /*
        |--------------------------------------------------------------------------
        | MOSTRAR / OCULTAR
        |--------------------------------------------------------------------------
        */

        if (solicitaContacto) {

            sendChatEmail.hidden =
                false;

        } else {

            /*
            | Si el mensaje no contiene una palabra configurada,
            | no hacemos aparecer el botón.
            */

            sendChatEmail.hidden =
                true;

        }

    }



    /*
    |--------------------------------------------------------------------------
    | CHAT
    |--------------------------------------------------------------------------
    */

    if (chatForm) {

        chatForm.addEventListener(
            "submit",
            async event => {

                event.preventDefault();


                const message =
                    messageInput
                        ? messageInput.value.trim()
                        : "";


                if (!message) {
                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | AÑADIR MENSAJE DEL CLIENTE
                |--------------------------------------------------------------------------
                */

                añadirMensaje(
                    message,
                    "user"
                );


                /*
                |--------------------------------------------------------------------------
                | COMPROBAR SI DEBE APARECER EL BOTÓN
                |--------------------------------------------------------------------------
                */

                comprobarSolicitudContacto(
                    message
                );


                if (messageInput) {

                    messageInput.value =
                        "";

                }


                const typingBubble =
                    añadirMensaje(
                        "Escribiendo...",
                        "assistant",
                        true
                    );


                try {

                    const response =
                        await fetch(
                            "chat.php",
                            {

                                method: "POST",

                                headers: {

                                    "Content-Type":
                                        "application/json"

                                },

                                body:
                                    JSON.stringify({

                                        message:
                                            message,

                                        agent:
                                            selectedAgent

                                    })

                            }
                        );


                    const textoRespuesta =
                        await response.text();


                    let data;


                    try {

                        data =
                            JSON.parse(
                                textoRespuesta
                            );

                    } catch (error) {

                        throw new Error(
                            "La respuesta del servidor no es válida."
                        );

                    }


                    if (
                        typingBubble &&
                        typingBubble.parentNode
                    ) {

                        typingBubble.remove();

                    }


                    if (
                        !data.success ||
                        !data.answer
                    ) {

                        throw new Error(
                            data.error ||
                            "No se ha recibido una respuesta válida."
                        );

                    }


                    añadirMensaje(
                        data.answer,
                        "assistant"
                    );


                } catch (error) {

                    if (
                        typingBubble &&
                        typingBubble.parentNode
                    ) {

                        typingBubble.remove();

                    }


                    añadirMensaje(
                        "❌ Error: " +
                        error.message,
                        "assistant"
                    );

                }

            }
        );

    }



    /*
    |--------------------------------------------------------------------------
    | BOTÓN ENVIAR CONVERSACIÓN
    |--------------------------------------------------------------------------
    */

    if (sendChatEmail) {

        sendChatEmail.addEventListener(
            "click",
            () => {


                if (!usuarioLogueado) {
                    return;
                }


                const conversacion =
                    obtenerConversacion();


                if (!conversacion) {

                    alert(
                        "Primero debes mantener una conversación con nuestro asistente."
                    );

                    return;

                }


                if (chatEmailForm) {

                    chatEmailForm.hidden =
                        false;

                }


                if (chatNombre) {

                    chatNombre.focus();

                }

            }
        );

    }



    /*
    |--------------------------------------------------------------------------
    | VALIDAR FECHA/HORA DE REUNIÓN
    |--------------------------------------------------------------------------
    */

    function validarFechaHoraReunion() {

        if (
            !chatReunion ||
            !chatReunion.checked
        ) {

            return true;

        }


        const fecha =
            chatFechaReunion
                ? chatFechaReunion.value
                : "";


        const hora =
            chatHoraReunion
                ? chatHoraReunion.value
                : "";


        if (!fecha) {

            alert(
                "Selecciona una fecha para la reunión."
            );

            return false;

        }


        if (!hora) {

            alert(
                "Selecciona una hora para la reunión."
            );

            return false;

        }


        const fechaHora =
            new Date(
                `${fecha}T${hora}`
            );


        if (
            isNaN(
                fechaHora.getTime()
            )
        ) {

            alert(
                "La fecha o la hora de la reunión no son válidas."
            );

            return false;

        }


        if (
            fechaHora <= new Date()
        ) {

            alert(
                "La reunión debe programarse en una fecha y hora futuras."
            );

            return false;

        }


        return true;

    }



    /*
    |--------------------------------------------------------------------------
    | ENVIAR CONVERSACIÓN
    |--------------------------------------------------------------------------
    */

    async function confirmarEnvioConversacion() {


        if (!usuarioLogueado) {
            return;
        }


        const conversacion =
            obtenerConversacion();


        if (!conversacion) {

            alert(
                "No hay ninguna conversación para enviar."
            );

            return;

        }


        const nombre =
            chatNombre
                ? chatNombre.value.trim()
                : "";


        const email =
            chatEmail
                ? chatEmail.value.trim()
                : "";


        if (!nombre) {

            alert(
                "Introduce tu nombre."
            );


            if (chatNombre) {
                chatNombre.focus();
            }


            return;

        }


        const emailValido =
            /^[^\s@]+@[^\s@]+\.[^\s@]+$/;


        if (
            !emailValido.test(email)
        ) {

            alert(
                "Introduce un correo electrónico válido."
            );


            if (chatEmail) {
                chatEmail.focus();
            }


            return;

        }


        if (
            !validarFechaHoraReunion()
        ) {

            return;

        }


        const formData =
            new FormData();


        formData.append(
            "action",
            "email"
        );


        formData.append(
            "nombre",
            nombre
        );


        formData.append(
            "email",
            email
        );


        formData.append(
            "conversacion",
            conversacion
        );


        formData.append(
            "agent",
            selectedAgent
        );


        const quiereCita =
            chatReunion &&
            chatReunion.checked;


        formData.append(
            "quiereCita",
            quiereCita
                ? "1"
                : "0"
        );


        formData.append(
            "fecha_reunion",
            chatFechaReunion
                ? chatFechaReunion.value
                : ""
        );


        formData.append(
            "hora_reunion",
            chatHoraReunion
                ? chatHoraReunion.value
                : ""
        );


        if (
            chatFile &&
            chatFile.files &&
            chatFile.files.length > 0
        ) {

            formData.append(
                "archivo",
                chatFile.files[0]
            );

        }


        if (confirmSendChatEmail) {

            confirmSendChatEmail.disabled =
                true;

            confirmSendChatEmail.textContent =
                "📧 Enviando...";

        }


        try {

            const response =
                await fetch(
                    "chat.php",
                    {

                        method: "POST",

                        body:
                            formData

                    }
                );


            const textoRespuesta =
                await response.text();


            let data;


            try {

                data =
                    JSON.parse(
                        textoRespuesta
                    );

            } catch (error) {

                throw new Error(
                    "La respuesta del servidor no es válida."
                );

            }


            if (!data.success) {

                throw new Error(
                    data.error ||
                    "No se pudo enviar la conversación."
                );

            }


            let mensaje =
                "✅ Conversación enviada correctamente.";


            if (quiereCita) {

                mensaje +=
                    "\n\n📅 Solicitud de reunión: " +
                    chatFechaReunion.value +
                    " a las " +
                    chatHoraReunion.value;

            }


            alert(
                mensaje
            );


            if (chatEmailForm) {

                chatEmailForm.hidden =
                    true;

            }


            if (sendChatEmail) {

                sendChatEmail.textContent =
                    "✓ Conversación enviada";

                sendChatEmail.disabled =
                    true;

            }


        } catch (error) {

            alert(
                "❌ Error al enviar la conversación: " +
                error.message
            );


            if (confirmSendChatEmail) {

                confirmSendChatEmail.disabled =
                    false;

                confirmSendChatEmail.textContent =
                    "Enviar conversación";

            }

        }

    }



    if (confirmSendChatEmail) {

        confirmSendChatEmail.addEventListener(
            "click",
            confirmarEnvioConversacion
        );

    }



    /*
    |--------------------------------------------------------------------------
    | MODAL REUNIÓN
    |--------------------------------------------------------------------------
    */

    let fechaCalendario =
        new Date();



    function abrirModalReunion() {

        if (!usuarioLogueado) {

            if (chatReunion) {

                chatReunion.checked =
                    false;

            }


            window.location.href =
                "login.php";


            return;

        }


        if (!reunionModal) {
            return;
        }


        fechaCalendario =
            new Date();


        renderizarCalendario();


        reunionModal.classList.add(
            "active"
        );


        reunionModal.setAttribute(
            "aria-hidden",
            "false"
        );

    }



    function cerrarModalReunion() {

        if (!reunionModal) {
            return;
        }


        reunionModal.classList.remove(
            "active"
        );


        reunionModal.setAttribute(
            "aria-hidden",
            "true"
        );

    }



    if (chatReunion) {

        chatReunion.addEventListener(
            "change",
            () => {

                if (
                    chatReunion.checked
                ) {

                    abrirModalReunion();

                } else {

                    cerrarModalReunion();

                }

            }
        );

    }



    if (closeReunionModal) {

        closeReunionModal.addEventListener(
            "click",
            () => {

                if (chatReunion) {

                    chatReunion.checked =
                        false;

                }


                cerrarModalReunion();

            }
        );

    }



    if (reunionModalOverlay) {

        reunionModalOverlay.addEventListener(
            "click",
            () => {

                if (chatReunion) {

                    chatReunion.checked =
                        false;

                }


                cerrarModalReunion();

            }
        );

    }



    /*
    |--------------------------------------------------------------------------
    | CALENDARIO
    |--------------------------------------------------------------------------
    */

    function renderizarCalendario() {

        if (
            !calendarDays ||
            !calendarMonth
        ) {

            return;

        }


        const year =
            fechaCalendario.getFullYear();


        const month =
            fechaCalendario.getMonth();


        const nombresMeses = [

            "Enero",
            "Febrero",
            "Marzo",
            "Abril",
            "Mayo",
            "Junio",
            "Julio",
            "Agosto",
            "Septiembre",
            "Octubre",
            "Noviembre",
            "Diciembre"

        ];


        calendarMonth.textContent =
            `${nombresMeses[month]} ${year}`;


        calendarDays.innerHTML =
            "";


        const primerDia =
            new Date(
                year,
                month,
                1
            );


        let diaSemana =
            primerDia.getDay();


        diaSemana =
            diaSemana === 0
                ? 6
                : diaSemana - 1;


        const diasMes =
            new Date(
                year,
                month + 1,
                0
            ).getDate();


        for (
            let i = 0;
            i < diaSemana;
            i++
        ) {

            const espacio =
                document.createElement(
                    "span"
                );


            espacio.className =
                "calendar-empty";


            calendarDays.appendChild(
                espacio
            );

        }


        const hoy =
            new Date();


        hoy.setHours(
            0,
            0,
            0,
            0
        );


        for (
            let dia = 1;
            dia <= diasMes;
            dia++
        ) {

            const boton =
                document.createElement(
                    "button"
                );


            boton.type =
                "button";


            boton.className =
                "calendar-day";


            boton.textContent =
                dia;


            const fecha =
                new Date(
                    year,
                    month,
                    dia
                );


            fecha.setHours(
                0,
                0,
                0,
                0
            );


            if (
                fecha < hoy
            ) {

                boton.disabled =
                    true;


                boton.classList.add(
                    "disabled"
                );

            } else {

                boton.addEventListener(
                    "click",
                    () => {

                        seleccionarFecha(
                            fecha
                        );

                    }
                );

            }


            if (
                chatFechaReunion &&
                chatFechaReunion.value
            ) {

                const seleccionada =
                    new Date(
                        chatFechaReunion.value +
                        "T00:00:00"
                    );


                if (

                    seleccionada.getFullYear()
                    === year &&

                    seleccionada.getMonth()
                    === month &&

                    seleccionada.getDate()
                    === dia

                ) {

                    boton.classList.add(
                        "selected"
                    );

                }

            }


            calendarDays.appendChild(
                boton
            );

        }

    }



    function seleccionarFecha(
        fecha
    ) {

        const year =
            fecha.getFullYear();


        const month =
            String(
                fecha.getMonth() + 1
            ).padStart(
                2,
                "0"
            );


        const day =
            String(
                fecha.getDate()
            ).padStart(
                2,
                "0"
            );


        const valor =
            `${year}-${month}-${day}`;


        if (chatFechaReunion) {

            chatFechaReunion.value =
                valor;

        }


        if (selectedDate) {

            selectedDate.textContent =
                `Fecha seleccionada: ${day}/${month}/${year}`;

        }


        renderizarCalendario();

    }



    if (calendarPrev) {

        calendarPrev.addEventListener(
            "click",
            () => {

                fechaCalendario.setMonth(
                    fechaCalendario.getMonth() - 1
                );


                renderizarCalendario();

            }
        );

    }



    if (calendarNext) {

        calendarNext.addEventListener(
            "click",
            () => {

                fechaCalendario.setMonth(
                    fechaCalendario.getMonth() + 1
                );


                renderizarCalendario();

            }
        );

    }



    if (confirmReunion) {

        confirmReunion.addEventListener(
            "click",
            () => {

                if (
                    !validarFechaHoraReunion()
                ) {

                    return;

                }


                cerrarModalReunion();

            }
        );

    }



    /*
    |--------------------------------------------------------------------------
    | CONTACTO WHATSAPP
    |--------------------------------------------------------------------------
    */

    if (contactForm) {

        contactForm.addEventListener(
            "submit",
            async event => {

                event.preventDefault();


                const formData =
                    new FormData(
                        contactForm
                    );


                if (formResult) {

                    formResult.textContent =
                        "Enviando...";

                }


                try {

                    const response =
                        await fetch(
                            "contacto.php",
                            {

                                method: "POST",

                                body:
                                    formData

                            }
                        );


                    const data =
                        await response.json();


                    if (!data.success) {

                        throw new Error(
                            data.error ||
                            "No se pudo enviar el formulario."
                        );

                    }


                    if (formResult) {

                        formResult.textContent =
                            "✅ Datos enviados correctamente.";

                    }


                    if (data.whatsapp) {

                        window.open(
                            data.whatsapp,
                            "_blank"
                        );

                    }


                    contactForm.reset();


                } catch (error) {

                    if (formResult) {

                        formResult.textContent =
                            "❌ Error: " +
                            error.message;

                    }

                }

            }
        );

    }



    /*
    |--------------------------------------------------------------------------
    | RESET
    |--------------------------------------------------------------------------
    */

    if (resetChat) {

        resetChat.addEventListener(
            "click",
            () => {


                if (chatMessages) {

                    chatMessages.innerHTML =
                        "";

                }


                if (messageInput) {

                    messageInput.value =
                        "";

                }


                if (chatNombre) {

                    chatNombre.value =
                        "";

                }


                if (chatEmail) {

                    chatEmail.value =
                        "";

                }


                if (chatFile) {

                    chatFile.value =
                        "";

                }


                if (chatReunion) {

                    chatReunion.checked =
                        false;

                }


                if (chatFechaReunion) {

                    chatFechaReunion.value =
                        "";

                }


                if (chatHoraReunion) {

                    chatHoraReunion.value =
                        "";

                }


                if (selectedDate) {

                    selectedDate.textContent =
                        "Selecciona una fecha";

                }


                cerrarModalReunion();


                /*
                |--------------------------------------------------------------------------
                | EL BOTÓN VUELVE A OCULTARSE
                |--------------------------------------------------------------------------
                */

                if (sendChatEmail) {

                    sendChatEmail.hidden =
                        true;

                    sendChatEmail.disabled =
                        false;

                    sendChatEmail.textContent =
                        "Enviar conversación por correo";

                }


                /*
                |--------------------------------------------------------------------------
                | OCULTAR FORMULARIO
                |--------------------------------------------------------------------------
                */

                if (chatEmailForm) {

                    chatEmailForm.hidden =
                        true;

                }


                selectedAgent =
                    "diseño y desarrollo web";


                actualizarAgente();

            }
        );

    }



    /*
    |--------------------------------------------------------------------------
    | INICIALIZACIÓN
    |--------------------------------------------------------------------------
    */

    actualizarAgente();


    /*
    |--------------------------------------------------------------------------
    | EL BOTÓN SIEMPRE COMIENZA OCULTO
    |--------------------------------------------------------------------------
    |
    | Esto se ejecuta aunque el usuario esté logueado.
    | Después, comprobarSolicitudContacto() será quien lo muestre.
    |--------------------------------------------------------------------------
    */

    if (sendChatEmail) {

        sendChatEmail.hidden =
            true;

    }


    if (chatEmailForm) {

        chatEmailForm.hidden =
            true;

    }


});