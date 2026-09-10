/* =========================================================
   VIZIUNEAI - JAVASCRIPT PRINCIPAL
========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    /* =====================================================
       ELEMENTOS DEL CHAT
    ===================================================== */

    const chatForm = document.getElementById("chatForm");
    const messageInput = document.getElementById("message");
    const chatMessages = document.getElementById("chatMessages");

    /* =====================================================
       ELEMENTOS DEL AGENTE
    ===================================================== */

    const agentButtons = document.querySelectorAll(".agent-button");

    const assistantName =
        document.getElementById("assistantName");

    const assistantDescription =
        document.getElementById("assistantDescription");

    const assistantAvatar =
        document.getElementById("assistantAvatar");

    /* =====================================================
       AGENTE SELECCIONADO
    ===================================================== */

    let selectedAgent = "diseño y desarrollo web";

    /* =====================================================
       INFORMACIÓN DE LOS AGENTES
    ===================================================== */

    const agentInfo = {

        "diseño y desarrollo web": {
            name:
                "Alejandro Herradón, tu Asesor en Diseño y Desarrollo Web",

            description:
                "● Diseño y desarrollo de páginas web profesionales, modernas y adaptadas a las necesidades de tu negocio.",

            avatar:
                "img/asset.png"
        },

        "tiendas online": {
            name:
                "Alejandro Herradón, tu Asesor de Tiendas Online",

            description:
                "● Creación y desarrollo de tiendas online para vender productos y servicios por Internet.",

            avatar:
                "img/asset.png"
        },

        "asesor seo y sem": {
            name:
                "Alejandro Herradón, tu Asesor SEO y SEM",

            description:
                "● Estrategias SEO y SEM para mejorar la visibilidad de tu negocio, atraer tráfico y conseguir clientes.",

            avatar:
                "img/asset.png"
        },

        "asesoramiento web": {
            name:
                "Alejandro Herradón, tu Asesor Web",

            description:
                "● Asesoramiento para mejorar, optimizar y hacer crecer la presencia online de tu negocio.",

            avatar:
                "img/asset.png"
        }

    };

    /* =====================================================
       NEURONAS
    ===================================================== */

    const canvas =
        document.getElementById("neural-canvas");

    if (canvas) {

        const ctx = canvas.getContext("2d");

        const CONFIG = {
            particleCount: 50,
            maxDistance: 130,
            nodeColor: "#00f3ff",
            lineColor: "0, 243, 255",
            speed: 0.5
        };

        let particles = [];

        function resizeCanvas() {

            const dpr =
                window.devicePixelRatio || 1;

            canvas.width =
                window.innerWidth * dpr;

            canvas.height =
                window.innerHeight * dpr;

            canvas.style.width =
                window.innerWidth + "px";

            canvas.style.height =
                window.innerHeight + "px";

            ctx.setTransform(
                dpr,
                0,
                0,
                dpr,
                0,
                0
            );

            init();

        }

        window.addEventListener(
            "resize",
            resizeCanvas
        );

        class Neuron {

            constructor() {

                this.x =
                    Math.random() *
                    window.innerWidth;

                this.y =
                    Math.random() *
                    window.innerHeight;

                this.vx =
                    (Math.random() - 0.5) *
                    CONFIG.speed;

                this.vy =
                    (Math.random() - 0.5) *
                    CONFIG.speed;

                this.radius =
                    Math.random() * 2 + 2;
            }

            update() {

                this.x += this.vx;
                this.y += this.vy;

                if (
                    this.x < 0 ||
                    this.x > window.innerWidth
                ) {

                    this.vx *= -1;

                }

                if (
                    this.y < 0 ||
                    this.y > window.innerHeight
                ) {

                    this.vy *= -1;

                }

            }

            draw() {

                ctx.beginPath();

                ctx.arc(
                    this.x,
                    this.y,
                    this.radius,
                    0,
                    Math.PI * 2
                );

                ctx.fillStyle =
                    CONFIG.nodeColor;

                ctx.shadowBlur = 8;

                ctx.shadowColor =
                    CONFIG.nodeColor;

                ctx.fill();

                ctx.shadowBlur = 0;

            }

        }

        function init() {

            particles = [];

            for (
                let i = 0;
                i < CONFIG.particleCount;
                i++
            ) {

                particles.push(
                    new Neuron()
                );

            }

        }

        function animate() {

            ctx.clearRect(
                0,
                0,
                window.innerWidth,
                window.innerHeight
            );

            particles.forEach(
                function (particle) {

                    particle.update();
                    particle.draw();

                }
            );

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

                    const dx =
                        particles[i].x -
                        particles[j].x;

                    const dy =
                        particles[i].y -
                        particles[j].y;

                    const distance =
                        Math.sqrt(
                            dx * dx +
                            dy * dy
                        );

                    if (
                        distance <
                        CONFIG.maxDistance
                    ) {

                        const opacity =
                            (
                                1 -
                                (
                                    distance /
                                    CONFIG.maxDistance
                                )
                            ) * 0.25;

                        ctx.beginPath();

                        ctx.moveTo(
                            particles[i].x,
                            particles[i].y
                        );

                        ctx.lineTo(
                            particles[j].x,
                            particles[j].y
                        );

                        ctx.strokeStyle =
                            `rgba(${CONFIG.lineColor}, ${opacity})`;

                        ctx.lineWidth = 1;

                        ctx.stroke();

                    }

                }

            }

            requestAnimationFrame(
                animate
            );

        }

        resizeCanvas();
        animate();

    }

    /* =====================================================
       NORMALIZAR AGENTE
    ===================================================== */

    function obtenerAgenteValido(agent) {

        if (!agent) {
            return null;
        }

        const texto =
            agent
                .toLowerCase()
                .trim()
                .normalize("NFD")
                .replace(
                    /[\u0300-\u036f]/g,
                    ""
                );

        if (
            texto === "diseno y desarrollo web" ||
            texto === "diseno y desarrollo"
        ) {

            return "diseño y desarrollo web";

        }

        if (
            texto === "tiendas online" ||
            texto === "tienda online"
        ) {

            return "tiendas online";

        }

        if (
            texto === "asesor seo y sem" ||
            texto === "seo y sem" ||
            texto === "seo sem" ||
            texto === "seo"
        ) {

            return "asesor seo y sem";

        }

        if (
            texto === "asesoramiento web" ||
            texto === "asesor web"
        ) {

            return "asesoramiento web";

        }

        return null;

    }

    /* =====================================================
       CAMBIAR IDENTIDAD DEL AGENTE
    ===================================================== */

    function cambiarIdentidad(agent) {

        const agenteValido =
            obtenerAgenteValido(agent);

        if (!agenteValido) {

            console.error(
                "Agente no encontrado:",
                agent
            );

            return;

        }

        selectedAgent =
            agenteValido;

        agentButtons.forEach(
            function (button) {

                button.classList.remove(
                    "active"
                );

                const botonAgent =
                    obtenerAgenteValido(
                        button.dataset.agent
                    );

                if (
                    botonAgent ===
                    agenteValido
                ) {

                    button.classList.add(
                        "active"
                    );

                }

            }
        );

        const info =
            agentInfo[agenteValido];

        if (!info) {
            return;
        }

        if (assistantName) {

            assistantName.textContent =
                info.name;

        }

        if (assistantDescription) {

            assistantDescription.textContent =
                info.description;

        }

        if (assistantAvatar) {

            assistantAvatar.src =
                info.avatar;

            assistantAvatar.alt =
                info.name;

        }

    }

    /* =====================================================
       BOTONES AGENTE
    ===================================================== */

    agentButtons.forEach(
        function (button) {

            button.addEventListener(
                "click",
                function (event) {

                    event.preventDefault();

                    cambiarIdentidad(
                        this.dataset.agent
                    );

                }
            );

        }
    );

    /* =====================================================
       EMAIL
    ===================================================== */

    const sendChatEmail =
        document.getElementById(
            "sendChatEmail"
        );

    const chatEmailForm =
        document.getElementById(
            "chatEmailForm"
        );

    const chatNombre =
        document.getElementById(
            "chatNombre"
        );

    const chatEmail =
        document.getElementById(
            "chatEmail"
        );

    const chatFile =
        document.getElementById(
            "chatFile"
        );

    const confirmSendChatEmail =
        document.getElementById(
            "confirmSendChatEmail"
        );

    /* =====================================================
       ELEMENTOS REUNIÓN
    ===================================================== */

    const chatReunion =
        document.getElementById(
            "chatReunion"
        );

    const reunionFields =
        document.getElementById(
            "reunionFields"
        );

    const chatFechaReunion =
        document.getElementById(
            "chatFechaReunion"
        );

    const chatHoraReunion =
        document.getElementById(
            "chatHoraReunion"
        );

    /* =====================================================
       MODAL REUNIÓN
    ===================================================== */

    let reunionModal = null;

    let reunionConfirmada = false;

    function crearModalReunion() {

        if (reunionModal) {
            return;
        }

        reunionModal =
            document.createElement("div");

        reunionModal.id =
            "reunionModal";

        reunionModal.className =
            "reunion-modal";

        reunionModal.innerHTML = `

            <div class="reunion-modal-overlay"></div>

            <div
                class="reunion-modal-content"
                role="dialog"
                aria-modal="true"
                aria-labelledby="reunionModalTitle"
            >

                <div class="reunion-modal-header">

                    <div>

                        <span class="reunion-modal-label">
                            REUNIÓN
                        </span>

                        <h2 id="reunionModalTitle">
                            Solicitar una reunión
                        </h2>

                    </div>

                    <button
                        type="button"
                        class="reunion-modal-close"
                        id="cerrarReunionModal"
                        aria-label="Cerrar"
                    >
                        ×
                    </button>

                </div>

                <div class="reunion-modal-body">

                    <p>
                        Selecciona el día y la hora
                        que prefieras para que podamos
                        contactar contigo.
                    </p>

                    <div class="form-group">

                        <label for="modalFechaReunion">
                            Fecha de la reunión
                        </label>

                        <input
                            type="date"
                            id="modalFechaReunion"
                        >

                    </div>

                    <div class="form-group">

                        <label for="modalHoraReunion">
                            Hora de la reunión
                        </label>

                        <input
                            type="time"
                            id="modalHoraReunion"
                        >

                    </div>

                </div>

                <div class="reunion-modal-actions">

                    <button
                        type="button"
                        id="cancelarReunion"
                        class="reunion-modal-cancel"
                    >
                        Cancelar
                    </button>

                    <button
                        type="button"
                        id="confirmarReunion"
                        class="reunion-modal-confirm"
                    >
                        ✓ Confirmar reunión
                    </button>

                </div>

            </div>
        `;

        document.body.appendChild(
            reunionModal
        );

        const modalFecha =
            document.getElementById(
                "modalFechaReunion"
            );

        const modalHora =
            document.getElementById(
                "modalHoraReunion"
            );

        /* =================================================
           FECHA MÍNIMA
        ================================================= */

        if (modalFecha) {

            const hoy =
                new Date();

            const anio =
                hoy.getFullYear();

            const mes =
                String(
                    hoy.getMonth() + 1
                ).padStart(
                    2,
                    "0"
                );

            const dia =
                String(
                    hoy.getDate()
                ).padStart(
                    2,
                    "0"
                );

            modalFecha.min =
                `${anio}-${mes}-${dia}`;

        }

        /* =================================================
           CERRAR MODAL
        ================================================= */

        const cerrar =
            function () {

                cerrarModalReunion();

            };

        const botonCerrar =
            document.getElementById(
                "cerrarReunionModal"
            );

        const botonCancelar =
            document.getElementById(
                "cancelarReunion"
            );

        const overlay =
            reunionModal.querySelector(
                ".reunion-modal-overlay"
            );

        if (botonCerrar) {

            botonCerrar.addEventListener(
                "click",
                cerrar
            );

        }

        if (botonCancelar) {

            botonCancelar.addEventListener(
                "click",
                cerrar
            );

        }

        if (overlay) {

            overlay.addEventListener(
                "click",
                cerrar
            );

        }

        /* =================================================
           CONFIRMAR REUNIÓN
        ================================================= */

        const confirmar =
            document.getElementById(
                "confirmarReunion"
            );

        if (confirmar) {

            confirmar.addEventListener(
                "click",
                function () {

                    const fecha =
                        modalFecha
                            ? modalFecha.value
                            : "";

                    const hora =
                        modalHora
                            ? modalHora.value
                            : "";

                    if (!fecha) {

                        alert(
                            "Por favor, selecciona el día de la reunión."
                        );

                        if (modalFecha) {
                            modalFecha.focus();
                        }

                        return;

                    }

                    if (!hora) {

                        alert(
                            "Por favor, selecciona la hora de la reunión."
                        );

                        if (modalHora) {
                            modalHora.focus();
                        }

                        return;

                    }

                    const fechaSeleccionada =
                        new Date(
                            `${fecha}T${hora}`
                        );

                    if (
                        isNaN(
                            fechaSeleccionada.getTime()
                        )
                    ) {

                        alert(
                            "La fecha u hora de la reunión no es válida."
                        );

                        return;

                    }

                    const ahora =
                        new Date();

                    if (
                        fechaSeleccionada <= ahora
                    ) {

                        alert(
                            "La fecha y hora deben ser posteriores a la hora actual."
                        );

                        return;

                    }

                    if (chatFechaReunion) {

                        chatFechaReunion.value =
                            fecha;

                    }

                    if (chatHoraReunion) {

                        chatHoraReunion.value =
                            hora;

                    }

                    reunionConfirmada =
                        true;

                    if (chatReunion) {

                        chatReunion.checked =
                            true;

                    }

                    cerrarModalReunion();

                    actualizarEstadoReunion();

                }
            );

        }

    }

    /* =====================================================
       ABRIR MODAL
    ===================================================== */

    function abrirModalReunion() {

        crearModalReunion();

        const modalFecha =
            document.getElementById(
                "modalFechaReunion"
            );

        const modalHora =
            document.getElementById(
                "modalHoraReunion"
            );

        if (
            modalFecha &&
            chatFechaReunion &&
            chatFechaReunion.value
        ) {

            modalFecha.value =
                chatFechaReunion.value;

        }

        if (
            modalHora &&
            chatHoraReunion &&
            chatHoraReunion.value
        ) {

            modalHora.value =
                chatHoraReunion.value;

        }

        reunionModal.classList.add(
            "active"
        );

        document.body.classList.add(
            "reunion-modal-open"
        );

        setTimeout(
            function () {

                if (modalFecha) {
                    modalFecha.focus();
                }

            },
            100
        );

    }

    /* =====================================================
       CERRAR MODAL
    ===================================================== */

    function cerrarModalReunion() {

        if (!reunionModal) {
            return;
        }

        reunionModal.classList.remove(
            "active"
        );

        document.body.classList.remove(
            "reunion-modal-open"
        );

        if (!reunionConfirmada) {

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

        }

    }

    /* =====================================================
       ACTUALIZAR ESTADO REUNIÓN
    ===================================================== */

    function actualizarEstadoReunion() {

        if (!chatReunion) {
            return;
        }

        if (chatReunion.checked) {

            if (reunionFields) {

                reunionFields.hidden =
                    false;

            }

            if (chatFechaReunion) {

                chatFechaReunion.disabled =
                    false;

                chatFechaReunion.required =
                    true;

            }

            if (chatHoraReunion) {

                chatHoraReunion.disabled =
                    false;

                chatHoraReunion.required =
                    true;

            }

        } else {

            if (reunionFields) {

                reunionFields.hidden =
                    true;

            }

            if (chatFechaReunion) {

                chatFechaReunion.disabled =
                    true;

                chatFechaReunion.required =
                    false;

                chatFechaReunion.value =
                    "";

            }

            if (chatHoraReunion) {

                chatHoraReunion.disabled =
                    true;

                chatHoraReunion.required =
                    false;

                chatHoraReunion.value =
                    "";

            }

        }

    }

    /* =====================================================
       CLICK SOLICITAR REUNIÓN
    ===================================================== */

    if (chatReunion) {

        chatReunion.addEventListener(
            "change",
            function () {

                if (chatReunion.checked) {

                    reunionConfirmada =
                        false;

                    abrirModalReunion();

                } else {

                    reunionConfirmada =
                        false;

                    actualizarEstadoReunion();

                }

            }
        );

    }

    /* =====================================================
       ESC PARA CERRAR MODAL
    ===================================================== */

    document.addEventListener(
        "keydown",
        function (event) {

            if (
                event.key === "Escape" &&
                reunionModal &&
                reunionModal.classList.contains("active")
            ) {

                cerrarModalReunion();

            }

        }
    );

    /* =====================================================
       OBTENER DATOS REUNIÓN
    ===================================================== */

    function obtenerDatosReunion() {

        const datos = {

            quiereCita:
                false,

            fechaCita:
                "",

            horaCita:
                ""

        };

        if (
            !chatReunion ||
            !chatReunion.checked
        ) {

            return datos;

        }

        datos.quiereCita =
            true;

        datos.fechaCita =
            chatFechaReunion
                ? chatFechaReunion.value
                : "";

        datos.horaCita =
            chatHoraReunion
                ? chatHoraReunion.value
                : "";

        if (!datos.fechaCita) {

            alert(
                "Por favor, selecciona el día de la reunión."
            );

            abrirModalReunion();

            return null;

        }

        if (!datos.horaCita) {

            alert(
                "Por favor, selecciona la hora de la reunión."
            );

            abrirModalReunion();

            return null;

        }

        const fechaSeleccionada =
            new Date(
                `${datos.fechaCita}T${datos.horaCita}`
            );

        if (
            isNaN(
                fechaSeleccionada.getTime()
            )
        ) {

            alert(
                "La fecha u hora de la reunión no es válida."
            );

            abrirModalReunion();

            return null;

        }

        if (
            fechaSeleccionada <= new Date()
        ) {

            alert(
                "La fecha y hora de la reunión deben ser posteriores a la hora actual."
            );

            abrirModalReunion();

            return null;

        }

        return datos;

    }

    /* =====================================================
       BOTÓN REINICIAR CHAT
    ===================================================== */

    const resetChat =
        document.getElementById(
            "resetChat"
        );

    if (resetChat) {

        resetChat.addEventListener(
            "click",
            function (event) {

                event.preventDefault();

                if (reunionModal) {

                    reunionModal.classList.remove(
                        "active"
                    );

                }

                document.body.classList.remove(
                    "reunion-modal-open"
                );

                reunionConfirmada =
                    false;

                if (chatMessages) {

                    chatMessages.innerHTML =
                        "";

                }

                if (messageInput) {

                    messageInput.value =
                        "";

                }

                if (sendChatEmail) {

                    sendChatEmail.hidden =
                        true;

                    sendChatEmail.disabled =
                        false;

                    sendChatEmail.innerText =
                        "Enviar conversación por correo";

                }

                if (chatEmailForm) {

                    chatEmailForm.hidden =
                        true;

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

                actualizarEstadoReunion();

                cambiarIdentidad(
                    "diseño y desarrollo web"
                );

                if (messageInput) {

                    messageInput.focus();

                }

            }
        );

    }

    /* =====================================================
       AVATAR MENSAJES
    ===================================================== */

    function ponerAvatar(
        avatar,
        type
    ) {

        if (type === "bot") {

            const img =
                document.createElement(
                    "img"
                );

            img.src =
                "img/asset.png";

            img.alt =
                "Asistente";

            avatar.appendChild(
                img
            );

        } else {

            avatar.textContent =
                "👤";

        }

    }

    /* =====================================================
       AÑADIR MENSAJE
    ===================================================== */

    function addMessage(
        text,
        type,
        temporary = false
    ) {

        if (!chatMessages) {

            return null;

        }

        const messageElement =
            document.createElement(
                "div"
            );

        messageElement.classList.add(
            "message",
            type
        );

        if (temporary) {

            messageElement.classList.add(
                "temporary"
            );

        }

        const avatar =
            document.createElement(
                "div"
            );

        avatar.classList.add(
            "avatar"
        );

        ponerAvatar(
            avatar,
            type
        );

        const bubble =
            document.createElement(
                "div"
            );

        bubble.classList.add(
            "bubble"
        );

        bubble.innerText =
            text;

        messageElement.appendChild(
            avatar
        );

        messageElement.appendChild(
            bubble
        );

        chatMessages.appendChild(
            messageElement
        );

        chatMessages.scrollTop =
            chatMessages.scrollHeight;

        return messageElement;

    }

    /* =====================================================
       ENVIAR MENSAJE A CHAT.PHP
    ===================================================== */

    if (
        chatForm &&
        messageInput &&
        chatMessages
    ) {

        chatForm.addEventListener(
            "submit",
            async function (event) {

                event.preventDefault();

                const message =
                    messageInput.value.trim();

                if (!message) {
                    return;
                }

                addMessage(
                    message,
                    "user"
                );

                messageInput.value =
                    "";

                comprobarSolicitudContacto(
                    message
                );

                const typing =
                    addMessage(
                        "Escribiendo...",
                        "bot",
                        true
                    );

                messageInput.disabled =
                    true;

                const submitButton =
                    chatForm.querySelector(
                        "button[type='submit']"
                    );

                if (submitButton) {

                    submitButton.disabled =
                        true;

                }

                try {

                    console.log(
                        "Enviando mensaje a chat.php..."
                    );

                    console.log(
                        "Agente:",
                        selectedAgent
                    );

                    const response =
                        await fetch(
                            "chat.php",
                            {
                                method:
                                    "POST",

                                headers: {
                                    "Content-Type":
                                        "application/json",

                                    "Accept":
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

                    const responseText =
                        await response.text();

                    console.log(
                        "Respuesta de chat.php:",
                        responseText
                    );

                    if (
                        !responseText.trim()
                    ) {

                        throw new Error(
                            "chat.php no ha devuelto ninguna respuesta."
                        );

                    }

                    let data;

                    try {

                        data =
                            JSON.parse(
                                responseText
                            );

                    } catch (jsonError) {

                        console.error(
                            "Respuesta no válida:",
                            responseText
                        );

                        throw new Error(
                            "chat.php ha devuelto una respuesta que no es JSON. Revisa los errores de PHP."
                        );

                    }

                    if (typing) {
                        typing.remove();
                    }

                    if (
                        !response.ok ||
                        !data.success
                    ) {

                        throw new Error(
                            data.error ||
                            "Error desconocido del servidor."
                        );

                    }

                    if (!data.answer) {

                        throw new Error(
                            "El servidor no ha devuelto ninguna respuesta del asistente."
                        );

                    }

                    addMessage(
                        data.answer,
                        "bot"
                    );

                } catch (error) {

                    if (typing) {
                        typing.remove();
                    }

                    console.error(
                        "ERROR COMPLETO DEL CHAT:",
                        error
                    );

                    addMessage(
                        "❌ Error: " +
                        (
                            error.message ||
                            "No se pudo conectar con el servidor."
                        ),
                        "bot"
                    );

                } finally {

                    messageInput.disabled =
                        false;

                    if (submitButton) {

                        submitButton.disabled =
                            false;

                    }

                    messageInput.focus();

                }

            }
        );

    }

    /* =====================================================
       DETECTAR SOLICITUD DE CONTACTO
    ===================================================== */

    function comprobarSolicitudContacto(
        message
    ) {

        if (!sendChatEmail) {
            return;
        }

        const texto =
            message
                .toLowerCase()
                .normalize("NFD")
                .replace(
                    /[\u0300-\u036f]/g,
                    ""
                );

        const palabrasContacto = [

            "quiero contactar",
            "quiero contacto",
            "contactar",
            "contacto",
            "quiero hablar",
            "hablar con alguien",
            "hablar con la empresa",
            "hablar con vosotros",
            "hablar con ustedes",
            "quiero contratar",
            "quiero contrataros",
            "contratar",
            "quiero presupuesto",
            "necesito presupuesto",
            "presupuesto",
            "precio",
            "precios",
            "informacion",
            "mas informacion",
            "quiero informacion",
            "me interesa",
            "estoy interesado",
            "estoy interesada",
            "quiero saber mas",
            "necesito ayuda",
            "hablar con una persona"

        ];

        const quiereContactar =
            palabrasContacto.some(
                function (palabra) {

                    return texto.includes(
                        palabra
                    );

                }
            );

        if (quiereContactar) {

            sendChatEmail.hidden =
                false;

        }

    }

    /* =====================================================
       OBTENER CONVERSACIÓN
    ===================================================== */

    function obtenerConversacion() {

        if (!chatMessages) {
            return "";
        }

        const mensajes =
            chatMessages.querySelectorAll(
                ".message"
            );

        let conversacion =
            "";

        mensajes.forEach(
            function (mensaje) {

                if (
                    mensaje.classList.contains(
                        "temporary"
                    )
                ) {

                    return;

                }

                const bubble =
                    mensaje.querySelector(
                        ".bubble"
                    );

                if (!bubble) {
                    return;
                }

                const texto =
                    bubble.innerText.trim();

                if (!texto) {
                    return;
                }

                if (
                    mensaje.classList.contains(
                        "bot"
                    )
                ) {

                    conversacion +=
                        "ASISTENTE:\n" +
                        texto +
                        "\n\n";

                } else {

                    conversacion +=
                        "CLIENTE:\n" +
                        texto +
                        "\n\n";

                }

            }
        );

        return conversacion.trim();

    }

    /* =====================================================
       BOTÓN ENVIAR CONVERSACIÓN
    ===================================================== */

    if (sendChatEmail) {

        sendChatEmail.addEventListener(
            "click",
            function (event) {

                event.preventDefault();

                const conversacion =
                    obtenerConversacion();

                if (!conversacion) {

                    alert(
                        "No hay ninguna conversación para enviar."
                    );

                    return;

                }

                if (chatEmailForm) {

                    chatEmailForm.hidden =
                        false;

                    if (chatNombre) {

                        chatNombre.focus();

                    }

                    chatEmailForm.scrollIntoView({
                        behavior:
                            "smooth",

                        block:
                            "nearest"
                    });

                }

            }
        );

    }

    /* =====================================================
       ENVIAR CONVERSACIÓN POR EMAIL
    ===================================================== */

    async function confirmarEnvioConversacion() {

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
                "Por favor, introduce tu nombre antes de enviar la conversación."
            );

            if (chatNombre) {
                chatNombre.focus();
            }

            return;

        }

        if (!email) {

            alert(
                "Por favor, introduce tu correo electrónico antes de enviar la conversación."
            );

            if (chatEmail) {
                chatEmail.focus();
            }

            return;

        }

        const emailValido =
            /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!emailValido.test(email)) {

            alert(
                "Por favor, introduce un correo electrónico válido."
            );

            if (chatEmail) {
                chatEmail.focus();
            }

            return;

        }

        const datosReunion =
            obtenerDatosReunion();

        if (datosReunion === null) {
            return;
        }

        if (confirmSendChatEmail) {

            confirmSendChatEmail.disabled =
                true;

            confirmSendChatEmail.innerText =
                "📧 Enviando...";

        }

        try {

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

            formData.append(
                "quiereCita",
                datosReunion.quiereCita
                    ? "1"
                    : "0"
            );

            formData.append(
                "fecha_reunion",
                datosReunion.fechaCita
            );

            formData.append(
                "hora_reunion",
                datosReunion.horaCita
            );

            if (
                chatFile &&
                chatFile.files &&
                chatFile.files.length > 0
            ) {

                formData.append(
                    "chatFile",
                    chatFile.files[0]
                );

            }

            const response =
                await fetch(
                    "chat.php",
                    {
                        method:
                            "POST",

                        body:
                            formData
                    }
                );

            const responseText =
                await response.text();

            console.log(
                "Respuesta email:",
                responseText
            );

            let data;

            try {

                data =
                    JSON.parse(
                        responseText
                    );

            } catch (error) {

                console.error(
                    "Respuesta no JSON:",
                    responseText
                );

                throw new Error(
                    "El servidor no ha devuelto una respuesta JSON válida."
                );

            }

            if (
                !response.ok ||
                !data.success
            ) {

                throw new Error(
                    data.error ||
                    "No se pudo enviar la conversación."
                );

            }

            let mensajeFinal =
                "✅ Conversación enviada correctamente.\n\n" +
                "Hemos recibido tus datos y la conversación.";

            if (
                datosReunion.quiereCita &&
                datosReunion.fechaCita &&
                datosReunion.horaCita
            ) {

                mensajeFinal +=
                    "\n\n📅 Reunión solicitada:\n" +
                    datosReunion.fechaCita +
                    " a las " +
                    datosReunion.horaCita;

            }

            alert(
                mensajeFinal
            );

            if (chatEmailForm) {

                chatEmailForm.hidden =
                    true;

            }

            if (sendChatEmail) {

                sendChatEmail.innerText =
                    "✓ Conversación enviada";

                sendChatEmail.disabled =
                    true;

            }

        } catch (error) {

            console.error(
                "Error al enviar conversación:",
                error
            );

            alert(
                "❌ " +
                (
                    error.message ||
                    "No se pudo enviar la conversación."
                )
            );

        } finally {

            if (confirmSendChatEmail) {

                confirmSendChatEmail.disabled =
                    false;

                confirmSendChatEmail.innerText =
                    "📧 Enviar conversación";

            }

        }

    }

    /* =====================================================
       BOTÓN CONFIRMAR EMAIL
    ===================================================== */

    if (confirmSendChatEmail) {

        confirmSendChatEmail.addEventListener(
            "click",
            function (event) {

                event.preventDefault();

                confirmarEnvioConversacion();

            }
        );

    }

    /* =====================================================
       FORMULARIO DE CONTACTO
    ===================================================== */

    const contactForm =
        document.getElementById(
            "contactForm"
        );

    const formResult =
        document.getElementById(
            "formResult"
        );

    if (contactForm) {

        contactForm.addEventListener(
            "submit",
            async function (event) {

                event.preventDefault();

                const formData =
                    new FormData(
                        contactForm
                    );

                const button =
                    contactForm.querySelector(
                        "button[type='submit']"
                    );

                if (button) {

                    button.disabled =
                        true;

                    button.innerText =
                        "Enviando...";

                }

                if (formResult) {

                    formResult.innerHTML =
                        "";

                }

                try {

                    const response =
                        await fetch(
                            "contacto.php",
                            {
                                method:
                                    "POST",

                                body:
                                    formData
                            }
                        );

                    const responseText =
                        await response.text();

                    console.log(
                        "Respuesta contacto:",
                        responseText
                    );

                    let data;

                    try {

                        data =
                            JSON.parse(
                                responseText
                            );

                    } catch (error) {

                        throw new Error(
                            "El servidor no ha devuelto una respuesta válida."
                        );

                    }

                    if (!response.ok) {

                        throw new Error(
                            data.error ||
                            "Error HTTP " +
                            response.status
                        );

                    }

                    if (!data.success) {

                        if (formResult) {

                            formResult.innerHTML =
                                '<p class="error">' +
                                (
                                    data.error ||
                                    "No se pudieron guardar los datos."
                                ) +
                                "</p>";

                        }

                        return;

                    }

                    if (formResult) {

                        formResult.innerHTML =
                            '<p class="success">' +
                            "✅ Datos guardados correctamente. " +
                            "Abriendo WhatsApp..." +
                            "</p>";

                    }

                    if (data.whatsapp) {

                        window.open(
                            data.whatsapp,
                            "_blank"
                        );

                    }

                    contactForm.reset();

                } catch (error) {

                    console.error(
                        "Error del formulario:",
                        error
                    );

                    if (formResult) {

                        formResult.innerHTML =
                            '<p class="error">' +
                            "❌ " +
                            (
                                error.message ||
                                "Ha ocurrido un error al conectar con el servidor."
                            ) +
                            "</p>";

                    }

                } finally {

                    if (button) {

                        button.disabled =
                            false;

                        button.innerText =
                            "💬 Contactar por WhatsApp";

                    }

                }

            }
        );

    }

    /* =====================================================
       AGENTE INICIAL
    ===================================================== */

    cambiarIdentidad(
        "diseño y desarrollo web"
    );

    /* =====================================================
       ESTADO INICIAL REUNIÓN
    ===================================================== */

    actualizarEstadoReunion();

    /* =====================================================
       COMPROBACIÓN
    ===================================================== */

    console.log(
        "ViziuneAI JavaScript cargado correctamente."
    );

    console.log(
        "Sistema preparado para utilizar Kimi mediante chat.php."
    );

    console.log(
        "Sistema de reuniones preparado con ventana emergente."
    );

    console.log(
        "Agente inicial:",
        selectedAgent
    );

});