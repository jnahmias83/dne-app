<?php 
include 'functions/functions.php';

$one = 1;
$query = "UPDATE dne_inputs_colors SET ".$_POST['field']." = ? WHERE id = ?";
$query = $mysqli->prepare($query);
$query->bind_param('si',$_POST['value'],$one);	
$query->execute();
?>