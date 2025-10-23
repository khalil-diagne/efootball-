<?php
// Remplacez 'nouveau_mot_de_passe_secret' par le mot de passe que vous voulez utiliser.
$passwordToHash = 'ibeu10';

 $hashedPassword = password_hash($passwordToHash, PASSWORD_DEFAULT);

echo "Copiez ce hash : <br><br>";
echo "<textarea rows='3' cols='80' readonly>" . htmlspecialchars($hashedPassword) . "</textarea>";
?>
