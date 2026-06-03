<?php 
include 'functions/functions.php';

$query = $mysqli->prepare("SELECT * FROM dne_global_tasks");
$query->execute();
$query->store_result();
$global_tasks = fetch($query);

foreach($global_tasks as $item) {
	$query = $mysqli->prepare("SELECT * FROM dne_tasks WHERE id_project = ? AND name_he = ?");
    $query->bind_param("is",$_POST['id_project'],$item->name_he);
	$query->execute();
    $query->store_result();
    $num_task = $query->num_rows;
	$current_task = fetch_unique($query);
	
	if($num_task == 0) {
		$query = "INSERT INTO dne_tasks (id_project,name,name_he,color,bgcolor) VALUES(?,?,?,?,?)";
	    $query = $mysqli->prepare($query);
	    $query->bind_param('issss',$_POST['id_project'],$item->name,$item->name_he,$item->color,$item->bgcolor);  
	    $query->execute();
	}
	else {
		$query = "UPDATE dne_tasks SET name = ?, color = ?,bgcolor = ? WHERE name_he = ? AND id_project = ?";
	    $query = $mysqli->prepare($query);
	    $query->bind_param('ssssi',$item->name,$item->color,$item->bgcolor,$current_task->name_he,$_POST['id_project']);  
	    $query->execute();
	}
}
?>