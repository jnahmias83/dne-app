<?php
include "functions/functions.php";
session_start();

$one = 1;

$meeting_ids = isset($_POST['meeting_ids']) ? $_POST['meeting_ids'] : array();

foreach($meeting_ids as $id_meeting){
	if($id_meeting == '') continue;

	$query = $mysqli->prepare("SELECT id,updated_users FROM dne_log_meeting_updates
	                          WHERE is_remark_appears_log = ?
							  AND NOT FIND_IN_SET(?,updated_users)
							  AND id_meeting = ?");
	$query->bind_param("iii",$one,$_SESSION['id_user'],$id_meeting);
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
	$query->bind_param("iii",$one,$_SESSION['id_user'],$id_meeting);
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
?>
