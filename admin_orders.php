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

// 3. Récupérer la liste des commandes avec les informations de l'utilisateur
$stmt = $pdo->query(
    'SELECT 
        o.id, 
        o.total_price, 
        o.status,
        o.order_date, 
        v.username 
    FROM 
        orders AS o
    JOIN 
        visiteur AS v ON o.user_id = v.id
    ORDER BY 
        o.order_date DESC'
);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);


?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des commandes - Admin</title>
    <link rel="stylesheet" href="admin_styles.css"> <!-- Fichier de style partagé pour l'admin -->
</head>
<body>
    <div class="admin-container">
        <?php include 'admin_sidebar.php'; // Inclure la barre latérale réutilisable ?>

        <div class="admin-content">
            <h1>Gestion des commandes</h1>

            <div class="admin-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID Commande</th>
                            <th>Client (Username)</th>
                            <th>Prix Total</th>
                            <th>Date de la commande</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 20px;">Aucune commande n'a été passée pour le moment.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?php echo htmlspecialchars($order['id']); ?></td>
                                <td><?php echo htmlspecialchars($order['username']); ?></td>
                                <td><?php echo htmlspecialchars(number_format($order['total_price'], 0, ',', ' ')); ?> FCFA</td>
                                <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($order['order_date']))); ?></td>
                                <td><span class="status-badge status-<?php echo htmlspecialchars($order['status']); ?>"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $order['status']))); ?></span></td>
                                <td>
                                    <a href="order_details.php?id=<?php echo $order['id']; ?>" class="admin-btn-primary">Voir détails</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
 