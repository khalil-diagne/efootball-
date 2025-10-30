<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1. Sécurité : Vérifier la méthode et l'utilisateur
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: acceuil.php');
    exit();
}

if (!isset($_SESSION['logged']) || $_SESSION['logged'] !== true) {
    die('Accès non autorisé.');
}

// 2. Protection CSRF
if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    die('Jeton de sécurité invalide.');
}

// 3. Valider les données du formulaire
$montant = filter_input(INPUT_POST, 'montant', FILTER_VALIDATE_FLOAT);
$nom = trim(filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_STRING));
$telephone = trim(filter_input(INPUT_POST, 'telephone', FILTER_SANITIZE_STRING));

// Récupérer le panier de la session
$cart = $_SESSION['cart_for_checkout'] ?? [];
$userId = $_SESSION['user_id_from_db'] ?? null;

if ($montant === false || empty($nom) || empty($telephone) || empty($cart) || !$userId) {
    die('Toutes les informations sont requises.');
}

// 4. Connexion à la base de données
try {
    $pdo = new PDO('mysql:host=localhost;dbname=efootball;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Erreur de connexion à la base de données: ' . $e->getMessage());
}

// 5. Créer la commande ET insérer le paiement dans une transaction
try {
    $pdo->beginTransaction();

    // Étape 1: Créer la commande dans la table `orders`
    $stmtOrder = $pdo->prepare('INSERT INTO orders (user_id, total_price, status) VALUES (:user_id, :total_price, :status)');
    $stmtOrder->execute([
        ':user_id' => $userId, 
        ':total_price' => $montant,
        ':status' => 'en_attente' // Le statut est "en attente" de votre validation manuelle
    ]);
    $orderId = $pdo->lastInsertId();

    // Étape 2: Insérer les articles de la commande dans `order_items`
    $stmtItem = $pdo->prepare('INSERT INTO order_items (order_id, article_id, quantity, price) VALUES (:order_id, :article_id, :quantity, :price)');
    foreach ($cart as $item) {
        $stmtItem->execute([
            ':order_id' => $orderId, 
            ':article_id' => $item['id'],
            ':quantity' => 1, // Quantité fixe pour le moment
            ':price' => $item['price']
        ]);
    }

    // Étape 3: Insérer la preuve de paiement dans la table `paiements`
    $stmtPaiement = $pdo->prepare(
        'INSERT INTO paiements (order_id, nom, telephone, montant) VALUES (:order_id, :nom, :telephone, :montant)'
    );
    $stmtPaiement->execute([
        ':order_id' => $orderId,
        ':nom' => $nom,
        ':telephone' => $telephone,
        ':montant' => $montant
    ]);

    $pdo->commit();

    // Vider le panier de la session
    unset($_SESSION['cart_for_checkout']);
    unset($_SESSION['cart_total_price']);

    // Rediriger vers la page de confirmation finale
    header('Location: order_confirmation.php?order_id=' . $orderId);
    exit();
} catch (Exception $e) {
    $pdo->rollBack();
    die('Erreur lors de l\'enregistrement du paiement: ' . $e->getMessage());
}