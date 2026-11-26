<?php
require_once('tcpdf_min/config/tcpdf_config.php');
require_once('tcpdf_min/tcpdf.php');
require_once __DIR__ . '/include/session_config.php';   // connexion gardee 30 jours
session_start();
include 'functions/functions.php';
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

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
			$sql = str_replace('{CURRENT_USER_ID}', (int)@$_SESSION['id_user'], @$custom_report->sql_str);
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
		if(!$query){
			error_log('SQL invalide pour un rapport (meetings_report.php, project_id='.$project_id.'): '.$mysqli->error.' -- '.$sql);
			$all_meetings_num_rows = 0;
			$all_meetings_with_images_num_rows = 0;
		}
		else {
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
		}
	
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
		if(!$query){
			error_log('SQL invalide pour un rapport (meetings_report.php, project_id='.$project_id.'): '.$mysqli->error.' -- '.$sql);
			$all_meetings_num_rows = 0;
			$all_meetings_with_images_num_rows = 0;
		}
		else {
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
$_default_order = (@$lang == 'HE') ? ' ORDER BY m.subject,m.id_area,t.name_he' : ' ORDER BY m.subject,m.id_area,t.name';
$_order_by = !empty($_order_parts) ? ' ORDER BY '.implode(',',$_order_parts) : $_default_order;

$align_txt = 'alignLeft';
$padding_txt = 'paddingLeft10';
												
if(@$lang == 'HE'){
	$dir_table = 'rtl';
    $style_table = "margin:auto;";
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
    $style_table = "margin:auto;";
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
		$this->Ln(min($this->getLastH(), 5));   // saut normal d une ligne (apres une photo haute, Ln() envoyait le pied de page sous la page)
        $this->Cell(0, 0, '', 'T');

        $this->SetX($this->lMargin);

        $pagePagination = 'Page ' . $this->getAliasNumPage() . ' of ' . $this->getAliasNbPages();
	    
		// Pied de page sur TOUTES les pages (l'ancien code faisait setPrintFooter(false) en anglais : plus de pied de page ensuite).
		// 3 cases sur toute la largeur : la partie de droite collee au bord droit, celle de gauche au bord gauche,
		// la pagination au centre ; disposition selon le vrai sens du PDF (droite-a-gauche en hebreu), en paysage comme en portrait.
		$footer_w = $this->getPageWidth() - $this->lMargin - $this->rMargin;
		$footer_mid = $this->GetStringWidth('Page 999 of 999') + 6;   // case de pagination a la largeur de son texte
		$footer_side = ($footer_w - $footer_mid) / 2;
		$footer_al = $this->getRTL() ? array('R', 'L') : array('L', 'R');   // RTL : projet au bord droit ; LTR : projet au bord gauche
		$this->mqFooterCell($footer_side, $project_name, $footer_al[0]);
		$this->Cell($footer_mid, 10, $pagePagination, 0, 0, 'C');
		$this->mqFooterCell($footer_side, $pdf_file_name, $footer_al[1]);
		$this->SetFont('freesans', '', 10);
	}

	// texte trop long pour sa case (ex. nom de fichier) : police reduite juste pour lui, jamais de chevauchement avec la pagination
	function mqFooterCell($w, $txt, $align){
		$fs = 10;
		$this->SetFont('freesans', '', $fs);
		while($fs > 6 && $this->GetStringWidth($txt) > $w - 2){
			$fs -= 0.5;
			$this->SetFont('freesans', '', $fs);
		}
		$this->Cell($w, 10, $txt, 0, 0, $align);
		$this->SetFont('freesans', '', 10);
    }

	// cadre beige arrondi (fond creme) autour du tableau des taches, sur chaque page ou il y a du tableau.
	// Dessine SOUS le contenu : le dessin est place au debut du contenu de la page (q ... Q, etat graphique inchange).
	// $frames[page] = array(haut, bas) du tableau ; $x, $w = bord gauche et largeur du tableau (mm, absolus).
	function mqDrawFrames($frames, $x, $w){
		if(empty($frames) || $w <= 0) return;
		$pad = 3;
		$lastPage = $this->getPage();
		$gv = $this->getGraphicVars();
		foreach($frames as $p => $fy){
			$this->setPage($p);
			$old = $this->getPageBuffer($p);
			$oldLen = strlen($old);
			$prefix = isset($this->footerlen[$p]) ? $oldLen - $this->footerlen[$p] : $oldLen;
			$this->_out('q');
			$this->RoundedRect($x - $pad, $fy[0] - $pad, $w + 2 * $pad, $fy[1] - $fy[0] + 2 * $pad, 3, '1111', 'DF',
				array('width' => 0.3, 'color' => array(239, 229, 215)), array(255, 250, 244));
			$this->Rect($x, $fy[0], $w, $fy[1] - $fy[0], 'F', array(), array(255, 255, 255));   // cases sans couleur : blanches
			$this->_out('Q');
			$new = $this->getPageBuffer($p);
			$chunk = substr($new, $prefix, strlen($new) - $oldLen);
			$this->setPageBuffer($p, $chunk.$old);
		}
		$this->setGraphicVars($gv);
		$this->setPage($lastPage);
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

$html_header = '<table><tr><td style="text-align:center;"><img src="uploads/'.@$logo->logo_stread.'" /></td></tr>';   // pas de saut de ligne sous le logo (moins d'espace avant le titre)

$new_task_legend_html = '';
if(@$project_id != 69 && @$is_colors && !empty(@$period_new_tasks)) {
	$new_task_legend_label = (@$lang == 'HE') ? 'עדכון/חדש' : 'Update/New';
	$new_task_legend_page_w = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];
	$new_task_legend_badge = '<table width="28mm" cellpadding="2" cellspacing="0"><tr><td style="background-color:'.@$global_bgcolor_new_task->bgcolor.';border:1px solid black;text-align:center;"><strong style="font-size:13px;">'.$new_task_legend_label.'</strong></td></tr></table>';
	// structure qui a deja donne HE=gauche/EN=droite de façon fiable : table pleine largeur explicite (pas
	// margin:auto), dir="ltr" force, meme ordre spacer+badge dans les deux langues (le bidi du texte suffit
	// a determiner le cote sous ce contexte precis)
	$new_task_legend_spacer_percent = 85;
	if($pdf_direction == 'L') {
		$new_task_legend_cols_width_px = @$count_width;
		if(in_array('subject',$columns_list_array)) $new_task_legend_cols_width_px += @$subject_width;
		if(in_array('area',$columns_list_array)) $new_task_legend_cols_width_px += @$area_width;
		if(in_array('_task',$columns_list_array)) $new_task_legend_cols_width_px += @$task_width;
		if(in_array('responsible',$columns_list_array)) $new_task_legend_cols_width_px += @$responsible_width;
		if(in_array('pass on',$columns_list_array)) $new_task_legend_cols_width_px += @$pass_on_width;
		if(in_array('task creation',$columns_list_array)) $new_task_legend_cols_width_px += @$task_creation_width;
		if(in_array('destination date',$columns_list_array)) $new_task_legend_cols_width_px += @$destination_date_width;
		if(in_array('progress status',$columns_list_array)) $new_task_legend_cols_width_px += @$progress_status_width;
		$new_task_legend_fixed_mm = $pdf->getHTMLUnitToUnits($new_task_legend_cols_width_px, $new_task_legend_page_w, 'px');
		$new_task_legend_description_pct = 0;
		if(in_array('description',$columns_list_array))
			$new_task_legend_description_pct = ((float)rtrim(@$description_width,'%')) / 100;
		$new_task_legend_real_table_mm = ($new_task_legend_description_pct > 0 && $new_task_legend_description_pct < 1)
			? ($new_task_legend_fixed_mm / (1 - $new_task_legend_description_pct))
			: $new_task_legend_fixed_mm;
		$new_task_legend_gap_mm = ($new_task_legend_page_w - $new_task_legend_real_table_mm) / 2;
		$new_task_legend_spacer_percent = (($new_task_legend_gap_mm + $new_task_legend_real_table_mm - 28) / $new_task_legend_page_w) * 100;
	}
	$new_task_legend_spacer = '<td width="'.$new_task_legend_spacer_percent.'%">&nbsp;</td>';
	$new_task_legend_cell = '<td width="'.(100 - $new_task_legend_spacer_percent).'%">'.$new_task_legend_badge.'</td>';
	$new_task_legend_cells = $new_task_legend_spacer.$new_task_legend_cell;
	$new_task_legend_html = '<!--MQLEG--><tr><td colspan="2"><table width="'.$new_task_legend_page_w.'mm" cellpadding="0" cellspacing="0" dir="ltr"><tr>'.$new_task_legend_cells.'</tr></table></td></tr><!--/MQLEG-->';   // reperes : badge realigne sur le bord du tableau (mqWriteTasksByBlocks)
}

$html1_body = '<tr style="font-size:16px;"><td width="40px;">&nbsp;</td><td style="text-align:center;"><span dir="'.$dir_table.'"><strong><u>'.str_replace('<br/>', '</u></strong><br/><span style="font-size:5px;"><br/></span><strong><u>', $title).'</u></strong></span></td></tr>'.$new_task_legend_html.'</table>';   // petit espace entre les lignes du titre

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

// centrage vertical de l'en-tete (meme technique/pourcentage que les lignes de donnees, calcule une seule
// fois puisque les libelles d'en-tete sont statiques, partage entre html1_body et html2_body)
$header_content_width_mm = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];
$header_cell_texts = array();
$header_cell_texts['count'] = array(@$count_width, '&#x2116;');
if(in_array('subject',$columns_list_array)) $header_cell_texts['subject'] = array(@$subject_width, $subject_domain_header);
if(in_array('area',$columns_list_array)) $header_cell_texts['area'] = array(@$area_width, $area_subject_header);
if(in_array('description',$columns_list_array)) $header_cell_texts['description'] = array(@$description_width, $description_header);
if(in_array('_task',$columns_list_array)) $header_cell_texts['task'] = array(@$task_width, $task_type_header);
if(in_array('responsible',$columns_list_array)) $header_cell_texts['responsible'] = array(@$responsible_width, $responsible_header);
if(in_array('pass on',$columns_list_array)) $header_cell_texts['pass_on'] = array(@$pass_on_width, $transfer_confirm_header);
if(in_array('task creation',$columns_list_array)) $header_cell_texts['task_creation'] = array(@$task_creation_width, $task_creation_date_header);
if(in_array('destination date',$columns_list_array)) $header_cell_texts['destination_date'] = array(@$destination_date_width, $destination_date_header);
if(in_array('progress status',$columns_list_array)) $header_cell_texts['progress_status'] = array(@$progress_status_width, $progress_status_header);

