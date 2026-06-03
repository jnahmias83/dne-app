<?php
session_start();
require_once('tcpdf_min/config/tcpdf_config.php');
require_once('tcpdf_min/tcpdf.php');
include 'functions/functions.php';
error_reporting(E_ALL);
ini_set('display_errors', '1');

$id = @$_GET['id'];
$project_id = @$_GET['project_id'];

$query = $mysqli->prepare("SELECT name,name_he,nickname FROM dne_projects WHERE id = ?");
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

$query = $mysqli->prepare("SELECT * FROM dne_custom_reports WHERE id = ?");
$query->bind_param("i",$id);
$query->execute();
$query->store_result();
$custom_report = fetch_unique($query); 

$sql = @$custom_report->sql_str;
$is_images = @$custom_report->is_images;
$is_colors = @$custom_report->is_colors;
$lang = @$custom_report->lang;
$_SESSION['lang'] = $lang;

if($lang == 'HE') 
  $title = $project->name_he.'<br/>';

else if($lang == 'EN' || $lang == 'FR') 
  $title = $project->name.'<br/>';

if($custom_report->title != '')
  $title .= $custom_report->title.'<br/>';

if($custom_report->subtitle != '')
  $title .= $custom_report->subtitle.'<br/>';

$title .= substr(date('Y-m-d'),8,2).'/'.substr(date('Y-m-d'),5,2).'/'.substr(date('Y-m-d'),0,4);

$pdf_file_name = substr(date('Y-m-d'),8,2).'/'.substr(date('Y-m-d'),5,2).'/'.substr(date('Y-m-d'),0,4).'-';

if($custom_report->id_supplier == 0) {
	if($custom_report->is_project_status_report == 1)
		$pdf_file_name .= $custom_report->pdf_name.'-'.$project->nickname.'.pdf';
	else 
		$pdf_file_name .= 'Tasks Report-'.$custom_report->pdf_name.'-'.$project->nickname.'.pdf';
}
else {
	$query = $mysqli->prepare("SELECT s.nickname AS supplier_nickname 
							  FROM dne_projects_suppliers ps 
							  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
							  WHERE ps.id = ?");
	$query->bind_param("i",$custom_report->id_supplier);
	$query->execute();
	$query->store_result();
	$supplier = fetch_unique($query); 
	$supplier_nickname = $supplier->supplier_nickname;
	$pdf_file_name .= $supplier_nickname.'-Tasks Report-'.$project->nickname.'.pdf';
}

$period_new_tasks = @$custom_report->period_new_tasks;
$columns_list = @$custom_report->columns_list;
$columns_list_array = explode(',',$columns_list);
$colspan_image_tr = sizeof($columns_list_array)+1;

$count_width = "30";
$subject_width = "90";
$area_width = "90";
$task_width = "90";
$responsible_width = "80";
$pass_on_width = "90";
$task_creation_width = "50";
$destination_date_width = "60";
$progress_status_width = "85";     
$description_width = '0%';

if(sizeof($columns_list_array) == 1 && in_array('description',$columns_list_array)) 
   $description_width = '960px';
else if(sizeof($columns_list_array) == 2 && in_array('description',$columns_list_array))
   $description_width = '900px';
else if(sizeof($columns_list_array) == 3 && in_array('description',$columns_list_array)) 
   $description_width = '850px';
else if(sizeof($columns_list_array) == 4 && in_array('description',$columns_list_array))
   $description_width = '690px';
else if(sizeof($columns_list_array) == 5 && in_array('description',$columns_list_array)) 
   $description_width = '600px';
else if(sizeof($columns_list_array) == 6 && in_array('description',$columns_list_array)) 
   $description_width = '530px';
else if(sizeof($columns_list_array) == 7 && in_array('description',$columns_list_array))
   $description_width = '470px';
else if(sizeof($columns_list_array) == 8 && in_array('description',$columns_list_array)) 
   $description_width = '380px';
else if(sizeof($columns_list_array) == 9)
   $description_width = '330px';

$task_type_header = getLang('task_type');
$subject_domain_header = getLang('subject_domain');
$area_subject_header = getLang('area_subject');
$description_header = getLang('description');
$responsible_header = getLang('responsible');
$transfer_confirm_header = getLang('transfer_confirm');
$task_creation_header = getLang('task_creation');
$destination_date_header = getLang('destination_date');
$progress_status_header = getLang('progress_status');

$query = $mysqli->prepare("SELECT * FROM dne_projects WHERE id = ?");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$project = fetch_unique($query);

if($lang == 'HE') {
  $dir_table = 'rtl';
  $style_table = "margin-top:25px;margin-left:1%;";
  $text_align = 'text-align: right';
  $padding = 'padding-right';
}
  
else if($lang == 'EN' || $lang == 'FR') {
  $dir_table = 'ltr';
  $style_table = "margin-top:25px;margin-right:1%;";
  $text_align = 'text-align: left';
  $padding = 'padding-left';
}

$counts_array = array();
$images_array = array();
$is_appears_img_array = array();
$chapters_array = array();

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

$_SESSION['id_responsibles_part'] = $id_responsibles_part;

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
		$query = $mysqli->prepare("SELECT name_he FROM dne_progress_status WHERE id = ?");
		$query->bind_param("i",$id_progress_status_array[$i]);
		$query->execute();
		$query->store_result();
		$query = fetch_unique($query);
		
		if($query->name_he == 'ארכיון' && sizeof($id_progress_status_array) == 1)
		   $sql = str_replace('m.is_appears = 1','m.is_appears = 0',$sql);
		else if($query->name_he == 'ארכיון' && sizeof($id_progress_status_array) > 1) {
			$sql = str_replace('m.is_appears = 1','m.is_appears IN(0,1)',$sql);
		}
	}
}

