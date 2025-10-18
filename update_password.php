<?php
session_start();
ini_set('display_errors',1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: change_password.php');
    exit();
}

if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    header('Location: connexion.php');
    exit();
}

if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    die('Jeton CSRF invalide');
}

$old = $_POST['old_password'] ?? '';
$new = $_POST['new_password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

if ($new === '' || $old === '' || $confirm === '') {
    die('Tous les champs sont obligatoires');
}
if ($new !== $confirm) {
    die('Le nouveau mot de passe et la confirmation ne correspondent pas');
}

try {
    $pdo = new PDO('mysql:host=localhost;dbname=efootball;charset=utf8mb4','root','');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare('SELECT password FROM visiteur WHERE username = :username LIMIT 1');
    $stmt->execute([':username' => $_SESSION['username']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) die('Utilisateur introuvable');

    if (!isset($row['password']) || !password_verify($old, $row['password'])) {
        die('Ancien mot de passe incorrect');
    }

    $hash = password_hash($new, PASSWORD_DEFAULT);
    $u = $pdo->prepare('UPDATE visiteur SET password = :pw WHERE username = :username');
    $u->execute([':pw' => $hash, ':username' => $_SESSION['username']]);

    header('Location: profile.php?pw_changed=1');
    exit();
} catch (PDOException $e) {
    die('Erreur BDD: ' . $e->getMessage());
}
