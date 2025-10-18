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

if ($prenom === '' || $nom === '' || $email === '') {
    die('Tous les champs sont obligatoires');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die('Email invalide');
}

try {
    $pdo = new PDO('mysql:host=localhost;dbname=efootball;charset=utf8mb4','root','');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare('UPDATE visiteur SET prenom = :prenom, nom = :nom, email = :email WHERE username = :username');
    $stmt->execute([':prenom'=>$prenom, ':nom'=>$nom, ':email'=>$email, ':username'=>$_SESSION['username']]);

    // Mettre à jour la session
    $_SESSION['prenom'] = $prenom;
    $_SESSION['nom'] = $nom;
    $_SESSION['email'] = $email;

    header('Location: profile.php?updated=1');
    exit();
} catch (PDOException $e) {
    die('Erreur BDD: '.$e->getMessage());
}
