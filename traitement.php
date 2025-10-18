<?php
// Afficher les erreurs pour le développement
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

try {
    $dbUser = 'root';
    $dbPass = '';
    $dbName = 'efootball';
    $dbHost = 'localhost';
    $dsn = "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4";

    $conn = new PDO($dsn, $dbUser, $dbPass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    
    die('Erreur de connexion à la base de données : ' . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification des champs obligatoires
    if (empty($_POST['prenom']) || empty($_POST['nom']) || empty($_POST['email']) || empty($_POST['username']) || empty($_POST['password']) || empty($_POST['confirm_password'])) {
        die('ERREUR: Tous les champs sont obligatoires');
    }

    // Récupération et nettoyage
    $prenom = htmlspecialchars(strip_tags($_POST['prenom']));
    $nom = htmlspecialchars(strip_tags($_POST['nom']));
    $email = htmlspecialchars(strip_tags($_POST['email']));
    $username = htmlspecialchars(strip_tags($_POST['username']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        die('ERREUR: LES MOTS DE PASSES NE SONT PAS CORRESPONDANTS');
    }

    // Hachage du mot de passe
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        // DEBUG: afficher les données POST reçues
        error_log("DEBUG POST: " . print_r($_POST, true));

        // Préparation de la requête (n'exécutez pas encore) - affichez l'état des paramètres
        $sql = "INSERT INTO visiteur (prenom, nom, email, username, password ) VALUES (:prenom, :nom, :email, :username, :password)";
        error_log("DEBUG SQL: $sql");
        error_log("DEBUG params: prenom=$prenom, nom=$nom, email=$email, username=$username");

        // Vérifier si le username ou l'email existe déjà (clé primaire sur username possible)
        $checkSql = "SELECT COUNT(*) FROM visiteur WHERE username = :username OR email = :email";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bindParam(':username', $username);
        $checkStmt->bindParam(':email', $email);
        $checkStmt->execute();
        $exists = (int) $checkStmt->fetchColumn();
        if ($exists > 0) {
            // Ne pas tenter l'insertion et informer l'utilisateur
            error_log("DEBUG: Username or email already exists: username=$username, email=$email");
            die('ERREUR: Le nom d\'utilisateur ou l\'email existe déjà. Choisissez un autre username ou utilisez un autre email.');
        }

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':prenom', $prenom);
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $hashed_password);

        // Pour debug, exécuter et récupérer le résultat
        $executed = $stmt->execute();
        if ($executed) {
            error_log("DEBUG: Insert executed, lastInsertId=" . $conn->lastInsertId());
        } else {
            $errInfo = $stmt->errorInfo();
            error_log("DEBUG: Execute failed: " . print_r($errInfo, true));
        }

        // Stocker uniquement ce qui est nécessaire en session
        $_SESSION['username'] = $username;
        $_SESSION['prenom'] = $prenom;
        $_SESSION['nom'] = $nom;
        $_SESSION['email'] = $email;
        $_SESSION['logged'] = true;


        // Redirection facultative après succès
        header("Location: connexion.php");
        exit();
    } catch (PDOException $e){
        error_log('Erreur SQL: ' . $e->getMessage());
        die('Erreur SQL: ' . $e->getMessage());
    }
}
?>



