<?php
include 'functions/functions.php';

$query = $mysqli->prepare("DELETE FROM dne_sup_field_of_work WHERE id = ?");
$query->bind_param("i",$_POST['id']);
$query->execute();
?>