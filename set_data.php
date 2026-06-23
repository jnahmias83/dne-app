<?php 
include 'functions/functions.php';
session_start();

$where_ids = ' IN(';
if($_POST['all_ids_to_edit'] == '') 
	$where_ids .= $_POST['meeting_id'];
else {
	$where_ids .= $_POST['all_ids_to_edit'];
	$where_ids_array = explode(',',$_POST['all_ids_to_edit']);
}
$where_ids .= ')';
$all_ids_to_edit_array = explode(',',$_POST['all_ids_to_edit']);

$log_remark = '';
if($_POST['remark'] != '')
	$log_remark = htmlspecialchars($_POST['remark']);

$action = "סטטוס/יעד/הערה";

$is_change_row_style = 0;
$is_appears = 1;

$query = "SELECT * FROM dne_custom_reports WHERE id_project = ?";
$query = $mysqli->prepare($query);
$query->bind_param('i',$_POST['id_project']);   
$query->execute();
$query->store_result();
$custom_reports = fetch($query);

if($_POST['field'] == "subject"){
	$query = "UPDATE dne_meetings SET subject = ? WHERE id = ?";
	$query = $mysqli->prepare($query);
	$query->bind_param('si',htmlspecialchars($_POST['subject']),
	                   $_POST['meeting_id']);	
	$query->execute();
	
	$screen_type = 'popup';
	$list_name = 'meetings';
}

else if($_POST['field'] == "area"){
	$query = "UPDATE dne_meetings SET area = ? WHERE id = ?";
	$query = $mysqli->prepare($query);
	$query->bind_param('si',htmlspecialchars($_POST['area']),
	                   $_POST['meeting_id']);	
	$query->execute();
}

else if($_POST['field'] == "description"){
	$query = "UPDATE dne_meetings SET description = ? WHERE id = ?";
	$query = $mysqli->prepare($query);
	$query->bind_param('si',$_POST['description'],$_POST['meeting_id']);	
	$query->execute();
}

else if($_POST['field'] == "id_task"){
	$query = "UPDATE dne_meetings SET id_task = ? WHERE id".@$where_ids;
	$query = $mysqli->prepare($query);
	$query->bind_param('i',$_POST['id_task']);	
	$query->execute();
	
	foreach ($custom_reports as $item){ 
		$position_where = strpos($item->sql_str,"WHERE");
		$where_length = strlen($item->sql_str)-$position_where;
		$where_part_sql = substr($item->sql_str,$position_where,$where_length); 
		$where_part_sql_array = explode(' AND ',$where_part_sql);
		
		for($i=0;$i < sizeof($where_part_sql_array);$i++) {
			if(strpos($where_part_sql_array[$i],'id_task') !== false && strpos($where_part_sql_array[$i],'id_task_type') === false) {
				$position_task_in = strpos($where_part_sql_array[$i],"IN(");
				$where_task_in_length = strlen($where_part_sql_array[$i])-$position_task_in;
				$where_part_task_in = substr($where_part_sql_array[$i],$position_task_in,$where_task_in_length);
				$task_ids = substr($where_part_task_in,3,-1); 
				if(strpos($task_ids,$_POST['id_task']) == false)
				   $task_ids .= ','.$_POST['id_task'];							   
				$new_task_in = 'm.id_task IN('.$task_ids.')';
				$where_part_sql_array[$i] = $new_task_in;             						   
			}	  
		}
		
		$new_sql_str = 'SELECT c.name AS name,m.id AS id,
						m.id_user AS id_user,
						m.id_task_type AS id_task_type,
						m.id_chapter AS id_chapter,m.subject AS subject,
						m.ids_rdv AS ids_rdv,m.area,m.description,
						m.id_task,m.id_responsible,m.id_pass_on,
						m.task_creation_date,m.destination_date,
						m.id_progress_status,
						m.updated_date AS updated_date,
						m.image1 AS image1,
						m.is_appears_img1 AS is_appears_img1,
						m.image1_width AS image1_width,
						m.image1_height AS image1_height,
						m.image2 AS image2,
						m.image2_width AS image2_width,
						m.image2_height AS image2_height,
						m.is_appears_img2 AS is_appears_img2,
						m.is_change_row_style AS is_change_row_style,
                        m.track_type AS track_type,
                        m.id_track_responsible AS id_track_responsible,
                        m.reminder_time AS reminder_time,
						m.reminder_date AS reminder_date,
                        m.is_agrees AS is_agrees,m.is_reminds AS is_reminds						
						FROM dne_meetings m 
						LEFT JOIN dne_chapters c ON m.id_chapter = c.id 
						LEFT JOIN dne_tasks t ON m.id_task = t.id 
						LEFT JOIN dne_progress_status ps ON m.id_progress_status = ps.id
						LEFT JOIN dne_responsibles r ON m.id_responsible = r.id '.
						implode(' AND ',$where_part_sql_array);						
			   
		$tasks_list = @$item->tasks_list;
		if(strpos($tasks_list,$_POST['id_task']) == false){
			if($tasks_list == "")
				$tasks_list = $_POST['id_task'];
			else 
				$tasks_list .= ','.$_POST['id_task'];
		}

		$query = "UPDATE dne_custom_reports SET tasks_list = ?,sql_str = ? 
		          WHERE id = ?";
		$query = $mysqli->prepare($query);
		$query->bind_param('ssi',$tasks_list,$new_sql_str,$item->id);	
		$query->execute();	
	}
}

