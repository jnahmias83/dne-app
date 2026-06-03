<?php 
include 'functions/functions.php';

$query = $mysqli->prepare("SELECT is_priority,track_type FROM dne_meetings WHERE id = ?");
$query->bind_param('i',$_POST['id_meeting']);	
$query->execute(); 
$query->store_result();
$meeting = fetch_unique($query);
echo $meeting->is_priority.'-'.$meeting->track_type;
?>