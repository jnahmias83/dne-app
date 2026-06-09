<?php 
include 'functions/functions.php';
error_reporting(E_ALL);
ini_set('display_errors', '1');	

$one = 1;
$empty_remark = '';
$content = '';

if(@$_POST['isTracking'] == 1){
	$sql_log_meeting_tracking = "SELECT lmt.id AS id,lmt.action_date AS action_date,
	                            lmt.remark AS remark,u.nickname AS user_nickname,
								m.reminder_date AS reminder_date,
								m.id_track_responsible AS id_track_responsible
                                FROM dne_log_meeting_tracking lmt	
								LEFT JOIN dne_meetings m ON lmt.id_meeting = m.id
								LEFT JOIN dne_responsibles r ON m.id_responsible = r.id
								LEFT JOIN dne_progress_status ps ON m.id_progress_status = ps.id
								LEFT JOIN dne_users u ON lmt.id_user = u.id	
                                WHERE lmt.id_meeting = ?							
							    AND lmt.is_remark_appears_log = ?
							    AND lmt.remark <> ?
							    ORDER BY lmt.id DESC";	
	$query = $mysqli->prepare($sql_log_meeting_tracking);
	$query->bind_param("iis",$_POST['id_meeting'],$one,$empty_remark);
	$query->execute();
	$query->store_result();
	$log_meeting_tracking_num_rows = $query->num_rows;
	$log_meeting_tracking = fetch($query);	

	$existing_remarks = "עדכונים";
	$padding = 'paddingRight5';
	$align = 'alignRight';
}
else {
	$sql_log_meeting_updates = "SELECT lmu.id AS id,lmu.action_date AS action_date,
	                            lmu.remark AS remark,u.nickname AS user_nickname,
                                ps.name AS ps_name,ps.name_he AS ps_name_he								
                                FROM dne_log_meeting_updates lmu
								LEFT JOIN dne_users u ON lmu.id_user = u.id
								LEFT JOIN dne_progress_status ps ON lmu.id_progress_status = ps.id
                                WHERE lmu.id_meeting = ?							
							    AND lmu.is_remark_appears_log = ?
                                AND lmu.remark <> ?								
							    ORDER BY lmu.id DESC";	
	$query = $mysqli->prepare($sql_log_meeting_updates);
	$query->bind_param("iis",$_POST['id_meeting'],$one,$empty_remark);
	$query->execute();
	$query->store_result();
	$log_meeting_updates_num_rows = $query->num_rows;
	$log_meeting_updates = fetch($query);
	
	$existing_remarks = "עדכונים";
	$padding = 'paddingRight5';
	$align = 'alignRight';
	
	if($_POST['lang'] == "EN"){
		$padding = 'paddingLeft5';
		$align = 'alignLeft';
		
		if(@$_POST['is_updates'] == 1)
			$existing_remarks = "Updates";	
	}
}

