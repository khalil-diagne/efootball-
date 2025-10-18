<?php
session_start();
ini_set('display_errors',1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: profile_edit.php');
    exit();
}

if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    header('Location: connexion.php');
    exit();
}

// Vérifier CSRF
if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    die('Jeton CSRF invalide');
}

$prenom = trim($_POST['prenom'] ?? '');
$nom = trim($_POST['nom'] ?? '');
$email = trim($_POST['email'] ?? '');

// avatar will be processed if provided
$avatarFilename = null;

if ($prenom === '' || $nom === '' || $email === '') {
    die('Tous les champs sont obligatoires');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die('Email invalide');
}

try {
    $pdo = new PDO('mysql:host=localhost;dbname=efootball;charset=utf8mb4','root','');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ensure avatar column exists (simple migration)
    try {
        $pdo->query("ALTER TABLE visiteur ADD COLUMN avatar VARCHAR(255) NULL");
    } catch (PDOException $ignore) {
        // ignore errors (column probably exists)
    }

    // handle avatar upload and resize/crop to 200x200 using GD
    if (!empty($_FILES['avatar']) && is_uploaded_file($_FILES['avatar']['tmp_name'])) {
        $file = $_FILES['avatar'];
        $allowed = ['image/jpeg' => '.jpg', 'image/png' => '.png'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!isset($allowed[$mime])) {
            die('Type de fichier non supporté.');
        }
        if ($file['size'] > 4 * 1024 * 1024) {
            die('Fichier trop volumineux (max 4MB).');
        }

        $ext = $allowed[$mime];
        $avatarFilename = bin2hex(random_bytes(12)) . $ext;
        $destPath = __DIR__ . '/uploads/avatars/' . $avatarFilename;

        // create image resource from upload
        if ($mime === 'image/jpeg') {
            $src = imagecreatefromjpeg($file['tmp_name']);
        } else {
            $src = imagecreatefrompng($file['tmp_name']);
        }
        if (!$src) {
            die('Impossible de traiter l\'image.');
        }

        $w = imagesx($src);
        $h = imagesy($src);
        $size = 200; // target

        // compute cropping to square centered
        if ($w > $h) {
            $new_h = $h;
            $new_w = $h;
            $src_x = intval(($w - $h) / 2);
            $src_y = 0;
        } else {
            $new_w = $w;
            $new_h = $w;
            $src_x = 0;
            $src_y = intval(($h - $w) / 2);
        }

        $tmp = imagecreatetruecolor($size, $size);
        // preserve PNG transparency
        if ($mime === 'image/png') {
            imagealphablending($tmp, false);
            imagesavealpha($tmp, true);
            $transparent = imagecolorallocatealpha($tmp, 0, 0, 0, 127);
            imagefilledrectangle($tmp, 0, 0, $size, $size, $transparent);
        } else {
            $bg = imagecolorallocate($tmp, 255, 255, 255);
            imagefilledrectangle($tmp, 0, 0, $size, $size, $bg);
        }

        // copy and resize cropped region
        imagecopyresampled($tmp, $src, 0, 0, $src_x, $src_y, $size, $size, $new_w, $new_h);

        // save to dest
        if ($mime === 'image/jpeg') {
            imagejpeg($tmp, $destPath, 90);
        } else {
            imagepng($tmp, $destPath, 6);
        }

        imagedestroy($src);
        imagedestroy($tmp);

        // remove previous avatar if exists
        try {
            $oldStmt = $pdo->prepare('SELECT avatar FROM visiteur WHERE username = :username');
            $oldStmt->execute([':username' => $_SESSION['username']]);
            $old = $oldStmt->fetchColumn();
            if ($old) {
                $oldPath = __DIR__ . '/uploads/avatars/' . $old;
                if (is_file($oldPath)) @unlink($oldPath);
            }
        } catch (Exception $e) {
            // ignore
        }
    }

    // build query dynamically if avatar present
    if ($avatarFilename) {
        $stmt = $pdo->prepare('UPDATE visiteur SET prenom = :prenom, nom = :nom, email = :email, avatar = :avatar WHERE username = :username');
        $params = [':prenom'=>$prenom, ':nom'=>$nom, ':email'=>$email, ':avatar'=>$avatarFilename, ':username'=>$_SESSION['username']];
    } else {
        $stmt = $pdo->prepare('UPDATE visiteur SET prenom = :prenom, nom = :nom, email = :email WHERE username = :username');
        $params = [':prenom'=>$prenom, ':nom'=>$nom, ':email'=>$email, ':username'=>$_SESSION['username']];
    }
    $stmt->execute($params);

    // Mettre à jour la session
    $_SESSION['prenom'] = $prenom;
    $_SESSION['nom'] = $nom;
    $_SESSION['email'] = $email;
    if ($avatarFilename) $_SESSION['avatar'] = $avatarFilename;

    header('Location: profile.php?updated=1');
    exit();
} catch (PDOException $e) {
    die('Erreur BDD: '.$e->getMessage());
}