class PDF extends TCPDF {
    public function Footer() { 
		$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		
        if ($mysqli->connect_error) {
            die("Connection failed: " . $mysqli->connect_error);
        }
        
		$mysqli->set_charset("utf8");
		
		$pdf_file_name = substr(date('Y-m-d'),0,4).substr(date('Y-m-d'),5,2).substr(date('Y-m-d'),8,2).'-';
		
        $query = "SELECT id_project,pdf_name,id_supplier,is_project_status_report FROM dne_custom_reports WHERE id =".@$_GET['id'];
        $result = $mysqli->query($query);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $id_project = $row['id_project'];		
			$pdf_name = $row['pdf_name'];	   
		   
			$id_supplier = $row['id_supplier'];
			$is_project_status_report = $row['is_project_status_report'];
        } else {
            $id_project = $pdf_name = $id_supplier = $is_project_status_report = 'No Data Found';
        }
		
		if($id_supplier > 0) {
			$query = "SELECT s.nickname AS supplier_nickname
					  FROM dne_projects_suppliers ps
					  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
					  WHERE ps.id=".$id_supplier;
			$result = $mysqli->query($query);
			if ($result->num_rows > 0) {
				$row = $result->fetch_assoc();
				$supplier_nickname = $row['supplier_nickname'];
				$pdf_file_name .= $supplier_nickname.'-Tasks Report-';
			} else {
				$supplier_nickname = 'No Data Found';
			}
		}
		else {
			if($is_project_status_report == 1) 
			   $pdf_file_name .= $pdf_name.'-';
		    else
			   $pdf_file_name .= 'Tasks Report-'.$pdf_name.'-';
		}
		
