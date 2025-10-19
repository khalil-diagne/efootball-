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
    // Assurer que les colonnes ID existent (auto-migration simple)
    try {
        // La table visiteur a besoin d'un ID pour la clé étrangère
        $pdo->query("ALTER TABLE `visiteur` ADD COLUMN `id` INT AUTO_INCREMENT PRIMARY KEY FIRST;");
    } catch (PDOException $e) {
        // Ignorer si la colonne existe déjà
    }
    try {
        // La table articles a besoin d'un ID pour la clé étrangère
        $pdo->query("ALTER TABLE `articles` ADD COLUMN `id` INT AUTO_INCREMENT PRIMARY KEY FIRST;");
    } catch (PDOException $e) {
        // Ignorer si la colonne existe déjà
    }

    // Récupérer l'ID de l'utilisateur
    $stmtUser = $pdo->prepare('SELECT id FROM visiteur WHERE username = :username');
    $stmtUser->execute([':username' => $_SESSION['username']]);
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception("Utilisateur non trouvé.");
    }
    $userId = $user['id'];

    // Calculer le prix total
    $totalPrice = array_reduce($cart, function ($sum, $item) {
        return $sum + (isset($item['price']) ? ($item['price'] * 1) : 0); // 1 est la quantité, à adapter si besoin
    }, 0);

    // Insérer dans la table `orders`
    $pdo->beginTransaction();
    $stmtOrder = $pdo->prepare('INSERT INTO orders (user_id, total_price) VALUES (:user_id, :total_price)');
    $stmtOrder->execute([':user_id' => $userId, ':total_price' => $totalPrice]);
    $orderId = $pdo->lastInsertId();

    // Insérer chaque article dans `order_items`
    $stmtItem = $pdo->prepare('INSERT INTO order_items (order_id, article_id, quantity, price) VALUES (:order_id, :article_id, :quantity, :price)');
    foreach ($cart as $item) {
        if (isset($item['id']) && isset($item['price'])) {
            $stmtItem->execute([
                ':order_id' => $orderId, 
                ':article_id' => $item['id'], // Assurez-vous que les articles dans le JS ont un 'id'
                ':quantity' => 1, // Quantité fixe pour le moment
                ':price' => $item['price']]);
        }
    }
    $pdo->commit();

    echo json_encode(['success' => true, 'message' => 'Commande enregistrée avec succès!', 'order_id' => $orderId]);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'enregistrement de la commande: ' . $e->getMessage()]);
}