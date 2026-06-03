<?php 
include 'functions/functions.php';

$all_ids_to_edit_array = explode(',',$_POST['all_ids_to_edit']);

$zero = 0;
$query = "UPDATE dne_log_current_report SET id_custom_report = ?,id_rdv_report = ? WHERE id_project = ?";
$query = $mysqli->prepare($query);
$query->bind_param('iii',$zero,$_POST['id_rdv'],$_POST['id_project']);
$query->execute();

foreach ($all_ids_to_edit_array as $id_meeting) {
    $query = $mysqli->prepare("SELECT ids_rdv FROM dne_meetings WHERE id = ?");
    $query->bind_param("i",$id_meeting);
	$query->execute();
	$query->store_result();
	$meeting = fetch_unique($query);
	$ids_rdv = $meeting->ids_rdv;
    
	$new_ids_rdv = $ids_rdv;
	
	if($ids_rdv == '') {
	    $new_ids_rdv = $_POST['id_rdv'];		
	}
    else {
		if(!in_array((string)$_POST['id_rdv'],explode(',',$ids_rdv))) 
		    $new_ids_rdv = $ids_rdv.','.$_POST['id_rdv'];
        else
            $new_ids_rdv = $ids_rdv;			
	}

    $query = "UPDATE dne_meetings SET ids_rdv = ? WHERE id = ?";
	$query = $mysqli->prepare($query);
    $query->bind_param('si',$new_ids_rdv,$id_meeting);	
	$query->execute();	
}
?>