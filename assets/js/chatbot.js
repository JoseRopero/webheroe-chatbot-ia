/*
document.addEventListener("DOMContentLoaded", function() {
    const chatButton = document.getElementById('chatbot-toggle-btn');
    const chatWindow = document.getElementById('chat-window');
    const chatInput = document.getElementById('chat-input');
    const chatSubmit = document.getElementById('chat-submit');
    const chatLog = document.getElementById('chat-log');

    // Inicializar el estado del chat como cerrado
    chatWindow.style.display = "none";

    // Al hacer clic en el botón, se alterna la visibilidad del chat
    chatButton.addEventListener("click", function() {
        chatWindow.style.display = chatWindow.style.display === "none" ? "block" : "none";
    });

    // Manejar el envío de mensajes
    chatSubmit.addEventListener("click", function() {
        const userInput = chatInput.value.trim();

        if (userInput === '') return;

        // Agregar el mensaje del usuario al chat log
        const userMessageDiv = document.createElement('div');
        userMessageDiv.classList.add('user-message');
        userMessageDiv.textContent = userInput;
        chatLog.appendChild(userMessageDiv);

        // Limpiar el campo de texto
        chatInput.value = '';

        // Mostrar un mensaje de "Pensando..." mientras se espera la respuesta
        const loadingMessage = document.createElement('div');
        loadingMessage.classList.add('bot-message');
        loadingMessage.textContent = "Pensando...";
        chatLog.appendChild(loadingMessage);

        // Hacer una llamada AJAX a la API del servidor para obtener las respuestas
        fetch('/wp-admin/admin-ajax.php', {
            method: 'POST',
            body: new URLSearchParams({
                action: 'webheroe_chatbot_ia_process_input',
                message: userInput,
                nonce: webheroe_ajax.nonce
            })
        })
        .then(response => response.json())
        .then(responseData => {
            if (responseData.success) {
                // Reemplazar el mensaje de carga con la respuesta del bot
                loadingMessage.textContent = responseData.data.relevante;
                const titularDiv = document.createElement('div');
                titularDiv.classList.add('bot-message');
                titularDiv.textContent = "Titulares: " + responseData.data.titulares.join(", ");
                chatLog.appendChild(titularDiv);

                const relevanciaDiv = document.createElement('div');
                relevanciaDiv.classList.add('bot-message');
                relevanciaDiv.textContent = responseData.data.relevancia;
                chatLog.appendChild(relevanciaDiv);
            } else {
                loadingMessage.textContent = "Error al procesar la solicitud.";
            }
        })
        .catch(() => {
            loadingMessage.textContent = "Error de comunicación.";
        });
    });

    // Enviar mensaje al presionar "Enter"
    chatInput.addEventListener("keypress", function(e) {
        if (e.key === "Enter") {
            chatSubmit.click();
        }
    });
});
*/
// chatbot.js
document.addEventListener("DOMContentLoaded", function() {
    const chatButton = document.getElementById('chatbot-toggle-btn');
    const chatWindow = document.getElementById('chat-window');
    const chatInput = document.getElementById('chat-input');
    const chatSubmit = document.getElementById('chat-submit');
    const chatLog = document.getElementById('chat-log');

    // Inicializar el estado del chat como cerrado
    chatWindow.style.display = "none";

    // Al hacer clic en el botón, se alterna la visibilidad del chat
    chatButton.addEventListener("click", function() {
        chatWindow.style.display = chatWindow.style.display === "none" ? "block" : "none";
    });

    // Manejar el envío de mensajes
    chatSubmit.addEventListener("click", function() {
        const userInput = chatInput.value.trim();

        if (userInput === '') return;

        // Agregar el mensaje del usuario al chat log
        const userMessageDiv = document.createElement('div');
        userMessageDiv.classList.add('user-message');
        userMessageDiv.textContent = userInput;
        chatLog.appendChild(userMessageDiv);

        // Limpiar el campo de texto
        chatInput.value = '';

        // Mostrar un mensaje de "Pensando..." mientras se espera la respuesta
        const loadingMessage = document.createElement('div');
        loadingMessage.classList.add('bot-message');
        loadingMessage.textContent = "Pensando...";
        chatLog.appendChild(loadingMessage);

        // Hacer una llamada AJAX a la API del servidor para obtener las respuestas
        fetch(webheroe_ajax.ajax_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
            },
            body: new URLSearchParams({
                action: 'webheroe_chatbot_ia_process_input',
                message: userInput,
                nonce: webheroe_ajax.nonce
            })
        })
        .then(response => response.json())
        .then(responseData => {
            if (responseData.success) {
                // Reemplazar el mensaje de carga con la respuesta del bot
                loadingMessage.textContent = responseData.data.respuesta;
            } else {
                loadingMessage.textContent = responseData.data;
            }
            // Opcional: Desplazar el chat al final
            chatLog.scrollTop = chatLog.scrollHeight;
        })
        .catch(() => {
            loadingMessage.textContent = "Error de comunicación.";
        });
    });

    // Enviar mensaje al presionar "Enter"
    chatInput.addEventListener("keypress", function(e) {
        if (e.key === "Enter") {
            chatSubmit.click();
        }
    });
});



