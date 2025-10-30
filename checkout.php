<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1. Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['username']) || !isset($_SESSION['logged']) || $_SESSION['logged'] !== true) {
    http_response_code(403); // Forbidden
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté.']);
    exit();
}

// 2. Récupérer les données du panier envoyées en JSON
$json = file_get_contents('php://input');
$cart = json_decode($json, true);
 
// Validation améliorée du panier
if (!is_array($cart) || empty($cart)) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Le panier est vide.']);
    exit();
}

// 3. Connexion à la base de données
try {
    $pdo = new PDO('mysql:host=localhost;dbname=efootball;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur de connexion à la base de données.']);
    exit();
}

// 4. Calculer le prix total et valider les articles du panier
try {
    $articleIds = array_column($cart, 'id');
    if (empty($articleIds)) {
        throw new Exception("Le panier ne contient aucun article valide.");
    }
    
    $placeholders = implode(',', array_fill(0, count($articleIds), '?'));
    $stmtCheck = $pdo->prepare("SELECT id, price FROM articles WHERE id IN ($placeholders)");
    $stmtCheck->execute($articleIds);
    $dbArticles = $stmtCheck->fetchAll(PDO::FETCH_ASSOC);

    // Si certains articles du panier n'existent plus en BDD, on lève une erreur
    if (count($dbArticles) !== count($articleIds)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Certains articles de votre panier ne sont plus disponibles. Veuillez rafraîchir la page.']);
        exit();
    }

    // Calculer le prix total à partir des prix sécurisés de la BDD
    $totalPrice = array_reduce($dbArticles, function ($sum, $item) {
        return $sum + floatval($item['price']);
    }, 0);

    // 5. Sauvegarder le panier validé et le total dans la session
    // On ne stocke que les articles qui ont été vérifiés en BDD
    $_SESSION['cart_for_checkout'] = $dbArticles;
    $_SESSION['cart_total_price'] = $totalPrice;

    // 6. Construire l'URL de paiement Wave directe
    $waveBaseUrl = "https://pay.wave.com/m/M_sn_v_ayijpULtTM/c/sn/";
    $paymentUrl = $waveBaseUrl . "?amount=" . $totalPrice;

    // 7. Renvoyer l'URL au client pour redirection
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Redirection vers Wave pour le paiement.', 'paymentUrl' => $paymentUrl]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la préparation de la commande: ' . $e->getMessage()]);
}