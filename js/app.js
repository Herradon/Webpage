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
   BOTÓN WHATSAPP
========================================== */

const sendChatWhatsApp =
    document.getElementById("sendChatWhatsApp");


/* ==========================================
   BOTÓN REINICIAR
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

        img.src = "img/asset.png";

        img.alt = "Alejandro Herradón";

        avatar.appendChild(img);

    } else {

        avatar.textContent = "👤";

    }

}


/* ==========================================
   ENVIAR MENSAJE AL CHAT
========================================== */

chatForm.addEventListener(
    "submit",
    async function (event) {

        event.preventDefault();


        const message =
            messageInput.value.trim();


        if (!message) {
            return;
        }


        /* ==============================
           MENSAJE DEL USUARIO
        ============================== */

        addMessage(
            message,
            "user"
        );


        /* Limpiar input */

        messageInput.value = "";


        /* ==============================
           COMPROBAR SI QUIERE CONTACTAR
        ============================== */

        comprobarSolicitudContacto(message);


        /* ==============================
           MOSTRAR "ESCRIBIENDO..."
        ============================== */

        const typing =
            addMessage(
                "Escribiendo...",
                "bot",
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

                                message: message

                            })

                    }
                );


            const data =
                await response.json();


            /* Eliminar escribiendo */

            typing.remove();


            /* ==============================
               ERROR
            ============================== */

            if (!data.success) {

                addMessage(

                    "Lo siento, ha ocurrido un error: " +
                    data.error,

                    "bot"

                );

                return;

            }


            /* ==============================
               RESPUESTA DEL BOT
            ============================== */

            addMessage(
                data.answer,
                "bot"
            );


        } catch (error) {


            typing.remove();


            addMessage(

                "No se ha podido conectar con el servidor.",

                "bot"

            );


            console.error(
                "Error:",
                error
            );

        }

    }
);


/* ==========================================
   AÑADIR MENSAJE
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


    const avatar =
        document.createElement("div");


    avatar.classList.add(
        "avatar"
    );


    /* Poner avatar */

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


    /* Scroll automático */

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


            /* Borrar mensajes */

            chatMessages.innerHTML =
                "";


            /* Limpiar input */

            messageInput.value =
                "";


            /* Ocultar WhatsApp */

            if (sendChatWhatsApp) {

                sendChatWhatsApp.hidden =
                    true;

            }


            /* Volver al input */

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

        "más informacion",

        "mas informacion",

        "quiero informacion",

        "quiero información",

        "me interesa",

        "estoy interesado",

        "estoy interesada"

    ];


    const quiereContactar =
        palabrasContacto.some(
            palabra =>
                texto.includes(palabra)
        );


    if (
        quiereContactar &&
        sendChatWhatsApp
    ) {

        sendChatWhatsApp.hidden =
            false;

    }

}


/* ==========================================
   OBTENER CONVERSACIÓN
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
               MENSAJE DEL ASISTENTE
            ============================== */

            if (
                mensaje.classList.contains(
                    "bot"
                )
            ) {


                conversacion +=

                    "Asistente: " +
                    texto +
                    "\n\n";


            }


            /* ==============================
               MENSAJE DEL CLIENTE
            ============================== */

            else {


                conversacion +=

                    "Cliente: " +
                    texto +
                    "\n\n";

            }

        }
    );


    return conversacion;

}


/* ==========================================
   ENVIAR CONVERSACIÓN A WHATSAPP
========================================== */

async function enviarChatWhatsApp() {


    if (!sendChatWhatsApp) {
        return;
    }


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


    /* Desactivar botón */

    sendChatWhatsApp.disabled =
        true;


    sendChatWhatsApp.innerText =
        "Preparando WhatsApp...";


    try {


        /* ==============================
           CREAR DATOS
        ============================== */

        const formData =
            new FormData();


        formData.append(
            "conversacion",
            conversacion
        );


        /* ==============================
           ENVIAR A PHP
        ============================== */

        const response =
            await fetch(
                "formulario.php",
                {

                    method: "POST",

                    body: formData

                }
            );


        const data =
            await response.json();


        /* ==============================
           ERROR PHP
        ============================== */

        if (!data.success) {


            alert(

                data.error ||
                "No se pudo preparar WhatsApp."

            );


            sendChatWhatsApp.disabled =
                false;


            sendChatWhatsApp.innerText =
                "📲 Contactar por WhatsApp";


            return;

        }


        /* ==============================
           ABRIR WHATSAPP
        ============================== */

        window.open(
            data.whatsapp,
            "_blank"
        );


        /* Cambiar texto */

        sendChatWhatsApp.innerText =
            "✓ WhatsApp preparado";


        sendChatWhatsApp.disabled =
            false;


    } catch (error) {


        console.error(
            "Error al enviar a WhatsApp:",
            error
        );


        alert(
            "Ha ocurrido un error al conectar con el servidor."
        );


        sendChatWhatsApp.disabled =
            false;


        sendChatWhatsApp.innerText =
            "📲 Contactar por WhatsApp";

    }

}


/* ==========================================
   EVENTO BOTÓN WHATSAPP
========================================== */

if (sendChatWhatsApp) {


    sendChatWhatsApp.addEventListener(
        "click",
        enviarChatWhatsApp 
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
                    "button"
                );


            button.disabled =
                true;


            button.innerText =
                "Guardando...";


            try {


                const response =
                    await fetch(
                        "formulario.php",
                        {

                            method: "POST",

                            body: formData

                        }
                    );


                const data =
                    await response.json();


                /* ==============================
                   ERROR
                ============================== */

                if (!data.success) {


                    formResult.innerHTML =

                        `<p class="error">
                            ${data.error}
                        </p>`;


                    button.disabled =
                        false;


                    button.innerText =
                        "💬 Contactar por WhatsApp";


                    return;

                }


                /* ==============================
                   ÉXITO
                ============================== */

                formResult.innerHTML =

                    `<p class="success">
                        Datos guardados correctamente.
                        Abriendo WhatsApp...
                    </p>`;


                /* Abrir WhatsApp */

                window.open(
                    data.whatsapp,
                    "_blank"
                );


                /* Limpiar formulario */

                contactForm.reset();


            } catch (error) {


                formResult.innerHTML =

                    `<p class="error">
                        Ha ocurrido un error.
                    </p>`;


                console.error(
                    "Error al enviar el formulario:",
                    error
                );

            }


            button.disabled =
                false;


            button.innerText =
                "💬 Contactar por WhatsApp";

        }
    );

}