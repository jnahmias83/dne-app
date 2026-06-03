<?php
include 'functions/functions.php'; 
session_start();

if($_POST['id_meeting'] > 0){
	$query = $mysqli->prepare("DELETE FROM dne_to_do_today 
							  WHERE id_user = ? AND id_meeting = ?");
	$query->bind_param("ii",$_SESSION['id_user'],$_POST['id_meeting']);
	$query->execute();
}

if($_POST['delete_all'] == 1){
	$query = $mysqli->prepare("DELETE FROM dne_to_do_today WHERE id_user = ?");
	$query->bind_param("i",$_SESSION['id_user']);
	$query->execute();
}

$list_from_reminders = 'reminders';
if($_POST['time_tasks_appear'] > 0){
    $query = $mysqli->prepare("DELETE FROM dne_to_do_today 
	                           WHERE id_user = ? 
							   AND DATE(pin_date) <= DATE_SUB(CURDATE(),INTERVAL ? DAY)
							   AND list_from <> ?");
	$query->bind_param('iis',$_SESSION['id_user'],$_POST['time_tasks_appear'],$list_from_reminders);
	$query->execute();
}
?>