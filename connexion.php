<?php
session_start();


?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dribbleur Store - Connexion</title>
    <style>
        /* Style global pour un thème gaming plus fluide et dynamique */
        body {
            background: linear-gradient(135deg, #1a237e, #0d47a1); /* Gradient bleu pour un effet fluide et gaming */
            font-family: 'Arial', sans-serif;
            color: #ffffff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            overflow: hidden; /* Empêche le scrolling inutile pour une expérience fluide */
        }

        .container {
            background-color: rgba(13, 71, 161, 0.9); /* Fond semi-transparent pour un effet fluide */
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 255, 255, 0.3); /* Ombre plus douce et fluide */
            width: 90%; /* Plus fluide : s'adapte aux écrans */
            max-width: 400px;
            animation: slideIn 0.8s ease-out; /* Animation d'entrée fluide */
            transition: transform 0.3s ease; /* Transition pour les interactions */
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(50px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h1 {
            text-align: center;
            color: #4caf50;
            font-size: 24px;
            margin-bottom: 25px;
            text-shadow: 0 2px 5px rgba(76, 175, 80, 0.5); /* Ombre subtile pour un effet gaming fluide */
        }

        .logo {
            display: block;
            margin: 0 auto 20px auto; /* Centrer le logo */
            width: 190px; /* Taille adaptable */
            animation: fadeIn 1s ease-in; /* Animation d'apparition fluide */
            border-radius: 10px; /* Coins arrondis pour un style gaming */
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-bottom: 8px;
            font-weight: bold;
            transition: color 0.3s ease; /* Transition fluide pour les labels */
        }

        input {
            padding: 12px;
            margin-bottom: 18px;
            border: none;
            border-radius: 8px;
            background-color: #11a923b1;
            color: #000000;
            transition: box-shadow 0.4s ease, transform 0.2s ease; /* Transition fluide au focus */
        }

        input:focus {
            box-shadow: 0 0 15px rgba(76, 175, 80, 0.7); /* Halo vert fluide */
            transform: translateY(-2px); /* Légère élévation pour un effet dynamique */
            outline: none;
        }

        button {
     padding: 15px;
    background: linear-gradient(90deg, #4caf50, #66bb6a);
    color: #ffffffc1;
    border-radius: 82px;
    cursor: pointer;
    transition: background 0.3s ease, transform 0.3s ease;
        }

        button:hover {
            background: linear-gradient(90deg, #66bb6a, #4caf50); /* Inversion de gradient */
            transform: scale(1.05); /* Zoom fluide */
            text-decoration: underline;
            cursor: pointer;
            align-items:center;
        }

        .error {
            color: #ff1744;
            font-size: 12px;
            margin-top: -12px;
            margin-bottom: 15px;
            animation: shake 0.5s ease; /* Animation d'erreur, mais plus douce */
        }

        @keyframes shake {
            0% { transform: translateX(0); }
            25% { transform: translateX(-3px); }
            50% { transform: translateX(3px); }
            75% { transform: translateX(-3px); }
            100% { transform: translateX(0); }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Ajout du logo -->
        <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSPaMEBxEFKZDyKze1PbLLKgv-PZ2BJQkJd1Q&s" alt="Logo eFootball" class="logo"> <!-- Remplacez par l'URL réelle du logo -->
        
        <h1>Dribbleur Store </h1>
        
        <!-- Formulaire de connexion simplifié -->
        <?php if (isset($_GET['error'])): ?>
            <div class="error">
                <?php
                    if ($_GET['error'] === 'login') echo 'Identifiants incorrects.';
                    elseif ($_GET['error'] === 'empty') echo 'Veuillez remplir tous les champs.';
                    else echo 'Erreur inconnue.';
                ?>
            </div>
        <?php endif; ?>

        <form id="connexionForm" action="traitement_connexion.php" method="POST">

            <label for="username">Nom d'utilisateur :</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Mot de passe :</label>
            <input type="password" id="password" name="password" required>
            <div id="errorMessage" class="error"></div> <!-- Message d'erreur général -->

            <button type="submit">Se connecter</button>
            <a href="inscription.php" style=" display: inline-block;
    margin-left: 10px;
    padding: 12px 18px;
    background: #1aa75a;
    color: #ffffff;
    border-radius: 16px;
text-align: center;
    cursor: pointer;
    transition: background 0.3s 
ease, transform 0.3s 
ease;


    text-decoration: none;">S'inscrire</a>

        </form>
    </div>

    <script>
        // JavaScript pour la validation dynamique du formulaire
        document.getElementById('connexionForm').addEventListener('submit', function(event) {
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const errorMessage = document.getElementById('errorMessage');

            // Si un champ est vide, empêcher la soumission et afficher le message d'erreur
            if (username === "" || password === "") {
                event.preventDefault();event.preventDefault();
                errorMessage.textContent = "Veuillez remplir tous les champs !";
            } else {
                // Champs valides : laisser le navigateur soumettre le formulaire normalement
                errorMessage.textContent = "";
            }
        });
    </script>
</body>
</html>