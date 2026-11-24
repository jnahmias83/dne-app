<?php
// Deconnexion : ferme la session DNE et renvoie vers la page de login.
if (session_status() === PHP_SESSION_NONE) {
	require_once __DIR__ . '/include/session_config.php';   // connexion gardee 30 jours
	session_start();
}

// Vide les variables de session
$_SESSION = array();

// Supprime le cookie de session cote navigateur
if (ini_get('session.use_cookies')) {
	$params = session_get_cookie_params();
	setcookie(session_name(), '', [
		'expires'  => time() - 42000,
		'path'     => $params['path'],
		'domain'   => $params['domain'],
		'secure'   => $params['secure'],
		'httponly' => $params['httponly'],
		'samesite' => isset($params['samesite']) && $params['samesite'] !== '' ? $params['samesite'] : 'Lax',
	]);
}

// Detruit la session cote serveur
session_destroy();

header('Location: login.php');
exit;
