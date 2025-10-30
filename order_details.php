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

// 3. Récupérer l'ID de la commande depuis l'URL et valider
$order_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$order_id) {
    header('Location: admin_orders.php');
    exit();
}

$message = '';
$error = '';

// 4. Traitement de la mise à jour du statut
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    // Protection CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        $error = 'Jeton de sécurité invalide.';
    } else {
        $new_status = $_POST['new_status'] ?? '';
        $allowed_statuses = ['en_attente', 'validee', 'annulee'];

        if (in_array($new_status, $allowed_statuses)) {
            $stmt = $pdo->prepare('UPDATE orders SET status = :status WHERE id = :id');
            $stmt->execute([':status' => $new_status, ':id' => $order_id]);
            $message = 'Le statut de la commande a été mis à jour.';
        } else {
            $error = 'Statut non valide.';
        }
    }
}

// Générer un nouveau token CSRF
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// 5. Récupérer les informations de la commande
$stmtOrder = $pdo->prepare(
    'SELECT o.id, o.total_price, o.status, o.order_date, v.username, v.email
     FROM orders AS o
     JOIN visiteur AS v ON o.user_id = v.id
     WHERE o.id = :id'
);
$stmtOrder->execute([':id' => $order_id]);
$order = $stmtOrder->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    die('Commande non trouvée.');
}

// 6. Récupérer les articles de la commande
$stmtItems = $pdo->prepare(
    'SELECT oi.quantity, oi.price, a.title
     FROM order_items AS oi
     JOIN articles AS a ON oi.article_id = a.id
     WHERE oi.order_id = :order_id'
);
$stmtItems->execute([':order_id' => $order_id]);
$order_items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

// 7. Récupérer les informations de paiement
$stmtPaiement = $pdo->prepare(
    'SELECT nom, telephone, montant, date_paiement
     FROM paiements
     WHERE order_id = :order_id
     LIMIT 1'
);
$stmtPaiement->execute([':order_id' => $order_id]);
$payment_info = $stmtPaiement->fetch(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de la commande #<?php echo htmlspecialchars($order['id']); ?></title>
    <link rel="stylesheet" href="admin_styles.css">
    <style>
        .order-summary { background: #f9f9f9; border: 1px solid #eee; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .order-summary h2 { margin-top: 0; }
        .order-summary p { margin: 5px 0; }
        .grid-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'admin_sidebar.php'; ?>

        <div class="admin-content">
            <h1>Détails de la commande #<?php echo htmlspecialchars($order['id']); ?></h1>

            <?php if ($message): ?><div class="admin-alert-success"><?php echo $message; ?></div><?php endif; ?>
            <?php if ($error): ?><div class="admin-alert-error"><?php echo $error; ?></div><?php endif; ?>

            <div class="grid-container">
                <div class="order-summary">
                    <h2>Récapitulatif Commande</h2>
                    <p><strong>Client :</strong> <?php echo htmlspecialchars($order['username']); ?> (<?php echo htmlspecialchars($order['email']); ?>)</p>
                    <p><strong>Date :</strong> <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($order['order_date']))); ?></p>
                    <p><strong>Montant Total :</strong> <?php echo htmlspecialchars(number_format($order['total_price'], 0, ',', ' ')); ?> FCFA</p>
                    <p><strong>Statut actuel :</strong> <span class="status-badge status-<?php echo htmlspecialchars($order['status']); ?>"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $order['status']))); ?></span></p>
                </div>

                <div class="order-summary" style="background-color: #e8f6fd;">
                    <h2>Informations de Paiement</h2>
                    <?php if ($payment_info): ?>
                        <p><strong>Nom du payeur :</strong> <?php echo htmlspecialchars($payment_info['nom']); ?></p>
                        <p><strong>Téléphone du payeur :</strong> <?php echo htmlspecialchars($payment_info['telephone']); ?></p>
                        <p><strong>Montant déclaré :</strong> <?php echo htmlspecialchars(number_format($payment_info['montant'], 0, ',', ' ')); ?> FCFA</p>
                        <p><strong>Date de soumission :</strong> <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($payment_info['date_paiement']))); ?></p>
                    <?php else: ?>
                        <p>Aucune information de paiement n'a été soumise pour cette commande.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="order-summary">
                <form action="order_details.php?id=<?php echo $order_id; ?>" method="POST" style="margin-top: 20px;">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="action" value="update_status">
                    <label for="new_status" style="font-weight: bold;">Changer le statut :</label>
                    <select name="new_status" id="new_status" style="padding: 8px; border-radius: 5px; margin-right: 10px;">
                        <option value="en_attente" <?php if ($order['status'] === 'en_attente') echo 'selected'; ?>>En attente</option>
                        <option value="validee" <?php if ($order['status'] === 'validee') echo 'selected'; ?>>Validée</option>
                        <option value="annulee" <?php if ($order['status'] === 'annulee') echo 'selected'; ?>>Annulée</option>
                    </select>
                    <button type="submit" class="admin-btn-primary">Mettre à jour</button>
                </form>
            </div>

            <h2>Articles de la commande</h2>
            <div class="admin-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Article</th>
                            <th>Quantité</th>
                            <th>Prix unitaire</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['title']); ?></td>
                            <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                            <td><?php echo htmlspecialchars(number_format($item['price'], 0, ',', ' ')); ?> FCFA</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</body>
</html>