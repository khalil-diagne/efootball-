<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1. Sécurité : Vérifier si l'utilisateur est un administrateur connecté
if (!isset($_SESSION['logged']) || $_SESSION['logged'] !== true || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: connexion.php');
    exit();
}

// 2. Connexion à la base de données
try {
    $pdo = new PDO('mysql:host=localhost;dbname=efootball;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Erreur de connexion à la base de données: ' . $e->getMessage());
}

$message = '';
$error = '';

// 3. Traitement de la suppression d'un article
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    // Protection CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        $error = 'Jeton de sécurité invalide.';
    } else {
        $article_id = $_POST['article_id'] ?? null;
        if ($article_id) {
            // D'abord, récupérer le nom de l'image pour la supprimer du serveur
            $stmtImg = $pdo->prepare('SELECT image FROM articles WHERE id = :id');
            $stmtImg->execute([':id' => $article_id]);
            $image_name = $stmtImg->fetchColumn();

            // Ensuite, supprimer l'article de la base de données
            $stmtDelete = $pdo->prepare('DELETE FROM articles WHERE id = :id');
            $stmtDelete->execute([':id' => $article_id]);

            // Enfin, si l'image existe, la supprimer du dossier uploads
            if ($image_name && file_exists('uploads/articles/' . $image_name)) {
                @unlink('uploads/articles/' . $image_name);
            }

            $message = 'Article supprimé avec succès.';
        }
    }
}

// Générer un nouveau token CSRF pour les formulaires
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// 4. Récupérer la liste de tous les articles
$stmt = $pdo->query('SELECT id, title, price, image, author_username, created_at FROM articles ORDER BY created_at DESC');
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des articles - Admin</title>
    <link rel="stylesheet" href="admin_styles.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'admin_sidebar.php'; ?>

        <div class="admin-content">
            <h1>Gestion des articles</h1>

            <a href="new_article.php" class="admin-btn-primary" style="margin-bottom: 20px; display: inline-block;">+ Ajouter un article</a>

            <?php if ($message): ?><div class="admin-alert-success"><?php echo $message; ?></div><?php endif; ?>
            <?php if ($error): ?><div class="admin-alert-error"><?php echo $error; ?></div><?php endif; ?>

            <div class="admin-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th style="width: 80px;">Image</th>
                            <th>Titre</th>
                            <th>Prix</th>
                            <th>Auteur</th>
                            <th>Date</th>
                            <th style="width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($articles as $article): ?>
                        <tr>
                            <td><img src="uploads/articles/<?php echo htmlspecialchars($article['image']); ?>" alt="" style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px;"></td>
                            <td><?php echo htmlspecialchars($article['title']); ?></td>
                            <td><?php echo htmlspecialchars(number_format($article['price'], 0, ',', ' ')); ?> FCFA</td>
                            <td><?php echo htmlspecialchars($article['author_username']); ?></td>
                            <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($article['created_at']))); ?></td>
                            <td>
                                <a href="#" class="admin-btn-primary" style="background-color: #f39c12;">Éditer</a>
                                <form action="admin_articles.php" method="POST" style="display: inline;" onsubmit="return confirm('Voulez-vous vraiment supprimer cet article ?');">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <input type="hidden" name="article_id" value="<?php echo $article['id']; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="admin-btn-danger">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>