<?php
session_start();

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulaire d'Inscription eFootball</title>
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
            background-color: #ffffff;
            color: #000000;
            transition: box-shadow 0.4s ease, transform 0.2s ease; /* Transition fluide au focus */
        }

        input:focus {
            box-shadow: 0 0 15px rgba(76, 175, 80, 0.7); /* Halo vert fluide */
            transform: translateY(-2px); /* Légère élévation pour un effet dynamique */
            outline: none;
        }

        button {
            padding: 12px;
            background: linear-gradient(90deg, #4caf50, #66bb6a); /* Gradient pour un aspect plus fluide */
            color: #ffffff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s ease, transform 0.3s ease; /* Transition fluide au hover */
        }

        button:hover {
            background: linear-gradient(90deg, #66bb6a, #4caf50); /* Inversion de gradient */
            transform: scale(1.05); /* Zoom fluide */
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
    </style>
</head>
<body>
    <div class="container">
        <h1>Formulaire d'Inscription eFootball</h1>
        <form id="inscriptionForm" action="traitement.php" method="POST" >

            <label for="prenom">Prénom :</label>
            <input type="text" id="prenom" name="prenom" required>
            
            <label for="nom">Nom  :</label>
            <input type="text" id="nom" name="nom" required>
            
            <label for="email">Adresse e-mail :</label>
            <input type="email" id="email" name="email" required>
            
            <label for="username">Nom d'utilisateur :</label>
            <input type="text" id="username" name="username" required>
            
            <label for="password">Mot de passe :</label>
            <input type="password" id="password" name="password" required>
            
            <label for="confirmPassword">Confirmer le mot de passe :</label>
            <input type="password" id="confirmPassword" name="confirm_password" required>
            <div id="passwordError" class="error"></div>
            
            <label>
                <input type="checkbox" id="terms" name="terms" required> J'accepte les termes et conditions d'eFootball.
            </label>
            
            <button type="submit">S'inscrire</button>
        </form>
    </div>

    <script>
        // JavaScript pour la validation dynamique
        document.getElementById('inscriptionForm').addEventListener('submit', function(event) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const passwordError = document.getElementById('passwordError');

            if (password !== confirmPassword) {
                // Empêche la soumission seulement si les mots de passe ne correspondent pas
                event.preventDefault();
                passwordError.textContent = "Les mots de passe ne correspondent pas !";
                document.getElementById('confirmPassword').focus();
            } else {
                passwordError.textContent = "";
                // Laisser le formulaire se soumettre normalement
            }
        });
    </script>
</body>
</html>
