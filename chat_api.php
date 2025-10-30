<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Sécurité : l'utilisateur doit être connecté
if (!isset($_SESSION['logged']) || $_SESSION['logged'] !== true || !isset($_SESSION['user_id_from_db'])) {
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé.']);
    exit();
}

try {
    $pdo = new PDO('mysql:host=localhost;dbname=efootball;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur de connexion à la base de données.']);
    exit();
}

$action = $_REQUEST['action'] ?? null;
$current_user_id = $_SESSION['user_id_from_db'];
$is_admin = ($_SESSION['role'] === 'admin');

// L'ID de l'admin principal (à adapter si besoin)
define('ADMIN_USER_ID', 5); 

switch ($action) {
    case 'fetch':
        // Si un admin ouvre une conversation, on marque les messages comme lus
        if ($is_admin && isset($_GET['user_id'])) {
            $stmt_mark_read = $pdo->prepare(
                "UPDATE chat_messages SET is_read = 1 WHERE sender_id = :sender_id AND receiver_id = :receiver_id AND is_read = 0"
            );
            $stmt_mark_read->execute([
                ':sender_id' => $_GET['user_id'],
                ':receiver_id' => $current_user_id
            ]);
        }
        // L'admin voit les messages d'un utilisateur spécifique, l'utilisateur ne voit que sa propre conversation
        $conversation_partner_id = $is_admin ? ($_GET['user_id'] ?? 0) : ADMIN_USER_ID;
        
        if ($is_admin && !$conversation_partner_id) {
             echo json_encode([]); // L'admin doit sélectionner un utilisateur
             exit();
        }

        $user_id_for_query = $is_admin ? $conversation_partner_id : $current_user_id;

        $stmt = $pdo->prepare(
            "SELECT *, (sender_id = :admin_id) as is_admin_sender 
             FROM chat_messages 
             WHERE (sender_id = :user_id AND receiver_id = :admin_id) 
                OR (sender_id = :admin_id AND receiver_id = :user_id)
             ORDER BY timestamp ASC"
        );
        $stmt->execute([':user_id' => $user_id_for_query, ':admin_id' => ADMIN_USER_ID]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($messages);
        break;

    case 'send':
        $message = trim($_POST['message'] ?? '');
        if (empty($message)) {
            echo json_encode(['success' => false, 'message' => 'Le message ne peut pas être vide.']);
            exit();
        }

        // Ensure current_user_id is valid before proceeding
        if (!is_numeric($current_user_id) || $current_user_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID utilisateur invalide pour l\'envoi.']);
            exit();
        }

        // L'admin envoie à un utilisateur spécifique, l'utilisateur envoie toujours à l'admin
        $receiver_id = $is_admin ? ($_POST['receiver_id'] ?? 0) : ADMIN_USER_ID;

        if (!$receiver_id) {
            echo json_encode(['success' => false, 'message' => 'Destinataire non spécifié.']);
            exit();
        }
        
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO chat_messages (sender_id, receiver_id, message) VALUES (:sender_id, :receiver_id, :message)"
            );
            $stmt->execute([
                ':sender_id' => $current_user_id,
                ':receiver_id' => $receiver_id,
                ':message' => $message
            ]);
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            error_log("Chat API Send Error: " . $e->getMessage()); // Log l'erreur pour le débogage serveur
            echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'envoi du message à la base de données.']);
        }
        break;

    case 'get_conversations':
        // Uniquement pour l'admin, pour lister les utilisateurs qui ont envoyé un message
        if (!$is_admin) {
            echo json_encode(['success' => false, 'message' => 'Accès non autorisé.']);
            exit();
        }
        // Récupère les conversations et indique celles qui ont des messages non lus
        $stmt = $pdo->prepare(
            "SELECT 
                v.id, 
                v.username,
                (SELECT COUNT(*) FROM chat_messages WHERE sender_id = v.id AND receiver_id = :admin_id AND is_read = 0) as unread_count
             FROM 
                visiteur v
             WHERE v.id IN (
                SELECT DISTINCT sender_id FROM chat_messages WHERE receiver_id = :admin_id
                UNION
                SELECT DISTINCT receiver_id FROM chat_messages WHERE sender_id = :admin_id
             ) AND v.id != :admin_id
             ORDER BY unread_count DESC, v.username ASC"
        );
        $stmt->execute([':admin_id' => ADMIN_USER_ID]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    case 'check_new':
        // Uniquement pour l'admin, pour vérifier s'il y a des messages non lus
        if (!$is_admin) {
            echo json_encode(['unread_count' => 0]);
            exit();
        }
        $stmt = $pdo->prepare("SELECT COUNT(id) FROM chat_messages WHERE receiver_id = :admin_id AND is_read = 0");
        $stmt->execute([':admin_id' => $current_user_id]);
        $unread_count = $stmt->fetchColumn();
        echo json_encode(['unread_count' => (int)$unread_count]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Action non valide.']);
        break;
}
?>