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
   IDENTIDADES DEL AGENTE
========================================== */

const agentButtons =
    document.querySelectorAll(".agent-button");

const assistantName =
    document.getElementById("assistantName");

const assistantDescription =
    document.getElementById("assistantDescription");

const assistantAvatar =
    document.getElementById("assistantAvatar");


/*
   Identidad seleccionada por defecto
*/

let selectedAgent = "asesor";


/*
   Información visual de cada identidad
*/

const agentInfo = {

    asesor: {

        name:
            "Alejandro Herradón, tu Asistente de confianza",

        description:
            "● Asesoramiento personalizado las 24 horas",

        avatar:
            "img/asset.png"

    },

    ventas: {

        name:
            "Alejandro, tu Asesor Comercial",

        description:
            "● Especialista en ventas y servicios",

        avatar:
            "img/asset.png"

    },

    ia: {

        name:
            "Alejandro, Consultor de Inteligencia Artificial",

        description:
            "● Especialista en IA y automatización",

        avatar:
            "img/asset.png"

    },

    soporte: {

        name:
            "Alejandro, Especialista de Soporte",

        description:
            "● Soporte técnico y resolución de problemas",

        avatar:
            "img/asset.png"

    }

};


/* ==========================================
   CAMBIAR IDENTIDAD
========================================== */

function cambiarIdentidad(agent) {

    if (!agentInfo[agent]) {
        return;
    }


    selectedAgent = agent;


    /*
       Cambiar botón activo
    */

    agentButtons.forEach(function(button) {

        button.classList.remove("active");

        if (
            button.dataset.agent === agent
        ) {

            button.classList.add("active");

        }

    });


    /*
       Cambiar nombre
    */

    if (assistantName) {

        assistantName.textContent =
            agentInfo[agent].name;

    }


    /*
       Cambiar descripción
    */

    if (assistantDescription) {

        assistantDescription.textContent =
            agentInfo[agent].description;

    }


    /*
       Cambiar avatar
    */

    if (assistantAvatar) {

        assistantAvatar.src =
            agentInfo[agent].avatar;

        assistantAvatar.alt =
            agentInfo[agent].name;

    }

}


/* ==========================================
   EVENTOS DE LOS BOTONES
========================================== */

agentButtons.forEach(function(button) {

    button.addEventListener(
        "click",
        function() {

            const agent =
                button.dataset.agent;

            cambiarIdentidad(agent);

        }
    );

});


/* ==========================================
   BOTÓN EMAIL
========================================== */

const sendChatEmail =
    document.getElementById("sendChatEmail");


/* ==========================================
   FORMULARIO DATOS CHAT
========================================== */

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


/* ==========================================
   BOTÓN REINICIAR
========================================== */

const resetChat =
    document.getElementById("resetChat");


/* ==========================================
   AVATAR MENSAJES
========================================== */

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


/* ==========================================
   ENVIAR MENSAJE
========================================== */

if (chatForm) {

    chatForm.addEventListener(
        "submit",
        async function(event) {

            event.preventDefault();


            const message =
                messageInput.value.trim();


            if (!message) {
                return;
            }


            /*
               Mostrar mensaje usuario
            */

            addMessage(
                message,
                "user"
            );


            messageInput.value = "";


            /*
               Detectar intención contacto
            */

            comprobarSolicitudContacto(
                message
            );


            /*
               Mostrar escribiendo
            */

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

                /*
                   Enviar mensaje + identidad
                */

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


                /*
                   Respuesta del agente
                */

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


/* ==========================================
   REINICIAR CHAT
========================================== */

if (resetChat) {

    resetChat.addEventListener(
        "click",
        function() {

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


            /*
               Volvemos al asesor
            */

            cambiarIdentidad(
                "asesor"
            );


            messageInput.focus();

        }
    );

}


/* ==========================================
   DETECTAR CONTACTO
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
        function(mensaje) {

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
   BOTÓN ENVIAR CONVERSACIÓN
========================================== */

if (sendChatEmail) {

    sendChatEmail.addEventListener(
        "click",
        function() {

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

            }

        }
    );

}


/* ==========================================
   ENVIAR CONVERSACIÓN
========================================== */

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

        chatNombre.focus();

        return;

    }


    if (!email) {

        alert(
            "Por favor, introduce tu correo electrónico antes de enviar la conversación."
        );

        chatEmail.focus();

        return;

    }


    /*
       VALIDACIÓN CORRECTA
    */

    const emailValido =
        /^[^\s@]+@[^\s@]+\.[^\s@]+$/;


    if (!emailValido.test(email)) {

        alert(
            "Por favor, introduce un correo electrónico válido."
        );

        chatEmail.focus();

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


/* ==========================================
   EVENTO CONFIRMAR EMAIL
========================================== */

if (confirmSendChatEmail) {

    confirmSendChatEmail.addEventListener(
        "click",
        confirmarEnvioConversacion
    );

}


/* ==========================================
   FORMULARIO CONTACTO
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
        async function(event) {

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