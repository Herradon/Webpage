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
       NORMALIZAR AGENTE
    ===================================================== */

    function obtenerAgenteValido(agent) {

        if (!agent) {
            return null;
        }

        const texto = agent
            .toLowerCase()
            .trim()
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "");


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


        /* Guardar agente */

        selectedAgent =
            agenteValido;


        /* =================================================
           BOTÓN ACTIVO
        ================================================= */

        agentButtons.forEach(function (button) {

            button.classList.remove("active");

            const botonAgent =
                obtenerAgenteValido(
                    button.dataset.agent
                );


            if (botonAgent === agenteValido) {

                button.classList.add("active");

            }

        });


        /* =================================================
           CAMBIAR NOMBRE
        ================================================= */

        if (assistantName) {

            assistantName.textContent =
                agentInfo[agenteValido].name;

        }


        /* =================================================
           CAMBIAR DESCRIPCIÓN
        ================================================= */

        if (assistantDescription) {

            assistantDescription.textContent =
                agentInfo[agenteValido].description;

        }


        /* =================================================
           CAMBIAR AVATAR
        ================================================= */

        if (assistantAvatar) {

            assistantAvatar.src =
                agentInfo[agenteValido].avatar;

            assistantAvatar.alt =
                agentInfo[agenteValido].name;

        }


        console.log(
            "Agente seleccionado:",
            selectedAgent
        );

    }


    /* =====================================================
       BOTONES DE AGENTE
    ===================================================== */

    agentButtons.forEach(function (button) {

        button.addEventListener("click", function (event) {

            event.preventDefault();

            const agent =
                this.dataset.agent;

            console.log(
                "Botón pulsado:",
                agent
            );

            cambiarIdentidad(agent);

        });

    });


    /* =====================================================
       BOTÓN EMAIL
    ===================================================== */

    const sendChatEmail =
        document.getElementById("sendChatEmail");


    /* =====================================================
       FORMULARIO EMAIL DEL CHAT
    ===================================================== */

    const chatEmailForm =
        document.getElementById("chatEmailForm");

    const chatNombre =
        document.getElementById("chatNombre");

    const chatEmail =
        document.getElementById("chatEmail");

    const confirmSendChatEmail =
        document.getElementById(
            "confirmSendChatEmail"
        );


    /* =====================================================
       BOTÓN REINICIAR
    ===================================================== */

    const resetChat =
        document.getElementById("resetChat");


    /* =====================================================
       AVATAR DE LOS MENSAJES
    ===================================================== */

    function ponerAvatar(avatar, type) {

        if (type === "bot") {

            const img =
                document.createElement("img");

            img.src =
                "img/asset.png";

            img.alt =
                "Asistente";

            avatar.appendChild(img);

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
            document.createElement("div");


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
            document.createElement("div");


        avatar.classList.add(
            "avatar"
        );


        ponerAvatar(
            avatar,
            type
        );


        const bubble =
            document.createElement("div");


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
       ENVIAR MENSAJE AL CHAT
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


                /* Mensaje usuario */

                addMessage(
                    message,
                    "user"
                );


                messageInput.value =
                    "";


                /* Detectar contacto */

                comprobarSolicitudContacto(
                    message
                );


                /* Escribiendo */

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
                        "Enviando agente:",
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


                    if (!response.ok) {

                        throw new Error(
                            "Error HTTP " +
                            response.status
                        );

                    }


                    const data =
                        await response.json();


                    if (typing) {

                        typing.remove();

                    }


                    if (!data.success) {

                        addMessage(

                            "Lo siento, ha ocurrido un error: " +

                            (
                                data.error ||
                                "Error desconocido."
                            ),

                            "bot"

                        );

                        return;
                    }


                    /* Respuesta */

                    addMessage(
                        data.answer,
                        "bot"
                    );


                } catch (error) {

                    if (typing) {
                        typing.remove();
                    }


                    console.error(
                        "Error del chatbot:",
                        error
                    );


                    addMessage(

                        "No se ha podido conectar con el servidor. " +
                        "Comprueba que XAMPP y Apache estén funcionando.",

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
       REINICIAR CHAT
    ===================================================== */

    if (resetChat) {

        resetChat.addEventListener(
            "click",
            function (event) {

                event.preventDefault();


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
                        "📧 Enviar conversación por correo";

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


                /* Volver al primer agente */

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
       DETECTAR SOLICITUD DE CONTACTO
    ===================================================== */

    function comprobarSolicitudContacto(message) {

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

                /* Ignorar "Escribiendo..." */

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


        /* Nombre */

        if (!nombre) {

            alert(
                "Por favor, introduce tu nombre antes de enviar la conversación."
            );


            if (chatNombre) {
                chatNombre.focus();
            }


            return;

        }


        /* Email */

        if (!email) {

            alert(
                "Por favor, introduce tu correo electrónico antes de enviar la conversación."
            );


            if (chatEmail) {
                chatEmail.focus();
            }


            return;

        }


        /* =================================================
           CORREGIDO: REGEX DEL EMAIL
        ================================================= */

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


        if (confirmSendChatEmail) {

            confirmSendChatEmail.disabled =
                true;

            confirmSendChatEmail.innerText =
                "📧 Enviando...";

        }


        try {

            const response =
                await fetch(
                    "chat.php",
                    {

                        method:
                            "POST",

                        headers: {

                            "Content-Type":
                                "application/json"

                        },

                        body:
                            JSON.stringify({

                                action:
                                    "email",

                                nombre:
                                    nombre,

                                email:
                                    email,

                                conversacion:
                                    conversacion,

                                agent:
                                    selectedAgent

                            })

                    }
                );


            const data =
                await response.json();


            if (
                !response.ok ||
                !data.success
            ) {

                throw new Error(
                    data.error ||
                    "No se pudo enviar la conversación."
                );

            }


            alert(
                "✅ Conversación enviada correctamente.\n\n" +
                "Hemos recibido tus datos y la conversación."
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


                    if (!response.ok) {

                        throw new Error(
                            "Error HTTP " +
                            response.status
                        );

                    }


                    const data =
                        await response.json();


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


                    /* =================================================
                       CORREGIDO: window.open
                    ================================================= */

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
                            "❌ Ha ocurrido un error al conectar con el servidor." +
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
       ACTIVAR AGENTE INICIAL
    ===================================================== */

    cambiarIdentidad(
        "diseño y desarrollo web"
    );


    /* =====================================================
       COMPROBACIÓN
    ===================================================== */

    console.log(
        "ViziuneAI JavaScript cargado correctamente."
    );

    console.log(
        "Agente inicial:",
        selectedAgent
    );

});