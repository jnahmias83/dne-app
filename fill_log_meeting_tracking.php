<?php 
include 'functions/functions.php';
session_start();

$ids_remark_tracking_checked_array = explode(',',$_POST['ids_remark_tracking_checked']);

$one = 1;
$empty_remark = '';
$query = $mysqli->prepare("SELECT id
	                       FROM dne_log_meeting_tracking
						   WHERE id_meeting = ?							
						   AND is_remark_appears_log = ?
						   AND remark <> ?");
$query->bind_param("iis",$_POST['meeting_id'],$one,$empty_remark);
$query->execute();
$query->store_result();
$log_meeting_tracking_num_rows = $query->num_rows;
$log_meeting_tracking = fetch($query);
	
if(!empty($ids_remark_tracking_checked_array) && $ids_remark_tracking_checked_array[0] !== ''){
	foreach($log_meeting_tracking as $item){
		$is_remark_appears_log = 0;

		if(in_array(@$item->id,$ids_remark_tracking_checked_array))
			$is_remark_appears_log = 1;

		$query = "UPDATE dne_log_meeting_tracking
			      SET is_remark_appears_log = ? WHERE id = ?";
		$query = $mysqli->prepare($query);
		$query->bind_param('si',$is_remark_appears_log,$item->id);
		$query->execute();
	}
}

$query = "INSERT INTO dne_log_meeting_tracking (id_user,id_meeting,action_date,remark,updated_users,is_remark_appears_log)
          VALUES(?,?,?,?,?,?)";
$query = $mysqli->prepare($query);
$query->bind_param('iisssi',$_SESSION['id_user'],$_POST['meeting_id'],date('Y-m-d'),
                   htmlspecialchars($_POST['remark']),$_SESSION['id_user'],$one);
$query->execute();

$query = $mysqli->prepare("SELECT id FROM dne_to_do_today 
						   WHERE id_user = ? AND id_meeting = ?");
$query->bind_param("ii",$_SESSION['id_user'],$_POST['meeting_id']);
$query->execute();
$query->store_result();
		
$list_from = 'reminders';
$is_reminder = 1;
		
if($query->num_rows == 0){
	$query = "INSERT INTO dne_to_do_today (id_user,id_meeting,list_from,pin_date,is_reminder)
			  VALUES(?,?,?,?,?)";
	$query = $mysqli->prepare($query);
	$query->bind_param('iissi',$_SESSION['id_user'],$_POST['meeting_id'],$list_from,$_POST['reminder_date'],
					   $is_reminder);
	$query->execute();
}

$track_type = isset($_POST['track_type']) ? (int)$_POST['track_type'] : 1;

$query = "UPDATE dne_meetings SET reminder_time = ?,reminder_date = ?,track_type = ?,id_track_responsible = ?
          WHERE id = ?";
$query = $mysqli->prepare($query);
$query->bind_param('isiii',$_POST['reminder_time'],$_POST['reminder_date'],
                   $track_type,$_POST['id_user'],$_POST['meeting_id']);
$query->execute();
?>