$header_max_h = 0;
$header_cell_heights = array();
foreach ($header_cell_texts as $hk => $hv) {
	$hw_mm = $pdf->getHTMLUnitToUnits($hv[0], $header_content_width_mm, 'px') * 1.4;
	$hnorm = str_replace(array('<br>','<br/>','<br />'), "\n", (string)$hv[1]);
	$hplain = html_entity_decode(trim($hnorm), ENT_QUOTES, 'UTF-8');
	$hh = $pdf->getStringHeight(max($hw_mm, 1), $hplain);
	$header_cell_heights[$hk] = array($hh, max($hw_mm, 1));
	if ($hh > $header_max_h) $header_max_h = $hh;
}
$header_pad_lines = array();
foreach ($header_cell_heights as $hk => $hhw) {
	list($hh, $hw_mm) = $hhw;
	$gap_mm = ($hh < $header_max_h - 0.3) ? (0.25 * $header_max_h) : 0;
	$gap_mm = min($gap_mm, 20); // evite un espaceur demesure qui pousserait le texte hors de la cellule si une colonne est anormalement haute
	if ($gap_mm > 0.3) {
		$unit_font_px = 24;
		$unit_h = $pdf->getStringHeight($hw_mm, '') * ($unit_font_px / 10);
		$spacer_html = '';
		if ($unit_h > 0) {
			$full_units = (int)floor($gap_mm / $unit_h);
			$remainder_mm = $gap_mm - ($full_units * $unit_h);
			for ($u = 0; $u < $full_units; $u++) {
				$spacer_html .= '<span style="font-size:'.$unit_font_px.'px;">&nbsp;<br></span>';
			}
			if ($remainder_mm > 0.3) {
				$one_line_h = $pdf->getStringHeight($hw_mm, '');
				$rem_font_px = ($one_line_h > 0) ? max(1, round(10 * ($remainder_mm / $one_line_h), 1)) : 0;
				if ($rem_font_px > 0) {
					$spacer_html .= '<span style="font-size:'.$rem_font_px.'px;">&nbsp;<br></span>';
				}
			}
		}
		$header_pad_lines[$hk] = $spacer_html;
	} else {
		$header_pad_lines[$hk] = '';
	}
}

$html1_body.='<!--MQHEAD-->';   // repere invisible : debut du tableau des taches (voir mqWriteTasksByBlocks)
$html1_body.= '<table dir="'.$dir_table.'" style="'.$style_table.'"><tr><td></td></tr><tr><td width="1%">&nbsp;</td><td><table cellpadding="4">';
$html1_body.='<!--MQHROW-->';   // repere invisible : ligne des titres de colonnes
$html1_body.='<tr style="background-color:#0E3C5D;color:#ffffff;font-size:11px;">';
$html1_body.='<th width="'.@$count_width.'" style="text-align:center;border:1px solid #D6D2D2;font-size:10px;font-weight:bold;">'.(@$header_pad_lines['count']??'').'&#x2116;</th>';
if(in_array('subject',$columns_list_array))
  $html1_body.='<th width="'.@$subject_width.'" style="text-align:center;border:1px solid #D6D2D2;font-size:10px;font-weight:bold;">'.(@$header_pad_lines['subject']??'').$subject_domain_header.'</th>';
if(in_array('area',$columns_list_array))
  $html1_body.='<th width="'.@$area_width.'" style="text-align:center;border:1px solid #D6D2D2;font-size:10px;font-weight:bold;">'.(@$header_pad_lines['area']??'').$area_subject_header.'</th>';
if(in_array('description',$columns_list_array))
  $html1_body.='<th width="'.@$description_width.'" style="text-align:center;border:1px solid #D6D2D2;font-size:10px;font-weight:bold;">'.(@$header_pad_lines['description']??'').$description_header.'</th>';
if(in_array('_task',$columns_list_array))
  $html1_body.='<th width="'.@$task_width.'" style="text-align:center;border:1px solid #D6D2D2;font-size:10px;font-weight:bold;">'.(@$header_pad_lines['task']??'').$task_type_header.'</th>';
if(in_array('responsible',$columns_list_array))
  $html1_body.='<th width="'.@$responsible_width.'" style="text-align:center;border:1px solid #D6D2D2;font-size:10px;font-weight:bold;">'.(@$header_pad_lines['responsible']??'').$responsible_header.'</th>';
if(in_array('pass on',$columns_list_array))
  $html1_body.='<th width="'.@$pass_on_width.'" style="text-align:center;border:1px solid #D6D2D2;font-size:10px;font-weight:bold;">'.(@$header_pad_lines['pass_on']??'').$transfer_confirm_header.'</th>';
if(in_array('task creation',$columns_list_array))
  $html1_body.='<th width="'.@$task_creation_width.'" style="text-align:center;border:1px solid #D6D2D2;font-size:10px;font-weight:bold;">'.(@$header_pad_lines['task_creation']??'').$task_creation_date_header.'</th>';
if(in_array('destination date',$columns_list_array))
  $html1_body.='<th width="'.@$destination_date_width.'" style="text-align:center;border:1px solid #D6D2D2;font-size:10px;font-weight:bold;">'.(@$header_pad_lines['destination_date']??'').$destination_date_header.'</th>';
