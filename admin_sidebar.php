

<?php
// S'assurer que l'ID de l'admin est en session pour les appels API (comme le chat)
if (!isset($_SESSION['user_id_from_db'])) {
    try {
        $pdo_sidebar = new PDO('mysql:host=localhost;dbname=efootball;charset=utf8mb4', 'root', '');
        $pdo_sidebar->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt_sidebar = $pdo_sidebar->prepare('SELECT id FROM visiteur WHERE username = :username');
        $stmt_sidebar->execute([':username' => $_SESSION['username']]);
        $user_id = $stmt_sidebar->fetchColumn();
        if ($user_id) {
            $_SESSION['user_id_from_db'] = $user_id;
        }
    } catch (PDOException $e) { /* Ignorer l'erreur pour ne pas bloquer l'affichage */ }
}
?>









<style>

/* Style pour le badge de notification */
.notification-badge {
    background-color: #e74c3c; /* Rouge */
    color: white;
    border-radius: 10px;
    padding: 2px 8px;
    font-size: 12px;
    font-weight: bold;
    margin-left: 8px;
    display: none; /* Caché par défaut */
}
</style>

<div class="admin-sidebar">
    <h2>Admin Dashboard</h2>
    <ul>
        <li><a href="admin.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'admin.php' ? 'active' : ''); ?>">Tableau de bord</a></li>
        <li><a href="admin_users.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'admin_users.php' ? 'active' : ''); ?>">Gestion des utilisateurs</a></li>
        <li><a href="admin_articles.php" class="<?php echo (in_array(basename($_SERVER['PHP_SELF']), ['admin_articles.php', 'new_article.php']) ? 'active' : ''); ?>">Gestion des articles</a></li>
        <li>
            <a href="admin_chat.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'admin_chat.php' ? 'active' : ''); ?>">
                Messagerie
                <span id="chat-notification-badge" class="notification-badge"></span>
            </a>
        </li>
        <li><a href="admin_orders.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'admin_orders.php' ? 'active' : ''); ?>">Gestion des commandes</a></li>
        <li><a href="acceuil.php">Retour au site</a></li>
        <li><a href="logout.php">Déconnexion</a></li>
    </ul>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatBadge = document.getElementById('chat-notification-badge');

    async function checkNewMessages() {
        try {
            const response = await fetch('chat_api.php?action=check_new');
            const data = await response.json();
            if (data.unread_count > 0) {
                chatBadge.textContent = data.unread_count;
                chatBadge.style.display = 'inline-block';
            } else {
                chatBadge.style.display = 'none';
            }
        } catch (error) {
            console.error('Erreur lors de la vérification des messages:', error);
        }
    }

    // Vérifier les messages toutes les 10 secondes
    checkNewMessages();
    setInterval(checkNewMessages, 10000);
});
</script>
