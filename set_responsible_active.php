<?php 
include 'functions/functions.php';

$query = "UPDATE dne_responsibles SET is_active = ? WHERE id = ?";
$query = $mysqli->prepare($query);
$query->bind_param('si',$_POST['is_active'],$_POST['id_responsible']);	
$query->execute();
?>