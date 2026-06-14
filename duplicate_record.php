<?php
ob_start();
include 'functions/functions.php';
session_start();

$currentDate = date('Y-m-d');
$is_appears = 1;
$is_change_row_style = 0;

if($_POST['table_name'] == 'dne_meetings') {
	$query = $mysqli->prepare("SELECT * FROM dne_meetings WHERE id = ?");
	$query->bind_param("i",$_POST['id']);
	$query->execute();
	$query->store_result();
    $meeting = fetch_unique($query);
	$task_creation_date = $meeting->task_creation_date;

	$blank = '';
	$query = $mysqli->prepare("SELECT * FROM dne_progress_status
	                          WHERE id_project = ? AND name_he = ?");
	$query->bind_param("is",$_POST['id_project'],$blank);
	$query->execute();
	$query->store_result();
    $progress_status = fetch_unique($query);
	$id_progress_status = @$progress_status->id;

	$query = "SELECT * FROM dne_tasks WHERE id = ?";
    $query = $mysqli->prepare($query);
	$query->bind_param('i',$meeting->id_task);
	$query->execute();
	$query->store_result();
	$query = fetch_unique($query);
	$task_name = $query->name_he;

	$query = "SELECT * FROM dne_progress_status WHERE id = ?";
    $query = $mysqli->prepare($query);
    $query->bind_param('i',$meeting->id_progress_status);
    $query->execute();
    $query->store_result();
    $query = fetch_unique($query);
    $ps_name = $query->name_he;

	if($task_name == 'הנחיית ביצוע' || $task_name == 'סטטוס ביצוע' || $task_name == 'בקשה/שאילתה' || $task_name == 'בקרת איכות' || $ps_name == 'בהמתנה' || $ps_name == 'הנחיה/החלטה')
	   $is_change_row_style = 1;

	if($ps_name == 'בוצע/נמסר') {
		$task_creation_date = date('Y-m-d');
		$is_change_row_style = 1;
	}
	else if($ps_name == 'ארכיון') {
		$is_appears = 0;

		$query = "UPDATE dne_meetings SET status_archived_updated_date = ?
		          WHERE id = ?";
		$query = $mysqli->prepare($query);
		$query->bind_param('si',$currentDate,$_POST['id']);
		$query->execute();
	}

	else if($ps_name == 'הנחיה/החלטה') {
	   $query = "UPDATE dne_meetings SET status_decision_updated_date = ?
	             WHERE id = ?";
	   $query = $mysqli->prepare($query);
	   $query->bind_param('si',$currentDate,$_POST['id']);
	   $query->execute();
	}

	$id_parent = 0;
	if(@$_POST['is_continuous_task'] == 1)
       $id_parent = @$_POST['id'];

	$destination_date_new = date("Y-m-d", strtotime($currentDate." +7 days"));
	$query = "INSERT INTO dne_meetings (id_parent,id_user,id_project,
	          id_chapter,id_task_type,ids_rdv,subject,area,
			  id_task,id_responsible,id_pass_on,task_creation_date,
			  destination_date,id_progress_status,status_in_ex_updated_date,
			  status_late_updated_date,status_finished_updated_date,
			  status_hold_updated_date,status_archived_updated_date,
			  status_decision_updated_date,status_updated_date,
			  last_pdf_created,last_pdf_created_date,is_appears,
			  is_change_row_style,updated_date)
			  VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
	$query = $mysqli->prepare($query);
	$query->bind_param('iiiiisssiiississsssssssiis',$id_parent,$meeting->id_user,$meeting->id_project,
	                   $meeting->id_chapter,$meeting->id_task_type,$meeting->ids_rdv,$meeting->subject,
					   $meeting->area,$meeting->id_task,$meeting->id_responsible,$meeting->id_pass_on,
					   $currentDate,$destination_date_new,$id_progress_status,
					   $meeting->status_in_ex_updated_date,$meeting->status_late_updated_date,
					   $meeting->status_finished_updated_date,$meeting->status_hold_updated_date,
					   $meeting->status_archived_updated_date,$meeting->status_decision_updated_date,
					   $meeting->status_updated_date,$meeting->last_pdf_created,
					   $meeting->last_pdf_created_date,$is_appears,$is_change_row_style,$currentDate);
	$query->execute();
	$inserted_meeting = $query->insert_id;
	ob_end_clean();
	echo $inserted_meeting;

	$action = 'חדשה';
	$query = "INSERT INTO dne_log_meeting_updates (id_user,id_meeting,
		      action_date,action,destination_date,id_progress_status,
			  updated_users) VALUES(?,?,?,?,?,?,?)";
	$query = $mysqli->prepare($query);
	$query->bind_param('iisssii',$_SESSION['id_user'],$inserted_meeting,
		               $currentDate,$action,$meeting->destination_date,
					   $id_progress_status,$_SESSION['id_user']);
	$query->execute();

	if(@$_POST['is_continuous_task'] == 1 && @$_POST['progress_status'] != 'no_change') {
	    $query = $mysqli->prepare("SELECT * FROM dne_progress_status
	                              WHERE id_project = ? AND name_he = ?");
	    $progress_status_post = $_POST['progress_status'] ?? '';
	    $parent_id_project = (int)$meeting->id_project;
	    $query->bind_param("is",$parent_id_project,$progress_status_post);
	    $query->execute();
	    $query->store_result();
        $progress_status = fetch_unique($query);
	    $id_progress_status = @$progress_status->id;
		$parent_change_row_style = in_array($progress_status_post, ['בוצע/נמסר','ארכיון']) ? 1 : 0;

		if($id_progress_status > 0) {
			$query = "UPDATE dne_meetings SET id_progress_status = ?, is_change_row_style = ? WHERE id = ?";
			$query = $mysqli->prepare($query);
			$query->bind_param('iii',$id_progress_status,$parent_change_row_style,$_POST['id']);
			$query->execute();
		}
	}
}
