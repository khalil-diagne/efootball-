<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Connexion DB (même config que les autres fichiers)
try {
    $dbHost = 'localhost';
    $dbUser = 'root';
    $dbPass = '';
    $dbName = 'efootball';

    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Erreur BDD: ' . $e->getMessage());
}

// Si l'utilisateur n'est pas connecté, rediriger
if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    header('Location: connexion.php');
    exit();
}

// Afficher uniquement le profil de l'utilisateur connecté (ne pas permettre l'affichage d'autres profils)
$requested = $_SESSION['username'];

 $stmt = $pdo->prepare('SELECT prenom, nom, email, username, avatar FROM visiteur WHERE username = :username LIMIT 1');
$stmt->execute([':username' => $requested]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $notFound = true;
} else {
    $notFound = false;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Profil - <?php echo isset($user['username']) ? htmlspecialchars($user['username']) : 'Utilisateur'; ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<?php if ($notFound): ?>
    <div class="container notfound">
        <div class="profile-card">
            <h2>Profil introuvable</h2>
            <p>Le profil demandé n'existe pas.</p>
            <a class="btn btn-outline" href="acceuil.php">Retour</a>
        </div>
    </div>
<?php else: ?>
    <div class="container">
        <div class="profile-card">
            <div class="profile-header">
                <div>
                    <?php if (!empty($user['avatar'])): ?>
                        <img class="avatar-img" src="uploads/avatars/<?php echo htmlspecialchars($user['avatar']); ?>" alt="avatar">
                    <?php else: ?>
                        <div class="avatar"><?php echo strtoupper(substr($user['prenom'] ?? $user['username'],0,1)); ?></div>
                    <?php endif; ?>
                </div>
                <div>
                    <div class="profile-name"><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></div>
                    <div class="profile-meta"><strong>Nom d'utilisateur :</strong> <?php echo htmlspecialchars($user['username']); ?></div>
                    <div class="profile-meta"><strong>Email :</strong> <?php echo htmlspecialchars($user['email']); ?></div>
                </div>
            </div>

            <?php if (isset($_GET['updated']) && $_GET['updated'] == '1'): ?>
                <div class="success-text">Profil mis à jour avec succès.</div>
            <?php endif; ?>
            <?php if (isset($_GET['pw_changed']) && $_GET['pw_changed'] == '1'): ?>
                <div class="success-text">Mot de passe mis à jour.</div>
            <?php endif; ?>

            <div class="profile-actions">
                <a class="btn btn-primary" href="profile_edit.php">Éditer mon profil</a>
                <a class="btn btn-success" href="change_password.php">Changer mot de passe</a>
                <a class="btn btn-danger" href="logout.php">Se déconnecter</a>
            </div>

            <div style="margin-top: 20px; text-align: center;">
                <a href="acceuil.php" class="btn btn-outline">Retour à l'accueil</a>
            </div>
        </div>
    </div>
<?php endif; ?>
</body>
</html>
