<?php
include 'functions/functions.php';
error_reporting(E_ALL);
ini_set('display_errors', '1');	

$query = $mysqli->prepare("SELECT s.name_he,s.phone AS phone,s.email_office AS email_office
                          FROM dne_projects_suppliers ps
						  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
						  WHERE ps.id = ?");
$query->bind_param("i",$_POST['id_projects_suppliers']);
$query->execute();
$query->store_result();
$supplier = fetch_unique($query);

echo json_encode([
	'name_he' => $supplier->name_he,
	'phone' => $supplier->phone,
	'email' => $supplier->email_office
], JSON_UNESCAPED_UNICODE);
?>