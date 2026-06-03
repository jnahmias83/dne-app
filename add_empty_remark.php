<?php
include 'functions/functions.php';

$is_task_priority = 0;
$remark = '';

$query = "INSERT INTO dne_tasks_followup (id_meeting,id_task_action,action_date,
          id_progress_status,remark,is_task_priority,id_user) 
		  VALUES(?,?,?,?,?,?,?)";
$query = $mysqli->prepare($query);
$query->bind_param('iisisii',$_POST['meeting_id'],$_POST['id_task_action'],
                   date('Y-m-d'),$_POST['id_progress_status'],
				   $remark,$is_task_priority,$_POST['id_user']); 
$query->execute();
?>