else if($_POST['field'] == "id_responsible"){
	$query = "UPDATE dne_meetings SET id_responsible = ?
	          WHERE id".@$where_ids;
	$query = $mysqli->prepare($query);
	$query->bind_param('i',$_POST['id_responsible']);
	$query->execute();
}

else if($_POST['field'] == "id_pass_on"){
	$query = "UPDATE dne_meetings SET id_pass_on = ? WHERE id".@$where_ids;
	$query = $mysqli->prepare($query);
	$query->bind_param('i',$_POST['id_pass_on']);
	$query->execute();
}

else if($_POST['field'] == "task_creation_date"){
	$query = "UPDATE dne_meetings SET task_creation_date = ?
	          WHERE id".@$where_ids;
	$query = $mysqli->prepare($query);
	$query->bind_param('s',$_POST['task_creation_date']);	
	$query->execute();
}

else if($_POST['field'] == "destination_date"){
    $ids_remark_checked_array = explode(',',$_POST['ids_remark_checked']);
	
    $one = 1;
    $empty_remark = '';
	$query = $mysqli->prepare("SELECT id
	                          FROM dne_log_meeting_updates 
							  WHERE id_meeting = ?							
							  AND is_remark_appears_log = ?
							  AND remark <> ?");
	$query->bind_param("iis",$_POST['meeting_id'],$one,$empty_remark);
	$query->execute();
	$query->store_result();
    $log_meeting_updates_num_rows = $query->num_rows;
	$log_meeting_updates = fetch($query);
    
	foreach($log_meeting_updates as $item){
		$is_remark_appears_log = 0;
		
		if($_POST['isRemark'] 
		   && in_array(@$item->id,$ids_remark_checked_array)) 
			$is_remark_appears_log = 1;

		$query = "UPDATE dne_log_meeting_updates 
		          SET is_remark_appears_log = ? WHERE id = ?";
		$query = $mysqli->prepare($query);
		$query->bind_param('si',$is_remark_appears_log,$item->id);	
		$query->execute();
	}
	
    if($_POST['all_ids_to_edit'] != ''){
	    for($i=0;$i < sizeof($all_ids_to_edit_array);$i++){
			$query = "SELECT destination_date FROM dne_meetings 
			          WHERE id = ?";
			$query = $mysqli->prepare($query);
			$query->bind_param('i',$all_ids_to_edit_array[$i]);   
			$query->execute();
			$query->store_result();
			$elem_meeting = fetch_unique($query);
			
			if(@$all_ids_to_edit_array[$i] == @$_POST['meeting_id']) {
				if($_POST['destination_date'] != '0000-00-00')
					$destination_date = @$_POST['destination_date'];
			}
			else if(@$all_ids_to_edit_array[$i] != @$_POST['meeting_id']){
			    $destination_date = @$elem_meeting->destination_date;
				if(@$destination_date != @$_POST['destination_date'])
					$destination_date = @$_POST['destination_date'];  
			}
			
			$query = "UPDATE dne_meetings SET destination_date = ? WHERE id = ?";
			$query = $mysqli->prepare($query);
			$query->bind_param('si',$destination_date,$all_ids_to_edit_array[$i]);	
			$query->execute();	
			
			$log_destination_date = '';
            if(@$elem_meeting->destination_date != @$_POST['destination_date'])
		        $log_destination_date = @$_POST['destination_date']; 
			
			$log_id_progress_status = 0;
           
		    if($_POST['isRemark']){
				$query = "INSERT INTO dne_log_meeting_updates 
						 (id_user,id_meeting,action_date,action,
						 destination_date,remark,id_progress_status,
						 updated_users) VALUES(?,?,?,?,?,?,?,?)";
				$query = $mysqli->prepare($query);
				$query->bind_param('iissssii',$_SESSION['id_user'],
								   $all_ids_to_edit_array[$i],date('Y-m-d'),
								   $action,$log_destination_date,$log_remark,
								   $log_id_progress_status,$_SESSION['id_user']);
				$query->execute();
            }			
	    }
	}
    else {
		$query = "SELECT * FROM dne_meetings WHERE id = ?";
		$query = $mysqli->prepare($query);
		$query->bind_param('i',$_POST['meeting_id']);   
		$query->execute();
		$query->store_result();
		$meeting = fetch_unique($query);
		
		$destination_date = @$meeting->destination_date;
		if($_POST['destination_date'] != '0000-00-00')
		    $destination_date = $_POST['destination_date'];
		
		$query = "UPDATE dne_meetings SET destination_date = ? WHERE id = ?";
		$query = $mysqli->prepare($query);
		$query->bind_param('si',$destination_date,$_POST['meeting_id']);	
		$query->execute();

        $log_destination_date = '';
        if(@$meeting->destination_date != @$_POST['destination_date']
		   && @$_POST['destination_date'] != '0000-00-00')
			$log_destination_date = @$_POST['destination_date']; 	

        $log_id_progress_status = 0;
       
	    if($_POST['isRemark']){
			$query = "INSERT INTO dne_log_meeting_updates 
					 (id_user,id_meeting,action_date,action,
					 destination_date,remark,id_progress_status,updated_users) 
					 VALUES(?,?,?,?,?,?,?,?)";
			$query = $mysqli->prepare($query);
			$query->bind_param('iissssii',$_SESSION['id_user'],
							   $_POST['meeting_id'],date('Y-m-d'),$action,
							   $log_destination_date,$log_remark,
							   $log_id_progress_status,$_SESSION['id_user']);
			$query->execute();
        }		
	}
}

else if($_POST['field'] == "id_progress_status" || $_POST['field'] == "update_task"){
	$ids_remark_checked_array = explode(',',$_POST['ids_remark_checked']);
	
    $one = 1;
    $empty_remark = '';
	$query = $mysqli->prepare("SELECT id
	                          FROM dne_log_meeting_updates 
							  WHERE id_meeting = ?							
							  AND is_remark_appears_log = ?
							  AND remark <> ?");
	$query->bind_param("iis",$_POST['meeting_id'],$one,$empty_remark);
	$query->execute();
	$query->store_result();
    $log_meeting_updates_num_rows = $query->num_rows;
	$log_meeting_updates = fetch($query);	
    
	foreach($log_meeting_updates as $item){
		$is_remark_appears_log = 0;
		
		if($_POST['isRemark'] && in_array(@$item->id,$ids_remark_checked_array)) 
			$is_remark_appears_log = 1;

		$query = "UPDATE dne_log_meeting_updates 
		          SET is_remark_appears_log = ? WHERE id = ?";
		$query = $mysqli->prepare($query);
		$query->bind_param('si',$is_remark_appears_log,$item->id);	
		$query->execute();
	}
	
	$query = "SELECT * FROM dne_progress_status WHERE id = ?";
    $query = $mysqli->prepare($query);
	$query->bind_param('i',$_POST['id_progress_status']);   
	$query->execute();
	$query->store_result();
	$query = fetch_unique($query);
	$ps_name = $query->name_he;	
	
	if($_POST['all_ids_to_edit'] != ''){
	    for($i=0;$i<sizeof($all_ids_to_edit_array);$i++){
			$query = "SELECT destination_date,id_progress_status 
			          FROM dne_meetings WHERE id = ?";
			$query = $mysqli->prepare($query);
			$query->bind_param('i',$all_ids_to_edit_array[$i]);   
			$query->execute();
			$query->store_result();
			$elem_meeting = fetch_unique($query);
			$destination_date = @$elem_meeting->destination_date;
			$id_progress_status = @$elem_meeting->id_progress_status;
			
			if(@$all_ids_to_edit_array[$i] == @$_POST['meeting_id']){
				if($_POST['new_destination_date'] != '0000-00-00')
					$destination_date = @$_POST['new_destination_date'];
				$id_progress_status = @$_POST['id_progress_status'];
			}
			else if(@$all_ids_to_edit_array[$i] != @$_POST['meeting_id']){
			    if(@$destination_date != @$_POST['new_destination_date'] 
				   && $_POST['new_destination_date'] != '0000-00-00')
				  $destination_date = @$_POST['new_destination_date']; 
				if(@$id_progress_status != @$_POST['id_progress_status'])
				   $id_progress_status = @$_POST['id_progress_status'];		
			}
			
			$query = "UPDATE dne_meetings SET destination_date = ?,
			          id_progress_status = ? WHERE id = ?";
			$query = $mysqli->prepare($query);
			$query->bind_param('sii',$destination_date,$id_progress_status,
			                   $all_ids_to_edit_array[$i]);	
			$query->execute();	
			
			$log_destination_date = '';
            if(@$elem_meeting->destination_date != @$_POST['new_destination_date'])
		        $log_destination_date = @$_POST['new_destination_date']; 
			
			$log_id_progress_status = 0;
            if(@$elem_meeting->id_progress_status != @$_POST['id_progress_status'])
		        $log_id_progress_status = @$_POST['id_progress_status']; 
			
			if($_POST['isRemark']){
				$query = "INSERT INTO dne_log_meeting_updates 
						 (id_user,id_meeting,action_date,action,
						 destination_date,remark,id_progress_status,
						 updated_users) VALUES(?,?,?,?,?,?,?,?)";
				$query = $mysqli->prepare($query);
				$query->bind_param('iissssii',$_SESSION['id_user'],
								   $all_ids_to_edit_array[$i],date('Y-m-d'),
								   $action,$log_destination_date,$log_remark,
								   $log_id_progress_status,$_SESSION['id_user']);
				$query->execute();
			}			

			if(@$ps_name == 'ארכיון' || @$ps_name == 'בוצע/נמסר'){
				$query = $mysqli->prepare("DELETE FROM dne_to_do_today 
							               WHERE id_user = ? AND id_meeting = ?");
				$query->bind_param("ii",$_SESSION['id_user'],$all_ids_to_edit_array[$i]);
	            $query->execute();
			}				
	    }
	}
    else {
		$query = "SELECT * FROM dne_meetings WHERE id = ?";
		$query = $mysqli->prepare($query);
		$query->bind_param('i',$_POST['meeting_id']);   
		$query->execute();
		$query->store_result();
		$meeting = fetch_unique($query);
		
		$destination_date = @$meeting->destination_date;
		if($_POST['new_destination_date'] != '0000-00-00')
		    $destination_date = $_POST['new_destination_date'];
		
		$query = "UPDATE dne_meetings SET destination_date = ?,
			      id_progress_status = ? WHERE id = ?";
		$query = $mysqli->prepare($query);
		$query->bind_param('sii',$destination_date,$_POST['id_progress_status'],$_POST['meeting_id']);	
		$query->execute();

        $log_destination_date = '';
        if(@$meeting->destination_date != @$_POST['new_destination_date']
		   && @$_POST['new_destination_date'] != '0000-00-00')
			$log_destination_date = @$_POST['new_destination_date']; 	

        $log_id_progress_status = 0;
        if(@$meeting->id_progress_status != @$_POST['id_progress_status'])
			$log_id_progress_status = @$_POST['id_progress_status']; 					
  
        if($_POST['isRemark']){
			$query = "INSERT INTO dne_log_meeting_updates 
					 (id_user,id_meeting,action_date,action,
					 destination_date,remark,id_progress_status,updated_users) 
					 VALUES(?,?,?,?,?,?,?,?)";
			$query = $mysqli->prepare($query);
			$query->bind_param('iissssii',$_SESSION['id_user'],
							   $_POST['meeting_id'],date('Y-m-d'),$action,
							   $log_destination_date,$log_remark,
							   $log_id_progress_status,$_SESSION['id_user']);
			$query->execute();
		}

        if(@$ps_name == 'ארכיון' || @$ps_name == 'בוצע/נמסר'){
			$query = $mysqli->prepare("DELETE FROM dne_to_do_today 
									   WHERE id_user = ? AND id_meeting = ?");
			$query->bind_param("ii",$_SESSION['id_user'],$_POST['meeting_id']);
			$query->execute();
		}					
	}
}

else if($_POST['field'] == "track_type"){
	$query = "UPDATE dne_meetings SET track_type = ? 
	          WHERE id".@$where_ids;
	$query = $mysqli->prepare($query);
	$query->bind_param('i',$_POST['track_type']);	
	$query->execute();
}

else if($_POST['field'] == "track_responsible_id") {
	$query = "UPDATE dne_meetings SET id_track_responsible = ? 
	          WHERE id ".@$where_ids;
	$query = $mysqli->prepare($query);
	$query->bind_param('i',$_POST['id_track_responsible']);	
	$query->execute();
}

$task_creation_date = @$_POST['task_creation_date'];

if($_POST['all_ids_to_edit'] != ''){
	for($i=0;$i<sizeof($all_ids_to_edit_array);$i++){
		if($_POST['field'] == 'id_task'){
			$query = "SELECT * FROM dne_tasks WHERE id = ?";
			$query = $mysqli->prepare($query);
			$query->bind_param('i',$_POST['id_task']);   
			$query->execute();
			$query->store_result();
			$query = fetch_unique($query);
			$task_name = $query->name_he;	
		}
		else {
			$query = "SELECT t.name_he AS name_he 
					 FROM dne_meetings m 
					 LEFT JOIN dne_tasks t ON m.id_task = t.id
					 WHERE m.id = ?";
			$query = $mysqli->prepare($query);
			$query->bind_param('i',$all_ids_to_edit_array[$i]);   
			$query->execute();
			$query->store_result();
			$query = fetch_unique($query);
			$task_name = $query->name_he;
		}

		if($_POST['field'] == 'id_progress_status'){
			$query = "SELECT * FROM dne_progress_status WHERE id = ?";
			$query = $mysqli->prepare($query);
			$query->bind_param('i',$_POST['id_progress_status']);   
			$query->execute();
			$query->store_result();
			$query = fetch_unique($query);
			$ps_name = $query->name_he;
		}
		else {
			$query = "SELECT ps.name_he AS name_he
					  FROM dne_meetings m
					  LEFT JOIN dne_progress_status ps 
					  ON m.id_progress_status = ps.id
					  WHERE m.id = ?";
			$query = $mysqli->prepare($query);
			$query->bind_param('i',$all_ids_to_edit_array[$i]);   
			$query->execute();
			$query->store_result();
			$query = fetch_unique($query);
			$ps_name = $query->name_he;
		}
		
		if($task_name == 'הנחיית ביצוע' || $task_name == 'סטטוס ביצוע' 
			|| $task_name == 'בקשה/שאילתה' || $task_name == 'בקרת איכות' 
			|| $ps_name == 'בהמתנה' || $ps_name == 'הנחיה/החלטה') 
			$is_change_row_style = 1;

		if($ps_name == 'בביצוע'){
		   $query = "UPDATE dne_meetings SET status_in_ex_updated_date = ? 
					 WHERE id = ?";
		   $query = $mysqli->prepare($query);
		   $query->bind_param('si',date('Y-m-d'),$all_ids_to_edit_array[$i]);	
		   $query->execute();
		}
		
		else if($ps_name == 'איחור'){
		   $query = "UPDATE dne_meetings SET status_late_updated_date = ? 
					 WHERE id = ?";
		   $query = $mysqli->prepare($query);
		   $query->bind_param('si',date('Y-m-d'),$all_ids_to_edit_array[$i]);	
		   $query->execute();
		}

		else if($ps_name == 'בוצע/נמסר'){
			$task_creation_date = date('Y-m-d');
			$is_change_row_style = 1;
			$is_not_priority = 0;
			
			$query = "UPDATE dne_meetings SET status_finished_updated_date = ?,
					  is_priority = ? WHERE id = ?";
			$query = $mysqli->prepare($query);
			$query->bind_param('sii',date('Y-m-d'),$is_not_priority,
							   $all_ids_to_edit_array[$i]);	
			$query->execute();
		}

		else if($ps_name == 'בהמתנה'){
		   $query = "UPDATE dne_meetings SET status_hold_updated_date = ? 
					 WHERE id = ?";
		   $query = $mysqli->prepare($query);
		   $query->bind_param('si',date('Y-m-d'),$all_ids_to_edit_array[$i]);	
		   $query->execute();
		}

		else if($ps_name == 'ארכיון'){
			$is_appears = 0;
			$is_not_priority = 0;
			
			$query = "UPDATE dne_meetings SET status_archived_updated_date = ?,
					  is_priority = ? WHERE id = ?";
			$query = $mysqli->prepare($query);
			$query->bind_param('sii',date('Y-m-d'),$is_not_priority,
							   $all_ids_to_edit_array[$i]);	
			$query->execute();
		}

		else if($ps_name == 'הנחיה/החלטה'){
		   $query = "UPDATE dne_meetings SET status_decision_updated_date = ? 
					 WHERE id = ?";
		   $query = $mysqli->prepare($query);
		   $query->bind_param('si',date('Y-m-d'),$all_ids_to_edit_array[$i]);	
		   $query->execute();
		}

		if(@$_POST['field']!= 'destination_date' 
		   && @$_POST['field']!= 'id_progress_status'){
			$query = "UPDATE dne_meetings SET updated_date = ? WHERE id = ?";
			$query = $mysqli->prepare($query);
			$query->bind_param('si',date('Y-m-d'),$all_ids_to_edit_array[$i]);	
			$query->execute();
		}

		$query = "UPDATE dne_meetings SET is_change_row_style = ?,is_appears = ?, 
		          updated_date = ? WHERE id = ?";
		$query = $mysqli->prepare($query);
		$query->bind_param('iisi',$is_change_row_style,$is_appears,date('Y-m-d'),
						   $all_ids_to_edit_array[$i]);	
		$query->execute();	

        $query = $mysqli->prepare("SELECT id FROM dne_connexion_data WHERE id_user = ?");
		$query->bind_param("i",$_SESSION['id_user']);
		$query->execute();
		$query->store_result();
			
		$screen_type = 'popup';
		$list_name = 'meetings';
		
		if($query->num_rows == 0){
			$query = "INSERT INTO dne_connexion_data 
					 (id_user,id_meeting,screen_type,list_name) VALUES(?,?,?,?)";
			$query = $mysqli->prepare($query);
			$query->bind_param('iiss',$_SESSION['id_user'],$all_ids_to_edit_array[$i],$screen_type,$list_name);
			$query->execute();
		}
		else {
			$query = "UPDATE dne_connexion_data SET id_meeting = ?,screen_type = ?,list_name = ?
					  WHERE id_user = ?";
			$query = $mysqli->prepare($query);
			$query->bind_param('issi',$all_ids_to_edit_array[$i],$screen_type,$list_name,$_SESSION['id_user']);	
			$query->execute();	
		}		
	}	
}
else {
	if($_POST['field'] == 'id_task'){
		$query = "SELECT * FROM dne_tasks WHERE id = ?";
		$query = $mysqli->prepare($query);
		$query->bind_param('i',$_POST['id_task']);   
		$query->execute();
		$query->store_result();
		$query = fetch_unique($query);
		$task_name = $query->name_he;	
	}
	else {
		$query = "SELECT t.name_he AS name_he 
				 FROM dne_meetings m 
				 LEFT JOIN dne_tasks t ON m.id_task = t.id
				 WHERE m.id = ?";
		$query = $mysqli->prepare($query);
		$query->bind_param('i',$_POST['meeting_id']);   
		$query->execute();
		$query->store_result();
		$query = fetch_unique($query);
		$task_name = $query->name_he;
	}

	if($_POST['field'] == 'id_progress_status'){
		$query = "SELECT * FROM dne_progress_status WHERE id = ?";
		$query = $mysqli->prepare($query);
		$query->bind_param('i',$_POST['id_progress_status']);   
		$query->execute();
		$query->store_result();
		$query = fetch_unique($query);
		$ps_name = $query->name_he;
	}
	else {
		$query = "SELECT ps.name_he AS name_he
				  FROM dne_meetings m
				  LEFT JOIN dne_progress_status ps 
				  ON m.id_progress_status = ps.id
				  WHERE m.id = ?";
		$query = $mysqli->prepare($query);
		$query->bind_param('i',$_POST['meeting_id']);   
		$query->execute();
		$query->store_result();
		$query = fetch_unique($query);
		$ps_name = $query->name_he;
	}
	
	if($task_name == 'הנחיית ביצוע' || $task_name == 'סטטוס ביצוע' 
		|| $task_name == 'בקשה/שאילתה' || $task_name == 'בקרת איכות' 
		|| $ps_name == 'בהמתנה' || $ps_name == 'הנחיה/החלטה') 
		$is_change_row_style = 1;

	if($ps_name == 'בביצוע'){
	   $query = "UPDATE dne_meetings SET status_in_ex_updated_date = ? 
				 WHERE id = ?";
	   $query = $mysqli->prepare($query);
	   $query->bind_param('si',date('Y-m-d'),$_POST['meeting_id']);	
	   $query->execute();
	}
	
	else if($ps_name == 'איחור'){
	   $query = "UPDATE dne_meetings SET status_late_updated_date = ? 
				 WHERE id = ?";
	   $query = $mysqli->prepare($query);
	   $query->bind_param('si',date('Y-m-d'),$_POST['meeting_id']);	
	   $query->execute();
	}

	else if($ps_name == 'בוצע/נמסר'){
		$task_creation_date = date('Y-m-d');
		$is_change_row_style = 1;
		$is_not_priority = 0;
		
		$query = "UPDATE dne_meetings SET status_finished_updated_date = ?,
				  is_priority = ? WHERE id = ?";
		$query = $mysqli->prepare($query);
		$query->bind_param('sii',date('Y-m-d'),$is_not_priority,
						   $_POST['meeting_id']);	
		$query->execute();
	}

	else if($ps_name == 'בהמתנה'){
	   $query = "UPDATE dne_meetings SET status_hold_updated_date = ? 
				 WHERE id = ?";
	   $query = $mysqli->prepare($query);
	   $query->bind_param('si',date('Y-m-d'),$_POST['meeting_id']);	
	   $query->execute();
	}

	else if($ps_name == 'ארכיון'){
		$is_appears = 0;
		$is_not_priority = 0;
		
		$query = "UPDATE dne_meetings SET status_archived_updated_date = ?,
				  is_priority = ? WHERE id = ?";
		$query = $mysqli->prepare($query);
		$query->bind_param('sii',date('Y-m-d'),$is_not_priority,
						   $_POST['meeting_id']);	
		$query->execute();
	}

	else if($ps_name == 'הנחיה/החלטה'){
	   $query = "UPDATE dne_meetings SET status_decision_updated_date = ? 
				 WHERE id = ?";
	   $query = $mysqli->prepare($query);
	   $query->bind_param('si',date('Y-m-d'),$_POST['meeting_id']);	
	   $query->execute();
	}

	$query = "UPDATE dne_meetings SET is_change_row_style = ?,is_appears = ?, 
	          updated_date = ? WHERE id = ?";
	$query = $mysqli->prepare($query);
	$query->bind_param('iisi',$is_change_row_style,$is_appears,date('Y-m-d'),
	                   $_POST['meeting_id']);	
	$query->execute();
	
	$query = $mysqli->prepare("SELECT id FROM dne_connexion_data WHERE id_user = ?");
	$query->bind_param("i",$_SESSION['id_user']);
	$query->execute();
	$query->store_result();
	
	$screen_type = 'popup';
	$list_name = 'meetings';

	if($query->num_rows === 0){
		$query = "INSERT INTO dne_connexion_data 
				 (id_user,id_meeting,screen_type,list_name) VALUES(?,?,?,?)";
		$query = $mysqli->prepare($query);
		$query->bind_param('iiss',$_SESSION['id_user'],$_POST['meeting_id'],$screen_type,$list_name);
		$query->execute();
	}
	else {
		$query = "UPDATE dne_connexion_data SET id_meeting = ?,screen_type = ?,list_name = ?
				  WHERE id_user = ?";
		$query = $mysqli->prepare($query);
		$query->bind_param('issi',$_POST['meeting_id'],$screen_type,$list_name,$_SESSION['id_user']);	
		$query->execute();	
	}
}
?>