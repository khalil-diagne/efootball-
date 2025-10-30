<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1. Sécurité : Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['logged']) || $_SESSION['logged'] !== true) {
    header('Location: connexion.php');
    exit();
}

// 2. Récupérer les informations du panier depuis la session
$cart = $_SESSION['cart_for_checkout'] ?? [];
$amount = $_SESSION['cart_total_price'] ?? 0;

if (empty($cart) || $amount <= 0) {
    header('Location: list_articles.php'); // Si le panier est vide, on retourne aux articles
    exit();
}

// Générer un token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement de la commande</title>
    <link rel="stylesheet" href="Style_acceuil.css">
    <style>
        body {
            background: linear-gradient(135deg, #1a237e, #0d47a1);
        }
        .container {
            max-width: 500px;
            margin-top: 50px;
            background-color: rgba(13, 71, 161, 0.9);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 255, 255, 0.3);
        }
        .page-title {
            color: #4caf50;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #fff;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: none;
            background-color: #f0f0f0;
        }
        .order-summary {
            background: rgba(255,255,255,0.1);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <nav>
        <a href="acceuil.php" style="font-size: 28px; font-weight: bold; color: #fff; text-decoration: none;"> Dribbleur Store</a>
    </nav>

    <div class="container">
        <h2 class="page-title">Finaliser le paiement</h2>

        <div class="order-summary">
            <h3 style="color: #00d4ff; margin-bottom: 15px;">Instructions de paiement</h3>
            <p>1. Envoyez <strong><?php echo htmlspecialchars(number_format($amount, 0, ',', ' ')); ?> FCFA</strong> au numéro Wave :</p>
            <p style="font-size: 1.5em; font-weight: bold; color: #00ff88; margin: 10px 0;">77 959 75 48</p>
            <p>2. Une fois le paiement effectué, remplissez le formulaire ci-dessous pour valider votre commande.</p>
        </div>

        <form action="process_payment.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <!-- Nous n'avons plus d'order_id ici, il sera créé dans process_payment.php -->
            <input type="hidden" name="montant" value="<?php echo htmlspecialchars($amount); ?>">

            <div class="form-group">
                <label for="nom">Nom complet</label>
                <input type="text" id="nom" name="nom" required placeholder="Votre nom complet">
            </div>

            <div class="form-group">
                <label for="telephone">Votre numéro de téléphone (utilisé pour le paiement)</label>
                <input type="tel" id="telephone" name="telephone" required placeholder="Le numéro qui a envoyé l'argent">
            </div>

            <button type="submit" class="cta-button" style="width: 100%;">J'ai payé, valider ma commande</button>
        </form>

        <div style="text-align: center; margin-top: 20px;">
            <a href="list_articles.php" style="color: #fff; opacity: 0.8;">Annuler et retourner aux articles</a>
        </div>
    </div>
</body>
</html>