<?php 
include 'functions/functions.php';

$query = $mysqli->prepare("SELECT * FROM dne_global_progress_status");
$query->execute();
$query->store_result();
$global_progress_status = fetch($query);

foreach($global_progress_status as $item){
	$query = $mysqli->prepare("SELECT * FROM dne_progress_status WHERE id_project = ? AND name_he = ?");
    $query->bind_param("is",$_POST['id_project'],$item->name_he);
	$query->execute();
    $query->store_result();
    $num_progress_status = $query->num_rows;
	$current_ps = fetch_unique($query);
	$current_ps_name_he = $current_ps->name_he;
	
	if($num_progress_status == 0){
		$query = "INSERT INTO dne_progress_status (id_project,name,name_he,color,bgcolor) VALUES(?,?,?,?,?)";
	    $query = $mysqli->prepare($query);
	    $query->bind_param('issss',$_POST['id_project'],$item->name,$item->name_he,$item->color,$item->bgcolor);  
	    $query->execute();
	}
	else {	
		$query = "UPDATE dne_progress_status SET name = ?,color = ?,bgcolor = ? WHERE name_he = ? AND id_project = ?";
	    $query = $mysqli->prepare($query);
	    $query->bind_param('ssssi',$item->name,$item->color,$item->bgcolor,$current_ps_name_he,$_POST['id_project']);  
	    $query->execute();
	}
}
?>