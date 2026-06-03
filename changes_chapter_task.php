<?php
include 'functions/functions.php';

$query = "SELECT id_task,id_responsible,id_pass_on FROM dne_chapters 
		  WHERE id = ?";
$query = $mysqli->prepare($query);
$query->bind_param('i',$_POST['id_chapter']);   
$query->execute();
$query->store_result();
$default_task_data = fetch_unique($query);

if($default_task_data->id_task == 0) {
	$query = "SELECT id_task,id_responsible,id_pass_on 
	          FROM dne_latest_tasks_data 
			  WHERE id_project = ? AND id_user = ?";
    $query = $mysqli->prepare($query);
    $query->bind_param('ii',$_POST['id_project'],$_POST['id_user']);   
    $query->execute();
    $query->store_result();
	$latest_task_data = fetch_unique($query);
	
	if($latest_task_data->id_task != 0) {
		echo $latest_task_data->id_task.'-'.$latest_task_data->id_responsible.'-'.$latest_task_data->id_pass_on;
	}
	else {
		echo 'nodefaultdata';
	}
}
else {
	echo $default_task_data->id_task.'-'.$default_task_data->id_responsible.'-'.$default_task_data->id_pass_on;
}
?>