<?php
session_start();
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

$query = $mysqli->prepare("SELECT * FROM dne_rdv WHERE id = ?");
$query->bind_param("i",$_SESSION['id_rdv']);
$query->execute();
$query->store_result();
$rdv = fetch_unique($query);

$rdv_persons_array = explode(',',@$rdv->rdv_persons);

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
$title = $project->name_he.'<br/>'.@$rdv->rdv_name.'<br/>'.substr(@$rdv->rdv_date,8,2).'/'.substr(@$rdv->rdv_date,5,2).'/'.substr(@$rdv->rdv_date,0,4);

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
$pdf->setPrintFooter(false);

$html_header = '<table><tr><td style="text-align:center;"><img src="images/davidnahmias_stripe.jpg" /><br/><br/></td></tr>';

$html1_body = '<tr style="font-size:16px;"><td width="40px;">&nbsp;</td><td style="text-align:center;"><span dir="'.$dir_table.'"><strong><u>'.$title.'</u></strong></span></td></tr></table>';
$html1_body.= '<div class="row"><div class="col-md-12">';
$html1_body.= '<strong><u>נוכחים:</u></strong>';
$html1_body.= '</div></div>';

$resp_emails_array = array();
foreach($rdv_persons_array as $item) {
   $query = $mysqli->prepare("SELECT r.name AS name,r.email AS email,s.name_he AS sup_name_he
                             FROM dne_responsibles r
                             LEFT JOIN dne_projects_suppliers ps ON ps.id = r.id_projects_suppliers
                             LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id							 
							 WHERE r.id = ?");
   $query->bind_param("i",$item);
   $query->execute();
   $query->store_result();
   $responsible = fetch_unique($query);
   array_push($resp_emails_array,$responsible->email);
   $html1_body.= $responsible->sup_name_he.' - '.@$responsible->name.'<br/>';
}

$html1_body.= '<div class="col-md-12">';
$html1_body.= '<table dir="'.$dir_table.'" style="'.$style_table.'"><tr><td width="4%">&nbsp;</td><td><table cellpadding="4">';
$html1_body.='<tr style="background-color:silver;font-size:13px;">';
$html1_body.='<th width="30" style="text-align:center;border:1px solid black;font-size:11px;font-weight:bold;">&#x2116;</th>';
$html1_body.='<th width="90" style="text-align:center;border:1px solid black;font-size:11px;font-weight:bold;">נושא/תחום</th>';
$html1_body.='<th width="90" style="text-align:center;border:1px solid black;font-size:11px;font-weight:bold;">איזור/נושא</th>';
$html1_body.='<th width="320" style="text-align:center;border:1px solid black;font-size:11px;font-weight:bold;">תיאור</th>';
$html1_body.='<th width="90" style="text-align:center;border:1px solid black;font-size:11px;font-weight:bold;">סוג משימה</th>';
$html1_body.='<th width="80" style="text-align:center;border:1px solid black;font-size:11px;font-weight:bold;">אחראי</th>';
$html1_body.='<th width="90" style="text-align:center;border:1px solid black;font-size:11px;font-weight:bold;">להעביר ל/ <br/> לאשר מול</th>';
$html1_body.='<th width="50" style="text-align:center;border:1px solid black;font-size:10px;font-weight:bold;">יצירת <br/> משימה</th>';
$html1_body.='<th width="50" style="text-align:center;border:1px solid black;font-size:10px;">תאריך <br/> יעד</th>';
$html1_body.='<th width="85" style="text-align:center;border:1px solid black;font-size:11px;font-weight:bold;">סטטוס <br/> התקדמות</th>';
$html1_body.='</tr>';

$query = $mysqli->prepare("SELECT * FROM dne_chapters WHERE id_project = ? ORDER BY id_display");
$query->bind_param("i",$project_id);
$query->execute(); 
$query->store_result();
$chapters = fetch($query);

foreach($chapters as $item) {	
	$chapter_id = $item->id;
	$is_appears = 1;
	$is_pdf_appears = 1;
								
	$query = $mysqli->prepare("SELECT m.id AS id,m.subject AS subject,m.id_rdv AS id_rdv,m.id_area AS id_area,m.description AS description,m.id_task AS id_task,m.id_responsible AS id_responsible,
	                          m.id_pass_on AS id_pass_on,m.is_change_row_style AS is_change_row_style,m.task_creation_date AS task_creation_date,
							  m.destination_date AS destination_date,m.id_progress_status AS id_progress_status,m.updated_date AS updated_date,
							  c.name AS chapter_name
	                          FROM dne_meetings m
							  LEFT JOIN dne_chapters c ON m.id_chapter = c.id
							  LEFT JOIN dne_tasks t ON m.id_task = t.id
							  WHERE m.id_project = ? AND m.id_chapter = ? AND m.is_appears = ? AND m.is_pdf_appears = ? AND m.id_rdv = ?
							  ORDER BY t.id_display,m.subject,m.id_area,m.destination_date DESC");
	$query->bind_param("iiiii",$project_id,$chapter_id,$is_appears,$is_pdf_appears,$_SESSION['id_rdv']);
	$query->execute(); 
	$query->store_result();
	$meetings_num_rows = $query->num_rows;
	$meetings = fetch($query);
	
	if($meetings_num_rows > 0) {
		$html1_body.='<tr style="background-color:#a3def0;font-size:11px;">';
		$html1_body.='<td colspan="12" style="text-align:right;padding-right:5px;border:1px solid black;"><strong>'.@$item->name.'</strong></td>';
		$html1_body.='</tr>';
		
		$count1 = 0;
		foreach($meetings as $item) {
			$count1++;
			
			$meeting_id = @$item->id;
			$id_rdv = @$item->id_rdv;
			$subject = @$item->subject;
			$area_id = @$item->id_area;
			$description = @$item->description;
			$task_id = @$item->id_task;
			$responsible_id = @$item->id_responsible;
			$pass_on_id = @$item->id_pass_on;
			$is_change_row_style = @$item->is_change_row_style;
			
			$color_num = 'black';
			if(@$item->is_appears_img1 != 0 || @$item->is_appears_img1)
			   $color_num = 'green';
			
			$update_cell_bg_color = 'background-color:white';
			
			$task_creation_date = '';
			if(@$item->task_creation_date != '0000-00-00')
				$task_creation_date = substr(@$item->task_creation_date,8,2).'/'.substr(@$item->task_creation_date,5,2);
			
			$destination_date = '';
			if(@$item->destination_date != '0000-00-00')
				$destination_date = substr(@$item->destination_date,8,2).'/'.substr(@$item->destination_date,5,2);
			
			$progress_status_id = @$item->id_progress_status;
			
			$updated_date = @$item->updated_date;
			
			$subject_bg_color = 'background-color:white';
			
			$query = $mysqli->prepare("SELECT * FROM dne_areas WHERE id = ?");
			$query->bind_param("i",$item->id_area);
			$query->execute();
			$query->store_result();
			$query = fetch_unique($query);
			$area = @$query->name;
			$area_bg_color = 'background-color:white';
			
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
			
			$task_creation_date_color = 'color:black';
			if($id_rdv > 0)
			  $task_creation_date_color = 'color:green';
			
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
			
			if(($last_pdf_date <= $updated_date) && ($progress_status != 'בוצע/נמסר')) {
				$update_cell_bg_color = 'background-color:'.$project->bgcolor_new_task;
				$subject_bg_color = 'background-color:'.$project->bgcolor_new_task;
				$area_bg_color = 'background-color:'.$project->bgcolor_new_task;
				$description_bg_color = 'background-color:'.$project->bgcolor_new_task;
				$pass_on_bg_color = 'background-color:'.$project->bgcolor_new_task;
				$task_creation_date_bg_color = 'background-color:'.$project->bgcolor_new_task;
				$dest_date_bg_color = 'background-color:'.$project->bgcolor_new_task;
			}
														
			$html1_body.='<tr style="font-size:11px;">';	
            $html1_body.='<td width="30" style="text-align:center;'.@$update_cell_bg_color.';border:1px solid black;color:'.$color_num.'">'.@$count1.'</td>';
			$html1_body.='<td width="90" style="text-align:right;padding-right:5px;'.@$subject_bg_color.';border:1px solid black;">'.@$subject.'</td>';	
			$html1_body.='<td width="90" style="text-align:right;padding-right:5px;'.@$area_bg_color.';border:1px solid black;">'.@$area.'</td>';
			$html1_body.='<td width="320" style="text-align:right;padding-right:5px;'.@$description_bg_color.';border:1px solid black;">'.@$description.'</td>';
			$html1_body.='<td width="90" style="text-align:center;color:'.@$task_color.';background-color:'.@$task_bgcolor.';border:1px solid black;"><strong>'.@$task.'</strong></td>';			
			$html1_body.='<td width="80" style="text-align:center;color:'.@$responsible_color.';background-color:'.@$responsible_bgcolor.';border:1px solid black;"><strong>'.@$responsible.'</strong></td>';
			$html1_body.='<td width="90" style="text-align:center;border:1px solid black;'.@$pass_on_bg_color.'"><strong>'.@$pass_on.'</strong></td>';
			$html1_body.='<td width="50" style="text-align:center;border:1px solid black;font-size:10px;'.$task_creation_date_color.';'.$task_creation_date_bg_color.'">'.@$task_creation_date.'</td>';
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

$html2_body = '<tr style="font-size:16px;"><td width="40px;">&nbsp;</td><td style="text-align:center;"><span dir="'.$dir_table.'"><strong><u>'.$project->name_he.'<br/>דו\'ח סטטוס פרוייקט <br/>'.substr(date('Y-m-d'),8,2).'/'.substr(date('Y-m-d'),5,2).'/'.substr(date('Y-m-d'),0,4).'</u></strong></span></td></tr></table>';
$html2_body.= '<div class="row">';
$html2_body.= '<div class="col-md-12">';
$html2_body.= '<table dir="'.$dir_table.'" style="'.$style_table.'"><tr><td width="4%">&nbsp;</td><td><table cellpadding="4">';
$html2_body.='<tr style="background-color:silver;font-size:13px;">';
$html2_body.='<th width="30" style="text-align:center;border:1px solid black;font-size:11px;font-weight:bold;">&#x2116;</th>';
$html2_body.='<th width="90" style="text-align:center;border:1px solid black;font-size:11px;font-weight:bold;">נושא/תחום</th>';
$html2_body.='<th width="90" style="text-align:center;border:1px solid black;font-size:11px;font-weight:bold;">איזור/נושא</th>';
$html2_body.='<th width="320" style="text-align:center;border:1px solid black;font-size:11px;font-weight:bold;">תיאור</th>';
$html2_body.='<th width="90" style="text-align:center;border:1px solid black;font-size:11px;font-weight:bold;">סוג משימה</th>';
$html2_body.='<th width="80" style="text-align:center;border:1px solid black;font-size:11px;font-weight:bold;">אחראי</th>';
$html2_body.='<th width="90" style="text-align:center;border:1px solid black;font-size:11px;font-weight:bold;">להעביר ל/ <br/> לאשר מול</th>';
$html2_body.='<th width="50" style="text-align:center;border:1px solid black;font-size:10px;font-weight:bold;">יצירת <br/> משימה</th>';
$html2_body.='<th width="50" style="text-align:center;border:1px solid black;font-size:10px;">תאריך <br/> יעד</th>';
$html2_body.='<th width="85" style="text-align:center;border:1px solid black;font-size:11px;font-weight:bold;">סטטוס <br/> התקדמות</th>';
$html2_body.='</tr>';

$query = $mysqli->prepare("SELECT * FROM dne_chapters WHERE id_project = ? ORDER BY id_display");
$query->bind_param("i",$project_id);
$query->execute(); 
$query->store_result();
$chapters = fetch($query);

$is_html2_appears = false;

foreach($chapters as $item) {	
	$chapter_id = $item->id;
	$is_appears = 1;
	$is_pdf_appears = 1;
	
	$query = $mysqli->prepare("SELECT m.id AS id,m.subject AS subject,m.id_rdv AS id_rdv,m.id_area AS id_area,m.description AS description,m.id_task AS id_task,m.id_responsible AS id_responsible,
	                          m.id_pass_on AS id_pass_on,m.is_change_row_style AS is_change_row_style,m.task_creation_date AS task_creation_date,
							  m.destination_date AS destination_date,m.id_progress_status AS id_progress_status,m.image1 AS image1,m.is_appears_img1 AS is_appears_img1,
							  m.image2 AS image2,m.is_appears_img2 AS is_appears_img2,m.updated_date AS updated_date,
							  c.name AS chapter_name
	                          FROM dne_meetings m
							  LEFT JOIN dne_chapters c ON m.id_chapter = c.id
							  LEFT JOIN dne_tasks t ON m.id_task = t.id
							  WHERE m.id_project = ? AND m.id_chapter = ? AND m.is_appears = ? AND m.is_pdf_appears = ? AND is_appears_img1 = 1 AND image1 <> ''
							  AND m.id_rdv = ?
							  ORDER BY t.id_display,m.subject,m.id_area,m.destination_date DESC");
	$query->bind_param("iiiii",$project_id,$chapter_id,$is_appears,$is_pdf_appears,$_SESSION['id_rdv']);
	$query->execute(); 
	$query->store_result();
	$meetings_with_image_num_rows = $query->num_rows;
	$meetings_with_image = fetch($query);
	
	if($meetings_with_image_num_rows > 0) 
	   $is_html2_appears = true;
   
	if($meetings_with_image_num_rows > 0) {
		$html2_body.='<tr style="background-color:#a3def0;font-size:11px;">';
		$html2_body.='<td colspan="12" style="text-align:right;padding-right:5px;border:1px solid black;"><strong>'.@$item->name.'</strong></td>';
		$html2_body.='</tr>';
		
		$count2= 0;
		foreach($meetings_with_image as $item) {
			$count2++;
			
			$meeting_id = @$item->id;
			$id_rdv = @$item->id_rdv;
			$subject = @$item->subject;
			$area_id = @$item->id_area;
			$description = @$item->description;
			$task_id = @$item->id_task;
			$responsible_id = @$item->id_responsible;
			$pass_on_id = @$item->id_pass_on;
			$is_change_row_style = @$item->is_change_row_style;
			
			$color_num = 'black';
			if(@$item->is_appears_img1 != 0 || @$item->is_appears_img1)
			   $color_num = 'green';
			
			$update_cell_bg_color = 'background-color:white';
			
			$task_creation_date = '';
			if(@$item->task_creation_date != '0000-00-00')
				$task_creation_date = substr(@$item->task_creation_date,8,2).'/'.substr(@$item->task_creation_date,5,2);
			
			$destination_date = '';
			if(@$item->destination_date != '0000-00-00')
				$destination_date = substr(@$item->destination_date,8,2).'/'.substr(@$item->destination_date,5,2);
			
			$progress_status_id = @$item->id_progress_status;
			
			$updated_date = @$item->updated_date;
			
			$subject_bg_color = 'background-color:white';
			
			$query = $mysqli->prepare("SELECT * FROM dne_areas WHERE id = ?");
			$query->bind_param("i",$item->id_area);
			$query->execute();
			$query->store_result();
			$query = fetch_unique($query);
			$area = @$query->name;
			$area_bg_color = 'background-color:white';
			
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
			
			$task_creation_date_color = 'color:black';
			if($id_rdv > 0) 
				$task_creation_date_color = 'color:green';
			
			$task_creation_date_bg_color = 'background-color:white';
			
			$dest_date_color = 'color:black';
			$dest_date_bg_color = 'background-color:white';
			
			if(@$item->destination_date < date('Y-m-d')) { 
			   $dest_date_color = 'color:red;';
			}
			
			$image1 = @$item->image1;
			
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
			
			if(($last_pdf_date <= $updated_date) && ($progress_status != 'בוצע/נמסר')) {
				$update_cell_bg_color = 'background-color:'.$project->bgcolor_new_task;
				$subject_bg_color = 'background-color:'.$project->bgcolor_new_task;
				$area_bg_color = 'background-color:'.$project->bgcolor_new_task;
				$description_bg_color = 'background-color:'.$project->bgcolor_new_task;
				$pass_on_bg_color = 'background-color:'.$project->bgcolor_new_task;
				$task_creation_date_bg_color = 'background-color:'.$project->bgcolor_new_task;
				$dest_date_bg_color = 'background-color:'.$project->bgcolor_new_task;
			}
														
			$html2_body.='<tr style="font-size:11px;">';	
            $html2_body.='<td width="30" style="text-align:center;'.@$update_cell_bg_color.';border:1px solid black;color:'.$color_num.'">'.@$count2.'</td>';			
			$html2_body.='<td width="90" style="text-align:right;padding-right:5px;'.@$subject_bg_color.';border:1px solid black;">'.@$subject.'</td>';	
			$html2_body.='<td width="90" style="text-align:right;padding-right:5px;'.@$area_bg_color.';border:1px solid black;">'.@$area.'</td>';
			$html2_body.='<td width="320" style="text-align:right;padding-right:5px;'.@$description_bg_color.';border:1px solid black;">'.@$description.'</td>';
			$html2_body.='<td width="90" style="text-align:center;color:'.@$task_color.';background-color:'.@$task_bgcolor.';border:1px solid black;"><strong>'.@$task.'</strong></td>';
			$html2_body.='<td width="80" style="text-align:center;color:'.@$responsible_color.';background-color:'.@$responsible_bgcolor.';border:1px solid black;"><strong>'.@$responsible.'</strong></td>';
			$html2_body.='<td width="90" style="text-align:center;border:1px solid black;'.@$pass_on_bg_color.'"><strong>'.@$pass_on.'</strong></td>';
			$html2_body.='<td width="50" style="text-align:center;border:1px solid black;font-size:10px;'.$task_creation_date_color.';'.$task_creation_date_bg_color.'">'.@$task_creation_date.'</td>';
			$html2_body.='<td width="50" style="text-align:center;border:1px solid black;font-size:10px;'.@$dest_date_color.';'.$dest_date_bg_color.'"><strong>'.@$destination_date.'</strong></td>';
			$html2_body.='<td width="85" style="text-align:center;color:'.@$progress_status_color.';background-color:'.@$progress_status_bgcolor.';border:1px solid black;"><strong>'.@$progress_status.'</strong></td>';
			$html2_body.='</tr>'; 
			$html2_body.='<tr><td colspan="10"><img src="uploads/'.@$image1.'" height="200" width="200" style="object-fit:fixed;" /></td></tr>';
		}
	}
}
							
$html2_body.='</table></td></tr></table>';
$html2_body.='</div>';
$html2_body.='</div>';

if($is_html2_appears) {
    $pdf->writeHTMLCell(0, 0, '', '', $html2, 0, 1, 0, true, '', true);
    $html2 = $html_header.$html2_body;
    $pdf->setRTL(true);
    $pdf->AddPage();
    $pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));
}

ob_end_clean();
$pdf_name = substr(date('Y-m-d'),0,4).substr(date('Y-m-d'),5,2).substr(date('Y-m-d'),8,2).'-Tasks Report-'.$project->nickname.'.pdf';
$pdf_content = $pdf->Output($pdf_name,'S');

$to = 'jnahmias83@gmail.com';
$from = 'david@nahmias-eng.com';
$subject = 'סיכום ישיבה';

// Set the headers
$headers = "From: $from\r\n";
$headers .= "Reply-To: $from\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: multipart/mixed; boundary=\"boundary\"\r\n";

// Construct the email body
$body = "--boundary\r\n";
$body .= "Content-Type: text/plain; charset=UTF-8\r\n";
$body .= "Content-Transfer-Encoding: 7bit\r\n";
$body .= "\r\n";
$body .= "סיכום ישיבה";
$body .= "\r\n";
$body .= "--boundary\r\n";
$body .= "Content-Type: application/pdf; name=\"$pdf_name\"\r\n";
$body .= "Content-Disposition: attachment; filename=\"$pdf_name\"\r\n";
$body .= "Content-Transfer-Encoding: base64\r\n";
$body .= "\r\n";
$body .= chunk_split(base64_encode($pdf_content));
$body .= "\r\n";
$body .= "--boundary--";

$mail_sent = mail($to,$subject,$body,$headers);

for($i=0;$i<sizeof($resp_emails_array);$i++) {
	if($resp_emails_array[$i] != '')
	   mail($resp_emails_array[$i],$subject,$body,$headers);
}
?>