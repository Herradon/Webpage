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
   FORMULARIO DE DATOS DEL CHAT
========================================== */

const chatEmailForm =
    document.getElementById("chatEmailForm");

const chatNombre =
    document.getElementById("chatNombre");

const chatEmail =
    document.getElementById("chatEmail");

const confirmSendChatEmail =
    document.getElementById("confirmSendChatEmail");


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

                if (typing) {

                    typing.remove();

                }


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

            chatMessages.innerHTML =
                "";


            messageInput.value =
                "";


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
            palabra =>
                texto.includes(palabra)
        );


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


/* ==========================================
   BOTÓN "ENVIAR CONVERSACIÓN"
========================================== */

if (sendChatEmail) {

    sendChatEmail.addEventListener(

        "click",

        function () {

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

                    behavior: "smooth",
                    block: "nearest"

                });

            } else {

                alert(
                    "No se ha encontrado el formulario de datos del chat."
                );

            }

        }

    );

}


/* ==========================================
   ENVIAR CONVERSACIÓN DEFINITIVAMENTE
========================================== */

async function confirmarEnvioConversacion() {

    /* ==============================
       OBTENER CONVERSACIÓN
    ============================== */

    const conversacion =
        obtenerConversacion();


    if (!conversacion) {

        alert(
            "No hay ninguna conversación para enviar."
        );

        return;

    }


    /* ==============================
       OBTENER NOMBRE
    ============================== */

    const nombre =
        chatNombre
            ? chatNombre.value.trim()
            : "";


    /* ==============================
       OBTENER EMAIL
    ============================== */

    const email =
        chatEmail
            ? chatEmail.value.trim()
            : "";


    /* ==============================
       VALIDAR NOMBRE
    ============================== */

    if (!nombre) {

        alert(
            "Por favor, introduce tu nombre antes de enviar la conversación."
        );


        if (chatNombre) {

            chatNombre.focus();

        }


        return;

    }


    /* ==============================
       VALIDAR EMAIL
    ============================== */

    if (!email) {

        alert(
            "Por favor, introduce tu correo electrónico antes de enviar la conversación."
        );


        if (chatEmail) {

            chatEmail.focus();

        }


        return;

    }


    /* ==============================
       VALIDAR FORMATO EMAIL
    ============================== */

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


    /* ==============================
       DESACTIVAR BOTÓN
    ============================== */

    if (confirmSendChatEmail) {

        confirmSendChatEmail.disabled =
            true;

        confirmSendChatEmail.innerText =
            "📧 Enviando...";

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

                            action:
                                "email",

                            nombre:
                                nombre,

                            email:
                                email,

                            conversacion:
                                conversacion

                        })

                }

            );


        /* ==============================
           LEER RESPUESTA
        ============================== */

        const data =
            await response.json();


        /* ==============================
           COMPROBAR ERROR
        ============================== */

        if (!response.ok || !data.success) {

            throw new Error(

                data.error ||
                "No se pudo enviar la conversación."

            );

        }


        /* ==============================
           ÉXITO
        ============================== */

        alert(

            "✅ Conversación enviada correctamente.\n\n" +

            "Hemos recibido tus datos y la conversación."

        );


        /* ==============================
           OCULTAR FORMULARIO
        ============================== */

        if (chatEmailForm) {

            chatEmailForm.hidden =
                true;

        }


        /* ==============================
           CAMBIAR BOTÓN
        ============================== */

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


/* ==========================================
   EVENTO CONFIRMAR ENVÍO
========================================== */

if (confirmSendChatEmail) {

    confirmSendChatEmail.addEventListener(

        "click",

        confirmarEnvioConversacion

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

                            method: "POST",
                            body: formData

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

                            `<p class="error">
                                ${
                                    data.error ||
                                    "No se pudieron guardar los datos."
                                }
                            </p>`;

                    }


                    return;

                }


                if (formResult) {

                    formResult.innerHTML =

                        `<p class="success">
                            ✅ Datos guardados correctamente.
                            Abriendo WhatsApp...
                        </p>`;

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

                        `<p class="error">
                            ❌ Ha ocurrido un error al conectar con el servidor.
                        </p>`;

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