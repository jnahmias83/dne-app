<?php
include 'functions/functions.php';

$query = $mysqli->prepare("DELETE FROM dne_projects_suppliers WHERE id = ?");
$query->bind_param("i",$_POST['id_projects_suppliers']);
$query->execute();
$query = $mysqli->prepare("DELETE FROM dne_orders WHERE id_projects_suppliers = ?");
$query->bind_param("i",$_POST['id_projects_suppliers']);
$query->execute();
$query = $mysqli->prepare("DELETE FROM dne_payments WHERE id_projects_suppliers = ?");
$query->bind_param("i",$_POST['id_projects_suppliers']);
$query->execute(); 
?>