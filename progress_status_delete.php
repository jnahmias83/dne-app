<?php
include 'functions/functions.php';

if($_POST['global'] == 0) {
	$query = $mysqli->prepare("DELETE FROM dne_progress_status WHERE id = ?");
	$query->bind_param("i",$_POST['id']);
	$query->execute();
}
else {
	$query = $mysqli->prepare("DELETE FROM dne_global_progress_status WHERE id = ?");
	$query->bind_param("i",$_POST['id']);
	$query->execute();
}
?>