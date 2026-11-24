<?php 
require_once __DIR__ . '/include/session_config.php';   // connexion gardee 30 jours
session_start();
include 'functions/functions.php';

$query = "SELECT id_meeting FROM dne_connexion_data WHERE id_user = ?";
$query = $mysqli->prepare($query);
$query->bind_param('i',$_SESSION['id_user']);   
$query->execute();
$query->store_result();
$query = fetch_unique($query);
$id_meeting = $query->id_meeting;
echo $id_meeting;
?>