if(in_array('progress status',$columns_list_array))
  $html1_body.='<th width="'.@$progress_status_width.'" style="text-align:center;border:1px solid #D6D2D2;font-size:10px;font-weight:bold;">'.(@$header_pad_lines['progress_status']??'').$progress_status_header.'</th>';
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
	$chapter_name = stripNbspArtifact($item->name);
	
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
			if(!$query){
				error_log('SQL invalide pour un rapport (meetings_report.php, project_id='.$project_id.'): '.$mysqli->error.' -- '.$sql);
				$meetings = array();
			}
			else {
				$query->execute();
				$query->store_result();
				$meetings = fetch($query);
			}

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
								  m.is_appears_img2 AS is_appears_img2,
								  m.updated_date AS updated_date
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

	// Largeur de contenu disponible (mm), utilisee pour mesurer la vraie hauteur de texte par cellule (centrage vertical)
	$content_width_mm = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];

	if($counter_with_image > 0) {
		$html1_body.='<!--MQSEG:C-->';   // repere invisible : ligne de chapitre (ecrite avec la 1re tache qui suit)
		$html1_body.='<tr style="background-color:#cbddec;font-size:11px;">';
		$html1_body.='<td colspan="'.@$colspan_image_tr.'" style="'.$text_align.','.$padding.':5px;border:1px solid #D6D2D2;"><strong>'.@$chapter_name.'</strong></td>';
		$html1_body.='</tr>';
		
		foreach($meetings as $item){
			$count1++;
			
			$meeting_id = @$item->id;
			$is_priority = @$item->is_priority;
			$ids_rdv = @$item->ids_rdv;
			$subject = escapeStrayAngleBrackets(html_entity_decode(@$item->subject));
			$area = escapeStrayAngleBrackets(html_entity_decode(@$item->area));
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
			$description = escapeStrayAngleBrackets($description);
			$description = preg_replace('#<div[^>]*>\s*(<br\s*/?>)?\s*</div>#i', '', $description);
			$description = preg_replace('#</?div[^>]*>#i', '<br>', $description);
			$description = preg_replace('#(<br>){2,}#i', '<br>', $description);
			// supprime les <span></span> vides : artefacts frequents d'un copier-coller depuis Word,
			// qui perturbent le moteur bidi de TCPDF et peuvent corrompre le rendu de tout le document
			$description = preg_replace('#<span[^>]*>\s*</span>#i', '', $description);
			// retire les forcages de direction dir="ltr"/"rtl" sur les balises inline (artefact frequent
			// d'un copier-coller Word) : plusieurs alternances de direction forcee dans un meme texte
			// font planter le moteur bidi de TCPDF et corrompent tout le rendu du document. Le texte
			// mixte hebreu/anglais s'affiche correctement SANS ce forcage (bidi Unicode automatique).
			$description = preg_replace('#\s+dir\s*=\s*(["\']).*?\1#i', '', $description);
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
			
			if($query->num_rows > 0)   // + petite marge entre la description et les remarques (comme marginTop5 dans meetings.php)
				{ $description = preg_replace('#(\s|&nbsp;|<br\s*/?>)+$#i', '', $description); $description .= '<span style="font-size:4px;"><br/><br/></span>'; }   // sans les sauts de ligne en fin de description (sinon une ligne vide en plus)			

			foreach($log_meeting_updates as $item){
				$remark = html_entity_decode(@$item->remark ?? '', ENT_QUOTES, 'UTF-8');
				if(!mb_check_encoding($remark, 'UTF-8'))
					$remark = mb_convert_encoding($remark, 'UTF-8', 'auto');
				$remark = html_entity_decode($remark, ENT_QUOTES, 'UTF-8');
				$remark = escapeStrayAngleBrackets($remark);
				$remark = preg_replace('/<\/?(div|p)[^>]*>/i', '<br />', $remark);
				$remark = preg_replace('/(<br\s*\/?>\s*){2,}/i', '<br />', $remark);
				$remark = preg_replace('/^(<br\s*\/?>)+|(<br\s*\/?>)+$/i', '', $remark);
				$remark = str_replace(["\r\n", "\r", "\n"], '<br />', $remark);

				$action_date = @$item->action_date;		
                $progress_status_log_updates = simplifyStatusLabel(@$item->ps_name_he);
				if(@$lang == 'EN')
					$progress_status_log_updates = @$item->ps_name;

				if(trim($remark) != '') {
					$status_text = trim(@$progress_status_log_updates);
					$show_status = preg_match('/\p{L}/u', $status_text) && $status_text != 'ללא' && $status_text != 'Without';

					// Demande client : plus de badge ni de nom ; date + statut, puis la remarque sur plusieurs lignes alignee comme a l'ecran
					// (TCPDF ne gere pas flex -> petit tableau : date/statut dans une colonne, remarque dans l'autre).
					// Largeur de la colonne date/statut = longueur REELLE du texte (mesuree par TCPDF) + 0,3 mm, rapportee a la colonne Description.
					// En hebreu le rapport est en mode RTL (setRTL) : TCPDF place lui-meme la 1re colonne (date/statut) a droite.
					$lmu_desc_pct = max(1, (float)rtrim(@$description_width, '%'));
					$lmu_margins = $pdf->getMargins();
					$lmu_cell_mm = max(10, ($pdf->getPageWidth() - $lmu_margins['left'] - $lmu_margins['right']) * $lmu_desc_pct / 100 - 2.7);
					$lmu_label_mm = $pdf->GetStringWidth(smartDate($action_date, $lang).'    ', 'dejavusans', '', 8.4)
								  + ($show_status ? $pdf->GetStringWidth(@$progress_status_log_updates.'   ', 'dejavusans', 'B', 8.4) : 0);
					$lmu_label_pct = round(min(60, max(8, $lmu_label_mm / $lmu_cell_mm * 100)), 1);
					$lmu_rtl = (@$dir_table == 'rtl');
					// morceaux dans l'ordre de lecture : date, -, statut, -
					$lmu_parts = array('<span style="color:#706f6c;">'.smartDate($action_date, $lang).'</span>',
									   '&nbsp;&nbsp;');
					if($show_status) {
						$lmu_parts[] = '<span style="color:#2d7a52;font-weight:bold;">'.@$progress_status_log_updates.'</span>';
						$lmu_parts[] = '&nbsp;&nbsp;';
					}
					if($lmu_label_pct > 35){
						// colonne Description trop etroite pour mettre date et remarque cote a cote (sinon les mots se coupent) :
						// date/statut sur une ligne, remarque dessous sur toute la largeur (meme sens et alignement que l'ancien affichage)
						array_pop($lmu_parts);   // le dernier tiret ne sert plus : la remarque est dessous
						// petit tableau d'une case (et non un div, qui ajoute une ligne vide au-dessus dans TCPDF)
						$description .= '<table cellpadding="0" cellspacing="0" style="width:100%;"><tr><td dir="'.@$dir_table.'" style="'.@$text_align.'">'.implode(' ', $lmu_parts)
									  . '<br/><span dir="'.@$dir_table.'" style="color:#2d7a52;">'.$remark.'</span></td></tr></table>';
					} else {
						$lmu_label = '<td width="'.$lmu_label_pct.'%" style="'.@$text_align.'">'.implode(' ', $lmu_parts).'</td>';
						$lmu_text = '<td width="'.(100 - $lmu_label_pct).'%" style="'.@$text_align.'"><span dir="'.@$dir_table.'" style="color:#2d7a52;">'.$remark.'</span></td>';
						$description .= '<table cellpadding="0" cellspacing="0" style="width:100%;"><tr>'.$lmu_label.$lmu_text.'</tr></table>';
					}
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
			$pass_on_bgcolor = ''; // remis a zero a chaque ligne (sinon le gris d'une tache בוצע passait aux lignes suivantes)
			
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
			if(empty($updated_date) || $updated_date == '0000-00-00'){
				$end_updated_date = '0000-00-00';
			}
			if(@$is_colors && @$progress_status != 'בוצע/נמסר' && @$task != 'בקרת איכות'){
				if(strlen(@$period_new_tasks) > 1 && (date('Y-m-d') <= $end_new_tasks_date)){
					$update_cell_bgcolor = 'background-color:'.$global_bgcolor_new_task->bgcolor;
					$subject_bgcolor = 'background-color:'.$global_bgcolor_new_task->bgcolor;
					$area_bgcolor = 'background-color:'.$global_bgcolor_new_task->bgcolor;
					$description_bgcolor = 'background-color:'.$global_bgcolor_new_task->bgcolor;
					$dest_date_bgcolor = 'background-color:'.$global_bgcolor_new_task->bgcolor;
					$progress_status_bgcolor = 'background-color:'.$global_bgcolor_new_task->bgcolor;
				}

				if(strlen(@$period_new_tasks) > 1 && (date('Y-m-d') <= $end_updated_date)){
					if(checkIfChangedField($meeting_id,'description'))
						$description_bgcolor = 'background-color:'.$global_bgcolor_new_task->bgcolor;
					if(checkIfChangedField($meeting_id,'destination_date'))
						$dest_date_bgcolor = 'background-color:'.$global_bgcolor_new_task->bgcolor;
					if(checkIfChangedField($meeting_id,'id_progress_status'))
						$progress_status_bgcolor = 'background-color:'.$global_bgcolor_new_task->bgcolor;
				}
			}
			
			if(@$all_ids_to_print == '' || in_array($meeting_id,$all_ids_to_print_array)){

				// Mesure la vraie hauteur de chaque cellule (via TCPDF) pour calculer un padding-top qui centre verticalement le contenu le plus court sur la hauteur de la ligne
				$row_cell_texts = array();
				$row_cell_texts['count'] = array(@$count_width, @$count1);
				if(in_array('subject',$columns_list_array)) $row_cell_texts['subject'] = array(@$subject_width, @$subject);
				if(in_array('area',$columns_list_array)) $row_cell_texts['area'] = array(@$area_width, @$area);
				if(in_array('description',$columns_list_array)) $row_cell_texts['description'] = array(@$description_width, @$description);
				if(in_array('_task',$columns_list_array)) $row_cell_texts['task'] = array(@$task_width, @$task);
				if(in_array('responsible',$columns_list_array)) $row_cell_texts['responsible'] = array(@$responsible_width, @$responsible);
				if(in_array('pass on',$columns_list_array)) $row_cell_texts['pass_on'] = array(@$pass_on_width, @$pass_on);
				if(in_array('task creation',$columns_list_array)) $row_cell_texts['task_creation'] = array(@$task_creation_width, @$task_creation_date_display);
				if(in_array('destination date',$columns_list_array)) $row_cell_texts['destination_date'] = array(@$destination_date_width, @$destination_date_display);
				if(in_array('progress status',$columns_list_array)) $row_cell_texts['progress_status'] = array(@$progress_status_width, @$progress_status);

				$row_max_h = 0;
				$row_cell_heights = array();
				foreach ($row_cell_texts as $rk => $rv) {
					$rw_mm = $pdf->getHTMLUnitToUnits($rv[0], $content_width_mm, 'px') * 1.4;
					// les remarques colorees sont chacune dans un <div> separe, qui force un saut de ligne visuel :
					// il faut le convertir en \n ici aussi, sinon la hauteur mesuree sous-estime le nombre de lignes reel
					$normalized = preg_replace('/<\/(div|p)>/i', "\n", (string)$rv[1]);
					$normalized = preg_replace('/<(div|p)[^>]*>/i', "\n", $normalized);
					$normalized = str_replace(array('<br>','<br/>','<br />'), "\n", $normalized);
					$plain = trim(preg_replace('/\n{2,}/', "\n", strip_tags($normalized)));
					$rh = $pdf->getStringHeight(max($rw_mm, 1), $plain);
					$row_cell_heights[$rk] = array($rh, max($rw_mm, 1));
					if ($rh > $row_max_h) $row_max_h = $rh;
				}
				// centrage vertical des cellules : fait plus loin, par MESURE reelle sur le brouillon (voir mqCenterRow) ;
				// l'ancien espaceur estime ici (0.38 x hauteur) ne centrait pas
				$row_pad_lines = array();

			    // page-break-inside:avoid pour les lignes anormalement hautes (ex: description tres longue avec
			    // historique de modifications) : evite qu'une telle ligne soit coupee au milieu de la page (ce qui
			    // corrompt le rendu TCPDF), en la poussant entiere sur la page suivante si besoin.
			    $row_nobr_style = ($row_max_h > 40 && $row_max_h < 220) ? 'page-break-inside:avoid;' : '';
			    $html1_body.='<!--MQSEG:T-->';   // repere invisible : debut d'une tache (texte, puis photo eventuelle)
			    $html1_body.='<tr style="font-size:10px;'.$row_nobr_style.@$dir_table.'">';
                $html1_body.='<td width="'.@$count_width.'" style="text-align:center;'.@$update_cell_bgcolor.';border:1px solid #D6D2D2;'.$color_num.'">'.(@$row_pad_lines['count']??'').@$count1.'</td>';
			    if(in_array('subject',$columns_list_array))
			      $html1_body.='<td width="'.@$subject_width.'" style="'.@$text_align.';'.@$padding.':5px;'.@$subject_bgcolor.';border:1px solid #D6D2D2;">'.(@$row_pad_lines['subject']??'').@$subject.'</td>';
				if(in_array('area',$columns_list_array))
				   $html1_body.='<td width="'.@$area_width.'" style="'.@$text_align.';'.@$padding.':5px;'.@$area_bgcolor.';border:1px solid #D6D2D2;">'.(@$row_pad_lines['area']??'').@$area.'</td>';

				if(in_array('description',$columns_list_array)){
					$html1_body.='<td width="'.@$description_width.'" style="'.@$text_align.';'.@$padding.':5px;'.@$description_bgcolor.';border:1px solid #D6D2D2;">';
				    $html1_body.= (@$row_pad_lines['description']??'').@$description;
				    $html1_body.= '</td>';
				}

				if(in_array('_task',$columns_list_array))
				   $html1_body.='<td width="'.@$task_width.'" style="text-align:center;'.@$task_color.';'.@$task_bgcolor.';border:1px solid #D6D2D2;">'.(@$row_pad_lines['task']??'').'<strong>'.@$task.'</strong></td>';
				if(in_array('responsible',$columns_list_array))
				   $html1_body.='<td width="'.@$responsible_width.'" style="text-align:center;'.@$responsible_color.';'.@$responsible_bgcolor.';border:1px solid #D6D2D2;">'.(@$row_pad_lines['responsible']??'').'<strong>'.@$responsible.'</strong></td>';
				if(in_array('pass on',$columns_list_array))
				   $html1_body.='<td width="'.@$pass_on_width.'" style="text-align:center;border:1px solid #D6D2D2;'.@$pass_on_bgcolor.'">'.(@$row_pad_lines['pass_on']??'').@$pass_on.'</td>';
				if(in_array('task creation',$columns_list_array))
				   $html1_body.='<td width="'.@$task_creation_width.'" style="text-align:center;border:1px solid #D6D2D2;font-size:10px;'.$task_creation_date_color.';'.$task_creation_date_bgcolor.'">'.(@$row_pad_lines['task_creation']??'').@$task_creation_date_display.'</td>';
				if(in_array('destination date',$columns_list_array))
				   $html1_body.='<td width="'.@$destination_date_width.'" style="text-align:center;border:1px solid #D6D2D2;font-size:10px;'.@$dest_date_color.';'.$dest_date_bgcolor.';'.@$target_date_text_decoration.'">'.(@$row_pad_lines['destination_date']??'').'<strong>'.@$destination_date_display.'</strong></td>';
				if(in_array('progress status',$columns_list_array))
				   $html1_body.='<td width="'.@$progress_status_width.'" style="text-align:center;'.@$progress_status_color.';'.@$progress_status_bgcolor.';border:1px solid #D6D2D2;">'.(@$row_pad_lines['progress_status']??'').'<strong>'.simplifyStatusLabel(@$progress_status).'</strong></td>';
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
					
					$html1_body .= '<!--MQIMG-->';   // repere invisible : la ligne photo de la tache
					$html1_body .= '<tr style="page-break-inside:avoid;"><td colspan="'.@$colspan_image_tr.'">';

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

// ---- Ecriture du tableau des taches TACHE PAR TACHE (demande client) ---------------------------------------
// On MESURE la hauteur de chaque tache / photo sur un PDF brouillon separe (jamais dans le rapport), puis on
// decide ou l'ecrire : une tache n'est jamais coupee entre deux pages ; une photo qui ne tient pas en bas de page
// est reduite au plus de 20 % (90 %, puis 80 %), sinon le tableau s'arrete au texte et la photo passe en haut
// de la page suivante, a sa taille normale. Seule la taille d'affichage change : la resolution de la photo est gardee.
// (Les transactions TCPDF ne sont pas utilisables : writeHTMLCell en utilise deja en interne.)
function mqDraft($pdf){
	static $draft = null;
	if($draft === null){
		$draft = new TCPDF(($pdf->getPageWidth() > $pdf->getPageHeight()) ? 'L' : 'P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$draft->setPrintHeader(false);
		$draft->setPrintFooter(false);
		$m = $pdf->getMargins();
		$draft->SetMargins($m['left'], $m['top'], $m['right']);
		$draft->SetAutoPageBreak(true, $pdf->getBreakMargin());
		$draft->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$draft->setFontSubsetting(true);
		$draft->SetFont('dejavusans', '', 12, '', true);
		$draft->setRTL($pdf->getRTL());
	}
	return $draft;
}

// ecrit un morceau du tableau interieur (sans l'enveloppe externe), a la meme place et largeur qu'avant
function mqWriteCell($p, $html){
	$p->writeHTMLCell($GLOBALS['mq_w'], 0, $GLOBALS['mq_x'], '', $html, 0, 1, 0, true, '', true);
}

// hauteur (mm) qu'occupera $html, ecrit en haut d'une page vide du brouillon
function mqMeasure($pdf, $html){
	$d = mqDraft($pdf);
	$d->AddPage();
	$p0 = $d->getPage();
	$top = $d->GetY();
	mqWriteCell($d, $html);
	$pages = $d->getPage() - $p0;
	$usable = $d->getPageHeight() - $d->getBreakMargin() - $top;
	return $pages * $usable + ($d->GetY() - $top);
}

// Ecrit le haut du rapport (logo + titre). Le logo a ~5 mm de blanc en bas dans l'image : le titre est remonte
// de 4 mm sur ce blanc (logo intact). Le titre garde la meme structure de tableau (1re ligne a 1 case, vide et
// minuscule a la place du logo) pour rester centre comme avant.
function mqWriteTop($pdf, $html){
	$pi = strpos($html, '<img');
	$pl = ($pi !== false) ? strpos($html, '</td></tr>', $pi) : false;
	if($pl === false || strpos($html, '<table') !== 0){
		$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
		return;
	}
	$emptyRow = '<tr><td style="text-align:center;font-size:1px;"></td></tr>';
	$pdf->writeHTMLCell(0, 0, '', '', substr($html, 0, $pl + 10).'</table>', 0, 1, 0, true, '', true);
	$y = $pdf->GetY();
	$d = mqDraft($pdf);
	$d->AddPage();
	$t = $d->GetY();
	$d->writeHTMLCell(0, 0, '', '', '<table>'.$emptyRow.'</table>', 0, 1, 0, true, '', true);
	$pdf->SetY($y - 4 - ($d->GetY() - $t));
	$pdf->writeHTMLCell(0, 0, '', '', '<table>'.$emptyRow.substr($html, $pl + 10), 0, 1, 0, true, '', true);
}

// place libre (mm) entre la position actuelle et le bas de la page
function mqSpaceLeft($pdf){
	return $pdf->getPageHeight() - $pdf->getBreakMargin() - $pdf->GetY();
}

function mqPageIsEmpty($pdf){
	return $pdf->GetY() <= $pdf->getMargins()['top'] + 1;
}

// Centre verticalement le contenu des cellules d'une tache (N°, responsable, statut... face a une longue description).
// Cette librairie PDF ignore vertical-align et le padding par cellule : on ajoute en haut de chaque cellule courte un
// espaceur vide (une seule ligne <br> dont la taille de police donne la hauteur), de la moitie de l'ecart MESURE sur le
// brouillon (meme police, memes largeurs). La hauteur de l'espaceur est verifiee dans la cellule elle-meme (elle depend
// de la cellule) ; si elle n'est pas exacte, la cellule reste comme avant (en haut).
// Les lignes vides en fin de cellule (<br> final, &nbsp;, blocs <div> de l'editeur) sont retirees : elles laissaient
// un blanc sous le texte, qui paraissait alors trop haut.
// Securite : si la ligne obtenue est plus haute que l'originale, la ligne d'origine est gardee telle quelle.
function mqSpacer($px){
	return '<span style="font-size:'.$px.'px;"><br></span>';
}
function mqTrimCell($c){
	$c = preg_replace('#^\s*<div>#i', '', $c);                    // 1er bloc de l'editeur : pas de saut de ligne avant
	$c = preg_replace('#<div>#i', '<br>', $c);                    // blocs suivants = retour a la ligne (comme a l'ecran)
	$c = preg_replace('#</div>#i', '', $c);
	return preg_replace('#(?:\s|&nbsp;|<br\s*/?>)+$#i', '', $c);  // lignes vides en fin de cellule
}
function mqCenterRow($pdf, $chunk){
	$open = '<table cellpadding="4">';
	if(!preg_match('#<tr style="font-size:10px;[^"]*">#', $chunk, $mo, PREG_OFFSET_CAPTURE)) return $chunk;
	$trOpen = $mo[0][0];
	$rowStart = $mo[0][1];
	// cellules de la ligne (niveau 0 seulement : la description peut contenir de petits tableaux, les lignes de journal)
	preg_match_all('#<(/?)(table|tr|td)\b[^>]*>#i', $chunk, $tags, PREG_SET_ORDER | PREG_OFFSET_CAPTURE, $rowStart + strlen($trOpen));
	$depth = 0; $tdOpens = array(); $orig = array(); $cs = -1; $rowEnd = -1;
	foreach($tags as $t){
		$close = ($t[1][0] == '/'); $name = strtolower($t[2][0]); $pos = $t[0][1]; $len = strlen($t[0][0]);
		if($name == 'table'){ $depth += $close ? -1 : 1; continue; }
		if($depth > 0) continue;
		if($name == 'td' && !$close){ $tdOpens[] = $t[0][0]; $cs = $pos + $len; }
		elseif($name == 'td' && $close && $cs >= 0){ $orig[] = substr($chunk, $cs, $pos - $cs); $cs = -1; }
		elseif($name == 'tr' && $close){ $rowEnd = $pos + $len; break; }
		else return $chunk;   // structure imprevue : rien change
	}
	if($rowEnd < 0 || $depth != 0 || count($orig) < 2 || count($orig) != count($tdOpens)) return $chunk;
	$build = function($contents) use ($open, $trOpen, $tdOpens){
		$h = $open.$trOpen;
		foreach($tdOpens as $i => $tdOpen) $h .= $tdOpen.$contents[$i].'</td>';
		return $h.'</tr></table>';
	};
	$Horig = mqMeasure($pdf, $build($orig));
	$trim = array_map('mqTrimCell', $orig);
	$H = mqMeasure($pdf, $build($trim));
	if($H > $Horig + 0.3) return $chunk;
	// hauteur de la ligne quand seule la cellule $i est remplie (avec ou sans espaceur) ; mis en cache
	static $cache = array();
	$solo = function($i, $content) use ($pdf, $build, $trim, $tdOpens, &$cache){
		$k = md5($tdOpens[$i].'|'.$content);
		if(!isset($cache[$k])){
			$c = array_fill(0, count($trim), '');
			$c[$i] = $content;
			$cache[$k] = mqMeasure($pdf, $build($c));
		}
		return $cache[$k];
	};
	$new = $trim;
	foreach($trim as $i => $content){
		$hs = $solo($i, $content);
		$gap = ($H - $hs) / 2;
		if($gap < 0.5) continue;
		$u = $solo($i, mqSpacer(10).$content) - $hs;          // hauteur d'un espaceur de 10px dans CETTE cellule
		if($u <= 0.1) continue;
		$px = round(10 * $gap / $u, 2);
		$got = $solo($i, mqSpacer($px).$content) - $hs;        // verification
		if(abs($got - $gap) > 0.3 && $got > 0.1){
			$px = round($px * $gap / $got, 2);
			$got = $solo($i, mqSpacer($px).$content) - $hs;
		}
		if(abs($got - $gap) <= 0.3) $new[$i] = mqSpacer($px).$content;
	}
	if($new === $orig) return $chunk;
	$rowNew = $build($new);
	if(mqMeasure($pdf, $rowNew) > $H + 0.3) return $chunk;   // jamais plus haut
	$trNew = substr($rowNew, strlen($open), -strlen('</table>'));
	return substr($chunk, 0, $rowStart).$trNew.substr($chunk, $rowEnd);
}

// une tache (texte) : jamais coupee ; si elle ne tient pas, elle commence en haut de la page suivante
function mqWriteBlockTogether($pdf, $html){
	if(!mqPageIsEmpty($pdf) && mqMeasure($pdf, $html) > mqSpaceLeft($pdf))
		$pdf->AddPage();
	$p0 = $pdf->getPage(); $y0 = $pdf->GetY();
	mqWriteCell($pdf, $html);
	mqTrack($pdf, $p0, $y0);
}

// retient, pour chaque page, le haut et le bas du tableau ecrit (pour le cadre beige, voir PDF::mqDrawFrames)
function mqTrack($pdf, $p0, $y0){
	$p1 = $pdf->getPage(); $y1 = $pdf->GetY();
	for($p = $p0; $p <= $p1; $p++){
		$top = ($p == $p0) ? $y0 : $pdf->getMargins()['top'];
		$bot = ($p == $p1) ? $y1 : $pdf->getPageHeight() - $pdf->getBreakMargin();
		if(isset($GLOBALS['mq_frame'][$p])){
			$top = min($top, $GLOBALS['mq_frame'][$p][0]);
			$bot = max($bot, $GLOBALS['mq_frame'][$p][1]);
		}
		if($bot > $top) $GLOBALS['mq_frame'][$p] = array($top, $bot);
	}
}

// la photo d'une tache : 100 %, sinon 90 %, sinon 80 % si ca tient ; sinon 100 % en haut de la page suivante
function mqWriteImageRow($pdf, $html){
	if(!mqPageIsEmpty($pdf)){
		foreach(array(1, 0.9, 0.8) as $scale){
			$h = ($scale == 1) ? $html : preg_replace_callback('/(<img\b[^>]*\bwidth=")([0-9.]+)("[^>]*\bheight=")([0-9.]+)(")/i', function($m) use ($scale){
				return $m[1].round($m[2] * $scale, 2).$m[3].round($m[4] * $scale, 2).$m[5];
			}, $html);
			if(mqMeasure($pdf, $h) <= mqSpaceLeft($pdf)){ mqWriteImgCell($pdf, $h); return; }
		}
		$pdf->AddPage();
	}
	mqWriteImgCell($pdf, $html);
}

// ligne photo : reculee de la marge de cellule (cellpadding="4") pour que la 1re photo soit collee au bord du tableau
// (a droite en hebreu, a gauche en anglais) ; hauteur inchangee
function mqWriteImgCell($pdf, $html){
	$x = $GLOBALS['mq_x'];
	$GLOBALS['mq_x'] = $x - $pdf->getHTMLUnitToUnits('4', 1, 'px');
	$p0 = $pdf->getPage(); $y0 = $pdf->GetY();
	mqWriteCell($pdf, $html);
	mqTrack($pdf, $p0, $y0);
	$GLOBALS['mq_x'] = $x;
}

// Ecrit le rapport grace aux reperes invisibles MQHEAD / MQHROW / MQSEG:C / MQSEG:T / MQIMG.
// Sans reperes (cas imprevu) : ancien comportement, un seul grand bloc.
function mqWriteTasksByBlocks($pdf, $html){
	$ph = strpos($html, '<!--MQHEAD-->');
	$pr = strpos($html, '<!--MQHROW-->');
	$ps = strpos($html, '<!--MQSEG:');
	$close = '</table></td></tr></table>';
	if($ps === false && $ph !== false && $pr !== false && $pr > $ph) $ps = strlen($html) - strlen($close);   // rapport sans tache : en-tete seul, meme chemin (centre aussi)
	if($ph === false || $pr === false || $ps === false || substr($html, -strlen($close)) !== $close){
		mqWriteTop($pdf, $html);
		return;
	}
	// place et largeur du tableau interieur, exactement comme dans l'enveloppe d'origine :
	// enveloppe = largeur utile - 2 marges de cellule ; 1re colonne = 1 % ; 2e colonne = 99 % (le tableau interieur)
	$cp = $pdf->getCellPaddings();
	$Wc = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'] - $cp['L'] - $cp['R'];
	$GLOBALS['mq_x'] = $pdf->getMargins()['left'] + $cp['L'] + 0.01 * $Wc;
	$GLOBALS['mq_w'] = 0.99 * $Wc;
	$open = '<table cellpadding="4">';
	$top = substr($html, 0, $ph);                                   // logo + titre : tels quels
	$headRow = substr($html, $pr + 13, $ps - $pr - 13);             // ligne des titres de colonnes
	$rest = (string)substr($html, $ps, strlen($html) - strlen($close) - $ps);
	// ligne vide d'origine en tete de l'enveloppe (espace sous le titre / le badge עדכון/חדש, comme avant)
	$wrapOpen = substr($html, $ph + 13, $pr - $ph - 13);
	$pw = strpos($wrapOpen, '<tr><td width="1%">');
	$emptyRowTable = ($pw !== false) ? substr($wrapOpen, 0, $pw).'</table>' : '';
	// badge עדכון/חדש : retire du haut et reecrit juste apres, colle au bord du tableau (cote fin du texte)
	$legendBadge = '';
	if(preg_match('#<!--MQLEG-->.*?<!--/MQLEG-->#s', $top, $ml) && !empty($GLOBALS['new_task_legend_badge'])){
		$top = str_replace($ml[0], '', $top);
		$legendBadge = $GLOBALS['new_task_legend_badge'];
	}
	// largeur totale de la ligne des titres (colonne N° en px + colonnes en %) : la ligne de chapitre, seule en tete
	// de son petit tableau, recoit exactement cette largeur (sinon elle est plus etroite que l'en-tete)
	$tableW = $GLOBALS['mq_w'] - $cp['L'] - $cp['R'];
	$headW = 0;
	if(preg_match_all('/<th\b[^>]*\bwidth="([^"]+)"/i', $headRow, $mw))
		foreach($mw[1] as $wv) $headW += $pdf->getHTMLUnitToUnits($wv, $tableW, 'px', false);
	// tableau centre sur la page : la colonne vide de 1 % de l'enveloppe d'origine le poussait du cote du debut du texte
	// (a droite en hebreu, a gauche en anglais) ; largeur inchangee, donc memes retours a la ligne
	if($headW > 0)
		$GLOBALS['mq_x'] = ($pdf->getPageWidth() - $headW) / 2 - $cp['L'];   // bord dessine = mq_x + marge de cellule ; largeur dessinee = headW
	mqWriteTop($pdf, $top);
	if($legendBadge != '' && $headW > 28)
		mqWriteCell($pdf, '<table cellpadding="0" cellspacing="0"><tr><td width="'.round($headW - 28, 3).'mm">&nbsp;</td><td width="28mm">'.$legendBadge.'</td></tr></table>');
	if($emptyRowTable != '') $pdf->writeHTMLCell(0, 0, '', '', $emptyRowTable, 0, 1, 0, true, '', true);
	$GLOBALS['mq_frame'] = array();
	$GLOBALS['mq_frame_x'] = ($pdf->getPageWidth() - $headW) / 2;   // bord gauche dessine du tableau (centre)
	$GLOBALS['mq_frame_w'] = $headW;
	$p0 = $pdf->getPage(); $y0 = $pdf->GetY();
	mqWriteCell($pdf, $open.$headRow.'</table>');
	mqTrack($pdf, $p0, $y0);
	if($headW > 0)
		$rest = preg_replace('/(<!--MQSEG:C--><tr\b[^>]*><td colspan="[^"]*")/', '$1 width="'.round($headW, 3).'mm"', $rest);
	$parts = preg_split('/<!--MQSEG:([CT])-->/', $rest, -1, PREG_SPLIT_DELIM_CAPTURE);
	$pending = '';   // ligne de chapitre en attente : ecrite avec la tache qui suit (jamais seule en bas de page)
	for($i = 1; $i < count($parts); $i += 2){
		$chunk = isset($parts[$i + 1]) ? $parts[$i + 1] : '';
		if($parts[$i] == 'C'){ $pending .= $chunk; continue; }
		$pieces = explode('<!--MQIMG-->', $chunk, 2);
		mqWriteBlockTogether($pdf, $open.$pending.mqCenterRow($pdf, $pieces[0]).'</table>');
		$pending = '';
		if(isset($pieces[1]) && trim($pieces[1]) != '')
			mqWriteImageRow($pdf, $open.$pieces[1].'</table>');
	}
	if(trim($pending) != '') mqWriteBlockTogether($pdf, $open.$pending.'</table>');
}

$html1 = $html_header.$html1_body;
$pdf->setRTL(true);
if($lang != 'HE')
  $pdf->setRTL(false);
$pdf->AddPage();
mqWriteTasksByBlocks($pdf, $html1);

$html2_header = '<table><tr><td style="text-align:center;font-size:18px;">'.@$image_concentration_label.'</td></tr></table>';
$html2_body.= '<div class="row">';
$html2_body.= '<div class="col-md-12">';
$html2_body.= '<table dir="'.$dir_table.'" style="'.$style_table.'"><tr><td width="1%">&nbsp;</td><td><table cellpadding="4">';
$html2_body.='<tr style="background-color:#0E3C5D;color:#ffffff;font-size:11px;">';
$html2_body.='<th width="'.@$count_width.'" style="text-align:center;border:1px solid #D6D2D2;font-size:11px;font-weight:bold;">'.(@$header_pad_lines['count']??'').'&#x2116;</th>';
if(in_array('subject',$columns_list_array))
  $html2_body.='<th width="'.@$subject_width.'" style="text-align:center;border:1px solid #D6D2D2;font-size:11px;font-weight:bold;">'.(@$header_pad_lines['subject']??'').$subject_domain_header.'</th>';
if(in_array('area',$columns_list_array))
  $html2_body.='<th width="'.@$area_width.'" style="text-align:center;border:1px solid #D6D2D2;font-size:11px;font-weight:bold;">'.(@$header_pad_lines['area']??'').$area_subject_header.'</th>';
if(in_array('description',$columns_list_array))
  $html2_body.='<th width="'.@$description_width.'" style="text-align:center;border:1px solid #D6D2D2;font-size:11px;font-weight:bold;">'.(@$header_pad_lines['description']??'').$description_header.'</th>';
if(in_array('_task',$columns_list_array))
  $html2_body.='<th width="'.@$task_width.'" style="text-align:center;border:1px solid #D6D2D2;font-size:11px;font-weight:bold;">'.(@$header_pad_lines['task']??'').$task_type_header.'</th>';
if(in_array('responsible',$columns_list_array))
  $html2_body.='<th width="'.@$responsible_width.'" style="text-align:center;border:1px solid #D6D2D2;font-size:11px;font-weight:bold;">'.(@$header_pad_lines['responsible']??'').$responsible_header.'</th>';
if(in_array('pass on',$columns_list_array))
  $html2_body.='<th width="'.@$pass_on_width.'" style="text-align:center;border:1px solid #D6D2D2;font-size:11px;font-weight:bold;">'.(@$header_pad_lines['pass_on']??'').$transfer_confirm_header.'</th>';
if(in_array('task creation',$columns_list_array))
  $html2_body.='<th width="'.@$task_creation_width.'" style="text-align:center;border:1px solid #D6D2D2;font-size:10px;font-weight:bold;">'.(@$header_pad_lines['task_creation']??'').$task_creation_date_header.'</th>';
if(in_array('destination date',$columns_list_array))
  $html2_body.='<th width="'.@$destination_date_width.'" style="text-align:center;border:1px solid #D6D2D2;font-size:10px;font-weight:bold;">'.(@$header_pad_lines['destination_date']??'').$destination_date_header.'</th>';
if(in_array('progress status',$columns_list_array))
  $html2_body.='<th width="'.@$progress_status_width.'" style="text-align:center;border:1px solid #D6D2D2;font-size:11px;font-weight:bold;">'.(@$header_pad_lines['progress_status']??'').$progress_status_header.'</th>';
$html2_body.='</tr>';

$is_html2_appears = false;

$count2 = 0;

foreach($chapters as $item) {	
	$chapter_id = @$item->id;
	$chapter_name = stripNbspArtifact(@$item->name);
	
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
			if(!$query){
				error_log('SQL invalide pour un rapport (meetings_report.php, project_id='.$project_id.'): '.$mysqli->error.' -- '.$sql);
				$meetings = array();
			}
			else {
				$query->execute();
				$query->store_result();
				$meetings = fetch($query);
			}
											
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
		$html2_body.='<td colspan="'.@$colspan_image_tr.'" style="'.$text_align.','.$padding.':5px;border:1px solid #D6D2D2;"><strong>'.@$chapter_name.'</strong></td>';
		$html2_body.='</tr>';
		
		$is_html2_appears = true;
		
		foreach($meetings_with_image as $item) {
			$meeting_id = @$item->id;
			$is_priority = @$item->is_priority;
			$ids_rdv = @$item->ids_rdv;
			$subject = escapeStrayAngleBrackets(html_entity_decode(@$item->subject));
			$area = escapeStrayAngleBrackets(html_entity_decode(@$item->area));
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
            $description = escapeStrayAngleBrackets($description);
            $description = str_ireplace(['<div>','</div>'],'<br>',$description);
            $description = preg_replace('/(<br\s*\/?>\s*){2,}/i','<br>',$description);
            $description = preg_replace('/(?<!<br>)(\d+\s*[\.\-]\s*)/u','<br>$1',$description);
            $description = preg_replace('#<span[^>]*>\s*</span>#i', '', $description);
						
			$one = 1;
            $empty_remark = '';											
			// lmu.* = memes colonnes qu'avant (SELECT *) ; + nom du statut, pour l'afficher comme dans la partie principale
			$query = $mysqli->prepare("SELECT lmu.*, ps.name AS ps_name, ps.name_he AS ps_name_he
									  FROM dne_log_meeting_updates lmu
									  LEFT JOIN dne_progress_status ps ON lmu.id_progress_status = ps.id
									  WHERE lmu.id_meeting = ?
						              AND lmu.is_remark_appears_log = ?
									  AND lmu.remark <> ?
									  ORDER BY lmu.id ASC");
			$query->bind_param("iis",$meeting_id,$one,$empty_remark);
			$query->execute();
			$query->store_result();	
			$log_meeting_updates = fetch($query);
			if($query->num_rows > 0)   // petite marge entre la description et les remarques (comme marginTop5 dans meetings.php)
				{ $description = preg_replace('#(\s|&nbsp;|<br\s*/?>)+$#i', '', $description); $description .= '<span style="font-size:4px;"><br/><br/></span>'; }   // sans les sauts de ligne en fin de description (sinon une ligne vide en plus)
			
			foreach($log_meeting_updates as $item){
				$remark = html_entity_decode(@$item->remark);
				$remark = escapeStrayAngleBrackets($remark);
				$remark = str_ireplace(['<div>', '</div>'], '<br>', $remark);
				$remark = preg_replace('/(<br\s*\/?>\s*){2,}/i', '<br>', $remark);
				$remark = preg_replace('/^(<br\s*\/?>)+|(<br\s*\/?>)+$/i', '', $remark);

				$action_date = @$item->action_date;

				if($remark != ''){
					// Demande client : plus de badge ni de nom ; date, puis la remarque sur plusieurs lignes alignee (petit tableau, TCPDF ne gere pas flex)
					$lmu_desc_pct2 = max(1, (float)rtrim(@$description_width, '%'));
					$lmu_margins2 = $pdf->getMargins();   // largeur de la colonne date = longueur reelle de la date + 1,5 mm
					$lmu_cell_mm2 = max(10, ($pdf->getPageWidth() - $lmu_margins2['left'] - $lmu_margins2['right']) * $lmu_desc_pct2 / 100 - 2.7);
					// statut : meme regle que la partie principale (simplifie en hebreu, nom anglais en EN, rien si vide ou ללא/Without)
					$lmu_status2 = trim((string)(@$lang == 'EN' ? @$item->ps_name : simplifyStatusLabel(@$item->ps_name_he)));
					$lmu_show_status2 = preg_match('/\p{L}/u', $lmu_status2) && $lmu_status2 != 'ללא' && $lmu_status2 != 'Without';
					$lmu_label_mm2 = $pdf->GetStringWidth(smartDate(@$action_date, $lang).'    ', 'dejavusans', '', 8.4)
								   + ($lmu_show_status2 ? $pdf->GetStringWidth($lmu_status2.'   ', 'dejavusans', 'B', 8.4) : 0);
					$lmu_label_pct2 = round(min(60, max(6, $lmu_label_mm2 / $lmu_cell_mm2 * 100)), 1);
					$lmu_rtl2 = (@$dir_table == 'rtl');
					// morceaux dans l'ordre de lecture : date, -, statut, -
					$lmu_parts2 = array('<span style="color:#706f6c;">'.smartDate(@$action_date, $lang).'</span>', '&nbsp;&nbsp;');
					if($lmu_show_status2){
						$lmu_parts2[] = '<span style="color:#2d7a52;font-weight:bold;">'.$lmu_status2.'</span>';
						$lmu_parts2[] = '&nbsp;&nbsp;';
					}
					// hebreu : reglage d'origine (direction:rtl) ; anglais : sans direction:rtl (sinon les mots anglais s'affichaient dans le desordre)
					$lmu_remark2 = '<span style="'.($lmu_rtl2 ? 'direction:rtl;unicode-bidi:embed;' : '').'color:#2d7a52;">'.@$remark.'</span>';
					if($lmu_label_pct2 > 35){
						// colonne trop etroite pour date et remarque cote a cote : date/statut sur une ligne, remarque dessous sur toute la largeur
						array_pop($lmu_parts2);   // le dernier tiret ne sert plus : la remarque est dessous
						// petit tableau d'une case (et non un div, qui ajoute une ligne vide au-dessus dans TCPDF)
						$description .= '<table cellpadding="0" cellspacing="0" style="width:100%;"><tr><td dir="'.@$dir_table.'" style="'.@$text_align.'">'.implode(' ', $lmu_parts2).'<br/>'.$lmu_remark2.'</td></tr></table>';
					} else {
						$lmu_label2 = '<td width="'.$lmu_label_pct2.'%" style="'.@$text_align.'">'.implode(' ', $lmu_parts2).'</td>';
						$lmu_text2  = '<td width="'.(100 - $lmu_label_pct2).'%" style="'.@$text_align.'">'.$lmu_remark2.'</td>';
						$description .= '<table cellpadding="0" cellspacing="0" style="width:100%;"><tr>'.$lmu_label2.$lmu_text2.'</tr></table>';
					}
				}
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
			$pass_on_bgcolor = ''; // remis a zero a chaque ligne (sinon le gris d'une tache בוצע passait aux lignes suivantes)
			
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
			if(empty($updated_date) || $updated_date == '0000-00-00'){
				$end_updated_date = '0000-00-00';
			}
			if(@$is_colors && @$progress_status != 'בוצע/נמסר' && @$task != 'בקרת איכות') {
				if(strlen(@$period_new_tasks) > 1 && (date('Y-m-d') <= $end_new_tasks_date)){
					$subject_bgcolor = 'background-color:'.$global_bgcolor_new_task->bgcolor;
				    $area_bgcolor = 'background-color:'.$global_bgcolor_new_task->bgcolor;
					$description_bgcolor = 'background-color:'.$global_bgcolor_new_task->bgcolor;
					$dest_date_bgcolor = 'background-color:'.$global_bgcolor_new_task->bgcolor;
					$progress_status_bgcolor = 'background-color:'.$global_bgcolor_new_task->bgcolor;
				}

				if(strlen(@$period_new_tasks) > 1 && (date('Y-m-d') <= $end_updated_date)){
					if(checkIfChangedField($meeting_id,'description'))
						$description_bgcolor = 'background-color:'.$global_bgcolor_new_task->bgcolor;
					if(checkIfChangedField($meeting_id,'destination_date'))
						$dest_date_bgcolor = 'background-color:'.$global_bgcolor_new_task->bgcolor;
					if(checkIfChangedField($meeting_id,'id_progress_status'))
						$progress_status_bgcolor = 'background-color:'.$global_bgcolor_new_task->bgcolor;
				}
			}
			
            if((@$all_ids_to_print == '' || in_array($meeting_id,$all_ids_to_print_array)) && (@$image1 != '' && @$is_appears_img1)) {	
                $count2++;
				
				$html2_body.='<tr style="font-size:10px;'.$row_nobr_style.@$dir_table.'">';
				$html2_body.='<td width="'.@$count_width.'" style="text-align:center;'.@$update_cell_bgcolor.';border:1px solid #D6D2D2;'.$color_num.'">'.(@$row_pad_lines['count']??'').@$count2.'</td>';
				if(in_array('subject',$columns_list_array))
				   $html2_body.='<td width="'.@$subject_width.'" style="'.@$text_align.';'.@$padding.':5px;'.@$subject_bgcolor.';border:1px solid #D6D2D2;">'.(@$row_pad_lines['subject']??'').@$subject.'</td>';
				if(in_array('area',$columns_list_array))
				   $html2_body.='<td width="'.@$area_width.'" style="'.@$text_align.';'.@$padding.':5px;'.@$area_bgcolor.';border:1px solid #D6D2D2;">'.(@$row_pad_lines['area']??'').@$area.'</td>';

				if(in_array('description',$columns_list_array)) {
				   $html2_body.='<td width="'.@$description_width.'" style="'.@$text_align.';'.@$padding.':5px;'.@$description_bgcolor.';border:1px solid #D6D2D2;">';
				   $html2_body.= (@$row_pad_lines['description']??'').nl2br(@$description);
				   $html2_body.= '</td>';
				}

				if(in_array('_task',$columns_list_array))
				   $html2_body.='<td width="'.@$task_width.'" style="text-align:center;'.@$task_color.';'.@$task_bgcolor.';border:1px solid #D6D2D2;">'.(@$row_pad_lines['task']??'').'<strong>'.@$task.'</strong></td>';
				if(in_array('responsible',$columns_list_array))
				   $html2_body.='<td width="'.@$responsible_width.'" style="text-align:center;'.@$responsible_color.';'.@$responsible_bgcolor.';border:1px solid #D6D2D2;">'.(@$row_pad_lines['responsible']??'').'<strong>'.@$responsible.'</strong></td>';
				if(in_array('pass on',$columns_list_array))
				   $html2_body.='<td width="'.@$pass_on_width.'" style="text-align:center;border:1px solid #D6D2D2;'.@$pass_on_bgcolor.'">'.(@$row_pad_lines['pass_on']??'').@$pass_on.'</td>';
				if(in_array('task creation',$columns_list_array))
				   $html2_body.='<td width="'.@$task_creation_width.'" style="text-align:center;border:1px solid #D6D2D2;font-size:10px;'.$task_creation_date_color.';'.$task_creation_date_bgcolor.'">'.(@$row_pad_lines['task_creation']??'').@$task_creation_date_display.'</td>';
				if(in_array('destination date',$columns_list_array))
				   $html2_body.='<td width="'.@$destination_date_width.'" style="text-align:center;border:1px solid #D6D2D2;font-size:10px;'.@$dest_date_color.';'.$dest_date_bgcolor.';'.@$target_date_text_decoration.'">'.(@$row_pad_lines['destination_date']??'').'<strong>'.@$destination_date_display.'</strong></td>';
				if(in_array('progress status',$columns_list_array))
				   $html2_body.='<td width="'.@$progress_status_width.'" style="text-align:center;'.@$progress_status_color.';'.@$progress_status_bgcolor.';border:1px solid #D6D2D2;">'.(@$row_pad_lines['progress_status']??'').'<strong>'.simplifyStatusLabel(@$progress_status).'</strong></td>';
				$html2_body.='</tr>'; 
				
				if(strpos($image1,'Snag') === false && strpos($image2,'Snag') === false) {
				  $html2_body.=   '<tr style="page-break-inside:avoid;">
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
