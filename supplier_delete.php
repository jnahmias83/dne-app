<?php
include 'functions/functions.php';
error_reporting(E_ALL);
ini_set('display_errors', '1');

$query = $mysqli->prepare("SELECT id FROM dne_projects_suppliers WHERE id_supplier = ?");
$query->bind_param("i",$_POST['id_supplier']);
$query->execute();
$query->store_result();
$suppliers = fetch($query);

foreach($suppliers as $item) {
	$query = $mysqli->prepare("DELETE FROM dne_orders WHERE id_projects_suppliers = ?");    
	$query->bind_param("i",$item->id);
    $query->execute();	
	$query = $mysqli->prepare("DELETE FROM dne_payments WHERE id_projects_suppliers = ?");
    $query->bind_param("i",$item->id);
    $query->execute();
}

$query = $mysqli->prepare("DELETE FROM dne_projects_suppliers WHERE id_supplier = ?");
$query->bind_param("i",$_POST['id_supplier']);
$query->execute();

$query = $mysqli->prepare("DELETE FROM dne_suppliers WHERE id = ?");
$query->bind_param("i",$_POST['id_supplier']);
$query->execute();
?>