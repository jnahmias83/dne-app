<?php 
include 'functions/functions.php';
if(!$_POST['is_priority']) {
   $is_priority = 1;
   $query = $mysqli->prepare("SELECT id FROM dne_meetings WHERE id_project = ? AND is_priority = ?");
   $query->bind_param("ii",$_POST['id_project'],$is_priority);
   $query->execute();
   $query->store_result();
   $num_priority_tasks = $query->num_rows;
   if($num_priority_tasks >= 5 && !$_POST['is_priority']) {
	   echo 'fivepriorities'; 
   }	   
   else if($num_priority_tasks < 5) {
	    $query = "UPDATE dne_meetings SET is_priority = ? WHERE id = ?";
		$query = $mysqli->prepare($query);
		$query->bind_param('ii',$is_priority,$_POST['id_meeting']);	
		$query->execute();
   }
}
else if($_POST['is_priority']){
	$is_priority = 0;
	$query = "UPDATE dne_meetings SET is_priority = ? WHERE id = ?";
	$query = $mysqli->prepare($query);
	$query->bind_param('ii',$is_priority,$_POST['id_meeting']);	
	$query->execute();
}
?>