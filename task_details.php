<?php
include 'functions/functions.php';

$query = $mysqli->prepare("SELECT logo_stread FROM dne_logos LIMIT 1");
$query->store_result();
$logo = fetch_unique($query);

$query = $mysqli->prepare("SELECT * FROM dne_inputs_colors LIMIT 1");
$query->execute();
$query->store_result();
$bg_color_inputs = fetch_unique($query);

if(@$_POST['all_ids_to_edit'] == '') {
	$id = @$_POST['id_meeting'];

    $query = $mysqli->prepare("SELECT p.id AS p_id,p.name_he AS p_name_he,p.nickname AS p_nickname,
	                          r1.name AS r_name,
                              r2.name AS po_name,
							  m.task_creation_date AS task_creation_date,
							  m.destination_date AS destination_date,
						      m.task_creation_date AS task_creation_date,
							  c.name AS c_name,m.area AS area,
							  t.name_he AS t_name_he,t.color AS t_color,t.bgcolor AS t_bgcolor,
						      m.subject AS subject,
							  m.description AS description,
						      ps.id AS ps_id,ps.name_he AS ps_name_he,ps.name AS ps_name,
							  ps.color AS ps_color,ps.bgcolor AS ps_bgcolor,
						      m.image1 AS image1,
							  m.image1_width AS image1_width,
						      m.image1_height AS image1_height,
							  m.image2 AS image2,
						      m.image2_width AS image2_width,
						      m.image2_height AS image2_height,
							  m.id_track_responsible AS id_track_responsible,
							  m.track_type AS track_type,
							  m.reminder_date AS reminder_date,m.lang AS lang,p.lang AS p_lang
						      FROM dne_meetings m
						      LEFT JOIN dne_projects p ON m.id_project = p.id 
						      LEFT JOIN dne_responsibles r1 ON m.id_responsible = r1.id 
						      LEFT JOIN dne_responsibles r2 ON m.id_pass_on = r2.id 
						      LEFT JOIN dne_chapters c ON m.id_chapter = c.id 
						      LEFT JOIN dne_progress_status ps ON m.id_progress_status = ps.id
						      LEFT JOIN dne_tasks t ON m.id_task = t.id
						      WHERE m.id = ?");
	$query->bind_param('i',$id);	
	$query->execute(); 
	$query->store_result();
	$meeting = fetch_unique($query);

    $empty_remark = '';
    $one = 1;
    $action_update_label = 'סטטוס/יעד/הערה';	
	$query = $mysqli->prepare("SELECT lmu.action_date AS action_date,
		                       lmu.remark AS remark,u.nickname AS user_nickname,
							   ps.name AS ps_name,ps.name_he AS ps_name_he
							   FROM dne_log_meeting_updates lmu
							   LEFT JOIN dne_users u ON lmu.id_user = u.id
							   LEFT JOIN dne_progress_status ps ON lmu.id_progress_status = ps.id
							   WHERE lmu.id_meeting = ?
                               AND lmu.action = ?							   
							   AND lmu.is_remark_appears_log = ?
							   AND lmu.remark <> ?
							   ORDER BY lmu.id DESC");
	$query->bind_param("isis",$id,$action_update_label,$one,$empty_remark);
	$query->execute();
	$query->store_result();	
	$log_meeting_updates = fetch($query);

	$description = @$meeting->description;
	$description_updates = '';
	$all_remarks = '';

	$dir_log_meeting_updates = 'alignRight';
	$dir_updates = 'rtl';
	$padding_log_meeting_updates = 'paddingRight8';
	if(@$meeting->lang == 'EN'){
		$dir_log_meeting_updates = 'alignLeft';
		$dir_updates = 'ltr';
		$padding_log_meeting_updates = 'paddingLeft8';
	}

	foreach($log_meeting_updates as $item){
		$remark = html_entity_decode(@$item->remark);
		$action_date = @$item->action_date;
		$user_nickname = @$item->user_nickname;

		$all_remarks .= "<div class='marginTop5'>".@$user_nickname." - ".smartDate(@$action_date, @$meeting->p_lang).' - '.html_entity_decode(@$remark).'</div>';

		$progress_status_log_updates = simplifyStatusLabel(@$item->ps_name_he);
		if(@$meeting->p_lang == 'EN')
			$progress_status_log_updates = @$item->ps_name;

		if(@$remark != ''){
			$update_label_html = "<span class='badge-nickname-green'>"
							.@$user_nickname
							."</span> "
							."<span class='log-date-grey' style='unicode-bidi:isolate;'>"
							.smartDate(@$action_date, @$meeting->p_lang)
							."</span>";

			if(preg_match('/\p{L}/u', $progress_status_log_updates)){
				$update_label_html .=  " - <span style='font-weight:bold;'>"
								.@$progress_status_log_updates
								."</span>";
			}

			$update_label_html .= " - ";

			$description_updates .= "<div class='marginTop5 colorGreenDark ".@$padding_log_meeting_updates."' dir='".@$dir_updates."' style='display:flex;align-items:baseline;'>"
							."<span style='flex:0 0 auto;white-space:nowrap;'>".$update_label_html."</span>"
							."<span style='flex:1;min-width:0;unicode-bidi:isolate;'>"
							.html_entity_decode(@$remark)
							."</span>"
							."</div>";
		}
	}

	$log_tracking_toggle_icon = '';
	if(@$meeting->track_type == 1){
		$toggle_title = (@$meeting->p_lang == 'HE') ? 'הצג/הסתר מעקב' : 'Show/hide tracking';
		$log_tracking_toggle_icon = "<span class='cursor-pointer' onclick=\"$('#row_log_meeting_tracking').toggle();\" style='position:absolute;left:0;top:2px;z-index:2;' title='".$toggle_title."'><i class='fa-solid fa-paperclip fontSize14' style='color:#666;'></i></span>";
	}

	if($description_updates != ''){
		$description = "<div style='position:relative;'>".$description."</div>"
			."<hr style='border:none;border-top:2px solid #999;margin:8px 0;'/>"
			."<div style='position:relative;padding-left:22px;box-sizing:border-box;'>"
			.$log_tracking_toggle_icon
			."<div style='margin:4px 6px;max-height:90px;overflow-y:scroll;box-sizing:border-box;'>".$description_updates."</div>"
			."</div>";
	}
	else if($log_tracking_toggle_icon != ''){
		$description = "<div style='position:relative;padding-left:22px;box-sizing:border-box;'>".$log_tracking_toggle_icon.$description."</div>";
	}
	
	$tracking_remarks = '';

    if(@$meeting->track_type == 1){
		$progress_status = @$meeting->ps_name_he;
		if(@$meeting->p_lang != 'HE')
			$progress_status = @$meeting->ps_name;										
											
		$query = $mysqli->prepare("SELECT nickname FROM dne_users WHERE id = ?");
		$query->bind_param('i',$meeting->id_track_responsible);	
		$query->execute(); 
		$query->store_result();
		$track_responsible = fetch_unique($query);
		$track_responsible_name = $track_responsible->nickname;
		
		if(@$meeting->reminder_date != '0000-00-00' || @$track_responsible_name != '')
			$tracking_data = '(';
											
		if(@$meeting->reminder_date != '0000-00-00')
			$tracking_data .= smartDate(@$meeting->reminder_date, @$meeting->p_lang);
		
		if(@$meeting->reminder_date != '0000-00-00' && $track_responsible_name != '')
			$tracking_data .= ',';
		
		if(@$track_responsible_name != '')
			$tracking_data .= @$track_responsible_name;
		
		if(@$meeting->reminder_date != '0000-00-00' || @$track_responsible_name != '')
			$tracking_data .= ')';
		
		$query = $mysqli->prepare("SELECT lmt.action_date AS action_date,
		                           lmt.remark AS remark,u.nickname AS user_nickname
								   FROM dne_log_meeting_tracking lmt
							       LEFT JOIN dne_users u ON lmt.id_user = u.id
								   WHERE lmt.id_meeting = ?
							       AND lmt.is_remark_appears_log = ?
							       AND lmt.remark <> ?
							       ORDER BY lmt.id DESC");
	    $query->bind_param("iis",$id,$one,$empty_remark);
	    $query->execute();
	    $query->store_result();
	    $track_display_rows = $query->num_rows;
    	$log_meeting_tracking = fetch($query);

		if(@$meeting->reminder_date != '0000-00-00')
			$reminder_bell_html = "<div style='line-height:1.3;text-align:center;'><i class='fa-solid fa-bell colorRed'></i><div class='dir-rtl unicode-bidi-embed' style='white-space:nowrap;'>".smartDate(@$meeting->reminder_date, @$meeting->p_lang)."</div></div>";
		else
			$reminder_bell_html = "<i class='fa-solid fa-bell-slash colorGrey'></i>";

		$red_badge_html = '';
		if($track_responsible_name != ""){
			$red_badge_html =  "<span class='border-black padding-4x-4y borderRadius20 align-items-center justify-content-center fontSize12 colorWhite bgColorRed' style='display:inline-flex;line-height:1;'>"
									.@$track_responsible_name
								  ."</span>";
		}

		$tracking_table = "<table style='width:100%;table-layout:fixed;border-collapse:collapse;border:none!important;'>";

		$tracking_items = array_values((array)$log_meeting_tracking);
		$tracking_items_count = count($tracking_items);
		$tracking_rowspans = array();
		$tracking_group_start = 0;
		while($tracking_group_start < $tracking_items_count){
			$tracking_group_nickname = @$tracking_items[$tracking_group_start]->user_nickname;
			$tracking_group_size = 1;
			while(($tracking_group_start + $tracking_group_size) < $tracking_items_count
					&& @$tracking_items[$tracking_group_start + $tracking_group_size]->user_nickname === $tracking_group_nickname){
				$tracking_group_size++;
			}
			$tracking_rowspans[$tracking_group_start] = $tracking_group_size;
			for($tracking_group_offset = 1; $tracking_group_offset < $tracking_group_size; $tracking_group_offset++){
				$tracking_rowspans[$tracking_group_start + $tracking_group_offset] = 0;
			}
			$tracking_group_start += $tracking_group_size;
		}

		foreach($tracking_items as $tracking_row_index => $item){
			if(@$item->remark == '')
				$remark = 'במעקב';
			else
				$remark = html_entity_decode(@$item->remark);

			$action_date = smartDate(@$item->action_date, @$meeting->p_lang);
			$remark_color_class = ($tracking_row_index === 0) ? 'colorRed' : 'colorGrey';

			$tracking_table .= "<tr class='alignCenter' style='background-color:".@$bg_color_inputs->b_bgcolor.";'>";
			if($tracking_rowspans[$tracking_row_index] > 0){
				$tracking_table .= "<td rowspan='".$tracking_rowspans[$tracking_row_index]."' style='vertical-align:middle;text-align:center;width:9%;border:none!important;'>";
				$tracking_table .= "<span style='display:inline-block;width:18px;height:18px;line-height:18px;border-radius:50%;background-color:#000;color:#fff;font-size:9px;text-align:center;vertical-align:middle;box-sizing:border-box;'>"
									.@$item->user_nickname
								."</span>";
				$tracking_table .= "</td>";
			}
			$tracking_table .= "<td style='vertical-align:middle;text-align:right;padding:0 2px 0 6px;width:91%;border:none!important;'><span class='marginRight5 dir-rtl unicode-bidi-embed' style='white-space:nowrap;vertical-align:middle;'>".$action_date." -</span> <span class='".$remark_color_class." dir-rtl unicode-bidi-embed' style='white-space:normal;word-wrap:break-word;overflow-wrap:break-word;vertical-align:middle;'>".html_entity_decode($remark)."</span></td>";
			$tracking_table .= "</tr>";
	    }

		if($track_display_rows == 0){
			$mm_track_label = (@$meeting->p_lang != 'HE') ? 'Task in tracking' : 'משימה במעקב';
			$tracking_table .= "<tr class='alignCenter' style='background-color:".@$bg_color_inputs->b_bgcolor.";'><td colspan='2' style='vertical-align:middle;text-align:right;padding:0 8px 0 6px;border:none!important;'><span class='colorRed dir-rtl unicode-bidi-embed' style='vertical-align:middle;'>".$mm_track_label."</span></td></tr>";
		}

		$tracking_table .= "</table>";

		$tracking_remarks .= "<tr id='row_log_meeting_tracking'><td colspan='3'>"
							."<div style='position:relative;padding:0 38px 0 53px;min-height:44px;display:flex;align-items:center;background-color:".@$bg_color_inputs->b_bgcolor.";border:1px solid #999;border-radius:6px;box-sizing:border-box;margin:4px 6px;'>"
							."<div dir='rtl' onclick=\"$('#tracking_btn').trigger('click');\" style='width:100%;max-height:112px;overflow-y:scroll;border:2px solid ".@$bg_color_inputs->f_bgcolor.";border-radius:4px;box-sizing:border-box;cursor:pointer;'>".$tracking_table."</div>"
							."<div style='position:absolute;left:8px;top:0;bottom:0;width:45px;display:flex;align-items:center;justify-content:center;'>".$reminder_bell_html."</div>"
							."<div style='position:absolute;right:4px;top:0;bottom:0;width:30px;display:flex;align-items:center;justify-content:center;'>".$red_badge_html."</div>"
							."</div>"
							."</td></tr>";
	}	
	
	$new_task_label = 'חדשה';
	$query = $mysqli->prepare("SELECT destination_date 
	                          FROM dne_log_meeting_updates 
							  WHERE id_meeting = ? 
							  AND action = ?");
	$query->bind_param("is",$id,$new_task_label);
	$query->execute();
	$query->store_result();	
	
	if($query->num_rows == 0) {	
		$old_destination_date = date('Y-m-d',strtotime(@$meeting->task_creation_date.'+7 days'));
	}
	else {
		$query = fetch_unique($query);
		if(@$query->destination_date != '0000-00-00')
	        $old_destination_date = @$query->destination_date;
	    else 
			$old_destination_date = date('Y-m-d',strtotime(@$meeting->task_creation_date.'+7 days'));
	}	
	
	$update_task_label = 'סטטוס/יעד/הערה';
	$continue_task_label = 'משימת המשך';
	$empty_date = '0000-00-00';
	$query = $mysqli->prepare("SELECT COUNT(id) AS count_id
							  FROM dne_log_meeting_updates 
							  WHERE id_meeting = ?
							  AND (action = ? OR action = ?)
							  AND destination_date <> ?");
	$query->bind_param("isss",$id,$update_task_label,$continue_task_label,$empty_date);
	$query->store_result();	
	$query = fetch_unique($query);
	$count_delay_dest_date = $query->count_id;

	$task_details  = @$meeting->p_id.'|~|';
	$task_details .= @$meeting->p_name_he.'|~|';
	$task_details .= @$meeting->r_name.'|~|';
	$task_details .= @$meeting->po_name.'|~|';
	$task_details .= @$old_destination_date.'|~|';
	$task_details .= @$meeting->destination_date.'|~|';
	$task_details .= stripNbspArtifact(@$meeting->c_name).'|~|';
	$task_details .= stripNbspArtifact(@$meeting->area).'|~|';
	$task_details .= stripNbspArtifact(@$description).'|~|';
	$task_details .= @$meeting->image1.'|~|';
	$task_details .= @$meeting->ps_id.'|~|';
	$task_details .= simplifyStatusLabel(@$meeting->ps_name_he).'|~|';
	$task_details .= stripNbspArtifact(@$meeting->subject).'|~|';
	$task_details .= @$count_delay_dest_date.'|~|';
	$task_details .= @$meeting->t_name_he.'|~|';
	$task_details .= @$all_remarks.'|~|';
	$task_details .= @$meeting->image1_width.'|~|';
	$task_details .= @$meeting->image1_height.'|~|';
	$task_details .= @$meeting->image2.'|~|';
	$task_details .= @$meeting->image2_width.'|~|';
	$task_details .= @$meeting->image2_height.'|~|';
	$task_details .= @$meeting->task_creation_date.'|~|';
	$task_details .= @$meeting->track_type.'|~|';
	$task_details .= @$tracking_remarks.'|~|';
	$task_details .= @$meeting->reminder_date.'|~|';
	$task_details .= @$logo->logo_stread.'|~|';
	$task_details .= @$meeting->t_bgcolor.'|~|';
	$task_details .= @$meeting->p_nickname.'|~|';
	$task_details .= @$meeting->t_color.'|~|';
	$task_details .= @$meeting->ps_color.'|~|';
	$task_details .= @$meeting->ps_bgcolor;
}
else {
	$query = $mysqli->prepare("SELECT name_he,nickname FROM dne_projects WHERE id = ?");
	$query->bind_param('i',$_POST['id_project']);	
	$query->execute(); 
	$query->store_result();
	$project = fetch_unique($query);
	
	$task_details = @$project->nickname.'|~|'.@$project->name_he.'|~|';
	
	$all_ids_to_edit_array = explode(',',$_POST['all_ids_to_edit']);
	
	$task_details .= "   <tr class='fontSize13 alignCenter'>";
	$task_details .=         "<td class='alignRight paddingRight10 border-white'>";
	
	$last_chapter_name = '';
	$ps_id = 0;
	$count_ps_ids = 0;
	
	for($i=0;$i<sizeof($all_ids_to_edit_array);$i++) {
		$query = $mysqli->prepare("SELECT ps.id	AS ps_id	
							       FROM dne_meetings m
								   LEFT JOIN dne_responsibles r ON m.id_responsible = r.id
								   LEFT JOIN dne_projects_suppliers ps ON r.id_projects_suppliers = ps.id
								   WHERE m.id = ?");
	    $query->bind_param('i',$all_ids_to_edit_array[$i]);	
	    $query->execute(); 
	    $query->store_result();
		$meeting = fetch_unique($query);
        
		if($ps_id != @$meeting->ps_id) {
			$count_ps_ids++;
			$ps_id = $meeting->ps_id;	
		}			
	}
	
	for($i=0;$i<sizeof($all_ids_to_edit_array);$i++) {
		$query = $mysqli->prepare("SELECT id_responsible,id_chapter,subject,area		
							       FROM dne_meetings WHERE id = ?");
	    $query->bind_param('i',$all_ids_to_edit_array[$i]);	
	    $query->execute(); 
	    $query->store_result();
		$meeting = fetch_unique($query);	
													
		$query = $mysqli->prepare("SELECT name FROM dne_responsibles 
		                          WHERE id = ?");
		$query->bind_param('i',$meeting->id_responsible);	
		$query->execute(); 
		$query->store_result();
		$responsible = fetch_unique($query);
		$responsible_name = @$responsible->name;								
													
		if(strlen(@$responsible->name) > 30)
		   $responsible_name = mb_substr($responsible->name,0,30,'UTF-8');
													
		$query = $mysqli->prepare("SELECT name FROM dne_chapters 
		                          WHERE id = ?");
		$query->bind_param('i',$meeting->id_chapter);	
		$query->execute(); 
		$query->store_result();
		$chapter = fetch_unique($query);											
													
		$chapter_name = stripNbspArtifact(@$chapter->name);
		if(strlen(@$chapter->name) > 30)
			$chapter_name = mb_substr($chapter_name,0,30,'UTF-8');

		$subject = stripNbspArtifact(@$meeting->subject);
		if(strlen(@$meeting->subject) > 30)
		    $subject = mb_substr(trim(preg_replace('/[\x{00A0}\x{200B}]+/u',' ',strip_tags($subject))),0,30,'UTF-8');

		$area = stripNbspArtifact(@$meeting->area);
		if(strlen(@$meeting->area) > 30)
		   $area = mb_substr(trim(preg_replace('/[\x{00A0}\x{200B}]+/u',' ',strip_tags($area))),0,30,'UTF-8');
	   
	    $align_txt = 'alignLeft';
		$padding_txt = 'paddingLeft10';
													
		if(containsHebrew(@$metting->description)) {
			$align_txt = 'alignRight';
			$padding_txt = 'paddingRight10';
		}
		
		$ps_id = @$meeting->ps_id; 
		
		if($last_chapter_name != @$chapter_name) {
		    $last_chapter_name = @$chapter_name;
    	    $task_details .=  "<div class='marginTop5 font-weight-bold'>".@$chapter_name."</div>";	
		}
		
		if($count_ps_ids == 1)
			$task_details .=  "<div class='marginTop5 marginBottom5'><i class='fa-solid fa-check'></i>".@$subject."<span class='colorBlue font-weight-bold'> | </span>".@$area."</div>";
        else
			$task_details .=  "<div class='marginTop5 marginBottom5'><i class='fa-solid fa-check'></i>".@$responsible_name."<span class='colorBlue font-weight-bold'> | </span>".@$subject."<span class='colorBlue font-weight-bold'> | </span>".@$area."</div>";
	}
	 
	$task_details .=     "</td>";
	$task_details .= "</tr>|~|";
	$task_details .= @$logo->logo_stread;
}

echo $task_details;
?>