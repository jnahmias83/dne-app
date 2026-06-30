<?php
session_start();
include 'functions/functions.php';

$image1_name = '';
$image1_width = 0;
$image1_height = 0;
$image1_rotation = 0;
$image2_name = '';
$image2_width = 0;
$image2_height = 0;
$image2_rotation = 0;
$is_task_priority = 0;

if($_POST['id_chapter'] == 0 || isEffectivelyEmpty($_POST['subject']) || 
   isEffectivelyEmpty($_POST['area']) || $_POST['id_task'] == 0 || 
   $_POST['id_responsible'] == 0 || $_POST['id_pass_on'] == 0) {
	  echo "empty";
}
else {
	$blank = ' ';
	$_today = date('Y-m-d');
	$id_progress_status = @$_POST['id_progress_status'];
	if($_POST['id_progress_status'] == 0) {
		$query = "SELECT * FROM dne_progress_status WHERE name = ? 
		          AND id_project = ?";
		$query = $mysqli->prepare($query);
		$query->bind_param('si',$blank,$_POST['id_project']);   
	    $query->execute();
		$query->store_result();
		$query = fetch_unique($query);
		$id_progress_status = $query->id;
	}
	
	$task_creation_date = @$_POST['task_creation_date'];
	$is_change_row_style = 0;
	$is_appears = 1;
	
	$query = "SELECT * FROM dne_tasks WHERE id = ?";
	$query = $mysqli->prepare($query);
	$query->bind_param('i',$_POST['id_task']);   
	$query->execute();
	$query->store_result();
	$query = fetch_unique($query);
	$task_name = $query->name_he;
	
	$query = "SELECT * FROM dne_progress_status WHERE id = ?";
	$query = $mysqli->prepare($query);
	$query->bind_param('i',$id_progress_status);   
	$query->execute();
	$query->store_result();
	$query = fetch_unique($query);
    $ps_name = $query->name_he;
	
	if($task_name == 'הנחיית ביצוע' || $task_name == 'סטטוס ביצוע' 
	   || $task_name == 'בקשה/שאילתה' || $task_name == 'בקרת איכות' 
	   || $ps_name == 'בוצע/נמסר' || $ps_name == 'בהמתנה' || 
	   $ps_name == 'הנחיה/החלטה')
		  $is_change_row_style = 1;
	
	/*if(isset($_SESSION['task_image1_data'])) {
		$task_image1_data_array = explode("____",$_SESSION['task_image1_data']);
		$image1_name = $task_image1_data_array[0];
		$image1_width = $task_image1_data_array[1];
		$image1_height = $task_image1_data_array[2];
		$image1_rotation = $task_image1_data_array[3];
	}

	if(isset($_SESSION['task_image2_data'])) {
		$task_image2_data_array = explode("____",$_SESSION['task_image2_data']);
		$image2_name = $task_image2_data_array[0];
		$image2_width = $task_image2_data_array[1];
		$image2_height = $task_image2_data_array[2];
		$image2_rotation = $task_image2_data_array[3];
	}*/
	
	$log_remark = '';
	if(@$_POST['remark'] != '')
		$log_remark = htmlspecialchars(@$_POST['remark']);
	
	if($_POST['id'] == 0){ 
	    if($_POST['id_progress_status'] != 0){
		   $query = "SELECT * FROM dne_progress_status WHERE id = ?";
		   $query = $mysqli->prepare($query);
		   $query->bind_param('i',$_POST['id_progress_status']);   
		   $query->execute();
		   $query->store_result();
		   $query = fetch_unique($query);
		   if(@$query->name == 'ארכיון')
			 $is_appears = 0;
	    }
		
		if(isset($_FILES['image1']['name'])){
			$image1_name = $_FILES['image1']['name'];
			$imageUploadPath = 'uploads/'.$image1_name;
			$fileType = pathinfo($imageUploadPath, PATHINFO_EXTENSION); 
			
			$allowTypes = array('jpg','png','jpeg','gif'); 
			if(in_array($fileType, $allowTypes)){ 
				$imageTemp = $_FILES["image1"]["tmp_name"]; 
				$compressedImage = compressImage($imageTemp,$imageUploadPath,75); 
				list($image1_width,$image1_height) = getimagesize($imageUploadPath);
			}
	    }
		
		if(isset($_FILES['image2']['name'])){
			$image2_name = $_FILES['image2']['name'];
			$imageUploadPath = 'uploads/'.$image2_name;
			$fileType = pathinfo($imageUploadPath, PATHINFO_EXTENSION); 
			
			$allowTypes = array('jpg','png','jpeg','gif'); 
			if(in_array($fileType, $allowTypes)){ 
				$imageTemp = $_FILES["image2"]["tmp_name"]; 
				$compressedImage = compressImage($imageTemp, $imageUploadPath,75); 
				list($image2_width,$image2_height) = getimagesize($imageUploadPath);
			}
		}
		
		$track_type = 0;
		$reminder_date = '0000-00-00';
		
		if(@$_POST['is_reminds']){
			$track_type = 1;
			$reminder_date = date('Y-m-d', strtotime($_POST['destination_date'] . ' -3 days'));		
		}
				
		$query = "INSERT INTO dne_meetings (id_user,id_project,id_chapter,
		          ids_rdv,subject,area,description,id_task,id_responsible,
				  id_pass_on,task_creation_date,destination_date,
				  id_progress_status,is_appears,is_change_row_style,
				  image1,image1_width,image1_height,is_appears_img1,image2,
				  image2_width,image2_height,is_appears_img2,id_track_responsible,lang,
				  track_type,reminder_date,is_agrees,is_reminds,updated_date) 
				  VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
		$query = $mysqli->prepare($query);
		$_subject = htmlspecialchars($_POST['subject']);
		$_area    = htmlspecialchars($_POST['area']);
		$_descr   = htmlspecialchars($_POST['description']);
		$query->bind_param('iiissssiiissiiisiiisiiiisisiis',
		                   $_SESSION['id_user'],$_POST['id_project'],
						   $_POST['id_chapter'],$_POST['id_rdv'],
						   $_subject,
						   $_area,
						   $_descr,
						   $_POST['id_task'],$_POST['id_responsible'],
						   $_POST['id_pass_on'],$task_creation_date,
						   $_POST['destination_date'],$id_progress_status,
						   $is_appears,$is_change_row_style,$image1_name,
						   $image1_width,$image1_height,
						   $_POST['is_appears_img1'],$image2_name,
						   $image2_width,$image2_height,
						   $_POST['is_appears_img2'],$_SESSION['id_user'],
						   $_POST['lang'],$track_type,$reminder_date,
						   $_POST['is_agrees'],$_POST['is_reminds'],$_today);
		$query->execute();
		$inserted_meeting = $query->insert_id;

        $query = "SELECT id_task FROM dne_chapters WHERE id_project = ?";
		$query = $mysqli->prepare($query);
		$query->bind_param('i',$_POST['id_project']);   
		$query->execute();
		$query->store_result();
		$query = fetch_unique($query);
        
		if(@$query->id_task == 0){
			$query = "UPDATE dne_chapters SET id_task = ?,id_responsible = ?,
					  id_pass_on = ? WHERE id = ?";
			$query = $mysqli->prepare($query);
			$query->bind_param('iiii',$_POST['id_task'],
			                   $_POST['id_responsible'],$_POST['id_pass_on'],
			                   $_POST['id_chapter']);	
			$query->execute();
		}			
		
		$query = "INSERT INTO dne_log_meeting_updates (id_user,id_meeting,
		          action_date,action,destination_date,
				  id_progress_status,updated_users) VALUES(?,?,?,?,?,?,?)";
		$query = $mysqli->prepare($query);
		$query->bind_param('iisssii',$_SESSION['id_user'],$inserted_meeting,
		                  $_today,$_POST['action'],
						  $_POST['destination_date'],$id_progress_status,
						  $_SESSION['id_user']);
		$query->execute();
		
		$query = $mysqli->prepare("SELECT * FROM dne_latest_tasks_data 
		                          WHERE id_project = ? AND id_user = ?");
	    $query->bind_param("ii",$_POST['id_project'],$_SESSION['id_user']);
	    $query->execute(); 
		$query->store_result();

        if($_POST['is_reminds']){
			$is_remark_appears_log = 1;
			$remark = 'תזכורת 3 ימים לפני';
			
			$query = "INSERT INTO dne_log_meeting_tracking (id_user,id_meeting,action_date,remark,is_remark_appears_log,
			          updated_users) VALUES(?,?,?,?,?,?)";
			$query = $mysqli->prepare($query);
			$query->bind_param('iissis',$_SESSION['id_user'],$inserted_meeting,$_today,$remark,$is_remark_appears_log,
			                   $_SESSION['id_user']);
			$query->execute();	
		}
        
		echo 'inserted_'.$inserted_meeting;		
	}
	else if($_POST['id'] > 0){
		$all_ids_to_edit_array = explode(',',$_POST['all_ids_to_edit']);

		$responsible_is_valid = true;
		$pass_on_is_valid = true;
		$id_proj_check = (int)$_POST['id_project'];
		$q_sup = $mysqli->prepare("SELECT id FROM dne_custom_reports WHERE id_project = ? AND id_supplier > 0 LIMIT 1");
		$q_sup->bind_param('i', $id_proj_check);
		$q_sup->execute();
		$q_sup->store_result();
		$q_sup_num_rows = $q_sup->num_rows;
		$q_sup_po = $mysqli->prepare("SELECT id FROM dne_custom_reports WHERE id_project = ? AND id_supplier > 0 AND is_include_pass_on_tasks = 1 LIMIT 1");
		$q_sup_po->bind_param('i', $id_proj_check);
		$q_sup_po->execute();
		$q_sup_po->store_result();
		$has_supplier_doh_with_pass_on = $q_sup_po->num_rows > 0;
		if($q_sup_num_rows > 0) {
			$id_resp_check = (int)$_POST['id_responsible'];
			if($id_resp_check > 0) {
				$q = $mysqli->prepare("SELECT id_projects_suppliers FROM dne_responsibles WHERE id = ?");
				$q->bind_param('i', $id_resp_check);
				$q->execute(); $q->store_result();
				$r = fetch_unique($q);
				if(!(int)@$r->id_projects_suppliers) $responsible_is_valid = false;
			}
			$id_po_check = (int)$_POST['id_pass_on'];
			if($has_supplier_doh_with_pass_on && $id_po_check > 0) {
				$q = $mysqli->prepare("SELECT id_projects_suppliers FROM dne_responsibles WHERE id = ?");
				$q->bind_param('i', $id_po_check);
				$q->execute(); $q->store_result();
				$r = fetch_unique($q);
				if(!(int)@$r->id_projects_suppliers) $pass_on_is_valid = false;
			}
		}

		if(isset($_FILES['image1']['name'])) {
			$image1_name = $_FILES['image1']['name'];
			$imageUploadPath = 'uploads/'.$image1_name;
			$fileType = pathinfo($imageUploadPath, PATHINFO_EXTENSION); 
	 
			$allowTypes = array('jpg','png','jpeg','gif'); 
			if(in_array($fileType, $allowTypes)){ 
				$imageTemp = $_FILES["image1"]["tmp_name"]; 
				$compressedImage = compressImage($imageTemp,$imageUploadPath,75); 
				list($image1_width,$image1_height) = getimagesize($imageUploadPath);
			}
		
			$query = "UPDATE dne_meetings SET image1 = ?,image1_width = ?,
					  image1_height = ? WHERE id = ?";
			$query = $mysqli->prepare($query);
			$query->bind_param('siii',$image1_name,$image1_width,
			                   $image1_height,$_POST['id']);	
			$query->execute();
		}
		
		if(isset($_FILES['image2']['name'])){
			$image2_name = $_FILES['image2']['name'];
			$imageUploadPath = 'uploads/'.$image2_name;
			$fileType = pathinfo($imageUploadPath, PATHINFO_EXTENSION); 
		 
			$allowTypes = array('jpg','png','jpeg','gif'); 
			if(in_array($fileType, $allowTypes)){ 
				$imageTemp = $_FILES["image2"]["tmp_name"]; 
				$compressedImage = compressImage($imageTemp,$imageUploadPath,75); 
				list($image2_width,$image2_height) = getimagesize($imageUploadPath);
			}
			
			$query = "UPDATE dne_meetings SET image2 = ?,image2_width = ?,
					  image2_height = ? WHERE id = ?";
			$query = $mysqli->prepare($query);
			$query->bind_param('siii',$image2_name,$image2_width,
			                  $image2_height,$_POST['id']);	
			$query->execute();
		}		
				
		if($ps_name == 'בביצוע') {
		   $query = "UPDATE dne_meetings SET status_updated_date = ?,
		             status_in_ex_updated_date = ? WHERE id = ?";
		   $query = $mysqli->prepare($query);
		   $query->bind_param('ssi',$_today,$_today,$_POST['id']);
		   $query->execute();
		}

		else if($ps_name == 'איחור') {
		   $query = "UPDATE dne_meetings SET status_updated_date = ?,
		             status_late_updated_date = ? WHERE id = ?";
		   $query = $mysqli->prepare($query);
		   $query->bind_param('ssi',$_today,$_today,$_POST['id']);
		   $query->execute();
		}

	    else if($ps_name == 'בוצע/נמסר') {
			$task_creation_date = $_today;
			$is_change_row_style = 1;
			$is_not_priority = 0;

			$query = "UPDATE dne_meetings SET status_updated_date = ?,
			          status_finished_updated_date = ?,is_priority = ?
					  WHERE id = ?";
		    $query = $mysqli->prepare($query);
		    $query->bind_param('ssii',$_today,$_today,
			                   $is_not_priority,$_POST['id']);
		    $query->execute();
	    }

		else if($ps_name == 'בהמתנה') {
		   $query = "UPDATE dne_meetings SET status_updated_date = ?,
		             status_hold_updated_date = ? WHERE id = ?";
		   $query = $mysqli->prepare($query);
		   $query->bind_param('ssi',$_today,$_today,$_POST['id']);
		   $query->execute();
		}

		else if($ps_name == 'ארכיון') {
			$is_appears = 0;
			$is_not_priority = 0;

			$query = "UPDATE dne_meetings SET status_updated_date = ?,
			          status_archived_updated_date = ?,is_priority = ?
					  WHERE id = ?";
		    $query = $mysqli->prepare($query);
		    $query->bind_param('ssii',$_today,$_today,
			                   $is_not_priority,$_POST['id']);
		    $query->execute();
		}

		else if($ps_name == 'הנחיה/החלטה') {
		   $query = "UPDATE dne_meetings SET status_updated_date = ?,
		             status_decision_updated_date = ? WHERE id = ?";
		   $query = $mysqli->prepare($query);
		   $query->bind_param('ssi',$_today,$_today,$_POST['id']);
		   $query->execute();
		}
		
		if($_POST['all_ids_to_edit'] != ''){
			for($i=0;$i<sizeof($all_ids_to_edit_array);$i++){
				$query = $mysqli->prepare("SELECT * FROM dne_meetings 
					                      WHERE id = ?");
				$query->bind_param("i",$all_ids_to_edit_array[$i]);
				$query->execute();
				$query->store_result();
				$elem_meeting = fetch_unique($query);
	
				if(@$all_ids_to_edit_array[$i] == @$_POST['id']){
					$id_chapter = @$_POST['id_chapter'];
					$ids_rdv = @$_POST['ids_rdv_checked'];
					$subject = @$_POST['subject'];
					$area = @$_POST['area'];
					$description = $_POST['description'];
					$id_task = @$_POST['id_task'];
					$id_responsible = $responsible_is_valid ? @$_POST['id_responsible'] : @$elem_meeting->id_responsible;
					$id_pass_on = $pass_on_is_valid ? @$_POST['id_pass_on'] : @$elem_meeting->id_pass_on;
					$destination_date = @$_POST['destination_date'];
					$id_progress_status = @$_POST['id_progress_status'];
				}
				else if(@$all_ids_to_edit_array[$i] != @$_POST['id']){
					$id_chapter = @$elem_meeting->id_chapter;
					$ids_rdv = @$elem_meeting->ids_rdv;
				   	$subject = @$elem_meeting->subject;
                    $area = @$elem_meeting->area;
                    $description = @$elem_meeting->description;
					
					$id_task = @$elem_meeting->id_task;
				    if(@$id_task != @$_POST['id_task'])
					   $id_task = @$_POST['id_task'];

                    $id_responsible = @$elem_meeting->id_responsible;
				    if(@$id_responsible != @$_POST['id_responsible'] && $responsible_is_valid)
					   $id_responsible = @$_POST['id_responsible'];

                    $id_pass_on = @$elem_meeting->id_pass_on;
				    if(@$id_pass_on != @$_POST['id_pass_on'] && $pass_on_is_valid)
					   $id_pass_on = @$_POST['id_pass_on'];
				   
					$destination_date = @$elem_meeting->destination_date;
					if(@$destination_date != @$_POST['destination_date'])
						$destination_date = @$_POST['destination_date'];        
				
					$id_progress_status = @$elem_meeting->id_progress_status;
					if(@$id_progress_status != @$_POST['id_progress_status'])
						$id_progress_status = @$_POST['id_progress_status'];										   
				}

                $track_type = 0;
		        $reminder_date = '0000-00-00';
		
				if(@$_POST['is_reminds']){
					$track_type = 1;
					$reminder_date = date('Y-m-d', strtotime($_POST['destination_date'] . ' -3 days'));		
				}				

				$query = "UPDATE dne_meetings SET id_chapter = ?,
						 ids_rdv = ?,subject = ?,area = ?,
						 description = ?,id_task = ?,id_responsible = ?,
						 id_pass_on = ?,task_creation_date = ?,
						 destination_date = ?,id_progress_status = ?,
						 is_appears = ?,is_change_row_style = ?,
						 is_appears_img1 = ?,is_appears_img2 = ?,lang = ?,
						 track_type = ?,reminder_date = ?,
						 is_agrees = ?,is_reminds = ? WHERE id = ?";
				$query = $mysqli->prepare($query);
				$_loop_subject = htmlspecialchars($subject);
				$_loop_area    = htmlspecialchars($area);
				$_loop_descr   = htmlspecialchars($description);
				$query->bind_param('issssiiissiiiiisisiii',
								   $id_chapter,$ids_rdv,
								   $_loop_subject,
								   $_loop_area,
								   $_loop_descr,
								   $id_task,$id_responsible,$id_pass_on,
								   $task_creation_date,$destination_date,
								   $id_progress_status,$is_appears,
								   $is_change_row_style,
								   $_POST['is_appears_img1'],
								   $_POST['is_appears_img2'],
								   $_POST['lang'],$track_type,$reminder_date,
								   $_POST['is_agrees'],$_POST['is_reminds'],
								   $all_ids_to_edit_array[$i]);
				$query->execute();

                $log_destination_date = '';
                if(@$elem_meeting->destination_date != @$_POST['destination_date'])
					$log_destination_date = @$_POST['destination_date'];

                $log_id_progress_status = 0;
                if(@$elem_meeting->id_progress_status != @$_POST['id_progress_status'])
					$log_id_progress_status = @$_POST['id_progress_status'];

				$query = "INSERT INTO dne_log_meeting_updates
						  (id_user,id_meeting,action_date,action,
						   destination_date,remark,id_progress_status,
						   updated_users)
						   VALUES(?,?,?,?,?,?,?,?)";
				$query = $mysqli->prepare($query);
				$query->bind_param('iissssii',$_SESSION['id_user'],
								   $all_ids_to_edit_array[$i],$_today,
								   $_POST['action'],$log_destination_date,
								   $log_remark,$log_id_progress_status,
								   $_SESSION['id_user']);
				$query->execute();

				if($_POST['is_reminds']){
					$is_remark_appears_log = 1;
					$remark = 'תזכורת 3 ימים לפני';

					$query = "INSERT INTO dne_log_meeting_tracking (id_user,id_meeting,action_date,remark,is_remark_appears_log,
							  updated_users) VALUES(?,?,?,?,?,?)";
					$query = $mysqli->prepare($query);
					$query->bind_param('iissis',$_SESSION['id_user'],$all_ids_to_edit_array[$i],$_today,
					                   $remark,$is_remark_appears_log,$_SESSION['id_user']);
					$query->execute();
				}

				if(($elem_meeting->id_chapter != $_POST['id_chapter']) ||
				  ($elem_meeting->subject != $_POST['subject']) ||
			      ($elem_meeting->area != $_POST['area']) ||
				  ($elem_meeting->description != $_POST['description']) ||
			      ($elem_meeting->id_task != $_POST['id_task']) ||
				  ($elem_meeting->id_responsible != $_POST['id_responsible']) ||
			      ($elem_meeting->id_pass_on != $_POST['id_pass_on']))
		        {
					$query = "UPDATE dne_meetings SET updated_date = ?
					          WHERE id = ?";
					$query = $mysqli->prepare($query);
					$query->bind_param('si',$_today,
					                   $all_ids_to_edit_array[$i]);
					$query->execute();
			    }
			}
		}
		else {
            $query = "SELECT * FROM dne_meetings WHERE id = ?";
		    $query = $mysqli->prepare($query);
		    $query->bind_param('i',$_POST['id']);   
		    $query->execute();
		    $query->store_result();
		    $meeting = fetch_unique($query);
			$id_responsible_sv = $responsible_is_valid ? (int)$_POST['id_responsible'] : (int)@$meeting->id_responsible;
			$id_pass_on_sv = $pass_on_is_valid ? (int)$_POST['id_pass_on'] : (int)@$meeting->id_pass_on;

            $track_type = 0;
		    $reminder_date = '0000-00-00';
		
		    if(@$_POST['is_reminds']){
				$track_type = 1;
				$reminder_date = date('Y-m-d', strtotime($_POST['destination_date'] . ' -3 days'));		
			}			

			$query = "UPDATE dne_meetings SET id_chapter = ?,ids_rdv = ?,
			          subject = ?,area = ?,description = ?,id_task = ?,
					  id_responsible = ?,id_pass_on = ?,
					  task_creation_date = ?,destination_date = ?,
					  id_progress_status = ?,is_appears = ?,
					  is_change_row_style = ?,is_appears_img1 = ?,
					  is_appears_img2 = ?,lang = ?,track_type = ?,reminder_date = ?,
					  is_agrees = ?,is_reminds = ? WHERE id = ?";
			$query = $mysqli->prepare($query);
			$_subject = htmlspecialchars($_POST['subject']);
			$_area    = htmlspecialchars($_POST['area']);
			$_descr   = htmlspecialchars($_POST['description']);
			$query->bind_param('issssiiissiiiiisisiii',
			                   $_POST['id_chapter'],
							   $_POST['ids_rdv_checked'],
							   $_subject,
							   $_area,
							   $_descr,
							   $_POST['id_task'],$id_responsible_sv,
							   $id_pass_on_sv,$task_creation_date,
							   $_POST['destination_date'],
							   $_POST['id_progress_status'],
							   $is_appears,$is_change_row_style,
							   $_POST['is_appears_img1'],
							   $_POST['is_appears_img2'],$_POST['lang'],$track_type,$reminder_date,
							   $_POST['is_agrees'],$_POST['is_reminds'],$_POST['id']);	
			$query->execute();
			
			$log_destination_date = '';
            if(@$meeting->destination_date != @$_POST['destination_date'])
			   $log_destination_date = @$_POST['destination_date']; 	

            $log_id_progress_status = 0;
            if(@$meeting->id_progress_status != @$_POST['id_progress_status'])
			   $log_id_progress_status = @$_POST['id_progress_status']; 					

			$query = "INSERT INTO dne_log_meeting_updates 
					  (id_user,id_meeting,action_date,action,
					  destination_date,remark,id_progress_status,
					  updated_users) 
					  VALUES(?,?,?,?,?,?,?,?)";
			$query = $mysqli->prepare($query);
			$query->bind_param('iissssii',$_SESSION['id_user'],
							    $_POST['id'],$_today,$_POST['action'],
								$log_destination_date,$log_remark,
								$log_id_progress_status,
								$_SESSION['id_user']);
			$query->execute();

            if($_POST['is_reminds']){
				$is_remark_appears_log = 1;	
				$remark = 'תזכורת 3 ימים לפני';
					
				$query = "INSERT INTO dne_log_meeting_tracking (id_user,id_meeting,action_date,remark,is_remark_appears_log,
							  updated_users) VALUES(?,?,?,?,?,?)";
				$query = $mysqli->prepare($query);
				$query->bind_param('iissis',$_SESSION['id_user'],$_POST['id'],$_today,$remark,$is_remark_appears_log,
				                   $_SESSION['id_user']);
				$query->execute();
			}

			if(($meeting->id_chapter != $_POST['id_chapter']) ||
			  ($meeting->subject != $_POST['subject']) ||
			  ($meeting->area != $_POST['area']) ||
			  ($meeting->description != $_POST['description']) ||
			  ($meeting->id_task != $_POST['id_task']) ||
			  ($meeting->id_responsible != $_POST['id_responsible']) ||
			  ($meeting->id_pass_on != $_POST['id_pass_on']))
		    {
				$query = "UPDATE dne_meetings SET updated_date = ?
				          WHERE id = ?";
				$query = $mysqli->prepare($query);
				$query->bind_param('si',$_today,$_POST['id']);
				$query->execute();
			}
		}

		if($q_sup_num_rows > 0) {
			rebuild_supplier_doh_lists($mysqli, $_POST['id_project']);
		}
		echo 'updated';
	}
	
	$query = "SELECT * FROM dne_custom_reports WHERE id_project = ?";
	$query = $mysqli->prepare($query);
	$query->bind_param('i',$_POST['id_project']);   
	$query->execute();
	$query->store_result();
	$custom_reports = fetch($query);
	
	foreach ($custom_reports as $item){	
		$position_where = strpos($item->sql_str,"WHERE");
		$where_length = strlen($item->sql_str)-$position_where;
		$where_part_sql = substr($item->sql_str,$position_where,$where_length); 
		$where_part_sql_array = explode(' AND ',$where_part_sql);
		
		for($i=0;$i < sizeof($where_part_sql_array);$i++){
			if(strpos($where_part_sql_array[$i],'id_chapter')!= false){
				$position_chapter_in = strpos($where_part_sql_array[$i],"IN(");
				$where_chapter_in_length = strlen($where_part_sql_array[$i])-$position_chapter_in;
				$where_part_chapter_in = substr($where_part_sql_array[$i],$position_chapter_in,$where_chapter_in_length);
				$chapter_ids = substr($where_part_chapter_in,3,-1);
				
				if(strpos($chapter_ids,$_POST['id_chapter']) == false)
				   $chapter_ids .= ','.$_POST['id_chapter']; 				
				$new_chapter_in = 'm.id_chapter IN('.$chapter_ids.')';
				$where_part_sql_array[$i] = $new_chapter_in;  
			}
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
						m.image1 AS image1,m.image1_width AS image1_width,
						m.image1_height AS image1_height,
						m.is_appears_img1 AS is_appears_img1,
						m.image2 AS image2,m.image2_width AS image2_width,
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
			   
		$chapters_list = @$item->chapters_list;
		if(strpos($chapters_list,$_POST['id_chapter']) == false){
			if($chapters_list == "")
				$chapters_list = $_POST['id_chapter'];
			else 
				$chapters_list .= ','.$_POST['id_chapter'];
		}
		
		$tasks_list = @$item->tasks_list;
		if(strpos($tasks_list,$_POST['id_task']) == false){
			if($tasks_list == "")
				$tasks_list = $_POST['id_task'];
			else 
				$tasks_list .= ','.$_POST['id_task'];
		}
		
		$query = "UPDATE dne_custom_reports SET chapters_list = ?,
				  tasks_list = ?,sql_str = ? WHERE id = ?";
		$query = $mysqli->prepare($query);
		$query->bind_param('sssi',$chapters_list,$tasks_list,$new_sql_str,$item->id);
		$query->execute();
	}
}
?>