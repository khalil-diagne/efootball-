<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1. Vérifier si l'utilisateur est connecté ET s'il a le rôle 'admin'
if (!isset($_SESSION['username']) || !isset($_SESSION['logged']) || $_SESSION['logged'] !== true || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // Rediriger vers la page de connexion ou afficher une erreur d'accès
    header('Location: connexion.php'); // Correction : rediriger vers la page de connexion
    exit();
}

// 2. Connexion à la base de données
try {
    $pdo = new PDO('mysql:host=localhost;dbname=efootball;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Erreur de connexion à la base de données: ' . $e->getMessage());
}

// Le reste de la logique de la page admin viendra ici
// Par exemple, récupérer des statistiques, lister des utilisateurs, etc.

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration eFootball Store</title>
    <link rel="stylesheet" href="admin_styles.css"> <!-- Utiliser le fichier de style partagé -->
    <style>
        /* Styles spécifiques au tableau de bord */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .stat-card {
            background-color: #e0f2f7; /* Bleu très clair */
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            text-align: center;
        }
        .stat-card h3 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .stat-card p {
            font-size: 2em;
            font-weight: bold;
            color: #3498db;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'admin_sidebar.php'; // Inclure la barre latérale réutilisable ?>

    <div class="admin-content">
        <h1>Bienvenue, <?php echo htmlspecialchars($_SESSION['username']); ?> !</h1>
        <p>Ceci est votre tableau de bord d'administration. Vous pouvez gérer les utilisateurs, les articles et les commandes.</p>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Utilisateurs</h3>
                <p>
                    <?php
                    $stmt = $pdo->query('SELECT COUNT(*) FROM visiteur');
                    echo $stmt->fetchColumn();
                    ?>
                </p>
            </div>
            <div class="stat-card">
                <h3>Total Articles</h3>
                <p>
                    <?php
                    $stmt = $pdo->query('SELECT COUNT(*) FROM articles');
                    echo $stmt->fetchColumn();
                    ?>
                </p>
            </div>
            <div class="stat-card">
                <h3>Total Commandes</h3>
                <p>
                    <?php
                    $stmt = $pdo->query('SELECT COUNT(*) FROM orders');
                    echo $stmt->fetchColumn();
                    ?>
                </p>
            </div>
            <!-- Ajoutez d'autres statistiques ici -->
        </div>
    </div>
    </div>
</body>
</html>