if(@$_POST['isTracking'] == 1){
	if($log_meeting_tracking_num_rows > 0){	
		$content =	"<div class='marginTop5 fontSize13 color-349feb font-weight-bold alignCenter'>עדכונים</div>";
		$content .= "<div class='margin-10-x-auto max-height-180 overflow-x-hidden overflow-y-scroll'>";		
	
		foreach(@$log_meeting_tracking as $item){
			$id_track_responsible = @$item->id_track_responsible;
			$query = $mysqli->prepare("SELECT nickname FROM dne_users WHERE id = ?");
			$query->bind_param('i',$item->id_track_responsible);	
			$query->execute(); 
			$query->store_result();
			$track_responsible = fetch_unique($query);
			$track_responsible_name = @$track_responsible->nickname;
			
            if($item->reminder_date != '0000-00-00' || @$track_responsible_name != '')
				$tracking_data = '(';
											
			if($item->reminder_date != '0000-00-00')
				$tracking_data .= smartDate($item->reminder_date, @$_POST['lang']);
			
			if($item->reminder_date != '0000-00-00' && $track_responsible_name != '')
				$tracking_data .= ',';
			
			if(@$track_responsible_name != '')
				$tracking_data .= @$track_responsible_name;
			
			if($item->reminder_date != '0000-00-00' || @$track_responsible_name != '')
				$tracking_data .= ')';	   
			
			$content .= "<div class='flex marginTop10 width100Percents' >";
			$content .=      "<div class='width30 paddingTop5'>";
			$content .=           "<input type='checkbox' id='log_meeting_tracking_{$item->id}' name='log_meeting_tracking[]' value='{$item->id}' onclick=\"setTextColor('div_log_meeting_tracking_{$item->id}',this,'colorRed')\" />";
			$content .=      "</div>";
			$content .=      "<div id='div_log_meeting_tracking_{$item->id}' class='".@$padding." fontSize13 width90Percents colorGrey border-black ".$align."'>";      
			$content .=           "<span class='dir-rtl unicode-bidi-embed'>[".smartDate(@$item->action_date, @$_POST['lang'])."]</span> <span class='dir-rtl unicode-bidi-embed'>".@$item->user_nickname."</span>";
			$content .= " - <span class='dir-rtl unicode-bidi-embed'>מעקב</span>";						
			$content .= " : <span class='dir-rtl unicode-bidi-embed display-inline-block'>".html_entity_decode(@$item->remark)."</span> <span class='dir-rtl unicode-bidi-embed'>".@$tracking_data."</span>					         
			              </div></div>";
		}

		$content .= '</div>';
	}
	else 	
		$content .= '';
}
else {
	if($log_meeting_updates_num_rows > 0){
		$content =	"<div class='marginTop5 alignCenter fontSize13 color-349feb font-weight-bold alignCenter'>".@$existing_remarks."</div>";
		$content .= "<div class='margin-10-x-auto max-height-180 overflow-x-hidden overflow-y-scroll'>";
			
		foreach(@$log_meeting_updates as $item){
			$progress_status = @$item->ps_name_he;
			
			if($_POST['lang'] == 'EN')
				$progress_status = @$item->ps_name;						
										
			$content .= "<div class='flex marginTop10 width100Percents'>";
			
			if(@$_POST['is_updates']){
				$content .=      "<div class='width30 paddingTop5'>";
				$content .=           "<input type='checkbox' id='log_meeting_updates_is_updates_{$item->id}' name='log_meeting_updates_is_updates[]' value='{$item->id}' onclick=\"setTextColor('div_log_meeting_updates_{$item->id}',this,'colorGreen')\" />";
				$content .=      "</div>";
			}
			else {
				$content .=      "<div class='width30 paddingTop5'>";
				$content .=           "<input type='checkbox' id='log_meeting_updates_{$item->id}' name='log_meeting_updates[]' value='{$item->id}' onclick=\"setTextColor('div_log_meeting_updates_{$item->id}',this,'colorGreen')\" />";
				$content .=      "</div>";
			}
				
			$content .=   "<div id='div_log_meeting_updates_{$item->id}' class='".@$padding." fontSize13 width90Percents colorGrey border-black ".@$align."'>";      
			$content .=   "<span class='dir-rtl unicode-bidi-embed'>[".smartDate(@$item->action_date, @$_POST['lang'])."]</span> <span class='dir-rtl unicode-bidi-embed'>".@$item->user_nickname."</span>";
			
			if(preg_match('/\p{L}/u', $progress_status)){
				$content .= " - <span class='dir-rtl unicode-bidi-embed'>".@$progress_status."</span>";
			}				 
							 
			$content .=  " : <span class='dir-rtl unicode-bidi-embed display-inline-block'>".html_entity_decode(@$item->remark)."</span>	          								         
			              </div></div>";
		}

		$content .= '</div>';
	}
	else 	
		$content .= '';
}
			
echo $content;
?>