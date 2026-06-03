<?php 
include "functions/functions.php";
session_start();

$one = 1;
$empty_remark = '';

if($_POST['id_meeting'] > 0) {
	$query = $mysqli->prepare("SELECT id,updated_users FROM dne_log_meeting_updates 
	                          WHERE is_remark_appears_log = ?
							  AND NOT FIND_IN_SET(?,updated_users)
							  AND id_meeting = ?");
	$query->bind_param("iii",$one,$_SESSION['id_user'],$_POST['id_meeting']);
	$query->execute();
	$query->store_result();
	$log_meeting_updates = fetch($query);
	
	foreach($log_meeting_updates as $item){
		$new_updated_users = @$item->updated_users;
		if(@$item->updated_users != '') 
			$new_updated_users .= ','.@$_SESSION['id_user'];					
		
		$query = "UPDATE dne_log_meeting_updates SET updated_users = ? WHERE id = ?";
		$query = $mysqli->prepare($query);
		$query->bind_param('si',$new_updated_users,$item->id);	
		$query->execute();
	}
	
	$query = $mysqli->prepare("SELECT id,updated_users FROM dne_log_meeting_tracking 
	                          WHERE is_remark_appears_log = ?
							  AND NOT FIND_IN_SET(?,updated_users)
							  AND id_meeting = ?");
	$query->bind_param("iii",$one,$_SESSION['id_user'],$_POST['id_meeting']);
	$query->execute();
	$query->store_result();
	$log_meeting_tracking = fetch($query);
	
	foreach($log_meeting_tracking as $item){
		$new_updated_users = @$item->updated_users;
		if(@$item->updated_users != '') 
			$new_updated_users .= ','.@$_SESSION['id_user'];					
		
		$query = "UPDATE dne_log_meeting_tracking SET updated_users = ? WHERE id = ?";
		$query = $mysqli->prepare($query);
		$query->bind_param('si',$new_updated_users,$item->id);	
		$query->execute();
	}
}
else {
	$updated_users_array = explode(',',$_POST['updated_users']);
	if(!in_array($_SESSION['id_user'],$updated_users_array))
	   array_push($updated_users_array,$_SESSION['id_user']);
	$new_updated_users = implode(',',$updated_users_array);

	$query = "UPDATE dne_log_meeting_updates SET updated_users = ? WHERE id = ?";
	$query = $mysqli->prepare($query);
	$query->bind_param('si',$new_updated_users,$_POST['log_id']);	
	$query->execute();
}
?>