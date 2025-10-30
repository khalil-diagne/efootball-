<?php
session_start();
ini_set('display_errors',1);
error_reporting(E_ALL);

// 1. Sécurité renforcée : Vérifier si l'utilisateur est un administrateur connecté
if (!isset($_SESSION['logged']) || $_SESSION['logged'] !== true || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: connexion.php');
    exit();
}

// 2. Générer un token CSRF plus robuste et cohérent avec les autres pages admin
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvel article - Admin</title>
    <link rel="stylesheet" href="admin_styles.css"> <!-- 3. Intégration du style admin -->
</head>
<body>
    <div class="admin-container">
        <?php include 'admin_sidebar.php'; // 4. Inclusion de la barre latérale pour une UI cohérente ?>

        <div class="admin-content">
            <h1>Ajouter un nouvel article</h1>

            <form action="save_article.php" method="post" enctype="multipart/form-data" class="admin-form">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                
                <div class="form-group">
                    <label for="title">Titre de l'article</label>
                    <input type="text" id="title" name="title" required maxlength="255">
                </div>
                <div class="form-group">
                    <label for="content">Contenu</label>
                    <textarea id="content" name="content" rows="8" required></textarea>
                </div>
                <div class="form-group">
                    <label for="price">Prix (en FCFA)</label>
                    <input type="number" id="price" name="price" required step="1" min="0" placeholder="Ex: 5000">
                </div>
                <div class="form-group">
                    <label for="image">Image de l'article (JPG, PNG, max 2MB)</label>
                    <input type="file" id="image" name="image" accept="image/jpeg,image/png" required>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="admin-btn-primary">Publier l'article</button>
                    <a href="admin_articles.php" class="admin-btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
