<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL); 

// Connexion DB (même config que les autres fichiers)
try {
    $dbHost = 'localhost';
    $dbUser = 'root';
    $dbPass = '';
    $dbName = 'efootball';

    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Erreur BDD: ' . $e->getMessage());
}

// Si l'utilisateur n'est pas connecté, rediriger
if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    header('Location: connexion.php');
    exit();
}

// Récupérer l'ID de l'utilisateur pour la requête
$user_id = $_SESSION['user_id_from_db'] ?? null;

$orders = [];
if ($user_id) {
    // Récupérer les commandes de l'utilisateur connecté
    $stmt = $pdo->prepare(
        'SELECT id, total_price, status, order_date 
         FROM orders 
         WHERE user_id = :user_id 
         ORDER BY order_date DESC'
    );
    $stmt->execute([':user_id' => $user_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historique des commandes</title>
    <link rel="stylesheet" href="Style_acceuil.css">
</head>
<body>
    <nav>
        <a href="acceuil.php" style="font-size: 28px; font-weight: bold; color: #fff; text-decoration: none;"> Dribbleur Store</a>
    </nav>

    <div class="container" style="padding-top: 120px;">
        <h2 class="page-title">Mon historique de commandes</h2>

        <?php if (empty($orders)): ?>
            <div class="profile-card" style="text-align: center;">
                <p>Vous n'avez encore passé aucune commande.</p>
                <a href="list_articles.php" class="btn btn-primary">Voir les articles</a>
            </div>
        <?php else: ?>
            <div class="orders-list">
                <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-card-header">
                        <h3>Commande #<?php echo htmlspecialchars($order['id']); ?></h3>
                        <span class="status-badge status-<?php echo htmlspecialchars($order['status']); ?>">
                            <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $order['status']))); ?>
                        </span>
                    </div>
                    <div class="order-card-details">
                        <div>
                            <strong>Date :</strong><br>
                            <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($order['order_date']))); ?>
                        </div>
                        <div>
                            <strong>Prix Total :</strong><br>
                            <span style="color: #00ff88; font-weight: bold;"><?php echo htmlspecialchars(number_format($order['total_price'], 0, ',', ' ')); ?> FCFA</span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div style="margin-top: 20px;">
            <a href="acceuil.php" class="cta-button" style="padding: 12px 30px; font-size: 16px; text-decoration: none;">Retour à l'accueil</a>
        </div>
    </div>
</body>
</html>