<?php 
include 'functions/functions.php';

$query = $mysqli->prepare("SELECT * FROM dne_rdv WHERE id = ?");
$query->bind_param("i",$_POST['id_rdv']);
$query->execute();
$query->store_result();
$rdv = fetch_unique($query);

echo json_encode([
	'id_meetings_types' => $rdv->id_meetings_types,
	'rdv_lang' => $rdv->rdv_lang,
	'rdv_name' => $rdv->rdv_name,
	'rdv_persons' => $rdv->rdv_persons
], JSON_UNESCAPED_UNICODE);
?>