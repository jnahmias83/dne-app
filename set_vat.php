<?php 
include 'functions/functions.php';

$query = "UPDATE dne_vat SET vat = ?";
$query = $mysqli->prepare($query);
$query->bind_param('d',$_POST['vat']);	
$query->execute();
?>