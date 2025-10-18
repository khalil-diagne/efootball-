<?php
session_start();
ini_set('display_errors',1);
error_reporting(E_ALL);

if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    header('Location: connexion.php');
    exit();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}
?>
<!doctype html>
<html lang="fr">
<head><meta charset="utf-8"><title>Changer mot de passe</title></head>
<body style="font-family:Arial;padding:20px;">
    <h2>Changer le mot de passe</h2>
    <form action="update_password.php" method="post">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <label>Ancien mot de passe<br><input type="password" name="old_password" required></label><br><br>
        <label>Nouveau mot de passe<br><input type="password" name="new_password" required></label><br><br>
        <label>Confirmer nouveau<br><input type="password" name="confirm_password" required></label><br><br>
        <button type="submit">Mettre à jour</button>
        <a href="profile.php" style="margin-left:12px">Annuler</a>
    </form>
</body>
</html>
