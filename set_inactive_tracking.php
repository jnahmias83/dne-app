<?php 
include 'functions/functions.php';

$inactive_tracking = 0;
$empty_date = '0000-00-00';
$query = "UPDATE dne_meetings SET track_type = ?,reminder_time = ?,reminder_date = ? WHERE id = ?";
$query = $mysqli->prepare($query);
$query->bind_param('iisi',$inactive_tracking,$inactive_tracking,$empty_date,$_POST['meeting_id']);	
$query->execute();

$query = $mysqli->prepare("DELETE FROM dne_log_meeting_tracking WHERE id_meeting = ?");
$query->bind_param("i",$_POST['meeting_id']);
$query->execute();

$query = $mysqli->prepare("DELETE FROM dne_to_do_today WHERE id_meeting = ?");
$query->bind_param("i",$_POST['meeting_id']);
$query->execute();
?>