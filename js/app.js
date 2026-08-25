/* ==========================================
   CHATBOT
========================================== */

const chatForm =
    document.getElementById("chatForm");

const messageInput =
    document.getElementById("message");

const chatMessages =
    document.getElementById("chatMessages");


/* ==========================================
   BOTÓN ENVIAR CONVERSACIÓN POR EMAIL
========================================== */

const sendChatEmail =
    document.getElementById("sendChatEmail");


/* ==========================================
   BOTÓN REINICIAR CHAT
========================================== */

const resetChat =
    document.getElementById("resetChat");


/* ==========================================
   AVATAR DEL ASISTENTE
========================================== */

function ponerAvatar(avatar, type) {

    if (type === "bot") {

        const img =
            document.createElement("img");

        img.src =
            "img/asset.png";

        img.alt =
            "Alejandro Herradón";

        avatar.appendChild(img);

    } else {

        avatar.textContent =
            "👤";

    }

}


/* ==========================================
   ENVIAR MENSAJE AL CHAT
========================================== */

if (chatForm) {

    chatForm.addEventListener(
        "submit",
        async function (event) {

            event.preventDefault();


            /* ==============================
               OBTENER MENSAJE
            ============================== */

            const message =
                messageInput.value.trim();


            if (!message) {
                return;
            }


            /* ==============================
               MOSTRAR MENSAJE DEL CLIENTE
            ============================== */

            addMessage(
                message,
                "user"
            );


            /* ==============================
               LIMPIAR INPUT
            ============================== */

            messageInput.value = "";


            /* ==============================
               COMPROBAR SI QUIERE CONTACTAR
            ============================== */

            comprobarSolicitudContacto(
                message
            );


            /* ==============================
               MOSTRAR "ESCRIBIENDO..."
            ============================== */

            const typing =
                addMessage(
                    "Escribiendo...",
                    "bot",
                    true
                );


            /* ==============================
               DESACTIVAR INPUT
            ============================== */

            messageInput.disabled = true;


            const submitButton =
                chatForm.querySelector(
                    "button[type='submit']"
                );


            if (submitButton) {

                submitButton.disabled =
                    true;

            }


            try {

                /* ==============================
                   ENVIAR A CHAT.PHP
                ============================== */

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
                                        message
                                })
                        }
                    );


                /* ==============================
                   COMPROBAR RESPUESTA HTTP
                ============================== */

                if (!response.ok) {

                    throw new Error(
                        "Error HTTP " +
                        response.status
                    );

                }


                /* ==============================
                   CONVERTIR A JSON
                ============================== */

                const data =
                    await response.json();


                /* ==============================
                   QUITAR ESCRIBIENDO
                ============================== */

                if (typing) {
                    typing.remove();
                }


                /* ==============================
                   ERROR DEL SERVIDOR
                ============================== */

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


                /* ==============================
                   RESPUESTA DEL ASISTENTE
                ============================== */

                addMessage(
                    data.answer,
                    "bot"
                );


            } catch (error) {

                /* ==============================
                   QUITAR ESCRIBIENDO
                ============================== */

                if (typing) {
                    typing.remove();
                }


                /* ==============================
                   MOSTRAR ERROR
                ============================== */

                addMessage(

                    "No se ha podido conectar con el servidor. " +
                    "Comprueba que XAMPP y Apache estén funcionando.",

                    "bot"

                );


                console.error(
                    "Error del chatbot:",
                    error
                );


            } finally {

                /* ==============================
                   REACTIVAR CHAT
                ============================== */

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


/* ==========================================
   AÑADIR MENSAJE AL CHAT
========================================== */

function addMessage(
    text,
    type,
    temporary = false
) {

    const messageElement =
        document.createElement("div");


    messageElement.classList.add(
        "message",
        type
    );


    /* ======================================
       AVATAR
    ====================================== */

    const avatar =
        document.createElement("div");


    avatar.classList.add(
        "avatar"
    );


    ponerAvatar(
        avatar,
        type
    );


    /* ======================================
       BURBUJA
    ====================================== */

    const bubble =
        document.createElement("div");


    bubble.classList.add(
        "bubble"
    );


    bubble.innerText =
        text;


    /* ======================================
       AÑADIR AL MENSAJE
    ====================================== */

    messageElement.appendChild(
        avatar
    );


    messageElement.appendChild(
        bubble
    );


    /* ======================================
       AÑADIR AL CHAT
    ====================================== */

    chatMessages.appendChild(
        messageElement
    );


    /* ======================================
       SCROLL AUTOMÁTICO
    ====================================== */

    chatMessages.scrollTop =
        chatMessages.scrollHeight;


    return messageElement;

}


/* ==========================================
   REINICIAR SOLO EL CHAT
========================================== */

if (resetChat) {

    resetChat.addEventListener(
        "click",
        function () {

            /* ==============================
               BORRAR CONVERSACIÓN VISUAL
            ============================== */

            chatMessages.innerHTML =
                "";


            /* ==============================
               LIMPIAR INPUT
            ============================== */

            messageInput.value =
                "";


            /* ==============================
               OCULTAR BOTÓN EMAIL
            ============================== */

            if (sendChatEmail) {

                sendChatEmail.hidden =
                    true;

                sendChatEmail.disabled =
                    false;

                sendChatEmail.innerText =
                    "📧 Enviar conversación por correo";

            }


            /* ==============================
               VOLVER AL INPUT
            ============================== */

            messageInput.focus();

        }
    );

}


/* ==========================================
   DETECTAR INTENCIÓN DE CONTACTO
========================================== */

function comprobarSolicitudContacto(
    message
) {

    /* ==============================
       NORMALIZAR TEXTO
    ============================== */

    const texto =
        message
            .toLowerCase()
            .normalize("NFD")
            .replace(
                /[\u0300-\u036f]/g,
                ""
            );


    /* ==============================
       PALABRAS DE CONTACTO
    ============================== */

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


    /* ==============================
       COMPROBAR
    ============================== */

    const quiereContactar =
        palabrasContacto.some(
            palabra =>
                texto.includes(palabra)
        );


    /* ==============================
       MOSTRAR BOTÓN EMAIL
    ============================== */

    if (
        quiereContactar &&
        sendChatEmail
    ) {

        sendChatEmail.hidden =
            false;

    }

}


/* ==========================================
   OBTENER TODA LA CONVERSACIÓN
========================================== */

function obtenerConversacion() {

    const mensajes =
        chatMessages.querySelectorAll(
            ".message"
        );


    let conversacion =
        "";


    mensajes.forEach(
        function (mensaje) {

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


            /* ==============================
               ASISTENTE
            ============================== */

            if (
                mensaje.classList.contains(
                    "bot"
                )
            ) {

                conversacion +=
                    "ASISTENTE:\n" +
                    texto +
                    "\n\n";

            }


            /* ==============================
               CLIENTE
            ============================== */

            else {

                conversacion +=
                    "CLIENTE:\n" +
                    texto +
                    "\n\n";

            }

        }
    );


    return conversacion.trim();

}


/* ==========================================
   ENVIAR CONVERSACIÓN POR EMAIL
========================================== */

async function enviarChatEmail() {

    if (!sendChatEmail) {
        return;
    }


    /* ==============================
       OBTENER CONVERSACIÓN
    ============================== */

    const conversacion =
        obtenerConversacion();


    /* ==============================
       COMPROBAR CONVERSACIÓN
    ============================== */

    if (!conversacion) {

        alert(
            "No hay ninguna conversación para enviar."
        );

        return;

    }


    /* ==============================
       DESACTIVAR BOTÓN
    ============================== */

    sendChatEmail.disabled =
        true;

    sendChatEmail.innerText =
        "📧 Enviando conversación...";


    try {

        /* ==============================
           CREAR FORM DATA
        ============================== */

        const formData =
            new FormData();


        formData.append(
            "conversacion",
            conversacion
        );


        /* ==============================
           ENVIAR A CORREO.PHP
        ============================== */

        const response =
            await fetch(
                "correo.php",
                {
                    method: "POST",
                    body: formData
                }
            );


        /* ==============================
           COMPROBAR HTTP
        ============================== */

        if (!response.ok) {

            throw new Error(
                "Error HTTP " +
                response.status
            );

        }


        /* ==============================
           LEER JSON
        ============================== */

        const data =
            await response.json();


        /* ==============================
           COMPROBAR ERROR
        ============================== */

        if (!data.success) {

            alert(
                data.error ||
                "No se pudo enviar la conversación por correo."
            );


            sendChatEmail.disabled =
                false;


            sendChatEmail.innerText =
                "📧 Enviar conversación por correo";


            return;

        }


        /* ==============================
           ÉXITO
        ============================== */

        alert(
            "✅ La conversación se ha enviado correctamente al correo."
        );


        sendChatEmail.innerText =
            "✓ Conversación enviada";


        sendChatEmail.disabled =
            true;


    } catch (error) {

        console.error(
            "Error al enviar el correo:",
            error
        );


        alert(
            "❌ No se pudo enviar el correo.\n\n" +
            "Comprueba la configuración de Gmail y la contraseña de aplicación."
        );


        sendChatEmail.disabled =
            false;


        sendChatEmail.innerText =
            "📧 Enviar conversación por correo";

    }

}


/* ==========================================
   EVENTO BOTÓN EMAIL
========================================== */

if (sendChatEmail) {

    sendChatEmail.addEventListener(
        "click",
        enviarChatEmail
    );

}


/* ==========================================
   FORMULARIO DE CONTACTO
========================================== */

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


            /* ==============================
               DATOS
            ============================== */

            const formData =
                new FormData(
                    contactForm
                );


            /* ==============================
               BOTÓN
            ============================== */

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

                /* ==============================
                   ENVIAR A CONTACTO.PHP
                ============================== */

                const response =
                    await fetch(
                        "contacto.php",
                        {
                            method: "POST",
                            body: formData
                        }
                    );


                /* ==============================
                   COMPROBAR HTTP
                ============================== */

                if (!response.ok) {

                    throw new Error(
                        "Error HTTP " +
                        response.status
                    );

                }


                /* ==============================
                   LEER JSON
                ============================== */

                const data =
                    await response.json();


                /* ==============================
                   ERROR
                ============================== */

                if (!data.success) {

                    if (formResult) {

                        formResult.innerHTML =
                            `<p class="error">
                                ${
                                    data.error ||
                                    "No se pudieron guardar los datos."
                                }
                            </p>`;

                    }

                    return;

                }


                /* ==============================
                   ÉXITO
                ============================== */

                if (formResult) {

                    formResult.innerHTML =
                        `<p class="success">
                            ✅ Datos guardados correctamente.
                            Abriendo WhatsApp...
                        </p>`;

                }


                /* ==============================
                   ABRIR WHATSAPP
                ============================== */

                if (data.whatsapp) {

                    window.open(
                        data.whatsapp,
                        "_blank"
                    );

                }


                /* ==============================
                   LIMPIAR FORMULARIO
                ============================== */

                contactForm.reset();


            } catch (error) {

                console.error(
                    "Error del formulario:",
                    error
                );


                if (formResult) {

                    formResult.innerHTML =
                        `<p class="error">
                            ❌ Ha ocurrido un error al conectar con el servidor.
                        </p>`;

                }

            } finally {

                /* ==============================
                   REACTIVAR BOTÓN
                ============================== */

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