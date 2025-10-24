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

if (empty($cart)) {
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

// 4. Logique d'enregistrement de la commande
try {
    // Récupérer l'ID de l'utilisateur
    $stmtUser = $pdo->prepare('SELECT id FROM visiteur WHERE username = :username LIMIT 1');
    $stmtUser->execute([':username' => $_SESSION['username']]);
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception("Utilisateur non trouvé.");
    }
    $userId = $user['id'];

    // Calculer le prix total
    // On valide que les articles du panier existent bien en base de données
    $articleIds = array_column($cart, 'id');
    $placeholders = implode(',', array_fill(0, count($articleIds), '?'));
    $stmtCheck = $pdo->prepare("SELECT id, price FROM articles WHERE id IN ($placeholders)");
    $stmtCheck->execute($articleIds);
    $dbArticles = $stmtCheck->fetchAll(PDO::FETCH_KEY_PAIR);

    $totalPrice = array_reduce($cart, function ($sum, $item) use ($dbArticles) {
        // Utiliser le prix de la base de données, pas celui envoyé par le client
        return $sum + (isset($dbArticles[$item['id']]) ? floatval($dbArticles[$item['id']]) : 0);
    }, 0);

    // Insérer dans la table `orders`
    $pdo->beginTransaction();
    // Ajout du statut par défaut 'en_attente'
    $stmtOrder = $pdo->prepare('INSERT INTO orders (user_id, total_price, status) VALUES (:user_id, :total_price, :status)');
    $stmtOrder->execute([
        ':user_id' => $userId, 
        ':total_price' => $totalPrice,
        ':status' => 'en_attente' // Statut par défaut
    ]);

    $orderId = $pdo->lastInsertId();
    foreach ($cart as $item) {
        if (isset($item['id']) && isset($dbArticles[$item['id']])) {
            $stmtItem = $pdo->prepare('INSERT INTO order_items (order_id, article_id, quantity, price) VALUES (:order_id, :article_id, :quantity, :price)');
            $stmtItem->execute([
                ':order_id' => $orderId, 
                ':article_id' => $item['id'], // Assurez-vous que les articles dans le JS ont un 'id'
                ':quantity' => 1, // Quantité fixe pour le moment
                ':price' => $dbArticles[$item['id']] // Prix sécurisé depuis la BDD
            ]);
        }
    }
    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Commande enregistrée avec succès!', 'order_id' => $orderId]);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'enregistrement de la commande: ' . $e->getMessage()]);
}