<?php 
include 'functions/functions.php';
session_start();

$meeting_ids_array = array();
$meeting_ids_array;

if($_POST['from'] == 'projects'){
	$ps1 = 'ארכיון';
	$ps2 = 'בוצע/נמסר';
	$ps3 = 'בהמתנה ';
	$is_active_project = 1;
	$is_active_tracking = 1;
	$empty_remark ='';
	$is_remark_appears_log = 1;
	$zero_int = 0;
	$empty_date = '0000-00-00';
	
	if(@$_POST['is_to_do_today'] == 0){
		if(@$_POST['currentProject'] == 0){
			$is_user_active = 1;

			if(@$_POST['list_user_tasks'] == 'me')
				$users_ids = '('.$_SESSION['id_user'].')';
			if(@$_POST['list_user_tasks'] == 'team'){
				$query = $mysqli->prepare("SELECT * FROM dne_users WHERE is_user_active = ?");
				$query->bind_param("i",$is_user_active);
				$query->execute();
				$query->store_result();
				$active_users = fetch($query);
				
				$ids = [];
				foreach($active_users as $item){
					$ids[] = $item->id;
				}

				$users_ids = '('.implode(',', $ids).')';
			}
			
			$user_tasks_sql = "SELECT m.id AS id,m.id_user AS id_user,
							   p.id AS p_id,p.nickname AS p_nickname,
							   c.name AS chapter_name,t.name_he AS task_name,
							   m.subject AS subject,m.area AS area,
							   m.description AS description,
							   m.id_responsible AS id_responsible,
							   r.name AS responsible_name,
							   r.email AS responsible_email,
							   m.is_priority AS is_priority,
						       m.track_type AS track_type,
							   m.reminder_time AS reminder_time,
						       m.reminder_date AS reminder_date,
							   m.id_track_responsible AS id_track_responsible					   
							   FROM dne_meetings m
							   LEFT JOIN dne_chapters c ON m.id_chapter = c.id
							   LEFT JOIN dne_tasks t ON m.id_task = t.id
							   LEFT JOIN dne_responsibles r ON m.id_responsible = r.id
							   LEFT JOIN dne_users u ON r.id_user = u.id
						       LEFT JOIN dne_progress_status ps ON m.id_progress_status = ps.id
					           LEFT JOIN dne_projects p ON m.id_project = p.id
				               WHERE ps.name_he <> ?          						   
						       AND ps.name_he <> ?
						       AND ps.name_he <> ?   
						       AND p.is_project_active = ?
							   AND r.id_user IN ".@$users_ids."
							   AND EXISTS (
									SELECT 1
									FROM dne_responsibles r
									WHERE r.id_project = p.id
									AND r.id_user IN ".@$users_ids."
							    )";
			if(@$_POST['filter_by']	== 'project')
				$user_tasks_sql .= "ORDER BY p.nickname";
			if(@$_POST['filter_by']	== 'task')
				$user_tasks_sql .= "ORDER BY t.name_he";
			if(@$_POST['filter_by']	== 'responsible')
				$user_tasks_sql .= "ORDER BY r.name";
			
			$query = $mysqli->prepare($user_tasks_sql);			
			$query->bind_param('sssi',$ps1,$ps2,$ps3,$is_active_project);	
			$query->execute(); 
			$query->store_result();
			$user_tasks = fetch($query);
					
			$query = $mysqli->prepare("SELECT m.id AS id,m.id_user AS id_user,
									   p.id AS p_id,p.nickname AS p_nickname,
									   c.name AS chapter_name,t.name_he AS task_name,
									   m.subject AS subject,m.area AS area,
									   m.description AS description,
									   m.id_responsible AS id_responsible,
									   r.name AS responsible_name,
									   r.email AS responsible_email,
									   m.is_priority AS is_priority,
									   m.track_type AS track_type,
									   m.reminder_time AS reminder_time,
									   m.reminder_date AS reminder_date,
									   m.id_track_responsible AS id_track_responsible	
									   FROM dne_meetings m
									   LEFT JOIN dne_chapters c ON m.id_chapter = c.id
									   LEFT JOIN dne_tasks t ON m.id_task = t.id
									   LEFT JOIN dne_responsibles r ON m.id_responsible = r.id
									   LEFT JOIN dne_progress_status ps ON m.id_progress_status = ps.id
									   LEFT JOIN dne_projects p ON m.id_project = p.id
									   WHERE ps.name_he <> ?          						   
									   AND ps.name_he <> ?
									   AND ps.name_he <> ?
									   AND p.is_project_active = ?
									   AND m.id_track_responsible = ?
									   AND m.track_type = ?
									   AND EXISTS (
											SELECT 1
											FROM dne_responsibles r
											WHERE r.id_project = p.id
											AND r.id_user = ?
									   )
									   ORDER BY m.id DESC");
			$query->bind_param('sssiiii',$ps1,$ps2,$ps3,$is_active_project,$_SESSION['id_user'],$is_active_tracking,$_SESSION['id_user']);	
			$query->execute(); 
			$query->store_result();
			$active_tracking = fetch($query);
			
			$query = $mysqli->prepare("SELECT ln.id_log_meeting_updates AS id_log_meeting_updates,
									  ln.id_log_meeting_tracking AS id_log_meeting_tracking,
									  lmu.id AS lmu_id,lmu.action AS action,lmu.action_date AS lmu_action_date,
						              COALESCE(TRIM(lmu.remark),'') AS lmu_remark,
                                      lmt.id AS lmt_id,
                                      lmt.action_date AS lmt_action_date,
						              COALESCE(TRIM(lmt.remark),'') AS lmt_remark,						   
						              m.id AS id,m.id_user AS id_user,
						              p.id AS p_id,p.nickname AS p_nickname,
						              p.lang AS  p_lang,
						              c.name AS chapter_name,t.name_he AS task_name,
						              m.subject AS subject,m.area AS area,
						              m.description AS description,
						              m.id_responsible AS id_responsible,
									  m.destination_date AS destination_date,
									  m.id_progress_status AS id_progress_status,
									  r.name AS responsible_name,
									  r.email AS responsible_email,
									  m.is_priority AS is_priority,
									  m.track_type AS track_type,
						    		  m.reminder_time AS reminder_time,
									  m.reminder_date AS reminder_date,
								      m.id_track_responsible AS id_track_responsible,
									  u.nickname AS user_nickname
									  FROM dne_log_news ln
									  LEFT JOIN dne_log_meeting_updates lmu ON ln.id_log_meeting_updates = lmu.id
									  LEFT JOIN dne_users u ON lmu.id_user = u.id
									  LEFT JOIN dne_log_meeting_tracking lmt ON ln.id_log_meeting_tracking = lmt.id
									  INNER JOIN dne_meetings m ON ln.id_meeting = m.id  
									  LEFT JOIN dne_chapters c ON m.id_chapter = c.id
									  LEFT JOIN dne_tasks t ON m.id_task = t.id
									  LEFT JOIN dne_responsibles r ON m.id_responsible = r.id
									  LEFT JOIN dne_progress_status ps ON m.id_progress_status = ps.id
									  LEFT JOIN dne_projects p ON m.id_project = p.id
						  WHERE (ps.name_he IS NULL OR (ps.name_he <> ? AND ps.name_he <> ? AND ps.name_he <> ?))
									  AND p.is_project_active = ?  
									  AND lmu.id_user <> ?
									  AND lmu.is_remark_appears_log = ?
									  AND NOT FIND_IN_SET(?,lmu.updated_users)
									  AND EXISTS (
											SELECT 1
											FROM dne_responsibles r
											WHERE r.id_project = p.id
											AND r.id_user = ?
									  )
									  ORDER BY GREATEST(COALESCE(lmu.action_date,'1970-01-01'), COALESCE(lmt.action_date,'1970-01-01')) DESC");
            $query->bind_param('sssiiiii',$ps1,$ps2,$ps3,$is_active_project,$_SESSION['id_user'],$is_remark_appears_log,$_SESSION['id_user'],$_SESSION['id_user']);
			$query->execute(); 
			$query->store_result();
			$what_news = fetch($query);
	    }
		else {
			$query = $mysqli->prepare("SELECT m.id AS id,m.id_user AS id_user,
									   p.id AS p_id,p.nickname AS p_nickname,
									   c.name AS chapter_name,t.name_he AS task_name,
									   m.subject AS subject,m.area AS area,
									   m.description AS description,
									   m.id_responsible AS id_responsible,
									   r.name AS responsible_name,
									   r.email AS responsible_email,
									   m.is_priority AS is_priority,
									   m.track_type AS track_type,
									   m.reminder_time AS reminder_time,
									   m.reminder_date AS reminder_date,
									   m.id_track_responsible AS id_track_responsible					   
									   FROM dne_meetings m
									   LEFT JOIN dne_chapters c ON m.id_chapter = c.id
									   LEFT JOIN dne_tasks t ON m.id_task = t.id
									   LEFT JOIN dne_responsibles r ON m.id_responsible = r.id
									   LEFT JOIN dne_users u ON r.id_user = u.id
									   LEFT JOIN dne_progress_status ps ON m.id_progress_status = ps.id
									   LEFT JOIN dne_projects p ON m.id_project = p.id
									   WHERE ps.name_he <> ?          						   
									   AND ps.name_he <> ?
									   AND ps.name_he <> ?
									   AND m.id_project = ?
									   AND r.id_user = ?
									   ORDER BY m.id DESC");
			$query->bind_param('sssii',$ps1,$ps2,$ps3,$_POST['currentProject'],$_SESSION['id_user']);
			$query->execute(); 
			$query->store_result();
			$user_tasks = fetch($query);
			
			$query = $mysqli->prepare("SELECT m.id AS id,m.id_user AS id_user,
									   p.id AS p_id,p.nickname AS p_nickname,
									   c.name AS chapter_name,t.name_he AS task_name,
									   m.subject AS subject,m.area AS area,
									   m.description AS description,
									   m.id_responsible AS id_responsible,
									   r.name AS responsible_name,
									   r.email AS responsible_email,
									   m.is_priority AS is_priority,
									   m.track_type AS track_type,
									   m.reminder_time AS reminder_time,
									   m.reminder_date AS reminder_date,
									   m.id_track_responsible AS id_track_responsible	
									   FROM dne_meetings m
									   LEFT JOIN dne_chapters c ON m.id_chapter = c.id
									   LEFT JOIN dne_tasks t ON m.id_task = t.id
									   LEFT JOIN dne_responsibles r ON m.id_responsible = r.id
									   LEFT JOIN dne_progress_status ps ON m.id_progress_status = ps.id
									   LEFT JOIN dne_projects p ON m.id_project = p.id
									   WHERE ps.name_he <> ?  
									   AND ps.name_he <> ?
									   AND ps.name_he <> ?
									   AND m.id_project = ?
									   AND m.id_track_responsible = ?
									   AND m.track_type = ?
							           ORDER BY m.id DESC");
			$query->bind_param('sssiii',$ps1,$ps2,$ps3,$_POST['currentProject'],$_SESSION['id_user'],$is_active_tracking);	
			$query->execute(); 
			$query->store_result();
			$active_tracking = fetch($query);
			
			$query = $mysqli->prepare("SELECT ln.id_log_meeting_updates AS id_log_meeting_updates,
								       ln.id_log_meeting_tracking AS id_log_meeting_tracking,
								       lmu.id AS lmu_id,lmu.action AS action,
									   lmu.action_date AS lmu_action_date,
									   lmu.remark as lmu_remark,
								       lmt.action_date AS lmt_action_date,
								       lmt.remark as lmt_remark,
								       p.id AS p_id,p.nickname AS p_nickname,
									   p.lang AS p_lang,
						               m.id AS id,m.id_user AS id_user,
									   c.name AS chapter_name,t.name_he AS task_name,
									   m.subject AS subject,m.area AS area,
									   m.description AS description,
							           m.id_responsible AS id_responsible,
									   m.destination_date AS destination_date,
									   m.id_progress_status AS id_progress_status,
									   r.name AS responsible_name,
									   r.email AS responsible_email,
									   m.is_priority AS is_priority,
									   m.track_type AS track_type,
							           m.reminder_time AS reminder_time,
							           m.reminder_date AS reminder_date,
									   m.id_track_responsible AS id_track_responsible,
									   u.nickname AS user_nickname,
                                       ps.name_he AS ps_name_he
							           FROM dne_log_news ln
									   LEFT JOIN dne_log_meeting_updates lmu ON ln.id_log_meeting_updates = lmu.id
									   LEFT JOIN dne_users u ON lmu.id_user = u.id
									   LEFT JOIN dne_log_meeting_tracking lmt ON ln.id_log_meeting_tracking = lmt.id
									   INNER JOIN dne_meetings m ON ln.id_meeting = m.id  
								       LEFT JOIN dne_chapters c ON m.id_chapter = c.id
									   LEFT JOIN dne_tasks t ON m.id_task = t.id
									   LEFT JOIN dne_responsibles r ON m.id_responsible = r.id
						   LEFT JOIN dne_progress_status ps ON m.id_progress_status = ps.id
						   LEFT JOIN dne_projects p ON m.id_project = p.id
					   WHERE (ps.name_he IS NULL OR (ps.name_he <> ? AND ps.name_he <> ? AND ps.name_he <> ?))
					   AND m.id_project = ?
					   AND lmu.id_user <> ?
									   AND lmu.is_remark_appears_log = ?
									   AND NOT FIND_IN_SET(?,lmu.updated_users)
					                   ORDER BY GREATEST(COALESCE(lmu.action_date,'1970-01-01'), COALESCE(lmt.action_date,'1970-01-01')) DESC");
			$query->bind_param('sssiiii',$ps1,$ps2,$ps3,$_POST['currentProject'],$_SESSION['id_user'],$is_remark_appears_log,$_SESSION['id_user']);
			$query->execute(); 
			$query->store_result();
			$what_news = fetch($query);
		}
			
		if($_POST['currentBadgeTarget'] == 'user_tasks'){
			foreach($user_tasks as $ut){
				array_push($meeting_ids_array,@$ut->id);
			}
		}

		if($_POST['currentBadgeTarget'] == 'active_tracking'){
			foreach($active_tracking as $at) {
				array_push($meeting_ids_array,@$at->id);
			}
		}
		
		if($_POST['currentBadgeTarget'] == 'what_news'){
			foreach($what_news as $wn) {
				array_push($meeting_ids_array,@$wn->id);
			}	
		}
	}
    else {
		$query = $mysqli->prepare("SELECT tdt.list_from AS list_from,
                                   m.id AS id,m.id_user AS id_user,
								   p.id AS p_id,p.nickname AS p_nickname,
						           c.name AS chapter_name,t.name_he AS task_name,
						           m.subject AS subject,m.area AS area,
						           m.description AS description,
						           m.id_responsible AS id_responsible,
						           r.name AS responsible_name,
						           r.email AS responsible_email,
						           m.is_priority AS is_priority,
						           m.track_type AS track_type,
							       m.reminder_time AS reminder_time,
								   m.reminder_date AS reminder_date,
						           m.id_track_responsible AS id_track_responsible					   
		                           FROM dne_to_do_today tdt 
						           LEFT JOIN dne_meetings m ON tdt.id_meeting = m.id
						           LEFT JOIN dne_chapters c ON m.id_chapter = c.id
						           LEFT JOIN dne_tasks t ON m.id_task = t.id
						           LEFT JOIN dne_responsibles r ON m.id_responsible = r.id
						           LEFT JOIN dne_users u ON r.id_user = u.id
						           LEFT JOIN dne_progress_status ps ON m.id_progress_status = ps.id
						           LEFT JOIN dne_projects p ON m.id_project = p.id  
						           WHERE ps.name_he <> ?
								   AND ps.name_he <> ?
								   AND ps.name_he <> ?
								   AND tdt.id_user = ?
						           AND (tdt.is_reminder = 0 OR m.id_track_responsible = ? OR m.id_track_responsible = ?)
						           AND (tdt.is_reminder = 0 OR DATE(reminder_date) <= CURDATE() OR reminder_date = ?)
								   ORDER BY tdt.is_reminder DESC,tdt.pin_date");
		$query->bind_param('sssiiis',$ps1,$ps2,$ps3,$_SESSION['id_user'],$zero_int,$_SESSION['id_user'],$empty_date);
		$query->execute(); 
		$query->store_result();
		$to_do_today_num_rows = $query->num_rows;
		$to_do_today = fetch($query);
		
		foreach($to_do_today as $tdt){
			array_push($meeting_ids_array,@$tdt->id);
		}
	}		
}
else if($_POST['from'] == 'meetings'){
	$query = $mysqli->prepare("SELECT * FROM dne_log_current_report WHERE id_project = ?");
	$query->bind_param("i",$_POST['id_project']);
	$query->execute();
	$query->store_result();
	$log_custom_report = fetch_unique($query);
	$id_custom_report = @$log_custom_report->id_custom_report;
	$id_rdv_report = @$log_custom_report->id_rdv_report;
  
    if($id_custom_report){
		$query = $mysqli->prepare(@$_POST['sql']);	
	}
	else if($id_rdv_report){
		$is_appears = 1;
		
		$archive_label = 'ארכיון';
		$query = "SELECT id FROM dne_progress_status WHERE name_he = ? AND id_project = ?";
		$query = $mysqli->prepare($query);
		$query->bind_param('si',$archive_label,$_POST['id_project']);   
		$query->execute();
		$query->store_result();
		$query = fetch_unique($query);
		$ps_archive_id = $query->id;
		
		$sql = "SELECT m.id AS id,m.is_priority AS is_priority,
		        m.id_chapter AS id_chapter,m.subject AS subject,m.ids_rdv AS ids_rdv,
				m.area AS area,m.description AS description,m.id_task AS id_task,
			    m.id_responsible AS id_responsible,m.id_pass_on AS id_pass_on,
			    m.task_creation_date AS task_creation_date,
		        m.destination_date AS destination_date,
				m.id_progress_status AS id_progress_status,m.id_task_type AS id_task_type,m.is_change_row_style AS is_change_row_style,
			    m.image1 AS image1,m.is_appears_img1 AS is_appears_img1,m.image1_width AS image1_width,m.image1_height AS image1_height,
			    m.image2 AS image2,m.is_appears_img2 AS is_appears_img2,m.image2_width AS image2_width,m.image2_height AS image2_height,
				m.updated_date AS updated_date,m.id_track_responsible AS id_track_responsible,m.track_type AS track_type, 
				m.reminder_time AS reminder_time
				FROM dne_meetings m 
				LEFT JOIN dne_tasks t ON m.id_task = t.id
				WHERE m.id_project = ? 
				AND m.is_appears = ? 
				AND FIND_IN_SET(?, m.ids_rdv) > 0
				AND m.id_progress_status <> ?
			    ORDER BY m.subject,m.id_area,t.name_he";
		
		$query = $mysqli->prepare($sql);
		$query->bind_param("iiii",$_POST['id_project'],$is_appears,$id_rdv_report,$ps_archive_id);
	}
	
	$query->execute(); 
	$query->store_result();
	$query = fetch($query);
		
	foreach($query as $item){
		array_push($meeting_ids_array,@$item->id);
	}
}

$meeting_ids_array = array_unique($meeting_ids_array);
$meeting_ids = implode(',',$meeting_ids_array);
echo trim($meeting_ids);
?>
