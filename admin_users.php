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

$message = '';
$error = '';

// 3. Traitement des actions (suppression, changement de rôle)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Protection CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        $error = 'Jeton de sécurité invalide.';
    } else {
        $userId = $_POST['user_id'] ?? null;

        // Action de suppression
        if (isset($_POST['action']) && $_POST['action'] === 'delete' && $userId) {
            // On ne peut pas se supprimer soi-même
            if ($userId == ($_SESSION['user_id_from_db'] ?? null)) { // On vérifie que la variable de session existe
                $error = 'Vous ne pouvez pas supprimer votre propre compte administrateur.';
            } else {
                $stmt = $pdo->prepare('DELETE FROM visiteur WHERE id = :id');
                $stmt->execute([':id' => $userId]);
                $message = 'Utilisateur supprimé avec succès.';
            }
        }
        // Action de changement de rôle
        if (isset($_POST['action']) && $_POST['action'] === 'change_role' && $userId) {
            $newRole = $_POST['new_role'] ?? 'user';
            // On ne peut pas changer son propre rôle pour éviter de se bloquer
            if ($userId == ($_SESSION['user_id_from_db'] ?? null)) {
                $error = 'Vous ne pouvez pas modifier votre propre rôle.';
            } elseif ($newRole === 'admin' || $newRole === 'user') {
                $stmt = $pdo->prepare('UPDATE visiteur SET role = :role WHERE id = :id');
                $stmt->execute([':role' => $newRole, ':id' => $userId]);
                $message = 'Rôle de l\'utilisateur mis à jour.';
            }
        }
    }
}

// Générer un token CSRF pour les formulaires
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// 4. Récupérer la liste de tous les utilisateurs
$stmt = $pdo->query('SELECT id, username, prenom, nom, email, role FROM visiteur ORDER BY id ASC');
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Stocker l'ID de l'admin actuel en session pour la vérification de suppression
$stmtAdmin = $pdo->prepare('SELECT id FROM visiteur WHERE username = :username');
$stmtAdmin->execute([':username' => $_SESSION['username']]);
$_SESSION['user_id_from_db'] = $stmtAdmin->fetchColumn();

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des utilisateurs - Admin</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="admin_styles.css"> <!-- Fichier de style partagé pour l'admin -->
</head>
<body>
    <div class="admin-container">
        <div class="admin-sidebar">
            <h2>Admin Dashboard</h2>
            <ul>
                <li><a href="admin.php">Tableau de bord</a></li>
                <li><a href="admin_users.php" class="active">Gestion des utilisateurs</a></li>
                <li><a href="admin_articles.php">Gestion des articles</a></li>
                <li><a href="admin_orders.php">Gestion des commandes</a></li>
                <li><a href="logout.php">Déconnexion</a></li>
            </ul>
        </div>

        <div class="admin-content">
            <h1>Gestion des utilisateurs</h1>

            <?php if ($message): ?><div class="admin-alert-success"><?php echo $message; ?></div><?php endif; ?>
            <?php if ($error): ?><div class="admin-alert-error"><?php echo $error; ?></div><?php endif; ?>

            <div class="admin-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Nom complet</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <!-- Formulaire pour changer le rôle -->
                                <form action="admin_users.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <input type="hidden" name="action" value="change_role">
                                    <select name="new_role" onchange="this.form.submit()" <?php if ($user['id'] == ($_SESSION['user_id_from_db'] ?? null)) echo 'disabled'; ?>>
                                        <option value="user" <?php if ($user['role'] === 'user') echo 'selected'; ?>>Utilisateur</option>
                                        <option value="admin" <?php if ($user['role'] === 'admin') echo 'selected'; ?>>Admin</option>
                                    </select>
                                </form>
                            </td>
                            <td>
                                <!-- Formulaire pour supprimer l'utilisateur -->
                                <?php if ($user['id'] != ($_SESSION['user_id_from_db'] ?? null)): // Ne pas afficher le bouton pour soi-même ?>
                                <form action="admin_users.php" method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="admin-btn-danger">Supprimer</button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
<footer>
    <div class="footer-content">
<p>
    Améliorations et bonnes pratiques incluses

1.  **Sécurité renforcée** :
    *   **Protection CSRF** : Un jeton unique (`csrf_token`) est généré et vérifié pour chaque action (suppression, changement de rôle). Cela empêche des sites malveillants de forcer un administrateur à effectuer des actions à son insu.
    *   **Auto-protection** : Le script empêche un administrateur de supprimer son propre compte ou de changer son propre rôle via l'interface, évitant ainsi de se bloquer l'accès.
    *   **Confirmation JavaScript** : Une boîte de dialogue `confirm()` demande une confirmation avant toute suppression, réduisant les risques de clics accidentels.

</p>
    </div>
</footer>
</html>
 


