<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Connexion à la base
try {
    $dbHost = 'localhost';
    $dbUser = 'root';
    $dbPass = '';
    $dbName = 'efootball';

    $conn = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Erreur connexion base de données: ' . $e->getMessage());
}

// Traitement POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['username']) || empty($_POST['password'])) {
        header('Location: connexion.php?error=empty');
        exit();
    }

    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $sql = 'SELECT id, prenom, nom, email, username, password, role FROM visiteur WHERE username = :username LIMIT 1';
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result && isset($result['password']) && password_verify($password, $result['password'])) {
        $_SESSION['user_id_from_db'] = $result['id']; // Important pour les pages admin
        $_SESSION['username'] = $result['username'];
        $_SESSION['prenom'] = $result['prenom'];
        $_SESSION['nom'] = $result['nom'];
        $_SESSION['email'] = $result['email'];
        $_SESSION['role'] = $result['role']; // <-- C'est la ligne la plus importante !
        $_SESSION['logged'] = true;

        header('Location: acceuil.php');
        exit();
    } else {
        header('Location: connexion.php?error=login');
        exit();
    }

} else {
    header('Location: connexion.php');
    exit();
}

?>