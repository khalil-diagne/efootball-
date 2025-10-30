<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1. Sécurité : Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['logged']) || $_SESSION['logged'] !== true) {
    header('Location: connexion.php');
    exit();
}

// 2. Récupérer l'ID de la commande depuis l'URL et le valider
$order_id = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT);
if (!$order_id) {
    // Si pas d'ID, rediriger vers l'historique
    header('Location: order_history.php');
    exit();
}

$payment_info = null;
if ($order_id) {
    try {
        // Connexion à la base de données
        $pdo = new PDO('mysql:host=localhost;dbname=efootball;charset=utf8mb4', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Récupérer les informations du paiement lié à cette commande
        $stmt = $pdo->prepare(
            'SELECT nom, montant 
             FROM paiements 
             WHERE order_id = :order_id 
             LIMIT 1'
        );
        $stmt->execute([':order_id' => $order_id]);
        $payment_info = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Erreur BDD sur order_confirmation.php: ' . $e->getMessage());
    }
}

// 3. Vider le panier après un paiement réussi
echo "<script>localStorage.removeItem('efootball_cart');</script>";

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation de commande</title>
    <link rel="stylesheet" href="Style_acceuil.css">
    <style>
        body {
            background: linear-gradient(135deg, #1a237e, #0d47a1);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .container {
            max-width: 600px;
            text-align: center;
            background-color: rgba(13, 71, 161, 0.9);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 255, 255, 0.3);
        }
        .success-icon {
            font-size: 60px;
            color: #4caf50;
            margin-bottom: 20px;
        }
        .page-title {
            color: #fff;
            font-size: 28px;
        }
        p {
            color: #eee;
            font-size: 18px;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <nav>
        <a href="acceuil.php" style="font-size: 28px; font-weight: bold; color: #fff; text-decoration: none;"> Dribbleur Store</a>
    </nav>

    <div class="container">
        <div class="success-icon">✓</div>
        <h2 class="page-title">Merci pour votre commande !</h2>
        
        <?php if ($payment_info): ?>
            <p>Bonjour <strong><?php echo htmlspecialchars($payment_info['nom']); ?></strong>, votre commande <strong>#<?php echo htmlspecialchars($order_id); ?></strong> d'un montant de <strong><?php echo htmlspecialchars(number_format($payment_info['montant'], 0, ',', ' ')); ?> FCFA</strong> a bien été enregistrée.</p>
            <p>Elle est maintenant en cours de vérification et sera validée très prochainement.</p>
        <?php else: ?>
            <p>Votre commande <strong>#<?php echo htmlspecialchars($order_id); ?></strong> a bien été enregistrée et est en cours de traitement.</p>
        <?php endif; ?>

        <p>Vous pouvez suivre le statut dans votre historique de commandes.</p>
        <div style="margin-top: 30px;">
            <a href="order_history.php" class="cta-button" style="margin-right: 15px; text-decoration: none;">Voir mes commandes</a>
            <a href="list_articles.php" class="cta-button" style="background: transparent; border: 2px solid #00d4ff; text-decoration: none;">Continuer mes achats</a>
        </div>
    </div>
</body>
</html>