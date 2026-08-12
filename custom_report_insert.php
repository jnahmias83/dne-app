<?php
include 'functions/functions.php';
session_start();
error_reporting(E_ALL);
ini_set('display_errors', '1');

if($_POST['report_type'] == 'generalTasksReport') {
	$is_project_status_report = 1;
	$request_name = "כלל המשימות";
	$pdf_name = "General Tasks Report";
	$title = "כלל המשימות";
	$and_or_responsibles = 'AND';
	$is_images = 1;
	$is_colors = 1;
	$lang = !empty($_POST['lang']) ? $_POST['lang'] : 'HE';
	$columns_list = 'subject,area,description,_task,responsible,pass on,task creation,destination date,progress status';
		
	$chapters_array = array();
	$chapters_ids_array = array();
	$query = $mysqli->prepare("SELECT * FROM dne_chapters 
							   WHERE id_project = ?");
	$query->bind_param("i",$_POST['id_project']);
	$query->execute();
	$query->store_result();
	$chapters = fetch($query);
	foreach(@$chapters as $chapter){
		$chapters_array[@$chapter->id]= @$chapter->name;
		if(!in_array(@$chapter->id,$chapters_ids_array) && @$chapter->id != '')
		  array_push($chapters_ids_array,@$chapter->id);
	}

	$tasks_array = array();
	$tasks_ids_array = array();
	$query = $mysqli->prepare("SELECT * FROM dne_tasks WHERE id_project = ?");
	$query->bind_param("i",$_POST['id_project']);
	$query->execute();
	$query->store_result();
	$tasks = fetch($query);
	foreach(@$tasks as $task){
		$tasks_array[@$task->id]= @$task->name_he;
		if(@$custom_report->lang == 'EN')
			$tasks_array[@$task->id]= @$task->name;
		if(!in_array(@$task->id,$tasks_ids_array) && @$task->id != '')
		  array_push($tasks_ids_array,@$task->id);
	}

	$responsibles_array = array();
	$responsibles_ids_array = array();
	$pass_ons_array = array();
	$pass_ons_ids_array = array();
	$query = $mysqli->prepare("SELECT * FROM dne_responsibles 
							   WHERE id_project = ?");
	$query->bind_param("i",$_POST['id_project']);
	$query->execute();
	$query->store_result();
	$responsibles = fetch($query);
	foreach(@$responsibles as $responsible){
		$responsibles_array[@$responsible->id]= @$responsible->name;
		if(!in_array(@$responsible->id,$responsibles_ids_array) && @$responsible->id != '')
		  array_push($responsibles_ids_array,@$responsible->id);
		
		$pass_ons_array[@$responsible->id]= @$responsible->name; 
		if(!in_array(@$responsible->id,$pass_ons_ids_array) && @$responsible->id != '')
		  array_push($pass_ons_ids_array,@$responsible->id);	
	}

	$progress_status_array = array();
	$progress_status_ids_array = array();
	$query = $mysqli->prepare("SELECT * FROM dne_progress_status 
							   WHERE id_project = ?");
	$query->bind_param("i",$_POST['id_project']);
	$query->execute();
	$query->store_result();
	$progress_status = fetch($query);
	foreach(@$progress_status as $ps){	
		$progress_status_array[@$ps->id]= @$ps->name_he;
			
		if(@$custom_report->lang == 'EN')
			$progress_status_array[@$ps->id]= @$ps->name;
			
		if(!in_array(@$ps->id,$progress_status_ids_array) && @$ps->id != '' && @$ps->name_he != 'ארכיון' && @$ps->name != 'Archive')
			array_push($progress_status_ids_array,@$ps->id);
	}
		
	$chapters_list = implode(',',@$chapters_ids_array);
	$tasks_list = implode(',',@$tasks_ids_array);
	$responsibles_list = implode(',',@$responsibles_ids_array);
	$pass_ons_list = implode(',',@$pass_ons_ids_array);
	$progress_status_list = implode(',',@$progress_status_ids_array);
		
	$sql_str = "SELECT c.name AS name,m.id AS id,
		        m.is_priority AS is_priority,m.id_user AS id_user,
				m.id_chapter AS id_chapter,m.subject AS subject,
				m.ids_rdv AS ids_rdv,m.area,m.description,m.id_task,
				m.id_responsible,m.id_pass_on,m.task_creation_date,
				m.destination_date,m.id_progress_status,
				m.updated_date AS updated_date,m.image1 AS image1,
				m.is_appears_img1 AS is_appears_img1,
				m.image1_width AS image1_width,
				m.image1_height AS image1_height,m.image2 AS image2,		 
				m.is_appears_img2 AS is_appears_img2,
				m.image2_width AS image2_width,
				m.image2_height AS image2_height,
				m.is_change_row_style AS is_change_row_style,
				m.id_track_responsible AS id_track_responsible,
				m.track_type AS track_type,
				m.reminder_time AS reminder_time,
                m.reminder_date AS reminder_date,
                m.is_agrees AS is_agrees,m.is_reminds AS is_reminds				
				FROM dne_meetings m 
				LEFT JOIN dne_chapters c ON m.id_chapter = c.id 
				LEFT JOIN dne_tasks t ON m.id_task = t.id
                LEFT JOIN dne_responsibles r ON m.id_responsible = r.id
				LEFT JOIN dne_progress_status ps ON m.id_progress_status = ps.id					
			    WHERE m.id_project =".@$_POST['id_project']." 
				AND m.is_appears = 1";
		
    if(sizeof(@$chapters_array) > 0)		
		$sql_str.= " AND m.id_chapter IN(".@$chapters_list.")";	
	if(sizeof(@$tasks_array) > 0)	
	    $sql_str.= " AND m.id_task IN(".@$tasks_list.")";
	if(sizeof(@$responsibles_array) > 0)	
		$sql_str.= " AND m.id_responsible IN(".@$responsibles_list.")";
	if(sizeof(@$pass_ons_array) > 0)
		$sql_str.= " AND m.id_pass_on IN(".@$pass_ons_list.")";
	if(sizeof(@$progress_status_array) > 0)
		$sql_str.= " AND m.id_progress_status IN(".@$progress_status_list.")";
		
	$is_all_chapters_checked = $is_all_responsibles_checked = $is_all_pass_ons_checked = 1;			
		
	$query = "INSERT INTO dne_custom_reports (id_project,request_name,
		      pdf_name,is_all_chapters_checked,chapters_list,
		      tasks_list,is_all_responsibles_checked,responsibles_list,
			  is_all_pass_ons_checked,pass_ons_list,progress_status_list,
			  and_or_responsibles,sql_str,title,is_images,is_colors,lang,
	          columns_list,is_project_status_report) 
			  VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
	$query = $mysqli->prepare($query);
	$query->bind_param('ississisisssssiissi',$_POST['id_project'],
		                $request_name,$pdf_name,$is_all_chapters_checked,
						$chapters_list,$tasks_list,
                        $is_all_responsibles_checked,$responsibles_list,
						$is_all_pass_ons_checked,$pass_ons_list,
						$progress_status_list,$and_or_responsibles,
						$sql_str,$title,$is_images,$is_colors,$lang,
						$columns_list,$is_project_status_report);   
	$query->execute();
	echo "inserted";	
}
else if($_POST['report_type'] == 'myTasksReport') {
	$is_project_status_report = 0;
	$request_name = "משימות שלי";
	$pdf_name = "My Tasks Report";
	$title = "משימות שלי";
	$and_or_responsibles = 'AND';
	$is_images = 1;
	$is_colors = 1;
	$lang = !empty($_POST['lang']) ? $_POST['lang'] : 'HE';
	$columns_list = 'subject,area,description,_task,responsible,pass on,task creation,destination date,progress status';
		
	$chapters_array = array();
	$chapters_ids_array = array();
	$query = $mysqli->prepare("SELECT * FROM dne_chapters WHERE id_project = ?");
	$query->bind_param("i",$_POST['id_project']);
	$query->execute();
	$query->store_result();
	$chapters = fetch($query);
	foreach(@$chapters as $chapter){
		$chapters_array[@$chapter->id]= @$chapter->name;
		if(!in_array(@$chapter->id,$chapters_ids_array) && @$chapter->id != '')
		   array_push($chapters_ids_array,@$chapter->id);
	}

	$tasks_array = array();
	$tasks_ids_array = array();
	$query = $mysqli->prepare("SELECT * FROM dne_tasks 
							   WHERE id_project = ?");
	$query->bind_param("i",$_POST['id_project']);
	$query->execute();
	$query->store_result();
	$tasks = fetch($query);
	foreach(@$tasks as $task){
		$tasks_array[@$task->id]= @$task->name_he;
		if(@$custom_report->lang == 'EN')
			$tasks_array[@$task->id]= @$task->name;
		if(!in_array(@$task->id,$tasks_ids_array) && @$task->id != '')
		  array_push($tasks_ids_array,@$task->id);
	}

	$responsibles_array = array();
	$responsibles_ids_array = array();
	$pass_ons_array = array();
	$pass_ons_ids_array = array();
	$query = $mysqli->prepare("SELECT * FROM dne_responsibles 
							  WHERE id_project = ?");
	$query->bind_param("i",$_POST['id_project']);
	$query->execute();
	$query->store_result();
	$responsibles = fetch($query);
	foreach(@$responsibles as $responsible){
		$responsibles_array[@$responsible->id]= @$responsible->name;
		if(!in_array(@$responsible->id,$responsibles_ids_array) && @$responsible->id != '')
		  array_push($responsibles_ids_array,@$responsible->id);
		
		$pass_ons_array[@$responsible->id]= @$responsible->name; 
		if(!in_array(@$responsible->id,$pass_ons_ids_array) && @$responsible->id != '')
		  array_push($pass_ons_ids_array,@$responsible->id);	
	}

	$progress_status_array = array();
	$progress_status_ids_array = array();
	$query = $mysqli->prepare("SELECT * FROM dne_progress_status 
							  WHERE id_project = ?");
	$query->bind_param("i",$_POST['id_project']);
	$query->execute();
	$query->store_result();
	$progress_status = fetch($query);
	foreach(@$progress_status as $ps){	
		$progress_status_array[@$ps->id]= @$ps->name_he;
		
		if(@$custom_report->lang == 'EN')
			$progress_status_array[@$ps->id]= @$ps->name;
		
		if(!in_array(@$ps->id,$progress_status_ids_array) && @$ps->id != '' && @$ps->name_he != 'ארכיון' && @$ps->name != 'Archive')
		  array_push($progress_status_ids_array,@$ps->id);
	}
		
	$chapters_list = implode(',',@$chapters_ids_array);
	$tasks_list = implode(',',@$tasks_ids_array);
	$responsibles_list = implode(',',@$responsibles_ids_array);
	$pass_ons_list = implode(',',@$pass_ons_ids_array);
	$progress_status_list = implode(',',@$progress_status_ids_array);
		
	$sql_str = "SELECT c.name AS name,m.id AS id,
				m.is_priority AS is_priority,m.id_user AS id_user,
				m.id_chapter AS id_chapter,m.subject AS subject,
				m.ids_rdv AS ids_rdv,m.area,m.description,m.id_task,
				m.id_responsible,m.id_pass_on,m.task_creation_date,
				m.destination_date,m.id_progress_status,
				m.updated_date AS updated_date,m.image1 AS image1,
				m.is_appears_img1 AS is_appears_img1,
				m.image1_width AS image1_width,
				m.image1_height AS image1_height,m.image2 AS image2,		 
				m.is_appears_img2 AS is_appears_img2,
				m.image2_width AS image2_width,
				m.image2_height AS image2_height,
				m.is_change_row_style AS is_change_row_style,
				m.id_track_responsible AS id_track_responsible,
				m.track_type AS track_type,
				m.reminder_time AS reminder_time, 
				m.reminder_date AS reminder_date,
                m.is_agrees AS is_agrees,m.is_reminds AS is_reminds	
				FROM dne_meetings m 
				LEFT JOIN dne_chapters c ON m.id_chapter = c.id 
				LEFT JOIN dne_tasks t ON m.id_task = t.id
				LEFT JOIN dne_progress_status ps ON m.id_progress_status = ps.id					
				LEFT JOIN dne_responsibles r ON m.id_responsible = r.id
				WHERE m.id_project = ".@$_POST['id_project']."							
				AND m.is_appears = 1";
		
	if(sizeof(@$chapters_array) > 0)		
	   $sql_str.= " AND m.id_chapter IN(".@$chapters_list.")";	
	if(sizeof(@$tasks_array) > 0)	
	   $sql_str.= " AND m.id_task IN(".@$tasks_list.")";
	if(sizeof(@$responsibles_array) > 0)	
	   $sql_str.= " AND m.id_responsible IN(".@$responsibles_list.")";
	if(sizeof(@$pass_ons_array) > 0)
	   $sql_str.= " AND m.id_pass_on IN(".@$pass_ons_list.")";
	if(sizeof(@$progress_status_array) > 0)
	   $sql_str.= " AND m.id_progress_status IN(".@$progress_status_list.")";
   
    $sql_str.= " AND r.id_user = {CURRENT_USER_ID}";
		
	$is_all_chapters_checked = $is_all_responsibles_checked = $is_all_pass_ons_checked = 1;			
	
	$query = "INSERT INTO dne_custom_reports (id_project,request_name,
			  pdf_name,is_all_chapters_checked,chapters_list,
			  tasks_list,is_all_responsibles_checked,responsibles_list,
			  is_all_pass_ons_checked,pass_ons_list,progress_status_list,
			  and_or_responsibles,sql_str,title,is_images,is_colors,lang,
			  columns_list,is_project_status_report) 
			  VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
	$query = $mysqli->prepare($query);
	$query->bind_param('ississisisssssiissi',$_POST['id_project'],
					   $request_name,$pdf_name,$is_all_chapters_checked,
					   $chapters_list,$tasks_list,
					   $is_all_responsibles_checked,$responsibles_list,
					   $is_all_pass_ons_checked,$pass_ons_list,
					   $progress_status_list,$and_or_responsibles,
					   $sql_str,$title,$is_images,$is_colors,$lang,
					   $columns_list,$is_project_status_report);   
	$query->execute();
	echo "inserted";
}
else if($_POST['report_type'] == 'managementTeamReport') {
	$is_project_status_report = 0;
	$request_name = "צוות ניהול";
	$pdf_name = "Management Team Report";
	$title = "צוות ניהול";
	$and_or_responsibles = 'AND';
	$is_images = 1;
	$is_colors = 1;
	$lang = !empty($_POST['lang']) ? $_POST['lang'] : 'HE';
	$columns_list = 'subject,area,description,_task,responsible,pass on,task creation,destination date,progress status';

	$chapters_array = array();
	$chapters_ids_array = array();
	$query = $mysqli->prepare("SELECT * FROM dne_chapters WHERE id_project = ?");
	$query->bind_param("i",$_POST['id_project']);
	$query->execute();
	$query->store_result();
	$chapters = fetch($query);
	foreach(@$chapters as $chapter){
		$chapters_array[@$chapter->id]= @$chapter->name;
		if(!in_array(@$chapter->id,$chapters_ids_array) && @$chapter->id != '')
		   array_push($chapters_ids_array,@$chapter->id);
	}

	$tasks_array = array();
	$tasks_ids_array = array();
	$query = $mysqli->prepare("SELECT * FROM dne_tasks WHERE id_project = ?");
	$query->bind_param("i",$_POST['id_project']);
	$query->execute();
	$query->store_result();
	$tasks = fetch($query);
	foreach(@$tasks as $task){
		$tasks_array[@$task->id]= @$task->name_he;
		if(@$custom_report->lang == 'EN')
			$tasks_array[@$task->id]= @$task->name;
		if(!in_array(@$task->id,$tasks_ids_array) && @$task->id != '')
		  array_push($tasks_ids_array,@$task->id);
	}

	$responsibles_array = array();
	$responsibles_ids_array = array();
	$pass_ons_array = array();
	$pass_ons_ids_array = array();
	$query = $mysqli->prepare("SELECT * FROM dne_responsibles WHERE id_project = ?");
	$query->bind_param("i",$_POST['id_project']);
	$query->execute();
	$query->store_result();
	$responsibles = fetch($query);
	foreach(@$responsibles as $responsible){
		$responsibles_array[@$responsible->id]= @$responsible->name;
		if(!in_array(@$responsible->id,$responsibles_ids_array) && @$responsible->id != '')
		  array_push($responsibles_ids_array,@$responsible->id);

		$pass_ons_array[@$responsible->id]= @$responsible->name;
		if(!in_array(@$responsible->id,$pass_ons_ids_array) && @$responsible->id != '')
		  array_push($pass_ons_ids_array,@$responsible->id);
	}

	$progress_status_array = array();
	$progress_status_ids_array = array();
	$query = $mysqli->prepare("SELECT * FROM dne_progress_status WHERE id_project = ?");
	$query->bind_param("i",$_POST['id_project']);
	$query->execute();
	$query->store_result();
	$progress_status = fetch($query);
	foreach(@$progress_status as $ps){
		$progress_status_array[@$ps->id]= @$ps->name_he;

		if(@$custom_report->lang == 'EN')
			$progress_status_array[@$ps->id]= @$ps->name;

		if(!in_array(@$ps->id,$progress_status_ids_array) && @$ps->id != '' && @$ps->name_he != 'ארכיון' && @$ps->name != 'Archive')
		  array_push($progress_status_ids_array,@$ps->id);
	}

	$chapters_list = implode(',',@$chapters_ids_array);
	$tasks_list = implode(',',@$tasks_ids_array);
	$responsibles_list = implode(',',@$responsibles_ids_array);
	$pass_ons_list = implode(',',@$pass_ons_ids_array);
	$progress_status_list = implode(',',@$progress_status_ids_array);

	$sql_str = "SELECT c.name AS name,m.id AS id,
				m.is_priority AS is_priority,m.id_user AS id_user,
				m.id_chapter AS id_chapter,m.subject AS subject,
				m.ids_rdv AS ids_rdv,m.area,m.description,m.id_task,
				m.id_responsible,m.id_pass_on,m.task_creation_date,
				m.destination_date,m.id_progress_status,
				m.updated_date AS updated_date,m.image1 AS image1,
				m.is_appears_img1 AS is_appears_img1,
				m.image1_width AS image1_width,
				m.image1_height AS image1_height,m.image2 AS image2,
				m.is_appears_img2 AS is_appears_img2,
				m.image2_width AS image2_width,
				m.image2_height AS image2_height,
				m.is_change_row_style AS is_change_row_style,
				m.id_track_responsible AS id_track_responsible,
				m.track_type AS track_type,
				m.reminder_time AS reminder_time,
				m.reminder_date AS reminder_date,
				m.is_agrees AS is_agrees,m.is_reminds AS is_reminds
				FROM dne_meetings m
				LEFT JOIN dne_chapters c ON m.id_chapter = c.id
				LEFT JOIN dne_tasks t ON m.id_task = t.id
				LEFT JOIN dne_progress_status ps ON m.id_progress_status = ps.id
				LEFT JOIN dne_responsibles r ON m.id_responsible = r.id
				WHERE m.id_project = ".@$_POST['id_project']."
				AND m.is_appears = 1";

	if(sizeof(@$chapters_array) > 0)
	   $sql_str.= " AND m.id_chapter IN(".@$chapters_list.")";
	if(sizeof(@$tasks_array) > 0)
	   $sql_str.= " AND m.id_task IN(".@$tasks_list.")";
	if(sizeof(@$responsibles_array) > 0)
	   $sql_str.= " AND m.id_responsible IN(".@$responsibles_list.")";
	if(sizeof(@$pass_ons_array) > 0)
	   $sql_str.= " AND m.id_pass_on IN(".@$pass_ons_list.")";
	if(sizeof(@$progress_status_array) > 0)
	   $sql_str.= " AND m.id_progress_status IN(".@$progress_status_list.")";

	$sql_str.= " AND r.role IN('project_manager','inspector')";

	$is_all_chapters_checked = $is_all_responsibles_checked = $is_all_pass_ons_checked = 1;

	$query = "INSERT INTO dne_custom_reports (id_project,request_name,
			  pdf_name,is_all_chapters_checked,chapters_list,
			  tasks_list,is_all_responsibles_checked,responsibles_list,
			  is_all_pass_ons_checked,pass_ons_list,progress_status_list,
			  and_or_responsibles,sql_str,title,is_images,is_colors,lang,
			  columns_list,is_project_status_report)
			  VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
	$query = $mysqli->prepare($query);
	$query->bind_param('ississisisssssiissi',$_POST['id_project'],
					   $request_name,$pdf_name,$is_all_chapters_checked,
					   $chapters_list,$tasks_list,
					   $is_all_responsibles_checked,$responsibles_list,
					   $is_all_pass_ons_checked,$pass_ons_list,
					   $progress_status_list,$and_or_responsibles,
					   $sql_str,$title,$is_images,$is_colors,$lang,
					   $columns_list,$is_project_status_report);
	$query->execute();
	echo "inserted";
}
else if($_POST['report_type'] == 'entrepreneurTeamReport') {
	$is_project_status_report = 0;
	$request_name = "צוות יזם";
	$pdf_name = "Entrepreneur Team Report";
	$title = "צוות יזם";
	$and_or_responsibles = 'AND';
	$is_images = 1;
	$is_colors = 1;
	$lang = !empty($_POST['lang']) ? $_POST['lang'] : 'HE';
	$columns_list = 'subject,area,description,_task,responsible,pass on,task creation,destination date,progress status';

	$chapters_array = array();
	$chapters_ids_array = array();
	$query = $mysqli->prepare("SELECT * FROM dne_chapters WHERE id_project = ?");
	$query->bind_param("i",$_POST['id_project']);
	$query->execute();
	$query->store_result();
	$chapters = fetch($query);
	foreach(@$chapters as $chapter){
		$chapters_array[@$chapter->id]= @$chapter->name;
		if(!in_array(@$chapter->id,$chapters_ids_array) && @$chapter->id != '')
		   array_push($chapters_ids_array,@$chapter->id);
	}

	$tasks_array = array();
	$tasks_ids_array = array();
	$query = $mysqli->prepare("SELECT * FROM dne_tasks WHERE id_project = ?");
	$query->bind_param("i",$_POST['id_project']);
	$query->execute();
	$query->store_result();
	$tasks = fetch($query);
	foreach(@$tasks as $task){
		$tasks_array[@$task->id]= @$task->name_he;
		if(@$custom_report->lang == 'EN')
			$tasks_array[@$task->id]= @$task->name;
		if(!in_array(@$task->id,$tasks_ids_array) && @$task->id != '')
		  array_push($tasks_ids_array,@$task->id);
	}

	$responsibles_array = array();
	$responsibles_ids_array = array();
	$pass_ons_array = array();
	$pass_ons_ids_array = array();
	$query = $mysqli->prepare("SELECT * FROM dne_responsibles WHERE id_project = ?");
	$query->bind_param("i",$_POST['id_project']);
	$query->execute();
	$query->store_result();
	$responsibles = fetch($query);
	foreach(@$responsibles as $responsible){
		$responsibles_array[@$responsible->id]= @$responsible->name;
		if(!in_array(@$responsible->id,$responsibles_ids_array) && @$responsible->id != '')
		  array_push($responsibles_ids_array,@$responsible->id);

		$pass_ons_array[@$responsible->id]= @$responsible->name;
		if(!in_array(@$responsible->id,$pass_ons_ids_array) && @$responsible->id != '')
		  array_push($pass_ons_ids_array,@$responsible->id);
	}

	$progress_status_array = array();
	$progress_status_ids_array = array();
	$query = $mysqli->prepare("SELECT * FROM dne_progress_status WHERE id_project = ?");
	$query->bind_param("i",$_POST['id_project']);
	$query->execute();
	$query->store_result();
	$progress_status = fetch($query);
	foreach(@$progress_status as $ps){
		$progress_status_array[@$ps->id]= @$ps->name_he;

		if(@$custom_report->lang == 'EN')
			$progress_status_array[@$ps->id]= @$ps->name;

		if(!in_array(@$ps->id,$progress_status_ids_array) && @$ps->id != '' && @$ps->name_he != 'ארכיון' && @$ps->name != 'Archive')
		  array_push($progress_status_ids_array,@$ps->id);
	}

	$chapters_list = implode(',',@$chapters_ids_array);
	$tasks_list = implode(',',@$tasks_ids_array);
	$responsibles_list = implode(',',@$responsibles_ids_array);
	$pass_ons_list = implode(',',@$pass_ons_ids_array);
	$progress_status_list = implode(',',@$progress_status_ids_array);

	$sql_str = "SELECT c.name AS name,m.id AS id,
				m.is_priority AS is_priority,m.id_user AS id_user,
				m.id_chapter AS id_chapter,m.subject AS subject,
				m.ids_rdv AS ids_rdv,m.area,m.description,m.id_task,
				m.id_responsible,m.id_pass_on,m.task_creation_date,
				m.destination_date,m.id_progress_status,
				m.updated_date AS updated_date,m.image1 AS image1,
				m.is_appears_img1 AS is_appears_img1,
				m.image1_width AS image1_width,
				m.image1_height AS image1_height,m.image2 AS image2,
				m.is_appears_img2 AS is_appears_img2,
				m.image2_width AS image2_width,
				m.image2_height AS image2_height,
				m.is_change_row_style AS is_change_row_style,
				m.id_track_responsible AS id_track_responsible,
				m.track_type AS track_type,
				m.reminder_time AS reminder_time,
				m.reminder_date AS reminder_date,
				m.is_agrees AS is_agrees,m.is_reminds AS is_reminds
				FROM dne_meetings m
				LEFT JOIN dne_chapters c ON m.id_chapter = c.id
				LEFT JOIN dne_tasks t ON m.id_task = t.id
				LEFT JOIN dne_progress_status ps ON m.id_progress_status = ps.id
				LEFT JOIN dne_responsibles r ON m.id_responsible = r.id
				LEFT JOIN dne_responsibles rp ON m.id_pass_on = rp.id
				WHERE m.id_project = ".@$_POST['id_project']."
				AND m.is_appears = 1";

	if(sizeof(@$chapters_array) > 0)
	   $sql_str.= " AND m.id_chapter IN(".@$chapters_list.")";
	if(sizeof(@$tasks_array) > 0)
	   $sql_str.= " AND m.id_task IN(".@$tasks_list.")";
	if(sizeof(@$responsibles_array) > 0)
	   $sql_str.= " AND m.id_responsible IN(".@$responsibles_list.")";
	if(sizeof(@$pass_ons_array) > 0)
	   $sql_str.= " AND m.id_pass_on IN(".@$pass_ons_list.")";
	if(sizeof(@$progress_status_array) > 0)
	   $sql_str.= " AND m.id_progress_status IN(".@$progress_status_list.")";

	$sql_str.= " AND (r.role IN('entrepreneur','entrepreneur_team') OR rp.role IN('entrepreneur','entrepreneur_team'))";

	$is_all_chapters_checked = $is_all_responsibles_checked = $is_all_pass_ons_checked = 1;

	$query = "INSERT INTO dne_custom_reports (id_project,request_name,
			  pdf_name,is_all_chapters_checked,chapters_list,
			  tasks_list,is_all_responsibles_checked,responsibles_list,
			  is_all_pass_ons_checked,pass_ons_list,progress_status_list,
			  and_or_responsibles,sql_str,title,is_images,is_colors,lang,
			  columns_list,is_project_status_report)
			  VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
	$query = $mysqli->prepare($query);
	$query->bind_param('ississisisssssiissi',$_POST['id_project'],
					   $request_name,$pdf_name,$is_all_chapters_checked,
					   $chapters_list,$tasks_list,
					   $is_all_responsibles_checked,$responsibles_list,
					   $is_all_pass_ons_checked,$pass_ons_list,
					   $progress_status_list,$and_or_responsibles,
					   $sql_str,$title,$is_images,$is_colors,$lang,
					   $columns_list,$is_project_status_report);
	$query->execute();
	echo "inserted";
}
else if($_POST['report_type'] == 'trackingReport') {
	$is_project_status_report = 0;
	$request_name = "במעקב";
	$pdf_name = "Tracking Report";
	$title = "במעקב";
	$and_or_responsibles = 'AND';
	$is_images = 1;
	$is_colors = 1;
	$lang = !empty($_POST['lang']) ? $_POST['lang'] : 'HE';
	$columns_list = 'subject,area,description,_task,responsible,pass on,task creation,destination date,progress status';

	$chapters_array = array();
	$chapters_ids_array = array();
	$query = $mysqli->prepare("SELECT * FROM dne_chapters WHERE id_project = ?");
	$query->bind_param("i",$_POST['id_project']);
	$query->execute();
	$query->store_result();
	$chapters = fetch($query);
	foreach(@$chapters as $chapter){
		$chapters_array[@$chapter->id]= @$chapter->name;
		if(!in_array(@$chapter->id,$chapters_ids_array) && @$chapter->id != '')
		   array_push($chapters_ids_array,@$chapter->id);
	}

	$tasks_array = array();
	$tasks_ids_array = array();
	$query = $mysqli->prepare("SELECT * FROM dne_tasks WHERE id_project = ?");
	$query->bind_param("i",$_POST['id_project']);
	$query->execute();
	$query->store_result();
	$tasks = fetch($query);
	foreach(@$tasks as $task){
		$tasks_array[@$task->id]= @$task->name_he;
		if(@$custom_report->lang == 'EN')
			$tasks_array[@$task->id]= @$task->name;
		if(!in_array(@$task->id,$tasks_ids_array) && @$task->id != '')
		  array_push($tasks_ids_array,@$task->id);
	}

	$responsibles_array = array();
	$responsibles_ids_array = array();
	$pass_ons_array = array();
	$pass_ons_ids_array = array();
	$query = $mysqli->prepare("SELECT * FROM dne_responsibles WHERE id_project = ?");
	$query->bind_param("i",$_POST['id_project']);
	$query->execute();
	$query->store_result();
	$responsibles = fetch($query);
	foreach(@$responsibles as $responsible){
		$responsibles_array[@$responsible->id]= @$responsible->name;
		if(!in_array(@$responsible->id,$responsibles_ids_array) && @$responsible->id != '')
		  array_push($responsibles_ids_array,@$responsible->id);

		$pass_ons_array[@$responsible->id]= @$responsible->name;
		if(!in_array(@$responsible->id,$pass_ons_ids_array) && @$responsible->id != '')
		  array_push($pass_ons_ids_array,@$responsible->id);
	}

	$progress_status_array = array();
	$progress_status_ids_array = array();
	$query = $mysqli->prepare("SELECT * FROM dne_progress_status WHERE id_project = ?");
	$query->bind_param("i",$_POST['id_project']);
	$query->execute();
	$query->store_result();
	$progress_status = fetch($query);
	foreach(@$progress_status as $ps){
		$progress_status_array[@$ps->id]= @$ps->name_he;

		if(@$custom_report->lang == 'EN')
			$progress_status_array[@$ps->id]= @$ps->name;

		if(!in_array(@$ps->id,$progress_status_ids_array) && @$ps->id != '' && @$ps->name_he != 'ארכיון' && @$ps->name != 'Archive')
		  array_push($progress_status_ids_array,@$ps->id);
	}

	$chapters_list = implode(',',@$chapters_ids_array);
	$tasks_list = implode(',',@$tasks_ids_array);
	$responsibles_list = implode(',',@$responsibles_ids_array);
	$pass_ons_list = implode(',',@$pass_ons_ids_array);
	$progress_status_list = implode(',',@$progress_status_ids_array);

	$sql_str = "SELECT c.name AS name,m.id AS id,
				m.is_priority AS is_priority,m.id_user AS id_user,
				m.id_chapter AS id_chapter,m.subject AS subject,
				m.ids_rdv AS ids_rdv,m.area,m.description,m.id_task,
				m.id_responsible,m.id_pass_on,m.task_creation_date,
				m.destination_date,m.id_progress_status,
				m.updated_date AS updated_date,m.image1 AS image1,
				m.is_appears_img1 AS is_appears_img1,
				m.image1_width AS image1_width,
				m.image1_height AS image1_height,m.image2 AS image2,
				m.is_appears_img2 AS is_appears_img2,
				m.image2_width AS image2_width,
				m.image2_height AS image2_height,
				m.is_change_row_style AS is_change_row_style,
				m.id_track_responsible AS id_track_responsible,
				m.track_type AS track_type,
				m.reminder_time AS reminder_time,
				m.reminder_date AS reminder_date,
				m.is_agrees AS is_agrees,m.is_reminds AS is_reminds
				FROM dne_meetings m
				LEFT JOIN dne_chapters c ON m.id_chapter = c.id
				LEFT JOIN dne_tasks t ON m.id_task = t.id
				LEFT JOIN dne_progress_status ps ON m.id_progress_status = ps.id
				LEFT JOIN dne_responsibles r ON m.id_responsible = r.id
				WHERE m.id_project = ".@$_POST['id_project']."
				AND m.is_appears = 1";

	if(sizeof(@$chapters_array) > 0)
	   $sql_str.= " AND m.id_chapter IN(".@$chapters_list.")";
	if(sizeof(@$tasks_array) > 0)
	   $sql_str.= " AND m.id_task IN(".@$tasks_list.")";
	if(sizeof(@$responsibles_array) > 0)
	   $sql_str.= " AND m.id_responsible IN(".@$responsibles_list.")";
	if(sizeof(@$pass_ons_array) > 0)
	   $sql_str.= " AND m.id_pass_on IN(".@$pass_ons_list.")";
	if(sizeof(@$progress_status_array) > 0)
	   $sql_str.= " AND m.id_progress_status IN(".@$progress_status_list.")";

	$sql_str.= " AND m.track_type = 1 AND m.id_track_responsible <> 0";

	$is_all_chapters_checked = $is_all_responsibles_checked = $is_all_pass_ons_checked = 1;

	$query = "INSERT INTO dne_custom_reports (id_project,request_name,
			  pdf_name,is_all_chapters_checked,chapters_list,
			  tasks_list,is_all_responsibles_checked,responsibles_list,
			  is_all_pass_ons_checked,pass_ons_list,progress_status_list,
			  and_or_responsibles,sql_str,title,is_images,is_colors,lang,
			  columns_list,is_project_status_report)
			  VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
	$query = $mysqli->prepare($query);
	$query->bind_param('ississisisssssiissi',$_POST['id_project'],
					   $request_name,$pdf_name,$is_all_chapters_checked,
					   $chapters_list,$tasks_list,
					   $is_all_responsibles_checked,$responsibles_list,
					   $is_all_pass_ons_checked,$pass_ons_list,
					   $progress_status_list,$and_or_responsibles,
					   $sql_str,$title,$is_images,$is_colors,$lang,
					   $columns_list,$is_project_status_report);
	$query->execute();
	echo "inserted";
}
else {
	if(empty($_POST['request_name']) || empty($_POST['pdf_name'])) {
	   echo "empty";
    }
	else {
		if (containsHebrew($_POST['pdf_name'])) 
          echo "hebrewchars";
		else {
			if($_POST['id'] == 0) {
			   $query = "INSERT INTO dne_custom_reports (id_project,
			              id_supplier,is_include_pass_on_tasks,
			              request_name,pdf_name,subject,area,description,
						  is_all_chapters_checked,chapters_list,tasks_list,
						  is_all_responsibles_checked,responsibles_list,
						  is_all_pass_ons_checked,pass_ons_list,
						  progress_status_list,period_creation_date,
						  creation_date_start,creation_date_end,
						  period_destination_date,destination_date_start,
						  destination_date_end,and_or_responsibles,sql_str,
						  title,subtitle,is_images,is_colors,lang,
						  period_new_tasks,columns_list) 
						  VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
				$query = $mysqli->prepare($query);
				$query->bind_param('iiisssssissisissssssssssssiisss',
								   $_POST['id_project'],
								   $_POST['id_supplier'],
								   $_POST['is_include_pass_on_tasks'],
								   $_POST['request_name'],
								   $_POST['pdf_name'],
								   $_POST['subject'],
								   $_POST['area'],
								   $_POST['description'],
								   $_POST['is_all_chapters_checked'],
								   $_POST['chapters_list'],
								   $_POST['tasks_list'],
								   $_POST['is_all_responsibles_checked'],
								   $_POST['responsibles_list'],
								   $_POST['is_all_pass_ons_checked'],
								   $_POST['pass_ons_list'],
								   $_POST['progress_status_list'],
								   $_POST['period_creation_date'],
								   $_POST['creation_date_start'],
								   $_POST['creation_date_end'],
								   $_POST['period_destination_date'],
								   $_POST['destination_date_start'],
								   $_POST['destination_date_end'],
								   $_POST['and_or_responsibles'],
								   $_POST['sql_str'],
								   $_POST['title'],
								   $_POST['subtitle'],
								   $_POST['is_images'],
								   $_POST['is_colors'],
								   $_POST['lang'],
								   $_POST['period_new_tasks'],
								   $_POST['columns_list']);   
				$query->execute();
				echo "inserted";
			}
			else if($_POST['id'] > 0) {
				$query = "UPDATE dne_custom_reports SET id_project = ?,
				          id_supplier = ?,is_include_pass_on_tasks = ?,
						  request_name = ?,pdf_name = ?,subject = ?,area = ?,
						  description = ?,is_all_chapters_checked = ?,
						  chapters_list = ?,tasks_list = ?,
						  is_all_responsibles_checked = ?,
						  responsibles_list = ?,is_all_pass_ons_checked = ?,
						  pass_ons_list = ?,progress_status_list = ?,
						  period_creation_date = ?,creation_date_start = ?,
						  creation_date_end = ?,period_destination_date = ?,
						  destination_date_start = ?,destination_date_end = ?,
						  and_or_responsibles = ?,sql_str = ?,title = ?,
						  subtitle = ?,is_images = ?,is_colors = ?,
						  lang = ?,period_new_tasks = ?,columns_list = ? 
						  WHERE id = ?";
				$query = $mysqli->prepare($query);
				$query->bind_param('iiisssssissisissssssssssssiisssi',
								   $_POST['id_project'],
								   $_POST['id_supplier'],
								   $_POST['is_include_pass_on_tasks'],
								   $_POST['request_name'],
								   $_POST['pdf_name'],
								   $_POST['subject'],
								   $_POST['area'],
								   $_POST['description'],
								   $_POST['is_all_chapters_checked'],
								   $_POST['chapters_list'],
								   $_POST['tasks_list'],
								   $_POST['is_all_responsibles_checked'],
								   $_POST['responsibles_list'],
								   $_POST['is_all_pass_ons_checked'],
								   $_POST['pass_ons_list'],
								   $_POST['progress_status_list'],
								   $_POST['period_creation_date'],
								   $_POST['creation_date_start'],
								   $_POST['creation_date_end'],
								   $_POST['period_destination_date'],
								   $_POST['destination_date_start'],
								   $_POST['destination_date_end'],
								   $_POST['and_or_responsibles'],
								   $_POST['sql_str'],
								   $_POST['title'],
								   $_POST['subtitle'],
								   $_POST['is_images'],
								   $_POST['is_colors'],
								   $_POST['lang'],
								   $_POST['period_new_tasks'],
								   $_POST['columns_list'],
								   $_POST['id']);	
			   $query->execute();
			   echo "updated";
		   }
		}
	}
}
?>