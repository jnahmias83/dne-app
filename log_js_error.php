<?php
// Diagnostic : enregistre les erreurs JS cote client (tablette/navigateurs non testables ici)
// dans le journal d'erreurs serveur, pour pouvoir les consulter sans acces physique a l'appareil.
header('Content-Type: text/plain; charset=utf-8');

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

$message = @$data['message'] ?? '';
$source = @$data['source'] ?? '';
$lineno = @$data['lineno'] ?? '';
$colno = @$data['colno'] ?? '';
$stack = @$data['stack'] ?? '';
$page = @$data['page'] ?? '';
$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

error_log("JS_CLIENT_ERROR page=$page message=$message source=$source line=$lineno col=$colno ua=$ua stack=$stack");

echo 'ok';
