<?php
include 'functions/functions.php';

$reserved_names = ['כלל המשימות','משימות שלי','צוות ניהול','צוות יזם','במעקב'];

$query = $mysqli->prepare("SELECT id_project, request_name FROM dne_custom_reports WHERE id = ?");
$query->bind_param("i",$_POST['id']);
$query->execute();
$query->store_result();
$report = fetch_unique($query);
$is_base_report = in_array(@$report->request_name, $reserved_names);

$id_project = @$report->id_project;
$query = $mysqli->prepare("SELECT id FROM dne_meetings WHERE id_project = ? LIMIT 1");
$query->bind_param("i",$id_project);
$query->execute();
$query->store_result();
$is_active_project = @$query->num_rows > 0;

if($is_base_report && $is_active_project)
	echo 'basereport_and_activeproject';
else if($is_base_report)
	echo 'basereport';
else if($is_active_project)
	echo 'activeproject';
else {
	$query = $mysqli->prepare("DELETE FROM dne_custom_reports WHERE id = ?");
	$query->bind_param("i",$_POST['id']);
	$query->execute();
}
?>