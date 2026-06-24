<?php
require_once('tcpdf_min/config/tcpdf_config.php');
require_once('tcpdf_min/tcpdf.php');
session_start();
include 'functions/functions.php';
error_reporting(E_ALL);
ini_set('display_errors', '1');

$project_id = @$_GET['project_id'];
$is_specific_filter = @$_GET['is_specific_filter'];
$all_ids_to_print = @$_GET['all_ids_to_print'];
$pdf_direction = @$_GET['pdf_direction'];
$sort_select_1 = @$_GET['sort_select_1'];
$sort_select_2 = @$_GET['sort_select_2'];
$sort_select_3 = @$_GET['sort_select_3'];
$all_ids_to_print_array = explode(',',$all_ids_to_print);

$query = $mysqli->prepare("SELECT * FROM dne_projects WHERE id = ?");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$project = fetch_unique($query);

$query = $mysqli->prepare("SELECT logo_stread FROM dne_logos");
$query->execute();
$query->store_result();
$logo = fetch_unique($query);

$query = $mysqli->prepare("SELECT * FROM dne_global_bgcolor_new_task LIMIT 1");
$query->execute();
$query->store_result();
$global_bgcolor_new_task = fetch_unique($query);

$query = $mysqli->prepare("SELECT * FROM dne_log_current_report 
                          WHERE id_project = ?");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$log_current_report = fetch_unique($query);
$id_custom_report = $log_current_report->id_custom_report;
$id_rdv_report = $log_current_report->id_rdv_report;

if($id_rdv_report > 0) { 
  $archive_label = 'ארכיון';
  $query = "SELECT id FROM dne_progress_status 
            WHERE name_he = ? 
            AND id_project = ?";
  $query = $mysqli->prepare($query);
  $query->bind_param('si',$archive_label,$project_id);   
  $query->execute();
  $query->store_result();
  $query = fetch_unique($query);
  $ps_archive_id = $query->id;
  
  $query = $mysqli->prepare("SELECT rdv_lang FROM dne_rdv WHERE id = ?");
  $query->bind_param("i",$id_rdv_report);
  $query->execute();
  $query->store_result();
  $query = fetch_unique($query);
  $rdv_lang = @$query->rdv_lang;
}

if($id_custom_report > 0) {
	$query = $mysqli->prepare("SELECT * FROM dne_custom_reports
       	                      WHERE id = ?");
	$query->bind_param("i",$id_custom_report);
	$query->execute();
	$query->store_result();
		
	if($query->num_rows > 0) {
		$custom_report = fetch_unique($query);
		if(!$is_specific_filter) {
			$sql = @$custom_report->sql_str;
			$is_images = @$custom_report->is_images;
			$is_colors = @$custom_report->is_colors;
			$lang = @$custom_report->lang ?: @$project->lang;
			$_SESSION['lang'] = $lang;
			$columns_list = @$custom_report->columns_list;
			$period_new_tasks = @$custom_report->period_new_tasks;
		}
		else {
			$sql = @$_SESSION['filter_sql'];
			$is_images = @$_SESSION['filter_is_images'];
			$is_colors = @$_SESSION['filter_is_colors'];
			$lang = @$_SESSION['filter_lang'] ?: @$project->lang;
			$_SESSION['lang'] = $lang;
			$columns_list = @$_SESSION['filter_columns_list'];
			$period_new_tasks = @$_SESSION['filter_period_new_tasks'];
		}
	
		$id_responsibles_part = '';
		if(strpos($sql,"m.id_responsible")!== false) {
			$sql_array = explode(' AND ',$sql);
									
			for($i=1;$i<sizeof($sql_array);$i++) {
				if(strpos($sql_array[$i],'m.id_responsible') !== false) {
					$id_responsibles_part = $sql_array[$i];
					$id_responsibles_part = str_replace('m.id_responsible IN(','',$id_responsibles_part);
					$id_responsibles_part = substr($id_responsibles_part, 0, -1);
				}
			}
			
			$id_responsibles_part_array = explode('OR',$id_responsibles_part);
			$id_responsibles_part = $id_responsibles_part_array[0];
			$id_responsibles_part = str_replace('(','', $id_responsibles_part);
			$id_responsibles_part = str_replace(')','', $id_responsibles_part);
		}

		$id_progress_status_part = '';
		$id_progress_status_array = array();

		if(strpos($sql,"m.id_progress_status")!== false) {
			$sql_array = explode(' AND ',$sql);

			for($i=1;$i<sizeof($sql_array);$i++) {
				if(strpos($sql_array[$i],'m.id_progress_status') !== false) {
					$id_progress_status_part = $sql_array[$i];
					$id_progress_status_part = str_replace('m.id_progress_status IN(','',$id_progress_status_part);
					$id_progress_status_part = substr($id_progress_status_part, 0, -1);
					$id_progress_status_array = explode(',',$id_progress_status_part);
				}
			}
				
			for($i=0;$i<sizeof($id_progress_status_array);$i++) {
				$query = $mysqli->prepare("SELECT name_he 
				                          FROM dne_progress_status 
										  WHERE id = ?");
				$query->bind_param("i",$id_progress_status_array[$i]);
				$query->execute();
				$query->store_result();
				$query = fetch_unique($query);
				
				if($query->name_he == 'ארכיון' && sizeof($id_progress_status_array) == 1) 
				   $sql = str_replace('is_appears = 1','is_appears = 0',$sql);
				else if($query->name_he == 'ארכיון' && sizeof($id_progress_status_array) > 1) 
				   $sql = str_replace('is_appears = 1','is_appears IN(0,1)',$sql);
		   }
		}	
				
		$query = $mysqli->prepare($sql);
		$query->execute();
		$query->store_result();
		$all_meetings_num_rows = $query->num_rows;
		
		$query = $mysqli->prepare($sql." AND m.image1 <> '' 
		                          AND m.is_appears_img1 = 1 
								  ORDER BY t.id_display,
								  m.subject,m.id_area,
								  m.destination_date DESC");
		$query->execute(); 
		$query->store_result();
		$all_meetings_with_images_num_rows = $query->num_rows;
	
		$report_type = 'general_'.$id_custom_report;

		$_SESSION['id_responsibles_part'] = $id_responsibles_part;	
   }
}  
else if($id_rdv_report > 0){
    if(!@$is_specific_filter){
	    $query = $mysqli->prepare("SELECT * FROM dne_rdv WHERE id = ?");
        $query->bind_param("i",$id_rdv_report);
        $query->execute();
        $query->store_result();
        $query = fetch_unique($query);
       
		$is_images = @$query->is_images;
	    $is_colors = @$query->is_colors;
	    $lang = @$query->rdv_lang ?: @$project->lang;
		$period_new_tasks = @$query->period_new_tasks;
		$columns_list = @$query->columns_list;
  
	    $is_appears = 1;
	    $query = $mysqli->prepare("SELECT * FROM dne_meetings 
		                          WHERE is_appears = ? 
								  AND id_progress_status <> ?
								  AND FIND_IN_SET(?,ids_rdv) > 0");
	    $query->bind_param("iis",$is_appears,$ps_archive_id,$id_rdv_report);
	    $query->execute();
	    $query->store_result();
	    $all_meetings_num_rows = $query->num_rows;
    
	    $query = $mysqli->prepare("SELECT * FROM dne_meetings 
		                          WHERE is_appears = ? 
								  AND FIND_IN_SET(?,ids_rdv) > 0 
								  AND image1 <> '' 
								  AND is_appears_img1 = 1");
	    $query->bind_param("ii",$is_appears,$id_rdv_report);
	    $query->execute();
	    $query->store_result();
	    $all_meetings_with_images_num_rows = $query->num_rows;
    }
	else {
		$sql = @$_SESSION['filter_sql'];
		$is_images = @$_SESSION['filter_is_images'];
		$is_colors = @$_SESSION['filter_is_colors'];
		$lang = @$_SESSION['filter_lang'] ?: @$project->lang;
		$period_new_tasks = @$_SESSION['filter_period_new_tasks'];
		$columns_list = @$_SESSION['filter_columns_list'];

		$id_responsibles_part = '';
		if(strpos($sql,"m.id_responsible")!== false) {
			$sql_array = explode(' AND ',$sql);

			for($i=1;$i<sizeof($sql_array);$i++) {
				if(strpos($sql_array[$i],'m.id_responsible') !== false) {
					$id_responsibles_part = $sql_array[$i];
					$id_responsibles_part = str_replace('m.id_responsible IN(','',$id_responsibles_part);
					$id_responsibles_part = substr($id_responsibles_part, 0, -1);
				}
			}
			
			$id_responsibles_part_array = explode('OR',$id_responsibles_part);
			$id_responsibles_part = $id_responsibles_part_array[0];
			$id_responsibles_part = str_replace('(','', $id_responsibles_part);
			$id_responsibles_part = str_replace(')','', $id_responsibles_part);
		}

		$id_progress_status_part = '';
		$id_progress_status_array = array();

		if(strpos($sql,"m.id_progress_status")!== false) {
			$sql_array = explode(' AND ',$sql);

			for($i=1;$i<sizeof($sql_array);$i++) {
				if(strpos($sql_array[$i],'m.id_progress_status') !== false) {
					$id_progress_status_part = $sql_array[$i];
					$id_progress_status_part = str_replace('m.id_progress_status IN(','',$id_progress_status_part);
					$id_progress_status_part = substr($id_progress_status_part, 0, -1);
					$id_progress_status_array = explode(',',$id_progress_status_part);
				}
			}
				
			for($i=0;$i<sizeof($id_progress_status_array);$i++) {
				$query = $mysqli->prepare("SELECT name_he 
				                          FROM dne_progress_status 
										  WHERE id = ?");
				$query->bind_param("i",$id_progress_status_array[$i]);
				$query->execute();
				$query->store_result();
				$query = fetch_unique($query);
				
				if($query->name_he == 'ארכיון' && sizeof($id_progress_status_array) == 1) 
				   $sql = str_replace('is_appears = 1','is_appears = 0',$sql);
				else if($query->name_he == 'ארכיון' && sizeof($id_progress_status_array) > 1) 
				   $sql = str_replace('is_appears = 1','is_appears IN(0,1)',$sql);
		   }
		}
		
		$query = $mysqli->prepare($sql);
		$query->execute();
		$query->store_result();
		$all_meetings_num_rows = $query->num_rows;
		
		$query = $mysqli->prepare($sql." AND m.image1 <> '' 
		                          AND m.is_appears_img1 = 1 
								  ORDER BY t.id_display,m.subject,
								  m.id_area,m.destination_date DESC");
		$query->execute(); 
		$query->store_result();
		$all_meetings_with_images_num_rows = $query->num_rows;
	}
	
	$query = $mysqli->prepare("SELECT rdv.rdv_name AS rdv_name,
	                          rdv.rdv_persons AS rdv_persons,
							  mt.name_he AS meeting_name_he 
                              FROM dne_rdv rdv 
							  LEFT JOIN dne_meetings_types mt ON rdv.id_meetings_types = mt.id
							  WHERE rdv.id = ?");
	$query->bind_param("i",$id_rdv_report);
	$query->execute();
	$query->store_result();
	$rdv = fetch_unique($query);
    $rdv_name = @$rdv->rdv_name;
	$meeting_name_he = @$rdv->meeting_name_he;

    $rdv_persons_array = explode(',',@$rdv->rdv_persons);
	
	$resp_sfow_name_he_array = array();
	
	for($i=0;$i<sizeof($rdv_persons_array);$i++) {
		$query = $mysqli->prepare("SELECT s.name AS s_name,
		                          sfow.name_he AS sfow_name_he
								  FROM dne_responsibles r
								  LEFT JOIN dne_projects_suppliers ps ON ps.id = r.id_projects_suppliers
								  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
                                  LEFT JOIN dne_sup_field_of_work sfow ON s.id_field_of_work = sfow.id									 
								  WHERE r.id = ?");
		$query->bind_param("i",$rdv_persons_array[$i]);
		$query->execute();
		$query->store_result();
		$responsible = fetch_unique($query);
		$resp_sfow_name_he_array[@$rdv_persons_array[$i]]= @$responsible->sfow_name_he;
	}
	
	asort($resp_sfow_name_he_array);
	
    $report_type = 'resumeRdv_'.$id_rdv_report;
	
	$_SESSION['lang'] = $lang;
}

if(@$_GET['lang'] != '')
	$lang = @$_GET['lang'];

if(empty($lang))
	$lang = @$project->lang;

$title = $project->name_he;
if($lang != 'HE')
  $title = $project->name;
if($_SESSION['pdf_title1'] != '')
  $title .=	'<br/>'.$_SESSION['pdf_title1'];
if($_SESSION['pdf_title2'] != '')
  $title .=	'<br/>'.$_SESSION['pdf_title2'];

$title .= '<br/>'.substr($_SESSION['pdf_date'],6,2).'/'.substr($_SESSION['pdf_date'],4,2).'/'.substr($_SESSION['pdf_date'],0,4);  
	$columns_list_array = explode(',',$columns_list);
	if(empty(array_filter($columns_list_array))){
		$columns_list_array = ['subject','area','description','_task','responsible','pass on','task creation','destination date','progress status'];
	}

// Build dynamic ORDER BY based on sort params passed from meetings.php
$_order_parts = [];
foreach ([$sort_select_1, $sort_select_2, $sort_select_3] as $_col) {
	if (empty($_col) || $_col === '0') continue;
	switch ($_col) {
		case 'subject':            $_order_parts[] = "TRIM(REPLACE(m.subject,'&nbsp;',''))"; break;
		case 'area':               $_order_parts[] = "TRIM(REPLACE(m.area,'&nbsp;',''))"; break;
		case 'description':        $_order_parts[] = "m.description"; break;
		case 'responsible':        $_order_parts[] = "r.name"; break;
		case 'task':               $_order_parts[] = (@$lang == 'HE') ? "t.name_he" : "t.name"; break;
		case 'task_creation_date': $_order_parts[] = "m.task_creation_date"; break;
		case 'destination_date':   $_order_parts[] = "m.destination_date"; break;
		case 'status':             $_order_parts[] = (@$lang == 'HE') ? "ps.name_he" : "ps.name"; break;
	}
}
$_order_by = !empty($_order_parts) ? ' ORDER BY '.implode(',',$_order_parts) : ' ORDER BY t.id_display,m.subject,m.id_area,m.destination_date DESC';

$align_txt = 'alignLeft';
$padding_txt = 'paddingLeft10';
												
if(@$lang == 'HE'){
	$dir_table = 'rtl';
    $style_table = "margin-left:1%;";
	$text_align = 'text-align:right';
	$padding = 'padding-right';
	$image_concentration_label = 'ריכוז משימות עם תמונות';
	$participants_label = 'משתתפים';
    $subject_domain_header = 'נושא/תחום';
	$area_subject_header = 'איזור/נושא';
    $description_header = 'תאור';
    $task_type_header = 'משימה';
    $responsible_header = 'אחראי';
    $transfer_confirm_header = 'למסור ל';
    $task_creation_date_header = 'יצירה';
    $destination_date_header = 'יעד';
    $progress_status_header = "סטטוס";
}
else {
	$dir_table = 'ltr';
    $style_table = "margin-right:1%;";
	$text_align = 'text-align:left';
	$padding = 'padding-left';
	$image_concentration_label = 'Concentration of tasks with images';
	$participants_label = 'Participants';
	$subject_domain_header = 'Subject/<br/>Domain';
	$area_subject_header = 'Area/<br/>Subject';
	$description_header = 'Description';
	$task_type_header = 'Task Type';
	$responsible_header = 'Responsible';
	$transfer_confirm_header = 'Transfer To';
	$task_creation_date_header = 'Creation';
	$destination_date_header = 'Target';
	$progress_status_header = "Status";
}
 	
$colspan_image_tr = sizeof($columns_list_array)+1;

$count_width = "30";
$subject_width = "90";
$area_width = "90";
$task_width = "90";
$responsible_width = "80";
$pass_on_width = "90";
$task_creation_width = "60";
$destination_date_width = "60";
$progress_status_width = "85";     
$description_width = '0%';

if(sizeof($columns_list_array) == 1 && in_array('description',$columns_list_array)) {
  if($pdf_direction == 'L')
     $description_width = '97%';
  else if($pdf_direction == 'P')
	$description_width = '96%';
}
else if(sizeof($columns_list_array) == 2 && in_array('description',$columns_list_array)) {
  if($pdf_direction == 'L')
    $description_width = '88%';
  else if($pdf_direction == 'P')
	$description_width = '85%';  
}
else if(sizeof($columns_list_array) == 3 && in_array('description',$columns_list_array)) {
  if($pdf_direction == 'L')
    $description_width = '79%';
  else if($pdf_direction == 'P')
	$description_width = '74%';
}
else if(sizeof($columns_list_array) == 4 && in_array('description',$columns_list_array)) {
  if($pdf_direction == 'L')
    $description_width = '70%';
  else if($pdf_direction == 'P')
	$description_width = '64%';
}
else if(sizeof($columns_list_array) == 5 && in_array('description',$columns_list_array)) {
  if($pdf_direction == 'L')
    $description_width = '62%';
  else if($pdf_direction == 'P')
	$description_width = '53%';
}
else if(sizeof($columns_list_array) == 6 && in_array('description',$columns_list_array)) {
  if($pdf_direction == 'L')
    $description_width = '54%';
  else if($pdf_direction == 'P')
	$description_width = '44%';
}
else if(sizeof($columns_list_array) == 7 && in_array('description',$columns_list_array)) {
  if($pdf_direction == 'L')
    $description_width = '44%';
  else if($pdf_direction == 'P')
	$description_width = '32%';
}
else if(sizeof($columns_list_array) == 8 && in_array('description',$columns_list_array)) {
  if($pdf_direction == 'L')
    $description_width = '39%';
  else if($pdf_direction == 'P')
	$description_width = '22%';
} 
else if(sizeof($columns_list_array) == 9) {
  if($pdf_direction == 'L')
    $description_width = '33%';
  else if($pdf_direction == 'P')
	$description_width = '13%';
}

if(@$pdf_direction == "P") {
	$subject_width = "70";
	$area_width = "72";
	$task_width = "83";
	$responsible_width = "77";
	$pass_on_width = "80";
	$progress_status_width = "75";     
}

class PDF extends TCPDF {
    public function Footer() { 
		$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		
        if ($mysqli->connect_error) {
            die("Connection failed: " . $mysqli->connect_error);
        }
        
		$mysqli->set_charset("utf8");
		
        $query = "SELECT nickname,name,name_he 
		          FROM dne_projects WHERE id =".@$_GET['project_id'];
        $result = $mysqli->query($query);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
			if($_SESSION['lang'] == 'HE')
               $project_name = $row['name_he'];
		    else
			   $project_name = $row['name'];
		   
			$project_nickname = $row['nickname'];
	    } else {
            $project_name = $project_nickname = 'No Data Found';
        }
		
		$pdf_file_name = $_SESSION['pdf_date'].'-';
		
		if($_SESSION['pdf_text1'] != '')
           $pdf_file_name.= $_SESSION['pdf_text1'].'-';
		
		if($_SESSION['pdf_text2'] != '')
           $pdf_file_name.= $_SESSION['pdf_text2'].'-'; 
	   
	    $pdf_file_name.= $project_nickname.'.pdf';
	
		$this->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);  
        $this->SetFont('freesans', '', 10);
        $this->SetY(-25);
		$this->Ln();
        $this->Cell(0, 0, '', 'T');

        $this->SetX($this->lMargin);

        $pagePagination = 'Page ' . $this->getAliasNumPage() . ' of ' . $this->getAliasNbPages();
	    
		if($_GET['pdf_direction'] == 'L') {
			if($_SESSION['lang'] == 'HE') {
				$this->setPrintFooter(true);
				$this->Cell(100, 10, $project_name, 0, 0, 'R');
				$this->Cell(60, 10, $pagePagination, 0, 0, 'C');
				$this->Cell(120, 10, $pdf_file_name, 0, 0, 'L');
			}
			else {
				$this->setPrintFooter(false);
				$this->Cell(120, 10, $project_name, 0, 0, 'L');
				$this->Cell(60, 10, $pagePagination, 0, 0, 'C');
				$this->Cell(100, 10, $pdf_file_name, 0, 0, 'R');
			}
		}
		else if ($_GET['pdf_direction'] == 'P') {
			if($_SESSION['lang'] == 'HE') {
				$this->setPrintFooter(true);
				$this->Cell(50, 10, $project_name, 0, 0, 'R');
				$this->Cell(60, 10, $pagePagination, 0, 0, 'C');
				$this->Cell(85, 10, $pdf_file_name, 0, 0, 'L');
			}
			else {
				$this->setPrintFooter(false);
				$this->Cell(60, 10, $project_name, 0, 0, 'L');
				$this->Cell(85, 10, $pagePagination, 0, 0, 'C');
				$this->Cell(50, 10, $pdf_file_name, 0, 0, 'R');
			}
		}
    }
}

$pdf = new PDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
$pdf->setFooterData(array(0,64,0), array(0,64,128));
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->SetPageOrientation($pdf_direction);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->setFontSubsetting(true);
$pdf->SetAlpha(1);
$pdf->SetTextShadow(['enabled' => false]);
$pdf->SetFont('dejavusans','',12,'',true);
$pdf->SetTextColor(0,0,0);
$pdf->setPrintHeader(false);

$html_header = '<table><tr><td style="text-align:center;"><img src="uploads/'.@$logo->logo_stread.'" /><br/><br/></td></tr>';

$html1_body = '<tr style="font-size:16px;"><td width="40px;">&nbsp;</td><td style="text-align:center;"><span dir="'.$dir_table.'"><strong><u>'.$title.'</u></strong></span></td></tr></table>';

if(@$id_rdv_report > 0) { 
    $html1_body.= '<span>&nbsp;&nbsp;&nbsp;</span><strong style="text-decoration:underline;font-size:12px;">'.@$participants_label.':</strong><br/>';

    $max_rows_per_col = 4;
    $total_items = count($rdv_persons_array);
    $total_columns = ceil($total_items/$max_rows_per_col);

	$col_width = 65/$total_columns;
	$row_count = 0;
	$column_count = 0;

	$html1_body .= '<table cellpadding="5"><tr>';

	foreach ($rdv_persons_array as $index => $item) {
		$query = $mysqli->prepare("SELECT r.name AS name, sfow.name AS sfow_name, sfow.name_he AS sfow_name_he
								  FROM dne_responsibles r
								  LEFT JOIN dne_projects_suppliers ps ON ps.id = r.id_projects_suppliers
								  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
								  LEFT JOIN dne_sup_field_of_work sfow ON s.id_field_of_work = sfow.id
								  WHERE r.id = ?");
		$query->bind_param("i", $item);
		$query->execute();
		$query->store_result();
		$responsible = fetch_unique($query);

		$participants = '';
		if (@$responsible->sfow_name_he == ''){
			if (@$responsible->sfow_name != '')
				$participants .= @$responsible->sfow_name.' - ';
		} else {
			$participants .= @$responsible->sfow_name_he.' - ';
		}
		if (@$responsible->name != '')
			$participants .= @$responsible->name;

		if ($row_count == 0){
			$html1_body .= '<td width="'.$col_width.'%">';
		}

		$html1_body .= '<p style="line-height:0.4;font-size:12px;">'.htmlspecialchars($participants).'</p>';

		$row_count++;

		if ($row_count >= $max_rows_per_col || $index == $total_items - 1) {
			$html1_body .= '</td>';
			$row_count = 0;
			$column_count++;

			if ($column_count >= $total_columns && $index != $total_items - 1) {
				$html .= '</tr><tr>';
				$column_count = 0;
			}
		}
	}

    $html1_body .= '</tr></table>';
}

$html1_body.= '<table dir="'.$dir_table.'" style="'.$style_table.'"><tr><td></td></tr><tr><td width="1%">&nbsp;</td><td><table cellpadding="4">';
$html1_body.='<tr style="background-color:#e5f4ff;font-size:11px;">';
$html1_body.='<th width="'.@$count_width.'" style="text-align:center;border:1px solid black;font-size:10px;font-weight:bold;">&#x2116;</th>';
if(in_array('subject',$columns_list_array))
  $html1_body.='<th width="'.@$subject_width.'" style="text-align:center;border:1px solid black;font-size:10px;font-weight:bold;">'.$subject_domain_header.'</th>';
if(in_array('area',$columns_list_array))
  $html1_body.='<th width="'.@$area_width.'" style="text-align:center;border:1px solid black;font-size:10px;font-weight:bold;">'.$area_subject_header.'</th>';
if(in_array('description',$columns_list_array))
  $html1_body.='<th width="'.@$description_width.'" style="text-align:center;border:1px solid black;font-size:10px;font-weight:bold;">'.$description_header.'</th>';
if(in_array('_task',$columns_list_array))
  $html1_body.='<th width="'.@$task_width.'" style="text-align:center;border:1px solid black;font-size:10px;font-weight:bold;">'.$task_type_header.'</th>';
if(in_array('responsible',$columns_list_array))
  $html1_body.='<th width="'.@$responsible_width.'" style="text-align:center;border:1px solid black;font-size:10px;font-weight:bold;">'.$responsible_header.'</th>';
if(in_array('pass on',$columns_list_array))
  $html1_body.='<th width="'.@$pass_on_width.'" style="text-align:center;border:1px solid black;font-size:10px;font-weight:bold;">'.$transfer_confirm_header.'</th>';
if(in_array('task creation',$columns_list_array))
  $html1_body.='<th width="'.@$task_creation_width.'" style="text-align:center;border:1px solid black;font-size:10px;font-weight:bold;">'.$task_creation_date_header.'</th>';
if(in_array('destination date',$columns_list_array))
  $html1_body.='<th width="'.@$destination_date_width.'" style="text-align:center;border:1px solid black;font-size:10px;font-weight:bold;">'.$destination_date_header.'</th>';
if(in_array('progress status',$columns_list_array))
  $html1_body.='<th width="'.@$progress_status_width.'" style="text-align:center;border:1px solid black;font-size:10px;font-weight:bold;">'.$progress_status_header.'</th>';
$html1_body.='</tr>';

if($id_custom_report > 0 || ($id_rdv_report > 0 && $is_specific_filter)){
	$position_where = strpos($sql,"WHERE");
	$where_length = strlen($sql)-$position_where;
	$where_part_sql = substr($sql,$position_where,$where_length);
	$where_part_sql_array = explode(' AND ',$where_part_sql);
	
	$chapter_filter = '';
	if(strpos($where_part_sql,"m.id_chapter")!== false){
		
		for($i=0;$i<sizeof($where_part_sql_array);$i++){
			if(strpos($where_part_sql_array[$i],'m.id_chapter') !== false){
				$where_part_sql_array[$i] = str_replace('m.id_chapter','id',$where_part_sql_array[$i]);
			}
		}
		
		$where_part_sql = implode(' AND ',$where_part_sql_array);
		$chapter_filter = $where_part_sql;
		$chapter_filter_array = explode('AND ',$chapter_filter);
		$chapter_filter = $chapter_filter_array[2];
		$chapter_filter = ' AND '.$chapter_filter;
	}

	$sql_chapters = "SELECT * FROM dne_chapters 
	                 WHERE id_project = ? ".$chapter_filter.' ORDER BY id_display';
	$query = $mysqli->prepare($sql_chapters);
	$query->bind_param("i",$project_id);
	$query->execute();
	$query->store_result();
	$chapters = fetch($query);
}
if($id_rdv_report > 0 && !$is_specific_filter){
   $query = $mysqli->prepare("SELECT * FROM dne_chapters 
                             WHERE id_project = ? ORDER BY id_display");
   $query->bind_param("i",$project_id);
   $query->execute(); 
   $query->store_result();
   $chapters = fetch($query);
}

$count1 = 0;

foreach($chapters as $item){	
	$chapter_id = $item->id;
	$chapter_name = $item->name;
	
	if($id_custom_report > 0 || ($id_rdv_report > 0 && $is_specific_filter)){
		$sql_array = explode(' AND ',$sql);

		for($i=1;$i<sizeof($sql_array);$i++){
			if(strpos($sql_array[$i],'m.id_chapter') !== false){
				$sql_array[$i] = 'm.id_chapter ='.$chapter_id;
				$sql = implode(' AND ',$sql_array);
			}
		}
		
		if(strpos($sql,'m.id_chapter') !== true){
		    $sql.= ' AND m.id_chapter ='.$chapter_id;
		
		    $query = $mysqli->prepare($sql);
			$query->execute();
			$query->store_result();
			$meetings = fetch($query);
											
			$counter_with_image = 0;
			foreach ($meetings as $item){							
				if(@$all_ids_to_print == '' || in_array($item->id,$all_ids_to_print_array)) 
					$counter_with_image++;
			}
		
	        $query = $mysqli->prepare($sql.$_order_by);
			$query->execute();
			$query->store_result();
			$meetings = fetch($query);
		}
	}
	if($id_rdv_report > 0 && !$is_specific_filter){
		$is_appears = 1;
		
		$query = $mysqli->prepare("SELECT * FROM dne_meetings 
					              WHERE id_project = ? 
								  AND id_chapter = ? 
								  AND is_appears = ? 
								  AND id_progress_status <> ?
								  AND FIND_IN_SET(?, ids_rdv) > 0");
		$query->bind_param("iiiis",$project_id,$chapter_id,$is_appears,$ps_archive_id,$id_rdv_report);
		$query->execute();
		$query->store_result();
		$meetings = fetch($query);
		
		$counter_with_image = 0;
		foreach ($meetings as $item) {							
			if(@$all_ids_to_print == '' || in_array($item->id,$all_ids_to_print_array)) 
				$counter_with_image++;
		}
		
		$query = $mysqli->prepare("SELECT m.id AS id,
		                          m.is_priority AS is_priority,
								  m.id_chapter AS id_chapter,
		                          m.subject AS subject,m.ids_rdv AS ids_rdv,
								  m.area AS area,
								  m.description AS description,
								  m.id_task AS id_task ,
								  m.id_responsible AS id_responsible,
								  m.id_pass_on AS id_pass_on,
								  m.task_creation_date AS task_creation_date,
								  m.destination_date AS destination_date,
								  m.id_progress_status AS id_progress_status,
								  m.id_task_type AS id_task_type,
								  m.is_change_row_style AS is_change_row_style,
								  m.image1 AS image1,
								  m.image1_width AS image1_width,
								  m.image1_height AS image1_height,
								  m.is_appears_img1 AS is_appears_img1,
								  m.image2 AS image2,
								  m.image2_width AS image2_width,
								  m.image2_height AS image2_height,
								  m.is_appears_img2 AS is_appears_img2
								  FROM dne_meetings m 
								  LEFT JOIN dne_tasks t ON m.id_task = t.id
								  LEFT JOIN dne_progress_status ps ON m.id_progress_status = ps.id
								  LEFT JOIN dne_responsibles r ON m.id_responsible = r.id
								  WHERE m.id_project = ? 
								  AND m.id_chapter = ? 
								  AND m.is_appears = ? 
								  AND FIND_IN_SET(?,ids_rdv) > 0
								  AND m.id_progress_status <> ?
								  ".$_order_by);
		$query->bind_param("iiiii",$project_id,$chapter_id,$is_appears,
		                   $id_rdv_report,$ps_archive_id);
		$query->execute(); 
		$query->store_result();
		$meetings_num_rows = $query->num_rows;
		$meetings = fetch($query);
	}
	
	if($counter_with_image > 0) {
		$html1_body.='<tr style="background-color:#cbddec;font-size:11px;">';
		$html1_body.='<td colspan="12" style="'.$text_align.','.$padding.':5px;border:1px solid black;"><strong>'.@$chapter_name.'</strong></td>';
		$html1_body.='</tr>';
		
		foreach($meetings as $item){
			$count1++;
			
			$meeting_id = @$item->id;
			$is_priority = @$item->is_priority;
			$ids_rdv = @$item->ids_rdv;
			$subject = html_entity_decode(@$item->subject);
			$area = html_entity_decode(@$item->area);	
			$task_id = @$item->id_task;
			$responsible_id = @$item->id_responsible;
			$pass_on_id = @$item->id_pass_on;
			$progress_status_id = @$item->id_progress_status;
			$is_change_row_style = @$item->is_change_row_style;
			
			$task_creation_date = (@$item->task_creation_date != '0000-00-00') ? @$item->task_creation_date : '';
			$task_creation_date_display = smartDate(@$task_creation_date, $lang);
			
			$destination_date = @$item->destination_date;
			$destination_date_display = smartDate(@$destination_date, $lang);
			
			$updated_date = @$item->updated_date;
			
			$image1 = @$item->image1;
			$is_appears_img1 = @$item->is_appears_img1;
			$image1_width = @$item->image1_width;
			$image1_height = @$item->image1_height;
			
			$image2 = @$item->image2;
			$is_appears_img2 = @$item->is_appears_img2;
			$image2_width = @$item->image2_width;
			$image2_height = @$item->image2_height;
			
			$description = @$item->description ?? '';
			$description = html_entity_decode($description, ENT_QUOTES, 'UTF-8');
			$description = preg_replace('#<div[^>]*>\s*(<br\s*/?>)?\s*</div>#i', '', $description);
			$description = preg_replace('#</?div[^>]*>#i', '<br>', $description);
			$description = preg_replace('#(<br>){2,}#i', '<br>', $description);
			$description = trim($description);
			
			$query = $mysqli->prepare("SELECT * FROM dne_progress_status 
			                          WHERE id = ?");
			$query->bind_param("i",$progress_status_id);
			$query->execute();
			$query->store_result();
			$query = fetch_unique($query);
			
			if($lang == 'HE')
				$progress_status = @$query->name_he;				
			else
				$progress_status = @$query->name;
  
			$one = 1;
			$empty_remark = '';

			$query = $mysqli->prepare("SELECT lmu.remark AS remark,
									  lmu.action_date AS action_date,
									  u.nickname AS user_nickname,
									  ps.name AS ps_name,
									  ps.name_he AS ps_name_he
									  FROM dne_log_meeting_updates lmu
									  LEFT JOIN dne_users u ON lmu.id_user = u.id
									  LEFT JOIN dne_progress_status ps ON lmu.id_progress_status = ps.id
									  WHERE lmu.id_meeting = ? 
									  AND lmu.is_remark_appears_log = ?
									  AND lmu.remark <> ?
									  ORDER BY lmu.id");
			$query->bind_param("iis",$meeting_id,$one,$empty_remark);
			$query->execute();
			$query->store_result();
			$log_meeting_updates = fetch($query);
			
			$remark_color = @$is_colors ? 'color:green;' : 'color:black;';   
            
			if($query->num_rows > 0)
				$description .= '<br/>';			

			foreach($log_meeting_updates as $item){
				$remark = html_entity_decode(@$item->remark ?? '', ENT_QUOTES, 'UTF-8');
				if(!mb_check_encoding($remark, 'UTF-8'))
					$remark = mb_convert_encoding($remark, 'UTF-8', 'auto');
				$remark = html_entity_decode($remark, ENT_QUOTES, 'UTF-8');
				$remark = preg_replace('/<\/?(div|p)[^>]*>/i', '<br />', $remark);
				$remark = preg_replace('/(<br\s*\/?>\s*){2,}/i', '<br />', $remark);
				$remark = preg_replace('/^(<br\s*\/?>)+|(<br\s*\/?>)+$/i', '', $remark);
				$remark = str_replace(["\r\n", "\r", "\n"], '<br />', $remark);

				$action_date = @$item->action_date;		
                $progress_status_log_updates = @$item->ps_name_he;
				if(@$lang == 'EN')
					$progress_status_log_updates = @$item->ps_name;				

				if(trim($remark) != '') {
					$description .= '<div dir="'.@$dir_table.'" style="'.$remark_color.';'.@$text_align.'">';
					$description .= '<span dir="'.@$dir_table.'">['.smartDate($action_date, $lang).']</span>';
					$description .= ' <span dir="'.@$dir_table.'">'.@$progress_status_log_updates.'</span>';
					$description .= ' <span dir="'.@$dir_table.'">'.@$item->user_nickname.'</span> : <span dir="'.@$dir_table.'">'.$remark.'</span>';
					$description .= '</div>';
				}
			}
			
			$is_agrees = @$item->is_agrees;
			
			$target_date_text_decoration = 'text-decoration:none';
			if(@$is_agrees) 
				$target_date_text_decoration = 'text-decoration:underline';
			
			$is_reminds = @$item->is_reminds;

		    if(@$image1_width > 0 && @$image1_height > 0){
				$original_width = @$image1_width;
				$original_height = @$image1_height;
				$ratio = $original_width/$original_height;

				$max_width = 450;
				$max_height = 450;

				$image1_height = 230;
				$image1_width = $image1_height*$ratio;

				if($image1_width > $max_width){
					$image1_width = $max_width;
					$image1_height = $image1_width/$ratio;
				}

				if($image1_height > $max_height) {
					$image1_height = $max_height;
					$image1_width = $image1_height*$ratio;
				}
			}																					
											
			if(@$image2_width > 0 && @$image2_height > 0){
				$original_width = @$image2_width;
				$original_height = @$image2_height;
				$ratio = $original_width/$original_height;

				$max_width = 450;
				$max_height = 450;

				$image2_height = 230;
				$image2_width = $image2_height*$ratio;

				if($image2_width > $max_width){
					$image2_width = $max_width;
					$image2_height = $image2_width/$ratio;
				}

				if($image2_height > $max_height){
					$image2_height = $max_height;
					$image2_width = $image2_height*$ratio;
				}
			}		
			
			$color_num = 'color:black';
			if(@$is_colors && @$is_priority){
				$color_num = 'color:red';
			}
			
			$update_cell_bgcolor = 'background-color:white';		
			$subject_bgcolor = 'background-color:white';
			$area_bgcolor = 'background-color:white';	
			$description_bgcolor = 'background-color:white';
			
			$query = $mysqli->prepare("SELECT * FROM dne_tasks WHERE id = ?");
			$query->bind_param("i",$task_id);
			$query->execute();
			$query->store_result();
			$query = fetch_unique($query);
			$task = @$query->name_he;
			
			if($lang != 'HE')
			   $task = @$query->name;
			if(@$is_colors){
			   $task_color = 'color:'.@$query->color;
			   $task_bgcolor = 'background-color:'.@$query->bgcolor;
			}
			
			$query = $mysqli->prepare("SELECT * FROM dne_responsibles WHERE id = ?");
			$query->bind_param("i",$responsible_id);
			$query->execute();
			$query->store_result();
			$query = fetch_unique($query);
			$responsible = @$query->name;
			
			if(@$is_colors){
			  $responsible_color = 'color:'.@$query->color;
			  $responsible_bgcolor = 'background-color:'.@$query->bgcolor;
			}
			
			$query = $mysqli->prepare("SELECT * FROM dne_responsibles WHERE id = ?");
			$query->bind_param("i",$pass_on_id);
			$query->execute();
			$query->store_result();
			$query = fetch_unique($query);
			$pass_on = @$query->name;	

            $query = $mysqli->prepare("SELECT * FROM dne_progress_status WHERE id = ?");
			$query->bind_param("i",$progress_status_id);
			$query->execute();
			$query->store_result();
			$query = fetch_unique($query);
			$progress_status = @$query->name_he;
			
			if($lang == 'EN')
			   $progress_status = @$query->name;
			if(@$is_colors){
			  $progress_status_color = 'color:'.@$query->color;
			  $progress_status_bgcolor = 'background-color:'.@$query->bgcolor;
			}					
			
			if(@$lang == "HE"){
			    if(@$task == "הנחיית ביצוע" || 
			       @$progress_status == "הנחיה/החלטה" ||    
				   @$progress_status == "בהמתנה")
				      $destination_date_display = "";
			}
			else if(@$lang == "EN"){
			    if(@$task == "Executive order" || 
			       @$progress_status == "Decision" ||    
				   @$progress_status == "Hold")
				      $destination_date_display = "";
			}
			
			$task_creation_date_color = 'color:black';
			if(@$is_colors && !empty($ids_rdv) && !empty($id_rdv_report) && strpos($ids_rdv,(string)$id_rdv_report) !== false)
			   $task_creation_date_color = 'color:green';
			
			$task_creation_date_bgcolor = 'background-color:white';
			$dest_date_color = 'color:black';
			$dest_date_bgcolor = 'background-color:white';
			
			if(@$is_colors && @$item->destination_date < date('Y-m-d'))  
			   $dest_date_color = 'color:red;';	
			
			if(@$is_colors && $is_change_row_style){
				if($progress_status == 'בוצע/נמסר'){
				   $subject_bgcolor = 'background-color:#dedede';
				   $area_bgcolor = 'background-color:#dedede';
				   $description_bgcolor = 'background-color:#dedede';
				   $task_bgcolor = 'background-color:#dedede';
				   $responsible_bgcolor = 'background-color:#dedede';
				   $pass_on_bgcolor = 'background-color:#dedede';
				   $task_creation_date_bgcolor = 'background-color:#dedede';
				   $dest_date_color = 'color:#dedede';
				   $dest_date_bgcolor = 'background-color:#dedede';
				   $progress_status_bgcolor = 'background-color:#dedede';
				}
				else if($task == 'בקרת איכות'){
				   $subject_bgcolor = 'background-color:#fafd49';
				   $area_bgcolor = 'background-color:#fafd49';
				   $description_bgcolor = 'background-color:#fafd49';
				}
				else 
					$dest_date_color = 'color:white';
			}
			
			$end_new_tasks_date = $end_updated_date = $task_creation_date;
			if(@$period_new_tasks == 'today'){
				$end_new_tasks_date = $task_creation_date;
				$end_updated_date = $updated_date;
			}
			else if(@$period_new_tasks == 'three_days'){
				$end_new_tasks_date = date('Y-m-d',strtotime($task_creation_date . '+3 days'));
				$end_updated_date = date('Y-m-d',strtotime($updated_date . '+3 days'));
			}
			else if(@$period_new_tasks == 'one_week'){
				$end_new_tasks_date = date('Y-m-d',strtotime($task_creation_date . '+7 days'));
				$end_updated_date = date('Y-m-d',strtotime($updated_date . '+7 days'));
			}
			else if(@$period_new_tasks == 'two_weeks'){
				$end_new_tasks_date = date('Y-m-d',strtotime($task_creation_date . '+14 days'));
				$end_updated_date = date('Y-m-d',strtotime($updated_date . '+14 days'));
			}
			else if(@$period_new_tasks == 'one_month'){
				$end_new_tasks_date = date('Y-m-d',strtotime($task_creation_date . '+1 month'));
				$end_updated_date = date('Y-m-d',strtotime($updated_date . '+1 month'));
			}
			else if(@$period_new_tasks == 'two_months'){
				$end_new_tasks_date = date('Y-m-d',strtotime($task_creation_date . '+2 months'));
				$end_updated_date = date('Y-m-d',strtotime($updated_date . '+2 months'));
			}
			else if(@$period_new_tasks == 'one_year'){
				$end_new_tasks_date = date('Y-m-d',strtotime($task_creation_date . '+1 year'));
				$end_updated_date = date('Y-m-d',strtotime($updated_date . '+1 year'));
			}
			else if(@$period_new_tasks == 'two_years'){
				$end_new_tasks_date = date('Y-m-d',strtotime($task_creation_date . '+2 years'));
				$end_updated_date = date('Y-m-d',strtotime($updated_date . '+2 years'));												
			}
			if(empty($task_creation_date)){
				$hl = (!empty($updated_date) && $updated_date != '0000-00-00') ? $updated_date : '';
				if(empty($hl)) $end_new_tasks_date = '0000-00-00';
				elseif(@$period_new_tasks == 'today')       $end_new_tasks_date = $hl;
				elseif(@$period_new_tasks == 'three_days')  $end_new_tasks_date = date('Y-m-d',strtotime($hl.'+3 days'));
				elseif(@$period_new_tasks == 'one_week')    $end_new_tasks_date = date('Y-m-d',strtotime($hl.'+7 days'));
				elseif(@$period_new_tasks == 'two_weeks')   $end_new_tasks_date = date('Y-m-d',strtotime($hl.'+14 days'));
				elseif(@$period_new_tasks == 'one_month')   $end_new_tasks_date = date('Y-m-d',strtotime($hl.'+1 month'));
				elseif(@$period_new_tasks == 'two_months')  $end_new_tasks_date = date('Y-m-d',strtotime($hl.'+2 months'));
				elseif(@$period_new_tasks == 'one_year')    $end_new_tasks_date = date('Y-m-d',strtotime($hl.'+1 year'));
				elseif(@$period_new_tasks == 'two_years')   $end_new_tasks_date = date('Y-m-d',strtotime($hl.'+2 years'));
				else $end_new_tasks_date = '0000-00-00';
			}
			if(@$is_colors && @$progress_status != 'בוצע/נמסר' && @$task != 'בקרת איכות'){
				if(strlen(@$period_new_tasks) > 1 && (date('Y-m-d') <= $end_new_tasks_date)){			
					$update_cell_bgcolor = 'background-color:'.$global_bgcolor_new_task->bgcolor;
					$subject_bgcolor = 'background-color:'.$global_bgcolor_new_task->bgcolor;
					$area_bgcolor = 'background-color:'.$global_bgcolor_new_task->bgcolor;
					$description_bgcolor = 'background-color:'.$global_bgcolor_new_task->bgcolor;
					$dest_date_bgcolor = 'background-color:'.$global_bgcolor_new_task->bgcolor;
				}
			
				if(strlen(@$period_new_tasks) > 1 && (date('Y-m-d') <= $end_updated_date)){
					if(checkIfChangedField($meeting_id,'description'))
						$description_bgcolor = 'background-color:'.$global_bgcolor_new_task->bgcolor;
					if(checkIfChangedField($meeting_id,'destination_date'))
						$dest_date_bgcolor = 'background-color:'.$global_bgcolor_new_task->bgcolor;	
				}				
			}
			
			if(@$all_ids_to_print == '' || in_array($meeting_id,$all_ids_to_print_array)){
			    $html1_body.='<tr style="font-size:10px;'.@$dir_table.'">';	
                $html1_body.='<td width="'.@$count_width.'" style="text-align:center;'.@$update_cell_bgcolor.';border:1px solid black;'.$color_num.'">'.@$count1.'</td>';
			    if(in_array('subject',$columns_list_array))
			      $html1_body.='<td width="'.@$subject_width.'" style="'.setTextAlign($subject).';'.setPadding($subject).':5px;'.@$subject_bgcolor.';border:1px solid black;">'.@$subject.'</td>';	
				if(in_array('area',$columns_list_array))
				   $html1_body.='<td width="'.@$area_width.'" style="'.setTextAlign($area).';'.setPadding($area).':5px;'.@$area_bgcolor.';border:1px solid black;">'.@$area.'</td>';
				
				if(in_array('description',$columns_list_array)){
					$html1_body.='<td width="'.@$description_width.'" style="'.setTextAlign($description).';'.setPadding($description).':5px;'.@$description_bgcolor.';border:1px solid black;">';
				    $html1_body.= @$description;	
				    $html1_body.= '</td>';
				}
				
				if(in_array('_task',$columns_list_array))
				   $html1_body.='<td width="'.@$task_width.'" style="text-align:center;'.@$task_color.';'.@$task_bgcolor.';border:1px solid black;"><strong>'.@$task.'</strong></td>';			
				if(in_array('responsible',$columns_list_array))
				   $html1_body.='<td width="'.@$responsible_width.'" style="text-align:center;'.@$responsible_color.';'.@$responsible_bgcolor.';border:1px solid black;"><strong>'.@$responsible.'</strong></td>';
				if(in_array('pass on',$columns_list_array))
				   $html1_body.='<td width="'.@$pass_on_width.'" style="text-align:center;border:1px solid black;'.@$pass_on_bgcolor.'">'.@$pass_on.'</td>';
				if(in_array('task creation',$columns_list_array))
				   $html1_body.='<td width="'.@$task_creation_width.'" style="text-align:center;border:1px solid black;font-size:10px;'.$task_creation_date_color.';'.$task_creation_date_bgcolor.'">'.@$task_creation_date_display.'</td>';
				if(in_array('destination date',$columns_list_array))
				   $html1_body.='<td width="'.@$destination_date_width.'" style="text-align:center;border:1px solid black;font-size:10px;'.@$dest_date_color.';'.$dest_date_bgcolor.';'.@$target_date_text_decoration.'"><strong>'.@$destination_date_display.'</strong></td>';
				if(in_array('progress status',$columns_list_array))
				   $html1_body.='<td width="'.@$progress_status_width.'" style="text-align:center;'.@$progress_status_color.';'.@$progress_status_bgcolor.';border:1px solid black;"><strong>'.@$progress_status.'</strong></td>';
				$html1_body.='</tr>';
				
				$image1_path = !empty($image1) ? realpath(__DIR__.'/uploads/'.$image1) : false;
				$image2_path = !empty($image2) ? realpath(__DIR__.'/uploads/'.$image2) : false;

                if(($image1_path && file_exists($image1_path) && is_readable($image1_path) 
				    && @$is_images == 1 && @$image1_width > 0 && @$is_appears_img1 
				    && strpos(@$item->image1,'Snag') === false
				    && @getimagesize($image1_path) !== false) || 
					($image2_path && file_exists($image2_path) && is_readable($image2_path) 
					&& @$image2_width > 0 && @$is_appears_img2 
					&& strpos(@$image2,'Snag') === false
					&& @getimagesize($image2_path) !== false)){
					
					$html1_body .= '<tr><td colspan="'.@$colspan_image_tr.'">';

					if($image1_path && file_exists($image1_path) && is_readable($image1_path) 
						&& @$is_images == 1 && @$image1_width > 0 && @$is_appears_img1 
						&& strpos(@$item->image1,'Snag') === false
						&& @getimagesize($image1_path) !== false){
						
						$html1_body .= '<img src="' . $image1_path . '" width="'.@$image1_width.'" height="'.@$image1_height.'" />';
					} 	

					if($image2_path && file_exists($image2_path) && is_readable($image2_path) 
						&& @$image2_width > 0 && @$is_appears_img2 
						&& strpos(@$image2,'Snag') === false
						&& @getimagesize($image2_path) !== false){
						
						$html1_body .= '&nbsp;&nbsp;&nbsp;<img src="' . $image2_path . '" width="'.@$image2_width.'" height="'.@$image2_height.'" />';
					}
				
					$html1_body .= '</td></tr>';
				}			
			}
		}
	}
}
							
$html1_body.='</table></td></tr></table>';

$html1 = $html_header.$html1_body;
$pdf->setRTL(true);
if($lang != 'HE')
  $pdf->setRTL(false);	
$pdf->AddPage();
$pdf->writeHTMLCell(0,0,'','',$html1,0,1,0,true,'',true);

$html2_header = '<table><tr><td style="text-align:center;font-size:18px;">'.@$image_concentration_label.'</td></tr></table>';
$html2_body.= '<div class="row">';
$html2_body.= '<div class="col-md-12">';
$html2_body.= '<table dir="'.$dir_table.'" style="'.$style_table.'"><tr><td width="1%">&nbsp;</td><td><table cellpadding="4">';
$html2_body.='<tr style="background-color:#e5f4ff;font-size:11px;">';
$html2_body.='<th width="'.@$count_width.'" style="text-align:center;border:1px solid black;font-size:11px;font-weight:bold;">&#x2116;</th>';
if(in_array('subject',$columns_list_array))
  $html2_body.='<th width="'.@$subject_width.'" style="text-align:center;border:1px solid black;font-size:11px;font-weight:bold;">'.$subject_domain_header.'</th>';
if(in_array('area',$columns_list_array))
  $html2_body.='<th width="'.@$area_width.'" style="text-align:center;border:1px solid black;font-size:11px;font-weight:bold;">'.$area_subject_header.'</th>';
if(in_array('description',$columns_list_array))
  $html2_body.='<th width="'.@$description_width.'" style="text-align:center;border:1px solid black;font-size:11px;font-weight:bold;">'.$description_header.'</th>';
if(in_array('_task',$columns_list_array))
  $html2_body.='<th width="'.@$task_width.'" style="text-align:center;border:1px solid black;font-size:11px;font-weight:bold;">'.$task_type_header.'</th>';
if(in_array('responsible',$columns_list_array))
  $html2_body.='<th width="'.@$responsible_width.'" style="text-align:center;border:1px solid black;font-size:11px;font-weight:bold;">'.$responsible_header.'</th>';
if(in_array('pass on',$columns_list_array))
  $html2_body.='<th width="'.@$pass_on_width.'" style="text-align:center;border:1px solid black;font-size:11px;font-weight:bold;">'.$transfer_confirm_header.'</th>';
if(in_array('task creation',$columns_list_array))
  $html2_body.='<th width="'.@$task_creation_width.'" style="text-align:center;border:1px solid black;font-size:10px;font-weight:bold;">'.$task_creation_date_header.'</th>';
if(in_array('destination date',$columns_list_array))
  $html2_body.='<th width="'.@$destination_date_width.'" style="text-align:center;border:1px solid black;font-size:10px;font-weight:bold;">'.$destination_date_header.'</th>';
if(in_array('progress status',$columns_list_array))
  $html2_body.='<th width="'.@$progress_status_width.'" style="text-align:center;border:1px solid black;font-size:11px;font-weight:bold;">'.$progress_status_header.'</th>';
$html2_body.='</tr>';

$is_html2_appears = false;

$count2 = 0;

foreach($chapters as $item) {	
	$chapter_id = @$item->id;
	$chapter_name = @$item->name;
	
	if($id_custom_report > 0 || ($id_rdv_report > 0 && $is_specific_filter)) {
		$sql_array = explode(' AND ',$sql);

		for($i=1;$i<sizeof($sql_array);$i++) {
			if(strpos($sql_array[$i],'m.id_chapter') !== false) {
				$sql_array[$i] = 'm.id_chapter ='.$chapter_id;
				$sql = implode(' AND ',$sql_array);
			}
		}
		
		if(strpos($sql,'m.id_chapter') !== true) {
		    $sql.= ' AND m.id_chapter ='.$chapter_id;
			
			$query = $mysqli->prepare($sql);
			$query->execute();
			$query->store_result();
			$meetings = fetch($query);
											
			$counter_with_image = 0;
			foreach ($meetings as $item) {							
				if((@$all_ids_to_print == '' || in_array($item->id,$all_ids_to_print_array)) && ($item->image1 != '' && $item->is_appears_img1)) 
					$counter_with_image++;
			}					
			
	        $query = $mysqli->prepare($sql.$_order_by);
			$query->execute();
			$query->store_result();
		    $meetings_with_image = fetch($query);
		}
	}
	if($id_rdv_report > 0 && !$is_specific_filter) {
		$is_appears = 1;
		
		$query = $mysqli->prepare('SELECT * FROM dne_meetings 
					              WHERE id_project = ? AND 
								  id_chapter = ? 
								  AND is_appears = ?
								  AND id_progress_status <> ?
								  AND FIND_IN_SET(?,ids_rdv) > 0');
		$query->bind_param("iiiis",$project_id,$chapter_id,$is_appears,$ps_archive_id,$id_rdv_report);
		$query->execute();
		$query->store_result();
		$meetings = fetch($query);
		
		$counter_with_image = 0;
		foreach ($meetings as $item) {							
			if((@$all_ids_to_print == '' || in_array($item->id,$all_ids_to_print_array)) && ($item->image1 != '' && $item->is_appears_img1)) 
				$counter_with_image++;
		}
	
		$query = $mysqli->prepare("SELECT m.id AS id,
		                          m.is_priority AS is_priority,
								  m.subject AS subject,m.ids_rdv AS ids_rdv,
		                          m.area AS area,
								  m.description AS description,
								  m.id_task AS id_task,
								  m.id_responsible AS id_responsible,
								  m.id_pass_on AS id_pass_on,
								  m.is_change_row_style AS is_change_row_style,
								  m.task_creation_date AS task_creation_date,
								  m.destination_date AS destination_date,
								  m.id_progress_status AS id_progress_status,
								  m.image1 AS image1,
								  m.image1_width AS image1_width,
								  m.image1_height AS image1_height,
								  m.is_appears_img1 AS is_appears_img1,
								  m.image2 AS image2,
								  m.image2_width AS image2_width,
								  m.image2_height AS image2_height,
								  m.is_appears_img2 AS is_appears_img2,
								  m.updated_date AS updated_date,
								  c.name AS chapter_name
								  FROM dne_meetings m
								  LEFT JOIN dne_chapters c ON m.id_chapter = c.id
								  LEFT JOIN dne_tasks t ON m.id_task = t.id
								  LEFT JOIN dne_progress_status ps ON m.id_progress_status = ps.id
								  LEFT JOIN dne_responsibles r ON m.id_responsible = r.id
								  WHERE m.id_project = ? 
								  AND m.id_chapter = ? 
								  AND m.is_appears = ? 
								  AND FIND_IN_SET(?,ids_rdv) > 0
								  AND m.id_progress_status <> ?
								  ".$_order_by);
		$query->bind_param("iiiii",$project_id,$chapter_id,$is_appears,$id_rdv_report,$ps_archive_id);
		$query->execute(); 
		$query->store_result();
		$meetings_with_image = fetch($query);
	}
	
	if($counter_with_image > 0) {
		$html2_body.='<tr style="background-color:#cbddec;font-size:11px;">';
		$html2_body.='<td colspan="12" style="'.$text_align.','.$padding.':5px;border:1px solid black;"><strong>'.@$chapter_name.'</strong></td>';
		$html2_body.='</tr>';
		
		$is_html2_appears = true;
		
		foreach($meetings_with_image as $item) {
			$meeting_id = @$item->id;
			$is_priority = @$item->is_priority;
			$ids_rdv = @$item->ids_rdv;
			$subject = html_entity_decode(@$item->subject);
			$area = html_entity_decode(@$item->area);
			$task_id = @$item->id_task;
			$responsible_id = @$item->id_responsible;
			$pass_on_id = @$item->id_pass_on;
			$progress_status_id = @$item->id_progress_status;
			$is_change_row_style = @$item->is_change_row_style;
			
			$task_creation_date = (@$item->task_creation_date != '0000-00-00') ? @$item->task_creation_date : '';
			$task_creation_date_display = smartDate(@$task_creation_date, $lang);
			
			$destination_date = @$item->destination_date;
			$destination_date_display = smartDate(@$destination_date, $lang);
			
			$updated_date = @$item->updated_date;
			
			$image1 = @$item->image1;
			$is_appears_img1 = @$item->is_appears_img1;
			$image1_width = @$item->image1_width;
			$image1_height = @$item->image1_height;
			
			$image2 = @$item->image2;
			$is_appears_img2 = @$item->is_appears_img2;
			$image2_width = @$item->image2_width;
			$image2_height = @$item->image2_height;
			
			$description = html_entity_decode(@$item->description);
            $description = str_ireplace(['<div>','</div>'],'<br>',$description);
            $description = preg_replace('/(<br\s*\/?>\s*){2,}/i','<br>',$description);
            $description = preg_replace('/(?<!<br>)(\d+\s*[\.\-]\s*)/u','<br>$1',$description);
						
			$one = 1;
            $empty_remark = '';											
			$query = $mysqli->prepare("SELECT * FROM dne_log_meeting_updates 
									  WHERE id_meeting = ? 
						              AND is_remark_appears_log = ?
									  AND remark <> ?
									  ORDER BY id DESC");
			$query->bind_param("iis",$meeting_id,$one,$empty_remark);
			$query->execute();
			$query->store_result();	
			$log_meeting_updates = fetch($query);
			
			$remark_color = 'color:black';
			if(@$is_colors)
				$remark_color = 'color:green';
			
			foreach($log_meeting_updates as $item){
				$remark = html_entity_decode(@$item->remark);
				$remark = str_ireplace(['<div>', '</div>'], '<br>', $remark);
				$remark = preg_replace('/(<br\s*\/?>\s*){2,}/i', '<br>', $remark);
				$remark = preg_replace('/^(<br\s*\/?>)+|(<br\s*\/?>)+$/i', '', $remark);
							
				$action_date = @$item->action_date;
				
				if($remark != '')
					$description .= '<div style="'.@$remark_color.'"><span style="direction:rtl;unicode-bidi:embed;">['.smartDate(@$action_date, $lang).']</span> - <span style="direction:rtl;unicode-bidi:embed;">'.@$item->user_nickname.'</span><span style="direction:rtl;unicode-bidi:embed;"> - '.@$remark.'</span></div>';		
			}

            $is_agrees = @$item->is_agrees;
			
			$target_date_text_decoration = 'text-decoration:none';
			if(@$is_agrees) 
				$target_date_text_decoration = 'text-decoration:underline';
			
			$is_reminds = @$item->is_reminds;			

		    if(@$image1_width > 0 && @$image1_height > 0) {
				$original_width = @$image1_width;
				$original_height = @$image1_height;
				$ratio = $original_width/$original_height;

				$max_width = 450;
				$max_height = 450;

				$image1_height = 230;
				$image1_width = $image1_height*$ratio;

				if($image1_width > $max_width) {
					$image1_width = $max_width;
					$image1_height = $image1_width/$ratio;
				}

				if($image1_height > $max_height) {
					$image1_height = $max_height;
					$image1_width = $image1_height*$ratio;
				}
			}																					
											
			if(@$image2_width > 0 && @$image2_height > 0) {
				$original_width = @$image2_width;
				$original_height = @$image2_height;
				$ratio = $original_width/$original_height;

				$max_width = 450;
				$max_height = 450;

				$image2_height = 230;
				$image2_width = $image2_height*$ratio;

				if($image2_width > $max_width) {
					$image2_width = $max_width;
					$image2_height = $image2_width/$ratio;
				}

				if($image2_height > $max_height) {
					$image2_height = $max_height;
					$image2_width = $image2_height*$ratio;
				}
			}

            $color_num = 'color:black';
			if(@$is_colors && @$is_priority) {
				$color_num = 'color:red';
			}			
			
			$update_cell_bgcolor = 'background-color:white';		
			$subject_bgcolor = 'background-color:white';
		    $area_bgcolor = 'background-color:white';
			$description_bgcolor = 'background-color:white';
			
			$query = $mysqli->prepare("SELECT * FROM dne_tasks 
			                          WHERE id = ?");
			$query->bind_param("i",$item->id_task);
			$query->execute();
			$query->store_result();
			$query = fetch_unique($query);
			$task = @$query->name_he;
			if($lang == 'EN')
			   $task = @$query->name;
			if(@$is_colors) {
			  $task_color = 'color:'.@$query->color;
		      $task_bgcolor = 'background-color:'.@$query->bgcolor;
			}
			
			$query = $mysqli->prepare("SELECT * FROM dne_responsibles 
			                          WHERE id = ?");
			$query->bind_param("i",$item->id_responsible);
			$query->execute();
			$query->store_result();
			$query = fetch_unique($query);
			$responsible = @$query->name;
			if(@$is_colors) {
			  $responsible_color = 'color:'.@$query->color;
			  $responsible_bgcolor = 'background-color:'.@$query->bgcolor;
			}
			
			$query = $mysqli->prepare("SELECT * FROM dne_responsibles 
			                          WHERE id = ?");
			$query->bind_param("i",$item->id_pass_on);
			$query->execute();
			$query->store_result();
			$query = fetch_unique($query);
			$pass_on = @$query->name;
			
			$query = $mysqli->prepare("SELECT * FROM dne_progress_status 
			                           WHERE id = ?");
			$query->bind_param("i",$item->id_progress_status);
			$query->execute();
			$query->store_result();
			$query = fetch_unique($query);
			$progress_status = @$query->name_he;
			
			if($lang == 'EN')
			   $progress_status = @$query->name;
			if(@$is_colors){
			  $progress_status_color = 'color:'.@$query->color;
			  $progress_status_bgcolor = 'background-color:'.@$query->bgcolor;
			}
			
			if(@$lang == "HE"){
			    if(@$task == "הנחיית ביצוע" || 
			       @$progress_status == "הנחיה/החלטה" ||    
				   @$progress_status == "בהמתנה")
				      $destination_date_display = "";
			}
			else if(@$lang == "EN"){
			    if(@$task == "Executive order" || 
			       @$progress_status == "Decision" ||    
				   @$progress_status == "Hold")
				      $destination_date_display = "";
			}
			
			$task_creation_date_color = 'color:black';
			if(@$is_colors && !empty($ids_rdv) && !empty($id_rdv_report) && strpos($ids_rdv,(string)$id_rdv_report) !== false) {
			   $task_creation_date_color = 'color:green';
			}
			
			$task_creation_date_bgcolor = 'background-color:white';
			
			$dest_date_color = 'color:black';
			$dest_date_bgcolor = 'background-color:white';
			
			if(@$is_colors && @$item->destination_date < date('Y-m-d')) { 
			   $dest_date_color = 'color:red;';
			}
			
			if(@$is_colors && $is_change_row_style) {
				if($progress_status == 'בוצע/נמסר') {
				   $subject_bgcolor = 'background-color:#dedede';
				   $area_bgcolor = 'background-color:#dedede';
				   $description_bgcolor = 'background-color:#dedede';
				   $task_bgcolor = 'background-color:#dedede';
				   $responsible_bgcolor = 'background-color:#dedede';
				   $pass_on_bgcolor = 'background-color:#dedede';
				   $task_creation_date_bgcolor = 'background-color:#dedede';
				   $dest_date_color = 'color:#dedede';
				   $dest_date_bgcolor = 'background-color:#dedede';
				   $progress_status_bgcolor = 'background-color:#dedede';
				}
				else if($task == 'בקרת איכות') {
				   $subject_bgcolor = 'background-color:#fafd49';
				   $area_bgcolor = 'background-color:#fafd49';
				   $description_bgcolor = 'background-color:#fafd49';
				}
				else 
					$dest_date_color = 'color:white';
			}
			
			$end_new_tasks_date = $end_updated_date = $task_creation_date;
			if(@$period_new_tasks == 'today'){
				$end_new_tasks_date = $task_creation_date;
				$end_updated_date = $updated_date;
			}
			else if(@$period_new_tasks == 'three_days'){
				$end_new_tasks_date = date('Y-m-d',strtotime($task_creation_date . '+3 days'));
				$end_updated_date = date('Y-m-d',strtotime($updated_date . '+3 days'));
			}
			else if(@$period_new_tasks == 'one_week'){
				$end_new_tasks_date = date('Y-m-d',strtotime($task_creation_date . '+7 days'));
				$end_updated_date = date('Y-m-d',strtotime($updated_date . '+7 days'));
			}
			else if(@$period_new_tasks == 'two_weeks'){
				$end_new_tasks_date = date('Y-m-d',strtotime($task_creation_date . '+14 days'));
				$end_updated_date = date('Y-m-d',strtotime($updated_date . '+14 days'));
			}
			else if(@$period_new_tasks == 'one_month'){
				$end_new_tasks_date = date('Y-m-d',strtotime($task_creation_date . '+1 month'));
				$end_updated_date = date('Y-m-d',strtotime($updated_date . '+1 month'));
			}
			else if(@$period_new_tasks == 'two_months'){
				$end_new_tasks_date = date('Y-m-d',strtotime($task_creation_date . '+2 months'));
				$end_updated_date = date('Y-m-d',strtotime($updated_date . '+2 months'));
			}
			else if(@$period_new_tasks == 'one_year'){
				$end_new_tasks_date = date('Y-m-d',strtotime($task_creation_date . '+1 year'));
				$end_updated_date = date('Y-m-d',strtotime($updated_date . '+1 year'));
			}
			else if(@$period_new_tasks == 'two_years'){
				$end_new_tasks_date = date('Y-m-d',strtotime($task_creation_date . '+2 years'));
				$end_updated_date = date('Y-m-d',strtotime($updated_date . '+2 years'));												
			}
			if(empty($task_creation_date)){
				$hl = (!empty($updated_date) && $updated_date != '0000-00-00') ? $updated_date : '';
				if(empty($hl)) $end_new_tasks_date = '0000-00-00';
				elseif(@$period_new_tasks == 'today')       $end_new_tasks_date = $hl;
				elseif(@$period_new_tasks == 'three_days')  $end_new_tasks_date = date('Y-m-d',strtotime($hl.'+3 days'));
				elseif(@$period_new_tasks == 'one_week')    $end_new_tasks_date = date('Y-m-d',strtotime($hl.'+7 days'));
				elseif(@$period_new_tasks == 'two_weeks')   $end_new_tasks_date = date('Y-m-d',strtotime($hl.'+14 days'));
				elseif(@$period_new_tasks == 'one_month')   $end_new_tasks_date = date('Y-m-d',strtotime($hl.'+1 month'));
				elseif(@$period_new_tasks == 'two_months')  $end_new_tasks_date = date('Y-m-d',strtotime($hl.'+2 months'));
				elseif(@$period_new_tasks == 'one_year')    $end_new_tasks_date = date('Y-m-d',strtotime($hl.'+1 year'));
				elseif(@$period_new_tasks == 'two_years')   $end_new_tasks_date = date('Y-m-d',strtotime($hl.'+2 years'));
				else $end_new_tasks_date = '0000-00-00';
			}
			if(@$is_colors && @$progress_status != 'בוצע/נמסר' && @$task != 'בקרת איכות') {
				if(strlen(@$period_new_tasks) > 1 && (date('Y-m-d') <= $end_new_tasks_date)){			
					$subject_bgcolor = 'background-color:'.$global_bgcolor_new_task->bgcolor;
				    $area_bgcolor = 'background-color:'.$global_bgcolor_new_task->bgcolor;
					$description_bgcolor = 'background-color:'.$global_bgcolor_new_task->bgcolor;
					$dest_date_bgcolor = 'background-color:'.$global_bgcolor_new_task->bgcolor;
				}
			
				if(strlen(@$period_new_tasks) > 1 && (date('Y-m-d') <= $end_updated_date)){
					if(checkIfChangedField($meeting_id,'description'))
						$description_bgcolor = 'background-color:'.$global_bgcolor_new_task->bgcolor;
					if(checkIfChangedField($meeting_id,'destination_date'))
						$dest_date_bgcolor = 'background-color:'.$global_bgcolor_new_task->bgcolor;
				}				
			}
			
            if((@$all_ids_to_print == '' || in_array($meeting_id,$all_ids_to_print_array)) && (@$image1 != '' && @$is_appears_img1)) {	
                $count2++;
				
				$html2_body.='<tr style="font-size:10px;'.@$dir_table.'">';		
				$html2_body.='<td width="'.@$count_width.'" style="text-align:center;'.@$update_cell_bgcolor.';border:1px solid black;'.$color_num.'">'.@$count2.'</td>';			
				if(in_array('subject',$columns_list_array))
				   $html2_body.='<td width="'.@$subject_width.'" style="'.setTextAlign($subject).';'.setPadding($subject).':5px;'.@$subject_bgcolor.';border:1px solid black;">'.@$subject.'</td>';	
				if(in_array('area',$columns_list_array))
				   $html2_body.='<td width="'.@$area_width.'" style="'.setTextAlign($area).';'.setPadding($area).':5px;'.@$area_bgcolor.';border:1px solid black;">'.@$area.'</td>';
				
				if(in_array('description',$columns_list_array)) {
				   $html2_body.='<td width="'.@$description_width.'" style="'.setTextAlign($description).';'.@setPadding($description).':5px;'.@$description_bgcolor.';border:1px solid black;">';
				   $html2_body.= nl2br(@$description);	 	
				   $html2_body.= '</td>';
				}
				
				if(in_array('_task',$columns_list_array))
				   $html2_body.='<td width="'.@$task_width.'" style="text-align:center;'.@$task_color.';'.@$task_bgcolor.';border:1px solid black;"><strong>'.@$task.'</strong></td>';
				if(in_array('responsible',$columns_list_array))
				   $html2_body.='<td width="'.@$responsible_width.'" style="text-align:center;'.@$responsible_color.';'.@$responsible_bgcolor.';border:1px solid black;"><strong>'.@$responsible.'</strong></td>';
				if(in_array('pass on',$columns_list_array))
				   $html2_body.='<td width="'.@$pass_on_width.'" style="text-align:center;border:1px solid black;'.@$pass_on_bgcolor.'">'.@$pass_on.'</td>';
				if(in_array('task creation',$columns_list_array))
				   $html2_body.='<td width="'.@$task_creation_width.'" style="text-align:center;border:1px solid black;font-size:10px;'.$task_creation_date_color.';'.$task_creation_date_bgcolor.'">'.@$task_creation_date_display.'</td>';
				if(in_array('destination date',$columns_list_array))
				   $html2_body.='<td width="'.@$destination_date_width.'" style="text-align:center;border:1px solid black;font-size:10px;'.@$dest_date_color.';'.$dest_date_bgcolor.';'.@$target_date_text_decoration.'"><strong>'.@$destination_date_display.'</strong></td>';
				if(in_array('progress status',$columns_list_array))
				   $html2_body.='<td width="'.@$progress_status_width.'" style="text-align:center;'.@$progress_status_color.';'.@$progress_status_bgcolor.';border:1px solid black;"><strong>'.@$progress_status.'</strong></td>';
				$html2_body.='</tr>'; 
				
				if(strpos($image1,'Snag') === false && strpos($image2,'Snag') === false) {
				  $html2_body.=   '<tr>
					                    <td colspan="'.@$colspan_image_tr.'">
									        <img src="uploads/'.@$image1.'" width="'.@$image1_width.'" height="'.@$image1_height.'" />';
					if(@$image2 != '' && @$is_appears_img2 == 1) 
					   $html2_body.= '&nbsp;&nbsp;&nbsp;<img src="uploads/'.@$image2.'" width="'.@$image2_width.'" height="'.@$image2_height.'" />';
					$html2_body.= '</td></tr>';
			    }
			}
		}
	}
}

							
$html2_body.='</table></td></tr></table>';
$html2_body.='</div>';
$html2_body.='</div>';

if(@$is_images == 2 && @$is_html2_appears) {
	$html2 = $html2_header.$html2_body;
	$pdf->setRTL(true);
    if($lang != 'HE')
      $pdf->setRTL(false);
	$pdf->AddPage();
	$pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));
	$pdf->writeHTMLCell(0, 0, '', '', $html2, 0, 1, 0, true, '', true);
}

ob_end_clean();

$pdf_name = $_SESSION['pdf_date'].'-';
if($_SESSION['pdf_text1'] != '')
   $pdf_name.= $_SESSION['pdf_text1'].'-';
if($_SESSION['pdf_text2'] != '')
   $pdf_name.= $_SESSION['pdf_text2'].'-';
$pdf_name.= $project->nickname.'.pdf';

$mode = $_GET['mode']??'I';
if($mode === 'D') {
    $pdf->Output($pdf_name,'D'); 
} else {
    $pdf->Output($pdf_name,'I');
}
?>
