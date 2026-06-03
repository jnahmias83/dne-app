<?php
include 'functions/functions.php';

$query = $mysqli->prepare("SELECT id FROM dne_meetings WHERE id_responsible = ? OR id_pass_on = ?");
$query->bind_param("ii",$_POST['id'],$_POST['id']);
$query->execute();
$query->store_result();

if(@$query->num_rows > 0)
	echo 'notallowedtoremove';
else {
	$query = $mysqli->prepare("DELETE FROM dne_responsibles WHERE id = ?");
	$query->bind_param("i",$_POST['id']);
	$query->execute();
}
?>