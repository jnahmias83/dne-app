<?php
include 'functions/functions.php';

$query = $mysqli->prepare("SELECT id FROM dne_meetings WHERE id_chapter = ?");
$query->bind_param("i",$_POST['id']);
$query->execute();
$query->store_result();

if(@$query->num_rows > 0)
	echo 'notallowedtoremove';
else {
	$query = $mysqli->prepare("DELETE FROM dne_chapters WHERE id = ?");
	$query->bind_param("i",$_POST['id']);
	$query->execute();
}
?>