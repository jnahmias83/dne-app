<?php
include 'functions/functions.php';

$query = $mysqli->prepare("SELECT r.id FROM dne_responsibles r
                            INNER JOIN dne_meetings m ON (m.id_responsible = r.id OR m.id_pass_on = r.id)
                            WHERE r.id_projects_suppliers = ?");
$query->bind_param("i",$_POST['id_projects_suppliers']);
$query->execute();
$query->store_result();

if(@$query->num_rows > 0)
	echo 'notallowedtoremove';
else {
	$query = $mysqli->prepare("DELETE FROM dne_projects_suppliers WHERE id = ?");
	$query->bind_param("i",$_POST['id_projects_suppliers']);
	$query->execute();
	$query = $mysqli->prepare("DELETE FROM dne_orders WHERE id_projects_suppliers = ?");
	$query->bind_param("i",$_POST['id_projects_suppliers']);
	$query->execute();
	$query = $mysqli->prepare("DELETE FROM dne_payments WHERE id_projects_suppliers = ?");
	$query->bind_param("i",$_POST['id_projects_suppliers']);
	$query->execute();
}
?>