document.addEventListener('DOMContentLoaded', () => {
    const chatWidget = document.getElementById('chatWidget');
    const chatBody = document.getElementById('chatBody');
    const chatInput = document.getElementById('chatInput');
    const sendChatBtn = document.getElementById('sendChatBtn');

    let isChatOpen = false;
    let pollingInterval;

    window.toggleChat = async function() {
        isChatOpen = !isChatOpen;
        if (isChatOpen) {
            chatWidget.style.display = 'flex';
            // On s'assure que le premier chargement est terminé avant de lancer le rafraîchissement
            await fetchMessages(); 
            // On nettoie tout intervalle précédent avant d'en créer un nouveau
            if(pollingInterval) clearInterval(pollingInterval);
            pollingInterval = setInterval(fetchMessages, 5000);
        } else {
            chatWidget.style.display = 'none';
            if(pollingInterval) clearInterval(pollingInterval);
        }
    };

    function scrollToBottom() {
        chatBody.scrollTop = chatBody.scrollHeight;
    }

    async function fetchMessages() {
        try {
            const response = await fetch('chat_api.php?action=fetch');
            if (!response.ok) throw new Error('Network response was not ok');
            
            const messages = await response.json();
            
            chatBody.innerHTML = ''; // Vider les anciens messages
            messages.forEach(msg => {
                const messageDiv = document.createElement('div');
                messageDiv.classList.add('chat-message');
                // 'user_id' est défini dans la session PHP et injecté si nécessaire
                // Pour cet exemple, on suppose que l'API nous dit qui est l'expéditeur
                messageDiv.classList.add(msg.is_admin_sender ? 'received' : 'sent');
                messageDiv.textContent = msg.message;
                chatBody.appendChild(messageDiv);
            });
            scrollToBottom();
        } catch (error) {
            console.error('Erreur lors de la récupération des messages:', error);
        }
    }

    async function sendMessage() {
        const message = chatInput.value.trim();
        if (message === '') return;

        try {
            const formData = new FormData();
            formData.append('action', 'send');
            formData.append('message', message);

            const response = await fetch('chat_api.php', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) throw new Error('Failed to send message');
            
            const result = await response.json();

            if (result.success) {
                chatInput.value = '';
                fetchMessages(); // Recharger les messages immédiatement
            } else {
                alert('Erreur: ' + result.message);
            }
        } catch (error) {
            console.error('Erreur lors de l\'envoi du message:', error);
        }
    }

    sendChatBtn.addEventListener('click', sendMessage);
    chatInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });
});