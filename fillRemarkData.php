<?php 
include 'functions/functions.php';

$query = "SELECT id FROM dne_tasks_actions WHERE name_he = ?";
$query = $mysqli->prepare($query);
$query->bind_param('s',$_POST['task_action']);   
$query->execute();
$query->store_result();
$query = fetch_unique($query);
$id_task_action = @$query->id; 

$query = "SELECT id_user,id_progress_status FROM dne_meetings WHERE id = ?";
$query = $mysqli->prepare($query);
$query->bind_param('i',$_POST['meeting_id']);   
$query->execute();
$query->store_result();
$query = fetch_unique($query);
$id_user = $query->id_user;
$id_progress_status = $query->id_progress_status;

$is_remark_appears_pdf = 0;
$is_task_priority = 0;

$query = "INSERT INTO dne_tasks_followup (id_meeting,id_task_action,action_date,
          id_progress_status,remark,is_remark_appears_pdf,is_task_priority,id_user,
		  reminder_date) VALUES(?,?,?,?,?,?,?,?,?)";
$query = $mysqli->prepare($query);
$query->bind_param('iisisiiis',$_POST['meeting_id'],$id_task_action,date('Y-m-d'),
                   $id_progress_status,$_POST['remark'],$is_remark_appears_pdf,
				   $is_task_priority,$id_user,$_POST['reminder_date']); 
$query->execute();

$query = "UPDATE dne_meetings SET reminder_time = ? WHERE id = ?";
$query = $mysqli->prepare($query);
$query->bind_param('ii',$_POST['reminder_time'],$_POST['meeting_id']);	
$query->execute();
?>