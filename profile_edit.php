<?php
session_start();
if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    header('Location: connexion.php');
    exit();
}

// generate CSRF token
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(16));

try {
    $pdo = new PDO('mysql:host=localhost;dbname=efootball;charset=utf8mb4','root','');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->prepare('SELECT prenom, nom, email, username, avatar FROM visiteur WHERE username = :username LIMIT 1');
    $stmt->execute([':username'=>$_SESSION['username']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Erreur BDD: '.$e->getMessage());
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Éditer profil</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2 class="page-title">Éditer mon profil</h2>
        <form action="update_profile.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

            <div class="form-row" style="margin-bottom:12px">
                <div class="preview-box">
                    <?php if (!empty($user['avatar'])): ?>
                        <img src="uploads/avatars/<?php echo htmlspecialchars($user['avatar']); ?>" alt="avatar" class="avatar-img">
                    <?php else: ?>
                        <div class="avatar"><?php echo strtoupper(substr($user['prenom'] ?? $user['username'],0,1)); ?></div>
                    <?php endif; ?>
                </div>
                <div style="flex:1">
                    <div class="form-group">
                        <label>Changer avatar (jpg/png, &lt; 2MB)</label>
                        <input class="file-input" type="file" name="avatar" accept="image/png,image/jpeg">
                    </div>
                </div>
            </div>

            <div class="form-row" style="margin-bottom:6px">
                <div class="form-group" style="flex:1">
                    <label>Prénom</label>
                    <input type="text" name="prenom" value="<?php echo htmlspecialchars($user['prenom'] ?? ''); ?>" required>
                </div>
                <div class="form-group" style="flex:1">
                    <label>Nom</label>
                    <input type="text" name="nom" value="<?php echo htmlspecialchars($user['nom'] ?? ''); ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
            </div>

            <div class="form-actions">
                <button class="btn btn-primary" type="submit">Enregistrer</button>
                <a class="btn btn-outline" href="profile.php">Annuler</a>
            </div>
        </form>
    </div>
</body>
</html>
