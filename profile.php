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

$stmt = $pdo->prepare('SELECT prenom, nom, email, username FROM visiteur WHERE username = :username LIMIT 1');
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
    <style>
        body{font-family: Arial, Helvetica, sans-serif; padding:30px; background:#f7f7f7}
        .card{background:#fff;padding:20px;border-radius:8px;max-width:600px;margin:0 auto;box-shadow:0 6px 18px rgba(0,0,0,0.08)}
        .meta{color:#666;font-size:14px}
        a.button{display:inline-block;margin-top:15px;padding:8px 14px;background:#007bff;color:#fff;border-radius:6px;text-decoration:none}
    </style>
</head>
<body>
<?php if ($notFound): ?>
    <div class="card">
        <h2>Profil introuvable</h2>
        <p>Le profil demandé n'existe pas.</p>
        <a class="button" href="acceuil.php">Retour</a>
    </div>
<?php else: ?>
    <div class="card">
        <h2>Profil de <?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></h2>
        <p class="meta"><strong>Nom d'utilisateur :</strong> <?php echo htmlspecialchars($user['username']); ?></p>
        <p class="meta"><strong>Email :</strong> <?php echo htmlspecialchars($user['email']); ?></p>

        <?php if (isset($_GET['updated']) && $_GET['updated'] == '1'): ?>
            <p style="color:green">Profil mis à jour avec succès.</p>
        <?php endif; ?>
        <?php if (isset($_GET['pw_changed']) && $_GET['pw_changed'] == '1'): ?>
            <p style="color:green">Mot de passe mis à jour.</p>
        <?php endif; ?>

        <p class="meta">Ceci est votre profil.</p>
        <a class="button" href="profile_edit.php">Éditer mon profil</a>
        <a class="button" href="change_password.php" style="margin-left:8px; background:#28a745">Changer mot de passe</a>
        <a class="button" href="logout.php" style="background:#dc3545;margin-left:10px">Se déconnecter</a>
    </div>
<?php endif; ?>
</body>
</html>
