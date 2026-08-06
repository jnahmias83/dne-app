<?php
require_once('tcpdf_min/config/tcpdf_config.php');
require_once('tcpdf_min/tcpdf.php');
include 'include/header.php';
include 'functions/functions.php';
error_reporting(E_ALL);
ini_set('display_errors', '1');

$project_id = @$_GET['project_id'];

$query = $mysqli->prepare("SELECT * FROM dne_projects WHERE id = ?");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$project = fetch_unique($query);

$ps_name = 'בוצע/נמסר';
$query = $mysqli->prepare("SELECT id FROM dne_progress_status WHERE id_project = ? AND name = ?");
$query->bind_param("is",$project_id,$ps_name);
$query->execute();
$query->store_result();
$progress_status = fetch_unique($query);
$id_progress_status = $progress_status->id;

$query = $mysqli->prepare("SELECT * FROM dne_log_create_pdf WHERE id_project = ?");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$last_log_pdf_cd_num_rows = $query->num_rows;

if ($last_log_pdf_cd_num_rows === 1) {
	$last_log_pdf_cd = fetch_unique($query);
	$last_pdf_date = $last_log_pdf_cd->last_pdf_created_date;
}
else {
	$query = $mysqli->prepare("SELECT * FROM dne_log_create_pdf WHERE id_project = ? ORDER BY id DESC LIMIT 1,1");
    $query->bind_param("i",$project_id);
    $query->execute();
    $query->store_result();
	$last_log_pdf_cd = fetch_unique($query);
	$last_pdf_date = $last_log_pdf_cd->last_pdf_created_date;
}

$dir_table = 'rtl';
$style_table = "margin-top:25px;margin-left:1%;";

$counts_array = array();
$images_array = array();
$is_appears_img_array = array();
$chapters_array = array();

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
$pdf->setFooterData(array(0,64,0), array(0,64,128));
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->setFontSubsetting(true);
$pdf->SetFont('freesans', '', 12);
$pdf->setPrintHeader(false);

$html_header = '<table width="90%"><tr><td style="text-align:center;"><img src="images/davidnahmias_stripe.png" /><br/><br/></td></tr>';

$html1_body = '<tr style="font-size:16px;"><td width="40px;">&nbsp;</td><td style="text-align:center;"><span dir="'.$dir_table.'"><strong><u>'.$project->name_he.'<br/>דו\'ח סטטוס פרוייקט <br/>'.substr(date('Y-m-d'),8,2).'/'.substr(date('Y-m-d'),5,2).'/'.substr(date('Y-m-d'),0,4).'</u></strong></span></td></tr></table>';
$html1_body.= '<div class="row">';
$html1_body.= '<div class="col-md-12">';
$html1_body.= '<table dir="'.$dir_table.'" style="'.$style_table.'"><tr><td width="4%">&nbsp;</td><td><table cellpadding="4">';
$html1_body.='<tr style="background-color:silver;font-size:13px;font-weight:bold;">';
$html1_body.='<th width="30" style="text-align:center;border:1px solid black;font-size:11px;">&#x2116;</th>';
$html1_body.='<th width="90" style="text-align:center;border:1px solid black;font-size:11px;">נושא/תחום</th>';
$html1_body.='<th width="90" style="text-align:center;border:1px solid black;font-size:11px;">איזור/נושא</th>';
$html1_body.='<th width="320" style="text-align:center;border:1px solid black;font-size:11px;">תיאור</th>';
$html1_body.='<th width="90" style="text-align:center;border:1px solid black;font-size:11px;">סוג משימה</th>';
$html1_body.='<th width="80" style="text-align:center;border:1px solid black;font-size:11px;">אחראי</th>';
$html1_body.='<th width="90" style="text-align:center;border:1px solid black;font-size:11px;">להעביר ל/ <br/> לאשר מול</th>';
$html1_body.='<th width="50" style="text-align:center;border:1px solid black;font-size:10px;">יצירת <br/> משימה</th>';
$html1_body.='<th width="50" style="text-align:center;border:1px solid black;font-size:10px;">תאריך <br/> יעד</th>';
$html1_body.='<th width="85" style="text-align:center;border:1px solid black;font-size:11px;">סטטוס <br/> התקדמות</th>';
$html1_body.='</tr>';

$query = $mysqli->prepare("SELECT * FROM dne_chapters WHERE id_project = ? ORDER BY id_display");
$query->bind_param("i",$project_id);
$query->execute(); 
$query->store_result();
$chapters = fetch($query);

