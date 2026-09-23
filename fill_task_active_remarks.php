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
	$padding = 'paddingRight8';
	$align = 'alignRight';
	$dir_attr = 'rtl';
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
	$padding = 'paddingRight8';
	$align = 'alignRight';
	$dir_attr = 'rtl';

	if($_POST['lang'] == "EN"){
		$padding = 'paddingLeft8';
		$align = 'alignLeft';
		$dir_attr = 'ltr';

		if(@$_POST['is_updates'] == 1)
			$existing_remarks = "Updates";
	}
}

if(@$_POST['isTracking'] == 1){
	if($log_meeting_tracking_num_rows > 0){	
		$content =	"<div class='marginTop5 fontSize13 color-349feb font-weight-bold alignCenter'>עדכונים</div>";
		$content .= "<div class='margin-10-x-auto max-height-180 overflow-x-hidden overflow-y-scroll'>";

		foreach(@$log_meeting_tracking as $item){
			if(@$item->remark == '')
				$tracking_remark = 'מעקב';
			else
				$tracking_remark = @$item->remark;

			$tracking_initials = mb_strtoupper(mb_substr(@$item->user_nickname, 0, 2, 'UTF-8'));

			$content .= "<div class='flex marginTop10 width100Percents'>";
			$content .=      "<div class='width30 paddingTop5'>";
			$content .=           "<input type='checkbox' id='log_meeting_tracking_{$item->id}' name='log_meeting_tracking[]' value='{$item->id}' onclick=\"setTextColor('div_log_meeting_tracking_{$item->id}',this,'colorRed')\" />";
			$content .=      "</div>";
			$content .=      "<div id='div_log_meeting_tracking_{$item->id}' dir='".@$dir_attr."' class='".@$padding." fontSize13 width90Percents colorGrey border-black ".$align." display-block' style='line-height:1.8; padding:5px;'>";
			$content .=           "<span style='display:inline-flex;align-items:center;gap:4px;vertical-align:top;'>";
			$content .=                "<span class='badge-circle' style='display:inline-flex;align-items:center;justify-content:center;background-color:#333;width:22px;height:22px;font-size:10px;'>".$tracking_initials."</span>";
			$content .=                "<span class='dir-rtl unicode-bidi-embed'>".smartDate(@$item->action_date, @$_POST['lang'])."</span>";
			$content .=           "</span>";
			$content .= " - <span class='dir-rtl unicode-bidi-embed'>".html_entity_decode(@$tracking_remark)."</span>
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
			$progress_status = simplifyStatusLabel(@$item->ps_name_he);

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

			$update_initials = mb_strtoupper(mb_substr(@$item->user_nickname, 0, 2, 'UTF-8'));

			$content .=   "<div id='div_log_meeting_updates_{$item->id}' dir='".@$dir_attr."' class='".@$padding." fontSize13 width90Percents colorGrey border-black ".@$align." display-block' style='line-height:1.8; padding:5px;'>";
			$content .=   "<span style='display:inline-flex;align-items:center;gap:4px;vertical-align:top;'>";
			$content .=        "<span class='badge-circle' style='display:inline-flex;align-items:center;justify-content:center;background-color:#333;width:22px;height:22px;font-size:10px;'>".$update_initials."</span>";
			$content .=        "<span class='dir-rtl unicode-bidi-embed'>".smartDate(@$item->action_date, @$_POST['lang'])."</span>";
			$content .=   "</span>";

			if(preg_match('/\p{L}/u', $progress_status)){
				$content .= " - <span class='dir-rtl unicode-bidi-embed'>".@$progress_status."</span>";
			}

			$content .=  " : <span class='dir-rtl unicode-bidi-embed'>".html_entity_decode(@$item->remark)."</span>
			              </div></div>";
		}

		$content .= '</div>';
	}
	else 	
		$content .= '';
}
			
echo $content;
?>