document.addEventListener('DOMContentLoaded', () => {
    const chatWindow = document.getElementById('chat-window');
    const userInput = document.getElementById('user-input');
    const sendButton = document.getElementById('send-button');
    const statusSummaryDiv = document.getElementById('status-summary'); // Get the new div

    function appendMessage(sender, message) {
        const messageElement = document.createElement('div');
        messageElement.classList.add('message');
        messageElement.classList.add(sender === 'user' ? 'user-message' : 'bot-message');
        messageElement.textContent = message;
        chatWindow.appendChild(messageElement);
        chatWindow.scrollTop = chatWindow.scrollHeight; // Auto-scroll to bottom
    }

    // Function to fetch and display status summary
    async function fetchAndDisplayStatusSummary() {
        try {
            const response = await fetch('chatbot_backend.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ query: 'resumen estados' }) // Send a specific query for summary
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            if (data.ok && data.type === 'status_summary') {
                let summaryHtml = 'Resumen de Emisiones de Pago:<br>';
                for (const status in data.data) {
                    summaryHtml += `<strong>${status}</strong>: ${data.data[status]}<br>`;
                }
                statusSummaryDiv.innerHTML = summaryHtml;
            } else {
                statusSummaryDiv.textContent = 'No se pudo cargar el resumen de estados.';
                console.error('Error al cargar resumen de estados:', data.error);
            }
        } catch (error) {
            console.error('Error al obtener resumen de estados:', error);
            statusSummaryDiv.textContent = 'Error al conectar con el servicio de resumen.';
        }
    }

    // Initial fetch for status summary
    fetchAndDisplayStatusSummary();

    async function sendMessage() {
        const message = userInput.value.trim();
        if (message === '') return;

        appendMessage('user', message);
        userInput.value = '';

        try {
            const response = await fetch('chatbot_backend.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ query: message })
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            if (data.ok) {
                // If the response is a status summary, update the summary div
                if (data.type === 'status_summary') {
                    let summaryHtml = 'Resumen de Emisiones de Pago:<br>';
                    for (const status in data.data) {
                        summaryHtml += `<strong>${status}</strong>: ${data.data[status]}<br>`;
                    }
                    statusSummaryDiv.innerHTML = summaryHtml;
                    appendMessage('bot', 'He actualizado el resumen de estados.');
                } else {
                    appendMessage('bot', data.mensaje || 'No se encontraron resultados.');
                }
            } else {
                appendMessage('bot', data.mensaje || 'Ocurrió un error al procesar tu solicitud.');
                console.error('Error del bot:', data.error);
            }
        } catch (error) {
            console.error('Error al enviar mensaje:', error);
            appendMessage('bot', 'Lo siento, no pude conectar con el servicio. Inténtalo de nuevo más tarde.');
        }
    }

    sendButton.addEventListener('click', sendMessage);
    userInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });
});