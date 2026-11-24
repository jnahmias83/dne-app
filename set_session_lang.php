<?php 
require_once __DIR__ . '/include/session_config.php';   // connexion gardee 30 jours
session_start();
$_SESSION['lang'] = $_POST['lang'];
?>