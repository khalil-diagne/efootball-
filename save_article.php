<?php
session_start();
ini_set('display_errors',1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: new_article.php');
    exit();
}

if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    header('Location: connexion.php');
    exit();
}

if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    die('Jeton CSRF invalide');
}

$title = trim($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');
$price = filter_var($_POST['price'] ?? 0, FILTER_VALIDATE_FLOAT);

if ($title === '' || $content === '' || $price === false || $price < 0) {
    die('Titre, contenu et prix valides sont requis');
}

// Traiter l'upload
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    die('Erreur upload image');
}

$file = $_FILES['image'];
$allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!isset($allowed[$mime])) {
    die('Type de fichier non autorisé');
}

if ($file['size'] > 2 * 1024 * 1024) {
    die('Fichier trop volumineux (max 2MB)');
}

$ext = $allowed[$mime];
$safeName = bin2hex(random_bytes(8)) . '.' . $ext;
$destDir = __DIR__ . '/uploads/articles';
if (!is_dir($destDir)) mkdir($destDir, 0755, true);
$destPath = $destDir . DIRECTORY_SEPARATOR . $safeName;

if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    die('Impossible de déplacer le fichier');
}

// Générer slug simple
$slug = preg_replace('/[^a-z0-9]+/i', '-', strtolower(trim($title)));
$slug = trim($slug, '-');

try {
    $pdo = new PDO('mysql:host=localhost;dbname=efootball;charset=utf8mb4','root','');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // S'assurer que le slug est unique
    $base = $slug; $i = 1;
    while (true) {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM articles WHERE slug = :slug');
        $stmt->execute([':slug' => $slug]);
        $count = (int)$stmt->fetchColumn();
        if ($count === 0) break;
        $slug = $base . '-' . $i; $i++;
    }
    
    $stmt = $pdo->prepare('INSERT INTO articles (title, slug, content, price, image, author_username) VALUES (:t, :s, :c, :p, :img, :author)');
    $stmt->execute([':t'=>$title, ':s'=>$slug, ':c'=>$content, ':p' => $price, ':img'=>$safeName, ':author'=>$_SESSION['username']]);

    header('Location: list_articles.php?created=1');
    exit();
} catch (PDOException $e) {
    die('Erreur BDD: '.$e->getMessage());
}
