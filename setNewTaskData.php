<?php 
include 'functions/functions.php';

$query = "UPDATE dne_global_bgcolor_new_task SET bgcolor= ?";
$query = $mysqli->prepare($query);
$query->bind_param('s',$_POST['bgcolor']);	
$query->execute();
?>