foreach($chapters as $item) {	
	$chapter_id = $item->id;
	$is_appears = 1;
								
	$query = $mysqli->prepare("SELECT m.id AS id,m.subject AS subject,m.area AS area,m.description AS description,m.id_task AS id_task,m.id_responsible AS id_responsible,
	                          m.id_pass_on AS id_pass_on,m.is_change_row_style AS is_change_row_style,m.task_creation_date AS task_creation_date,
							  m.destination_date AS destination_date,m.id_progress_status AS id_progress_status,m.image1 AS image1,m.is_appears_img1 AS is_appears_img1,
							  m.updated_date AS updated_date,c.name AS chapter_name
	                          FROM dne_meetings m
							  LEFT JOIN dne_chapters c ON m.id_chapter = c.id
							  WHERE m.id_project = ? AND m.id_chapter = ? AND m.is_appears = ? AND m.updated_date > ? AND m.id_progress_status <> ?
							  ORDER BY m.subject,m.id_area");
	$query->bind_param("iiisi",$project_id,$chapter_id,$is_appears,$last_pdf_date,$id_progress_status);
	$query->execute(); 
	$query->store_result();
	$meetings_num_rows = $query->num_rows;
	$meetings = fetch($query);
	
	if($meetings_num_rows >0) {
		$html1_body.='<tr style="background-color:#a3def0;font-size:11px;">';
		$html1_body.='<td colspan="12" style="text-align:right;padding-right:5px;border:1px solid black;"><strong>'.stripNbspArtifact(@$item->name).'</strong></td>';
		$html1_body.='</tr>';
		
		$count1 = 0;
		foreach($meetings as $item) {
			$count1++;
			
			$meeting_id = @$item->id;
			$subject = stripNbspArtifact(@$item->subject);
			$area = stripNbspArtifact(@$item->area);
			$description = stripNbspArtifact(@$item->description);
			$task_id = @$item->id_task;
			$responsible_id = @$item->id_responsible;
			$pass_on_id = @$item->id_pass_on;
			$is_change_row_style = @$item->is_change_row_style;
			
			$task_creation_date = '';
			if(@$item->task_creation_date != '0000-00-00')
				$task_creation_date = substr(@$item->task_creation_date,8,2).'/'.substr(@$item->task_creation_date,5,2);
			
			$destination_date = '';
			if(@$item->destination_date != '0000-00-00')
				$destination_date = substr(@$item->destination_date,8,2).'/'.substr(@$item->destination_date,5,2);
			
			$progress_status_id = @$item->id_progress_status;
			
			if($item->image1 != '' && $item->is_appears_img1 === 1) {
				$description.= '<br/><br/><strong>ראה צילום/סקיצה מצורפת</strong> ';
			}
			
			if($item->image1 != '' && $item->is_appears_img1 === 1) {
				array_push($counts_array,$count1);
				array_push($images_array,$item->image1);
				array_push($is_appears_img_array,$item->is_appears_img1);
				array_push($chapters_array,$item->chapter_name);
				$description.= '<strong>'.toAlpha(sizeof($counts_array)-1).' </strong>';
			}
			
			$updated_date = @$item->updated_date;
			
			$area_bg_color = 'background-color:white';
			
			$subject_bg_color = 'background-color:white';
		
			$description_bg_color = 'background-color:white';
			
			$query = $mysqli->prepare("SELECT * FROM dne_tasks WHERE id = ?");
			$query->bind_param("i",$item->id_task);
			$query->execute();
			$query->store_result();
			$query = fetch_unique($query);
			$task = @$query->name;
			$task_color = @$query->color;
			$task_bgcolor = @$query->bgcolor;
			
			$query = $mysqli->prepare("SELECT * FROM dne_responsibles WHERE id = ?");
			$query->bind_param("i",$item->id_responsible);
			$query->execute();
			$query->store_result();
			$query = fetch_unique($query);
			$responsible = @$query->name;
			$responsible_color = @$query->color;
			$responsible_bgcolor = @$query->bgcolor;
			
			$query = $mysqli->prepare("SELECT * FROM dne_responsibles WHERE id = ?");
			$query->bind_param("i",$item->id_pass_on);
			$query->execute();
			$query->store_result();
			$query = fetch_unique($query);
			$pass_on = @$query->name;
			$pass_on_bg_color = 'background-color:white';
			
			$query = $mysqli->prepare("SELECT * FROM dne_progress_status WHERE id = ?");
			$query->bind_param("i",$item->id_progress_status);
			$query->execute();
			$query->store_result();
			$query = fetch_unique($query);
			$progress_status = @$query->name;
			$progress_status_color = @$query->color;
			$progress_status_bgcolor = @$query->bgcolor;
			
			$task_creation_date_bg_color = 'background-color:white';
			
			$dest_date_color = 'color:black';
			$dest_date_bg_color = 'background-color:white';
			
			if(@$item->destination_date < date('Y-m-d')) { 
			   $dest_date_color = 'color:red;';
			}
			
			if($is_change_row_style === 1) {
				if($progress_status == 'בוצע/נמסר') {
				   $subject_bg_color = 'background-color:#dedede';
				   $area_bg_color = 'background-color:#dedede';
				   $description_bg_color = 'background-color:#dedede';
				   $task_bgcolor = '#dedede';
				   $responsible_bgcolor = '#dedede';
				   $pass_on_bg_color = 'background-color:#dedede';
				   $task_creation_date_bg_color = 'background-color:#dedede';
				   $dest_date_color = 'color:#dedede';
				   $dest_date_bg_color = 'background-color:#dedede';
				   $progress_status_bgcolor = '#dedede';
				}
				else if($task == 'בקרת איכות') {
				   $subject_bg_color = 'background-color:#fafd49';
				   $area_bg_color = 'background-color:#fafd49';
				   $description_bg_color = 'background-color:#fafd49';
				}
				else 
					$dest_date_color = 'color:white';
			}
														
			$html1_body.='<tr style="font-size:11px;">';	
            $html1_body.='<td width="30" style="text-align:center;border:1px solid black;">'.@$count1.'</td>';			
			$html1_body.='<td width="90" style="text-align:right;padding-right:5px;'.@$subject_bg_color.';border:1px solid black;">'.@$subject.'</td>';	
			$html1_body.='<td width="90" style="text-align:right;padding-right:5px;'.@$area_bg_color.';border:1px solid black;">'.@$area.'</td>';
			$html1_body.='<td width="320" style="text-align:right;padding-right:5px;'.@$description_bg_color.';border:1px solid black;">'.@$description.'</td>';
			$html1_body.='<td width="90" style="text-align:center;color:'.@$task_color.';background-color:'.@$task_bgcolor.';border:1px solid black;"><strong>'.@$task.'</strong></td>';
			$html1_body.='<td width="80" style="text-align:center;color:'.@$responsible_color.';background-color:'.@$responsible_bgcolor.';border:1px solid black;"><strong>'.@$responsible.'</strong></td>';
			$html1_body.='<td width="90" style="text-align:center;border:1px solid black;'.@$pass_on_bg_color.'"><strong>'.@$pass_on.'</strong></td>';
			$html1_body.='<td width="50" style="text-align:center;border:1px solid black;font-size:10px;'.$task_creation_date_bg_color.'">'.@$task_creation_date.'</td>';
			$html1_body.='<td width="50" style="text-align:center;border:1px solid black;font-size:10px;'.@$dest_date_color.';'.$dest_date_bg_color.'"><strong>'.@$destination_date.'</strong></td>';
			$html1_body.='<td width="85" style="text-align:center;color:'.@$progress_status_color.';background-color:'.@$progress_status_bgcolor.';border:1px solid black;"><strong>'.@$progress_status.'</strong></td>';
			$html1_body.='</tr>';
		}
	}
}
							
$html1_body.='</table></td></tr></table>';
$html1_body.='</div>';
$html1_body.='</div>';

$html1 = $html_header.$html1_body;

$pdf->setRTL(true);
$pdf->AddPage();
$pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));
$pdf->writeHTMLCell(0, 0, '', '', $html1, 0, 1, 0, true, '', true);

$count2 = -1;
for($i=0;$i<sizeof($images_array);$i++) {
    if($is_appears_img_array[$i] == 1) {
	   $count2++;	   
       $html2_body.= '<div>'.toAlpha($count2).' - '.@$chapters_array[$i].' - '.@$counts_array[$i].'<br/><br/><img src="uploads/'.@$images_array[$i].'" height="200" style="object-fit:fixed;" /></div>';
	}		
}

$html2 = $html_header.$html2_body;

$pdf->setRTL(false);
$pdf->AddPage();
$pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));
$pdf->writeHTMLCell(0, 0, '', '', $html2, 0, 1, 0, true, '', true);

ob_end_clean();
$pdf_name = substr(date('Y-m-d'),0,4).substr(date('Y-m-d'),5,2).substr(date('Y-m-d'),8,2).'- New tasks Report-'.$project->nickname.'.pdf';
$pdf->Output($pdf_name,'I');
?>