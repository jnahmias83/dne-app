<?php
include 'functions/functions.php';

$query = $mysqli->prepare("SELECT name_he, nickname FROM dne_projects WHERE id = ?");
$query->bind_param('i', $_POST['project_id']);
$query->execute();
$query->store_result();
$project = fetch_unique($query);

echo json_encode([
	'name_he'  => @$project->name_he,
	'nickname' => @$project->nickname,
], JSON_UNESCAPED_UNICODE);
?>
