<div class="admin-sidebar">
    <h2>Admin Dashboard</h2>
    <ul>
        <li><a href="admin.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'admin.php' ? 'active' : ''); ?>">Tableau de bord</a></li>
        <li><a href="admin_users.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'admin_users.php' ? 'active' : ''); ?>">Gestion des utilisateurs</a></li>
        <li><a href="admin_articles.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'admin_articles.php' ? 'active' : ''); ?>">Gestion des articles</a></li>
        <li><a href="admin_orders.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'admin_orders.php' ? 'active' : ''); ?>">Gestion des commandes</a></li>
        <li><a href="acceuil.php">Retour au site</a></li>
        <li><a href="logout.php">Déconnexion</a></li>
    </ul>
</div>
