<?php
include 'functions/functions.php';

if(@$_POST['type'] == 'meeting_updates'){
	$query = "UPDATE dne_log_meeting_updates SET is_remark_appears_log = ? WHERE id = ?";
	$query = $mysqli->prepare($query);
	$query->bind_param('ii',$_POST['is_remark_appears_log'],$_POST['id']);	
	$query->execute();
}
else {
	$query = "UPDATE dne_log_meeting_tracking SET is_remark_appears_log = ? WHERE id = ?";
	$query = $mysqli->prepare($query);
	$query->bind_param('ii',$_POST['is_remark_appears_log'],$_POST['id']);	
	$query->execute();
}
?>