<?php
session_start();
ini_set('display_errors',1);
error_reporting(E_ALL);

if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    header('Location: connexion.php');
    exit();
}

// Générer token CSRF simple
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}

try {
    $pdo = new PDO('mysql:host=localhost;dbname=efootball;charset=utf8mb4','root','');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Erreur BDD: '.$e->getMessage());
}

$stmt = $pdo->prepare('SELECT prenom, nom, email, username FROM visiteur WHERE username = :username LIMIT 1');
$stmt->execute([':username' => $_SESSION['username']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) {
    die('Utilisateur introuvable');
}
?>
<!doctype html>
<html lang="fr">
<head><meta charset="utf-8"><title>Éditer profil</title></head>
<body style="font-family:Arial;padding:20px;">
    <h2>Édition du profil</h2>
    <form method="post" action="update_profile.php">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <label>Prénom<br><input name="prenom" value="<?php echo htmlspecialchars($user['prenom']); ?>" required></label><br><br>
        <label>Nom<br><input name="nom" value="<?php echo htmlspecialchars($user['nom']); ?>" required></label><br><br>
        <label>Email<br><input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required></label><br><br>
        <button type="submit">Enregistrer</button>
        <a href="profile.php" style="margin-left:12px">Annuler</a>
    </form>
</body>
</html>
