<?php
// Copy this file to config.php and fill in the credentials for your environment.
// NEVER commit config.php — it is in .gitignore.
define('DB_HOST', 'localhost');
define('DB_USER', 'YOUR_DB_USER');
define('DB_PASS', 'YOUR_DB_PASS');
define('DB_NAME', 'YOUR_DB_NAME');

// "Se connecter avec Google" : ID client OAuth "Web application" du projet Google Cloud "dne-mobile".
// Se termine par .apps.googleusercontent.com. Laisser vide desactive le bouton Google (le login mot de passe reste).
define('GOOGLE_CLIENT_ID', '');
// (App Android DNEMobile) ID client OAuth "Android". Utilise par la verification cote serveur pour accepter aussi
// les jetons emis pour l'app. Laisser vide si l'app n'utilise pas encore Google.
define('GOOGLE_ANDROID_CLIENT_ID', '');