		$query = "SELECT nickname,name,name_he FROM dne_projects WHERE id =".$id_project;
        $result = $mysqli->query($query);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
			$project_nickname = $row['nickname'];
            if($_SESSION['lang'] == 'HE')
			  $project_name = $row['name_he'];
			 else 
			   $project_name = $row['name']; 
        } else {
            $project_nickname = $project_name = 'No Data Found';
        }
		
		$pdf_file_name .= $project_nickname.'.pdf';
	
		$this->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $this->setPrintFooter(true);  
        $this->SetFont('freesans', '', 10);
        $this->SetY(-25);
		$this->Ln();
        $this->Cell(0, 0, '', 'T');

        $this->SetX($this->lMargin);

        $pagePagination = 'Page ' . $this->getAliasNumPage() . ' of ' . $this->getAliasNbPages();
	
	    if($_SESSION['lang'] == 'HE') {
	       $this->setRTL(true);
           $this->Cell(120, 10, $project_name, 0, 0, 'R');
           $this->Cell(20, 10, $pagePagination, 0, 0,'C');
           $this->Cell(146, 10, $pdf_file_name, 0, 0, 'L');
		}
		else {
		   $this->setRTL(false);
           $this->Cell(120, 10, $project_name, 0, 0, 'L');
           $this->Cell(80, 10, $pagePagination, 0, 0,'C');
           $this->Cell(80, 10, $pdf_file_name, 0, 0, 'R');
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
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->setFontSubsetting(true);
$pdf->SetFont('freesans', '', 12);
$pdf->setPrintHeader(false);

$html1_header = '<table><tr><td style="text-align:center;"><img src="uploads/'.@$logo->logo_stread.'" /><br/><br/></td></tr>';

$html1_body = '<tr style="font-size:16px;"><td width="40px;">&nbsp;</td><td style="text-align:center;padding-top:30px;"><span dir="'.$dir_table.'"><strong><u>'.$title.'</u></strong></span></td></tr></table>';
$html1_body.= '<div class="row">';
$html1_body.= '<div class="col-md-12">';
$html1_body.= '<table dir="'.$dir_table.'" style="'.$style_table.'"><tr><td width="1%">&nbsp;</td><td><table cellpadding="4">';
$html1_body.='<tr style="height:50px;background-color:silver;font-size:13px;">';
$html1_body.='<th width="'.@$count_width.'" style="text-align:center;border:1px solid black;font-size:11px;font-weight:bold;">&#x2116;</th>';
if(in_array('subject',$columns_list_array))
  $html1_body.='<th width="'.@$subject_width.'" style="text-align:center;border:1px solid black;font-size:11px;font-weight:bold;">'.$subject_domain_header.'</th>';
if(in_array('area',$columns_list_array))
  $html1_body.='<th width="'.@$area_width.'" style="text-align:center;border:1px solid black;font-size:11px;font-weight:bold;">'.$area_subject_header.'</th>';
if(in_array('description',$columns_list_array))
  $html1_body.='<th width="'.@$description_width.'" style="text-align:center;border:1px solid black;font-size:11px;font-weight:bold;">'.$description_header.'</th>';
if(in_array('_task',$columns_list_array))
  $html1_body.='<th width="'.@$task_width.'" style="text-align:center;border:1px solid black;font-size:11px;font-weight:bold;">'.$task_type_header.'</th>';
if(in_array('responsible',$columns_list_array))
  $html1_body.='<th width="'.@$responsible_width.'" style="text-align:center;border:1px solid black;font-size:11px;font-weight:bold;">'.$responsible_header.'</th>';
if(in_array('pass on',$columns_list_array))
  $html1_body.='<th width="'.@$pass_on_width.'" style="text-align:center;border:1px solid black;font-size:11px;font-weight:bold;">'.$transfer_confirm_header.'</th>';
if(in_array('task creation',$columns_list_array))
  $html1_body.='<th width="'.@$task_creation_width.'" style="text-align:center;border:1px solid black;font-size:10px;font-weight:bold;">'.$task_creation_header.'</th>';
if(in_array('destination date',$columns_list_array))
  $html1_body.='<th width="'.@$destination_date_width.'" style="text-align:center;border:1px solid black;font-size:10px;font-weight:bold;">'.$destination_date_header.'</th>';
if(in_array('progress status',$columns_list_array))
  $html1_body.='<th width="'.@$progress_status_width.'" style="text-align:center;border:1px solid black;font-size:11px;font-weight:bold;">'.$progress_status_header.'</th>';
$html1_body.='</tr>';

$position_where = strpos($sql,"WHERE");
$where_length = strlen($sql)-$position_where;
$where_part_sql = substr($sql,$position_where,$where_length);
$where_part_sql_array = explode(' AND ',$where_part_sql);
							
$chapter_filter = '';
if(strpos($where_part_sql,"m.id_chapter")!== false) {								
    for($i=0;$i<sizeof($where_part_sql_array);$i++) {
		if(strpos($where_part_sql_array[$i],'m.id_chapter') !== false) {
			$where_part_sql_array[$i] = str_replace('m.id_chapter','id',$where_part_sql_array[$i]);
		}
	}

	$where_part_sql = implode(' AND ',$where_part_sql_array);
	$chapter_filter = $where_part_sql;
	$chapter_filter_array = explode('AND ',$chapter_filter);
	$chapter_filter = $chapter_filter_array[2];
	$chapter_filter = ' AND '.$chapter_filter;
}

$sql_chapters = "SELECT * FROM dne_chapters WHERE id_project = ? ".$chapter_filter.' ORDER BY id_display';
$query = $mysqli->prepare($sql_chapters);
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$chapters = fetch($query);

$count1 = 0;
	
foreach($chapters as $item) {	
	$chapter_id = $item->id;
	$chapter_name = $item->name;
	
	$sql_array = explode(' AND ',$sql);

	for($i=1;$i<sizeof($sql_array);$i++) {
		if(strpos($sql_array[$i],'m.id_chapter') !== false) {
			$sql_array[$i] = 'm.id_chapter ='.$chapter_id;
			$sql = implode(' AND ',$sql_array);
		}
	}
	
	if(strpos($sql,'m.id_chapter') !== true) 
	   $sql.= ' AND m.id_chapter ='.$chapter_id;	
   
	$query = $mysqli->prepare($sql.' ORDER BY t.id_display,m.subject,m.id_area,m.destination_date DESC');
	$query->execute(); 
	$query->store_result();
	$meetings_num_rows = $query->num_rows;
	$meetings = fetch($query);
	
	if($meetings_num_rows > 0) {
		$html1_body.='<tr style="background-color:#a3def0;height:40px;font-size:11px;">';
		$html1_body.='<td colspan="12" style="'.$text_align.','.$padding.':5px;border:1px solid black;"><strong>'.@$chapter_name.'</strong></td>';
		$html1_body.='</tr>';
		
		foreach($meetings as $item) {
			$count1++;
			
			$meeting_id = @$item->id;
			$is_priority = @$item->is_priority;
			$id_rdv = @$item->id_rdv;
			$subject = @$item->subject;
			$area = @$item->area;
			$description = html_entity_decode(@$item->description);
			
			$change_status_label = 'שינוי סטטוס';
			$change_dest_date_label = 'דחיית תאריך יעד';
			$query = "SELECT id FROM dne_tasks_actions 
					  WHERE name_he IN('".$change_status_label."','".$change_dest_date_label."')";
			$query = $mysqli->prepare($query);   
			$query->execute();
			$query->store_result();
			$tasks_actions = fetch($query);
			
			$task_actions_ids = ' IN(';
			foreach($tasks_actions as $ta) {
				$task_actions_ids.= $ta->id.',';
			}
			
			$task_actions_ids = substr($task_actions_ids,0,-1);
			$task_actions_ids .= ') ';
			
			$reminder_label = 'מעקב';
			$query = "SELECT id FROM dne_tasks_actions WHERE name_he = ?";
			$query = $mysqli->prepare($query);
			$query->bind_param('s',$reminder_label);   
			$query->execute();
			$query->store_result();
			$query = fetch_unique($query);
			$id_task_action_remark = @$query->id;
			
			$is_remark_appears_pdf = 1;										
			
			$query = $mysqli->prepare("SELECT * FROM dne_tasks_followup 
									  WHERE id_meeting = ? 
									  AND is_remark_appears_pdf = ?
									  AND id_task_action".@$task_actions_ids. 
									  "ORDER BY id DESC LIMIT 1");
			$query->bind_param("ii",$meeting_id,$is_remark_appears_pdf);
			$query->execute();
			$query->store_result();	
			$query = fetch_unique($query);
			$remark_changes_status = @$query->remark;	
			$action_date = @$query->action_date;
											
			$query = $mysqli->prepare("SELECT * FROM dne_tasks_followup 
									  WHERE id_meeting = ? 
									  AND is_remark_appears_pdf = ?
									  AND id_task_action = ? 
									  ORDER BY id DESC LIMIT 1");
			$query->bind_param("iii",$meeting_id,$is_remark_appears_pdf,$id_task_action_remark);
			$query->execute();
			$query->store_result();	
			$query = fetch_unique($query);
			$remark = @$query->remark;	
			$action_date_remark = @$query->action_date;
			
			$color_remark_change_status = $color_remark = 'color:black';
			
			if(@$is_colors) {
				$color_remark_change_status = 'color:green';
				$color_remark = 'color:red';
			}
			
			$task_id = @$item->id_task;
			$responsible_id = @$item->id_responsible;
			$pass_on_id = @$item->id_pass_on;
			$is_change_row_style = @$item->is_change_row_style;
			
			
		    $image1 = @$item->image1;
			$is_appears_img1 = @$item->is_appears_img1;
			
			$image1_height = 180;
			$image1_width;
		                                    
			if(@$item->image1_height > 0) {
				$ratio_image1 = @$item->image1_width/@$item->image1_height;
				$image1_width = $ratio_image1*$image1_height;
			}
											
			$image2 = @$item->image2;
			$is_appears_img2 = @$item->is_appears_img2;
			
			$image2_height = 180;
			$image2_width;
			
			if(@$item->image2_height > 0) {
				$ratio_image2 = @$item->image2_width/@$item->image2_height;
				$image2_width = $ratio_image2*$image2_height;
			}

			$color_num = 'black';
			if(@$is_colors && @$is_priority) {
				$color_num = 'red';
			}		
			
			$update_cell_bg_color = 'background-color:white';
			
			$task_creation_date = @$item->task_creation_date;
			$task_creation_date_display = '';
			if(@$item->task_creation_date != '0000-00-00')
				$task_creation_date_display = substr(@$item->task_creation_date,8,2).'/'.substr(@$item->task_creation_date,5,2);
			
			$destination_date_display = '';
			if(@$item->destination_date != '0000-00-00')
				$destination_date_display = substr(@$item->destination_date,8,2).'/'.substr(@$item->destination_date,5,2);
			
			$progress_status_id = @$item->id_progress_status;
			
			$query = $mysqli->prepare("SELECT name FROM dne_chapters WHERE id = ?");
			$query->bind_param("i",$item->id_chapter);
			$query->execute(); 
			$query->store_result();
			$chapter = fetch_unique($query);
			
			if($item->image1 != '' && $item->is_appears_img1) {
				array_push($counts_array,$count1);
				array_push($images_array,$item->image1);
				array_push($is_appears_img_array,$item->is_appears_img1);
				array_push($chapters_array,$chapter->name);
			}
			
			if($item->image2 != '' && $item->is_appears_img2) {
				array_push($counts_array,$count1);
				array_push($images_array,$item->image2);
				array_push($is_appears_img_array,$item->is_appears_img2);
				array_push($chapters_array,$chapter->name);
			}
			
			$updated_date = @$item->updated_date;
			
			$subject_bg_color = 'background-color:white';
			
			$area_bg_color = 'background-color:white';
			
			$description_bg_color = 'background-color:white';
			
			$query = $mysqli->prepare("SELECT * FROM dne_tasks WHERE id = ?");
			$query->bind_param("i",$item->id_task);
			$query->execute();
			$query->store_result();
			$query = fetch_unique($query);
			$task = @$query->name_he;
			if($lang == 'EN')
			   $task = @$query->name;
			
			$task_color = 'black';
			$task_bgcolor = 'white';
			if($is_colors){
			  $task_color = @$query->color;
			  $task_bgcolor = @$query->bgcolor;
			}
			
			$query = $mysqli->prepare("SELECT * FROM dne_responsibles WHERE id = ?");
			$query->bind_param("i",$item->id_responsible);
			$query->execute();
			$query->store_result();
			$query = fetch_unique($query);
			$responsible = @$query->name;
			
			$responsible_color = 'black';
			$responsible_bgcolor = 'white';
			if($is_colors){
			  $responsible_color = @$query->color;
			  $responsible_bgcolor = @$query->bgcolor;
			}
			
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
			$progress_status = @$query->name_he;
			if($lang == 'EN')
			   $progress_status = @$query->name;
			
			$progress_status_color = 'black';
			$progress_status_bgcolor = 'white';
			if($is_colors){
			  $progress_status_color = @$query->color;
			  $progress_status_bgcolor = @$query->bgcolor;
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
			if($is_colors && $id_rdv > 0)
			  $task_creation_date_color = 'color:green';
			
			$task_creation_date_bg_color = 'background-color:white';
			
			$dest_date_color = 'color:black';
			$dest_date_bg_color = 'background-color:white';
			
			if($is_colors && @$item->destination_date < date('Y-m-d')) { 
			   $dest_date_color = 'color:red;';
			}
			
			if($is_colors && $is_change_row_style) {
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
			
			if(@$period_new_tasks === 'today')
				$end_new_tasks_date = $task_creation_date;
			else if(@$period_new_tasks === 'three_days')
				$end_new_tasks_date = date('Y-m-d',strtotime($task_creation_date . '+3 days'));
			else if(@$period_new_tasks === 'one_week')
				$end_new_tasks_date = date('Y-m-d',strtotime($task_creation_date . '+7 days'));
			else if(@$period_new_tasks === 'two_weeks')
				$end_new_tasks_date = date('Y-m-d',strtotime($task_creation_date . '+14 days'));
			else if(@$period_new_tasks === 'one_month')
				$end_new_tasks_date = date('Y-m-d',strtotime($task_creation_date . '+1 month'));
			else if(@$period_new_tasks === 'two_months')
				$end_new_tasks_date = date('Y-m-d',strtotime($task_creation_date . '+2 months'));
			else if(@$period_new_tasks === 'one_year')
				$end_new_tasks_date = date('Y-m-d',strtotime($task_creation_date . '+1 year'));
			else if(@$period_new_tasks === 'two_years')
				$end_new_tasks_date = date('Y-m-d',strtotime($task_creation_date . '+2 years'));
			
			if(strlen(@$period_new_tasks) > 1 && (date('Y-m-d') <= @$end_new_tasks_date) && (@$progress_status != 'בוצע/נמסר') && (@$task != 'בקרת איכות')) {
				$update_cell_bg_color = 'background-color:'.$global_bgcolor_new_task->bgcolor;
				$subject_bg_color = 'background-color:'.$global_bgcolor_new_task->bgcolor;
				$area_bg_color = 'background-color:'.$global_bgcolor_new_task->bgcolor;
				$description_bg_color = 'background-color:'.$global_bgcolor_new_task->bgcolor;
				$pass_on_bg_color = 'background-color:'.$global_bgcolor_new_task->bgcolor;
				$task_creation_date_bg_color = 'background-color:'.$global_bgcolor_new_task->bgcolor;
				$dest_date_bg_color = 'background-color:'.$global_bgcolor_new_task->bgcolor;
			}
														
			$html1_body.='<tr style="font-size:11px;">';	
            $html1_body.='<td width="'.@$count_width.'" style="text-align:center;'.@$update_cell_bg_color.';border:1px solid black;color:'.$color_num.'">'.@$count1.'</td>';
            if(in_array('subject',$columns_list_array))
			  $html1_body.='<td width="'.@$subject_width.'" style="'.setTextAlign($subject).','.setPadding($subject).':5px;'.@$subject_bg_color.';border:1px solid black;">'.@$subject.'</td>';	
			if(in_array('area',$columns_list_array))
			  $html1_body.='<td width="'.@$area_width.'" style="'.setTextAlign($area).','.setPadding($area).':5px;'.@$area_bg_color.';border:1px solid black;">'.@$area.'</td>';
			
		    if(in_array('description',$columns_list_array))
			   $html1_body.='<td width="'.@$description_width.'" style="'.setTextAlign($description).';'.setPadding($description).':5px;'.@$description_bg_color.';border:1px solid black;">';
			$html1_body.= nl2br(@$description);
			
			if(@$remark_changes_status != '') 
				$html1_body.= '<br/><div style="font-weight:bold;'.@$color_remark_change_status.'">['.substr(@$action_date,8,2).'/'.substr(@$action_date,5,2).'] - '.@$remark_changes_status.'</div>';
			if(@$remark != '') 
				$html1_body.= '<div style="font-weight:bold;'.@$color_remark.'">['.substr(@$action_date_remark,8,2).'/'.substr(@$action_date_remark,5,2).'] - '.@$remark.'</div>';
			
			$html1_body.= '</td>';
			
			if(in_array('_task',$columns_list_array))
			   $html1_body.='<td width="'.@$task_width.'" style="text-align:center;color:'.@$task_color.';background-color:'.@$task_bgcolor.';border:1px solid black;"><strong>'.@$task.'</strong></td>'; 			
			if(in_array('responsible',$columns_list_array))
			  $html1_body.='<td width="'.@$responsible_width.'" style="text-align:center;color:'.@$responsible_color.';background-color:'.@$responsible_bgcolor.';border:1px solid black;"><strong>'.@$responsible.'</strong></td>';
			if(in_array('pass on',$columns_list_array))
			  $html1_body.='<td width="'.@$pass_on_width.'" style="text-align:center;border:1px solid black;'.@$pass_on_bg_color.'">'.@$pass_on.'</td>';
			if(in_array('task creation',$columns_list_array))
			  $html1_body.='<td width="'.@$task_creation_width.'" style="text-align:center;border:1px solid black;font-size:10px;'.$task_creation_date_color.';'.$task_creation_date_bg_color.'">'.@$task_creation_date_display.'</td>';
			if(in_array('destination date',$columns_list_array))
			  $html1_body.='<td width="'.@$destination_date_width.'" style="text-align:center;border:1px solid black;font-size:10px;'.@$dest_date_color.';'.$dest_date_bg_color.'"><strong>'.@$destination_date_display.'</strong></td>';
			if(in_array('progress status',$columns_list_array))
			  $html1_body.='<td width="'.@$progress_status_width.'" style="text-align:center;color:'.@$progress_status_color.';background-color:'.@$progress_status_bgcolor.';border:1px solid black;"><strong>'.@$progress_status.'</strong></td>';
			$html1_body.='</tr>';
			
			 if($is_images == 1 && (($item->image1 != '' && $item->is_appears_img1) || ($item->image2 != '' && $item->is_appears_img2)) && strpos($image1,'Snag') === false && strpos($image2,'Snag') === false) {
			    $html1_body.=   '<tr>
					                <td colspan="'.@$colspan_image_tr.'">
									    <img src="uploads/'.@$item->image1.'" width="'.@$image1_width.'" height="'.@$image1_height.'" style="object-fit:fixed;" />';
				if(@$item->image2 != '' && @$item->is_appears_img2 == 1) 
				   $html1_body.= '&nbsp;&nbsp;&nbsp;<img src="uploads/'.@$item->image2.'" width="'.@$image2_width.'" height="'.@$image2_height.'" style="object-fit:fixed;" />';
				$html1_body.= '</td></tr>';
			 }
		}
	}
}
							
$html1_body.='</table></td></tr></table>';
$html1_body.='</div>';
$html1_body.='</div>';

$html1 = $html1_header.$html1_body;

if($lang == 'HE')
  $pdf->setRTL(true);
$pdf->AddPage();
$pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));
$pdf->writeHTMLCell(0, 0, '', '', $html1, 0, 1, 0, true, '', true);


$html2_header = '<table><tr><td style="text-align:center;font-size:18px;">ריכוז משימות עם תמונות</td></tr></table>';
$html2_body.= '<div class="row">';
$html2_body.= '<div class="col-md-12">';
$html2_body.= '<table dir="'.$dir_table.'" style="'.$style_table.'"><tr><td width="1%">&nbsp;</td><td><table cellpadding="4">';
$html2_body.='<tr style="height:50px;background-color:silver;font-size:13px;">';
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
  $html2_body.='<th width="'.@$task_creation_width.'" style="text-align:center;border:1px solid black;font-size:10px;font-weight:bold;">'.$task_creation_header.'</th>';
if(in_array('destination date',$columns_list_array))
  $html2_body.='<th width="'.@$destination_date_width.'" style="text-align:center;border:1px solid black;font-size:10px;font-weight:bold;">'.$destination_date_header.'</th>';
if(in_array('progress status',$columns_list_array))
  $html2_body.='<th width="'.@$progress_status_width.'" style="text-align:center;border:1px solid black;font-size:11px;font-weight:bold;">'.$progress_status_header.'</th>';
$html2_body.='</tr>';

$is_html2_appears = false;

$count2 = 0;

foreach($chapters as $item) {	
	$chapter_id = $item->id;
	$chapter_name = $item->name;
	
	$sql_array = explode(' AND ',$sql);

	for($i=1;$i<sizeof($sql_array);$i++) {
		if(strpos($sql_array[$i],'id_chapter') !== false) {
			$sql_array[$i] = 'id_chapter ='.$chapter_id;
			$sql = implode(' AND ',$sql_array);
		}
	} 
	
	if(strpos($sql,'id_chapter') !== true) 
	   $sql.= ' AND id_chapter ='.$chapter_id;
   
	$query = $mysqli->prepare($sql);
	$query->execute();
	$query->store_result();
	$meetings = fetch($query);
											
	$counter_with_image = 0;
	foreach ($meetings as $item) {							
		if($item->image1 != '' && $item->is_appears_img1) 
			$counter_with_image++;
	}							
	
	$query = $mysqli->prepare($sql." ORDER BY t.id_display,m.subject,m.id_area,m.destination_date DESC");
	$query->execute(); 
	$query->store_result();
	$meetings = fetch($query);
	
	if($counter_with_image > 0) 
	   $is_html2_appears = true;
	
	if($counter_with_image > 0) {
		$html2_body.='<tr style="background-color:#a3def0;height:40px;font-size:11px;">';
		$html2_body.='<td colspan="12" style="'.$text_align.','.$padding.':5px;border:1px solid black;"><strong>'.@$chapter_name.'</strong></td>';
		$html2_body.='</tr>';
			
		foreach($meetings as $item) {
			$meeting_id = @$item->id;
			$is_priority = @$item->is_priority;
			$id_rdv = @$item->id_rdv;
			$subject = @$item->subject;
			$area = @$item->area;
			$description = html_entity_decode(@$item->description);
			
			$change_status_label = 'שינוי סטטוס';
			$change_dest_date_label = 'דחיית תאריך יעד';
			$query = "SELECT id FROM dne_tasks_actions 
					  WHERE name_he IN('".$change_status_label."','".$change_dest_date_label."')";
			$query = $mysqli->prepare($query);   
			$query->execute();
			$query->store_result();
			$tasks_actions = fetch($query);
			
			$task_actions_ids = ' IN(';
			foreach($tasks_actions as $ta) {
				$task_actions_ids.= $ta->id.',';
			}
			
			$task_actions_ids = substr($task_actions_ids,0,-1);
			$task_actions_ids .= ') ';
			
			$reminder_label = 'מעקב';
			$query = "SELECT id FROM dne_tasks_actions WHERE name_he = ?";
			$query = $mysqli->prepare($query);
			$query->bind_param('s',$reminder_label);   
			$query->execute();
			$query->store_result();
			$query = fetch_unique($query);
			$id_task_action_remark = @$query->id;
			
			$is_remark_appears_pdf = 1;										
			
			$query = $mysqli->prepare("SELECT * FROM dne_tasks_followup 
									  WHERE id_meeting = ? 
									  AND is_remark_appears_pdf = ?
									  AND id_task_action".@$task_actions_ids. 
									  "ORDER BY id DESC LIMIT 1");
			$query->bind_param("ii",$meeting_id,$is_remark_appears_pdf);
			$query->execute();
			$query->store_result();	
			$query = fetch_unique($query);
			$remark_changes_status = @$query->remark;	
			$action_date = @$query->action_date;
											
			$query = $mysqli->prepare("SELECT * FROM dne_tasks_followup 
									  WHERE id_meeting = ? 
									  AND is_remark_appears_pdf = ?
									  AND id_task_action = ? 
									  ORDER BY id DESC LIMIT 1");
			$query->bind_param("iii",$meeting_id,$is_remark_appears_pdf,$id_task_action_remark);
			$query->execute();
			$query->store_result();	
			$query = fetch_unique($query);
			$remark = @$query->remark;	
			$action_date_remark = @$query->action_date;
			
			$color_remark_change_status = $color_remark = 'color:black';
			
			if(@$is_colors) {
				$color_remark_change_status = 'color:green';
				$color_remark = 'color:red';
			}
			
			$task_id = @$item->id_task;
			$responsible_id = @$item->id_responsible;
			$pass_on_id = @$item->id_pass_on;
			$is_change_row_style = @$item->is_change_row_style;
			
			$image1 = @$item->image1;
			$is_appears_img1 = @$item->is_appears_img1;
			
			$image1_height = 180;
			$image1_width;
		                                    
			if(@$item->image1_height > 0) {
				$ratio_image1 = @$item->image1_width/@$item->image1_height;
				
				if(@$item->image1_width > @$item->image1_height)
				  $image1_width = $ratio_image1*$image1_height;
				else 
				  $image1_width = $image1_height/$ratio_image1;	
			}
											
			$image2 = @$item->image2;
			$is_appears_img2 = @$item->is_appears_img2;
			
			$image2_height = 180;
			$image2_width;
			
			if(@$item->image2_height > 0) {
				$ratio_image2 = @$item->image2_width/@$item->image2_height;
				
				if(@$item->image2_width > @$item->image2_height)
				  $image2_width = $ratio_image2*$image2_height;
				else 
				  $image2_width = $image2_height/$ratio_image2;	
			}

			$color_num = 'black';
			if(@$is_colors && @$is_priority) {
				$color_num = 'red';
			}
			
			$update_cell_bg_color = 'background-color:white';
			
			$task_creation_date = @$item->task_creation_date;
			$task_creation_date_display = '';
			if(@$item->task_creation_date != '0000-00-00')
				$task_creation_date_display = substr(@$item->task_creation_date,8,2).'/'.substr(@$item->task_creation_date,5,2);
			
			$destination_date_display = '';
			if(@$item->destination_date != '0000-00-00')
				$destination_date_display = substr(@$item->destination_date,8,2).'/'.substr(@$item->destination_date,5,2);
			
			$progress_status_id = @$item->id_progress_status;
			
			$query = $mysqli->prepare("SELECT name FROM dne_chapters WHERE id = ?");
			$query->bind_param("i",$item->id_chapter);
			$query->execute(); 
			$query->store_result();
			$chapter = fetch_unique($query);
			
			if($item->image1 != '' && $item->is_appears_img1) {
				array_push($counts_array,$count1);
				array_push($images_array,$item->image1);
				array_push($is_appears_img_array,$item->is_appears_img1);
				array_push($chapters_array,$chapter->name);
			}
			
			if($item->image2 != '' && $item->is_appears_img2) {
				array_push($counts_array,$count1);
				array_push($images_array,$item->image2);
				array_push($is_appears_img_array,$item->is_appears_img2);
				array_push($chapters_array,$chapter->name);
			}
			
			$updated_date = @$item->updated_date;
			
			$subject_bg_color = 'background-color:white';
			
			$area_bg_color = 'background-color:white';
			
			$description_bg_color = 'background-color:white';
			
			$query = $mysqli->prepare("SELECT * FROM dne_tasks WHERE id = ?");
			$query->bind_param("i",$item->id_task);
			$query->execute();
			$query->store_result();
			$query = fetch_unique($query);
			$task = @$query->name_he;
			if($lang == 'EN')
			   $task = @$query->name;
			
			$task_color = 'black';
			$task_bgcolor = 'white';
			if($is_colors){
			  $task_color = @$query->color;
			  $task_bgcolor = @$query->bgcolor;
			}
			
			$query = $mysqli->prepare("SELECT * FROM dne_responsibles WHERE id = ?");
			$query->bind_param("i",$item->id_responsible);
			$query->execute();
			$query->store_result();
			$query = fetch_unique($query);
			$responsible = @$query->name;
			
			$responsible_color = 'black';
			$responsible_bgcolor = 'white';
			if($is_colors){
			  $responsible_color = @$query->color;
			  $responsible_bgcolor = @$query->bgcolor;
			}
			
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
			$progress_status = @$query->name_he;
			if($lang == 'EN')
			   $progress_status = @$query->name;
			
			$progress_status_color = 'black';
			$progress_status_bgcolor = 'white';
			if($is_colors){
			  $progress_status_color = @$query->color;
			  $progress_status_bgcolor = @$query->bgcolor;
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
			if($is_colors && $id_rdv > 0) 
			  $task_creation_date_color = 'color:green';
			
			$task_creation_date_bg_color = 'background-color:white';
			
			$dest_date_color = 'color:black';
			$dest_date_bg_color = 'background-color:white';
			
			if($is_colors && @$item->destination_date < date('Y-m-d')) { 
			   $dest_date_color = 'color:red;';
			}
			
			if($is_colors && $is_change_row_style) {
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
			
			if(@$project->period_new_tasks === 'today')
				$end_new_tasks_date = $task_creation_date;
			else if(@$project->period_new_tasks === 'three_days')
				$end_new_tasks_date = date('Y-m-d',strtotime($task_creation_date . '+3 days'));
			else if(@$project->period_new_tasks === 'one_week')
				$end_new_tasks_date = date('Y-m-d',strtotime($task_creation_date . '+7 days'));
			else if(@$project->period_new_tasks === 'two_weeks')
				$end_new_tasks_date = date('Y-m-d',strtotime($task_creation_date . '+14 days'));
			else if(@$project->period_new_tasks === 'one_month')
				$end_new_tasks_date = date('Y-m-d',strtotime($task_creation_date . '+1 month'));
			else if(@$project->period_new_tasks === 'two_months')
				$end_new_tasks_date = date('Y-m-d',strtotime($task_creation_date . '+2 months'));
			else if(@$project->period_new_tasks === 'one_year')
				$end_new_tasks_date = date('Y-m-d',strtotime($task_creation_date . '+1 year'));
			else if(@$project->period_new_tasks === 'two_years')
				$end_new_tasks_date = date('Y-m-d',strtotime($task_creation_date . '+2 years'));
			
			if(strlen(@$project->period_new_tasks) > 1 && (date('Y-m-d') <= @$end_new_tasks_date) && (@$progress_status != 'בוצע/נמסר') && (@$task != 'בקרת איכות')) {
				$update_cell_bg_color = 'background-color:'.$global_bgcolor_new_task->bgcolor;
				$subject_bg_color = 'background-color:'.$global_bgcolor_new_task->bgcolor;
				$area_bg_color = 'background-color:'.$global_bgcolor_new_task->bgcolor;
				$description_bg_color = 'background-color:'.$global_bgcolor_new_task->bgcolor;
				$pass_on_bg_color = 'background-color:'.$global_bgcolor_new_task->bgcolor;
				$task_creation_date_bg_color = 'background-color:'.$global_bgcolor_new_task->bgcolor;
				$dest_date_bg_color = 'background-color:'.$global_bgcolor_new_task->bgcolor;
			}
			
            if($image1 != '' && $is_appears_img1) {	
                $count2++;			
				
				$html2_body.='<tr style="font-size:11px;">';	
				$html2_body.='<td width="'.@$count_width.'" style="text-align:center;'.@$update_cell_bg_color.';border:1px solid black;color:'.$color_num.'">'.@$count2.'</td>';
				if(in_array('subject',$columns_list_array))
				  $html2_body.='<td width="'.@$subject_width.'" style="'.setTextAlign($subject).','.setPadding($subject).':5px;'.@$subject_bg_color.';border:1px solid black;">'.@$subject.'</td>';	
				if(in_array('area',$columns_list_array))
				  $html2_body.='<td width="'.@$area_width.'" style="'.setTextAlign($area).','.setPadding($area).':5px;'.@$area_bg_color.';border:1px solid black;">'.@$area.'</td>';
				
				if(in_array('description',$columns_list_array))
				   $html2_body.='<td width="'.@$description_width.'" style="'.setTextAlign($description).';'.setPadding($description).':5px;'.@$description_bg_color.';border:1px solid black;">';
				$html2_body.= nl2br(@$description);
				
				if(@$remark_changes_status != '') 
				   $html2_body.= '<br/><div style="font-weight:bold;'.@$color_remark_change_status.'">['.substr(@$action_date,8,2).'/'.substr(@$action_date,5,2).'] - '.@$remark_changes_status.'</div>';
			    if(@$remark != '') 
				   $html2_body.= '<div style="font-weight:bold;'.@$color_remark.'">['.substr(@$action_date_remark,8,2).'/'.substr(@$action_date_remark,5,2).'] - '.@$remark.'</div>';
			
				$html2_body.= '</td>';
				
				if(in_array('_task',$columns_list_array)) 
			    $html2_body.='<td width="'.@$task_width.'" style="text-align:center;color:'.@$task_color.';background-color:'.@$task_bgcolor.';border:1px solid black;"><strong>'.@$task.'</strong></td>';			
				if(in_array('responsible',$columns_list_array))
				  $html2_body.='<td width="'.@$responsible_width.'" style="text-align:center;color:'.@$responsible_color.';background-color:'.@$responsible_bgcolor.';border:1px solid black;"><strong>'.@$responsible.'</strong></td>';
				if(in_array('pass on',$columns_list_array))
				  $html2_body.='<td width="'.@$pass_on_width.'" style="text-align:center;border:1px solid black;'.@$pass_on_bg_color.'">'.@$pass_on.'</td>';
				if(in_array('task creation',$columns_list_array))
				  $html2_body.='<td width="'.@$task_creation_width.'" style="text-align:center;border:1px solid black;font-size:10px;'.$task_creation_date_color.';'.$task_creation_date_bg_color.'">'.@$task_creation_date_display.'</td>';
				if(in_array('destination date',$columns_list_array))
				  $html2_body.='<td width="'.@$destination_date_width.'" style="text-align:center;border:1px solid black;font-size:10px;'.@$dest_date_color.';'.$dest_date_bg_color.'"><strong>'.@$destination_date_display.'</strong></td>';
				if(in_array('progress status',$columns_list_array))
				  $html2_body.='<td width="'.@$progress_status_width.'" style="text-align:center;color:'.@$progress_status_color.';background-color:'.@$progress_status_bgcolor.';border:1px solid black;"><strong>'.@$progress_status.'</strong></td>';
				$html2_body.='</tr>';
				
				if(strpos($image1,'Snag') === false && strpos($image2,'Snag') === false) {
				  $html2_body.=   '<tr>
					                    <td colspan="'.@$colspan_image_tr.'">
									        <img src="uploads/'.@$item->image1.'" width="'.@$image1_width.'" height="'.@$image1_height.'" style="object-fit:fixed;" />';
					if(@$item->image2 != '' && @$item->is_appears_img2 == 1) 
					   $html2_body.= '&nbsp;&nbsp;&nbsp;<img src="uploads/'.@$item->image2.'" width="'.@$image2_width.'" height="'.@$image2_height.'" style="object-fit:fixed;" />';
					$html2_body.= '</td></tr>';
			    }
			}	
		}
	}
}
							
$html2_body.='</table></td></tr></table>';
$html2_body.='</div>';
$html2_body.='</div>';

if($is_images == 2 && $is_html2_appears) {
	$html2 = $html2_header.$html2_body;
	if($lang == 'HE')
	  $pdf->setRTL(true);
	$pdf->AddPage();
	$pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));
	$pdf->writeHTMLCell(0, 0, '', '', $html2, 0, 1, 0, true, '', true);
}

ob_end_clean();

$pdf->Output($pdf_file_name,'I');
?>