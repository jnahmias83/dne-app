<?php
include 'functions/functions.php';
session_start();

$is_active_tracking = 1; 
$empty_date = '0000-00-00';

if($_POST['toAddRememberTracking'] == 1){
	$query = $mysqli->prepare("SELECT id,reminder_date FROM dne_meetings 
							   WHERE id_track_responsible = ? 
							   AND track_type = ?
							   AND reminder_date <> ?
							   AND DATE(reminder_date) <= CURDATE()");
	$query->bind_param("iis",$_SESSION['id_user'],$is_active_tracking,$empty_date);
	$query->execute();
	$query->store_result();
	$reminder_meetings = fetch($query); 
	
	$list_from = 'reminders';
	$is_reminder = 1;
	
	foreach($reminder_meetings as $rm){
		$query = $mysqli->prepare("SELECT id FROM dne_to_do_today 
							       WHERE id_user = ? AND id_meeting = ?");
	    $query->bind_param("ii",$_SESSION['id_user'],$rm->id);
	    $query->execute();
	    $query->store_result();
		
		if($query->num_rows == 0){
			$query = "INSERT INTO dne_to_do_today (id_user,id_meeting,list_from,pin_date,is_reminder)
					  VALUES(?,?,?,?,?)";
			$query = $mysqli->prepare($query);
			$query->bind_param('iissi',$_SESSION['id_user'],$rm->id,$list_from,$rm->reminder_date,$is_reminder);
			$query->execute();
		}
	}
}
else {
	$query = $mysqli->prepare("SELECT id FROM dne_to_do_today 
							   WHERE id_user = ? AND id_meeting = ?");
	$query->bind_param("ii",$_SESSION['id_user'],$_POST['id_meeting']);
	$query->execute();
	$query->store_result();

	if($query->num_rows > 0) 
		echo 'exists';
	else {
		$query = $mysqli->prepare("SELECT id FROM dne_to_do_today WHERE id_user = ? ");
		$query->bind_param("i",$_SESSION['id_user']);
		$query->execute();
		$query->store_result();
		
		if($query->num_rows == $_POST['max_num_tasks']) 
			echo 'maxnumtasksreached';
		else {
			$query = "INSERT INTO dne_to_do_today (id_user,id_meeting,list_from,pin_date) VALUES(?,?,?,?)";
			$query = $mysqli->prepare($query);
			$query->bind_param('iiss',$_SESSION['id_user'],$_POST['id_meeting'],$_POST['list_from'],
			                   date('Y-m-d'));
			$query->execute();
			echo 'inserted';
		}
	}
}
?>