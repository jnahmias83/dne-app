<?php
include 'include/header.php';
include 'functions/functions.php';

if(isset($_GET['filter_by']))
	$_SESSION['filter_by'] = $_GET['filter_by'];
$filter_by = $_SESSION['filter_by'] ?? 'project';

if(isset($_GET['list']))
	$_SESSION['list_user_tasks'] = $_GET['list'];
$list_user_tasks = $_SESSION['list_user_tasks'] ?? 'me';

$query = $mysqli->prepare("SELECT * FROM dne_logos LIMIT 1");
$query->execute();
$query->store_result();
$logo = fetch_unique($query);

$is_inactive_project = @$_GET['is_inactive_project'];

if($is_inactive_project == 1) 
	$is_active_project = 0;

if($is_inactive_project == '') 
   $is_active_project = 1;

$title_add_project_btn = "הוסף פרוייקט";
$title_inactive_projects_btn = "פרוייקטים רדומים";

if($is_active_project){ 
	$query = $mysqli->prepare("SELECT p.*
							   FROM dne_projects p
							   WHERE p.is_project_active = ?
							   AND EXISTS (
									SELECT 1
									FROM dne_responsibles r
									WHERE r.id_project = p.id
									AND r.id_user = ?
								)
								ORDER BY p.nickname");	
	$query->bind_param('ii',$is_active_project,$_SESSION['id_user']);	
}
else {
	$query = $mysqli->prepare("SELECT * FROM dne_projects WHERE is_project_active = ? ORDER BY nickname");
	$query->bind_param('i',$is_active_project);	
}
	
$query->execute(); 
$query->store_result();
$projects = fetch($query);

$is_user_active = 1;
$query = $mysqli->prepare("SELECT * FROM dne_users WHERE is_user_active = ?");
$query->bind_param("i",$is_user_active);
$query->execute();
$query->store_result();
$active_users = fetch($query);

$query = $mysqli->prepare("SELECT * FROM dne_inputs_colors LIMIT 1");
$query->execute(); 
$query->store_result();
$bg_color_inputs = fetch_unique($query);

$ps1 = 'ארכיון';
$ps2 = 'בוצע/נמסר';
$ps3 = 'בהמתנה';
$update_action = 'סטטוס/יעד/הערה';
$is_active_tracking = 1;
$empty_remark ='';
$is_remark_appears_log = 1;
$zero_int = 0;
$empty_date = '0000-00-00';

$is_user_active = 1;

if(@$list_user_tasks == 'me')
	$users_ids = '('.$_SESSION['id_user'].')';
if(@$list_user_tasks == 'team'){
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

$all_user_tasks_sql =  "SELECT m.id AS id,m.id_user AS id_user,
                        p.id AS p_id,p.nickname AS p_nickname,
						p.lang AS p_lang,
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
if(empty(@$filter_by))
	$all_user_tasks_sql.= " ORDER BY m.id DESC";
else {
	if(@$filter_by == 'project')
		$all_user_tasks_sql.= " ORDER BY p.nickname";
	if(@$filter_by == 'task')
		$all_user_tasks_sql.= " ORDER BY t.name_he";
	if(@$filter_by == 'responsible')
		$all_user_tasks_sql.= " ORDER BY r.name";
}
						   
$query = $mysqli->prepare($all_user_tasks_sql);
$query->bind_param('sssi',$ps1,$ps2,$ps3,$is_active_project);	
$query->execute(); 
$query->store_result();
$all_user_tasks_num_rows = $query->num_rows;
$all_user_tasks = fetch($query);

$query = $mysqli->prepare("SELECT m.id AS id,m.id_user AS id_user,
						  p.id AS p_id,p.nickname AS p_nickname,
						  p.lang AS p_lang,
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
$all_active_tracking_num_rows = $query->num_rows;
$all_active_tracking = fetch($query);

$zero = 0.0;
$query = $mysqli->prepare("SELECT s.name_he AS s_name_he,
                           p.id AS p_id,p.nickname AS p_nickname,
						   a.submitted_account AS submitted_account,
                           ps.id AS ps_id
						   FROM dne_accounts a
						   LEFT JOIN dne_projects_suppliers ps ON a.id_projects_suppliers = ps.id
						   LEFT JOIN dne_projects p ON ps.id_project = p.id
						   LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
						   WHERE a.submitted_account > ? 
						   AND a.approved_amount = ? 
						   AND p.is_project_active = ?");
$query->bind_param("ddi",$zero,$zero,$is_active_project);
$query->execute(); 
$query->store_result();
$all_not_approved_accounts_num_rows = $query->num_rows; 
$all_not_approved_accounts = fetch($query);

$query = $mysqli->prepare("SELECT lmu.id AS lmu_id,lmu.updated_users,m.id AS id_meeting
                          FROM dne_log_meeting_updates lmu
						  INNER JOIN dne_meetings m ON lmu.id_meeting = m.id
						  LEFT JOIN dne_responsibles r ON m.id_responsible = r.id
						  LEFT JOIN dne_progress_status ps ON m.id_progress_status = ps.id
						  LEFT JOIN dne_projects p ON m.id_project = p.id
						  WHERE (ps.name_he IS NULL OR (ps.name_he <> ? AND ps.name_he <> ? AND ps.name_he <> ?))
					      AND p.is_project_active = ?
						  AND lmu.id_user <> ?
						  AND lmu.is_remark_appears_log = ?
						  AND EXISTS (
								SELECT 1
								FROM dne_responsibles r
								WHERE r.id_project = p.id
								AND r.id_user = ?
						   )
						   ORDER BY lmu.id");
$query->bind_param('sssiiii',$ps1,$ps2,$ps3,$is_active_project,$_SESSION['id_user'],$is_remark_appears_log,$_SESSION['id_user']);
$query->execute(); 
$query->store_result();
$query = fetch($query);

foreach($query as $item){
	$users_array = array_map('trim',explode(',',$item->updated_users));

    if(!in_array($_SESSION['id_user'],$users_array)){
		$query = $mysqli->prepare("SELECT id FROM dne_log_news WHERE id_meeting = ?");
		$query->bind_param('i',$item->id_meeting);
		$query->execute(); 
		$query->store_result();
		
		if(@$query->num_rows == 0){
			$query = "INSERT INTO dne_log_news (id_meeting,id_log_meeting_updates) VALUES (?,?)";
			$query = $mysqli->prepare($query);
			$query->bind_param('ii',$item->id_meeting,$item->lmu_id);
			$query->execute();
		}
		else {
			$query = "UPDATE dne_log_news SET id_log_meeting_updates = ? WHERE id_meeting = ?";
			$query = $mysqli->prepare($query);
			$query->bind_param('ii',$item->lmu_id,$item->id_meeting);	
			$query->execute();   
		}
	}
}

$query = $mysqli->prepare("SELECT lmt.id AS lmt_id,lmt.updated_users,m.id AS id_meeting 
                          FROM dne_log_meeting_tracking lmt
						  LEFT JOIN dne_meetings m ON lmt.id_meeting = m.id 
						  LEFT JOIN dne_responsibles r ON m.id_responsible = r.id
						  LEFT JOIN dne_progress_status ps ON m.id_progress_status = ps.id
						  LEFT JOIN dne_projects p ON m.id_project = p.id
						  WHERE ps.name_he <> ?  
						  AND ps.name_he <> ?
						  AND ps.name_he <> ?
					      AND p.is_project_active = ?  
						  AND lmt.id_user <> ?
						  AND lmt.is_remark_appears_log = ?
						  AND EXISTS (
								SELECT 1
								FROM dne_responsibles r
								WHERE r.id_project = p.id
								AND r.id_user = ?
						  )
						  ORDER BY lmt.id");	   
$query->bind_param('sssiiii',$ps1,$ps2,$ps3,$is_active_project,$_SESSION['id_user'],$is_remark_appears_log,$_SESSION['id_user']);
$query->execute(); 
$query->store_result();
$query = fetch($query);

foreach($query as $item){
	$users_array = array_map('trim',explode(',',$item->updated_users));

    if(!in_array($_SESSION['id_user'],$users_array)){
		$query = $mysqli->prepare("SELECT lmu.id 
                                   FROM dne_log_meeting_updates lmu
						           LEFT JOIN dne_meetings m ON lmu.id_meeting = m.id 
						           LEFT JOIN dne_responsibles r ON m.id_responsible = r.id
						           LEFT JOIN dne_progress_status ps ON m.id_progress_status = ps.id
						           LEFT JOIN dne_projects p ON m.id_project = p.id
						           WHERE ps.name_he <> ?  
						           AND ps.name_he <> ?
						           AND ps.name_he <> ?
					               AND p.is_project_active = ?  
								   AND lmu.id_meeting = ?
						           AND lmu.id_user <> ?
						           AND lmu.is_remark_appears_log = ?
						           AND EXISTS (
									    SELECT 1
											FROM dne_responsibles r
											WHERE r.id_project = p.id
											AND r.id_user = ?
						           )");
                                   
								   $query->bind_param('sssiiiii',$ps1,$ps2,$ps3,$is_active_project,$item->id_meeting,$_SESSION['id_user'],$is_remark_appears_log,$_SESSION['id_user']);
                                   $query->execute(); 
                                   $query->store_result();
								   
								   if(@$query->num_rows > 0){
									   $query = "UPDATE dne_log_news SET id_log_meeting_tracking = ? 
									             WHERE id_meeting = ?";
									   $query = $mysqli->prepare($query);
									   $query->bind_param('ii',$item->lmt_id,$item->id_meeting);	
									   $query->execute();   
								   }									   
	}
}

$query = $mysqli->prepare("SELECT ln.id_log_meeting_updates AS id_log_meeting_updates,
                           ln.id_log_meeting_tracking AS id_log_meeting_tracking,
                           lmu.id AS lmu_id,lmu.action_date AS lmu_action_date,
						   lmu.updated_users AS lmu_updated_users,
						   lmu.action AS lmu_action,
						   COALESCE(TRIM(lmu.remark),'') AS lmu_remark,
                           lmt.id AS lmt_id,lmt.action_date AS lmt_action_date,
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
					   u.nickname AS user_nickname,
					   ps.name_he AS current_status_name_he,
					(SELECT lmu2.remark FROM dne_log_meeting_updates lmu2 WHERE lmu2.id_meeting = m.id AND TRIM(lmu2.remark) <> '' AND lmu2.is_remark_appears_log = 1 ORDER BY lmu2.id DESC LIMIT 1) AS latest_lmu_remark,
					(SELECT ps2.name_he FROM dne_log_meeting_updates lmu3 LEFT JOIN dne_progress_status ps2 ON ps2.id = lmu3.id_progress_status WHERE lmu3.id_meeting = m.id AND lmu3.id_progress_status <> 0 AND lmu3.id_user <> ? ORDER BY lmu3.id DESC LIMIT 1) AS lmu_progress_status_name,
					(SELECT lmu4.destination_date FROM dne_log_meeting_updates lmu4 WHERE lmu4.id_meeting = m.id AND lmu4.destination_date <> '0000-00-00' AND lmu4.id_user <> ? ORDER BY lmu4.id DESC LIMIT 1) AS lmu_destination_date
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
					       ORDER BY GREATEST(COALESCE(lmu.action_date,'1970-01-01'), COALESCE(lmt.action_date,'1970-01-01')) DESC,
					       GREATEST(COALESCE(lmu.id,0), COALESCE(lmt.id,0)) DESC");
$query->bind_param('iisssiiiii',$_SESSION['id_user'],$_SESSION['id_user'],$ps1,$ps2,$ps3,$is_active_project,$_SESSION['id_user'],$is_remark_appears_log,$_SESSION['id_user'],$_SESSION['id_user']);
$query->execute();
$query->store_result();
$all_what_news_num_rows = $query->num_rows;
$all_what_news = fetch($query);

$query = $mysqli->prepare("SELECT tdt.list_from AS list_from,
                           m.id AS id,m.id_user AS id_user,
                           p.id AS p_id,p.nickname AS p_nickname,
						   p.lang AS p_lang,
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

$wn_meeting_ids_array = array();
foreach($all_what_news as $wn){
	if(!in_array(@$wn->id,$wn_meeting_ids_array))
		array_push($wn_meeting_ids_array,@$wn->id);
}
?> 			
        <form method="post" action="" class="form-inline">
            <input type="hidden" id="is_active_project" value="<?=@$is_active_project?>" />	
			<input type="hidden" id="wn_meeting_ids" value="<?=implode(',',@$wn_meeting_ids_array)?>" />  			
			<input type="hidden" id="filter_by" value="<?=@$filter_by?>" />
            <input type="hidden" id="list_user_tasks" value="<?=@$list_user_tasks?>" />				
			
			<div class="container">	
			    <div class="width550" id="contentToScreenshot"></div>	
			
			    <div class="row marginTop25 alignCenter">
					<div class="col-12">
						<a id="logo_link" class="cursor-pointer">
						  <img src="uploads/<?=@$logo->logo?>" width="170" height="170" />
						</a>
					</div>
			    </div>
				
				<div id="div_top_buttons" class="flex flex-wrap width-one-of-three margin-top-10-x-auto justify-content-center dir-rtl alignCenter">
				    <div class="width25Percents">
					    <a class="text-decoration-none cursor-pointer _badge-switcher borderRadius10" href="#" title="משימות שלי" data-target="user_tasks" data-bgcolor="<?=@$bg_color_inputs->a_bgcolor?>">
							<span class="padding-7x-3y borderRadius20 colorWhite font-weight-bold" style="background-color:<?=@$bg_color_inputs->e_bgcolor?>;box-shadow: 0 6px 12px rgba(0, 0, 0, 0.35);"><?=@$all_user_tasks_num_rows?></span>	
							<div class="marginTop5">
								<label class="fontSize12 color-1a5276 font-weight-bold">משימות שלי</label>
							</div>
					    </a>
				    </div>	
                    <div class="width25Percents">					
						<a class="text-decoration-none cursor-pointer _badge-switcher" href="#" title="מעקב אקטיבי" data-target="active_tracking" data-bgcolor="<?=@$bg_color_inputs->b_bgcolor?>">
							<span class="padding-7x-3y borderRadius20 colorWhite font-weight-bold" style="background-color:<?=@$bg_color_inputs->f_bgcolor?>;box-shadow: 0 6px 12px rgba(0, 0, 0, 0.35);"><?=@$all_active_tracking_num_rows?></span>
							<div class="marginTop5">
								<label class="fontSize12 color-1a5276 font-weight-bold">מעקב אקטיבי</label>
							</div>
						</a>
					</div>
					<div class="width25Percents">
                        <a class="text-decoration-none cursor-pointer _badge-switcher" href="#" title="תקציב" data-target="not_approved_accounts" data-bgcolor="<?=@$bg_color_inputs->c_bgcolor?>">
							<span class="padding-7x-3y borderRadius20 colorWhite font-weight-bold" style="background-color:<?=@$bg_color_inputs->g_bgcolor?>;box-shadow: 0 6px 12px rgba(0, 0, 0, 0.35);"><?=@$all_not_approved_accounts_num_rows?></span>
							<div class="marginTop5">
								<label class="fontSize12 color-1a5276 font-weight-bold">תקציב</label>
							</div>
						</a> 
					</div>
                    <div class="width25Percents">					
						<a class="text-decoration-none cursor-pointer _badge-switcher" href="#" title="מה חדש" data-target="what_news" data-bgcolor="<?=@$bg_color_inputs->d_bgcolor?>">
							<span class="padding-7x-3y borderRadius20 colorWhite font-weight-bold" style="background-color:<?=@$bg_color_inputs->h_bgcolor?>;box-shadow: 0 6px 12px rgba(0, 0, 0, 0.35);"><?=@$all_what_news_num_rows?></span>
							<div class="marginTop5">
								<label class="fontSize12 color-1a5276 font-weight-bold">מה חדש ?</label>
							</div>
						</a>		
					</div>
				</div>				

               	<div id="div_user_tasks" class="display-none border-black dir-rtl">				
					<div class="row alignRight">
						<div class="col-12">
							<input type="button" id="btn-close-user-tasks" class="borderRadius10 fontSize13 font-weight-bold" style="color:<?=@$bg_color_inputs->e_bgcolor?>; border:1px solid <?=@$bg_color_inputs->e_bgcolor?>" value="X" />
						</div>							  
					</div>
					
					<div class="row dir-rtl" id="user_tasks_filter_row">
						<div id="div_filter_user_tasks" class="col-6 alignCenter">
							<div class="text-center flex-grow-1">
								<span class="fontSize13 font-weight-bold">מיון לפי</span>
							</div>
							<div id="filter_user_tasks_radios">
								<div class="filter-radio-row"><input type="radio" id="by_project" name="user_tasks_filter" value="project" onclick="setFilterParam('project')" <?php if(@$filter_by == 'project') echo 'checked';?> />&nbsp;<span class="fontSize12">פרוייקט</span></div>
								<div class="filter-radio-row"><input type="radio" id="by_task" name="user_tasks_filter" value="task" onclick="setFilterParam('task')" <?php if(@$filter_by == 'task') echo 'checked';?> />&nbsp;<span class="fontSize12">סוג משימה</span></div>
								<div class="filter-radio-row"><input type="radio" id="by_responsible" name="user_tasks_filter" value="responsible" onclick="setFilterParam('responsible')" <?php if(@$filter_by == 'responsible') echo 'checked';?> />&nbsp;<span class="fontSize12">אחראי</span></div>
							</div>
						</div>
						
						<div class="col-6 btn-group btn-group-toggle dir-rtl">
							<label id="label-team" class="btn btn-secondary fontSize12 <?php if($list_user_tasks == 'team') echo 'active'; ?>">
								<input type="radio" id="team" name="options" class="width50 height10" autocomplete="off" onclick="setListParam('team')" /> TEAM
							</label>
							<label id="label-me" class="btn btn-secondary fontSize12 <?php if($list_user_tasks == 'me') echo 'active'; ?>">
								<input type="radio" id="me" name="options" class="width50 height10" autocomplete="off" onclick="setListParam('me')" /> ME
							</label>
						</div>
					</div>	

					<div class="row marginTop5">
					    <div class="col-12 alignCenter">
							<strong style="color:<?=@$bg_color_inputs->e_bgcolor?>">המשימות שלי</strong>
				        </div>
					</div>
					
					<div class="marginTop10 width100Percents overflow-y-scroll scrollbar-colored alignCenter dir-rtl" style="max-height:530px;">
						<table align="center" class="dir-rtl" cellpadding="4" width="100%">
							<?php foreach ($all_user_tasks as $ut){								
								$user_id = @$ut->id_user;
								
								$reminder_time = @$ut->reminder_time;
                                $reminder_date = @$ut->reminder_date;								
						
								$id_track_responsible = @$ut->id_track_responsible;
								$track_type = @$ut->track_type;			
						
								$chapter_name = stripNbspArtifact(@$ut->chapter_name);
								if(strlen(@$chapter_name) > 50)
								   $chapter_name = mb_substr($chapter_name,0,50,'UTF-8');

							    $responsible_email = @$ut->responsible_email;

							    $task_name = @$ut->task_name;
								if(strlen(@$task_name) > 50)
								   $task_name = mb_substr($task_name,0,50,'UTF-8');

								$subject = stripNbspArtifact(@$ut->subject);
								if(mb_strlen($subject,'UTF-8') > 30)
								   $subject = mb_substr(trim(preg_replace('/[\x{00A0}\x{200B}]+/u',' ',strip_tags($subject))),0,30,'UTF-8').'...';

								$area = stripNbspArtifact(@$ut->area);
								if(mb_strlen($area,'UTF-8') > 30)
								   $area = mb_substr(trim(preg_replace('/[\x{00A0}\x{200B}]+/u',' ',strip_tags($area))),0,30,'UTF-8').'...';

								$user_nickname = @$ut->user_nickname;
								?>
								<tr class="task-row fontSize13 meeting_<?=@$ut->id?>">
									<td>
										<a id="task_name_<?=@$ut->id?>" class="text-decoration-none w-100">
											<div class="flex flex-wrap justify-content-center align-items-center task_name width100Percents cursor-pointer" data-projectnickname="<?=@$ut->p_nickname?>" data-meetingid="<?=@$ut->id?>" data-projectid="<?=@$ut->p_id?>" data-userid="<?=@$user_id?>" data-lang="<?=@$ut->p_lang?>" data-chapter="<?=@$ut->chapter_name?>" data-name="<?=@$ut->subject?>" data-area="<?=@$ut->area?>" data-recipient="<?=@$responsible_email?>" data-responsibleid="<?=@$ut->id_responsible?>" data-destinationdate="<?=@$ut->destination_date?>" data-progresstatusid="<?=@$ut->id_progress_status?>" data-ispriority="<?=@$ut->is_priority?>" data-trackresponsibleid="<?=@$id_track_responsible?>" data-tracktype="<?=@$track_type?>" data-reminderdate="<?=@$reminder_date?>" data-remindertime="<?=@$reminder_time?>" data-istodotoday="0">
												<div class="width15Percents">
													<a class="drag-task cursor-pointer alignCenter text-decoration-none" data-id="<?=@$ut->id?>" data-listfrom="user_tasks">
														<i class="fa-solid fa-thumbtack"></i>
													</a>
													
													<span class="colorWhite bgColor-1a5276 border-black borderRadius10 padding-4x-4y fw-bold fontSize9">
														<?=@$ut->p_nickname?>
													</span>
													<?php if($list_user_tasks == 'team'){ ?>
													<span class="marginTop5 marginRight5 border-black padding-4x-4y borderRadius20 fontSize9 align-items-center justify-content-center colorWhite bgColorRed width25 alignCenter" style="display:block;line-height:1;">
														<?=@$user_nickname?>
													</span>
													<?php } ?>
												</div>
												<div class="width85Percents">
													<div class="flex flex-wrap justify-content-center task-title-row marginTop5" style="line-height:0.1">
														<div class="width65Percents align-items-center">
															<span class="color-19bf42 font-weight-bold"><?=@$chapter_name?></span>
															<!--<span class="color-1A5276 font-weight-bold">|</span>
															<span><?=@$task_name?></span>!-->				
														</div>
														<div class="marginTop5 width35Percents alignLeft">
															<?php if(@$task_name != ''){ ?>
																	<span class="color-1A5276 font-weight-bold fontSize12">
																		<?=@$task_name?>
																	</span>
															<?php } ?>
														</div>
													</div>													
													
													<div class="marginTop5 flex flex-wrap justify-content-center">
														<div class="width100Percents">
															<span class="color-1A5276 font-weight-bold"><?=@$subject?></span>
															<span class="color-1A5276 font-weight-bold">|</span>
															<span class="color-1A5276"><?=@$area?></span>
														</div>
													</div>
													<?php if($track_type == 1){
														$one_ut = 1; $empty_ut = '';
														$q_ut = $mysqli->prepare("SELECT lmt.action_date,lmt.remark,u.nickname AS user_nickname FROM dne_log_meeting_tracking lmt LEFT JOIN dne_users u ON lmt.id_user = u.id WHERE lmt.id_meeting = ? AND lmt.remark <> ? AND lmt.is_remark_appears_log = ? ORDER BY lmt.id DESC");
														$q_ut->bind_param('isi',$ut->id,$empty_ut,$one_ut);
														$tr_ut = fetch($q_ut);
														if($q_ut->num_rows > 0){ ?>
														<div class="d-flex flex-column" id="tracking_log_<?=@$ut->id?>">
															<?php foreach($tr_ut as $tr_item){
																$tr_remark = @$tr_item->remark;
																if(mb_strlen(@$tr_remark,'UTF-8') > 30) $tr_remark = mb_substr($tr_remark,0,30,'UTF-8').'...';
															?>
															<div class="marginTop5 fontSize11 text-end" style="line-height:1">
																<span class="border-black padding-2x-2y fontSize9 borderRadius20 align-items-center justify-content-center colorWhite bgColorBlack" style="display:inline-flex;line-height:1;background-color:#f2f6f9;color:#c0392b;font-weight:bold;"><?=@$tr_item->user_nickname?></span>
																<span class="marginRight2 log-date-grey dir-rtl unicode-bidi-embed"><?=smartDate(@$tr_item->action_date)?> -</span>
																<span class="colorRed dir-rtl unicode-bidi-embed display-inline-block"><?=html_entity_decode(@$tr_remark)?></span>
															</div>
															<?php break; } ?>
														</div>
													<?php } } ?>
												</div>
											</div>
										</a>
									</td>
								</tr>
						<?php } ?>
						</table>
					</div>
				</div>

                <div id="div_active_tracking" class="display-none border-black dir-rtl">									
					<div class="row fontSize16 position-relative">
						<div class="col-12 position-relative">
							<strong class="d-block text-center" style="color:<?=@$bg_color_inputs->f_bgcolor?>">מעקב אקטיבי</strong>
							<input type="button" id="btn-close-active-tracking" class="borderRadius10 fontSize16 font-weight-bold position-absolute" style="top:0;right:13;color:<?=@$bg_color_inputs->f_bgcolor?>;border:1px solid <?=@$bg_color_inputs->f_bgcolor?>" value="X" />
						</div>
					</div>							
									
					<div class="marginTop10 overflow-y-scroll scrollbar-colored alignCenter dir-rtl" style="max-height:530px;">
						<table align="center" class="dir-rtl" cellpadding="4" width="100%">
							<?php foreach ($all_active_tracking as $at){ 
								$user_id = @$at->id_user;
								
								$reminder_time = @$at->reminder_time;	
							    $reminder_date= @$at->reminder_date;						
								
								$id_track_responsible = @$at->id_track_responsible;
								$query = $mysqli->prepare("SELECT nickname FROM dne_users WHERE id = ?");
								$query->bind_param('i',$id_track_responsible);	
								$query->execute(); 
								$query->store_result();
								$track_responsible = fetch_unique($query);
								$track_responsible_name = @$track_responsible->nickname;
								
								$track_type = @$at->track_type;			
						
								$chapter_name = stripNbspArtifact(@$at->chapter_name);
								if(strlen(@$chapter_name) > 50)
								   $chapter_name = mb_substr($chapter_name,0,50,'UTF-8');

							    $responsible_name = @$at->responsible_name;
								if(mb_strlen(@$responsible_name,'UTF-8') > 20)
									$responsible_name = mb_substr($responsible_name,0,20,'UTF-8').'...';
								$responsible_email = @$at->responsible_email;

						        $task_name = @$at->task_name;
								if(strlen(@$task_name) > 50)
								   $task_name = mb_substr($task_name,0,50,'UTF-8');

								$subject = stripNbspArtifact(@$at->subject);
								if(mb_strlen($subject,'UTF-8') > 30)
								   $subject = mb_substr(trim(preg_replace('/[\x{00A0}\x{200B}]+/u',' ',strip_tags($subject))),0,30,'UTF-8').'...';

								$area = stripNbspArtifact(@$at->area);
								if(mb_strlen($area,'UTF-8') > 30)
								   $area = mb_substr(trim(preg_replace('/[\x{00A0}\x{200B}]+/u',' ',strip_tags($area))),0,30,'UTF-8').'...';
                                 																						
								$one = 1;
								$empty_remark ='';
								$query = $mysqli->prepare("SELECT lmt.action_date,lmt.remark,
 								                           u.nickname AS user_nickname
														   FROM dne_log_meeting_tracking lmt
														   LEFT JOIN dne_users u ON lmt.id_user = u.id
														   WHERE lmt.id_meeting = ?
														   AND lmt.remark <> ?
														   AND lmt.is_remark_appears_log = ?
														   ORDER BY lmt.id DESC");
								$query->bind_param('isi',$at->id,$empty_remark,$one);	
								$query->execute(); 
								$query->store_result();
								$log_meeting_tracking_num_rows = $query->num_rows;
								$log_meeting_tracking = fetch($query);
								?>
								<tr class="task-row fontSize13 meeting_<?=@$at->id?>">
									<td>
										<a id="task_name_<?=@$at->id?>" class="text-decoration-none w-100">
											<div class="flex flex-wrap justify-content-center align-items-center task_name cursor-pointer" data-projectnickname="<?=@$at->p_nickname?>" data-meetingid="<?=@$at->id?>" data-projectid="<?=@$at->p_id?>" data-userid="<?=@$user_id?>" data-lang="<?=@$at->p_lang?>" data-chapter="<?=@$at->chapter_name?>" data-name="<?=@$at->subject?>" data-area="<?=@$at->area?>" data-recipient="<?=@$responsible_email?>" data-responsibleid="<?=@$at->id_responsible?>" data-destinationdate="<?=@$at->destination_date?>" data-progresstatusid="<?=@$at->id_progress_status?>" data-ispriority="<?=@$at->is_priority?>" data-trackresponsibleid="<?=@$id_track_responsible?>" data-tracktype="<?=@$track_type?>" data-reminderdate="<?=@$reminder_date?>" data-remindertime="<?=@$reminder_time?>" data-istodotoday="0">
												<div class="width15Percents">		
													<a class="drag-task cursor-pointer alignCenter text-decoration-none" data-id="<?=@$at->id?>" data-listfrom="active_tracking">
														<i class="fa-solid fa-thumbtack"></i>
													</a>
													
													<span class="colorWhite bgColor-1a5276 border-black borderRadius10 padding-4x-4y fw-bold fontSize9">
														<?=@$at->p_nickname?>
													</span>
												</div>
												<div class="width85Percents">
													<div class="flex flex-wrap justify-content-center task-title-row" style="line-height:0.1">
														<div class="marginTop5 width65Percents align-items-center">
															<span class="color-19bf42 font-weight-bold"><?=@$chapter_name?></span>
															<!--<span class="color-1A5276 font-weight-bold">|</span>
															<span class=""><?=@$task_name?></span>!-->
														</div>
														<div class="marginTop5 width35Percents alignLeft">
															<?php if(@$responsible_name != ''){ ?>
																	<span class="color-1A5276 font-weight-bold fontSize12">
																		<?=@$responsible_name?>
																	</span>
															<?php } ?>
														</div>
													</div>
													<div class="marginTop5 flex flex-wrap justify-content-center">
														<div class="width100Percents">
															<span class="color-1A5276 font-weight-bold"><?=@$subject?></span>
															<span class="color-1A5276 font-weight-bold">|</span>
															<span class="color-1A5276"><?=@$area?></span>
														</div>
													</div>
													<?php if($log_meeting_tracking_num_rows > 0){ ?>
															<div class="d-flex flex-column">
																<?php foreach($log_meeting_tracking as $item){ 
																        $remark = @$item->remark;
																		if(mb_strlen(@$remark,'UTF-8') > 30)
																			$remark = mb_substr($remark,0,30,'UTF-8').'...';
																?>
																	<div class="marginTop5 fontSize11 text-end" style="line-height:1">
																			<span class="border-black padding-2x-2y fontSize9 borderRadius20 align-items-center justify-content-center colorWhite bgColorBlack" style="display:inline-flex;line-height:1;background-color:#f2f6f9;color:#c0392b;font-weight:bold;">
																				<?=@$item->user_nickname?>
																			</span>
																			<span class="marginRight2 log-date-grey dir-rtl unicode-bidi-embed">
																				<?php
																				$formatter = new IntlDateFormatter(
																					'he_IL',
																					IntlDateFormatter::NONE,
																					IntlDateFormatter::NONE,
																					null,
																					null,
																					'd MMM'
																				);
																				$action_date = new DateTime(@$item->action_date);
																				echo $formatter->format($action_date)?>
																			</span>
																			-
																			<span class="colorRed dir-rtl unicode-bidi-embed display-inline-block">
																				<?=html_entity_decode(@$remark)?>
																			</span>
																		</div>
																<?php break; } ?>
															</div>
													<?php } ?>
												</div>								
											</div>
										 </a>															
									</td>
								</tr>
						<?php } ?>
						</table>	
					</div>								
				</div>

                <div id="div_not_approved_accounts" class="display-none border-black dir-rtl" style="max-height:600px;">					
					<div class="row fontSize16 position-relative">
						<div class="col-12 position-relative">
							<strong class="d-block text-center" style="color:<?=@$bg_color_inputs->g_bgcolor?>">תקציב</strong>
							<input type="button" id="btn-close-not-approved-accounts" class="borderRadius10 fontSize16 font-weight-bold position-absolute" style="top:0;right:13;color:<?=@$bg_color_inputs->g_bgcolor?>;border:1px solid <?=@$bg_color_inputs->g_bgcolor?>" value="X" />
						</div>
					</div>		
									
					<?php foreach($all_not_approved_accounts as $nap){ ?>
						<div class="flex flex-wrap margin-top-10-x-auto width70Percents colorBlack font-weight-bold alignCenter dir-rtl">
						   <div class="col-12 cursor-pointer">
								<a class="text-decoration-none" href="accounts_payments.php?ps_id=<?=@$nap->ps_id?>&from=projects&lang_screen=HE">
									<?=@$nap->p_nickname.' '.$nap->s_name_he.number_format(@$nap->submitted_account,2,'.',',').'&#8362;'?>
								</a>    
						   </div>
						</div>
					<?php } ?>	
				</div>

                <div id="div_what_news" class="display-none border-black dir-rtl" style="overflow:hidden;border-radius:10px;">
					<div class="row fontSize16 position-relative" style="background-color:<?=@$bg_color_inputs->h_bgcolor?>;padding:8px 0;margin:0;width:100%;">
						<div class="col-12 position-relative">
							<strong class="d-block text-center what-news-title" style="color:#ffffff;font-weight:bold;"><i class="fa fa-bell" style="color:#ffffff;margin:0 15px;"></i>מה חדש</strong>
							<div class="d-flex align-items-center position-absolute what-news-header-actions" style="top:0;right:13;gap:8px;">
								<button type="button" class="btn btn-primary btn-sm fontSize12 whats-new-mark-seen-btn" data-panel="#left_new_content" onclick="markCheckedWhatsNewSeen('#left_new_content')" disabled>תודה על העדכון</button>
								<input type="button" id="btn-close-what-news" class="borderRadius10 fontSize16 font-weight-bold" style="color:#ffffff;background-color:<?=@$bg_color_inputs->h_bgcolor?>;border:1px solid #ffffff" value="X" />
							</div>
						</div>
					</div>
									
					<div class="marginTop10 width100Percents overflow-y-scroll scrollbar-colored alignCenter dir-rtl" style="max-height:530px;">
						<table align="center" class="dir-rtl" cellpadding="4" width="100%">
							<?php foreach ($all_what_news as $wn){
								$user_id = @$wn->id_user;
								
								$reminder_time = @$wn->reminder_time;	
								$reminder_date = @$wn->reminder_date;		
								
								$id_track_responsible = @$wn->id_track_responsible;
								$track_type = @$wn->track_type;			
						
								$chapter_name = stripNbspArtifact(@$wn->chapter_name);
								if(strlen(@$chapter_name) > 50)
								   $chapter_name = mb_substr($chapter_name,0,50,'UTF-8');

							    $responsible_name = @$wn->responsible_name;
								if(mb_strlen(@$responsible_name,'UTF-8') > 20)
									$responsible_name = mb_substr($responsible_name,0,20,'UTF-8').'...';
								$responsible_email = @$wn->responsible_email;

							    $task_name = @$wn->task_name;
								if(strlen(@$task_name) > 50)
								   $task_name = mb_substr($task_name,0,50,'UTF-8');

								$subject = stripNbspArtifact(@$wn->subject);
								if(mb_strlen(@$subject,'UTF-8') > 30)
								   $subject = mb_substr(trim(preg_replace('/[\x{00A0}\x{200B}]+/u',' ',strip_tags($subject))),0,30,'UTF-8').'...';

								$area = stripNbspArtifact(@$wn->area);
								if(mb_strlen(@$area,'UTF-8') > 30)
								   $area = mb_substr(trim(preg_replace('/[\x{00A0}\x{200B}]+/u',' ',strip_tags($area))),0,30,'UTF-8').'...';
								
								$query = $mysqli->prepare("SELECT ps.name_he AS ps_name_he,
								                           lmu.destination_date AS lmu_destination_date
														   FROM dne_log_meeting_updates lmu
														   LEFT JOIN dne_progress_status ps ON lmu.id_progress_status = ps.id
														   WHERE lmu.id = ?");
								$query->bind_param("i",$wn->lmu_id);
								$query->execute(); 
								$query->store_result();	
								$query = fetch_unique($query);					
								$progress_status_name = @$query->ps_name_he;
								$destination_date = @$query->lmu_destination_date;
								$track_responsible_name = '';
								if($id_track_responsible > 0){
									$q_tr = $mysqli->prepare("SELECT nickname FROM dne_users WHERE id = ?");
									$q_tr->bind_param('i',$id_track_responsible);
									$track_responsible = fetch_unique($q_tr);
									$track_responsible_name = @$track_responsible->nickname;
								}

								if($reminder_date != '0000-00-00' || @$track_responsible_name != '')
									$tracking_data = '(';

								if($reminder_date != '0000-00-00')
									$tracking_data .= smartDate($reminder_date);

								if($reminder_date != '0000-00-00' && $track_responsible_name != '')
									$tracking_data .= ',';

								if(@$track_responsible_name != '')
									$tracking_data .= @$track_responsible_name;

								if($reminder_date != '0000-00-00' || @$track_responsible_name != '')
									$tracking_data .= ')';
								$log_meeting_tracking_num_rows_wn = 0; 
								$log_meeting_tracking_wn = new stdClass();
								
								if($track_type == 1){
									$one_wn = 1; $empty_wn = '';
									$q_wn = $mysqli->prepare("SELECT lmt.action_date,lmt.remark,u.nickname AS user_nickname FROM dne_log_meeting_tracking lmt LEFT JOIN dne_users u ON lmt.id_user = u.id WHERE lmt.id_meeting = ? AND lmt.remark <> ? AND lmt.is_remark_appears_log = ? ORDER BY lmt.id DESC");
									$q_wn->bind_param('isi',$wn->id,$empty_wn,$one_wn);
									$log_meeting_tracking_wn = fetch($q_wn);
									$log_meeting_tracking_num_rows_wn = $q_wn->num_rows;
								}
								?>
								<tr class="task-row fontSize13 meeting_<?=@$wn->id?>">
									<td>
										<a id="task_name_<?=@$wn->id?>" class="text-decoration-none w-100 d-block">
											<div class="marginTop5 marginBottom5 flex flex-wrap justify-content-center align-items-start task_name width100Percents cursor-pointer" data-projectnickname="<?=@$wn->p_nickname?>" data-meetingid="<?=@$wn->id?>" data-projectid="<?=@$wn->p_id?>" data-userid="<?=@$user_id?>" data-lang="<?=@$wn->p_lang?>" data-chapter="<?=@$wn->chapter_name?>" data-name="<?=@$wn->subject?>" data-area="<?=@$wn->area?>" data-recipient="<?=@$responsible_email?>" data-responsibleid="<?=@$wn->id_responsible?>" data-destinationdate="<?=@$wn->destination_date?>" data-progresstatusid="<?=@$wn->id_progress_status?>" data-ispriority="<?=@$wn->is_priority?>" data-trackresponsibleid="<?=@$id_track_responsible?>" data-tracktype="<?=@$track_type?>" data-reminderdate="<?=@$reminder_date?>" data-remindertime="<?=@$reminder_time?>" data-istodotoday="0">
												<div class="width15Percents d-flex flex-row align-items-center justify-content-center" style="align-self:center;">
													<input type='checkbox' class='whats-new-checkbox marginRight5' title='כבר לא חדש' data-lmuid="<?=@$wn->lmu_id?>" data-updatedusers="<?=@$wn->lmu_updated_users?>" onclick="event.stopPropagation();" />
													<span class="marginRight5 colorWhite bgColor-1a5276 border-black borderRadius10 padding-4x-4y fw-bold fontSize9">
													  <?=@$wn->p_nickname?>
													</span>
												</div>
												<div class="width85Percents">
													<div class="flex flex-wrap justify-content-center align-items-center" style="line-height:1.3;flex-wrap:nowrap;">
														<div class="width65Percents align-items-center">
															<span class="marginRight5 color-19bf42 font-weight-bold"><?=@$chapter_name?></span>
															<!--<span class="color-1A5276 font-weight-bold">|</span>
															<span><?=@$task_name?></span>!-->
														</div>
														<div class="width35Percents alignLeft">
															<?php if(@$responsible_name != ''){ ?>
																	<span class="color-1A5276 font-weight-bold fontSize12">
																		<?=@$responsible_name?>
																	</span>
															<?php } ?>
														</div>
													</div>
													<div class="marginTop5 flex flex-wrap justify-content-center">
														<div class="width100Percents">
															<span class="color-1A5276 font-weight-bold"><?=@$subject?></span>
															<span class="color-1A5276 font-weight-bold">|</span>
															<span class="color-1A5276"><?=@$area?></span>
														</div>
													</div>
													<?php if(@$wn->id_log_meeting_updates != 0){
													        $remark = !isEffectivelyEmpty(@$wn->lmu_remark) ? @$wn->lmu_remark : @$wn->latest_lmu_remark;
															$remark = trim(strip_tags(html_entity_decode($remark)));
															if(mb_strlen(@$remark,'UTF-8') > 50)
																$remark = mb_substr($remark,0,50,'UTF-8').'...';
													?>
															<div class="marginTop5 fontSize11 text-end" style="position:relative;padding-right:26px;box-sizing:border-box;">
																	<span class="badge-nickname-green" style="display:inline-flex;line-height:1;position:absolute;right:0;top:0;background-color:#f2f6f9;color:#2d7a52 !important;font-weight:bold;">
																		<?=@$wn->user_nickname?>
																	</span>
																	<span class="log-date-grey dir-rtl unicode-bidi-embed" style="font-weight:normal;">
																		<?php
																		echo smartDate(@$wn->lmu_action_date)?>
																	</span>
																	<?php if(@$wn->lmu_action == 'חדשה'){ ?>
																		&nbsp;-
																		<span class="colorGreenDark dir-rtl unicode-bidi-embed" style="font-weight:bold;">
																			המשימה נוצרה בתאריך <?=smartDate(@$wn->lmu_action_date)?>
																		</span>
																	<?php } else { ?>
																		<?php if(trim(@$wn->current_status_name_he) != ''){ ?>
																			&nbsp;-
																			<span class="color-status-green-darker dir-rtl unicode-bidi-embed" style="font-weight:bold;">
																				<?=simplifyStatusLabel(@$wn->current_status_name_he)?>
																			</span>
																		<?php }
																		    if(@$remark != '') echo ' - <span class="colorGreenDark dir-rtl unicode-bidi-embed" style="font-weight:normal;">'.$remark.'</span>';
																		    ?>
																	<?php } ?>
															</div>
													<?php }
													      if($log_meeting_tracking_num_rows_wn > 0){ ?>
													<div style="display:block;width:100%;">
														<?php foreach($log_meeting_tracking_wn as $tr_item){
															$tr_remark = @$tr_item->remark;
															if(mb_strlen(@$tr_remark,'UTF-8') > 30) $tr_remark = mb_substr($tr_remark,0,30,'UTF-8').'...';
														?>
														<div class="marginTop5 fontSize11 text-end" style="line-height:1.4;position:relative;padding-right:26px;box-sizing:border-box;">
															<span class="border-black padding-2x-2y fontSize9 borderRadius20 align-items-center justify-content-center colorWhite bgColorBlack" style="display:inline-flex;line-height:1;position:absolute;right:0;top:0;background-color:#f2f6f9;color:#c0392b;font-weight:bold;"><?=@$tr_item->user_nickname?></span>
															<span class="marginRight2 log-date-grey dir-rtl unicode-bidi-embed"><?=smartDate(@$tr_item->action_date)?> -</span>
															<span class="colorRed dir-rtl unicode-bidi-embed" style="word-wrap:break-word;overflow-wrap:break-word;"><?=html_entity_decode(@$tr_remark)?></span>
														</div>
														<?php break; } ?>
													</div>
													<?php } ?>
												</div>
											</div>
										</a>																				
									</td>
								</tr>
						<?php } ?>		   
						</table>	
					</div>										
				</div>				
				
                <div class="row" style="max-width:1300px;margin:0 auto;">	
					<div id="left_content" class="col-12 col-md-6">
						<div id="left_initial_content" class="marginTop20 overflow-y-scroll flex flex-wrap justify-content-center" style="margin:20px auto;max-height:650px;">          
							<?php 
							foreach($projects as $pr){
								$query = $mysqli->prepare("SELECT m.id AS id,m.id_user AS id_user,
														   c.name AS chapter_name,
														   t.name_he AS task_name,
														   p.nickname AS p_nickname,p.lang AS p_lang,
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
								$query->bind_param('sssii',$ps1,$ps2,$ps3,$pr->id,$_SESSION['id_user']);	
								$query->execute(); 
								$query->store_result();
								$user_tasks_num_rows = $query->num_rows;
								$user_tasks = fetch($query);
								
								$query = $mysqli->prepare("SELECT m.id AS id,m.id_user AS id_user,
														  p.id AS p_id,p.nickname AS p_nickname,
														  p.lang AS p_lang,
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
								$query->bind_param('sssiii',$ps1,$ps2,$ps3,$pr->id,$_SESSION['id_user'],$is_active_tracking);	
								$query->execute(); 
								$query->store_result();
								$active_tracking_num_rows = $query->num_rows;
								$active_tracking = fetch($query);
								
								$query = $mysqli->prepare("SELECT s.name_he AS s_name_he,
														   a.submitted_account AS submitted_account,
														   ps.id AS ps_id
														   FROM dne_accounts a
														   LEFT JOIN dne_projects_suppliers ps ON a.id_projects_suppliers = ps.id
														   LEFT JOIN dne_projects p ON ps.id_project = p.id
														   LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
														   WHERE a.submitted_account > ? 
														   AND a.approved_amount = ? 
														   AND p.id = ?");
								$query->bind_param("ddi",$zero,$zero,$pr->id);
								$query->execute(); 
								$query->store_result();
								$not_approved_accounts_num_rows = $query->num_rows; 
								$not_approved_accounts = fetch($query);
								
								$query = $mysqli->prepare("SELECT ln.id_log_meeting_updates AS id_log_meeting_updates,
								                           ln.id_log_meeting_tracking AS id_log_meeting_tracking,
								                           lmu.id AS lmu_id,lmu.action_date AS lmu_action_date,
														   lmu.updated_users AS lmu_updated_users,
														   lmu.action AS lmu_action,
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
														   ps.name_he AS current_status_name_he,
														   (SELECT lmu2.remark FROM dne_log_meeting_updates lmu2 WHERE lmu2.id_meeting = m.id AND TRIM(lmu2.remark) <> '' AND lmu2.is_remark_appears_log = 1 ORDER BY lmu2.id DESC LIMIT 1) AS latest_lmu_remark,
														   (SELECT ps2.name_he FROM dne_log_meeting_updates lmu3 LEFT JOIN dne_progress_status ps2 ON ps2.id = lmu3.id_progress_status WHERE lmu3.id_meeting = m.id AND lmu3.id_progress_status <> 0 AND lmu3.id_user <> ? ORDER BY lmu3.id DESC LIMIT 1) AS lmu_progress_status_name,
														   (SELECT lmu4.destination_date FROM dne_log_meeting_updates lmu4 WHERE lmu4.id_meeting = m.id AND lmu4.destination_date <> '0000-00-00' AND lmu4.id_user <> ? ORDER BY lmu4.id DESC LIMIT 1) AS lmu_destination_date
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
														   ORDER BY GREATEST(COALESCE(lmu.action_date,'1970-01-01'), COALESCE(lmt.action_date,'1970-01-01')) DESC,
													   GREATEST(COALESCE(lmu.id,0), COALESCE(lmt.id,0)) DESC");
								$query->bind_param('iisssiiii',$_SESSION['id_user'],$_SESSION['id_user'],$ps1,$ps2,$ps3,$pr->id,$_SESSION['id_user'],$is_remark_appears_log,$_SESSION['id_user']);
								$query->execute();
								$query->store_result();
								$what_news_num_rows = $query->num_rows;
								$what_news = fetch($query);
							?>
				
								<div class="responsive-btn-container alignCenter">
									<div class="btn-container position-relative">
										<input type="button" value="<?=@$pr->nickname?>" 
										class="btn padding-btn marginTop10 borderRadius20 bgColorBlue2 width100 main-btn" 
										onclick="toTasksList(<?=@$pr->id?>);" />
									
										<div class="badge-group dir-rtl">
											<a class="text-decoration-none cursor-pointer badge-switcher" title="משימות שלי" data-target="user_tasks" data-prid="<?=$pr->id?>" data-bgcolor="<?=@$bg_color_inputs->a_bgcolor?>">
												<span class="badge-circle colorWhite font-weight-bold" style="background-color:<?=@$bg_color_inputs->e_bgcolor?>"><?=@$user_tasks_num_rows?></span>
											</a>	
											<a class="text-decoration-none cursor-pointer badge-switcher" title="מעקב אקטיבי" data-target="active_tracking" data-prid="<?=$pr->id?>" data-bgcolor="<?=@$bg_color_inputs->b_bgcolor?>">
												<span class="badge-circle colorWhite font-weight-bold" style="background-color:<?=@$bg_color_inputs->f_bgcolor?>"><?=@$active_tracking_num_rows?></span>
											</a>
											<a class="text-decoration-none cursor-pointer badge-switcher" title="תקציב" data-target="not_approved_accounts" data-prid="<?=$pr->id?>" data-bgcolor="<?=@$bg_color_inputs->c_bgcolor?>">
												<span class="badge-circle colorWhite font-weight-bold" style="background-color:<?=@$bg_color_inputs->g_bgcolor?>"><?=@$not_approved_accounts_num_rows?></span>
											</a>                										
											<a class="text-decoration-none cursor-pointer badge-switcher" title="מה חדש" data-target="what_news" data-prid="<?=$pr->id?>" data-bgcolor="<?=@$bg_color_inputs->d_bgcolor?>">
												<span class="badge-circle colorWhite font-weight-bold" style="background-color:<?=@$bg_color_inputs->h_bgcolor?>"><?=@$what_news_num_rows?></span>
											</a>
										</div>
									</div>							
									
									<div id="div_user_tasks_<?=@$pr->id?>" class="flex margin-0-x-auto width50Percents border-black display-none dir-rtl">
										<div class="row fontSize18 alignCenter">
											<div class="col-12 d-flex justify-content-between align-items-center position-relative">
												<label class="padding-4x-4y borderRadius20 bgColor-cbddec mx-auto position-absolute start-50 translate-middle-x">
													<?=@$pr->nickname?>
												</label>
												<input type="button" id="btn-close-user-tasks-<?=@$pr->id?>" class="borderRadius10 fontSize16 font-weight-bold ms-auto" style="color:<?=@$bg_color_inputs->e_bgcolor?>; border:1px solid <?=@$bg_color_inputs->e_bgcolor?>" value="X" />
											</div>
										</div>		
										
										<div class="row fontSize16">
											<div class="col-12 alignCenter">
												<strong class="paddingLeft15" style="color:<?=@$bg_color_inputs->e_bgcolor?>">משימות שלי</strong>	
											</div>
										</div>		
											
										<div class="marginTop10 width100Percents overflow-y-scroll scrollbar-colored alignCenter dir-rtl" style="max-height:530px;">
											<table align="center" class="dir-rtl" cellpadding="4" width="100%">
												<?php foreach ($user_tasks as $ut){ 
													$user_id = @$ut->id_user;
													
													$reminder_time = @$ut->reminder_time;
													$reminder_date = @$ut->reminder_date;												
													
													$id_track_responsible = @$ut->id_track_responsible;
													$track_type = @$ut->track_type;			
											
													$chapter_name = stripNbspArtifact(@$ut->chapter_name);
													if(strlen(@$chapter_name) > 50)
													   $chapter_name = mb_substr($chapter_name,0,50,'UTF-8');

												    $responsible_email = @$ut->responsible_email;

													$task_name = @$ut->task_name;
													if(strlen(@$task_name) > 50)
													   $task_name = mb_substr($task_name,0,50,'UTF-8');

													$subject = stripNbspArtifact(@$ut->subject);
													if(mb_strlen(@$subject,'UTF-8') > 30)
													   $subject = mb_substr(trim(preg_replace('/[\x{00A0}\x{200B}]+/u',' ',strip_tags($subject))),0,30,'UTF-8').'...';

													$area = stripNbspArtifact(@$ut->area);
													if(mb_strlen(@$area,'UTF-8') > 30)
													   $area = mb_substr(trim(preg_replace('/[\x{00A0}\x{200B}]+/u',' ',strip_tags($area))),0,30,'UTF-8').'...';

													$align_txt = 'alignRight';
													$padding_txt = 'paddingRight5';														
													
													$one = 1;
													$empty_remark ='';
													$query = $mysqli->prepare("SELECT lmu.action,lmu.action_date,lmu.remark,
																			   u.nickname AS user_nickname												
																			   FROM dne_log_meeting_updates lmu
																			   LEFT JOIN dne_users u ON lmu.id_user = u.id
																			   WHERE lmu.id_meeting = ?
																			   AND lmu.remark <> ?
																			   AND lmu.is_remark_appears_log = ?
																			   ORDER BY lmu.id DESC");
													$query->bind_param('isi',$ut->id,$empty_remark,$one);	
													$query->execute(); 
													$query->store_result();
													$log_meeting_updates_num_rows = $query->num_rows;
													$log_meeting_updates = fetch($query);
													?>
													<tr class="task-row fontSize13">	
														<td>
														    <input type="hidden" id="p_nickname_<?=@$ut->id?>" value="<?=@$pr->nickname?>">
															<a id="task_name_<?=@$ut->id?>" class="text-decoration-none w-100 d-block">
																<div class="marginTop5 marginBottom5 flex flex-wrap justify-content-center align-items-center task_name width100Percents cursor-pointer" data-projectnickname="<?=@$ut->p_nickname?>" data-meetingid="<?=@$ut->id?>" data-projectid="<?=@$pr->id?>" data-userid="<?=@$user_id?>" data-lang="<?=@$ut->p_lang?>" data-chapter="<?=@$ut->chapter_name?>" data-name="<?=@$ut->subject?>" data-area="<?=@$ut->area?>" data-recipient="<?=@$responsible_email?>" data-responsibleid="<?=@$ut->id_responsible?>" data-destinationdate="<?=@$ut->destination_date?>" data-progresstatusid="<?=@$ut->id_progress_status?>" data-ispriority="<?=@$ut->is_priority?>" data-trackresponsibleid="<?=@$id_track_responsible?>" data-tracktype="<?=@$track_type?>" data-reminderdate="<?=@$reminder_date?>" data-remindertime="<?=@$reminder_time?>" data-istodotoday="0">
																	<div class="width10Percents">
																		<a class="drag-task cursor-pointer alignCenter text-decoration-none" data-id="<?=@$ut->id?>" data-listfrom="user_tasks">
																			<i class="paddingTop10 fa-solid fa-thumbtack"></i>
																		</a>		
																	</div>
																	<div class="width90Percents">
																		<div class="flex flex-wrap justify-content-center task-title-row" style="line-height:0.1">
																			<div class="width65Percents align-items-center">
																				<span class="color-19bf42 font-weight-bold"><?=@$chapter_name?></span>
																				<!--<span class="color-1A5276 font-weight-bold">|</span>
																				<span><?=@$task_name?></span>!-->
																			</div>
																			<div class="marginTop5 width35Percents alignLeft">
																				<?php if(@$task_name != ''){ ?>
																						<span class="color-1A5276 font-weight-bold fontSize12">
																							<?=@$task_name?>
																						</span>
																				<?php } ?>
																			</div>
																		</div>
																		<div class="marginTop5 flex flex-wrap justify-content-center">
																			<div class="width100Percents">
																				<span class="color-1A5276 font-weight-bold"><?=@$subject?></span>
																				<span class="color-1A5276 font-weight-bold">|</span>
																				<span class="color-1A5276"><?=@$area?></span>
																			</div>
																		</div>
																		<?php if($track_type == 1){
																			$one_pr = 1; $empty_pr = '';
																			$q_pr = $mysqli->prepare("SELECT lmt.action_date,lmt.remark,u.nickname AS user_nickname FROM dne_log_meeting_tracking lmt LEFT JOIN dne_users u ON lmt.id_user = u.id WHERE lmt.id_meeting = ? AND lmt.remark <> ? AND lmt.is_remark_appears_log = ? ORDER BY lmt.id DESC");
																			$q_pr->bind_param('isi',$ut->id,$empty_pr,$one_pr);
																			$tr_pr = fetch($q_pr);
																			if($q_pr->num_rows > 0){ ?>
																		<div class="d-flex flex-column">
																			<?php foreach($tr_pr as $tr_item){
																				$tr_remark = @$tr_item->remark;
																				if(mb_strlen(@$tr_remark,'UTF-8') > 30) $tr_remark = mb_substr($tr_remark,0,30,'UTF-8').'...';
																			?>
																			<div class="marginTop5 fontSize11 text-end" style="line-height:1">
																				<span class="border-black padding-2x-2y fontSize9 borderRadius20 align-items-center justify-content-center colorWhite bgColorBlack" style="display:inline-flex;line-height:1;background-color:#f2f6f9;color:#c0392b;font-weight:bold;"><?=@$tr_item->user_nickname?></span>
																				<span class="marginRight2 log-date-grey dir-rtl unicode-bidi-embed"><?=smartDate(@$tr_item->action_date)?> -</span>
																				<span class="colorRed dir-rtl unicode-bidi-embed display-inline-block"><?=html_entity_decode(@$tr_remark)?></span>
																			</div>
																			<?php } ?>
																		</div>
																		<?php } } ?>
																	</div>
																</div>
															</a>
														</td>
													</tr>
											<?php } ?>
											</table>
										</div>
									</div>
									
									<div id="div_active_tracking_<?=@$pr->id?>" class="flex margin-0-x-auto width50Percents border-black display-none dir-rtl">
										<div class="row fontSize18 alignCenter">
											<div class="col-12 d-flex justify-content-between align-items-center position-relative">
												<label class="padding-4x-4y borderRadius20 bgColor-cbddec mx-auto position-absolute start-50 translate-middle-x">
													<?=@$pr->nickname?>
												</label>
												<input type="button" id="btn-close-active-tracking-<?=@$pr->id?>" class="borderRadius10 fontSize16 font-weight-bold ms-auto" style="color:<?=@$bg_color_inputs->f_bgcolor?>; border:1px solid <?=@$bg_color_inputs->f_bgcolor?>" value="X" />
											</div>
										</div>		
											
										<div class="row marginTop5 fontSize16">
											<div class="col-12 alignCenter">
												<strong style="color:<?=@$bg_color_inputs->f_bgcolor?>">מעקב אקטיבי</strong>
											</div>
										</div>
										
										<div class="marginTop10 width100Percents overflow-y-scroll scrollbar-colored alignCenter dir-rtl" style="max-height:530px;">
											<table align="center" class="dir-rtl" cellpadding="4" width="100%">
												<?php foreach ($active_tracking as $at){ 
													$user_id = @$at->id_user;
													
													$reminder_time = @$at->reminder_time;
													$reminder_date = @$at->reminder_date;																						
													
													$id_track_responsible = @$at->id_track_responsible;
													$query = $mysqli->prepare("SELECT nickname FROM dne_users WHERE id = ?");
													$query->bind_param('i',$id_track_responsible);	
													$query->execute(); 
													$query->store_result();
													$track_responsible = fetch_unique($query);
													$track_responsible_name = @$track_responsible->nickname;
													
													$track_type = @$at->track_type;			
											
													$chapter_name = stripNbspArtifact(@$at->chapter_name);
													if(strlen(@$chapter_name) > 50)
													   $chapter_name = mb_substr($chapter_name,0,50,'UTF-8');

													$responsible_name = @$at->responsible_name;
													if(mb_strlen(@$responsible_name,'UTF-8') > 20)
														$responsible_name = mb_substr($responsible_name,0,20,'UTF-8').'...';
													$responsible_email = @$at->responsible_email;

													$task_name = @$at->task_name;
													if(strlen(@$task_name) > 50)
													   $task_name = mb_substr($task_name,0,50,'UTF-8');

													$subject = stripNbspArtifact(@$at->subject);
													if(mb_strlen(@$subject,'UTF-8') > 30)
													   $subject = mb_substr(trim(preg_replace('/[\x{00A0}\x{200B}]+/u',' ',strip_tags($subject))),0,30,'UTF-8').'...';

													$area = stripNbspArtifact(@$at->area);
													if(mb_strlen(@$area,'UTF-8') > 30)
													   $area = mb_substr(trim(preg_replace('/[\x{00A0}\x{200B}]+/u',' ',strip_tags($area))),0,30,'UTF-8').'...';
										
													$align_txt = 'alignRight';
													$padding_txt = 'paddingRight5';	    										

													$one = 1;
													$empty_remark ='';
													$query = $mysqli->prepare("SELECT lmt.action_date,lmt.remark,
																			   u.nickname AS user_nickname
																			   FROM dne_log_meeting_tracking lmt
																			   LEFT JOIN dne_users u ON lmt.id_user = u.id
																			   WHERE lmt.id_meeting = ?
																			   AND lmt.remark <> ?
																			   AND lmt.is_remark_appears_log = ?
																			   ORDER BY lmt.id DESC");
													$query->bind_param('isi',$at->id,$empty_remark,$one);	
													$query->execute(); 
													$query->store_result();
													$log_meeting_tracking_num_rows = $query->num_rows;
													$log_meeting_tracking = fetch($query);

                                                    if($reminder_date != '0000-00-00' || @$track_responsible_name != '')
														$tracking_data = '(';
											
													if($reminder_date != '0000-00-00')
														$tracking_data .= smartDate($reminder_date);
													
													if($reminder_date != '0000-00-00' && $track_responsible_name != '')
														$tracking_data .= ',';
													
													if(@$track_responsible_name != '')
														$tracking_data .= @$track_responsible_name;
													
													if($reminder_date != '0000-00-00' || @$track_responsible_name != '')
														$tracking_data .= ')';														
													?>
													<tr class="task-row fontSize13">	
														<td>
															<input type="hidden" id="p_nickname_<?=@$at->id?>" value="<?=@$pr->nickname?>">
															<a id="task_name_<?=@$at->id?>" class="text-decoration-none w-100 d-block">
																<div class="marginTop5 flex flex-wrap justify-content-center align-items-center task_name width100Percents cursor-pointer" data-projectnickname="<?=@$at->p_nickname?>" data-meetingid="<?=@$at->id?>" data-projectid="<?=@$at->p_id?>" data-userid="<?=@$user_id?>" data-chapter="<?=@$at->chapter_name?>" data-name="<?=@$at->subject?>" data-area="<?=@$at->area?>" data-recipient="<?=@$responsible_email?>" data-responsibleid="<?=@$at->id_responsible?>" data-destinationdate="<?=@$at->destination_date?>" data-progresstatusid="<?=@$at->id_progress_status?>" data-ispriority="<?=@$at->is_priority?>" data-trackresponsibleid="<?=@$id_track_responsible?>" data-tracktype="<?=@$track_type?>" data-reminderdate="<?=@$reminder_date?>" data-remindertime="<?=@$reminder_time?>" data-istodotoday="0">
																	<div class="width10Percents">		
																		<a class="drag-task cursor-pointer alignCenter text-decoration-none" data-id="<?=@$at->id?>" data-listfrom="active_tracking">
																			<i class="fa-solid fa-thumbtack"></i>
																		</a>																		
																	</div>
																	<div class="width90Percents">
																		<div class="flex flex-wrap justify-content-center align-items-center task-title-row" style="line-height:0.1">
																			<div class="width65Percents align-items-center">
																				<span class="color-19bf42 font-weight-bold"><?=@$chapter_name?></span>
																				<!--<span class="color-1A5276 font-weight-bold">|</span>
																				<span class=""><?=@$task_name?></span>!-->
																			</div>
																			<div class="width35Percents alignLeft">
																				<?php if(@$responsible_name != ''){ ?>
																						<span class="color-1A5276 font-weight-bold fontSize12">
																							<?=@$responsible_name?>
																						</span>
																				<?php } ?>
																			</div>
																		</div>
																		<div class="marginTop5 flex flex-wrap justify-content-center">
																			<div class="width100Percents">
																				<span class="color-1A5276 font-weight-bold"><?=@$subject?></span>
																				<span class="color-1A5276 font-weight-bold">|</span>
																				<span class="color-1A5276"><?=@$area?></span>
																			</div>
																		</div>
																		<?php if($log_meeting_tracking_num_rows > 0){ ?>
																				<div class="d-flex flex-column">
													<?php foreach($log_meeting_tracking as $item){ ?>
														<div class="marginTop5 fontSize11 text-end" style="line-height:1">
															<span class="border-black padding-2x-2y fontSize9 borderRadius20 align-items-center justify-content-center colorWhite bgColorBlack" style="display:inline-flex;line-height:1;background-color:#f2f6f9;color:#c0392b;font-weight:bold;">
																<?=@$item->user_nickname?>
															</span>
															<span class="marginRight2 log-date-grey dir-rtl unicode-bidi-embed"><?=smartDate(@$item->action_date)?> -</span>
															<span class="colorRed dir-rtl unicode-bidi-embed display-inline-block">
																<?=html_entity_decode(@$item->remark) ?>
															</span>
															<span class="marginRight5 colorRed dir-rtl unicode-bidi-embed display-inline-block">
																<?=@$tracking_data ?>
															</span>
														</div>
													<?php break; } ?>
																				</div>
																		<?php } ?>
																	</div>										
															`	</div>
															 </a>													
														</td>
													</tr>
											<?php } ?>
											</table>	
										</div>																	
									</div>
									
									<div id="div_not_approved_accounts_<?=@$pr->id?>" class="flex margin-0-x-auto width50Percents border-black display-none dir-rtl" style="max-height:600px;">
										<div class="row fontSize18 alignCenter">
											<div class="col-12 d-flex justify-content-between align-items-center position-relative">
												<label class="padding-4x-4y borderRadius20 bgColor-cbddec mx-auto position-absolute start-50 translate-middle-x">
													<?=@$pr->nickname?>
												</label>
												<input type="button" id="btn-close-not-approved-accounts-<?=@$pr->id?>" class="borderRadius10 fontSize16 font-weight-bold ms-auto" style="color:<?=@$bg_color_inputs->g_bgcolor?>; border:1px solid <?=@$bg_color_inputs->g_bgcolor?>" value="X" />
											</div>
											
											<div class="row marginTop5 fontSize16">
												<div class="col-12 marginLeft10 alignCenter">
													<strong style="color:<?=@$bg_color_inputs->g_bgcolor?>">תקציב</strong>
												</div>
											</div>
										</div>
										
										<?php foreach($not_approved_accounts as $nap) { ?>
											<div class="flex flex-wrap width80Percents margin-top-10-x-auto colorBlack font-weight-bold alignCenter">
											   <div class="width100Percents cursor-pointer">
													<a class="text-decoration-none" href="accounts_payments.php?ps_id=<?=@$nap->ps_id?>&from=projects&lang_screen=HE">
														<?=@$nap->s_name_he?> <?=number_format(@$nap->submitted_account,2,'.',',')?>&#8362;
													</a>    
											   </div>
											</div>
										<?php } ?>										
									</div>	
									
									<div id="div_what_news_<?=@$pr->id?>" class="flex margin-0-x-auto width50Percents border-black display-none overflow-y-scroll dir-rtl">
										<div class="row fontSize18 alignCenter dir-rtl" style="background-color:<?=@$bg_color_inputs->h_bgcolor?>;padding:8px 0;">
											<div class="col-12 position-relative" style="min-height:38px;">
												<strong style="color:#ffffff;font-weight:bold;">מה חדש<i class="fa fa-bell" style="color:#ffffff;margin:0 15px;"></i></strong>
												<div class="d-flex align-items-center position-absolute" style="top:0;right:13;gap:8px;">
													<input type="button" id="btn-close-what-news-<?=@$pr->id?>" class="borderRadius10 fontSize16 font-weight-bold" style="color:#ffffff;background-color:transparent;border:1px solid #ffffff" value="X" />
													<button type="button" class="btn btn-primary btn-sm fontSize12 whats-new-mark-seen-btn" data-panel="#left_new_content" onclick="markCheckedWhatsNewSeen('#left_new_content')" disabled>תודה על העדכון</button>
												</div>
											</div>
										</div>
										<div class="row marginTop10 fontSize18 alignCenter dir-rtl">
											<div class="col-12">
												<label class="padding-4x-4y borderRadius20 bgColor-cbddec mx-auto">
													<?=@$pr->nickname?>
												</label>
											</div>
										</div>
										
										<div class="marginTop10 width100Percents overflow-y-scroll scrollbar-colored alignCenter dir-rtl" style="max-height:530px;">
											<table align="center" class="dir-rtl" cellpadding="4" width="100%">
												<?php foreach ($what_news as $wn){
													$user_id = @$wn->id_user;																					
													
													$reminder_time = @$wn->reminder_time;
													$reminder_date = @$wn->reminder_date;               																									
								
								                    $id_track_responsible = @$wn->id_track_responsible;
													$track_type = @$wn->track_type;			
											
													$chapter_name = stripNbspArtifact(@$wn->chapter_name);
													if(strlen(@$chapter_name) > 50)
													   $chapter_name = mb_substr($chapter_name,0,50,'UTF-8');

												    $responsible_name = @$wn->responsible_name;
													if(mb_strlen(@$responsible_name,'UTF-8') > 20)
														$responsible_name = mb_substr($responsible_name,0,20,'UTF-8').'...';
													$responsible_email = @$wn->responsible_email;

													$task_name = @$wn->task_name;
													if(strlen(@$task_name) > 50)
													   $task_name = mb_substr($task_name,0,50,'UTF-8');

													$subject = stripNbspArtifact(@$wn->subject);
													if(mb_strlen(@$subject,'UTF-8') > 30)
													   $subject = mb_substr(trim(preg_replace('/[\x{00A0}\x{200B}]+/u',' ',strip_tags($subject))),0,30,'UTF-8').'...';

													$area = stripNbspArtifact(@$wn->area);
													if(mb_strlen(@$area,'UTF-8') > 30)
													   $area = mb_substr(trim(preg_replace('/[\x{00A0}\x{200B}]+/u',' ',strip_tags($area))),0,30,'UTF-8').'...';
														
												    if($reminder_date != '0000-00-00' || @$track_responsible_name != '')
														$tracking_data = '(';
											
													if($reminder_date != '0000-00-00')
														$tracking_data .= smartDate($reminder_date);
													
													if($reminder_date != '0000-00-00' && $track_responsible_name != '')
														$tracking_data .= ',';
													
													$query = $mysqli->prepare("SELECT ps.name_he AS ps_name_he,
								                                              lmu.destination_date AS lmu_destination_date
														                      FROM dne_log_meeting_updates lmu
														                      LEFT JOIN dne_progress_status ps ON lmu.id_progress_status = ps.id
														                      WHERE lmu.id = ?");
													$query->bind_param("i",$wn->lmu_id);
													$query->execute(); 
													$query->store_result();	
													$query = fetch_unique($query);					
													$progress_status_name = @$query->ps_name_he;
													$destination_date = @$query->lmu_destination_date;
													
													if($reminder_date != '0000-00-00' || @$track_responsible_name != '')
														$tracking_data = '(';
											
													if($reminder_date != '0000-00-00')
														$tracking_data .= smartDate($reminder_date);
													
													if($reminder_date != '0000-00-00' && $track_responsible_name != '')
														$tracking_data .= ',';
													
													if(@$track_responsible_name != '')
														$tracking_data .= @$track_responsible_name;
													
													if($reminder_date != '0000-00-00' || @$track_responsible_name != '')
														$tracking_data .= ')';
													$log_meeting_tracking_num_rows_wn = 0; $log_meeting_tracking_wn = new stdClass();
													if($track_type == 1){
														$one_wn = 1; $empty_wn = '';
														$q_wn = $mysqli->prepare("SELECT lmt.action_date,lmt.remark,u.nickname AS user_nickname FROM dne_log_meeting_tracking lmt LEFT JOIN dne_users u ON lmt.id_user = u.id WHERE lmt.id_meeting = ? AND lmt.remark <> ? AND lmt.is_remark_appears_log = ? ORDER BY lmt.id DESC");
														$q_wn->bind_param('isi',$wn->id,$empty_wn,$one_wn);
														$log_meeting_tracking_wn = fetch($q_wn);
														$log_meeting_tracking_num_rows_wn = $q_wn->num_rows;
													}
													?>
													<tr class="task-row fontSize13">
														<td>
															<input type="hidden" id="p_nickname_<?=@$wn->id?>" value="<?=@$pr->nickname?>">
															<a id="task_name_<?=@$wn->id?>" class="text-decoration-none w-100 d-block">
																<div class="marginTop5 marginBottom5 flex flex-wrap justify-content-center align-items-start task_name width100Percents cursor-pointer" data-projectnickname="<?=@$wn->p_nickname?>" data-meetingid="<?=@$wn->id?>" data-projectid="<?=@$wn->p_id?>" data-userid="<?=@$user_id?>" data-lang="<?=@$wn->p_lang?>" data-chapter="<?=@$wn->chapter_name?>" data-name="<?=@$wn->subject?>" data-area="<?=@$wn->area?>" data-recipient="<?=@$responsible_email?>" data-responsibleid="<?=@$wn->id_responsible?>" data-destinationdate="<?=@$wn->destination_date?>" data-progresstatusid="<?=@$wn->id_progress_status?>" data-ispriority="<?=@$wn->is_priority?>" data-trackresponsibleid="<?=@$id_track_responsible?>" data-tracktype="<?=@$track_type?>" data-reminderdate="<?=@$reminder_date?>" data-remindertime="<?=@$reminder_time?>" data-istodotoday="0">
																	<div class="width10Percents d-flex align-items-center justify-content-center">
																		<input type='checkbox' class='whats-new-checkbox' title='כבר לא חדש' data-lmuid="<?=@$wn->lmu_id?>" data-updatedusers="<?=@$wn->lmu_updated_users?>" onclick="event.stopPropagation();" />
																	</div>
																	<div class="width90Percents">
																		<div class="marginRight5 flex flex-wrap justify-content-center align-items-center" style="line-height:1.3;flex-wrap:nowrap;">
																			<div class="width65Percents align-items-center">
																				<span class="color-19bf42 font-weight-bold"><?=@$chapter_name?></span>
																				<!--<span class="color-1A5276 font-weight-bold">|</span>
																				<span><?=@$task_name?></span>!-->
																			</div>
																			<div class="width35Percents alignLeft">
																				<?php if(@$responsible_name != ''){ ?>
																						<span class="color-1A5276 font-weight-bold fontSize12">
																							<?=@$responsible_name?>
																						</span>
																				<?php } ?>
																			</div>
				</div>
																		<div class="marginRight5 marginTop5 flex flex-wrap justify-content-center">
																			<div class="width100Percents">
																				<span class="color-1A5276 font-weight-bold"><?=@$subject?></span>
																				<span class="color-1A5276 font-weight-bold">|</span>
																				<span class="color-1A5276"><?=@$area?></span>
																			</div>
																		</div>
																		
																		<?php if(@$wn->id_log_meeting_updates != 0){
																 	        $lmu_remark_s2 = !isEffectivelyEmpty(@$wn->lmu_remark) ? @$wn->lmu_remark : @$wn->latest_lmu_remark;
																	        $lmu_remark_s2 = trim(strip_tags(html_entity_decode($lmu_remark_s2)));
																 	        if(mb_strlen(@$lmu_remark_s2,'UTF-8') > 50) $lmu_remark_s2 = mb_substr($lmu_remark_s2,0,50,'UTF-8').'...';
								                                         ?>
																			<div class="marginTop5 fontSize11 text-end" style="position:relative;padding-right:26px;box-sizing:border-box;">
																					<span class="badge-nickname-green" style="position:absolute;right:0;top:0;background-color:#f2f6f9;color:#2d7a52 !important;font-weight:bold;">
																						<?=@$wn->user_nickname?>
																					</span>
																					<span class="marginRight2 dir-rtl log-date-grey unicode-bidi-embed" style="font-weight:normal;">
																						<?=smartDate(@$wn->lmu_action_date)?>
																					</span>
																					<?php if(@$wn->lmu_action == 'חדשה'){ ?>
																						<span class="colorGreenDark dir-rtl unicode-bidi-embed" style="font-weight:bold;">
																							המשימה נוצרה בתאריך <?=smartDate(@$wn->lmu_action_date)?>
																						</span>
																					<?php } else { ?>
																						<?php if(trim(@$wn->current_status_name_he) != ''){ ?>
																							<span class="color-status-green-darker dir-rtl unicode-bidi-embed" style="font-weight:bold;">
																								<?=' '.simplifyStatusLabel(@$wn->current_status_name_he)?>
																							</span>
																						<?php }
																						if(@$lmu_remark_s2 != '') echo ' - <span class="colorGreenDark dir-rtl unicode-bidi-embed" style="font-weight:normal;">'.$lmu_remark_s2.'</span>';
																						?>
																					<?php } ?>
																				</div>
																		<?php }
																			if($log_meeting_tracking_num_rows_wn > 0){ ?>
																				<div style="display:block;width:100%;">
																					<?php foreach($log_meeting_tracking_wn as $tr_item){
																						$tr_remark = @$tr_item->remark;
																						if(mb_strlen(@$tr_remark,'UTF-8') > 30) $tr_remark = mb_substr($tr_remark,0,30,'UTF-8').'...';
																					?>
																					<div class="marginTop5 fontSize11 text-end" style="line-height:1.4;position:relative;padding-right:26px;box-sizing:border-box;">
																						<span class="border-black padding-2x-2y fontSize9 borderRadius20 align-items-center justify-content-center colorWhite bgColorBlack" style="display:inline-flex;line-height:1;position:absolute;right:0;top:0;background-color:#f2f6f9;color:#c0392b;font-weight:bold;"><?=@$tr_item->user_nickname?></span>
																						<span class="marginRight2 log-date-grey dir-rtl unicode-bidi-embed"><?=smartDate(@$tr_item->action_date)?> -</span>
																						<span class="colorRed dir-rtl unicode-bidi-embed display-inline-block"><?=html_entity_decode(@$tr_remark)?></span>
																					</div>
																							<?php break; } ?>
																				</div>
																				<?php } ?>																				  		
																	</div>
																</div>
															</a>																				
     													</td>
													</tr>
											<?php } ?>
											</table>	
										</div>							
									</div>						
								</div>	
							<?php } ?>
						</div>
                        <div class="marginTop15 borderRadius10 shadow" id="left_new_content" style="max-width:500px;max-height:650px;margin:20px auto;"></div>						
					</div>	
					<div id="right_content" class="col-12 col-md-6 marginTop15 border-black padding10 borderRadius10 shadow alignCenter" style="background-color:<?=@$bg_color_inputs->i_bgcolor?>;max-width:600px;max-height:650px;margin:20px auto;">				
						<div class="flex flex-wrap width300 fontSize12">	
							<div class="alignLeft">
								<a class="text-decoration-none cursor-pointer" onclick="toggleSettings_to_do_today()">
									<img src="images/3-vertical-dots-icon.png" alt="3 vertical dots icon" width="20" height="20" />
							    </a>
							</div>
							<div id="div_time_tasks_appear" class="width30Percents alignCenter">
								<label class="font-weight-bold">זמן הופעת<br/>משימה</label>
						        <div class="marginTop5">
									<select id="time_tasks_appear">	    
										<option value="1">עד הערב</option>
										<option value="2">עד מחר בערב</option>
										<option value="3">שלושה ימים</option>
										<option value="7">שבוע</option>										
									</select>
								</div>
							</div>
							
							<div id="div_max_num_tasks" class="width30Percents alignCenter">
								<label class="font-weight-bold">מס' משימות<br/>מקסימלי</label>
						        <div class="marginTop5">
									<select id="max_num_tasks">	    
										<option value="5">5</option>
										<option value="10">10</option>
										<option value="15">15</option>
										<option value="20">20</option>
										<option value="25">25</option>
										<option value="30">30</option>
									</select>
								</div>
							</div>

                            <div id="div_clear" class="width20Percents alignCenter">
								<a class="text-decoration-none colorBlue font-weight-bold cursor-pointer fontSize14" onclick="clearToDoToday()">Clear</a>
							</div>	
						</div>
						
						<div class="row marginTop5">
							<div class="col-12">
								<strong class="fontSize18">To Do Today</strong>	
								<div class="marginTop10 overflow-y-scroll alignCenter dir-rtl" style="max-height:520px;">
									<table align="center" class="dir-rtl" cellpadding="4" width="100%">
										<?php foreach ($to_do_today as $tdt){ 
											$user_id = @$tdt->id_user;
								
											$reminder_time = @$tdt->reminder_time;
											$reminder_date = @$tdt->reminder_date;								
									
											$id_track_responsible = @$tdt->id_track_responsible;
											$query = $mysqli->prepare("SELECT nickname FROM dne_users WHERE id = ?");
											$query->bind_param('i',$id_track_responsible);	
											$query->execute(); 
											$query->store_result();
											$track_responsible = fetch_unique($query);
											$track_responsible_name = @$track_responsible->nickname;
											
											$track_type = @$tdt->track_type;			
									
											$chapter_name = stripNbspArtifact(@$tdt->chapter_name);
											if(strlen(@$chapter_name) > 50)
											   $chapter_name = mb_substr($chapter_name,0,50,'UTF-8');

							                $responsible_name = @$tdt->responsible_name;
											if(strlen(@$responsible_name) > 20)
												$responsible_name = mb_substr($responsible_name,0,20,'UTF-8').'...';
											$responsible_email = @$tdt->responsible_email;

											$task_name = @$tdt->task_name;
											if(strlen(@$task_name) > 50)
											   $task_name = mb_substr($task_name,0,50,'UTF-8');

											$subject = stripNbspArtifact(@$tdt->subject);
											if(strlen(@$subject) > 30)
											   $subject = mb_substr(trim(preg_replace('/[\x{00A0}\x{200B}]+/u',' ',strip_tags($subject))),0,30,'UTF-8').'...';

											$area = stripNbspArtifact(@$tdt->area);
											if(strlen(@$area) > 30)
											   $area = mb_substr(trim(preg_replace('/[\x{00A0}\x{200B}]+/u',' ',strip_tags($area))),0,30,'UTF-8').'...';
										   
											$progress_status_name = @$tdt->ps_name_he;

											$align_txt = 'alignRight';
											$padding_txt = 'paddingRight5';
											
											$list_from = @$tdt->list_from;

                                            $bgcolor = 'white';
                                            if(@$list_from == 'user_tasks')											
												$bgcolor = @$bg_color_inputs->a_bgcolor;
										    else if(@$list_from == 'reminders' || @$list_from == 'active_tracking')											
												$bgcolor = @$bg_color_inputs->b_bgcolor;
											
											if(@$list_from == 'user_tasks'){
												$one = 1;
												$empty_remark ='';
												$query = $mysqli->prepare("SELECT lmu.action,lmu.action_date,lmu.remark,
																		   u.nickname AS user_nickname
																		   FROM dne_log_meeting_updates lmu
																		   LEFT JOIN dne_users u ON lmu.id_user = u.id
																		   WHERE lmu.id_meeting = ?
																		   AND lmu.remark <> ?
																		   AND lmu.is_remark_appears_log = ?
																		   ORDER BY lmu.id DESC");
												$query->bind_param('isi',$tdt->id,$empty_remark,$one);	
												$query->execute(); 
												$query->store_result();
												$log_meeting_updates_num_rows = $query->num_rows;
												$log_meeting_updates = fetch($query);
											}
											else if(@$list_from == 'active_tracking' || @$list_from == 'reminders'){
												$one = 1;
												$empty_remark ='';
												$query = $mysqli->prepare("SELECT lmt.action_date,lmt.remark,
																		   u.nickname AS user_nickname
																		   FROM dne_log_meeting_tracking lmt
																		   LEFT JOIN dne_users u ON lmt.id_user = u.id
																		   WHERE lmt.id_meeting = ?
																		   AND lmt.remark <> ?
																		   AND lmt.is_remark_appears_log = ?
																		   ORDER BY lmt.id DESC");
												$query->bind_param('isi',$tdt->id,$empty_remark,$one);
												$query->execute();
												$query->store_result();
												$log_meeting_tracking_num_rows = $query->num_rows;
												$log_meeting_tracking = fetch($query);
											}
											$log_meeting_tracking_num_rows_ut = 0; $log_meeting_tracking_ut = new stdClass();
											if(@$list_from == 'user_tasks' && $track_type == 1){
												$one_tdt = 1; $empty_tdt = '';
												$q_tdt = $mysqli->prepare("SELECT lmt.action_date,lmt.remark,u.nickname AS user_nickname FROM dne_log_meeting_tracking lmt LEFT JOIN dne_users u ON lmt.id_user = u.id WHERE lmt.id_meeting = ? AND lmt.remark <> ? AND lmt.is_remark_appears_log = ? ORDER BY lmt.id DESC");
												$q_tdt->bind_param('isi',$tdt->id,$empty_tdt,$one_tdt);
												$log_meeting_tracking_ut = fetch($q_tdt);
												$log_meeting_tracking_num_rows_ut = $q_tdt->num_rows;
											}
											?>
											<tr class="task-row fontSize13" style="background-color:<?=@$bgcolor?>">	
												<td>
													<a class="text-decoration-none w-100 d-block">
														<div class="marginTop5 marginBottom5 flex flex-wrap justify-content-center align-items-center task_name width100Percents cursor-pointer" data-projectnickname="<?=@$tdt->p_nickname?>" data-meetingid="<?=@$tdt->id?>" data-projectid="<?=@$tdt->p_id?>" data-userid="<?=@$user_id?>" data-chapter="<?=@$tdt->chapter_name?>" data-name="<?=@$tdt->subject?>" data-area="<?=@$tdt->area?>" data-recipient="<?=@$responsible_email?>" data-responsibleid="<?=@$tdt->id_responsible?>" data-destinationdate="<?=@$tdt->destination_date?>" data-progresstatusid="<?=@$tdt->id_progress_status?>" data-ispriority="<?=@$tdt->is_priority?>" data-trackresponsibleid="<?=@$id_track_responsible?>" data-tracktype="<?=@$track_type?>" data-reminderdate="<?=@$reminder_date?>" data-remindertime="<?=@$reminder_time?>" data-istodotoday="1">
															<div class="width15Percents">
																<a class="delete-task cursor-pointer alignCenter text-decoration-none" data-id="<?=@$tdt->id?>">
																	<i class="paddingTop5 fa-solid fa-ban"></i>
																</a>
																
																<span class="marginTop5 colorWhite bgColor-1a5276 border-black borderRadius10 padding-4x-4y fw-bold fontSize9">
																	<?=@$tdt->p_nickname?>
																</span>
																
																<?php if(@$list_from == 'reminders'){ ?>
																	<div class="marginTop10">
																		<i class="fa-solid fa-bell colorRed"></i>
																		<strong><?=smartDate(@$tdt->reminder_date)?></strong>
																	</div>
																<?php } ?>
															</div>
															<div class="width85Percents">
																<?php if(@$list_from == 'reminders' || @$list_from == 'active_tracking'){ ?>
																	<div class="flex flex-wrap justify-content-center align-items-center task-title-row" style="line-height:0.5">
																		<div class="width65Percents align-items-center">
																			<span class="color-19bf42 font-weight-bold"><?=@$chapter_name?></span>
																			<!--<span class="color-1A5276 font-weight-bold">|</span>
																			<span class=""><?=@$task_name?></span>!-->
																		</div>
																		<div class="width35Percents color-1A5276 font-weight-bold alignLeft">
																			<?php if(@$responsible_name != ''){ ?>
																					<span class="colorGrey fontSize12">
																						<?=@$responsible_name?>
																					</span>
																			<?php } ?>
																		</div>
																	</div>
																	<div class="marginTop5 flex flex-wrap justify-content-center">
																		<div class="width100Percents">
																			<span class="color-1A5276 font-weight-bold"><?=@$subject?></span>
																			<span class="color-1A5276 font-weight-bold">|</span>
																			<span class="color-1A5276"><?=@$area?></span>
																		</div>
																	</div>
																	
																	<?php if($log_meeting_tracking_num_rows > 0){ ?>
																			<div class="d-flex flex-column">
																				<?php foreach($log_meeting_tracking as $item){ 
																					$remark = @$item->remark;
																					if(mb_strlen(@$remark,'UTF-8') > 50)
																						$remark = mb_substr($remark,0,50,'UTF-8').'...';
																				?>
																	<div class="marginTop5 fontSize11 text-end" style="position:relative;padding-right:26px;box-sizing:border-box;">
																			<span class="border-black padding-2x-2y fontSize9 borderRadius20 align-items-center justify-content-center colorWhite bgColorBlack" style="display:inline-flex;line-height:1;position:absolute;right:0;top:0;background-color:#f2f6f9;color:#c0392b;font-weight:bold;">
																				<?=@$item->user_nickname?>
																			</span>
																			<span class="marginRight2 log-date-grey dir-rtl unicode-bidi-embed">
																				<?php
																				$formatter = new IntlDateFormatter(
																					'he_IL',
																					IntlDateFormatter::NONE,
																					IntlDateFormatter::NONE,
																					null,
																					null,
																					'd MMM'
																				);
																				$action_date = new DateTime(@$item->action_date);
																				echo $formatter->format($action_date)?>
																			</span>
																			-
																			<span class="colorRed dir-rtl unicode-bidi-embed display-inline-block font-weight-bold">
																				<?=html_entity_decode(@$remark)?>
																			</span>
																		</div>
																				<?php break; } ?>
																			</div>
																	<?php } 
																}
																else if(@$list_from == 'user_tasks'){ ?>
																	<div class="flex flex-wrap justify-content-center task-title-row" style="line-height:0.5">
																		<div class="width65Percents align-items-center">
																			<span class="color-63d687 font-weight-bold"><?=@$chapter_name?></span>
																			<span class="color-1A5276 font-weight-bold">|</span>
																			<span><?=@$task_name?></span>
																		</div>
																		<div class="width35Percents alignLeft">
																			<?php if(@$responsible_name != ''){ ?>
																					<span class="colorGrey fontSize12">
																						<?=@$responsible_name?>
																					</span>
																			<?php } ?>
																		</div>
																	</div>
																	<div class="marginTop5 flex flex-wrap justify-content-center">
																		<div class="width100Percents">
																			<span class="color-1A5276 font-weight-bold"><?=@$subject?></span>
																			<span class="color-1A5276 font-weight-bold">|</span>
																			<span class="color-1A5276"><?=@$area?></span>
																		</div>
																	</div>
																	<?php if($log_meeting_tracking_num_rows_ut > 0){ ?>
																	<div class="d-flex flex-column">
																		<?php foreach($log_meeting_tracking_ut as $tr_item){
																			$tr_remark = @$tr_item->remark;
																			if(mb_strlen(@$tr_remark,'UTF-8') > 30) $tr_remark = mb_substr($tr_remark,0,30,'UTF-8').'...';
																		?>
																		<div class="marginTop5 fontSize11 text-end" style="line-height:1">
																			<span class="border-black padding-2x-2y fontSize9 borderRadius20 align-items-center justify-content-center colorWhite bgColorBlack" style="display:inline-flex;line-height:1;background-color:#f2f6f9;color:#c0392b;font-weight:bold;"><?=@$tr_item->user_nickname?></span>
																			<span class="marginRight2 log-date-grey dir-rtl unicode-bidi-embed"><?=smartDate(@$tr_item->action_date)?> -</span>
																			<span class="colorRed dir-rtl unicode-bidi-embed display-inline-block"><?=html_entity_decode(@$tr_remark)?></span>
																		</div>
																		<?php break; } ?>
																	</div>
																	<?php } ?>
																<?php } ?>
															</div>
														</div>
													</a>				
												</td>
											</tr>
										<?php } ?>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
		    </div>
		</form>

        <div class="modal fade dir-rtl" id="modalTaskFollowupActions" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="btn-close btn-close-white-small" data-bs-dismiss="modal" aria-label="Close"></button>
						<div class="modal-title"></div>
					</div>
					<div class="modal-body" style="padding:0;overflow:hidden;">
					   <div id="modalContent">
					       <div id="div_content_task_details"></div>
					       <form class="marginTop15 alignCenter">    
							    <div class="row">
							        <div class="flex justify-content-center">
									    <div class="width20Percents">
										    <a class="btn text-dark bg-white" onclick="toEditeMeeting($('#hidden_meeting_id').val(),$('#hidden_iteration').val(),'from_'+$('#hidden_project_id').val())">
												<img src="images/edit-button.svg" width="30" height="30" />
												<br/>
												<strong class="fontSize14">עריכה</strong>
									        </a>
										</div>
										<div class="width20Percents">
										     <a class="btn text-dark bg-white" onclick="duplicateRecord($('#hidden_meeting_id').val(),$('#hidden_iteration').val(),'from_'+$('#hidden_project_id').val())">
												<img src="images/clone-solid.svg" width="30" height="30" />
												<br/>
												<strong class="fontSize14">שכפול</strong>
									         </a>	 
										</div>
										<div class="width20Percents">
										    <a id="continuous_btn" class="btn text-dark bg-white">
												<img src="images/arrow-right-from-bracket-solid.svg" width="30" height="30" />
												<br/>
												<strong class="fontSize14">המשך</strong>
									        </a>
										</div>
										<div class="width20Percents">
										    <a id="history_btn" class="btn text-dark bg-white width130">
												<img src="images/rectangle-list-regular.svg" width="30" height="30" />
												<br/>
												<strong class="fontSize14">הסטורי-ה</strong>
									        </a>
										</div>
									</div>
								</div>
								
								<div class="row marginTop5">
							        <div class="flex justify-content-center">
										<div class="width20Percents">
										    <a id="update_btn" class="btn text-dark bg-white height65">
											   <img src="images/status-icon.png" alt="status icon" width="30" height="30" />
											   <br/>
											   <strong class="fontSize14">עדכון</strong>
									        </a>
										</div>		
										<div class="width20Percents">
										    <a id="tracking_btn" class="btn text-dark bg-white width130">
												<i id="target-icon-popup" class="fas fa-bullseye" style="font-size:26px;color:#888;"></i>
												<br/>
												<strong class="fontSize14">מעקב</strong>
									        </a>
										</div>
										<div class="width20Percents">
										    <a id="send_email_btn" class="btn text-dark bg-white width130">
												<img src="images/email-icon.png" width="30" height="30" />
												<br/>
												<strong class="fontSize14">דוא''ל</strong>
									        </a>
										</div>
										<div class="width20Percents">
										    <a id="send_whatsapp_btn" class="btn text-dark bg-white width130">
												<img src="images/whatsapp-icon.png" width="30" height="30" />
												<br/>
												<strong class="fontSize14">WhatsApp</strong>
									        </a>
										</div>	
									</div>    	
							    </div>
								<div class="row marginTop5 dir-rtl">
								    <div class="col-12 alignCenter">
									   <div class="col-12 alignCenter">
											<a id="link_next_task" title="הבא"><i class="fa-solid fa-forward marginLeft10 fontSize33 color-349feb cursor-pointer"></i></a>
											<a id="link_prev_task" title="הקודם"><i class="fa-solid fa-backward fontSize33 color-349feb cursor-pointer"></i></a>
								        </div>
								    </div>
								</div>
						   </form>
					   </div>
					</div>
				</div>
           </div>
		</div>
		
		<div class="modal fade" id="modalContinuousTask" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="btn-close btn-close-white-small" data-bs-dismiss="modal" aria-label="Close"></button>
						<div class="modal-title"></div>
					</div>
					<div class="modal-body">
					   <div id="modalContent">
					        <form class="alignCenter">
						       <div class="marginTop15 fontSize18 alignCenter" id="continuous_task_intro">בדרך ליצור עבורך משימת המשך לפני כן, תציין אם ברצונך לסמן סטטוס משימה כ:</div>
							   <div class="marginTop15">
									<input type="radio" id="done" name="progress_status" value="1" onclick="continuousTask($('#hidden_meeting_id').val(),'בוצע/נמסר','','from_'+$('#hidden_project_id').val())" />&nbsp;<span id="continuous_label_done">בוצע</span>
							        <input type="radio" id="archive" name="progress_status" class="marginRight8" value="2" onclick="continuousTask($('#hidden_meeting_id').val(),'ארכיון','','from_'+$('#hidden_project_id').val())" />&nbsp;<span id="continuous_label_archive">ארכיון</span>
							        <input type="radio" id="no_change" name="progress_status" class="marginRight8" value="3" onclick="continuousTask($('#hidden_meeting_id').val(),'no_change','','from_'+$('#hidden_project_id').val())" />&nbsp;<span id="continuous_label_no_change">ללא שינוי</span>
							   </div>
						   </form>
					  </div>
					</div>
				</div>
            </div>
		</div>
		
		<div class="modal fade dir-rtl" id="modalHistoryTasks" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="btn-close btn-close-white-small" data-bs-dismiss="modal" aria-label="Close"></button>
					    <div class="modal-title"></div>
					</div>
					<div class="modal-body">
					    <div id="modalHistoryTasksContent"></div>
					</div>
				</div>
            </div>
		</div>
		
		<div class="modal fade" id="modalUpdateTask" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="btn-close btn-close-white-small" data-bs-dismiss="modal" aria-label="Close"></button>
						<div class="modal-title"></div>
					</div>
					<div class="modal-body">
                        <form action="" method="post">
						    <div class='marginTop5 subtitle color-19bf42 fontSize18 font-weight-bold alignCenter'></div>
						    <div class="row marginTop10">
							    <div class="col-12 fontSize13 alignCenter">
									<span id="progress_status_for_update_label"></span>
								    <select id="progress_status_update"></select>
							    </div>								
							</div>
							<hr class="colorGrey mb-1 mt-1"/>
							<div id="task_active_remarks_progress_status_update"></div>
							<div class="marginTop10 paddingRight10 alignRight height-auto bgColorWhite fontSize13 cursor-pointer border-black overflow-y-scroll dir-rtl">
								<div name="remark_changes_status_update" id="remark_changes_status_update" contenteditable="true" class="editable green cursor-pointer" data-placeholder="ניתן להוסיף כאן הערה"></div>
							</div>
                            <div id="div_target_date_title" class="marginTop5 fontSize13 font-weight-bold alignCenter"></div>							
							<div class="marginTop5 fontSize13 font-weight-bold alignCenter">
								<input type="date" id="new_destination_date_update" class="alignCenter" />
							</div>
							<div id="div_update_btns" class="marginTop15 marginBottom10 alignCenter">		   
							   	<input type="button" id="save_update_task_btn" class="btn btn-primary text-white font-weight-bold marginLeft5" value="שמור"/>
							    <input type="button" id="cancel_update_task_btn" class="btn bg-dark text-white font-weight-bold" value="בטל" onclick="hidePopup('modalUpdateTask','',$('#hidden_meeting_id').val(),'fromProjects')" />
							</div>
						</form>
					</div>
				</div>
            </div>
		</div>

        <input type="hidden" id="default_bgcolor_tracking" value="<?=@$bg_color_inputs->default_bgcolor?>">
        <input type="hidden" id="filled_bgcolor_tracking" value="<?=@$bg_color_inputs->filled_bgcolor?>">
<div class="modal fade dir-rtl" id="modalTaskTracking" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
				<div class="modal-content">
				    <div class="modal-header">
					    <button type="button" class="btn-close btn-close-white-small" data-bs-dismiss="modal" aria-label="Close"></button>
						<div class="modal-title"></div>
					</div>
					<div class="modal-body">	
					    <div id="modalContent">
					        <div class="container">
								<form>
								    <div class="row" dir="rtl">
									    <div class="col-12">
										    <div class='marginTop5 subtitle color-19bf42 fontSize18 font-weight-bold alignCenter'></div>
									    </div>
								    </div>

								    <div class="row marginTop5" dir="rtl">
									    <div class="col-2 p-2 text-center d-flex flex-column align-items-center justify-content-center">
										    <i class="fa fa-user" style="font-size:20px;"></i>
										    <div class="fw-bold fontSize13 marginTop5 text-nowrap">אחראי מעקב</div>
									    </div>
									    <div class="col-10 p-2 text-end d-flex flex-column justify-content-center align-items-center">
										    <select id="users" class="paddingRight8 fontSize13 marginBottom10">
											    <option value="0">--בחר משתמש--</option>
											    <?php
											    foreach($active_users as $item){ ?>
												    <option value="<?=@$item->id?>">
													    <strong><?=@$item->firstname?> <?=@$item->lastname?></strong>
												    </option>
												    <?php } ?>
										    </select>
									    </div>
								    </div>

								    <div class="row" dir="rtl"><div class="col-12"><hr class="marginTop5" style="border:none;border-top:2px solid #999;opacity:1;margin-left:-35px;margin-right:-35px;" /></div></div>

								    <div class="row marginTop5" dir="rtl">
									    <div class="col-2 p-2 text-center d-flex flex-column align-items-center justify-content-center">
										    <img src="images/bell-solid.svg" width="20" height="20" />
										    <div class="fw-bold fontSize13 marginTop5 text-nowrap">תזכורת מעקב</div>
									    </div>
									    <div class="col-10 p-2 text-end d-flex flex-column justify-content-center align-items-center">
										    <div class="row align-items-start justify-content-start flex-nowrap gx-2" dir="rtl">
											    <div class="col-auto fontSize13 text-end" style="margin-left:20px;">
												    <div><input type="radio" id="not_reminders" name="set_reminder_date_radio" value="0" > <i id="reminder_bell_icon" class="fa-solid fa-bell-slash" style="color:#888;font-size:16px;"></i></div>
											    </div>
											    <div class="col-auto fontSize13 text-end">
												    <div><input type="radio" id="reminder_tomorrow" name="set_reminder_date_radio" value="1" > מחר</div>
												    <div><input type="radio" id="reminder_after_one_week" name="set_reminder_date_radio" value="3" > בעוד שבוע</div>
											    </div>
											    <div class="col-auto fontSize13 text-end">
												    <div><input type="radio" id="reminder_after_selected_date" name="set_reminder_date_radio" value="6" onclick="setReminderDate(this.value);"> בתאריך</div>
											    </div>
											    <div class="col-auto" id="date_col_wrapper" style="display:none;">
												    <div id="div_reminder_date">
													    <input type="date" class="text-center" id="reminder_date" style="font-size:11px;width:85px;" />
												    <input type="hidden" id="computed_reminder_date" />
												    </div>
											    </div>
										    </div>
									    </div>
								    </div>

								    <div class="row" dir="rtl"><div class="col-12"><hr class="marginTop5" style="border:none;border-top:2px solid #999;opacity:1;margin-left:-35px;margin-right:-35px;" /></div></div>

								    <div class="row marginTop5" dir="rtl">
									    <div class="col-2 p-2 text-center d-flex flex-column align-items-center justify-content-center">
										    <img src="images/edit-button.svg" width="20" height="20" />
										    <div class="fw-bold fontSize13 marginTop5">עדכון</div>
									    </div>
									    <div class="col-10 ps-1 pe-2 d-flex flex-column justify-content-center align-items-center">
										    <div class="d-flex justify-content-between marginBottom5 w-100">
											    <div>
												    <a class="text-decoration-none cursor-pointer" onclick="$('#new_remark').html('')">Clear</a>
											    </div>
											    <div>
												    <a id="new_remark_tracking_en" class="text-decoration-none cursor-pointer" onclick="$('#new_remark').css({'direction':'ltr','padding-left':'5px','padding-right':'0','text-align':'left'});$(this).css('font-weight','bold');$('#new_remark_tracking_he').css('font-weight','normal');">EN</a>&nbsp;|
												    <a id="new_remark_tracking_he" class="text-decoration-none cursor-pointer" onclick="$('#new_remark').css({'direction':'rtl','padding-right':'5px','padding-left':'0','text-align':'right'});$(this).css('font-weight','bold');$('#new_remark_tracking_en').css('font-weight','normal');">ע</a>
											    </div>
										    </div>
										    <div class="bgColorWhite cursor-pointer border-black overflow-y-scroll dir-rtl w-100" style="padding:5px 8px; min-height:20px;">
											    <div name="new_remark" id="new_remark" contenteditable="true" class="editable red cursor-pointer" data-placeholder="ניתן להוסיף כאן הערה"></div>
										    </div>
									    </div>
								    </div>

		   
									<div class="row marginTop15" dir="rtl">
										<div class="col-12 d-flex justify-content-center align-items-center gap-3">
											<button type="button" class="btn font-weight-bold px-3 text-nowrap alignCenter" style="color:#212529;"
													onclick="fillLogTaskTracking($('#hidden_meeting_id').val(),'','for_closing',1)">
												<i class="fas fa-bullseye" style="color:#e74c3c;"></i><br/>שמור מעקב
											</button>
											<button type="button" class="btn font-weight-bold px-3 text-nowrap alignCenter" style="color:#212529;"
													onclick="advanceToNextTaskAndCancelTracking()">
												<i class="fas fa-bullseye" style="color:#95a5a6;"></i><br/>בטל מעקב
											</button>
											<input type="button" id="close_tracking_btn" class="btn bg-dark text-white font-weight-bold px-3 fontSize14" value="סגור" style="padding-top:2px!important;padding-bottom:2px!important;"
												   onclick="hidePopup('modalTaskTracking','',$('#hidden_meeting_id').val(),'fromProjects')" />
										</div>
									</div>
							   </form>
                           </div>
					  </div>
					</div>
				</div>
           </div>
		</div>

        <div class="modal fade dir-rtl" id="modalSendEmail" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
				<div class="modal-content">
				    <div class="modal-header">
					   <button type="button" class="btn-close btn-close-white-small" data-bs-dismiss="modal" aria-label="Close"></button>
					   <div class="modal-title"></div>
					</div>
					<div class="modal-body">
					    <div id="modalContent">
					        <form class="marginTop15 alignCenter">
						        <div class="alignCenter fontSize20">לשלוח ל</div>
								<div>
								    <input id="email_recipient" class="marginTop10 paddingRight10 width230" type="email" placeholder="לשלוח ל" />
								</div>
								<div class="marginTop15">
								    <input type="button" id="set_responsible_email_btn" class="btn btn-primary text-white font-weight-bold" value="שמור את דוא''ל האחראי" onclick="setResponsibleEmail($('#hidden_responsible_id').val(),'')" />
								</div>
								<div class="marginTop10 alignCenter fontSize20">נושא דוא''ל</div>
                                <div>
								    <input id="email_subject" class="marginTop10 paddingRight10" type="text" placeholder="נושא דוא''ל" />
								</div>									
								<div class="marginTop10 alignCenter fontSize20">גופן דוא''ל</div>
								<div class="marginTop10">
							       <textarea id="email_body" class="paddingRight10 width80Percents" rows="5" placeholder="גופן דוא''ל"></textarea>
							    </div>
								<div class="marginTop15 fontSize20 alignCenter">תאריך תזכורת</div>
							    <div class="marginTop10 alignCenter">
							       <input type="date" class="alignRight paddingRight10" id="email_reminder_date" value="<?=date("Y-m-d",strtotime(date('Y-m-d').'+7 days'))?>" />
							    </div>
							    <div class="marginTop15">
							       <input type="button" class="btn btn-primary font-weight-bold marginLeft10" value="שלח" onclick="sendEmail($('#hidden_meeting_id').val(),'','for_closing','fromProjects')" />
							       <input type="button" class="btn bg-dark font-weight-bold" value="ביטול" onclick="hidePopup('modalSendEmail','',0,'fromProjects')" />
							    </div>
						    </form>
					    </div>
					</div>
				</div>
            </div>
		</div>		
	</body>
</html>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.min.js"></script>

<script>
$(document).ready(function(){ 
    let project_id;
	let project_nickname;
	let lang;
	let meeting_id;
	let iteration;
	let user_id;
	let chapter;
	let subject;
	let area;
	let destination_date;
	let recipient;
	let responsible_id;
	let progress_status_id;
	let is_priority;
	let remark;
    let track_responsible_id;
	let track_type;
	let reminder_date;
    let reminder_time;
	let is_to_do_today;

	$('#modalHistoryTasks').on('hidden.bs.modal', function(){
		localStorage.removeItem('is_modal_task_actions_opened');
		localStorage.setItem('meeting_id', $('#hidden_meeting_id').val());
		localStorage.setItem('highlight_after_reload', 'true');
		window.location.reload();
	});

	$('#modalTaskFollowupActions').on('hidden.bs.modal', function (){
		localStorage.removeItem('is_modal_task_actions_opened');
		const mid = localStorage.getItem('meeting_id');
		if(mid){
			const $rowL = $('#left_new_content .task_name[data-meetingid="'+mid+'"]').closest('tr');
			const $rowR = $('#right_content .task_name[data-meetingid="'+mid+'"]').closest('tr');
			const $found = $rowL.length ? $rowL : $rowR;
			if($found.length){
				$('tr.task-row-highlight').removeClass('task-row-highlight');
				$found.addClass('task-row-highlight');
				$found[0].scrollIntoView({behavior:'smooth', block:'nearest'});
			}
		}
	});

	if(localStorage.getItem("is_modal_tasks_hystory_opened") === "true"){
		localStorage.removeItem('is_modal_tasks_hystory_opened');
		meeting_id = localStorage.getItem("meeting_id");
		project_id = localStorage.getItem("project_id");
		
		let form_data = new FormData();
        form_data.append('id_meeting',meeting_id);
		form_data.append('id_project',project_id);
	    form_data.append('sort_by',localStorage.getItem("sort_by"));
		
	    $.ajax({
			type: 'POST',
			url: 'fill_tasks_followup_history.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,
			success: function(data){
				$('#modalHistoryTasksContent').html(data);	
			},
	   });

	   $('#modalContent input[type="hidden"]').remove();
	   $('#modalContent').append("<input type='hidden' id='hidden_meeting_id' value='"+meeting_id+"'><input type='hidden' id='hidden_project_id' value='"+project_id+"'>");
	   $('#modalTaskFollowupActions').modal('hide');
	   $('#modalHistoryTasks').modal('show');
	   $('#modalHistoryTasks .modal-title').html("הסטורי-ה");
	};

	if(localStorage.getItem('highlight_after_reload') === 'true'){
		localStorage.removeItem('highlight_after_reload');
		const mid = localStorage.getItem('meeting_id');
		if(mid){
			const $rowReload = $('.task_name[data-meetingid="'+mid+'"]').closest('tr');
			if($rowReload.length){
				$('tr.task-row-highlight').removeClass('task-row-highlight');
				$rowReload.addClass('task-row-highlight');
				setTimeout(function(){
					const $vL = $('#left_new_content .task_name[data-meetingid="'+mid+'"]').closest('tr');
					const $vR = $('#right_content .task_name[data-meetingid="'+mid+'"]').closest('tr');
					const $v = $vL.length ? $vL : $vR;
					if($v.length) $v[0].scrollIntoView({behavior:'smooth', block:'nearest'});
				}, 600);
			}
		}
	}

	$('[id="continuous_btn"]').on('click', function(){
		$('#modalTaskFollowupActions').modal('hide');
	    $('#modalContinuousTask').modal('show');
		let isHE = ($('#hidden_lang').val() || 'HE') !== 'EN';
		$('#modalContinuousTask').attr('dir', isHE ? 'rtl' : 'ltr').toggleClass('dir-rtl', isHE);
		$('#modalContinuousTask .modal-title').html(isHE ? 'המשך' : 'Continue');
		$('#continuous_task_intro').html(isHE ? 'בדרך ליצור עבורך משימת המשך לפני כן, תציין אם ברצונך לסמן סטטוס משימה כ:' : 'A continuation task will be created. Please select the status to apply to the current task:');
		$('#continuous_label_done').html(isHE ? 'בוצע' : 'Done/Delivered');
		$('#continuous_label_archive').html(isHE ? 'ארכיון' : 'Archive');
		$('#continuous_label_no_change').html(isHE ? 'ללא שינוי' : 'No change');
	});
	
	$('[id="history_btn"]').on('click', function(){
		meeting_id = $('#hidden_meeting_id').val();
		project_id = $('#hidden_project_id').val();

		let form_data = new FormData();
        form_data.append('id_meeting',meeting_id);
		form_data.append('id_project',project_id);

	    $.ajax({
			type: 'POST',
			url: 'fill_tasks_followup_history.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,
			success: function(data){
				$('#modalHistoryTasksContent').html(data);
			},
	    });

	   $('#modalTaskFollowupActions').modal('hide');
	   $('#modalHistoryTasks').modal('show');
	   $('#modalHistoryTasks .modal-title').html("הסטורי-ה");
	});
	
	$('[id="update_btn"]').on('click', function(){
		meeting_id = $('#hidden_meeting_id').val();
		project_id = $('#hidden_project_id').val();
		project_nickname = $('#hidden_project_nickname').val();
        chapter = $('#hidden_chapter').val();
		subject = $('#hidden_name').val();
		area = $('#hidden_area').val();
		destination_date = 	$('#hidden_destination_date').val();
		progress_status_id = $('#hidden_progress_status_id').val();
		lang = $('#hidden_lang').val();
		let isHE = (lang || 'HE') !== 'EN';

		$('#modalUpdateTask').attr('dir', isHE ? 'rtl' : 'ltr');
		$('#modalUpdateTask .modal-title').html("<img src='images/status-icon.png' alt='status icon' width='20' height='20'>&nbsp;&nbsp;"+(isHE?'עדכון':'Update')+"&nbsp;&nbsp;<img src='images/status-icon.png' alt='status icon' width='20' height='20'>");
		$('.subtitle').html(chapter+"<br/>"+subject+"&nbsp;|&nbsp;"+area).css('line-height','1.1em');
        $('#div_remark_changes_status_update,#div_update_btns').css('direstion', isHE ? 'rtl' : 'ltr');
	    $('#progress_status_for_update_label').html(isHE ? 'סטטוס חדש:' : 'New status:').css({'margin-bottom':'5px','margin-left':'5px'});
	    $('#div_target_date_title').html(isHE ? 'תאריך יעד' : 'Target date');
		$('#save_remark_btn').css('margin-left','10px');
		$('#save_remark_btn').val(isHE ? 'שמור' : 'Save');
		$('#cancel_remark_btn').val(isHE ? 'ללא הערות' : 'No comments');
		$('#save_update_task_btn').val(isHE ? 'שמור' : 'Save');
		$('#cancel_update_task_btn').val(isHE ? 'בטל' : 'Cancel');
		$('#remark_changes_status_update').attr('data-placeholder', isHE ? 'ניתן להוסיף כאן הערה' : 'You can add a note here');
	    
        let form_data = new FormData();
		form_data.append('is_updates',1);
        form_data.append('id_meeting',meeting_id);
		form_data.append('id_project',project_id);
		form_data.append('id_progress_status',progress_status_id);
		form_data.append('lang',lang);
  
	    $.ajax({
			type: 'POST',
			url: 'get_progress_status_options.php',
			method: 'POST',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,
			success: function (data){
				$('#progress_status_update').html(data);
				blankStatusIfEmpty($('#progress_status_update'));
			}
        });

		$.ajax({
			type: 'POST',
			url: 'fill_task_active_remarks.php',
			method: 'POST',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,
			success: function(data){
			    $('#task_active_remarks_progress_status_update').html(data);
			},
	    });

		$('#new_destination_date_update').val(destination_date);
	    $('#modalTaskFollowupActions').modal('hide');
	    $('#modalUpdateTask').modal('show');
	});
	
	let currentBadgeTarget = '';
	let currentProject = 0;

	$('._badge-switcher').on('click', function (){
		const bgcolor = $(this).data('bgcolor');
		const target = $(this).data('target');
		const content = $('#div_' + target).html();
		currentBadgeTarget = target;
		currentProject = 0;

	    badgeSwitcherFillContent(content,bgcolor);
		$('#left_initial_content').hide();
		
		localStorage.removeItem('prId');
		localStorage.setItem('target',target);
		localStorage.setItem('bgcolor',bgcolor);
	});
	
	$('.badge-switcher').on('click', function (){  
		const target = $(this).data('target');
        const prId = $(this).data('prid');
		const bgcolor = $(this).data('bgcolor');
        const content = $('#div_'+target+'_'+prId).html();
		currentBadgeTarget = target;
		currentProject = prId;
		
        badgeSwitcherFillContent(content,bgcolor);
		$('#left_initial_content').hide();
		
		localStorage.setItem('target',target);
		localStorage.setItem('prId',prId);
		localStorage.setItem('bgcolor',bgcolor);
    });
	
	function badgeSwitcherFillContent(content,bgcolor){
		$('#left_new_content')
			.html(content)
			.css({
				'background-color': bgcolor,
				'border': '1px solid black',
				'padding': '10px'
			})
			.show();

		updateWhatsNewSeenBtnState();

		let styleEl = document.getElementById('dynamic-scrollbar-style');
		if (!styleEl) {
			styleEl = document.createElement('style');
			styleEl.id = 'dynamic-scrollbar-style';
			document.head.appendChild(styleEl);
		}
		styleEl.innerHTML =
			'.scrollbar-colored::-webkit-scrollbar-track {\n' +
			'    background-color: ' + bgcolor + ';\n' +
			'}\n' +
			'.scrollbar-colored {\n' +
			'    scrollbar-color: rgba(0, 0, 0, 0.4) ' + bgcolor + ';\n' +
			'}';
	}
	
	$(document).on('click','.drag-task', function (e){
		const id_meeting = $(this).data('id');
		const list_from = $(this).data('listfrom');
		
		let form_data = new FormData();
		form_data.append('id_meeting',id_meeting);
		form_data.append('list_from',list_from);
		form_data.append('max_num_tasks',$('#max_num_tasks').val());
		
		$.ajax({
			type: 'POST',
			url: 'add_meeting_to_do_today.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,
			success: function(data) {
				data = data.trim();
				if(data == 'exists')
					alert("המשימה כבר נמצאת ברשימת To Do Today");
				else if(data == 'maxnumtasksreached')
					alert('הגעת למקסימום המשימות');
				else if(data == 'enabledtodotoday')
					alert("אינך אחראי למעקב הזה או/גם תאריך התזכורת לא עבר");
				else {
					localStorage.setItem('time_tasks_appear',$('#time_tasks_appear').val());
					localStorage.setItem('max_num_tasks',$('#max_num_tasks').val());
					window.location.reload();
				}
			}, 
		});		   
	});
	
	$(document).on('click','.delete-task', function(e){
		const id_meeting = $(this).data('id');
		
		let form_data = new FormData();
		form_data.append('id_meeting',id_meeting);
		
		$.ajax({
			type: 'POST',
			url: 'to_do_today_delete.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,
			success: function(data) {
				localStorage.setItem('last_execution', today);
				localStorage.setItem('time_tasks_appear',$('#time_tasks_appear').val());
				localStorage.setItem('max_num_tasks',$('#max_num_tasks').val());
				window.location.reload();
			},
		});
    });
	
	$('#div_time_tasks_appear').hide();
	$('#div_max_num_tasks').hide();
	$('#div_clear').hide();
	
	function refreshTodoToday(){
		let form_data = new FormData();
		form_data.append('toAddRememberTracking',1);
			
		$.ajax({
			type: 'POST',
			url: 'add_meeting_to_do_today.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,
			success: function(data){
				localStorage.setItem('last_execution', today);
				localStorage.setItem('time_tasks_appear',$('#time_tasks_appear').val());
				localStorage.setItem('max_num_tasks',$('#max_num_tasks').val());
				window.location.reload();
			},
		});
	}

    const today = new Date().toISOString().split('T')[0];
    const last_execution = localStorage.getItem("last_execution");

    if(last_execution !== today){
        refreshTodoToday();
    }
	
	$('#max_num_tasks').on('change', function(){		
		localStorage.setItem('max_num_tasks',$('#max_num_tasks').val());
    });
	
	$('#time_tasks_appear').on('change', function(){		
		let form_data = new FormData();
		form_data.append('time_tasks_appear',$('#time_tasks_appear').val());

		$.ajax({
			type: 'POST',
			url: 'to_do_today_delete.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,
			success: function (data) {
				localStorage.setItem('max_num_tasks',$('#max_num_tasks').val());
                localStorage.setItem('time_tasks_appear',$('#time_tasks_appear').val());				
				localStorage.setItem('time_tasks_appear_deleted','1');
				window.location.reload();
			}
		});
    });
	
	const time_tasks_appear = localStorage.getItem('time_tasks_appear');
	if(time_tasks_appear !== null) 
		$('#time_tasks_appear').val(time_tasks_appear);
	
	const total_today_items = $('.task_name[data-istodotoday="1"]').length;
	const max_num_tasks_options = [5,10,15,20,25,30];
	$('#max_num_tasks option').each(function(){ if(parseInt($(this).val()) > 30) $(this).remove(); });
	if(total_today_items > 30){
		const ceil = Math.ceil(total_today_items / 5) * 5;
		for(let v = 35; v <= ceil; v += 5){
			max_num_tasks_options.push(v);
			$('#max_num_tasks').append('<option value="'+v+'">'+v+'</option>');
		}
	}
	let max_num_tasks = localStorage.getItem('max_num_tasks');
	let max_limit = max_num_tasks !== null ? parseInt(max_num_tasks) : 0;
	const max_available = max_num_tasks_options[max_num_tasks_options.length - 1];
	if(max_limit > max_available){
		max_limit = max_available;
		localStorage.setItem('max_num_tasks', String(max_limit));
	}
	if(total_today_items > 0 && total_today_items > max_limit){
		max_limit = max_num_tasks_options.find(o => o >= total_today_items) || max_available;
		localStorage.setItem('max_num_tasks', String(max_limit));
	}
	if(max_limit > 0){
		$('#max_num_tasks').val(max_limit);
		$('.task_name[data-istodotoday="1"]').closest('tr').slice(max_limit).hide();
	} else if(max_num_tasks !== null){
		$('#max_num_tasks').val(max_num_tasks);
	}
	
	let target = '';
	let bgcolor = '';
	let prId = 0;
	let content = '';	
	
	if(localStorage.getItem('target') != null && localStorage.getItem('bgcolor') != null){
		if(localStorage.getItem('prId') != null){
			target = localStorage.getItem('target');
		    prId = localStorage.getItem('prId');
		    bgcolor = localStorage.getItem('bgcolor'); 
		    content = $('#div_'+target+'_'+prId).html();
			currentBadgeTarget = target;
		    currentProject = prId;			  
		}
		else if(localStorage.getItem('prId') == null){      
			target = localStorage.getItem('target');	  
		    bgcolor = localStorage.getItem('bgcolor'); 
		    content = $('#div_'+target).html();
			currentBadgeTarget = target;		
		}
		
		badgeSwitcherFillContent(content,bgcolor);  
		$('#left_initial_content').hide();	        
	}

	$(document).on('click','[id^="btn-close"],#logo_link',function(){
		$('#left_initial_content').show();
		$('#left_new_content').hide();
		localStorage.removeItem('target');
		localStorage.removeItem('prId');
		localStorage.removeItem('bgcolor');
    });
	
	$('[id="tracking_btn"]').on('click', function(){
	    meeting_id = $('#hidden_meeting_id').val();
		iteration = $('#hidden_iteration').val();
	    subject = $('#hidden_name').val();
	    area = $('#hidden_area').val();
	    chapter = $('#hidden_chapter').val();
		reminder_time = $('#hidden_reminder_time').val();
		reminder_date = $('#hidden_reminder_date').val();

		$('#date_col_wrapper').show();
		$('#div_reminder_date').show();
		if(reminder_time == 6 && reminder_date != '0000-00-00') {
	      $('#reminder_date').val(reminder_date);
	    }
	    else {
		  $('#reminder_date').val(formatDate(new Date()));
		}

	    let trackTitleColor = $('#hidden_track_type').val() == 1 ? '#e74c3c' : '#95a5a6';
	    $('#modalTaskTracking .modal-title').html("<i class='fas fa-bullseye' style='font-size:22px;color:"+trackTitleColor+";'></i>&nbsp;&nbsp;מעקב אקטיבי&nbsp;&nbsp;<i class='fas fa-bullseye' style='font-size:22px;color:"+trackTitleColor+";'></i>");
		$('.subtitle').html(chapter+"<br/>"+subject+"&nbsp;|&nbsp;"+area).css('line-height','1.1em');

	    if($('#hidden_reminder_time').val() == 0){
		  $('#not_reminders').prop('checked',true);
	    }
	    else if($('#hidden_reminder_time').val() == 1){
	      $('#reminder_tomorrow').prop('checked',true);
	    }
	    else if($('#hidden_reminder_time').val() == 3 || $('#hidden_reminder_time').val() == 2){
	      $('#reminder_after_one_week').prop('checked',true);
	    }
	    else if($('#hidden_reminder_time').val() == 6){
		  $('#date_col_wrapper').show();
		  $('#div_reminder_date').show();
	      $('#reminder_after_selected_date').prop('checked',true);
		}

		updateReminderBellIcon($('#hidden_reminder_time').val() == 0 ? '0' : '1');

        $('#users option').each(function(){			
		    if($('#hidden_track_responsible_id').val() == 0){
				if($(this).val() == $('#hidden_user_id').val()) 
					$(this).prop('selected', true);
		    }		    	
		   
		    if($('#hidden_track_responsible_id').val() && ($(this).val() == $('#hidden_track_responsible_id').val()))
				$(this).prop('selected', true);  
	    });
	   
	    $('#modalTaskTracking').find('[id="users"]').css('background-color',
	        $('#hidden_track_responsible_id').val() > 0 ? $('#filled_bgcolor_tracking').val() : $('#default_bgcolor_tracking').val());

	    $('#modalTaskFollowupActions').modal('hide');
	    $('#modalTaskTracking').modal('show');
    });
	
	$('[id="users"]').on('change', function(){
		$(this).css('background-color', $(this).val() == '0' ? $('#default_bgcolor_tracking').val() : $('#filled_bgcolor_tracking').val());
		setData($('#hidden_meeting_id').val(),0,'track_responsible_id',0,0,'popup');
	});
	
	$('[id="send_email_btn"]').on('click', function(){
       meeting_id = $('#hidden_meeting_id').val(); 
	   subject = $('#hidden_name').val();
	   area = $('#hidden_area').val();
	   $('.modal-title').html(subject+' - '+area);
	   $('#email_recipient').val($('#hidden_recipient').val());
	   $('#modalTaskFollowupActions').modal('hide');
	   $('#modalSendEmail').modal('show');
    });
	
	$('[id="send_whatsapp_btn"]').on('click', function(){
       meeting_id = $('#hidden_meeting_id').val(); 
	   project_id = $('#hidden_project_id').val(); 
	   subject = $('#hidden_name').val();
	   area = $('#hidden_area').val()
	   $('#modalTaskFollowupActions').modal('hide');
	   
	   let form_data = new FormData();
       form_data.append('id_meeting',meeting_id);
	   
	   $.ajax({
			type: 'POST',
			url: 'task_details.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,
			success: function(data){
            	let task_details = data.split('|~|');
				let content = fillContentTaskDetails(meeting_id,'',task_details,false,true);
				$('#contentToScreenshot').html(content);
				
				const element = document.getElementById('contentToScreenshot');
				const width = element.offsetWidth;
                const height = element.offsetHeight;

				html2canvas(element, {
                scale: 1 
                }).then(function(canvas) {
					let finalCanvas = document.createElement('canvas');
                    finalCanvas.width = width;
                    finalCanvas.height = height;
                    
					let ctx = finalCanvas.getContext('2d');
                    ctx.drawImage(canvas, 0, 0, width, height);
					
					const imageData = canvas.toDataURL('image/jpeg');

					$.ajax({
						type: 'POST',
						url: 'save_image.php',
						data: {imageData:imageData,meeting_id:meeting_id},
						success: function(response){
							const imageUrl = '<?= BASE_URL ?>'+response;
							shareImage(imageUrl,meeting_id,project_id,'',0);
						}, 
				   });
                });
			},
	   });
    });
	
	$('[id="link_prev_task"]').on('click', function(){
		if(taskDetailsLoading) return;
	  	let form_data = new FormData();
        form_data.append('from','projects');
		form_data.append('currentBadgeTarget',currentBadgeTarget);
		form_data.append('currentProject',currentProject);
		form_data.append('filter_by',$('#filter_by').val());
		form_data.append('list_user_tasks',$('#list_user_tasks').val());

		if($('#hidden_is_to_do_today').val() == 1)
		   form_data.append('is_to_do_today',1);

		$.ajax({
			type: 'POST',
			url: 'fill_meeting_ids.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,
			success: function(data){
				let meeting_ids = data;
				navigateTasks(meeting_ids,'prev');
			},
		});
	});
	
	$('[id="link_next_task"]').on('click', function(){
		if(taskDetailsLoading) return;
		let form_data = new FormData();
        form_data.append('from','projects');
		form_data.append('currentBadgeTarget',currentBadgeTarget);
		form_data.append('currentProject',currentProject);
		form_data.append('filter_by',$('#filter_by').val());
		form_data.append('list_user_tasks',$('#list_user_tasks').val());
		
		if($('#hidden_is_to_do_today').val() == 1)
		   form_data.append('is_to_do_today',1);
		
		$.ajax({
			type: 'POST',
			url: 'fill_meeting_ids.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,
			success: function(data) {
				let meeting_ids = data;
				navigateTasks(meeting_ids,'next');
			}, 
		});		   
	});

	let modalTaskFollowupActions = $('#modalTaskFollowupActions');
	let modalTaskTracking = $('#modalTaskTracking');
	let modalSendEmail = $('#modalSendEmail');
	let modalHistoryTasks = $('#modalHistoryTasks');
	let modalUpdateTask = $('#modalUpdateTask');

	modalTaskTracking.on('shown.bs.modal', function(){
		$('#new_remark').focus();
		var lang = $('#hidden_lang').val();
		if (lang === 'HE') { $('#new_remark_tracking_he').trigger('click'); }
		else { $('#new_remark_tracking_en').trigger('click'); }
	});

	modalUpdateTask.on('shown.bs.modal', function(){
		$('#remark_changes_status_update').focus();
	});

	modalSendEmail.on('shown.bs.modal', function(){
		$('#email_body').focus();
	});

	$(window).click(function(event){
	    if ($(event.target).is(modalTaskFollowupActions) ||
			$(event.target).is(modalTaskTracking) ||
			$(event.target).is(modalSendEmail) ||
			$(event.target).is(modalHistoryTasks) ||
			$(event.target).is(modalUpdateTask)){
		      localStorage.setItem('highlight_after_reload', 'true');
		      localStorage.setItem('last_execution', today);
		      window.location.reload();
	    }
    });
	
	let taskDetailsLoading = false;

	$(document).on('click','.task_name', function (){
		taskDetailsLoading = true;
		project_id = $(this).data('projectid');
		project_nickname = $(this).data('projectnickname');
        lang = $(this).data('lang') || 'HE';
		meeting_id = $(this).data('meetingid');  
		chapter = $(this).data('chapter');
		subject = $(this).data('name');
		user_id = $(this).data('userid');
		area = $(this).data('area');
		recipient = $(this).data('recipient');
		responsible_id = $(this).data('responsibleid');
		destination_date = $(this).data('destinationdate');
		progress_status_id = $(this).data('progresstatusid');
		is_priority = $(this).data('ispriority');
		remark = $(this).data('remark');
		track_responsible_id = $(this).data('trackresponsibleid');
		track_type = $(this).data('tracktype');
		localStorage.setItem('track_type', track_type);
		reminder_date = $(this).data('reminderdate');
		reminder_time = $(this).data('remindertime');
		is_to_do_today = $(this).data('istodotoday');

		let form_data_nav = new FormData();
		form_data_nav.append('from','projects');
		form_data_nav.append('currentBadgeTarget',currentBadgeTarget);
		form_data_nav.append('currentProject',currentProject);
		form_data_nav.append('filter_by',$('#filter_by').val());
		form_data_nav.append('list_user_tasks',$('#list_user_tasks').val());
		if(is_to_do_today == 1)
			form_data_nav.append('is_to_do_today',1);

		$.ajax({
			type: 'POST',
			url: 'fill_meeting_ids.php',
			data: form_data_nav,
			cache: false,
			processData: false,
			contentType: false,
			success: function(nav_data){
				let nav_ids_array = $.trim(nav_data).split(',');
				let nav_index = nav_ids_array.indexOf(String(meeting_id));
				$('#link_prev_task').toggle(nav_index !== 0);
				$('#link_next_task').toggle(nav_index !== nav_ids_array.length - 1);
			}
		});

		let form_data = new FormData();
		form_data.append('id_meeting',meeting_id);
		form_data.append('wn_meeting_ids',$('#wn_meeting_ids').val());

		$.ajax({
			type: 'POST',
			url: 'task_details.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,
			success: function(data){
				let task_details = data.split('|~|');
				let content = fillContentTaskDetails(meeting_id,'',task_details,true);
				$('#div_content_task_details').html(content);
				taskDetailsLoading = false;
				$('#modalTaskFollowupActions').modal('show');
				setProjectModalTitle(project_id, '#modalTaskFollowupActions', false);
			},
		});

		setBellBcgColor(track_type);
		setEmergencyTaskCSS(is_priority);
			
		$.ajax({
			type: 'POST',
			url: 'check_if_new_task.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,
			success: function(data){
				$('.btn-set-to-read').remove();
				if(data == 1) {
					let button =
						"<button type='button' class='btn-set-to-read vertical-align-top btn btn-primary font-weight-bold marginLeft10 fontSize16' onclick='setToReadTask()'>" +
							"<i class='fa-solid fa-check colorGreen'></i>&nbsp;תודה על העדכון" +
						"</button>";

					$('#link_next_task').after(button);
				}
			},
		});	
		
		$('#modalContent input[type="hidden"]').remove();
		$('#modalContent').append("<input type='hidden' id='hidden_project_nickname' value='"+project_nickname+"'><input type='hidden' id='hidden_lang' value='"+lang+"'><input type='hidden' id='hidden_meeting_id' value='"+meeting_id+"'><input type='hidden' id='hidden_project_id' value='"+project_id+"'><input type='hidden' id='hidden_user_id' value='"+user_id+"'><input type='hidden' id='hidden_chapter' value='"+chapter+"'><input type='hidden' id='hidden_name' value='"+subject+"'><input type='hidden' id='hidden_area' value='"+area+"'><input type='hidden' id='hidden_recipient' value='"+recipient+"'><input type='hidden' id='hidden_responsible_id' value='"+responsible_id+"'><input type='hidden' id='hidden_destination_date' value='"+destination_date+"'><input type='hidden' id='hidden_progress_status_id' name='hidden_progress_status_id' value='"+progress_status_id+"'><input type='hidden' id='hidden_is_priority' value='"+is_priority+"'><input type='hidden' id='hidden_remark' value='"+remark+"'><input type='hidden' id='hidden_track_responsible_id' value='"+track_responsible_id+"'><input type='hidden' id='hidden_track_type' value='"+track_type+"'><input type='hidden' id='hidden_reminder_date' value='"+reminder_date+"'><input type='hidden' id='hidden_reminder_time' value='"+reminder_time+"'><input type='hidden' id='hidden_is_to_do_today' value='"+is_to_do_today+"'>");
		localStorage.setItem('meeting_id', meeting_id);
		$('tr.task-row-highlight').removeClass('task-row-highlight');
		const $highlightRow = $(this).closest('tr');
		if($highlightRow.length){ $highlightRow.addClass('task-row-highlight'); }
	});
	
	$(document).on('click','#save_update_task_btn', function (){
		let form_data = new FormData();
		form_data.append('from','projects');
		form_data.append('id_project',$('#hidden_project_id').val());
		form_data.append('currentBadgeTarget',localStorage.getItem('target'));
		form_data.append('filter_by',$('#filter_by').val());
		form_data.append('list_user_tasks',$('#list_user_tasks').val());
				
		if(localStorage.getItem('prId') != null)
			form_data.append('currentProject',localStorage.getItem('prId'));
		else  
			form_data.append('currentProject',0);
		
		if($('#hidden_is_to_do_today').val() == 1)
			form_data.append('is_to_do_today',1);
		
		$.ajax({
			type: 'POST',
			url: 'fill_meeting_ids.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,
			success: function(data){	   				
				let meeting_ids_array = data.split(',');						
				let current_meeting_id = $('#hidden_meeting_id').val();
				let index = meeting_ids_array.indexOf(current_meeting_id);

				if($('#progress_status_update option:selected').data('name-en') == 'Archive' ||
					$('#progress_status_update option:selected').data('name-en') == 'Done'){
						index++;
						localStorage.setItem('skip_followup_popup','1');
						if(localStorage.getItem('target') == 'active_tracking'){
							$('tr.meeting_' + current_meeting_id).remove();
							$('._badge-switcher[data-target="active_tracking"] span').each(function(){
								$(this).text(Math.max(0, parseInt($(this).text()) - 1));
							});
							$('.badge-switcher[data-target="active_tracking"][data-prid="' + $('#hidden_project_id').val() + '"] span').each(function(){
								$(this).text(Math.max(0, parseInt($(this).text()) - 1));
							});
						} else if(localStorage.getItem('target') == 'user_tasks'){
							$('tr.meeting_' + current_meeting_id).remove();
							$('._badge-switcher[data-target="user_tasks"] span').each(function(){
								$(this).text(Math.max(0, parseInt($(this).text()) - 1));
							});
							$('.badge-switcher[data-target="user_tasks"][data-prid="' + $('#hidden_project_id').val() + '"] span').each(function(){
								$(this).text(Math.max(0, parseInt($(this).text()) - 1));
							});
						} else if(localStorage.getItem('target') == 'what_news'){
							$('tr.meeting_' + current_meeting_id).remove();
							$('._badge-switcher[data-target="what_news"] span').each(function(){
								$(this).text(Math.max(0, parseInt($(this).text()) - 1));
							});
							$('.badge-switcher[data-target="what_news"][data-prid="' + $('#hidden_project_id').val() + '"] span').each(function(){
								$(this).text(Math.max(0, parseInt($(this).text()) - 1));
							});
						}
				}

				let next_meeting_id = (index < meeting_ids_array.length) ? meeting_ids_array[index] : '';
				localStorage.setItem('next_meeting_id',next_meeting_id);
				if($('#hidden_is_to_do_today').val() == 1)
					localStorage.setItem('is_to_do_today','1');
				else
					localStorage.removeItem('is_to_do_today');
				setData(current_meeting_id,'','update_task',1,0,'for_closing');
			}
       });
	});

	window.advanceToNextTaskAndCancelTracking = function(){
		let form_data = new FormData();
		form_data.append('from','projects');
		form_data.append('id_project',$('#hidden_project_id').val());
		form_data.append('currentBadgeTarget',localStorage.getItem('target'));
		form_data.append('filter_by',$('#filter_by').val());
		form_data.append('list_user_tasks',$('#list_user_tasks').val());

		if(localStorage.getItem('prId') != null)
			form_data.append('currentProject',localStorage.getItem('prId'));
		else
			form_data.append('currentProject',0);

		if($('#hidden_is_to_do_today').val() == 1)
			form_data.append('is_to_do_today',1);

		$.ajax({
			type: 'POST',
			url: 'fill_meeting_ids.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,
			success: function(data){
				let meeting_ids_array = data.split(',');
				let current_meeting_id = $('#hidden_meeting_id').val();
				let index = meeting_ids_array.indexOf(current_meeting_id);

				index++;

				let next_meeting_id = (index < meeting_ids_array.length) ? meeting_ids_array[index] : '';
				localStorage.setItem('next_meeting_id',next_meeting_id);
				if($('#hidden_is_to_do_today').val() == 1)
					localStorage.setItem('is_to_do_today','1');
				else
					localStorage.removeItem('is_to_do_today');

				if(localStorage.getItem('target') == 'active_tracking'){
					$('tr.meeting_' + current_meeting_id).remove();
					$('._badge-switcher[data-target="active_tracking"] span').each(function(){
						$(this).text(Math.max(0, parseInt($(this).text()) - 1));
					});
					$('.badge-switcher[data-target="active_tracking"][data-prid="' + $('#hidden_project_id').val() + '"] span').each(function(){
						$(this).text(Math.max(0, parseInt($(this).text()) - 1));
					});
				} else {
					$('#tracking_log_' + current_meeting_id).remove();
				}

				fillLogTaskTracking($('#hidden_meeting_id').val(),'','for_closing',0,true);
			}
       });
	};

    $(document).on('click','#save_update_task_no_comment_btn', function (){
		let form_data = new FormData();
		form_data.append('from','projects');
		form_data.append('id_project',$('#hidden_project_id').val());
		form_data.append('currentBadgeTarget',localStorage.getItem('target'));
		form_data.append('filter_by',$('#filter_by').val());
		form_data.append('list_user_tasks',$('#list_user_tasks').val());
				
		if(localStorage.getItem('prId') != null)
			form_data.append('currentProject',localStorage.getItem('prId'));
		else  
			form_data.append('currentProject',0);
		
		if($('#hidden_is_to_do_today').val() == 1)
			form_data.append('is_to_do_today',1);
		
		$.ajax({
			type: 'POST',
			url: 'fill_meeting_ids.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,
			success: function(data){	   				
				let meeting_ids_array = data.split(',');						
				let current_meeting_id = $('#hidden_meeting_id').val();
				let index = meeting_ids_array.indexOf(current_meeting_id);

				if($('#progress_status_update option:selected').data('name-en') == 'Archive' ||
					$('#progress_status_update option:selected').data('name-en') == 'Done'){
						index++;
						localStorage.setItem('skip_followup_popup','1');
						if(localStorage.getItem('target') == 'active_tracking'){
							$('tr.meeting_' + current_meeting_id).remove();
							$('._badge-switcher[data-target="active_tracking"] span').each(function(){
								$(this).text(Math.max(0, parseInt($(this).text()) - 1));
							});
							$('.badge-switcher[data-target="active_tracking"][data-prid="' + $('#hidden_project_id').val() + '"] span').each(function(){
								$(this).text(Math.max(0, parseInt($(this).text()) - 1));
							});
						} else if(localStorage.getItem('target') == 'user_tasks'){
							$('tr.meeting_' + current_meeting_id).remove();
							$('._badge-switcher[data-target="user_tasks"] span').each(function(){
								$(this).text(Math.max(0, parseInt($(this).text()) - 1));
							});
							$('.badge-switcher[data-target="user_tasks"][data-prid="' + $('#hidden_project_id').val() + '"] span').each(function(){
								$(this).text(Math.max(0, parseInt($(this).text()) - 1));
							});
						} else if(localStorage.getItem('target') == 'what_news'){
							$('tr.meeting_' + current_meeting_id).remove();
							$('._badge-switcher[data-target="what_news"] span').each(function(){
								$(this).text(Math.max(0, parseInt($(this).text()) - 1));
							});
							$('.badge-switcher[data-target="what_news"][data-prid="' + $('#hidden_project_id').val() + '"] span').each(function(){
								$(this).text(Math.max(0, parseInt($(this).text()) - 1));
							});
						}
				}

				let next_meeting_id = (index < meeting_ids_array.length) ? meeting_ids_array[index] : '';
				localStorage.setItem('next_meeting_id',next_meeting_id);
				if($('#hidden_is_to_do_today').val() == 1)
					localStorage.setItem('is_to_do_today','1');
				else
					localStorage.removeItem('is_to_do_today');
				setData(current_meeting_id,'','update_task',0,0,'for_closing')
			}
       });
	});

	$(document).on('click', '.drag-task,.delete-task', function(event){
		event.stopPropagation();
	});
});

function setFilterParam(filter){ 
	const url = new URL(window.location.href);
	const params = url.searchParams;
	params.set('filter_by',filter);
	window.location.href = url.pathname + '?' + params.toString();
}

function setListParam(list){ 
	const url = new URL(window.location.href);
	const params = url.searchParams;
	params.set('list',list);
	window.location.href = url.pathname + '?' + params.toString();
}

$(window).on('load', function(){
    if(localStorage.getItem("is_modal_task_actions_opened") === "true"){
		localStorage.removeItem('is_modal_task_actions_opened');
		let project_id = localStorage.getItem("project_id") || '';
		let meeting_id = localStorage.getItem("meeting_id") || '';
		let iteration = localStorage.getItem("iteration") || '';
		let subject_ls = localStorage.getItem("subject") || '';
		let area_ls = localStorage.getItem("area") || '';
		let recipient_ls = localStorage.getItem("recipient") || '';
		let responsible_id_ls = localStorage.getItem("responsible_id") || '';
		let is_priority_ls = localStorage.getItem("is_priority") || '';
		let remark_ls = localStorage.getItem("remark") || '';
		let lang_ls = localStorage.getItem("lang") || '';
		let user_id_ls = localStorage.getItem("user_id") || '';
		let chapter_ls = localStorage.getItem("chapter") || '';
		let track_responsible_id_ls = localStorage.getItem("track_responsible_id") || '';
		let reminder_date_ls = localStorage.getItem("reminder_date") || '';
		let reminder_time_ls = localStorage.getItem("reminder_time") || '';
		let track_type_ls = localStorage.getItem("track_type") || '';

		let form_data = new FormData();
		form_data.append('id_meeting', meeting_id);

		$.ajax({
			type: 'POST',
			url: 'task_details.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,
			success: function(data){
				let task_details = data.split('|~|');
				let content = fillContentTaskDetails(meeting_id,'',task_details,true);
				$('#div_content_task_details').html(content);
				$('#modalTaskFollowupActions').modal('show');
				setProjectModalTitle(project_id, '#modalTaskFollowupActions', false);
			},
		});

		setBellBcgColor(localStorage.getItem('track_type'));
		setEmergencyTaskCSS(is_priority_ls);
		$('#modalContent input[type="hidden"]').remove();
		$('#modalContent').append("<input type='hidden' id='hidden_meeting_id' value='"+meeting_id+"'><input type='hidden' id='hidden_iteration' value='"+iteration+"'><input type='hidden' id='hidden_project_id' value='"+project_id+"'><input type='hidden' id='hidden_user_id' value='"+user_id_ls+"'><input type='hidden' id='hidden_chapter' value='"+chapter_ls+"'><input type='hidden' id='hidden_name' value='"+subject_ls+"'><input type='hidden' id='hidden_area' value='"+area_ls+"'><input type='hidden' id='hidden_recipient' value='"+recipient_ls+"'><input type='hidden' id='hidden_responsible_id' value='"+responsible_id_ls+"'><input type='hidden' id='hidden_is_priority' value='"+is_priority_ls+"'><input type='hidden' id='hidden_remark' value='"+remark_ls+"'><input type='hidden' id='hidden_track_responsible_id' value='"+track_responsible_id_ls+"'><input type='hidden' id='hidden_track_type' value='"+track_type_ls+"'><input type='hidden' id='hidden_reminder_date' value='"+reminder_date_ls+"'><input type='hidden' id='hidden_reminder_time' value='"+reminder_time_ls+"'><input type='hidden' id='hidden_lang' value='"+lang_ls+"'>");
    }
});

function setToReadTask(){
	let form_data = new FormData();
	form_data.append('id_meeting',$('#hidden_meeting_id').val());

	$.ajax({
		type: 'POST',
		url: 'set_to_read_task.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,
		success: function(data){
			$('#modalTaskFollowupActions').modal('hide');
			location.href = 'projects.php';
		},
	});
}

function updateWhatsNewSeenBtnState(){
	let anyChecked = $('#left_new_content .whats-new-checkbox:checked').length > 0;
	$('.whats-new-mark-seen-btn').prop('disabled', !anyChecked);
}
$(document).on('change', '.whats-new-checkbox', updateWhatsNewSeenBtnState);

function markCheckedWhatsNewSeen(panelSelector){
	let $checked = $(panelSelector).find('.whats-new-checkbox:checked');
	if($checked.length == 0) return;

	let requests = [];
	$checked.each(function(){
		let $checkbox = $(this);
		let log_id = $checkbox.data('lmuid');
		let updated_users = $checkbox.data('updatedusers');

		let form_data = new FormData();
		form_data.append('log_id', log_id);
		form_data.append('updated_users', updated_users);

		requests.push($.ajax({
			type: 'POST',
			url: 'set_to_read_task.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,
		}));
	});

	$.when.apply($, requests).then(function(){
		location.reload();
	});
}

function toggleSettings_to_do_today(){
	$('#div_time_tasks_appear').toggle();
	$('#div_max_num_tasks').toggle();
	$('#div_clear').toggle();
}

function clearToDoToday(){
	let form_data = new FormData();
	form_data.append('delete_all',1);
	
	$.ajax({
		type: 'POST',
		url: 'to_do_today_delete.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,
		success: function(data){
			localStorage.removeItem('last_execution');
			localStorage.setItem('time_tasks_appear',$('#time_tasks_appear').val());
			localStorage.setItem('max_num_tasks',$('#max_num_tasks').val());
			window.location.reload();
		}, 
	});	
}

function toTasksList(id_project){
	$.post("unset_reports_sessions.php",{id_project:id_project},function(data){
		location.href='meetings.php?project_id='+id_project;
	});
}

</script>

<style>
.status-blank-when-empty {
	color: transparent;
}

.bgColor-cbddec { background-color: #9ef7b6 !important; }

.padding-btn {
	padding: 15px 0;
}

.colorBlue {
	color:#0d6efd;
}

.btn {
	color: white;
}

.btn:hover {
	color: #e8f1f1;
}

.responsible-col {
    margin-left: 5px;
}

.btn-group {
	width: 180px;
}

.btn-group-toggle .btn {
    font-size: 11px;
	height: 30px;
    line-height: 18px;
}

.btn-group-toggle input[type="radio"] {
    display: none;
}

@media (max-width: 768px) {
  .width25Percents{
      width: 50%;
	  margin-bottom: 10px;
   }
   
  .responsible-col {
     flex: 100%;
     text-align: right;
     margin-top: 5px;
  }
}

.filter-radio-row {
  display: inline-block;
}

@media (orientation: landscape) and (max-height: 500px) {
  #div_top_buttons {
     width: fit-content;
     margin-left: auto;
     margin-right: auto;
  }

  .task_name .width15Percents,
  .task_name .width85Percents {
     width: 100% !important;
  }

  .task_name .width65Percents,
  .task_name .width35Percents {
     width: 100% !important;
  }

  .task_name .width10Percents,
  .task_name .width90Percents {
     width: 100% !important;
  }

  .task-title-row {
     line-height: 1.3 !important;
     margin-bottom: 6px;
  }

  #div_filter_user_tasks input[name="user_tasks_filter"] {
     width: 16px;
     height: 16px;
     accent-color: #1a5276;
     vertical-align: middle;
     margin-left: 4px;
  }

  #filter_user_tasks_radios {
     display: flex;
     flex-wrap: wrap;
  }

  .filter-radio-row {
     width: 100%;
     box-sizing: border-box;
     margin-bottom: 8px;
     text-align: right;
  }

  .task_name .width15Percents,
  .task_name .width10Percents {
     margin-bottom: 8px;
     justify-content: flex-start !important;
  }
}

@media (min-width: 768px) and (max-width: 1024px) {
  .max-height-60vh {
     max-height: 40vh;
  }
  .responsible-col {
     flex: 100%;
     text-align: right;
     margin-top: 5px;
  }
}

@media screen and (max-width: 600px) {
    .task-container > .flex-row,
    .task-container > .width15Percents,
    .task-container > .width85Percents {
        flex-direction: column !important;
        width: 100% !important;
    }

    .task-container .width65Percents,
    .task-container .width35Percents {
        width: 100% !important;
    }

    .task_name .width15Percents,
    .task_name .width85Percents {
        width: 100% !important;
    }

    .task_name .width65Percents,
    .task_name .width35Percents {
        width: 100% !important;
    }

    #user_tasks_filter_row > div {
        width: 100% !important;
        max-width: 100% !important;
        flex: 0 0 100% !important;
    }

    .task_name .width10Percents,
    .task_name .width90Percents {
        width: 100% !important;
    }

    .task-title-row {
        line-height: 1.3 !important;
        margin-bottom: 6px;
    }

    #div_filter_user_tasks input[name="user_tasks_filter"] {
        width: 16px;
        height: 16px;
        accent-color: #1a5276;
        vertical-align: middle;
        margin-left: 4px;
    }

    #filter_user_tasks_radios {
        display: flex;
        flex-wrap: wrap;
    }

    .what-news-header-actions {
        position: static !important;
        justify-content: center !important;
        margin-top: 8px;
    }

    .filter-radio-row {
        width: 100%;
        box-sizing: border-box;
        margin-bottom: 8px;
        text-align: right;
    }

    .task_name .width15Percents,
    .task_name .width10Percents {
        margin-bottom: 8px;
        justify-content: flex-start !important;
    }

    #div_top_buttons {
        flex-direction: row !important;
        width: 100% !important;
    }

    span, .color-1A5276, .colorRed {
        word-break: break-word;
    }

    .modalTaskFollowupActions.modal {
        display: flex !important;          
        align-items: center !important;    
        justify-content: center !important;
        padding: 1rem;                     
    }

    .modalTaskFollowupActions .modal-dialog {
        width: 90% !important;        
        max-width: 90% !important;     
        margin: 0 auto !important;     
    }

    .modalTaskFollowupActions .modal-content {
        max-height: 80vh !important;
        overflow-y: auto !important;
    }
}

tr.task-row-highlight td {
    position: relative;
}
tr.task-row-highlight td::after {
    content: "";
    position: absolute;
    inset: 0;
    background-color: #9ef7b6;
    pointer-events: none;
    z-index: 0;
}
tr.task-row-highlight td > * {
    position: relative;
    z-index: 1;
    background-color: transparent !important;
}
</style>
