<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Sécurité : Vérifier si l'utilisateur est un administrateur connecté
if (!isset($_SESSION['logged']) || $_SESSION['logged'] !== true || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: connexion.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messagerie - Admin</title>
    <link rel="stylesheet" href="admin_styles.css">
    <link rel="stylesheet" href="chat_styles.css">
    <style>
        .chat-admin-container {
            display: flex;
            height: calc(100vh - 120px); /* Hauteur ajustée */
        }
        .conversations-list {
            width: 300px;
            border-right: 1px solid #e0e0e0;
            overflow-y: auto;
        }
        .conversation-item {
            padding: 15px;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
        }
        .conversation-item:hover, .conversation-item.active {
            background-color: #f7f9fc;
        }
        .chat-panel {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        .chat-body { background-color: #fff; }
        .chat-footer { background-color: #f9f9f9; }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'admin_sidebar.php'; ?>

        <div class="admin-content">
            <h1>Messagerie instantanée</h1>
            <div class="chat-admin-container">
                <div class="conversations-list" id="conversationsList">
                    <!-- La liste des conversations sera chargée ici -->
                </div>
                <div class="chat-panel">
                    <div class="chat-body" id="chatBody">
                        <p style="text-align:center; padding: 50px; color: #888;">Sélectionnez une conversation pour commencer.</p>
                    </div>
                    <div class="chat-footer" id="chatFooter" style="display:none;">
                        <input type="text" id="chatInput" placeholder="Répondre...">
                        <button id="sendChatBtn">Envoyer</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const conversationsList = document.getElementById('conversationsList');
        const chatBody = document.getElementById('chatBody');
        const chatFooter = document.getElementById('chatFooter');
        const chatInput = document.getElementById('chatInput');
        const sendChatBtn = document.getElementById('sendChatBtn');
        let activeUserId = null;

        async function loadConversations() {
            const response = await fetch('chat_api.php?action=get_conversations');
            const users = await response.json();
            conversationsList.innerHTML = '';
            users.forEach(user => {
                const div = document.createElement('div');
                div.className = 'conversation-item';
                if (user.id == activeUserId) {
                    div.classList.add('active');
                }
                div.textContent = user.username;
                div.dataset.userId = user.id;
                if (user.unread_count > 0) {
                    const badge = document.createElement('span');
                    badge.className = 'notification-badge unread-badge';
                    badge.textContent = user.unread_count;
                    badge.style.display = 'inline-block';
                    div.appendChild(badge);
                }
                div.onclick = () => selectConversation(user.id, div);
                conversationsList.appendChild(div);
            });
        }

        async function selectConversation(userId, element) {
            activeUserId = userId;
            document.querySelectorAll('.conversation-item').forEach(el => el.classList.remove('active'));
            element.classList.add('active');
            chatFooter.style.display = 'flex';
            await loadMessages(userId);
        }

        async function loadMessages(userId) {
            const response = await fetch(`chat_api.php?action=fetch&user_id=${userId}`);
            const messages = await response.json();
            chatBody.innerHTML = '';
            messages.forEach(msg => {
                const div = document.createElement('div');
                div.className = `chat-message ${msg.is_admin_sender ? 'sent' : 'received'}`;
                div.textContent = msg.message;
                chatBody.appendChild(div);
            });
            chatBody.scrollTop = chatBody.scrollHeight;

            // Recharger la liste des conversations pour mettre à jour les compteurs de non-lus
            await loadConversations();
        }

        async function sendMessage() {
            const message = chatInput.value.trim();
            if (!message || !activeUserId) return;
            
            const formData = new FormData();
            formData.append('action', 'send');
            formData.append('message', message);
            formData.append('receiver_id', activeUserId);

            try {
                const response = await fetch('chat_api.php', { method: 'POST', body: formData });
                const result = await response.json();

                if (result.success) {
                    chatInput.value = '';
                    await loadMessages(activeUserId); // Rafraîchir la conversation
                } else {
                    // Afficher une erreur si l'envoi a échoué
                    alert('Erreur lors de l\'envoi du message : ' + (result.message || 'Erreur inconnue'));
                }
            } catch (error) {
                console.error('Erreur critique lors de l\'envoi du message:', error);
                alert('Une erreur réseau est survenue. Veuillez réessayer.');
            }
        }

        sendChatBtn.addEventListener('click', sendMessage);
        chatInput.addEventListener('keypress', e => e.key === 'Enter' && sendMessage());

        loadConversations();
        setInterval(loadConversations, 15000); // Rafraîchir la liste des conversations
        setInterval(() => {
            if (activeUserId) loadMessages(activeUserId);
        }, 5000); // Rafraîchir les messages de la conv active
    });
    </script>
</body>
</html>
