/* ==========================================
   CHATBOT
========================================== */

const chatForm = document.getElementById("chatForm");

const messageInput = document.getElementById("message");

const chatMessages = document.getElementById("chatMessages");


chatForm.addEventListener("submit", async function (event) {

    event.preventDefault();


    const message = messageInput.value.trim();


    if (!message) {
        return;
    }


    /* Añadir mensaje del usuario */

    addMessage(message, "user");


    /* Limpiar input */

    messageInput.value = "";


    /* Mostrar escribiendo */

    const typing = addMessage(
        "Escribiendo...",
        "bot",
        true
    );


    try {

        const response = await fetch("chat.php", {

            method: "POST",

            headers: {

                "Content-Type": "application/json"

            },

            body: JSON.stringify({

                message: message

            })

        });


        const data = await response.json();


        /* Eliminar escribiendo */

        typing.remove();


        if (!data.success) {

            addMessage(
                "Lo siento, ha ocurrido un error: " +
                data.error,
                "bot"
            );

            return;
        }


        addMessage(
            data.answer,
            "bot"
        );


    } catch (error) {

        typing.remove();


        addMessage(
            "No se ha podido conectar con el servidor‼.",
            "bot"
        );

        console.error(error);

    }

});


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

    avatar.classList.add("avatar");


    const bubble =
        document.createElement("div");

    bubble.classList.add("bubble");


    bubble.innerText = text;


    messageElement.appendChild(avatar);

    messageElement.appendChild(bubble);


    chatMessages.appendChild(messageElement);


    /* Scroll automático */

    chatMessages.scrollTop =
        chatMessages.scrollHeight;


    return messageElement;
}

/* ==========================================
   REINICIAR CHAT
========================================== */
/* ==========================================
   REINICIAR SOLO EL CHAT
========================================== */

const resetChat = document.getElementById("resetChat");

resetChat.addEventListener("click", function () {

    // Borra SOLO los mensajes del chat
    chatMessages.innerHTML = "";

    // Limpia el cuadro de texto
    messageInput.value = "";

    // Vuelve a colocar el cursor en el input
    messageInput.focus();

});


/* ==========================================
   FORMULARIO CONTACTO
========================================== */

const contactForm =
    document.getElementById("contactForm");


const formResult =
    document.getElementById("formResult");


contactForm.addEventListener(
    "submit",
    async function (event) {

        event.preventDefault();


        const formData =
            new FormData(contactForm);


        const button =
            contactForm.querySelector("button");


        button.disabled = true;

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


            if (!data.success) {

                formResult.innerHTML =
                    `<p class="error">
                        ${data.error}
                    </p>`;

                button.disabled = false;

                button.innerText =
                    "💬 Contactar por WhatsApp";

                return;
            }


            formResult.innerHTML =
                `<p class="success">
                    Datos guardados correctamente.
                    Abriendo WhatsApp...
                </p>`;


            /*
                Abrir WhatsApp
            */

            window.open(
                data.whatsapp,
                "_blank"
            );


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


        button.disabled = false;

        button.innerText =
            "💬 Contactar por WhatsApp";

    }
);