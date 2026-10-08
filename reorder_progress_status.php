<?php
// Reordonne les statuts d'avancement d'un projet apres un glisser-deposer (progress_status.php).
// Recoit id_project + order (ids separes par des virgules, dans le nouvel ordre)
// et reaffecte id_display = 1..N selon cet ordre. Meme logique que reorder_chapters.php.
include 'functions/functions.php';

$id_project = (int) @$_POST['id_project'];
$order = @$_POST['order'];

if($id_project <= 0 || $order === '' || $order === null){
	echo 'invalid';
	exit;
}

$ids = array_filter(array_map('intval', explode(',', $order)));
if(empty($ids)){
	echo 'invalid';
	exit;
}

// Verifie que chaque id appartient bien a ce projet (pas de triche cross-projet).
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$types = 'i' . str_repeat('i', count($ids));
$query = $mysqli->prepare("SELECT id FROM dne_progress_status WHERE id_project = ? AND id IN ($placeholders)");
$params = array_merge([$id_project], $ids);
$bind_params = [];
foreach($params as $key => $value){
	$bind_params[] = &$params[$key];
}
array_unshift($bind_params, $types);
call_user_func_array([$query, 'bind_param'], $bind_params);
$query->execute();
$query->store_result();

if($query->num_rows !== count($ids)){
	echo 'invalid';
	exit;
}

$update = $mysqli->prepare("UPDATE dne_progress_status SET id_display = ? WHERE id = ? AND id_project = ?");
$display = 0;
foreach($ids as $status_id){
	$display++;
	$update->bind_param('iii', $display, $status_id, $id_project);
	$update->execute();
}

echo 'ok';
