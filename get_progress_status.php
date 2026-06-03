<?php
include 'functions/functions.php';

$query = $mysqli->prepare("SELECT name_he,color,bgcolor FROM dne_progress_status WHERE id = ?");
$query->bind_param("i", $_POST['id_progress_status']);
$query->execute();
$progress_status = fetch_unique($query);
$progress_status_name_he = @$progress_status->name_he;
if($progress_status_name_he == ' ')
	$progress_status_name_he = '(ללא)';

echo json_encode([
	'progress_status_name_he' => $progress_status_name_he,
	'progress_status_color' => $progress_status->color,
	'progress_status_bgcolor' => $progress_status->bgcolor
], JSON_UNESCAPED_UNICODE);
?>