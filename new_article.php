<?php
session_start();
ini_set('display_errors',1);
error_reporting(E_ALL);

if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    header('Location: connexion.php');
    exit();
}

if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
?>
<!doctype html>
<html lang="fr">
<head><meta charset="utf-8"><title>Nouvel article</title></head>
<body style="font-family:Arial;padding:20px;">
    <h2>Ajouter un article</h2>
    <form action="save_article.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <label>Titre<br><input type="text" name="title" required maxlength="255"></label><br><br>
        <label>Contenu<br><textarea name="content" rows="8" cols="60" required></textarea></label><br><br>
        <label>Prix (en FCFA)<br><input type="number" name="price" required step="0.01" min="0"></label><br><br>
        <label>Image (jpg/png/gif, max 2MB)<br><input type="file" name="image" accept="image/*" required></label><br><br>
        <button type="submit">Publier</button>
        <a href="profile.php" style="margin-left:12px">Annuler</a>
    </form>

</body>
</html>
