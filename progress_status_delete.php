<?php
include 'functions/functions.php';

if($_POST['global'] == 0) {
	$query = $mysqli->prepare("SELECT id FROM dne_meetings WHERE id_progress_status = ?");
	$query->bind_param("i",$_POST['id']);
	$query->execute();
	$query->store_result();

	if(@$query->num_rows > 0)
		echo 'notallowedtoremove';
	else {
		$query = $mysqli->prepare("DELETE FROM dne_progress_status WHERE id = ?");
		$query->bind_param("i",$_POST['id']);
		$query->execute();
	}
}
else {
	$query = $mysqli->prepare("DELETE FROM dne_global_progress_status WHERE id = ?");
	$query->bind_param("i",$_POST['id']);
	$query->execute();
}
?>