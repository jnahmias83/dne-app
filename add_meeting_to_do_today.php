<?php
include 'functions/functions.php';
session_start();

$is_active_tracking = 1; 
$empty_date = '0000-00-00';

if(isset($_POST['toAddRememberTracking']) && $_POST['toAddRememberTracking'] == 1){
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
		$query = $mysqli->prepare("SELECT tdt.id FROM dne_to_do_today tdt
			LEFT JOIN dne_meetings m ON tdt.id_meeting = m.id
			LEFT JOIN dne_progress_status ps ON m.id_progress_status = ps.id
			WHERE tdt.id_user = ?
			AND ps.name_he <> 'ארכיון'
			AND ps.name_he <> 'בוצע/נמסר'
			AND ps.name_he <> 'בהמתנה'");
		$query->bind_param("i",$_SESSION['id_user']);
		$query->execute();
		$query->store_result();

		if($query->num_rows >= $_POST['max_num_tasks'])
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