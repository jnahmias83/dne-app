<?php 
include 'functions/functions.php';
error_reporting(E_ALL);
ini_set('display_errors', '1');

$ids_array = array();
$ids_display_array = array();

$query = $mysqli->prepare("SELECT * FROM dne_chapters 
                          WHERE id_project = ? ORDER BY id_display");
$query->bind_param("i",$_POST['id_project']);
$query->execute();
$query->store_result();
$chapters = fetch($query);

foreach($chapters as $item){
	array_push($ids_array,$item->id);
	array_push($ids_display_array,$item->id_display);
}

if($_POST['direction'] == 'down'){
	for($i=0;$i<sizeof($ids_array);$i++){
		if($ids_display_array[$i] == $_POST['id_display']){
			$query = "UPDATE dne_chapters SET id_display = ? WHERE id = ?";
			$query = $mysqli->prepare($query);
			$query->bind_param('ii',$ids_display_array[$i+1],$ids_array[$i]);	
			$query->execute();
			
			$query = "UPDATE dne_chapters SET id_display = ? WHERE id = ?";
			$query = $mysqli->prepare($query);
			$query->bind_param('ii',$ids_display_array[$i],$ids_array[$i+1]);	
			$query->execute();

			break;
		}
	}
    
	$query = $mysqli->prepare("SELECT * FROM dne_chapters 
	                          WHERE id_project = ? ORDER BY id_display");
	$query->bind_param("i",$_POST['id_project']);
	$query->execute();
	$query->store_result();
	$chapters = fetch($query);

	$ids_array = array();
	$ids_display_array = array();

	foreach($chapters as $item){
		array_push($ids_array,$item->id);
		array_push($ids_display_array,$item->id_display);
	}
}

if($_POST['direction'] == 'up'){
    for($i=0;$i<sizeof($ids_array);$i++){
		if($ids_display_array[$i] == $_POST['id_display']){
			$query = "UPDATE dne_chapters SET id_display = ? WHERE id = ?";
			$query = $mysqli->prepare($query);
			$query->bind_param('ii',$ids_display_array[$i-1],$ids_array[$i]);	
			$query->execute();
			
			$query = "UPDATE dne_chapters SET id_display = ? WHERE id = ?";
			$query = $mysqli->prepare($query);
			$query->bind_param('ii',$ids_display_array[$i],$ids_array[$i-1]);	
			$query->execute();
			
			break;
		}
	}
	
	$query = $mysqli->prepare("SELECT * FROM dne_chapters 
	                          WHERE id_project = ? ORDER BY id_display");
	$query->bind_param("i",$_POST['id_project']);
	$query->execute();
	$query->store_result();
	$chapters = fetch($query);

	$ids_array = array();
	$ids_display_array = array();

	foreach($chapters as $item){
		array_push($ids_array,$item->id);
		array_push($ids_display_array,$item->id_display);
	}
}
?>