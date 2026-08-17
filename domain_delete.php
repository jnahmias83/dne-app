<?php
include 'functions/functions.php';

$query = $mysqli->prepare("SELECT id FROM dne_suppliers WHERE id_field_of_work = ?");
$query->bind_param("i",$_POST['id']);
$query->execute();
$query->store_result();

if(@$query->num_rows > 0)
	echo 'notallowedtoremove';
else {
	$query = $mysqli->prepare("DELETE FROM dne_sup_field_of_work WHERE id = ?");
	$query->bind_param("i",$_POST['id']);
	$query->execute();
}
?>