<?php
include 'include/header.php';
include 'functions/functions.php';
include "functions/PHPMailer/PHPMailer.php";
include 'functions/PHPMailer/SMTP.php';
include 'functions/PHPMailer/Exception.php';

$project_id = @$_GET['project_id'];

$period_new_task_filter = 'undefined';
if(@$_GET['period_new_task_filter'])	
  $period_new_task_filter = @$_GET['period_new_task_filter'];

$period_late_filter = 'undefined';
if(@$_GET['period_late_filter'])	
  $period_late_filter = @$_GET['period_late_filter'];

$task_filter = 'undefined';
if(@$_GET['task_filter'])	
  $task_filter = @$_GET['task_filter'];

$progress_status_filter = 'undefined';
if(@$_GET['progress_status_filter'])	
  $progress_status_filter = @$_GET['progress_status_filter'];

$supplier_filter = 'undefined';
if(@$_GET['supplier_filter'] && @$_GET['supplier_filter'] != 'undefined')	
  $supplier_filter = substr(@$_GET['supplier_filter'],0,strpos(@$_GET['supplier_filter'],"-"));

$search_filter = '';
if(@$_GET['search_filter'])	
  $search_filter = @$_GET['search_filter'];

$is_specific_filter = @$_GET['is_specific_filter'];
$row = @$_GET['row'];

$is_btn_new_bgcolor = @$_GET['is_btn_new_bgcolor'];

$sort_select_1 = @$_GET['sort_select_1'];
$sort_select_2 = @$_GET['sort_select_2'];
$sort_select_2_order = @$_GET['sort_select_2_order'];
$sort_select_3 = @$_GET['sort_select_3'];
$sort_select_3_order = @$_GET['sort_select_3_order'];

$query = $mysqli->prepare("SELECT * FROM dne_projects WHERE id = ?");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$project = fetch_unique($query);

$query = $mysqli->prepare("SELECT * FROM dne_global_bgcolor_new_task LIMIT 1");
$query->execute();
$query->store_result();
$global_bgcolor_new_task = fetch_unique($query);

$query = $mysqli->prepare("SELECT * FROM dne_inputs_colors LIMIT 1");
$query->execute(); 
$query->store_result();
$bg_color_inputs = fetch_unique($query);

$query = $mysqli->prepare("SELECT * FROM dne_log_current_report WHERE id_project = ?");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$log_custom_report = fetch_unique($query);
$id_custom_report = @$log_custom_report->id_custom_report;
$id_rdv_report = @$log_custom_report->id_rdv_report;

if($id_custom_report > 0) 
  $id_report = @$id_custom_report;

if($id_rdv_report > 0){
  $id_report = @$id_rdv_report;
  
  $archive_label = 'ארכיון';
  $query = "SELECT id FROM dne_progress_status WHERE name_he = ? AND id_project = ?";
  $query = $mysqli->prepare($query);
  $query->bind_param('si',$archive_label,$project_id);   
  $query->execute();
  $query->store_result();
  $query = fetch_unique($query);
  $ps_archive_id = $query->id;
  
  $query = $mysqli->prepare("SELECT rdv_name,rdv_lang FROM dne_rdv WHERE id = ?");
  $query->bind_param("i",$id_rdv_report);
  $query->execute();
  $query->store_result();
  $query = fetch_unique($query);
  $rdv_name = @$query->rdv_name;
  $rdv_lang = @$query->rdv_lang;
}

$query = $mysqli->prepare("SELECT * FROM dne_custom_reports 
                          WHERE id_project = ? 
						  ORDER BY is_project_status_report DESC");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$custom_reports_num_rows = $query->num_rows;
$custom_reports = fetch($query);

$title = '';

if($id_custom_report > 0){
	$query = $mysqli->prepare("SELECT * FROM dne_custom_reports 
	                          WHERE id = ?");
	$query->bind_param("i",$id_custom_report);
	$query->execute();
	$query->store_result();
		
	if($query->num_rows > 0){
		$custom_report = fetch_unique($query);		
			
		if(!$is_specific_filter){
			$subject_str = @$custom_report->subject;
			$area_str = @$custom_report->area;
			$description_str = @$custom_report->description;
			$chapters_ids = rtrim(str_replace(',,',',',@$custom_report->chapters_list??''),',');
			$tasks_types_ids = rtrim(str_replace(',,',',',@$custom_report->task_types_list??''),',');
			$tasks_ids = rtrim(str_replace(',,',',',@$custom_report->tasks_list??''),',');
			$responsibles_ids = rtrim(str_replace(',,',',',@$custom_report->responsibles_list??''),',');
			$and_or_responsibles = @$custom_report->and_or_responsibles;
			$pass_ons_ids = rtrim(str_replace(',,',',',@$custom_report->pass_ons_list??''),',');
			$progress_status_ids = rtrim(str_replace(',,',',',@$custom_report->progress_status_list??''),',');
			$period_creation_date = @$custom_report->period_creation_date;
			$creation_date_start = @$custom_report->creation_date_start;
			$creation_date_end = @$custom_report->creation_date_end;
			$period_destination_date = @$custom_report->period_destination_date;
			$destination_date_start = @$custom_report->destination_date_start;
			$destination_date_end = @$custom_report->destination_date_end;
			$sql = @$custom_report->sql_str;
			$is_images = @$custom_report->is_images;
			$is_colors = @$custom_report->is_colors;
			$cr_lang = @$custom_report->lang;
			if ($cr_lang === 'EN' || $cr_lang === '1') $lang = 'EN';
			elseif ($cr_lang === 'HE' || $cr_lang === '0') $lang = 'HE';
			else $lang = !empty(@$project->lang) ? @$project->lang : 'HE';
			$_SESSION['lang'] = $lang;
			$_SESSION['project_lang'] = @$project->lang;
			
			if($lang == 'HE') 
			   $project_name = '<span class="fontSize26 font-weight-bold font-family-david">פרוייקט '.htmlspecialchars($project->name_he).'</span>';
		    else
			   $project_name = '<span class="fontSize26 font-weight-bold">Project '.htmlspecialchars($project->name).'</span>';
			
	    	if($custom_report->title != '')
			   $title = '<span class="font-weight-bold"><u>'.$custom_report->title.'</u></span>';
		    if($custom_report->subtitle != '')
		       $title .= '<br/><span class="fontSize20 font-weight-bold"><u>'.@$custom_report->subtitle.'</u></span>';
			
			$period_new_tasks = @$custom_report->period_new_tasks;
			$columns_list = @$custom_report->columns_list;
		}
		else {
			$subject_str = @$_SESSION['filter_subject'];
			$area_str = @$_SESSION['filter_area'];
			$description_str = @$_SESSION['filter_description'];
			$chapters_ids = rtrim(str_replace(',,',',',@$_SESSION['filter_chapters_list']??''),',');
			$tasks_types_ids = rtrim(str_replace(',,',',',@$_SESSION['filter_tasks_types_list']??''),',');
			$tasks_ids = rtrim(str_replace(',,',',',@$_SESSION['filter_tasks_list']??''),',');
			$responsibles_ids = rtrim(str_replace(',,',',',@$_SESSION['filter_responsibles_list']??''),',');
			$and_or_responsibles = @$_SESSION['filter_and_or_responsibles'];
			$pass_ons_ids = rtrim(str_replace(',,',',',@$_SESSION['filter_pass_ons_list']??''),',');
			$progress_status_ids = rtrim(str_replace(',,',',',@$_SESSION['filter_progress_status_list']??''),',');
			$period_creation_date = @$_SESSION['filter_period_creation_date'];
			$creation_date_start = @$_SESSION['filter_creation_date_start'];
			$creation_date_end = @$_SESSION['filter_creation_date_end'];
			$period_destination_date = @$_SESSION['filter_period_destination_date'];
			$destination_date_start = @$_SESSION['filter_destination_date_start'];
			$destination_date_end = @$_SESSION['filter_destination_date_end'];
			$sql = @$_SESSION['filter_sql'];
			$is_images = @$_SESSION['filter_is_images'];
			$is_colors = @$_SESSION['filter_is_colors'];
			$cr_lang = @$custom_report->lang;
			if ($cr_lang === 'EN' || $cr_lang === '1') $lang = 'EN';
			elseif ($cr_lang === 'HE' || $cr_lang === '0') $lang = 'HE';
			else $lang = !empty(@$project->lang) ? @$project->lang : 'HE';
			$_SESSION['lang'] = $lang;
			$_SESSION['project_lang'] = @$project->lang;
			
			if($lang == 'HE') 
			    $project_name = '<span class="fontSize26 font-weight-bold font-family-david">פרוייקט '.htmlspecialchars($project->name_he).'</span>';
		    else
				$project_name = '<span class="fontSize26 font-weight-bold">Project '.htmlspecialchars($project->name).'</span>';

            if($custom_report->title != '')
			   $title = '<span class="font-weight-bold"><u>'.htmlspecialchars($custom_report->title).'</u></span>';
		    if($custom_report->subtitle != '')
		       $title .= '<br/><span class="fontSize20 font-weight-bold"><u>'.htmlspecialchars(@$custom_report->subtitle).'</u></span>';			
         
            $period_new_tasks = @$_SESSION['filter_period_new_tasks'];
			$columns_list = @$_SESSION['filter_columns_list'];
		}
			
		$id_responsibles_part = '';
		if(strpos($sql,"m.id_responsible")!== false){
			$sql_array = explode(' AND ',$sql);
									
			for($i=1;$i<sizeof($sql_array);$i++){
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

		if(strpos($sql,"m.id_progress_status")!== false){
			$sql_array = explode(' AND ',$sql);

			for($i=1;$i<sizeof($sql_array);$i++){
				if(strpos($sql_array[$i],'m.id_progress_status') !== false){
					$id_progress_status_part = $sql_array[$i];
					$id_progress_status_part = str_replace('m.id_progress_status IN(','',$id_progress_status_part);
					$id_progress_status_part = substr($id_progress_status_part, 0, -1);
					$id_progress_status_array = explode(',',$id_progress_status_part);
				}
			}
				
			for($i=0;$i<sizeof($id_progress_status_array);$i++){
				$query = $mysqli->prepare("SELECT name_he FROM dne_progress_status WHERE id = ?");
				$query->bind_param("i",$id_progress_status_array[$i]);
				$query->execute();
				$query->store_result();
				$query = fetch_unique($query);
				
				if(@$query->name_he == 'ארכיון' && sizeof($id_progress_status_array) == 1) 
				   $sql = str_replace('is_appears = 1','is_appears = 0',$sql);
				else if(@$query->name_he == 'ארכיון' && sizeof($id_progress_status_array) > 1) 
				   $sql = str_replace('is_appears = 1','is_appears IN(0,1)',$sql);
		   }
		}
	
		$query = $mysqli->prepare($sql);
		$query->execute();
		$query->store_result();
		$all_meetings_num_rows = $query->num_rows;
		$all_meetings = fetch($query);
		$all_meetings_with_images_num_rows = 0;
		
		foreach($all_meetings as $item){
			if($item->image1 != '' && $item->is_appears_img1)
			   $all_meetings_with_images_num_rows++;
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
	    $lang = @$query->rdv_lang;
		$period_new_tasks = @$query->period_new_tasks;
	    $columns_list = @$query->columns_list;
		
	    $query = $mysqli->prepare("SELECT id_chapter,id_task_type,
		                          id_task,id_responsible,id_pass_on,
								  id_progress_status 
								  FROM dne_meetings 
								  WHERE id_project = ?");
		$query->bind_param("i",$project_id);
		$query->execute();
		$query->store_result();
		$meetings_rdv = fetch($query);

		$chapters_array = array();
		$tasks_types_array = array();
		$tasks_array = array();
		$responsibles_array = array();
		$pass_ons_array = array();
		$progress_status_array = array();
	
		foreach ($meetings_rdv as $item){		
			if(!in_array($item->id_chapter,$chapters_array))
			  array_push($chapters_array,$item->id_chapter);
			
			if(!in_array($item->id_task_type,$tasks_types_array))
			   array_push($tasks_types_array,$item->id_task_type);
			
			if(!in_array($item->id_task,$tasks_array))
			  array_push($tasks_array,$item->id_task);
			
			if(!in_array($item->id_responsible,$responsibles_array))
			   array_push($responsibles_array,$item->id_responsible);
			
			if(!in_array($item->id_pass_on,$pass_ons_array))
			   array_push($pass_ons_array,$item->id_pass_on);
			
			$query = $mysqli->prepare("SELECT name_he FROM dne_progress_status WHERE id = ?");
			$query->bind_param("i",$item->id_progress_status);
			$query->execute();
			$query->store_result();
			$progress_status = fetch_unique($query);
			$progress_status_name = @$progress_status->name_he;
			
			if(!in_array($item->id_progress_status,$progress_status_array) && $progress_status_name != 'ארכיון')
			  array_push($progress_status_array,$item->id_progress_status);
		}
		
		$subject_str = @$_SESSION['filter_subject'];
		$area_str = @$_SESSION['filter_area'];
		$description_str = @$_SESSION['filter_description'];
		$chapters_ids = implode(',',@$chapters_array);
		$tasks_types_ids = implode(',',@$tasks_types_array);
		$tasks_ids = implode(',',@$tasks_array);
		$responsibles_ids = implode(',',@$responsibles_array);
		$and_or_responsibles = 'AND';
		$pass_ons_ids = implode(',',@$pass_ons_array);
		$progress_status_ids = implode(',',@$progress_status_array);
		$creation_date_start = '0000-00-00';
		$creation_date_end = '0000-00-00';
	    $destination_date_start = '0000-00-00';
		$destination_date_end = '0000-00-00';
		
	    $is_appears = 1;
	    $query = $mysqli->prepare("SELECT * FROM dne_meetings 
		                          WHERE is_appears = ?
								  AND id_progress_status <> ?
		                          AND FIND_IN_SET(?,ids_rdv) > 0");
	    $query->bind_param("iis",$is_appears,$ps_archive_id,$id_rdv_report);
	    $query->execute();
	    $query->store_result();
	    $all_meetings_num_rows = $query->num_rows;
        $all_meetings = fetch($query);
	    $all_meetings_with_images_num_rows = 0;
		
	    foreach($all_meetings as $item){
			if($item->image1 != '' && $item->is_appears_img1)
			   $all_meetings_with_images_num_rows++;
	    }
		
		$sql = "SELECT id FROM dne_meetings WHERE id_project =".@$project_id." AND is_appears = 1 AND FIND_IN_SET(?,ids_rdv) > 0";
    }
	else {
		$subject_str = @$_SESSION['filter_subject'];
		$area_str = @$_SESSION['filter_area'];
		$description_str = @$_SESSION['filter_description'];
		$chapters_ids = @$_SESSION['filter_chapters_list'];
		$tasks_types_ids = @$_SESSION['filter_tasks_types_list'];
		$tasks_ids = @$_SESSION['filter_tasks_list'];
		$responsibles_ids = @$_SESSION['filter_responsibles_list'];
	    $and_or_responsibles = @$_SESSION['filter_and_or_responsibles'];
		$pass_ons_ids = @$_SESSION['filter_pass_ons_list'];
		$progress_status_ids = @$_SESSION['filter_progress_status_list'];
		$period_creation_date = @$_SESSION['filter_period_creation_date'];
		$creation_date_start = @$_SESSION['filter_creation_date_start'];
		$creation_date_end = @$_SESSION['filter_creation_date_end'];
		$period_destination_date = @$_SESSION['filter_period_destination_date'];
		$destination_date_start = @$_SESSION['filter_destination_date_start'];
		$destination_date_end = @$_SESSION['filter_destination_date_end'];	
		$sql = @$_SESSION['filter_sql'];
		$is_images = @$_SESSION['filter_is_images'];
		$is_colors = @$_SESSION['filter_is_colors'];
		$lang = !empty($rdv_lang) ? $rdv_lang : @$project->lang;
		$period_new_tasks = @$_SESSION['filter_period_new_tasks'];
		$columns_list = @$_SESSION['filter_columns_list'];
		
		$id_responsibles_part = '';
		if(strpos($sql,"m.id_responsible")!== false){
			$sql_array = explode(' AND ',$sql);
									
			for($i=1;$i<sizeof($sql_array);$i++){
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

		if(strpos($sql,"m.id_progress_status")!== false){
			$sql_array = explode(' AND ',$sql);

			for($i=1;$i<sizeof($sql_array);$i++){
				if(strpos($sql_array[$i],'m.id_progress_status') !== false){
					$id_progress_status_part = $sql_array[$i];
					$id_progress_status_part = str_replace('m.id_progress_status IN(','',$id_progress_status_part);
					$id_progress_status_part = substr($id_progress_status_part, 0, -1);
					$id_progress_status_array = explode(',',$id_progress_status_part);
				}
			}
				
			for($i=0;$i<sizeof($id_progress_status_array);$i++){
				$query = $mysqli->prepare("SELECT name_he FROM dne_progress_status WHERE id = ?");
				$query->bind_param("i",$id_progress_status_array[$i]);
				$query->execute();
				$query->store_result();
				$query = fetch_unique($query);
				
				if(@$query->name_he == 'ארכיון' && sizeof($id_progress_status_array) == 1) 
				   $sql = str_replace('is_appears = 1','is_appears = 0',$sql);
				else if(@$query->name_he == 'ארכיון' && sizeof($id_progress_status_array) > 1) 
				   $sql = str_replace('is_appears = 1','is_appears IN(0,1)',$sql);
		   }
		}
		
		$query = $mysqli->prepare($sql);
		$query->execute();
		$query->store_result();
		$all_meetings_num_rows = $query->num_rows;
		$all_meetings = fetch($query);
		$all_meetings_with_images_num_rows = 0;
		
		foreach($all_meetings as $item){
			if($item->image1 != '' && $item->is_appears_img1)
			   $all_meetings_with_images_num_rows++;
		}
	}
	
	$query = $mysqli->prepare("SELECT rdv.rdv_name AS rdv_name,
	                          rdv.rdv_date AS rdv_date,
							  rdv.rdv_persons AS rdv_persons,
							  mt.name AS meeting_name, 
							  mt.name_he AS meeting_name_he 
                              FROM dne_rdv rdv 
							  LEFT JOIN dne_meetings_types mt ON rdv.id_meetings_types = mt.id
							  WHERE rdv.id = ?");
	$query->bind_param("i",$id_rdv_report);
	$query->execute();
	$query->store_result();
	$rdv = fetch_unique($query);
    $rdv_name = @$rdv->rdv_name;
	$rdv_date = @$rdv->rdv_date;
	$meeting_name = @$rdv->meeting_name;
	$meeting_name_he = @$rdv->meeting_name_he;

    $rdv_persons_array = explode(',',@$rdv->rdv_persons);
    $report_type = 'resumeRdv_'.$id_rdv_report;
	
	$_SESSION['lang'] = $lang;
	$_SESSION['project_lang'] = @$project->lang;

	if($lang == 'HE')
	   $project_name = '<span class="fontSize26 font-weight-bold font-family-david">פרוייקט '.htmlspecialchars($project->name_he).'</span><br/><span class="fontSize26 color-349feb font-family-david font-weight-bold">ישיבת '.htmlspecialchars($meeting_name_he).'</span>';
    else
	   $project_name = '<span class="fontSize26 font-weight-bold">Project '.htmlspecialchars($project->name).'</span><br/><span class="fontSize26 color-349feb font-family-david font-weight-bold">'.htmlspecialchars($meeting_name).' Meeting</span>';	
    
	$title = '<strong><u>'.htmlspecialchars($rdv_name).'</u></strong><br/><u class="fontSize20 font-weight-bold">'.smartDate($rdv_date, $lang).'</u>';
}

$columns_list_array = explode(',',@$columns_list);

if (empty($lang))
	$lang = @$project->lang;

// Sauvegarder en session UNIQUEMENT quand l'utilisateur change le dropdown (lang_origin=user)
if (!empty(@$_GET['lang']) && @$_GET['lang_origin'] === 'user') {
    $_SESSION['meetings_forced_lang'] = $_GET['lang'];
}

// La langue choisie par l'utilisateur prend le dessus sur la langue du rapport/rdv
if (!empty($_SESSION['meetings_forced_lang'])) {
    $lang = $_SESSION['meetings_forced_lang'];
} elseif (empty($lang)) {
    $lang = @$_GET['lang'];
}

$_SESSION['filter_lang'] = $lang;

if(@$lang == 'HE'){
   $dir = 'rtl';
   $inverse_dir = 'ltr';
   $align = "alignRight";
   $padding = 'padding-right';
   $padding_10 = "paddingRight10";
   $text_align = 'text-align:right';
   $all_label = 'הכל';
   $table_design_label = 'TABLE VIEW';
   $picture_label = 'PICTURE';
   $colors_label = 'COLORS';
   $track_label = 'TRACK';
   $sort_label = 'SORT';
   $border_cell_table_start = "border_cell_table_start_he";
   $border_cell_table_end = "border_cell_table_end_he";
   $border_cell_cbx = "border_cell_cbx_he";
   $border_cell_number = "border_cell_number_he";
   $margin_all_label = 'marginRight5';
   $margin_btns_popup = 'marginLeft10';
   $margin_between_images = 'marginLeft10';
   $participants_label = 'משתתפים';
   $image_concentration_label = 'ריכוז משימות עם תמונות';
   $subject_label = 'נושא/תחום';
   $area_label = 'איזור/נושא';
   $description_label = 'תאור';
   $task_type_label = 'משימה';
   $responsible_label = 'אחראי';
   $pass_on_label = 'למסור ל';
   $task_creation_date_label = 'יצירה';
   $target_date_label = 'יעד';
   $target_date_popup_label = 'תאריך יעד';
   $progress_status_label = "סטטוס<br/>התקדמות";
   $progress_status_popup_label = "סטטוס חדש";
   $status_label = "סטטוס";
   $update_label = 'עדכון';
   $new_update_label = 'ניתן להוסיף כאן הערה';
   $save_remarks_label = "שמור";
   $save_without_remark_label = "שמור סטטוס ללא הערות";
   $cancel_label = "בטל";
}
else {
	$dir = 'ltr';
	$inverse_dir = 'rtl';
	$align = "alignLeft";
	$padding = 'padding-left';
	$padding_10 = "paddingLeft10";
	$text_align = 'text-align:left';
	$all_label = 'All';
	$table_design_label = 'TABLE VIEW';
	$picture_label = 'PICTURE';
	$colors_label = 'COLORS';
	$track_label = 'TRACK';
	$sort_label = 'SORT';
    $border_cell_table_start = "border_cell_table_start";
    $border_cell_table_end = "border_cell_table_end";
	$border_cell_cbx = "border_cell_cbx";
    $border_cell_number = "border_cell_number";
    $margin_all_label = 'marginLeft5';
	$margin_btns_popup = 'marginRight10';
	$margin_between_images = 'marginRight10';
	$participants_label = 'Participants';
	$image_concentration_label = 'Concentration of tasks with images';
	$subject_label = 'Subject/Domain';
	$area_label = 'Area/Subject';
	$description_label = 'Description';
	$task_type_label = 'Task Type';
	$responsible_label = 'Responsible';
	$pass_on_label = 'Transfer To';
	$task_creation_date_label = 'Creation';
	$target_date_label = 'Target';
	$target_date_popup_label = 'Target Date';
	$progress_status_label = "Progress<br/>Status";
	$progress_status_popup_label = "New Status";
	$status_label = "Status";
	$update_label = 'Update';
	$new_update_label = 'You can add a comment here';
	$save_remarks_label = "Save";
    $save_without_remark_label = "Save status without comments";
    $cancel_label = "Cancel";
}

$colspan_image_tr = sizeof($columns_list_array)+2;

$is_active = 1;		
$query = $mysqli->prepare("SELECT * FROM dne_responsibles 
                          WHERE id_project = ? AND is_active = ?
						  ORDER BY name");
$query->bind_param("ii",$project_id,$is_active);
$query->execute();
$query->store_result();
$responsibles = fetch($query);

$query = $mysqli->prepare("SELECT * FROM dne_tasks
                          WHERE id_project = ? AND is_appears_tasks_list = 1
						  ORDER BY id_display");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$tasks = fetch($query);

$query = $mysqli->prepare("SELECT * FROM dne_progress_status WHERE id_project = ?");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$progress_status_s = fetch($query);

$sup_type = 'S';
$des_type = 'D';
$etrp_type = 'E';

$query = $mysqli->prepare("SELECT ps.id AS id,s.name_he AS name_he
                           FROM dne_projects_suppliers ps
						   LEFT JOIN dne_projects p ON ps.id_project = p.id
						   LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
						   WHERE ps.id_project = ?
						   AND s.type = ?
						   AND NOT EXISTS (
						   SELECT 1
								FROM dne_responsibles r
							    WHERE r.id_projects_suppliers = ps.id
								AND r.role = 'project_manager'
							)						
                            ORDER BY name_he ASC");
$query->bind_param("is",$project_id,$sup_type);
$query->execute();
$query->store_result();
$suppliers = fetch($query);

$query = $mysqli->prepare("SELECT ps.id AS id,s.name_he AS name_he
                           FROM dne_projects_suppliers ps
						   LEFT JOIN dne_projects p ON ps.id_project = p.id
						   LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
						   WHERE ps.id_project = ?
						   AND s.type = ?
						   AND NOT EXISTS (
						   SELECT 1
								FROM dne_responsibles r
							    WHERE r.id_projects_suppliers = ps.id
								AND r.role = 'project_manager'
							)
							ORDER BY name_he ASC");
$query->bind_param("is",$project_id,$des_type);
$query->execute();
$query->store_result();
$designers = fetch($query);

$query = $mysqli->prepare("SELECT ps.id AS id,s.name_he AS name_he
                          FROM dne_projects_suppliers ps
						  LEFT JOIN dne_projects p ON ps.id_project = p.id
						  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
						  WHERE ps.id_project = ? AND s.type = ?
                          ORDER BY name_he ASC");
$query->bind_param("is",$project_id,$etrp_type);
$query->execute();
$query->store_result();
$entrepreneur = fetch_unique($query);

$request_name = "כלל המשימות";
$query = $mysqli->prepare("SELECT id FROM dne_custom_reports 
                          WHERE request_name = ? AND id_project = ?");
$query->bind_param("si",$request_name,$project_id);
$query->execute();
$query->store_result();
$query = fetch_unique($query);
$id_project_status_report = @$query->id;

$is_user_active = 1;
$query = $mysqli->prepare("SELECT * FROM dne_users 
                          WHERE is_user_active = ?");
$query->bind_param("i",$is_user_active);
$query->execute();
$query->store_result();
$active_users = fetch($query);

$query = $mysqli->prepare("SELECT * FROM dne_log_meeting_updates 
						  WHERE id_meeting = ? 
						  AND is_remark_appears_log = ?
						  ORDER BY id DESC");
$query->bind_param("ii",$meeting_id,$one);
$query->execute();
$query->store_result();
$log_meeting_updates = fetch($query);

$ps1 = 'ארכיון';
$is_active_project = 1;
$is_active_tracking = 1;
$empty_remark ='';
$is_remark_appears_log = 1;

$query = $mysqli->prepare("SELECT lmu.id AS lmu_id,
						   lmu.action AS action,
						   lmu.action_date AS action_date,
						   lmu.remark as remark,
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
						   m.id_track_responsible AS id_track_responsible,
                           u.nickname AS user_nickname					   
				           FROM dne_log_meeting_updates lmu
                           LEFT JOIN dne_users u ON lmu.id_user = u.id						   
						   LEFT JOIN dne_meetings m ON lmu.id_meeting = m.id  
						   LEFT JOIN dne_chapters c ON m.id_chapter = c.id
						   LEFT JOIN dne_tasks t ON m.id_task = t.id
						   LEFT JOIN dne_responsibles r ON m.id_responsible = r.id
						   LEFT JOIN dne_progress_status ps ON m.id_progress_status = ps.id
				           LEFT JOIN dne_projects p ON m.id_project = p.id
						   WHERE ps.name_he <> ?          						   
					       AND p.is_project_active = ?
					       AND m.id_user <> ?
						   AND lmu.remark <> ?
						   AND lmu.is_remark_appears_log = ?
						   AND NOT FIND_IN_SET(?,lmu.updated_users)
					       ORDER BY lmu_id");
$query->bind_param('siisii',$ps1,$is_active_project,$_SESSION['id_user'],$empty_remark,$is_remark_appears_log,$_SESSION['id_user']);	
$query->execute(); 
$query->store_result();
$all_what_news = fetch($query);	

$wn_meeting_ids_array = array();
foreach($all_what_news as $wn){
	if(!in_array(@$wn->id,$wn_meeting_ids_array))
		array_push($wn_meeting_ids_array,@$wn->id);
}

$filters_str = '';
if(@$supplier_filter != 'undefined' || @$period_new_task_filter != 'undefined' 
   || @$period_late_filter != 'undefined' || @$progress_status_filter != 'undefined' 
   || @$task_filter != 'undefined'){
	    $filters_array = array();
	if(@$supplier_filter != 'undefined'){
		$query = $mysqli->prepare("SELECT s.name_he 
		                           FROM dne_projects_suppliers ps
                                   LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
		                           WHERE ps.id = ?");
		$query->bind_param("i",$supplier_filter);
		$query->execute();
		$query->store_result();
		$query = fetch_unique($query);
		$filter_by_supplier = @$query->name_he;
		array_push($filters_array,$filter_by_supplier);
	}
	
	if(@$period_new_task_filter != 'undefined'){
		$periods = ['today' => 'היום',
					'three_days' => '3 ימים',
					'one_week' => 'שבוע',
					'two_weeks' => 'שבועים',
				    'one_month' => 'חודש',
			        'two_months' => 'חדשיים',
				    'one_year' => 'שנה',
					'two_years' => 'שנתיים'];

		foreach ($periods as $value => $label){
			if($period_new_task_filter == $value)
				$filter_by_period_new_task = 'חדש('.$label.')';
		}
		
		array_push($filters_array,$filter_by_period_new_task);
	}
	
	if(@$period_late_filter != 'undefined'){
		$periods = ['three_days' => '3 ימים',
					'one_week' => 'שבוע',
					'two_weeks' => 'שבועים',
					'one_month' => 'חודש',
					'two_months' => 'חדשיים'];

		foreach ($periods as $value => $label){
			if($period_late_filter == $value)
				$filter_by_period_late_filter = 'איחור('.$label.')';
		}
		
		array_push($filters_array,$filter_by_period_late_filter);
	}
	
	if(@$progress_status_filter != 'undefined'){
		$query = $mysqli->prepare("SELECT name_he FROM dne_progress_status WHERE id = ?");
		$query->bind_param("i",$progress_status_filter);
		$query->execute();
		$query->store_result();
		$query = fetch_unique($query);
		if(@$query->name_he == ' ')
			$filter_by_progress_status = 'סטטוס(ללא)';
		else 
			$filter_by_progress_status = 'סטטוס('.@$query->name_he.')';
		array_push($filters_array,$filter_by_progress_status);
	}
	
	if(@$task_filter != 'undefined'){
		$query = $mysqli->prepare("SELECT name_he FROM dne_tasks WHERE id = ?");
		$query->bind_param("i",$task_filter);
		$query->execute();
		$query->store_result();
		$query = fetch_unique($query);
			$filter_by_task = 'משימה('.@$query->name_he.')';
		array_push($filters_array,$filter_by_task);
	}
	
	$filters_str = 'סינון לפי:'.implode('+',$filters_array);
}

include 'menu_tasks.php';
?>
        <form method="post" action="" class="form-inline">	
			<div class="container">	
			    <input type="hidden" id="project_id" name="project_id" value="<?=@$project->id?>" />
				<input type="hidden" id="project_nickname" name="project_nickname" value="<?=@$project->nickname?>" />
				<input type="hidden" id="id_project_status_report" name="id_project_status_report" value="<?=@$id_project_status_report?>" />
				<input type="hidden" id="id_custom_report" name="id_custom_report" value="<?=@$id_custom_report?>" />
				<input type="hidden" id="task_filter" name="task_filter" value="<?=@$task_filter?>" />
				<input type="hidden" id="progress_status_filter" name="progress_status_filter" value="<?=@$progress_status_filter?>" />
				<input type="hidden" id="period_late_filter" name="period_late_filter" value="<?=@$period_late_filter?>" />
				<input type="hidden" id="get_supplier_filter" name="get_supplier_filter" value="<?=@$_GET['supplier_filter']?>" />
				<input type="hidden" id="supplier_filter" name="supplier_filter" value="<?=@$supplier_filter?>" />
				<input type="hidden" id="search_filter" name="search_filter" value="<?=@$search_filter?>" />
				<input type="hidden" id="period_new_tasks" name="period_new_tasks" value="<?=@$period_new_tasks?>" />
				<input type="hidden" id="period_new_task_filter" name="period_new_task_filter" value="<?=@$period_new_task_filter?>" />
				<input type="hidden" id="report_type" name="report_type" value="<?=@$report_type?>" />
                <input type="hidden" id="row" value="<?=@$row?>" />
				<input type="hidden" id="id_rdv_report" value="<?=@$id_rdv_report?>" />
				<input type="hidden" id="rdv_name" name="rdv_name" value="<?=@$rdv_name?>" />
				<input type="hidden" id="is_specific_filter" value="<?=@$is_specific_filter?>" />
				<input type="hidden" id="subject_str" name="subject_str" value="<?=@$subject_str?>" />
				<input type="hidden" id="area_str" name="area_str" value="<?=@$area_str?>" />
				<input type="hidden" id="description_str" name="description_str" value="<?=@$description_str?>" />
				<input type="hidden" id="chapters_ids" name="chapters_ids" value="<?=@$chapters_ids?>" />
				<input type="hidden" id="tasks_types_ids" name="tasks_types_ids" value="<?=@$tasks_types_ids?>" />
				<input type="hidden" id="tasks_ids" name="tasks_ids" value="<?=@$tasks_ids?>" />
				<input type="hidden" id="responsibles_ids" name="responsibles_ids" value="<?=@$responsibles_ids?>" />
				<input type="hidden" id="and_or_responsibles" name="and_or_responsibles" value="<?=@$and_or_responsibles?>" />
				<input type="hidden" id="pass_ons_ids" name="pass_ons_ids" value="<?=@$pass_ons_ids?>" />
				<input type="hidden" id="progress_status_ids" name="progress_status_ids" value="<?=@$progress_status_ids?>" />
				<input type="hidden" id="period_creation_date" name="period_creation_date" value="<?=@$period_creation_date?>" />
				<input type="hidden" id="creation_date_start" name="creation_date_start" value="<?=@$creation_date_start?>" />
				<input type="hidden" id="creation_date_end" name="creation_date_end" value="<?=@$creation_date_end?>" />
				<input type="hidden" id="period_destination_date" name="period_destination_date" value="<?=@$period_destination_date?>" />
				<input type="hidden" id="destination_date_start" name="destination_date_start" value="<?=@$destination_date_start?>" />
				<input type="hidden" id="destination_date_end" name="destination_date_end" value="<?=@$destination_date_end?>" />
				<input type="hidden" id="columns_list" name="columns_list" value="<?=@$columns_list?>" />
				<input type="hidden" id="lang" name="lang" value="<?=@$lang?>" />
				<input type="hidden" id="project_lang" value="<?=!empty(@$project->lang)?@$project->lang:'HE'?>" />
				<input type="hidden" id="is_images" name="is_images" value="<?=@$is_images?>" />
				<input type="hidden" id="is_colors" name="is_colors" value="<?=@$is_colors?>" />
				<input type="hidden" id="sql" name="sql" value="<?=@$sql?>" />
				<input type="hidden" id="wn_meeting_ids" value="<?=implode(',',@$wn_meeting_ids_array)?>" />
				
				<div class="width550" id="contentToScreenshot"></div>
				
				<div class="row alignCenter marginTop25">
					<div class="col-12">
						<a href="projects.php"><img src="images/davidnahmias_logo.png" width="170px" height="170px" /></a>
					</div>
			    </div>
				
				<div class="row title marginTop15">
                    <div class="col-12">
					    <a id="a_project_title" class="text-decoration-none color-1A5276 cursor-pointer" href="projects.php">
						   <div class="fontSize33 line-height-1em"><strong><?=@$project->nickname?></strong></div>
						   <div class="fontSize26"><?=@$project_name?></div>
					    </a>		
					</div>		
			    </div>
						
				<div class="margin-top-10-x-auto external-width-tasks-filter padding10 paddingTop15 bgColor-e5f4ff borderRadius20">
					<div class="report-container paddingTop10 paddingRight10 paddingBottom20 position-relative dir-rtl">
						<div class="badge-top-right cursor-pointer" onclick="location.href='custom_reports.php?project_id=<?=@$project_id?>'">הדו''חות שלי</div>
						<div class="flex flex-wrap alignCenterap justify-content-center align-items-center dir-rtl">	
							<div class="flex flex-wrap alignCenter justify-content-center dir-rtl">
								<?php foreach ($custom_reports as $item) { ?>
									<div class="responsive-btn-container">
										<input type="button" class="btn bgColor-579ecf font-weight-bold width120 fontSize12 borderRadius20" 
											value="<?=htmlspecialchars(@$item->request_name)?>" 
											<?php if(@$item->id == @$id_custom_report) echo "style='background-color:#63d687;'"?> 
											onclick="setCurrentReportSession(<?=@$item->id?>);" />
									</div>	
								<?php } ?>

								<a class="marginTop5 cursor-pointer text-decoration-none" onclick="location.href='add_custom_report.php?id=0&project_id=<?=@$project_id?>'">
									<img src="images/plus-icon.png" width="20" height="20" alt="plus icon" />
								</a>									
							</div>																
						</div>
					</div>
					
					<div class="marginTop15 borderRadius20 position-relative flex flex-wrap justify-content-center dir-rtl" style="gap:10px;">
						<div class="width60Percents alignCenter">	
							<div class="fontSize12 height90 report-container">
								<div class="badge-top-right"><img src="images/filter-solid.svg" width="16" height="12" />סינון לפי...</div>						
								<div class="row marginTop10">
									<div class="col-12">
										<a id="filter_by_supplier_btn" class="filter_btn text-decoration-none marginLeft10 cursor-pointer" <?php if(@$supplier_filter != 'undefined' && @$is_specific_filter) echo "style=background-color:#63d687;"?> data-projectid="<?=@$project_id?>">ספק</a>		
										<a id="filter_by_period_new_task_btn" class="filter_btn text-decoration-none marginLeft10 cursor-pointer" <?php if(@$period_new_task_filter != 'undefined' && @$is_specific_filter) echo "style=background-color:#63d687;"?> data-projectid="<?=@$project_id?>">חדש</a>
										<a id="filter_by_period_late_btn" class="filter_btn text-decoration-none marginLeft10 cursor-pointer" <?php if(@$period_late_filter != 'undefined' && @$is_specific_filter) echo "style=background-color:#63d687;"?> data-projectid="<?=@$project_id?>">איחור</a>
										<a id="filter_by_progress_status_btn" class="filter_btn text-decoration-none marginLeft10 cursor-pointer" <?php if(@$progress_status_filter != 'undefined' && @$is_specific_filter) echo "style=background-color:#63d687;"?> data-projectid="<?=@$project_id?>">סטטוס</a>
										<a id="filter_by_task_btn" class="filter_btn text-decoration-none marginLeft10 cursor-pointer" <?php if(@$task_filter != 'undefined' && @$is_specific_filter) echo "style=background-color:#63d687;"?> data-projectid="<?=@$project_id?>">משימה</a>										
									</div>
								</div>
							
								<div class="row marginTop5">
									<div class="col-12">																
										<div class="search-container position-relative display-inline-block dir-rtl">
											<i class="fa fa-search cursor-pointer position-absolute" style="right:10px;top:35%;transform:translateY(-50%);"></i>
											<input type="text" id="input_search" class="marginLeft8 paddingRight30 width140 borderRadius20 mb-2 cursor-pointer" style="border:1px solid #ccc;" placeholder="חפש" value="<?=@$search_filter?>" onchange="setFilter(<?=@$id_report?>,0,this.value,'search_filter');" />
										</div>
										
										<a id="specific_filter_btn" class="filter_btn width100 text-decoration-none marginLeft10 cursor-pointer" <?php if(@$is_btn_new_bgcolor == 1) echo "style=background-color:#63d687;"?> onclick="makeFilter()"><img src="images/filter-solid.svg" width="16" height="12" />סינון מתקדם</a>
									</div>
								</div>
							</div>
						</div>
						<div class="width40Percents alignCenter">
							<div class="fontSize12 height90 report-container">
								<div class="badge-top-right"><img src="images/meeting.svg" width="20" height="15" />דו''חות ישיבה</div>	   
								<div class="flex flex-wrap width90Percents margin-0-x-auto justify-content-center align-items-center">
									<div class="width30Percents">
										<a class="marginTop5 text-decoration-none cursor-pointer" onclick="toAddRdv('addRdv')">
											<strong class="fontSize9"><img id="img_plus_rdv" src="images/plus-icon.png" width="20" height="20" alt="plus icon" /><br/>ישיבה חדשה</strong>
										</a>				
									</div>
									<div class="width70Percents">
										<div class="row">
											<div class="col-12">
												<a id="resume_rdv_btn" class="reports_btns marginTop5 btn width130 bgColor-f5f4fe font-weight-bold borderRadius10" style="<?php if(@$id_rdv_report > 0) echo 'background-color:#63d687;'?>" onclick="location.href='resume_rdv.php?&project_id=<?=@$project_id?>';">
													<div class="marginRight5 marginLeft5 fontSize12 flex flex-wrap align-items-center">
														<div class="width20 alignRight">
															<i class="fa fa-search marginLeft5"></i>
														</div>
														<div id="resume_rdv_label" class="width70">
															בחר ישיבה
														</div>
													</div>
												</a>
												<?php if(@$id_rdv_report > 0) { ?>
													<a class="btn btn-info marginTop5 marginRight5 fontSize13 font-weight-bold borderRadius20" title="נתוני ישיבה" onclick="location.href='add_rdv.php?project_id=<?=@$project_id?>&id=<?=@$id_rdv_report?>'"><i>i</i></a>
											    <?php } ?>
											</div>
										</div>
										<div class="row marginTop5">
											<div class="col-12">
												<a id="apply_tasks_to_rdv_btn" class="reports_btns marginTop5 btn width130 bgColor-f5f4fe color-474d5d font-weight-bold borderRadius10 disabled-link" onclick="toAddRdv('applyTasksToRdv')">						
													<div class="marginRight5 fontSize12 flex flex-wrap align-items-center">
														<div class="width20 alignRight">
															<i class="fa-solid fa-plus marginLeft5"></i>
														</div>
														<div class="width70">
															צרף משימות
														</div>
													</div>
												</a>
											</div>				 
										</div>				
									</div>				
								</div>						
							</div>
						</div>
					</div>	
				</div>

				<div class="alignCenter position-relative top-minus-12 dir-rtl">
					<div class="padding10 flex flex-wrap justify-content-center margin-0-x-auto bgColor-cbddec width200 shadow borderRadius10 alignCenter">			
						<div class="width-one-of-three">
							<a id="to_add_meeting_btn" class="text-decoration-none marginLeft8 cursor-pointer">
								<img src="images/task_list_square_add_regular_icon_204845.svg" width="35" height="25" alt="task list square" />
								<br/>
								<strong id="task_label" class="fontSize12 color-1A5276">משימה</strong>
							</a>
						</div>       
						<div class="width-one-of-three">
							<?php if(@$all_meetings_num_rows > 0) { ?>
							   <a id="create_pdf_btn" class="text-decoration-none marginLeft8 cursor-pointer" onclick="toReportSettings(<?=@$meeting_id?>);">
								  <img src="images/file-pdf-solid.svg" width="35" height="25" alt="pdf icon" />
							      <br/>
							      <strong id="pdf_label" class="fontSize12 color-1A5276">דו''ח</strong>
							   </a> 								   
							 <?php } ?>
						</div>
						<div class="width-one-of-three">
							<a id="send_tasks_btn" class="text-decoration-none cursor-pointer disabled-link">
								<img src="images/share-icon.svg" width="35" height="25" alt="share icon" title="שתף" />
							    <br/>
							    <strong id="share_label" class="fontSize12 color-1A5276">שתף</strong>	    
							</a>
						</div>
					</div>
					<div class="col-2 col-sm-3 col-md-4 col-lg-4"></div>
				</div>
				
				<div class="color-4cb050 fontSize23 alignCenter"><?=@$title?></div>
				<div id="div_filters" class="fontSize13 color-1A5276 font-weight-bold alignCenter dir-rtl"><?=@$filters_str?></div>

				<?php 
				if(@$id_rdv_report > 0) { ?>
					<div class="row marginTop10" style="direction:<?=@$dir?>;">
				   	    <div class="col-12">
						    <strong><?=@$participants_label?>:</strong>
						</div>
					</div>
					
					<?php
					$max_rows_per_col = 4;
					$total_items = count($rdv_persons_array);
					$total_columns = ceil($total_items/$max_rows_per_col); 	

					echo '<div class="participants-container" style="direction:'.@$dir.'">'; 	

					$row_count = 0;
					foreach ($rdv_persons_array as $index => $item){
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
						if(@$responsible->name != '')
							$participants .= @$responsible->name;
						if($row_count == 0){
							echo '<div class="column">';
						}

						echo '<div class="participant">'.@$participants.'</div>';
	  
						$row_count++;

						if ($row_count >= $max_rows_per_col || $index == $total_items - 1) {
							echo '</div>'; 
							$row_count = 0;
						}
                    }

                    echo '</div>'; 		
				}
				
				if(@$all_meetings_num_rows > 0){ ?>
					<div class="row <?php if(@$id_custom_report > 0) echo "marginTop15"?> fontSize12" style="direction:<?=@$dir?>;">
						<div class="col-12" style="overflow-x:auto;">
							<div class="d-flex justify-content-between align-items-center flex-wrap">	
								<div class="d-flex align-items-center gap-2">
									<input type="checkbox" id="check_all" onclick="checkAllItems()" />
									<span class="fontSize14 font-weight-bold"><?=@$all_label?></span>	
								</div>
								<div class="d-flex align-items-center gap-2" style="direction:<?=@$inverse_dir?>;">							
									<div>
										<select id="select_lang" name="lang" class="marginTop10 marginBottom10 border-color-initial">
											<option value="HE" <?php if($lang == 'HE') echo 'selected'?>>עברית</option>
											<option value="EN" <?php if($lang == 'EN') echo 'selected'?>>English</option>					   
										</select>
                                    </div> |
									<div class="alignCenter">
										<a id="table_view_btn" class="marginTop10 cursor-pointer text-decoration-none mb-2" title="<?=@$table_design_label?>">
											<i class="fa-solid fa-table fontSize20"></i>
										    <br/>
											<strong class="fontSize10"><?=@$table_design_label?></strong>
										</a>		
									</div> |
									<div class="alignCenter">
										<a id="picture_btn" class="marginTop10 cursor-pointer text-decoration-none mb-2" title="<?=@$picture_label?>">
											<img src="images/picture-icon.png" alt="picture icon" width="20" height="20" />
										    <br/>
											<strong class="fontSize10"><?=@$picture_label?></strong>
										</a>			
									</div>
                                    <div class="alignCenter">  
										<?php
										$switch_is_images = 'ON';
										if(!@$is_images)
											$switch_is_images = 'OFF';
										?>
										<label class="switch">
											<input type="checkbox" id="toggle_switch_images" <?=@$switch_is_images === 'ON'? 'checked' : ''?>>
											<span class="slider round"></span>
										</label>	
									</div> |								
								    <div class="alignCenter">
										<a id="colors_btn" class="marginTop10 cursor-pointer text-decoration-none mb-2" title="<?=@$colors_label?>">
											<i class="fa-solid fa-eye-dropper fontSize20"></i>
											<br/>
											<strong class="fontSize10"><?=@$colors_label?></strong>
										</a>
									</div>
                                    <div class="alignCenter">  
										<?php
										$switch_is_colors = 'ON';
										if(!@$is_colors)
											$switch_is_colors = 'OFF';
										?>
										<label class="switch">
											<input type="checkbox" id="toggle_switch_colors" <?=@$switch_is_colors === 'ON'? 'checked' : ''?>>
											<span class="slider round"></span>
										</label>	
									</div> |
                                    <div class="alignCenter"> 									
										<a id="track_btn" class="marginTop10 cursor-pointer text-decoration-none mb-2">	
											<img id='target-icon' src="images/red-target-icon.png" alt="target icon" title="<?=@$track_label?>" width="20" height="20" />
										    <br/>
											<strong class="fontSize10"><?=@$track_label?></strong>
										</a>
                                    </div>	
                                    <div class="alignCenter">  		
										<label class="switch">
											<input type="checkbox" id="toggle_switch_track" checked>
											<span class="slider round"></span>
										</label>	
									</div> |
                                    <div class="alignCenter">
										<a id="sort_btn" class="marginTop10 cursor-pointer text-decoration-none mb-2" title="<?=@$sort_label?>">
											<img id='sort-icon' src="images/sort-icon.png" alt="sort icon" title="<?=@$sort_label?>" width="20" height="20" />
										    <br/>
											<strong class="fontSize10"><?=@$sort_label?></strong>	
										</a>										
                                    </div>
									<div id="div_sort" class="display-none">
										<div>
											<select id="sort_select_1" name="sort_select_1">
												<option value="0" <?= @$_GET['sort_select_1']=='0' ? 'selected' : '' ?>>Sort By</option>
												<option value="subject" <?= @$_GET['sort_select_1']=='subject' ? 'selected' : '' ?>>Subject</option>
												<option value="area" <?= @$_GET['sort_select_1']=='area' ? 'selected' : '' ?>>Area</option>
												<option value="description" <?= @$_GET['sort_select_1']=='description' ? 'selected' : '' ?>>Description</option>
												<option value="responsible" <?= @$_GET['sort_select_1']=='responsible' ? 'selected' : '' ?>>Responsible</option>
												<option value="task" <?= @$_GET['sort_select_1']=='task' ? 'selected' : '' ?>>Task Type</option>
												<option value="task_creation_date" <?= @$_GET['sort_select_1']=='task_creation_date' ? 'selected' : '' ?>>Creation</option>
												<option value="destination_date" <?= @$_GET['sort_select_1']=='destination_date' ? 'selected' : '' ?>>Target Date</option>
												<option value="status" <?= @$_GET['sort_select_1']=='status' ? 'selected' : '' ?>>Status</option>
											</select>
										</div>
										<div class="marginTop5">
											<select id="sort_select_2" name="sort_select_2">
												<option value="0" <?= @$_GET['sort_select_2']=='0' ? 'selected' : '' ?>>Sort By</option>
												<option value="subject" <?= @$_GET['sort_select_2']=='subject' ? 'selected' : '' ?>>Subject</option>
												<option value="area" <?= @$_GET['sort_select_2']=='area' ? 'selected' : '' ?>>Area</option>
												<option value="description" <?= @$_GET['sort_select_2']=='description' ? 'selected' : '' ?>>Description</option>
												<option value="responsible" <?= @$_GET['sort_select_2']=='responsible' ? 'selected' : '' ?>>Responsible</option>
												<option value="task" <?= @$_GET['sort_select_2']=='task' ? 'selected' : '' ?>>Task Type</option>
												<option value="task_creation_date" <?= @$_GET['sort_select_2']=='task_creation_date' ? 'selected' : '' ?>>Creation</option>
												<option value="destination_date" <?= @$_GET['sort_select_2']=='destination_date' ? 'selected' : '' ?>>Target Date</option>
												<option value="status" <?= @$_GET['sort_select_2']=='status' ? 'selected' : '' ?>>Status</option>
											</select>
										</div>
										<div class="marginTop5 marginBottom5">
											<select id="sort_select_3" name="sort_select_3">
												<option value="0" <?= @$_GET['sort_select_3']=='0' ? 'selected' : '' ?>>Sort By</option>
												<option value="subject" <?= @$_GET['sort_select_3']=='subject' ? 'selected' : '' ?>>Subject</option>
												<option value="area" <?= @$_GET['sort_select_3']=='area' ? 'selected' : '' ?>>Area</option>
												<option value="description" <?= @$_GET['sort_select_3']=='description' ? 'selected' : '' ?>>Description</option>
												<option value="responsible" <?= @$_GET['sort_select_3']=='responsible' ? 'selected' : '' ?>>Responsible</option>
												<option value="task" <?= @$_GET['sort_select_3']=='task' ? 'selected' : '' ?>>Task Type</option>
												<option value="task_creation_date" <?= @$_GET['sort_select_3']=='task_creation_date' ? 'selected' : '' ?>>Creation</option>
												<option value="destination_date" <?= @$_GET['sort_select_3']=='destination_date' ? 'selected' : '' ?>>Target Date</option>
												<option value="status" <?= @$_GET['sort_select_3']=='status' ? 'selected' : '' ?>>Status</option>
											</select>
										</div>
									</div> |
									<div class="alignCenter">
										<strong class="fontSize10"><?= $lang == 'HE' ? 'להדגיש' : 'Highlight' ?></strong>
										<br/>
										<select id="period_new_tasks_toolbar" class="height26" style="width:auto;" dir="<?= $lang == 'HE' ? 'rtl' : 'ltr' ?>">
											<option value="0"          <?= @$period_new_tasks == 0            ? 'selected' : '' ?>><?= $lang == 'HE' ? 'לא'       : 'Off'      ?></option>
											<option value="today"      <?= @$period_new_tasks == 'today'      ? 'selected' : '' ?>><?= $lang == 'HE' ? 'היום'     : 'Today'    ?></option>
											<option value="three_days" <?= @$period_new_tasks == 'three_days' ? 'selected' : '' ?>><?= $lang == 'HE' ? '3 ימים'   : '3 days'   ?></option>
											<option value="one_week"   <?= @$period_new_tasks == 'one_week'   ? 'selected' : '' ?>><?= $lang == 'HE' ? 'שבוע'     : '1 week'   ?></option>
											<option value="two_weeks"  <?= @$period_new_tasks == 'two_weeks'  ? 'selected' : '' ?>><?= $lang == 'HE' ? 'שבועיים'  : '2 weeks'  ?></option>
											<option value="one_month"  <?= @$period_new_tasks == 'one_month'  ? 'selected' : '' ?>><?= $lang == 'HE' ? 'חודש'     : '1 month'  ?></option>
											<option value="two_months" <?= @$period_new_tasks == 'two_months' ? 'selected' : '' ?>><?= $lang == 'HE' ? 'חדשיים'   : '2 months' ?></option>
											<option value="one_year"   <?= @$period_new_tasks == 'one_year'   ? 'selected' : '' ?>><?= $lang == 'HE' ? 'שנה'      : '1 year'   ?></option>
											<option value="two_years"  <?= @$period_new_tasks == 'two_years'  ? 'selected' : '' ?>><?= $lang == 'HE' ? 'שנתיים'   : '2 years'  ?></option>
										</select>
									</div> |
									<div class="btn-group btn-group-toggle width70" data-toggle="buttons">
										<div class="btn-group btn-group-toggle" data-toggle="buttons" style="direction:ltr">
											<label class="btn btn-primary fontSize9 borderRadius10 height20 active">
												<input type="radio" name="options" id="option1" autocomplete="off" checked> FULL
											</label>
										    <label class="btn btn-primary fontSize9 borderRadius10 height20">
												<input type="radio" name="options" id="option2" autocomplete="off"> SHORT
										    </label>
										</div>
									</div>		
								</div>
							</div>
							
						    <?php
						    // Toutes les colonnes ont une largeur fixe — description absorbe le reste
						    $w_first = 8;
						    $fixed = [
						        'subject'          => 10,
						        'area'             => 10,
						        '_task'            => 10,
						        'task creation'    => 6,
						        'destination date' => 6,
						        'progress status'  => 8,
						        'responsible'      => 11,
						        'pass on'          => 10,
						    ];
						    $fixed_sum = $w_first;
						    foreach ($columns_list_array as $col) {
						        if ($col !== 'description' && isset($fixed[$col]))
						            $fixed_sum += $fixed[$col];
						    }
						    $w_subject     = $fixed['subject'];
						    $w_area        = $fixed['area'];
						    $w_task        = $fixed['_task'];
						    $w_task_cr     = $fixed['task creation'];
						    $w_dest        = $fixed['destination date'];
						    $w_progress    = $fixed['progress status'];
						    $w_responsible = $fixed['responsible'];
						    $w_pass_on     = $fixed['pass on'];
						    $w_desc        = in_array('description', $columns_list_array) ? max(12, 100 - $fixed_sum) : 0;
						    ?>
					    <table id="meetings_table" class="rounded-table" border="1">
								<tr class="bgColor-f2f6f9 height50">
									<th class="border-top-white <?=@$border_cell_table_start?> no-border-right alignCenter" colspan="2" width="<?=$w_first?>%">
										<a class="text-decoration-underline cursor-pointer" onclick="location.href='chapters.php?project_id=<?=@$project_id?>'"><img id="img_plus_rdv" src="images/plus-icon.png" width="18" height="18" alt="plus icon" /></a>
									</th>
									
									<?php if(in_array('subject',$columns_list_array)){ ?>
									    <th class="<?php if(end($columns_list_array) == "subject") echo $border_cell_table_end;?> border-top-white no-border-inline-end alignCenter" width="<?=$w_subject?>%"><?=@$subject_label?></th>
									<?php } 
									
									if(in_array('area',$columns_list_array)){ ?>    
									   <th class="<?php if(end($columns_list_array) == "area") echo $border_cell_table_end;?> border-top-white no-border-inline-end alignCenter" width="<?=$w_area?>%"><?=@$area_label?></th>
									<?php } 
									
									if(in_array('description',$columns_list_array)){ ?>    
									    <th class="<?php if(end($columns_list_array) == "description") echo $border_cell_table_end;?> border-top-white no-border-inline-end alignCenter" width="<?=$w_desc?>%"><?=@$description_label?></th>
									<?php } 
									
									if(in_array('_task',$columns_list_array)){ ?>    
									   <th class="<?php if(end($columns_list_array) == "_task") echo $border_cell_table_end;?> border-top-white no-border-inline-end alignCenter" width="<?=$w_task?>%"><?=@$task_type_label?></th>	
									<?php }
									
								    if(in_array('responsible',$columns_list_array)){ ?>    
									   <th class="<?php if(end($columns_list_array) == "responsible") echo $border_cell_table_end;?> border-top-white no-border-inline-end alignCenter" width="<?=$w_responsible?>%"><?=@$responsible_label?><br/><a class="text-decoration-underline cursor-pointer" onclick="location.href='responsibles.php?project_id=<?=@$project_id?>'"><i class="fa-solid fa-user-plus fontSize9"></i></a></th>
									<?php }
									
									if(in_array('pass on',$columns_list_array)) { ?>   
									   <th class="<?php if(end($columns_list_array) == "pass on") echo $border_cell_table_end;?> border-top-white no-border-inline-end alignCenter fontSize13" width="<?=$w_pass_on?>%"><?=@$pass_on_label?></th>
									<?php }
									
									if(in_array('task creation',$columns_list_array)){ ?>   
								       <th class="<?php if(end($columns_list_array) == "task creation") echo $border_cell_table_end;?> border-top-white no-border-inline-end alignCenter" width="<?=$w_task_cr?>%"><?=@$task_creation_date_label?></th>
									<?php }
									
									if(in_array('destination date',$columns_list_array)) { ?>   
									   <th class="<?php if(end($columns_list_array) == "destination date") echo $border_cell_table_end;?> border-top-white no-border-inline-end alignCenter" width="<?=$w_dest?>%"><?=@$target_date_label?></th>
									<?php }
									
									if(in_array('progress status',$columns_list_array)){ ?>   
									   <th class="<?php if(end($columns_list_array) == "progress status") echo $border_cell_table_end;?> border-top-white no-border-inline-end alignCenter" width="<?=$w_progress?>%"><?=@$status_label?></th>
									<?php } ?>								
								</tr>
								<?php
								if($id_custom_report > 0 || ($id_rdv_report > 0 && $is_specific_filter)){
								    $position_where = strpos($sql,"WHERE");
								    $where_length = strlen($sql)-$position_where;
									$where_part_sql = substr($sql,$position_where,$where_length);
									$where_part_sql_array = explode(' AND ',$where_part_sql);
									
									$chapter_filter = '';
									if(strpos($where_part_sql,"m.id_chapter")!== false){
										for($i=0;$i<sizeof($where_part_sql_array);$i++){
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
								
									$sql_chapters = "SELECT * FROM dne_chapters 
									                 WHERE id_project = ? ".@$chapter_filter.' ORDER BY id_display';
									$query = $mysqli->prepare($sql_chapters);
									$query->bind_param("i",$project_id);
									$query->execute();
									$query->store_result();
									$chapters = fetch($query);
								}
								if($id_rdv_report > 0 && !$is_specific_filter){
								   $query = $mysqli->prepare("SELECT * FROM dne_chapters 
								                             WHERE id_project = ? 
															 ORDER BY id_display");
							       $query->bind_param("i",$project_id);
								   $query->execute(); 
								   $query->store_result();
								   $chapters = fetch($query);
								}
								
								$iteration = 0;
								$count = 0;
								
								foreach($chapters as $item){	
									$chapter_id = $item->id;
									$chapter_name = $item->name;
									
									if($id_custom_report > 0 || ($id_rdv_report > 0 && $is_specific_filter)){
									    $sql_array = explode(' AND ',$sql);
								
										for($i=0;$i<sizeof($sql_array);$i++){
											if(strpos($sql_array[$i],'m.id_chapter') !== false && $i > 0){
												$sql_array[$i] = 'm.id_chapter ='.$chapter_id;
												$sql = implode(' AND ',$sql_array);
											}
										}
										
										if(strpos($where_part_sql,'m.id_chapter') === false) 
										  $sql.= ' AND m.id_chapter ='.$chapter_id;
									   
									    if(@$sort_select_1 == '' && @$sort_select_2 == '' 
										   && @$sort_select_3 == ''){
											if($lang == 'HE')
												$sql = $sql.' ORDER BY m.subject,m.id_area,t.name_he';
											else
												$sql = $sql.' ORDER BY m.subject,m.id_area,t.name ';
										}
                                        else {
											$order_part = ' ORDER BY ';
											$parts = [];

											if(@$sort_select_1 != ''){
												if($sort_select_1 == 'subject')      
													$parts[] = "TRIM(REPLACE(m.subject, '&nbsp;', ''))";
												if($sort_select_1 == 'area')
													$parts[] = "TRIM(REPLACE(m.area, '&nbsp;', ''))";
												if($sort_select_1 == 'description')  
													$parts[] = "m.description";
												if($sort_select_1 == 'responsible')  
													$parts[] = "r.name";
												if($sort_select_1 == 'task'){
													if($lang == 'HE')
														$parts[] = "t.name_he";
													else
														$parts[] = "t.name";
												}
												if($sort_select_1 == 'task_creation_date')  
													$parts[] = "m.task_creation_date";
												if($sort_select_1 == 'destination_date')  
													$parts[] = "m.destination_date";
												if($sort_select_1 == 'status'){
													if($lang == 'HE')
														$parts[] = "ps.name_he";
													else
														$parts[] = "ps.name";
												}        
											}

											if(@$sort_select_2 != ''){
												if($sort_select_2 == 'subject')      
													$parts[] = "TRIM(REPLACE(m.subject, '&nbsp;', ''))";
												if($sort_select_2 == 'area')
													$parts[] = "TRIM(REPLACE(m.area, '&nbsp;', ''))";
												if($sort_select_2 == 'description')  
													$parts[] = "m.description";
												if($sort_select_2 == 'responsible')  
													$parts[] = "r.name";
												if($sort_select_2 == 'task'){
													if($lang == 'HE')
														$parts[] = "t.name_he";
													else
														$parts[] = "t.name";
												}
												if($sort_select_2 == 'task_creation_date')  
													$parts[] = "m.task_creation_date";
												if($sort_select_2 == 'destination_date')  
													$parts[] = "m.destination_date";
												if($sort_select_2 == 'status'){
													if($lang == 'HE')
														$parts[] = "ps.name_he";
													else
														$parts[] = "ps.name";
												}        
											}

											if(@$sort_select_3 != ''){
												if($sort_select_3 == 'subject')      
													$parts[] = "TRIM(REPLACE(m.subject, '&nbsp;', ''))";
												if($sort_select_3 == 'area')
													$parts[] = "TRIM(REPLACE(m.area, '&nbsp;', ''))";
												if($sort_select_3 == 'description')  
													$parts[] = "m.description";
												if($sort_select_3 == 'responsible')  
													$parts[] = "r.name";
												if($sort_select_3 == 'task'){
													if($lang == 'HE')
														$parts[] = "t.name_he";
													else
														$parts[] = "t.name";
												}
												if($sort_select_3 == 'task_creation_date')  
													$parts[] = "m.task_creation_date";
												if($sort_select_3 == 'destination_date')  
													$parts[] = "m.destination_date";
												if($sort_select_3 == 'status'){
													if($lang == 'HE')
														$parts[] = "ps.name_he";
													else
														$parts[] = "ps.name";
												}        
											}
								
											if(!empty($parts)){
												if ((in_array('t.name_he', $parts) || in_array('t.name', $parts))
													&& strpos($sql, 'dne_tasks t') === false)
													$sql = preg_replace('/\bWHERE\b/', 'LEFT JOIN dne_tasks t ON m.id_task = t.id WHERE', $sql, 1);
												if ((in_array('ps.name_he', $parts) || in_array('ps.name', $parts))
													&& strpos($sql, 'dne_progress_status ps') === false)
													$sql = preg_replace('/\bWHERE\b/', 'LEFT JOIN dne_progress_status ps ON m.id_progress_status = ps.id WHERE', $sql, 1);
												if (in_array('r.name', $parts)
													&& strpos($sql, 'dne_responsibles r') === false)
													$sql = preg_replace('/\bWHERE\b/', 'LEFT JOIN dne_responsibles r ON m.id_responsible = r.id WHERE', $sql, 1);
												$sql .= $order_part.implode(',',$parts);
											}
										}

										$query = $mysqli->prepare($sql);
										$query->execute();
										$query->store_result();
										$meetings_num_rows = $query->num_rows;
										$meetings = fetch($query);
									}
									else if($id_rdv_report > 0 && !@$is_specific_filter){	
										$is_appears = 1;
										
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
												LEFT JOIN dne_progress_status ps ON m.id_progress_status = ps.id
												LEFT JOIN dne_responsibles r ON m.id_responsible = r.id
												WHERE m.id_project = ?
												AND m.id_chapter = ? 
												AND m.is_appears = ? 
												AND FIND_IN_SET(?, m.ids_rdv) > 0
												AND m.id_progress_status <> ?";
												
										if(@$sort_select_1 == '' && @$sort_select_2 == '' 
										   && @$sort_select_3 == ''){
											if($lang == 'HE')
												$sql = $sql.' ORDER BY m.subject,m.id_area,t.name_he';
											else
												$sql = $sql.' ORDER BY m.subject,m.id_area,t.name ';		
										}
										else {
											$order_part = ' ORDER BY ';
											$parts = [];

											$sorts = [
												['col' => $sort_select_1 ?? ''],
												['col' => $sort_select_2 ?? ''],
												['col' => $sort_select_3 ?? '']
											];

											foreach($sorts as $s){
												if($s['col'] === '') continue;

												switch ($s['col']) {
													case 'subject':      $parts[] = "TRIM(REPLACE(m.subject, '&nbsp;', ''))"; break;
													case 'area':         $parts[] = "TRIM(REPLACE(m.area, '&nbsp;', ''))"; break;
													case 'description':  $parts[] = "m.description"; break;
													case 'responsible':  $parts[] = "r.name"; break;
													case 'task':               
														$parts[] = ($lang == 'HE' ? "t.name_he" : "t.name"); 
														break;
													case 'task_creation_date': $parts[] = "m.task_creation_date"; break;
													case 'destination_date':   $parts[] = "m.destination_date"; break;
													case 'status':             
														$parts[] = ($lang == 'HE' ? "ps.name_he" : "ps.name"); 
														break;
												}
											}
											
											if(!empty($parts)){
												$sql .= $order_part . implode(',', $parts);
											}
										}										
		
										$query = $mysqli->prepare($sql);
										$query->bind_param("iiiii",$project_id,$chapter_id,$is_appears,$id_rdv_report,$ps_archive_id);
										$query->execute(); 
										$query->store_result();
										$meetings_num_rows = $query->num_rows;
										$meetings = fetch($query);			
									}									
									
									if($meetings_num_rows > 0){ ?>
										<tr class="bgColor-cbddec height40">
										  <td class="<?=@$border_cell_table_start?> <?=@$border_cell_table_end?>" colspan="<?=sizeof($columns_list_array)+2?>" style="<?=@$text_align?>;<?=@$padding?>:5px;">
											<a class="text-decoration-underline cursor-pointer" onclick="redirectToAddTaskForThisChapter(<?=@$chapter_id?>);"><strong><?=@$chapter_name?></strong></a>
										  </td>
										</tr>
										<?php 	
										foreach($meetings as $item){
											$meeting_id = @$item->id;
											$user_id = @$item->id_user;
											$is_priority = @$item->is_priority;
											$task_id = @$item->id_task;
											$responsible_id = @$item->id_responsible;
											$pass_on_id = @$item->id_pass_on;
											$progress_status_id = @$item->id_progress_status;
											$ids_rdv = @$item->ids_rdv;
											$id_chapter = @$item->id_chapter;
											$id_task_type = @$item->id_task_type;
											$subject = @$item->subject;
											$area = @$item->area;

											$task_creation_date = '';
											if(@$item->task_creation_date != '0000-00-00')
												$task_creation_date = @$item->task_creation_date;

											$destination_date = '';
											if(@$item->destination_date != '0000-00-00')
												$destination_date = @$item->destination_date;

											$updated_date = @$item->updated_date;

											$is_change_row_style = @$item->is_change_row_style;

											$image1 = @$item->image1;
											$is_appears_img1 = @$item->is_appears_img1;
											$image1_width = @$item->image1_width;
											$image1_height = @$item->image1_height;

											$image2 = @$item->image2;
											$is_appears_img2 = @$item->is_appears_img2;
											$image2_width = @$item->image2_width;
											$image2_height = @$item->image2_height;

											$description_popup = nl2br(@$item->description);
											$description = @$item->description;

											$id_track_responsible = @$item->id_track_responsible;
											$query = $mysqli->prepare("SELECT nickname FROM dne_users WHERE id = ?");
											$query->bind_param('i',$id_track_responsible);	
											$query->execute(); 
											$query->store_result();
											$track_responsible = fetch_unique($query);
											$track_responsible_name = @$track_responsible->nickname;
											
											$track_type = @$item->track_type;
											$num_bgcolor = 'background-color:#cbddec';
											$num_border_color = 'border:1px solid transparent';
											
											// num_bgcolor stays #cbddec regardless of track_type
											
											if(@$track_type && @$id_track_responsible == @$_SESSION['id_user'])
												$num_border_color = 'border:2px solid '.@$bg_color_inputs->f_bgcolor;
											
											$reminder_time = @$item->reminder_time;
											$reminder_date = @$item->reminder_date;
											
											$query = $mysqli->prepare("SELECT * FROM dne_progress_status 
											                          WHERE id = ?");
											$query->bind_param("i",$progress_status_id);
											$query->execute();
											$query->store_result();
											$query = fetch_unique($query);
											
											$progress_status_he = @$query->name_he;
											$progress_status = $progress_status_he;
											if(@$lang != 'HE')
												$progress_status = @$query->name;
											if(@$is_colors){
											   $progress_status_color = 'color:'.@$query->color;
											   $progress_status_bgcolor = 'background-color:'.@$query->bgcolor;
											}
											
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
											
											$dir_log_meeting_updates = 'alignRight';
												if(@$item->lang == 'EN')
													$dir_log_meeting_updates = 'alignLeft';
		
											foreach($log_meeting_updates as $item){
												$remark = @$item->remark;	
												$action_date = @$item->action_date;
												$progress_status_log_updates = @$item->ps_name_he;
												if(@$lang == 'EN')
													$progress_status_log_updates = @$item->ps_name;
											    
												if(@$remark != ''){
													$description .= "<div class='marginTop5 colorGreen display-block".@$dir_log_meeting_updates."'>"
																	."<span style='direction:".@$dir.";unicode-bidi:embed;'>"
																	."[".smartDate(@$action_date, $lang)."]"
																	."</span> "
																	." <span style='direction:".@$dir.";unicode-bidi:embed;'>"
																	.@$item->user_nickname;
													
													if(preg_match('/\p{L}/u', $progress_status_log_updates)){
														$description .=  " - "
																		."<span style='direction:".@$dir.";unicode-bidi:embed;'>"
																		.@$progress_status_log_updates
																		."</span>";
													}
													
													$description .=  " : "
																	."<span style='direction:".@$dir.";unicode-bidi:embed;white-space:nowrap;'>"
																	.html_entity_decode(@$remark)
																	."</span>"
																	."</div>";
												}
											}

                                            $query = $mysqli->prepare("SELECT lmt.remark AS remark,
											                           lmt.action_date AS action_date,
																	   u.nickname AS user_nickname
											                           FROM dne_log_meeting_tracking lmt
																	   LEFT JOIN dne_users u ON lmt.id_user = u.id
											                           WHERE lmt.id_meeting = ? 
																	   AND lmt.is_remark_appears_log = ?
																	   AND lmt.remark <> ?
																	   ORDER BY lmt.id");
											$query->bind_param("iis",$meeting_id,$one,$empty_remark);
											$query->execute();
											$query->store_result();	
											$log_meeting_tracking = fetch($query);

											$tracking_data = '';
											if($reminder_date != '0000-00-00')
												$tracking_data = '('.smartDate($reminder_date, $lang).')';

                                            $dir_log_meeting_tracking = 'alignRight';
												if(@$lang == 'EN')
													$dir_log_meeting_tracking = 'alignLeft';

                                            foreach($log_meeting_tracking as $item){
												$remark = @$item->remark;
												$action_date = @$item->action_date;

												if(@$track_type && @$remark != ''){
													$track_initials = mb_strtoupper(mb_substr(@$item->user_nickname, 0, 2, 'UTF-8'));
													$description .= "<div id='div-tracking-remarks-'".@$meeting_id."' class='marginTop5 display-block ".@$dir_log_meeting_tracking."' style='line-height:1.8;'>"
																  . "<span class='badge-circle' style='display:inline-flex;background-color:#333;width:15px;height:15px;font-size:8px;vertical-align:middle;'>".$track_initials."</span>"
																  . " <span class='colorGrey ".(@$lang=='HE' ? 'dir-rtl' : 'dir-ltr')." unicode-bidi-embed' style='vertical-align:top;'>".smartDate(@$item->action_date, $lang)."</span>"
																  . " : <span class='colorRed ".(@$lang=='HE' ? 'dir-rtl' : 'dir-ltr')." unicode-bidi-embed'>".html_entity_decode(@$item->remark)."</span>"
																  . (@$tracking_data != '' ? " <span class='colorRed ".(@$lang=='HE' ? 'dir-rtl' : 'dir-ltr')." unicode-bidi-embed'>".@$tracking_data."</span>" : "")
																  . "</div>";
												}
											}											
											
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

												if($image1_height > $max_height){
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
 
												if($image2_width > $max_width) {
													$image2_width = $max_width;
													$image2_height = $image2_width/$ratio;
												}

												if($image2_height > $max_height){
													$image2_height = $max_height;
													$image2_width = $image2_height*$ratio;
												}
											}	
																		
											$subject_bgcolor = 'background-color:white';
											$area_bgcolor = 'background-color:white';	 
											$description_bgcolor = 'background-color:white';
											
											$query = $mysqli->prepare("SELECT * FROM dne_tasks WHERE id = ?");
											$query->bind_param("i",$task_id);
											$query->execute();
											$query->store_result();
											$query = fetch_unique($query);
											$task = @$query->name_he;
											if(@$lang != 'HE')
												$task = @$query->name;
											if(@$is_colors) {
											   $task_color = 'color:'.@$query->color;
											   $task_bgcolor = 'background-color:'.@$query->bgcolor;
											}
											
											$query = $mysqli->prepare("SELECT * FROM dne_responsibles 
											                          WHERE id = ?");
											$query->bind_param("i",$responsible_id);
											$query->execute();
											$query->store_result();
											$query = fetch_unique($query);
											$responsible = @$query->name;
											$responsible_email = @$query->email;
											if(@$is_colors){
										  	   $responsible_color = 'color:'.@$query->color;
											   $responsible_bgcolor = 'background-color:'.@$query->bgcolor;							   
											}	
											
											$query = $mysqli->prepare("SELECT * FROM dne_responsibles 
											                          WHERE id = ?");
											$query->bind_param("i",$pass_on_id);
											$query->execute();
											$query->store_result();
											$query = fetch_unique($query);
											$pass_on = @$query->name;
											$pass_on_bgcolor = 'background-color:white';							   																							
											
											$task_creation_date_color = 'color:black';
											if(@$is_colors && !empty($ids_rdv) && !empty($id_rdv_report) 
											   && strpos($ids_rdv,(string)$id_rdv_report) !== false)
												$task_creation_date_color = 'color:green';
											
											$task_creation_date_bgcolor = 'background-color:white';
											
											$dest_date_color = 'color:black';
											$dest_date_bgcolor = 'background-color:white';
											
											if(@$is_colors && $destination_date < date('Y-m-d')) 
											   $dest_date_color = 'color:red;';										   
											
											if(@$is_colors && $progress_status_he == 'בוצע/נמסר'){
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
											elseif(@$is_colors && $is_change_row_style && $task == 'בקרת איכות'){
												   $subject_bgcolor = 'background-color:#fafd49';
												   $area_bgcolor = 'background-color:#fafd49';
												   $description_bgcolor = 'background-color:#fafd49';
											}										
          									
											$end_new_tasks_date = '0000-00-00';
											$end_updated_date = '0000-00-00';
											
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
											
											$td_count_bgcolor = 'background-color:white';										
											if(@$is_colors && @$progress_status != 'בוצע/נמסר' && @$task != 'בקרת איכות'){
												if(strlen(@$period_new_tasks) > 1 && (date('Y-m-d') <= $end_new_tasks_date)){			
													$td_count_bgcolor = 'background-color:'.$global_bgcolor_new_task->bgcolor;
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
											
											$count++;
		                                    $iteration++;							
											?>
											<input type="hidden" id="task_created_by_<?=@$meeting_id?>" value="<?=@$user_id?>" />
											<input type="hidden" id="task_created_by_<?=@$meeting_id?>" value="<?=@$user_id?>" />
											<input type="hidden" id="id_chapter_<?=@$meeting_id?>" value="<?=@$id_chapter?>" />
											<input type="hidden" id="id_task_type_<?=@$meeting_id?>" value="<?=@$id_task_type?>" />
											<input type="hidden" id="is_appears_img1_<?=@$meeting_id?>" value="<?=@$is_appears_img1?>" />
											<input type="hidden" id="is_appears_img2_<?=@$meeting_id?>" value="<?=@$is_appears_img2?>" />											
											<input type="hidden" id="track_type_<?=@$meeting_id?>" value="<?=@$track_type?>" />
											<input type="hidden" id="is_priority_<?=@$meeting_id?>" value="<?=@$is_priority?>" />
											<input type="hidden" id="action_date_remark_<?=@$meeting_id?>" value="<?=@$action_date_remark?>" />
											<input type="hidden" id="hidden_remark_<?=@$meeting_id?>" value="<?=@$remark?>" />
											
											<tr id="row_<?=@$iteration?>" class="meeting_<?=@$meeting_id?>">
											    <td id="td_cbx_meetings_to_update_<?=@$meeting_id?>" class="alignCenter <?=@$border_cell_table_start?> <?=@$border_cell_cbx?>" style="<?=@$td_count_bgcolor?>">
												    <input type="checkbox" id="meetings_to_update_cbx_<?=@$meeting_id?>" name="meetings_to_update_cbx[]" class="meetings_to_update_cbx" value="<?=@$meeting_id?>" />	
												</td>
												<td id="td_count_<?=@$meeting_id?>" class="alignCenter <?=@$border_cell_number?>" style="<?=@$td_count_bgcolor?>">
													<div style="display:flex;flex-direction:column;align-items:center;justify-content:center;gap:5px;height:100%;width:100%;">
														<a id="task_actions_<?=@$meeting_id?>" data-projectid="<?=@$project_id?>" data-lang="<?=@$project->lang?>" data-meetingid="<?=@$meeting_id?>" data-iteration=<?=@$iteration?> data-userid="<?=@$user_id?>" data-ispriority="<?=@$is_priority?>" data-remark="<?=@$remark?>" data-chapter="<?=@$chapter_name?>" data-name="<?=@$subject?>" data-area="<?=@$area?>" data-recipient="<?=@$responsible_email?>" data-responsibleid="<?=@$responsible_id?>" data-destinationdate="<?=@$destination_date?>" data-progresstatusid="<?=@$progress_status_id?>" data-trackresponsibleid="<?=@$id_track_responsible?>" data-tracktype="<?=@$track_type?>" data-reminderdate="<?=@$reminder_date?>" data-remindertime="<?=@$reminder_time?>" class="text-decoration-none cursor-pointer fontSize12 padding-7x-3y font-weight-bold borderRadius10" style="<?=@$num_bgcolor?>!important;<?=@$num_border_color?>;display:inline-block;"><?=@$count?></a><?php if(@$track_type && @$track_responsible_name != ''): ?><span class="badge-circle" style="background-color:#e53935;width:18px;height:18px;font-size:9px;display:inline-flex;box-shadow:none;" title="<?=htmlspecialchars(@$track_responsible_name)?>"><?=mb_strtoupper(mb_substr(@$track_responsible_name, 0, 2, 'UTF-8'))?></span><?php endif; ?>
													</div>
												</td>
												
												<?php if(in_array('subject',$columns_list_array)){ ?>
													<td id="td_subject_<?=@$meeting_id?>" class="<?php if(end($columns_list_array) == "subject") echo $border_cell_table_end;?>" style="<?=@$text_align?>;<?=@$padding?>:5px;<?=@$subject_bgcolor?>;">
														<div class="height-auto fontSize12 cursor-pointer overflow-y-scroll" id="subject_<?=@$meeting_id?>" name="subject_<?=@$meeting_id?>" style="direction:<?=@$dir?>;width:100%;<?=@$padding?>:5px;<?=@$subject_bgcolor?>;" contenteditable="true" onblur="setData(<?=@$meeting_id?>,<?=@$iteration?>,'subject',0,0,'screen');">
															<?=html_entity_decode($subject)?>
														</div>
													</td>
												<?php }

												if(in_array('area',$columns_list_array)){ ?> 
													<td id="td_area_<?=@$meeting_id?>" class="<?php if(end($columns_list_array) == "area") echo $border_cell_table_end;?>" style="<?=@$text_align?>;<?=@$padding?>:5px;<?=@$area_bgcolor?>;" contenteditable="true" onblur="setData(<?=@$meeting_id?>,<?=@$iteration?>,'area',0,0,'screen');">
														<div class="height-auto fontSize12 cursor-pointer overflow-y-scroll" id="area_<?=@$meeting_id?>" name="area_<?=@$meeting_id?>" style="direction:<?=@$dir?>;width:100%;<?=@$padding?>:5px;<?=@$area_bgcolor?>;" contenteditable="true">
															<?=html_entity_decode($area)?>
														</div>
													</td>																	
												<?php } 
												
												if(in_array('description',$columns_list_array)){ ?>
												    <td id="td_description_<?=@$meeting_id?>" class="<?php if(end($columns_list_array) == "description") echo $border_cell_table_end;?>" style="<?=@$text_align?>;<?=@$padding?>:5px;<?=@$description_bgcolor?>;">
														<div class="height-auto fontSize12 cursor-pointer overflow-y-scroll" name="description_<?=@$meeting_id?>" id="description_<?=@$meeting_id?>" style="direction:<?=@$dir?>;width:100%;<?=@$padding?>:5px;<?=@$description_bgcolor?>;" contenteditable="true" data-meetingid="<?=@$meeting_id?>" data-iteration=<?=@$iteration?> data-chapter="<?=@$chapter_name?>" data-name="<?=@$subject?>" data-area="<?=@$area?>" data-description="<?=htmlspecialchars($description_popup,ENT_QUOTES)?>">
															<?=html_entity_decode($description)?>
														</div>
													</td>
												<?php } 
												
												if(in_array('_task',$columns_list_array)){ ?>
													<td id="td_task_<?=@$meeting_id?>" class="<?php if(end($columns_list_array) == "_task") echo $border_cell_table_end;?> cursor-pointer" style="<?=@$task_bgcolor?>">
														<select id="task_<?=@$meeting_id?>" class="form-control font-weight-bold fontSize12 border-none cursor-pointer alignCenter" style="direction:<?=@$dir?>;width:98%;<?=@$task_color?>;<?=@$task_bgcolor?>;" onchange="setData(<?=@$meeting_id?>,<?=@$iteration?>,'id_task',0,0,'screen');">	
															<?php
															foreach($tasks as $task){
																if ($task->is_appears_tasks_list != 1 && $task->id != @$task_id) continue;
															?>
																<option value="<?=@$task->id?>" <?php if($task->id == @$task_id) echo "selected";?>>
																	<strong><?php if($lang == 'HE') echo @$task->name_he;else if($lang == 'EN') echo @$task->name?></strong>
																</option>
																<?php
															}
															?>         														
														</select>
													</td>
											    <?php }
												
												if(in_array('responsible',$columns_list_array)){ ?>
													<td id="td_responsible_<?=@$meeting_id?>" class="<?php if(end($columns_list_array) == "responsible") echo $border_cell_table_end;?> cursor-pointer" style="<?=@$responsible_bgcolor?>">
														<select id="responsible_<?=@$meeting_id?>" class="form-control fontSize12 border-none font-weight-bold cursor-pointer alignCenter" style="direction:<?=@$dir?>;width:98%;<?=@$responsible_color?>;<?=@$responsible_bgcolor?>;" onchange="setData(<?=@$meeting_id?>,<?=@$iteration?>,'id_responsible',0,0,'screen');">
															<?php 
															foreach($responsibles as $item){
															?>
																<option value="<?=@$item->id?>" <?php if($item->id == @$responsible_id) echo "selected";?>>
																	<strong><?=@$item->name?></strong>
																</option>
																<?php
															}
															?>
														</select>
													</td>
												<?php } 
												
												if(in_array('pass on',$columns_list_array)){ ?>
													<td id="td_pass_on_<?=@$meeting_id?>" class="<?php if(end($columns_list_array) == "pass on") echo $border_cell_table_end;?> cursor-pointer" style="<?=@$pass_on_bgcolor?>;">
														<select id="pass_on_<?=@$meeting_id?>" class="form-control fontSize12 border-none cursor-pointer alignCenter" style="direction:<?=@$dir?>;width:98%;<?=@$pass_on_bgcolor?>;" onchange="setData(<?=@$meeting_id?>,<?=@$iteration?>,'id_pass_on',0,0,'screen');">	
															<?php 
															foreach($responsibles as $item){
															?>
																<option value="<?=@$item->id?>" <?php if($item->id == @$pass_on_id) echo "selected";?>>
																	<strong><?=@$item->name?></strong>
																</option>
																<?php
															}
															?>						
														</select>
													</td>
												<?php } 
												
												if(in_array('task creation',$columns_list_array)){ ?>
													<td id="td_task_creation_date_<?=@$meeting_id?>" class="<?php if(end($columns_list_array) == "task creation") echo $border_cell_table_end;?> cursor-pointer alignCenter" style="<?=@$task_creation_date_bgcolor?>">
														<input type="text" id="task_creation_date_<?=@$meeting_id?>" name="task_creation_date_<?=@$meeting_id?>" class="fontSize13 border-none width60 cursor-pointer alignCenter" style="direction:<?=@$dir?>;<?=@$task_creation_date_color?>;<?=@$task_creation_date_bgcolor?>;" value="<?=smartDate(@$task_creation_date, $lang)?>" data-meetingid="<?=@$meeting_id?>" data-iteration=<?=@$iteration?> data-taskcreationdate="<?=@$task_creation_date?>" />
													</td>
												<?php }
												
												if(in_array('destination date',$columns_list_array)){ ?>
													<td id="td_destination_date_<?=@$meeting_id?>" class="<?php if(end($columns_list_array) == "destination date") echo $border_cell_table_end;?> cursor-pointer alignCenter" style="<?=@$text_align?>;<?=@$padding?>:5px;<?=@$dest_date_color?>;<?=@$dest_date_bgcolor?>;">			
													   <input type="text" id="destination_date_<?=@$meeting_id?>" name="destination_date_<?=@$meeting_id?>" class="fontSize13 border-none width60 cursor-pointer alignCenter" style="direction:<?=@$dir?>;<?=@$dest_date_color?>;<?=@$dest_date_bgcolor?>;" value="<?=smartDate(@$destination_date, $lang)?>" data-meetingid="<?=@$meeting_id?>" data-iteration="<?=@$iteration?>" data-chapter="<?=@$chapter_name?>" data-name="<?=@$subject?>" data-area="<?=@$area?>" data-destinationdate="<?=@$destination_date?>" />
													</td>
												<?php }
												
												if(in_array('progress status',$columns_list_array)){ ?>
													<td id="td_progress_status_<?=@$meeting_id?>" class="<?php if(end($columns_list_array) == "progress status") echo $border_cell_table_end;?> cursor-pointer" style="<?=@$text_align?>;<?=@$padding?>:5px;<?=@$progress_status_color?>;<?=@$progress_status_bgcolor?>;">
														<select id="_progress_status_<?=@$meeting_id?>" class="form-control fontSize12 font-weight-bold border-none cursor-pointer alignCenter" style="direction:<?=@$dir?>;width:98%;<?=@$progress_status_bgcolor?>;" data-meetingid="<?=@$meeting_id?>" data-iteration=<?=@$iteration?> data-chapter="<?=@$chapter_name?>" data-name="<?=@$subject?>" data-area="<?=@$area?>" data-destinationdate="<?=@$destination_date?>">
															<option>---סטטוס---</option>
															<?php 
															foreach($progress_status_s as $item){
															?>
																<option value="<?=@$item->id?>" <?php if($item->id == @$progress_status_id) echo "selected";?>>
																	<strong>
																		<?php 
																		if ($lang == 'HE'){
																			echo trim(@$item->name_he) !== '' ? @$item->name_he : '(ללא)';
																		} else {
																			echo trim(@$item->name) !== '' ? @$item->name : '(Without)';
																		}
																		?>
																	</strong>
																</option>
																<?php
															}
															?>						
														</select>
													</td>
												<?php } ?>
										    </tr>
											<?php
											if(@$is_images == 1 && (($image1 != '' && $is_appears_img1) || ($image2 != '' && $is_appears_img2)) && strpos($image1,'Snag') === false && strpos($image2,'Snag') === false){ ?>
											    <tr class="bgColor-f5f6f8 tr-image-row">
												   <td class="<?=@$border_cell_table_start?> <?=@$border_cell_table_end?>" colspan="<?=@$colspan_image_tr?>">
													  <img class="<?=@$margin_between_images?> object-fit-fixed" src="<?='uploads/'.$image1?>" width="<?=@$image1_width?>" height="<?=@$image1_height?>" />
												      <?php if($image2 != '' && $is_appears_img2 && strpos($image2,'Snag') === false) { ?>
													      <img class="object-fit-fixed" src="<?='uploads/'.$image2?>" width="<?=@$image2_width?>" height="<?=@$image2_height?>" />
											          <?php } ?>
												   </td>
												</tr>
											<?php }
										}
									}
								}
								?>
							</table>		
						</div>
					</div>
					<?php } 
					
					if(@$is_images == 2 && @$all_meetings_with_images_num_rows > 0){ ?>
						<div class="row" style="direction:<?=@$dir?>;">
							<label class="<?=@$margin_all_label?> colorBlack font-weight-bold"><?=@$image_concentration_label?></label>
							<div class="col-12 overflow-x-scroll mx-2">
								<table class="marginTop5" border="1">							
									<tr class="bgColor-f2f6f9 height50">
										<?php if(in_array('subject',$columns_list_array)) { ?>
											<th class="alignCenter" width="11%"><?=@$subject_label?></th>
										<?php } 
										
										if(in_array('area',$columns_list_array)){ ?>    
										   <th class="alignCenter" width="11%"><?=@$area_label?></th>
										<?php } 
										
										if(in_array('description',$columns_list_array)){ ?>    
											<th class="alignCenter"><?=@$description_label?></th>
										<?php } 
										
										if(in_array('_task',$columns_list_array)){ ?>    
										  <th class="alignCenter" width="8%"><?=@$task_type_label?></th>	
										<?php }
										
										if(in_array('responsible',$columns_list_array)){ ?>    
										   <th class="alignCenter" width="8%"><?=@$responsible_label?></th>
										<?php }
										
										if(in_array('pass on',$columns_list_array)){ ?>   
										   <th class="alignCenter fontSize13" width="8%"><?=@$pass_on_label?></th>
										<?php }
										
										if(in_array('task creation',$columns_list_array)){ ?>   
										   <th class="alignCenter" width="8%"><?=@$task_creation_date_label?></th>
										<?php }
										
										if(in_array('destination date',$columns_list_array)){ ?>   
										   <th class="alignCenter" width="8%"><?=@$target_date_label?></th>
										<?php }
										
										if(in_array('progress status',$columns_list_array)){ ?>   
										   <th class="alignCenter" width="8%"><?=@$status_label?></th>
										<?php } ?>
									</tr>
									<?php	
									foreach($chapters as $item){	
										$chapter_id = $item->id;
										$chapter_name = $item->name;
										
										if($id_custom_report > 0 || ($id_rdv_report > 0 && $is_specific_filter)){
											$sql_array = explode(' AND ',$sql);							
									
											for($i=0;$i<sizeof($sql_array);$i++){
												if(strpos($sql_array[$i],'m.id_chapter') !== false && $i > 0){
													$sql_array[$i] = 'm.id_chapter ='.$chapter_id;
													$sql = implode(' AND ',$sql_array);
												}
											}
											
											if(strpos($where_part_sql,'m.id_chapter') === false) 
											  $sql.= ' AND m.id_chapter ='.$chapter_id;

											$query = $mysqli->prepare($sql);
											$query->execute();
											$query->store_result();
											$meetings = fetch($query);
											
											$counter_with_image = 0;
											foreach ($meetings as $item){							
												if($item->image1 != '' && $item->is_appears_img1 == 1) 
													$counter_with_image++;
											}
                      										
											$query = $mysqli->prepare($sql." ORDER BY t.id_display,m.subject,m.id_area,m.destination_date DESC");
											$query->execute();
											$query->store_result();
											$meetings = fetch($query);	
										}
										else if($id_rdv_report > 0 && !$is_specific_filter){
											$is_appears = 1;	
											$query = $mysqli->prepare('SELECT * FROM dne_meetings 
																	  WHERE id_project = ? 
																	  AND id_chapter = ? 
																	  AND is_appears = ? 
																	  AND id_progress_status <> ?
																	  AND FIND_IN_SET(?, ids_rdv) > 0');
											$query->bind_param("iiiis",$project_id,$chapter_id,$is_appears,$ps_archive_id,$id_rdv_report);
											$query->execute();
											$query->store_result();
											$meetings = fetch($query);
											
											$counter_with_image = 0;
											foreach ($meetings as $item){							
												if($item->image1 != '' && $item->is_appears_img1) 
													$counter_with_image++;
											}
											
											$query = $mysqli->prepare("SELECT m.id AS id,m.is_priority AS is_priority,
											                           m.id_chapter AS id_chapter,
											                           m.subject AS subject,m.ids_rdv AS ids_rdv,m.area AS area,
																       m.description AS description,m.id_task AS id_task ,m.id_responsible AS id_responsible,
																       m.id_pass_on AS id_pass_on,m.task_creation_date AS task_creation_date,m.destination_date AS destination_date,
																       m.id_progress_status AS id_progress_status,m.id_task_type AS id_task_type,m.is_change_row_style AS is_change_row_style,
																       m.image1 AS image1,m.is_appears_img1 AS is_appears_img1,m.image1_width AS image1_width,m.image1_height AS image1_height,
																       m.image2 AS image2,m.is_appears_img2 AS is_appears_img2,m.image2_width AS image2_width,m.image2_height AS image2_height,
																       m.updated_date AS updated_date,m.id_track_responsible AS id_track_responsible,m.track_type AS track_type 
																       FROM dne_meetings m 
																       LEFT JOIN dne_tasks t ON m.id_task = t.id
																       WHERE m.id_project = ? 
																	   AND m.id_chapter = ? 
																	   AND m.is_appears = ? 
																	   AND FIND_IN_SET(?, m.ids_rdv) > 0
																	   AND m.id_progress_status <> ?
																       ORDER BY t.id_display,m.subject,m.id_area,m.destination_date DESC");
											$query->bind_param("iiiii",$project_id,$chapter_id,$is_appears,$id_rdv_report,$ps_archive_id);
											$query->execute(); 
											$query->store_result();
											$meetings = fetch($query);						
										}
										
										if($counter_with_image > 0){
										?>
											<tr class="bgColor-cbddec height40">
											  <td colspan="14" style="<?=@$text_align?>;<?=@$padding?>:5px;">
												 <strong><?=@$chapter_name?></strong>
											  </td>
											</tr>
											<?php 
											foreach($meetings as $item){
												$meeting_id = @$item->id;
												$user_id = @$item->id_user;
												$task_id = @$item->id_task;
												$responsible_id = @$item->id_responsible;
												$pass_on_id = @$item->id_pass_on;
												$progress_status_id = @$item->id_progress_status;
												$ids_rdv = @$item->ids_rdv;
												$id_chapter = @$item->id_chapter;
												$id_task_type = @$item->id_task_type;
												$subject = @$item->subject;
												$area = @$item->area;
												$task_creation_date = '';
												if(@$item->task_creation_date != '0000-00-00')
													$task_creation_date = @$item->task_creation_date;

												$destination_date = '';
												if(@$item->destination_date != '0000-00-00')
													$destination_date = @$item->destination_date;

												$updated_date = @$item->updated_date;

												$description = @$item->description;
												
												$id_track_responsible = @$item->id_track_responsible;
												$query = $mysqli->prepare("SELECT nickname FROM dne_users WHERE id = ?");
												$query->bind_param('i',$id_track_responsible);	
												$query->execute(); 
												$query->store_result();
												$track_responsible = fetch_unique($query);
												$track_responsible_name = @$track_responsible->nickname;										
												
												$track_type = @$item->track_type;
												$is_change_row_style = @$item->is_change_row_style;
												
												$image1 = @$item->image1;
												$is_appears_img1 = @$item->is_appears_img1;
												$image1_width = @$item->image1_width;
												$image1_height = @$item->image1_height;
												
												$image2 = @$item->image2;
												$is_appears_img2 = @$item->is_appears_img2;
												$image2_width = @$item->image2_width;
												$image2_height = @$item->image2_height;
												
                                                $one = 1;
                                                $empty_remark = '';		
											    $query = $mysqli->prepare("SELECT lmu.remark AS remark,
											                               lmu.action_date AS action_date,
																	       u.nickname AS user_nickname
											                               FROM dne_log_meeting_updates lmu
																	       LEFT JOIN dne_users u ON lmu.id_user = u.id
											                               WHERE lmu.id_meeting = ? 
																	       AND lmu.is_remark_appears_log = ?
																	       AND lmu.remark <> ?
																	       ORDER BY lmu.id DESC");
											    $query->bind_param("iis",$meeting_id,$one,$empty_remark);
											    $query->execute();
											    $query->store_result();	
											    $log_meeting_updates = fetch($query);
												
											    $reminder_date = @$item->reminder_date;
												
												$dir_log_meeting_updates = 'alignRight paddingRight10';
												if(@$item->lang == 'EN')
													$dir_log_meeting_updates = 'alignLeft paddingLeft10';
												
												foreach($log_meeting_updates as $item){
													$remark = @$item->remark;	
													$action_date = @$item->action_date;
													
													if(@$remark != '')
														$description .= "<div class='marginTop5 colorGreen display-block ".@$dir_log_meeting_updates."'>"
																	    .@$item->user_nickname." "
																	    ."<span style='direction:".@$dir.";unicode-bidi:embed;'>"
																		."[".smartDate(@$action_date, $lang)."]"
																		."</span> : "
																		.html_entity_decode(@$remark)
																		 ."</div>";
												}
																								
												$query = $mysqli->prepare("SELECT lmt.remark AS remark,
											                               lmt.action_date AS action_date,
																	       u.nickname AS user_nickname
											                               FROM dne_log_meeting_tracking lmt
																	       LEFT JOIN dne_users u ON lmt.id_user = u.id
											                               WHERE lmt.id_meeting = ? 
																	       AND lmt.is_remark_appears_log = ?
																	       AND lmt.remark <> ?
																	       ORDER BY lmt.id DESC");
												$query->bind_param("iis",$meeting_id,$one,$empty_remark);
												$query->execute();
												$query->store_result();	
												$log_meeting_tracking = fetch($query);	
												
												$tracking_data = '';
											    
												if($reminder_date != '0000-00-00')
													$tracking_data = '('.smartDate($reminder_date, $lang).')';

                                                $dir_log_meeting_tracking = 'alignRight paddingRight10';
												if(@$lang == 'EN')
													$dir_log_meeting_tracking = 'alignLeft paddingLeft10';

												foreach($log_meeting_tracking as $item){
													$remark = @$item->remark;	
													$action_date = @$item->action_date;
											    
													if(@$track_type && @$remark != ''){
														$track_initials2 = mb_strtoupper(mb_substr(@$item->user_nickname, 0, 2, 'UTF-8'));
														$description .= "<div id='div-tracking-remarks-'".@$meeting_id."' class='marginTop5 display-block ".@$dir_log_meeting_tracking."' style='line-height:1.8;'>"
																	  . "<span class='badge-circle' style='display:inline-flex;background-color:#333;width:15px;height:15px;font-size:8px;vertical-align:middle;'>".$track_initials2."</span>"
																	  . " <span class='colorGrey ".(@$lang=='HE' ? 'dir-rtl' : 'dir-ltr')." unicode-bidi-embed' style='vertical-align:top;'>".smartDate(@$item->action_date, $lang)."</span>"
																	  . " : <span class='colorRed ".(@$lang=='HE' ? 'dir-rtl' : 'dir-ltr')." unicode-bidi-embed'>".html_entity_decode(@$item->remark)."</span>"
																	  . (@$tracking_data != '' ? " <span class='colorRed ".(@$lang=='HE' ? 'dir-rtl' : 'dir-ltr')." unicode-bidi-embed'>".@$tracking_data."</span>" : "")
																	  . "</div>";
													}
												}																	
												
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
												
												$subject_bgcolor = 'background-color:white';
												$area_bgcolor = 'background-color:white';
												$description_bgcolor = 'background-color:white';
												
												$query = $mysqli->prepare("SELECT * FROM dne_tasks WHERE id = ?");
												$query->bind_param("i",$task_id);
												$query->execute();
												$query->store_result();
												$query = fetch_unique($query);				
												$task = @$query->name_he;
											    if(@$lang != 'HE')
												   $task = @$query->name;
												if(@$is_colors) {
												   $task_color = 'color:'.@$query->color;
												   $task_bgcolor = 'background-color:'.@$query->bgcolor;
											    }
												
												$query = $mysqli->prepare("SELECT * FROM dne_responsibles WHERE id = ?");
												$query->bind_param("i",$responsible_id);
												$query->execute();
												$query->store_result();
												$query = fetch_unique($query);
												$responsible = @$query->name;
												if(@$is_colors) {
											        $responsible_color = 'color:'.@$query->color;
												    $responsible_bgcolor = 'background-color:'.@$query->bgcolor;
												}
												
												$query = $mysqli->prepare("SELECT * FROM dne_responsibles WHERE id = ?");
												$query->bind_param("i",$pass_on_id);
												$query->execute();
												$query->store_result();
												$query = fetch_unique($query);
												$pass_on = @$query->name;
												if(@$is_colors) 
													$pass_on_bgcolor = 'background-color:white';
												
												$query = $mysqli->prepare("SELECT * FROM dne_progress_status WHERE id = ?");
												$query->bind_param("i",$progress_status_id);
												$query->execute();
												$query->store_result();
												$query = fetch_unique($query);
												$progress_status_he = @$query->name_he;
												$progress_status = $progress_status_he;
												if($lang != 'HE')
												   $progress_status = @$query->name;
												if(@$is_colors) {
											    	$progress_status_color = 'color:'.@$query->color;
												    $progress_status_bgcolor = 'background-color:'.@$query->bgcolor;
												}
												
												$task_creation_date_color = 'color:black';
												if($is_colors && strpos($ids_rdv, (string) $id_rdv_report) !== false)
													$task_creation_date_color = 'color:green';
												
												$task_creation_date_bgcolor = 'background-color:white';
												
												$dest_date_color = 'color:black';
												$dest_date_bgcolor = 'background-color:white';
												
												if($is_colors && $destination_date < date('Y-m-d'))  
												   $dest_date_color = 'color:red;';
		
												if($is_colors && $progress_status_he == 'בוצע/נמסר') {
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
												elseif($is_colors && $is_change_row_style && $task == 'בקרת איכות') {
													$subject_bgcolor = 'background-color:#fafd49';
													$area_bgcolor = 'background-color:#fafd49';
													$description_bgcolor = 'background-color:#fafd49';
													$task_bgcolor = 'background-color:#fafd49';
													$responsible_bgcolor = 'background-color:#fafd49';
													$pass_on_bgcolor = 'background-color:#fafd49';
													$task_creation_date_bgcolor = 'background-color:#fafd49';
													$dest_date_bgcolor = 'background-color:#fafd49';
													$progress_status_bgcolor = 'background-color:#fafd49';
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
											
												if($is_colors && @$progress_status != 'בוצע/נמסר' && @$task != 'בקרת איכות') {
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
												
												if($image1 != '' && $is_appears_img1) { ?>
												<tr>
													<?php if(in_array('subject',$columns_list_array)) { ?>
														<td style="<?=@$text_align?>;<?=@$padding?>:5px;<?=@$subject_bgcolor?>;">
															<?=html_entity_decode(@$subject)?>
														</td>
													<?php }

													if(in_array('area',$columns_list_array)) { ?> 
														<td style="<?=@$text_align?>;<?=@$padding?>:5px;<?=@$area_bgcolor?>;">
															<?=html_entity_decode(@$area)?> 
														</td>
													<?php } 
													
													if(in_array('description',$columns_list_array)) { ?>
														<td style="<?=@$text_align?>;<?=@$padding?>:5px;<?=@$description_bgcolor?>"><?=html_entity_decode($description)?></td>
													<?php } 
													
													if(in_array('_task',$columns_list_array)) { ?>
														<td style="<?=@$text_align?>;<?=@$padding?>:5px;<?=@$task_bgcolor?>">														
														    <?=@$task?>					
														</td>
													<?php }
													
													if(in_array('responsible',$columns_list_array)) { ?>
														<td style="<?=@$text_align?>;<?=@$padding?>:5px;<?=@$responsible_bgcolor?>">
															<?=@$responsible?>
														</td>
													<?php } 
													
													if(in_array('pass on',$columns_list_array)) { ?>
														<td style="<?=@$text_align?>;<?=@$padding?>:5px;<?=@$pass_on_bgcolor?>;">
    														<?=@$pass_on?>			
														</td>
													<?php } 
													
													if(in_array('task creation',$columns_list_array)) { ?>
														<td class="alignCenter" style="<?=@$task_creation_date_bgcolor?>;">
															<?=smartDate(@$task_creation_date, $lang)?>
														</td>
													<?php }

													if(in_array('destination date',$columns_list_array)) { ?>
														<td class="alignCenter" style="<?=@$dest_date_color?>;<?=@$dest_date_bgcolor?>;">
															<?=smartDate(@$destination_date, $lang)?>
													<?php }

													if(in_array('progress status',$columns_list_array)) { ?>
														<td style="<?=@$text_align?>;<?=@$padding?>:5px;<?=@$progress_status_color?>;<?=@$progress_status_bgcolor?>;">
															<?=@$progress_status?>
														</td>
													<?php } ?>
													
								                </tr>
												<?php if(strpos($image1,'Snag') === false && strpos($image2,'Snag') === false) { ?>
												       <tr class="bgColor-f5f6f8 tr-image-row">
													       <td colspan="<?=sizeof(@$columns_list_array)?>">
														       <img class="<?=@$margin_between_images?> object-fit-fixed" src="<?='uploads/'.$image1?>" width="<?=@$image1_width?>" height="<?=@$image1_height?>" />
															   <?php if($image2 != '' && $is_appears_img2 && strpos($image2,'Snag') === false) { ?>
															       <img class="object-fit-fixed" src="<?='uploads/'.$image2?>" width="<?=@$image2_width?>" height="<?=@$image2_height?>" />
														       <?php } ?>
														  </td>
														</tr>
												<?php } }
											}
										}
									}
									?>
								</table>		
							</div>
						</div>
					<?php }	?>
				</div>
		</form>
		
		<div class="modal fade dir-rtl" id="modalFilterBySupplier" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
				<div class="modal-content">
                    <div class="modal-header">
						<button type="button" class="btn-close btn-close-white-small" data-bs-dismiss="modal" aria-label="Close"></button>
					    <div class="modal-title"></div>
					</div>				
					<div class="modal-body">	
					    <div id="modalContent">
							<h5 class="marginTop5 fontSize13">חברה</h5>			    						
							<div class="row">
								<div class="col-12">
									<?php foreach ($suppliers as $item) { 
											$query = $mysqli->prepare("SELECT id FROM dne_responsibles 
																	  WHERE id_projects_suppliers = ?");
											$query->bind_param("i",$item->id);
											$query->execute();
											$query->store_result();
											$responsibles_list = fetch($query);

											$responsibles_ids = '';

											foreach($responsibles_list as $resp) {
												  $responsibles_ids.= $resp->id.',';
											}

											$responsibles_ids = substr($responsibles_ids, 0, -1);
											  
											if($responsibles_ids != '') {
									?>
										<input type="radio" name="supplier_filter" value="<?=@$item->id.'-'.@$responsibles_ids?>" <?php if($item->id == @$supplier_filter) echo 'checked';?> onclick="setFilter(<?=@$id_report?>,this.value,'','supplier_filter');" />&nbsp;<?=@$item->name_he?><br/>
									<?php }} ?>
								</div>
							</div>														
							
							<h5 class="marginTop5 fontSize13">משרד</h5>		
							<div class="row">
								<div class="col-12">
									<?php foreach ($designers as $item) { 
											$query = $mysqli->prepare("SELECT id FROM dne_responsibles 
																	   WHERE id_projects_suppliers = ?");
											$query->bind_param("i",$item->id);
											$query->execute();
											$query->store_result();
											$responsibles_list = fetch($query);

											$responsibles_ids = '';

											foreach($responsibles_list as $resp) {
												$responsibles_ids.= $resp->id.',';
											}

											$responsibles_ids = substr($responsibles_ids, 0, -1);
										  
											if($responsibles_ids != '') {
									?>
										<input type="radio" name="supplier_filter" value="<?=@$item->id.'-'.@$responsibles_ids?>" <?php if($item->id == @$supplier_filter) echo 'checked';?> onclick="setFilter(<?=@$id_report?>,this.value,'','supplier_filter');" />&nbsp;<?=@$item->name_he?><br/>
									<?php }} ?>
									
								</div>
							</div>

                            <h5 class="marginTop5 fontSize13">יזם</h5>
                            <div class="row">
								<div class="col-12">
									<?php 
									$query = $mysqli->prepare("SELECT id FROM dne_responsibles WHERE id_projects_suppliers = ?");
									$query->bind_param("i",$entrepreneur->id);
									$query->execute();
									$query->store_result();
									$responsibles_list = fetch($query);

									$responsibles_ids = '';

									foreach($responsibles_list as $resp) {
									  $responsibles_ids.= $resp->id.',';
									}
  
									$responsibles_ids = substr($responsibles_ids, 0, -1);
									
									if($responsibles_ids != '') { 
									?>
								       <input type="radio" name="supplier_filter" value="<?=@$entrepreneur->id.'-'.@$responsibles_ids?>" <?php if($item->id == @$supplier_filter) echo 'checked';?> onclick="setFilter(<?=@$id_report?>,this.value,'','supplier_filter');" />&nbsp;<?=@$entrepreneur->name_he?><br/>
									<?php } ?>					
								</div>
							</div>					
					    </div>
					</div>
				</div>
            </div>
		</div>
		
		<div class="modal fade dir-rtl" id="modalFilterByPeriodNewTasks" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
				<div class="modal-content">
                    <div class="modal-header">
						<button type="button" class="btn-close btn-close-white-small" data-bs-dismiss="modal" aria-label="Close"></button>
						<div class="modal-title"></div>
					</div>				
					<div class="modal-body marginTop5">	
					    <div id="modalContent">
							<?php
								$periods = [
								  'today' => 'היום',
								  'three_days' => '3 ימים',
								  'one_week' => 'שבוע',
								  'two_weeks' => 'שבועים',
								  'one_month' => 'חודש',
								  'two_months' => 'חדשיים',
								  'one_year' => 'שנה',
								  'two_years' => 'שנתיים'
								];

								foreach ($periods as $value => $label){
									$checked = '';
									if (@$_GET['period_new_task_filter'] == $value) 
										$checked = 'checked';
									
									$radio_id = "period_$value";
									?>
									<label for="<?=$radio_id?>" class="display-block">
										<input type="radio" name="period_new_task_filter" id="<?=@$radio_id?>" value="<?=@$value?>" <?=@$checked?> onclick="setFilter(<?=@$id_report?>,this.value,'','period_new_task_filter');" />
										<?=@$label?>
									</label>
						    <?php } ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<div class="modal fade dir-rtl" id="modalFilterByPeriodLate" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
				<div class="modal-content">
                    <div class="modal-header">
						<button type="button" class="btn-close btn-close-white-small" data-bs-dismiss="modal" aria-label="Close"></button>
						<div class="modal-title"></div>
					</div>				
					<div class="modal-body marginTop5">	
					    <div id="modalContent">
							<?php
							$period_late_options = [	
								'three_days' => '3 ימים',
								'one_week' => 'שבוע',
								'two_weeks' => 'שבועים',
							    'one_month' => 'חודש',
								'two_months' => 'חדשיים'
							];	
							
							foreach ($period_late_options as $value => $label){ 
								$checked = (@$_GET['period_late_filter'] == $value) ? 'checked' : '';
								$radio_id = "period_$value";
								?>
								<label for="<?=$radio_id?>" class="display-block">
									<input type="radio" name="period_late_filter" id="<?=@$radio_id?>" value="<?=@$value?>" <?=@$checked?> onclick="setFilter(<?=@$id_report?>,this.value,'','period_late_filter');" />
									<?=@$label?>
								</label>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<div class="modal fade dir-rtl" id="modalFilterByProgressStatus" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
				<div class="modal-content">
                    <div class="modal-header">
						<button type="button" class="btn-close btn-close-white-small" data-bs-dismiss="modal" aria-label="Close"></button>
						<div class="modal-title"></div>
					</div>				
					<div class="modal-body">	
					    <div id="modalContent">
							<div class="row marginTop5">
								<div class="col-12">
									<?php 
									foreach($progress_status_s as $item){									
										$checked = ($item->id == @$progress_status_filter && strpos(@$_SESSION['filter_progress_status_list'], ',') === false) ? 'checked' : '';?>
										<label class="display-block">
											<input type="radio" name="progress_status_filter" value="<?=@$item->id?>" <?=@$checked?> onclick="setFilter(<?=@$id_report?>,this.value,'','progress_status_filter');" />
											<?=(trim(@$item->name_he) !== '' ? @$item->name_he : '(ללא)')?>
										</label>
										<?php
									}
									?>			
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<div class="modal fade dir-rtl" id="modalFilterByTask" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
				<div class="modal-content">
                    <div class="modal-header">
						<button type="button" class="btn-close btn-close-white-small" data-bs-dismiss="modal" aria-label="Close"></button>
						<div class="modal-title"></div>
					</div>				
					<div class="modal-body">	
					    <div id="modalContent">
							<div class="row marginTop5">
								<div class="col-12">
									<?php foreach ($tasks as $item){
										    $checked = ($item->id == @$task_filter && strpos(@$_SESSION['filter_tasks_list'], ',') === false) ? 'checked' : '';
									?>
											<label class="display-block">
												<input type="radio" name="task_filter" value="<?=@$item->id?>" <?=@$checked?> onclick="setFilter(<?=@$id_report?>,this.value,'','task_filter');" />
												<?=@$item->name_he?>
											</label>
									<?php } ?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

        <div class="modal fade dir-rtl " id="modalTableView" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="btn-close btn-close-white-small" data-bs-dismiss="modal" aria-label="Close"></button>
						<div class="modal-title"></div>
					</div>
					<div class="modal-body">	
					    <div id="modalContent">	        
							<form>
                                <div class="row marginTop5 <?=@$dir?>">
									<div class="col-12">															
							           <strong>בחירת שדות</strong>
								    </div>
								</div>
								<div class="row marginTop5 alignCenter">
								    <div class="col-12 alignRight">  
									    <input id="columns_list" name="columns_list[]" type="checkbox" value="subject" <?php if(in_array('subject',$columns_list_array)) echo 'checked';?> />&nbsp; נושא/תחום
										<br/>
										<input id="columns_list" name="columns_list[]" type="checkbox" value="area" <?php if(in_array('area',$columns_list_array)) echo 'checked';?> />&nbsp; איזור/נושא
										<br/>
										<input id="columns_list" name="columns_list[]" type="checkbox" value="description" <?php if(in_array('description',$columns_list_array)) echo 'checked';?> />&nbsp; תיאור
										<br/>
										<input id="columns_list" name="columns_list[]" type="checkbox" value="_task" <?php if(in_array('_task',$columns_list_array)) echo 'checked';?> />&nbsp; סוגי משימה
										<br/>
										<input id="columns_list" name="columns_list[]" type="checkbox" value="responsible" <?php if(in_array('responsible',$columns_list_array)) echo 'checked';?> />&nbsp; אחראיים
										<br/>
										<input id="columns_list" name="columns_list[]" type="checkbox" value="pass on" <?php if(in_array('pass on',$columns_list_array)) echo 'checked';?> />&nbsp; להעביר ל/לאשר מול
										<br/>
										<input id="columns_list" name="columns_list[]" type="checkbox" value="task creation" <?php if(in_array('task creation',$columns_list_array)) echo 'checked';?> />&nbsp; תאריך יצירת משימה
										<br/>
										<input id="columns_list" name="columns_list[]" type="checkbox" value="destination date" <?php if(in_array('destination date',$columns_list_array)) echo 'checked';?> />&nbsp; תאריך יעד
										<br/>
										<input id="columns_list" type="checkbox" value="progress status" <?php if(in_array('progress status',$columns_list_array)) echo 'checked';?> />&nbsp; סטטוס התקדמות
									</div>
								</div>
  								<div class="marginTop20 alignCenter">
							        <input type="button" class="btn btn-primary text-white font-weight-bold marginLeft10" value="שמור" style="padding:.375rem .75rem!important;" onclick="setReportData('table_view')" />
							        <input type="button" class="btn bg-black text-white font-weight-bold" value="ביטול" style="padding:.375rem .75rem!important;" onclick="$('#modalTableView').modal('hide');window.location.reload();" />
							    </div>
						   </form>
					    </div>
					</div>
				</div>
           </div>
		</div>		
		
		<div class="modal fade dir-rtl" id="modalTaskDescription" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
				<div class="modal-content">
				    <div class="modal-header">
					    <button type="button" class="btn-close btn-close-white-small" data-bs-dismiss="modal" aria-label="Close"></button>
						<div class="modal-title"></div>
					</div>
					<div class="modal-body">	
					    <div id="modalContent">   
					       <form>
						       <div class='marginTop5 subtitle color-349feb fontSize18 font-weight-bold alignCenter'></div>
							   <div class="row marginTop10">
							       <div class="col-1"></div>
							       <div class="col-10">
								       <div class="height-auto bgColorWhite">
							               <div id="new_description" contenteditable="true" class="paddingRight10 border-black overflow-y-scroll"></div>
							           </div>
								   </div>
								   <div class="col-1"></div>
							   </div>
						     
							   <div class="marginTop15 alignCenter">
							      <input type="button" class="btn btn-primary text-white font-weight-bold marginLeft10" value="שמור" style="padding:.375rem .75rem!important;" onclick="setData($('#hidden_meeting_id').val(),$('#hidden_iteration').val(),'description',0,0,'for_closing')" />
							      <input type="button" class="btn bg-black text-white font-weight-bold" value="ביטול" style="padding:.375rem .75rem!important;" onclick="hidePopup('modalTaskDescription',$('#hidden_iteration').val(),'fromMeetings')" />
							   </div>
						   </form>
					  </div>
					</div>
				</div>
            </div>
		</div>
		
		<div class="modal fade dir-rtl" id="modalTaskCreationDate" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
				<div class="modal-content"> 
					<div class="modal-body">	
					    <div id="modalContent">
					        <form class="marginTop15 alignCenter">
							   <input type="date" name="popup_task_creation_date" id="popup_task_creation_date" class="fontSize13 font-weight-bold alignCenter" onchange="setData($('#hidden_meeting_id').val(),$('#hidden_iteration').val(),'task_creation_date',0,0,'screen');" />
						    </form>
					    </div>
					</div>
				</div>
            </div>
		</div>
		
		<div class="modal fade dir-rtl" id="modalDestinationDate" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
				<div class="modal-content"> 
					<div class="modal-body">	
					    <div id="modalContent">
					        <form class="marginTop15 alignCenter">
							   <input type="date" name="popup_destination_date_input" id="popup_destination_date_input" class="fontSize13 font-weight-bold alignCenter" data-meetingid="<?=@$meeting_id?>" data-iteration=<?=@$iteration?> />
						    </form>
					    </div>
					</div>
				</div>
            </div>
		</div>
		
		<div class="modal fade dir-rtl" id="modalTaskFollowupDelayTargetDate" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
				<div class="modal-content">
				    <div class="modal-header">
					    <button type="button" class="btn-close btn-close-white-small" data-bs-dismiss="modal" aria-label="Close"></button>
						<div class="modal-title"></div>
					</div>
					<div class="modal-body">	
					    <div id="modalContent">	        	        
					        <form>
							    <div class='marginTop5 subtitle color-349feb fontSize18 font-weight-bold alignCenter'></div>
                                <div id="task_active_remarks_target_date"></div>							
								<div class="marginTop10 <?=@$padding_10?> <?=@$align?> height-auto bgColorWhite fontSize13 cursor-pointer border-black overflow-y-scroll dir-rtl">
									<div name="remark_delay_target_date" id="remark_delay_target_date" contenteditable="true" class="editable green cursor-pointer" data-placeholder="ניתן להוסיף כאן הערה"></div>
								</div>
							    <div class="marginTop15 alignCenter">
							       	<input type="button" class="btn btn-primary text-white font-weight-bold marginLeft10" value="שמור" style="padding:.375rem .75rem!important;" onclick="setData($('#hidden_meeting_id').val(),$('#hidden_iteration').val(),'destination_date',1,0,'for_closing')" />
									<input type="button" class="btn bg-dark text-white font-weight-bold" value="בטל" style="padding:.375rem .75rem!important;" onclick="hidePopup('modalTaskFollowupDelayTargetDate',$('#hidden_iteration').val(),$('#hidden_meeting_id').val(),'fromMeetings')" />
							    </div>
						    </form>
					    </div>
					</div>
				</div>
            </div>
		</div>
		
		<div class="modal fade dir-rtl" id="modalTaskFollowupChangeStatus" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
				<div class="modal-content">
				    <div class="modal-header">
					    <button type="button" class="btn-close btn-close-white-small" data-bs-dismiss="modal" aria-label="Close"></button>
						<div class="modal-title"></div>
					</div>
					<div class="modal-body">	
					    <div id="modalContent">			                             					   		
							<form>
							    <div class='marginTop5 subtitle color-349feb fontSize18 font-weight-bold alignCenter'></div>
                                <div class="margin-top-10-x-auto padding-4x-4y colorWhite bgColorBlack borderRadius10 width100 border-black fontSize13 font-weight-bold alignCenter" id="div_new_progress_status"></div>							
                                <div class="marginTop10"></div>
								<hr class="colorGrey" style="margin:1px 0" />
								<div id="task_active_remarks_progress_status"></div>							
								<div class="marginTop10 <?=@$padding_10?> <?=@$align?> height-auto bgColorWhite fontSize13 cursor-pointer border-black overflow-y-scroll dir-rtl">
									<div name="remark_changes_status" id="remark_changes_status" contenteditable="true" class="editable green cursor-pointer" data-placeholder="ניתן להוסיף כאן הערה"></div>
								</div>	
								<div class="marginTop10"></div>
								<hr class="colorGrey" style="margin:1px 0" />
								<div class="marginTop5 color-349feb fontSize13 font-weight-bold alignCenter">
									יעד
									<div class="marginTop5">
										<input type="date" id="new_destination_date" class="alignCenter" />	
                                    </div>									
								</div>    
							    <div class="marginTop15 marginBottom10 alignCenter">
							       <input type="button" class="btn btn-primary text-white font-weight-bold marginLeft10" value="שמור" style="padding:.375rem .75rem!important;" onclick="setData($('#hidden_meeting_id').val(),$('#hidden_iteration').val(),'id_progress_status',1,0,'for_closing')" />
							       <input type="button" class="btn bg-dark text-white font-weight-bold" value="סגור" style="padding:.375rem .75rem!important;" onclick="hidePopup('modalTaskFollowupChangeStatus',$('#hidden_iteration').val(),$('#hidden_meeting_id').val(),'fromMeetings')" />
							   </div>
						    </form>
					    </div>
					</div>
				</div>
            </div>
		</div>
		
		<div class="modal fade dir-rtl" id="modalTaskFollowupActions" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-body">	
					   <div id="modalContent">
					       <div id="div_content_task_details"></div>
					       <form class="marginTop15 alignCenter">    
							   <div class="row">
							        <div class="flex justify-content-center">
									    <div class="width20Percents">
										    <a class="btn colorBlack" style="box-shadow:none;" onclick="toEditeMeeting($('#hidden_meeting_id').val(),$('#hidden_iteration').val(),'fromMeetings')">
												<img src="images/edit-button.svg" width="30" height="30" />
												<br/>
												<strong class="fontSize14">עריכה</strong>
									        </a>
										</div>
										<div class="width20Percents">
										     <a class="btn colorBlack" style="box-shadow:none;" onclick="duplicateRecord($('#hidden_meeting_id').val(),$('#hidden_iteration').val(),'fromMeetings')">
												<img src="images/clone-solid.svg" width="30" height="30" />
												<br/>
												<strong class="fontSize14">שכפול</strong>
									         </a>	 
										</div>
										<div class="width20Percents">
										    <a id="continuous_btn" class="btn text-dark bg-white" style="box-shadow:none;">
												<img src="images/arrow-right-from-bracket-solid.svg" width="30" height="30" />
												<br/>
												<strong class="fontSize14">המשך</strong>
									        </a>
										</div>
										<div class="width20Percents">
										    <a id="history_btn" class="btn text-dark bg-white width130" style="box-shadow:none;">
												<img src="images/rectangle-list-regular.svg" width="30" height="30" />
												<br/>
												<strong class="fontSize14">הסטורי-ה</strong>
									        </a>
										</div>
										<div class="width20Percents">
										    <a id="delete_btn" class="btn text-black width130 height65" style="box-shadow:none;" onclick="return removeMeeting($('#hidden_meeting_id').val(),$('#hidden_iteration').val());">
											   <img src="images/delete.svg" alt="image delete" width="30" height="30" />
											   <br/>
											   <strong class="fontSize14">מחק</strong>
									        </a>
										</div>	     
									</div>
								</div>
								
								<div class="row">
									<div class="flex justify-content-center">
										<div class="width20Percents">
										    <a id="update_btn" class="btn text-black width130 height65" style="box-shadow:none;">
											   <img src="images/status-icon.png" alt="status icon" width="30" height="30" />
											   <br/>
											   <strong class="fontSize14">עדכון</strong>
									        </a>
										</div>			
										<div class="width20Percents">	    
										    <a id="tracking_btn" class="btn colorBlack width130" style="box-shadow:none;">
												<i id="target-icon-popup" class="fas fa-bullseye" style="font-size:26px;color:#888;"></i>
												<br/>
												<strong class="fontSize14">מעקב</strong>
									        </a>
										</div>
										<div class="width20Percents">
										    <a id="send_email_btn" class="btn colorBlack width130" style="box-shadow:none;">
												<img src="images/email-icon.png" width="30" height="30" />
												<br/>
												<strong class="fontSize14">דוא''ל</strong>
									        </a>
										</div>
										<div class="width20Percents">
										    <a id="send_whatsapp_btn" class="btn width130" style="box-shadow:none;">
												<img src="images/whatsapp-icon.png" width="30" height="30" />
												<br/>
												<strong class="fontSize14">WhatsApp</strong>
									        </a>
										</div>									
									</div>    	
							    </div>
								<div class="row marginTop5 dir-rtl">
								    <div class="col-12 alignCenter">
									    <a id="link_next_task"><i class="fa-solid fa-forward marginLeft10 fontSize33 color-349feb cursor-pointer"></i></a>		   
									    <a id="link_prev_task"><i class="fa-solid fa-backward fontSize33 color-349feb cursor-pointer"></i></a>
								    </div>
								</div>
						   </form>
					   </div>
					</div>
				</div>
           </div>
		</div>
		
		<div class="modal fade <?=(!empty(@$project->lang) ? @$project->lang : 'HE')=='HE' ? 'dir-rtl' : ''?>" id="modalContinuousTask" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
				<div class="modal-content">  
					<div class="modal-body">	
					    <div id="modalContent">
					        <form class="alignCenter">
						        <div class="marginTop15 fontSize18 alignCenter"><?=(!empty(@$project->lang)?@$project->lang:'HE')=='HE' ? 'בדרך ליצור עבורך משימת המשך לפני כן, תציין אם ברצונך לסמן סטטוס משימה כ:' : 'A continuation task will be created. Please select the status to apply to the current task:'?></div>
							    <div class="marginTop15 d-flex justify-content-center" style="gap:20px;">
									<label style="cursor:pointer">
										<input type="radio" id="done" name="progress_status" value="1" onclick="continuousTask($('#hidden_meeting_id').val(),'בוצע/נמסר',$('#hidden_iteration').val(),'fromMeetings')" />&nbsp;<?=(!empty(@$project->lang)?@$project->lang:'HE')=='HE' ? 'בוצע/נמסר' : 'Done/Delivered'?>
									</label>
									<label style="cursor:pointer">
										<input type="radio" id="archive" name="progress_status" value="2" onclick="continuousTask($('#hidden_meeting_id').val(),'ארכיון',$('#hidden_iteration').val(),'fromMeetings')" />&nbsp;<?=(!empty(@$project->lang)?@$project->lang:'HE')=='HE' ? 'ארכיון' : 'Archive'?>
									</label>
									<label style="cursor:pointer">
										<input type="radio" id="no_change" name="progress_status" value="3" onclick="continuousTask($('#hidden_meeting_id').val(),'no_change',$('#hidden_iteration').val(),'fromMeetings')" />&nbsp;<?=(!empty(@$project->lang)?@$project->lang:'HE')=='HE' ? 'ללא שינוי' : 'No change'?>
									</label>
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
					<div class="modal-body">	
					    <div id="modalHistoryTasksContent"></div>
					</div>
				</div>
            </div>
		</div>
		
		<?php $_pl = !empty(@$project->lang) ? @$project->lang : 'HE'; $_pd = ($_pl=='HE') ? 'rtl' : 'ltr'; ?>
		<div class="modal fade <?=($_pl=='HE') ? 'dir-rtl' : ''?>" id="modalUpdateTask" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
				<div class="modal-content">  
					<div class="modal-header">
						<div class="modal-title"></div>
						<button type="button" class="btn-close btn-close-white-small" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body">
                        <form action="" method="post">
                            <div class='marginTop5 subtitle color-349feb fontSize18 font-weight-bold alignCenter'></div>
							<div class="row marginTop10" style="direction:<?=$_pd?>">
								<div class="col-12 fontSize13 alignCenter">
									<strong><?=($_pl=='HE') ? 'סטטוס חדש' : 'New Status'?>:</strong>
									<select id="progress_status_update" class="<?=($_pl=='HE') ? 'marginRight5' : 'marginLeft5'?>" style="direction:<?=$_pd?>">
										<option value="0">--<?=($_pl=='HE') ? 'סטטוס' : 'Status'?>--</option>
										<?php
											foreach($progress_status_s as $item){
												if($_pl == "HE"){
													if(@$item->name_he == ' ')
														$progress_status_name_for_update = '(ללא)';
													else 
														$progress_status_name_for_update = @$item->name_he;
												}
												else {
													if(@$item->name == ' ')
														$progress_status_name_for_update = '(Without)';
													else 
														$progress_status_name_for_update = @$item->name;
												}
											?>
												<option value="<?=@$item->id?>">
													<strong>
														<?=@$progress_status_name_for_update?>
													</strong>
												</option>
												<?php
											}
										?>				
									</select> 
								</div>
							</div>
                            <hr class="colorGrey mb-1 mt-1"/>							
							<div id="task_active_remarks_progress_status_update" style="direction:<?=$_pd?>"></div>
							<div class="marginTop10 <?=($_pl=='HE') ? 'paddingRight10' : 'paddingLeft10'?> <?=($_pl=='HE') ? 'alignRight' : 'alignLeft'?> height-auto bgColorWhite fontSize13 cursor-pointer border-black overflow-y-scroll" style="direction:<?=$_pd?>">
								<div name="remark_changes_status_update" id="remark_changes_status_update" contenteditable="true" class="editable green cursor-pointer" data-placeholder="<?=($_pl=='HE') ? 'ניתן להוסיף כאן הערה' : 'You can add a comment here'?>"></div>
							</div>
                            <div class="marginTop5 fontSize13 font-weight-bold alignCenter" style="direction:<?=$_pd?>"><?=($_pl=='HE') ? 'תאריך יעד' : 'Target Date'?></div>
							<div class="marginTop5 fontSize13 alignCenter" style="direction:<?=$_pd?>">
								<input type="date" id="new_destination_date_update" class="alignCenter" />
							</div>
							<div class="marginTop15 marginBottom10 alignCenter" style="direction:<?=$_pd?>">
							   	<input type="button" id="save_update_task_btn" class="btn btn-primary text-white font-weight-bold <?=($_pl=='HE') ? 'marginLeft10' : 'marginRight10'?>" value="<?=($_pl=='HE') ? 'שמור' : 'Save'?>" style="padding:.375rem .75rem!important;" />
							    <input type="button" class="btn bg-dark text-white font-weight-bold" value="<?=($_pl=='HE') ? 'בטל' : 'Cancel'?>" style="padding:.375rem .75rem!important;" onclick="hidePopup('modalUpdateTask',$('#hidden_iteration').val(),$('#hidden_meeting_id').val(),'fromMeetings')" />
							</div>
						</form>
					</div>
				</div>
            </div>
		</div>
		
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
								    <div class='marginTop5 subtitle color-349feb fontSize18 font-weight-bold alignCenter'></div>
									<div class="row marginTop5 alignCenter">
									    <div class="col-12">
											<strong class="fontSize13">אחראי מעקב</strong>
										</div>
									</div>	
									
									<div class="row marginTop5 alignCenter">
										<div class="col-12">
											<select id="users" class="paddingRight8 fontSize13">	
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
									
									<div class="row" dir="rtl">
										<div class="col-12 d-flex justify-content-center">
											<div class="row mt-2 width85Percents">
												<div id="div_reminder_date" class="col-4 p-2 text-end">
													<div class="fw-bold d-flex align-items-center justify-content-end gap-1">
														<span class="fontSize13">תזכורת מעקב</span>
														<img src="images/bell-solid.svg" width="15" height="15" />
													</div>
													<div class="marginTop5 fontSize12 text-end">
														<input type="date" class="text-center" id="reminder_date" />
													</div>
												</div>

												<div id="div_radios" class="col-8 p-2 text-end">
													<div class="row g-0 fontSize13">
														<div class="col-6">
															<div>
																<input type="radio" id="reminder_tomorrow" name="set_reminder_date_radio" value="1"
																	   onclick="setReminderDate(this.value)"> מחר
															</div>
															<div>
																<input type="radio" id="reminder_after_three_days" name="set_reminder_date_radio" value="2"
																	   onclick="setReminderDate(this.value)"> בעוד 3 ימים
															</div>
															<div>
																<input type="radio" id="reminder_after_one_week" name="set_reminder_date_radio" value="3"
																	   onclick="setReminderDate(this.value)"> בעוד שבוע
															</div>
														</div>

														<div class="col-6">
															<div>
																<input type="radio" id="reminder_after_two_weeks" name="set_reminder_date_radio" value="4"
																	   onclick="setReminderDate(this.value)"> בעוד שבועיים
															</div>
															<div>
																<input type="radio" id="reminder_after_one_month" name="set_reminder_date_radio" value="5"
																	   onclick="setReminderDate(this.value)"> בעוד חודש
															</div>
															<div>
																<input type="radio" id="reminder_after_selected_date" name="set_reminder_date_radio" value="6"
																	   onclick="setReminderDate(this.value)"> בתאריך...
															</div>
															<div>
																<input type="radio" id="not_reminders" name="set_reminder_date_radio" value="0"
																	   onclick="setReminderDate(this.value)"> אין צורך
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									
									<div id="task_active_remarks_tracking"></div>                                
					    
								    <div class="row marginTop5">	
										<div class="col-12 fontSize13">
											<div class="row">
												<div class="col-6 alignRight">				
													<a id="new_remark_tracking_en" class="text-decoration-none cursor-pointer" onclick="$('#new_remark').css({'direction':'ltr','padding-left':'5px','padding-right':'0','text-align':'left'});$(this).css('font-weight','bold');$('#new_remark_tracking_he').css('font-weight','normal');$('#div_new_remark').css('direction','ltr');">EN</a>&nbsp;|
													<a id="new_remark_tracking_he" class="text-decoration-none cursor-pointer" onclick="$('#new_remark').css({'direction':'rtl','padding-right':'5px','padding-left':'0','text-align':'right'});$(this).css('font-weight','bold');$('#new_remark_tracking_en').css('font-weight','normal');$('#div_new_remark').css('direction','rtl');">ע</a>
												</div>

												<div class="col-6 alignLeft">
													<a class="text-decoration-none" onclick="$('#new_remark').html('')">Clear</a>
												</div>
											</div>

											<div id="div_new_remark" class="marginTop5 paddingRight10 alignRight height-auto bgColorWhite cursor-pointer margin-0-x-auto border-black overflow-y-scroll dir-rtl">		
												<div name="new_remark" id="new_remark" contenteditable="true" class="editable red cursor-pointer" data-placeholder="ניתן להוסיף כאן הערה"></div>
											</div>
	                                    </div>										
				                    </div>                         
		   
									<div class="marginTop15 alignCenter">
									  <input type="button" class="btn btn-primary font-weight-bold marginLeft10" value="שמור מעקב" style="padding:.375rem .75rem!important;" onclick="fillLogTaskTracking($('#hidden_meeting_id').val(),$('#hidden_iteration').val(),'for_closing')" />
									  <input type="button" class="btn bg-dark text-white font-weight-bold" value="בטל מעקב" style="padding:.375rem .75rem!important;" onclick="hidePopup('modalTaskTracking',$('#hidden_iteration').val(),$('#hidden_meeting_id').val(),'fromMeetings')" />
									</div>
							    </form>
                           </div>
					  </div>
					</div>
				</div>
           </div>
		</div>
		
		<div class="modal fade dir-rtl" id="modalSendEmail" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
				<div class="modal-content">
				    <div class="modal-header">
					   <h5 class="modal-title fontSize26"></h5>
					</div>
					<div class="modal-body">	
					    <div id="modalContent">
					        <form class="marginTop15 alignCenter">
						        <div class="alignCenter fontSize20">לשלוח ל</div>
								<div>
								    <input id="email_recipient" class="marginTop10 paddingRight10 width230" type="email" placeholder="לשלוח ל" />
								</div>
								<div class="marginTop15">
								    <input type="button" id="set_responsible_email_btn" class="btn btn-primary text-white font-weight-bold" value="שמור את דוא''ל האחראי" onclick="setResponsibleEmail($('#hidden_responsible_id').val(),$('#hidden_iteration').val())" />
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
							       <input type="button" class="btn btn-primary text-white font-weight-bold marginLeft10" value="שלח" style="padding:.375rem .75rem!important;" onclick="sendEmail($('#hidden_meeting_id').val(),$('#hidden_iteration').val(),'for_closing','fromMeetings')" />
							       <input type="button" class="btn bg-dark text-white font-weight-bold" value="ביטול" style="padding:.375rem .75rem!important;" onclick="hidePopup('modalSendEmail',$('#hidden_iteration').val(),'fromMeetings')" />
							    </div>
						    </form>
					    </div>
					</div>
				</div>
            </div>
		</div>
	</body>
</html>

<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
let project_id;
let meeting_id;
let iteration;
let lang;
let user_id;
let chapter;
let subject;
let area;
let description;
let destination_date;
let recipient;
let responsible_id;
let progress_status_id;
let task_creation_date;
let is_priority;
let remark;
let track_responsible_id;
let track_type;
let reminder_date;
let reminder_time;

let click_set_track_count = 1;

const urlParams = new URLSearchParams(window.location.search);
const projectId = urlParams.get('project_id') || 'default';
const _rt = document.getElementById('report_type');
const reportId = (_rt && _rt.value) ? _rt.value : projectId;

const isSortedKey = 'is_data_sorted_' + reportId;
const sort1Key = 'sort_select_1_' + reportId;
const sort2Key = 'sort_select_2_' + reportId;
const sort3Key = 'sort_select_3_' + reportId;

const isSorted = localStorage.getItem(isSortedKey) === 'true';
if(isSorted){
	$('#div_sort').show();

	$('#sort_select_1').val(localStorage.getItem(sort1Key) || 'subject');
	$('#sort_select_2').val(localStorage.getItem(sort2Key) || 'area');
	$('#sort_select_3').val(localStorage.getItem(sort3Key) || 'task');
} 
else {
    $('#div_sort').hide();
}
	
$(document).ready(function(){
	localStorage.setItem('dne_last_url', window.location.href);
	let container = $('.container');

	if($('#row').val() != ''){
		const rowVal = $('#row').val();
		const element = rowVal.startsWith('meeting_')
			? document.querySelector('tr.' + rowVal)
			: document.getElementById(rowVal);

		if(element){
			const elementPosition = element.getBoundingClientRect().top + window.pageYOffset;
			const targetY = elementPosition - 100;

			window.scrollTo({
				top: targetY,
				behavior: 'instant'
			});

			element.classList.add('row-darken');
			localStorage.setItem('scroll_Y_<?=$project_id?>', Math.round(targetY));
		}
	}
	else {   
		window.scrollTo({
		   top: 0,
		   behavior: 'instant'
		});
	}
	
	if($('#id_rdv_report').val() > 0){
		$('#resume_rdv_label').html($('#rdv_name').val());
		$('#apply_tasks_to_rdv_btn').css('display','none');
	}
	
	$('[id="filter_by_supplier_btn"]').on('click', function(){
	   let id_report = $(this).data('reportid');
	   $('.modal-title').html("סינון לפי ספק");
	   $('#modalFilterBySupplier').modal('show');
	})
	
	$('[id="filter_by_period_new_task_btn"]').on('click', function(){
	   let id_report = $(this).data('reportid');
	   $('.modal-title').html("סינון לפי חדש");
	   $('#modalFilterByPeriodNewTasks').modal('show');
	})
	
	$('[id="filter_by_period_late_btn"]').on('click', function(){
	   let id_report = $(this).data('reportid');
	   $('.modal-title').html("סינון לפי איחור");
	   $('#modalFilterByPeriodLate').modal('show');
	})
	
	$('[id="filter_by_progress_status_btn"]').on('click', function() {
	   let id_report = $(this).data('reportid');
	   $('.modal-title').html("סינון לפי סטטוס");
	   $('#modalFilterByProgressStatus').modal('show');
	})
	
	$('[id="filter_by_task_btn"]').on('click', function() {
	   let id_report = $(this).data('reportid');
	   $('.modal-title').html("סינון לפי משימה");
	   $('#modalFilterByTask').modal('show');
	})
	
	$('input[type="checkbox"][id^="meetings_to_update_cbx"]').each(function() {
		let meeting_id = $(this).attr('id').slice($(this).attr('id').indexOf('cbx')+4);
        let elem_track_type = '#track_type_'+meeting_id;
		let elem_is_priority = '#is_priority_'+meeting_id;
		
		if($('#is_colors').val() && $(elem_is_priority).val() == 1)
		  $('#td_cbx_meetings_to_update_'+meeting_id).css({'background-color':'red'});
    });
	
	$('input[type="checkbox"][id^="meetings_to_update_cbx"]').on('click', function () {
		const atLeastOneChecked = $('input[type="checkbox"][id^="meetings_to_update_cbx"]:checked').length > 0;
        let checked_row = $(this).attr('id').replace('meetings_to_update_cbx_','');
		
		['td_cbx_meetings_to_update_',
		'td_count_',
		'td_subject_',
		'td_area_',
		'td_description_',
		'td_task_',
		'td_responsible_',
		'td_pass_on_',
		'td_task_creation_date_',
		'td_destination_date_',
		'td_progress_status_'].forEach(function(prefix){
			$('#'+prefix+checked_row).toggleClass('in-strong-relief');
		});
       
		if(atLeastOneChecked){				
			$('#apply_tasks_to_rdv_btn')
				.removeClass('disabled-link')
				.prop('disabled',false)
				.css('background-color','red')
				.css('color','#474d5d');
			
			$('#send_tasks_btn')
			    .removeClass('disabled-link')
				.prop('disabled',false);
			
			$('#img_plus_rdv').attr('src','images/actif-plus-icon.png');
			$('#task_label,#pdf_label,#share_label').css('color','red');
		} 
		else {
			$('#apply_tasks_to_rdv_btn')
				.addClass('disabled-link')
				.prop('disabled',true)
				.css('background-color','#f5f4fe')
				.css('color','#474d5d');
			
			$('#send_tasks_btn')
			    .addClass('disabled-link')
				.prop('disabled',true)
			
			$('#img_plus_rdv').attr('src','images/plus-icon.png');
			$('#task_label,#pdf_label,#share_label').css('color','#1A5276');
		}
    });
	
	$('#modalTaskFollowupActions').on('shown.bs.modal', function(){
		_taskModalFullyShown = true;
	});

	$('#modalTaskFollowupActions').on('hidden.bs.modal', function (){
		_taskModalFullyShown = false;
		localStorage.removeItem('is_modal_task_actions_opened');
		if(!_suppressScrollOnHide){
			let $row = $('#meetings_table tr.meeting_' + meeting_id);
			if ($row.length) $row[0].scrollIntoView({block: 'center'});
		}
		_suppressScrollOnHide = false;
    });

	if(localStorage.getItem("is_modal_task_actions_opened") === "true" &&
	   localStorage.getItem("project_id") == "<?=$project_id?>"){
	   project_id = localStorage.getItem("project_id");
	   meeting_id = localStorage.getItem("meeting_id");
	   if(!/^\d+$/.test(meeting_id)){ localStorage.removeItem('is_modal_task_actions_opened'); }
	   else {
	   iteration = localStorage.getItem("iteration");
	   chapter = localStorage.getItem("chapter");
	   subject = localStorage.getItem("subject");
	   area = localStorage.getItem("area");
	   recipient = localStorage.getItem("recipient");
	   responsible_id = localStorage.getItem("responsible_id");
	   remark = localStorage.getItem("remark");
	   track_type = parseInt($('#track_type_' + meeting_id).val()) || 0;
	   is_priority = 1;

	   if(localStorage.getItem("is_priority") == 1 || localStorage.getItem("is_five_priorities"))
	      is_priority = 0;

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
				if(data.indexOf('|~|') === -1){
					localStorage.removeItem('is_modal_task_actions_opened');
					localStorage.removeItem('meeting_id');
					return;
				}
				let task_details = data.split('|~|');
				let content = fillContentTaskDetails(meeting_id,'',task_details,true);
				$('#div_content_task_details').html(content);
				setBellBcgColor(parseInt(task_details[22]) || 0);
				setEmergencyTaskCSS(is_priority);
				$('#modalContent').append("<input type='hidden' id='hidden_meeting_id' value='"+meeting_id+"'><input type='hidden' id='hidden_iteration' value='"+iteration+"'><input type='hidden' id='hidden_project_id' value='"+project_id+"'><input type='hidden' id='hidden_user_id' value='"+user_id+"'><input type='hidden' id='hidden_chapter' value='"+chapter+"'><input type='hidden' id='hidden_name' value='"+subject+"'><input type='hidden' id='hidden_area' value='"+area+"'><input type='hidden' id='hidden_recipient' value='"+recipient+"'><input type='hidden' id='hidden_responsible_id' value='"+responsible_id+"'><input type='hidden' id='hidden_is_priority' value='"+is_priority+"'><input type='hidden' id='hidden_remark' value='"+remark+"'><input type='hidden' id='hidden_track_responsible_id' value='"+track_responsible_id+"'><input type='hidden' id='hidden_track_type' value='"+track_type+"'><input type='hidden' id='hidden_reminder_date' value='"+reminder_date+"'><input type='hidden' id='hidden_reminder_time' value='"+reminder_time+"'>");
				$('#modalTaskFollowupActions').modal('show');
				setTimeout(function(){
				    let $row = $('#meetings_table tr.meeting_' + meeting_id);
				    if ($row.length) $row[0].scrollIntoView({block: 'center'});
				}, 400);
			},
	   });
	   } // end else (valid meeting_id)
	}

	if(localStorage.getItem("is_modal_tasks_hystory_opened")){
		meeting_id = localStorage.getItem("meeting_id");
		iteration = localStorage.getItem("iteration");
		
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
	   
	   $('#modalContent').append("<input type='hidden' id='hidden_meeting_id' value='"+meeting_id+"'><input type='hidden' id='hidden_iteration' value='"+iteration+"'><input type='hidden' id='hidden_project_id' value='"+project_id+"'>");
	   $('#modalTaskFollowupActions').modal('hide');
	   $('#modalHistoryTasks').modal('show');
	};
	
	$('#modalHistoryTasks').on('hidden.bs.modal', function () {
		setlocalStorage($('#hidden_meeting_id').val(),$('#iteration').val());
		window.location.reload();
    });

	// Save scroll position as user scrolls (throttled, per project)
	let scrollSaveTimer;
	window.addEventListener('scroll', function(){
		clearTimeout(scrollSaveTimer);
		scrollSaveTimer = setTimeout(function(){
			if(!$('#modalTaskFollowupActions').hasClass('show'))
				localStorage.setItem('scroll_Y_<?=$project_id?>', Math.round(window.scrollY));
		}, 200);
	});

	// Restore scroll on load only if no popup was restored for this project
	if(!(localStorage.getItem("is_modal_task_actions_opened") === "true" &&
	     localStorage.getItem("project_id") == "<?=$project_id?>")){
		let savedY = localStorage.getItem('scroll_Y_<?=$project_id?>');
		if(savedY) setTimeout(function(){ window.scrollTo({top: parseInt(savedY)}); }, 250);
	}

	$("#select_lang").change(function(){
		let lang = $(this).val();
		let url = new URL(window.location.href);
		url.searchParams.set('lang', lang);
		url.searchParams.set('lang_origin', 'user');
		let meetingClass = null;

		let $tr = $('tr.row-darken');
		if ($tr.length) {
			meetingClass = $tr.attr('class').split(' ').find(c => c.startsWith('meeting_'));
		}

		if (!meetingClass) {
			$('tr[class*="meeting_"]').each(function() {
				const rect = this.getBoundingClientRect();
				if (rect.top >= 0 && rect.top < window.innerHeight) {
					meetingClass = $(this).attr('class').split(' ').find(c => c.startsWith('meeting_'));
					return false;
				}
			});
		}

		if (meetingClass) url.searchParams.set('row', meetingClass);
		window.location.href = url.toString();
	});
	
	$('[id="table_view_btn"]').on('click', function(){
	   let id_report = $(this).data('reportid');
	   $('.modal-title').html("Table View"); 
	   $('#modalTableView').modal('show');
	});
	
	$("#toggle_switch_images").on("change", function(){
	   setReportData('picture');	
	});
	
	$("#toggle_switch_colors").on("change", function(){
	   setReportData('colors');
	});

	$('#period_new_tasks_toolbar').on('change', function(){
	   setReportData('highlight');
	});
	
	const trackingKey = 'track_off_' + reportId;

	function applyTrackingState(isOn) {
		if(isOn) {
			$('#target-icon').attr('src','images/red-target-icon.png');
			$('[id^="div-tracking-remarks-"]').css('display','block');
			$('#toggle_switch_track').prop('checked', true);
			localStorage.removeItem(trackingKey);
		} else {
			$('#target-icon').attr('src','images/grey-target-icon.png');
			$('[id^="div-tracking-remarks-"]').css('display','none');
			$('#toggle_switch_track').prop('checked', false);
			localStorage.setItem(trackingKey, '1');
		}
	}

	$('#toggle_switch_track').on('click', function(){
		applyTrackingState(this.checked);
	});

	$('#track_btn').on('click', function(){
		let newState = !$('#toggle_switch_track').prop('checked');
		applyTrackingState(newState);
	});

	if(localStorage.getItem(trackingKey) === '1')
		applyTrackingState(false);

	if(localStorage.getItem('full_short_' + reportId) === 'short') {
		setTimeout(function(){
			$('#option1').prop('checked', false).closest('label').removeClass('active');
			$('#option2').prop('checked', true).closest('label').addClass('active');
			$('#option2').trigger('change');
		}, 0);
	}

	let params = new URLSearchParams(window.location.search);

    if(params.has('sort_select_1') || params.has('sort_select_2') || params.has('sort_select_3')){
        $('#div_sort').show();
    }  

	$('#sort_btn').on('click', function(){
        $('#div_sort').toggle();

        const currentlySorted = localStorage.getItem(isSortedKey) === 'true';
        localStorage.setItem(isSortedKey, !currentlySorted);

        if(!currentlySorted){
            $('#sort_select_1').val('subject');
            $('#sort_select_2').val('area');
            $('#sort_select_3').val('task');

            localStorage.setItem(sort1Key, 'subject');
            localStorage.setItem(sort2Key, 'area');
            localStorage.setItem(sort3Key, 'task');
        } 
		else {
            $('#sort_select_1').val(localStorage.getItem(sort1Key) || 'subject');
            $('#sort_select_2').val(localStorage.getItem(sort2Key) || 'area');
            $('#sort_select_3').val(localStorage.getItem(sort3Key) || 'task');
        }

        const params = new URLSearchParams(window.location.search);
        params.set('sort_select_1', $('#sort_select_1').val());
        params.set('sort_select_2', $('#sort_select_2').val());
        params.set('sort_select_3', $('#sort_select_3').val());
        history.replaceState(null, '', '?' + params.toString());
	});

    $('#sort_select_1,#sort_select_2,#sort_select_3').on('change', function(){
		const params = new URLSearchParams(window.location.search);
        const id = $(this).attr('id');
        const val = $(this).val();

        params.set(id, val);
        window.location.search = params.toString();
		
        localStorage.setItem(sort1Key, $('#sort_select_1').val());
        localStorage.setItem(sort2Key, $('#sort_select_2').val());
        localStorage.setItem(sort3Key, $('#sort_select_3').val());
    });
	
	function decodeHtml(html){
		let txt = document.createElement("textarea");
		txt.innerHTML = html;
		return txt.value;
    }
   
    $('[id^="description"]').on('click', function(){
      meeting_id = $(this).data('meetingid');
	  iteration = $(this).data('iteration');
	  chapter = $(this).data('chapter');
	  subject = $(this).data('name');
	  area = $(this).data('area');
	  description = String($(this).data('description'));
	  description = decodeHtml(description).replace(/\n/g,'<br>');
	  $('#new_description').html(description);  
	  $('.modal-title').html("<img src='images/status-icon.png' alt='status icon' width='20' height='20'>&nbsp;&nbsp;עדכון תאור&nbsp;&nbsp;<img src='images/status-icon.png' alt='status icon' width='20' height='20'>&nbsp;&nbsp;");
	  $('.subtitle').html(chapter+"<br/>"+subject+"&nbsp;|&nbsp;"+area).css('line-height','1.1em');
	  $('#modalContent').append("<input type='hidden' id='hidden_meeting_id' value='"+meeting_id+"'><input type='hidden' id='hidden_iteration' value='"+iteration+"'>");
	  $('#modalTaskDescription').modal('show');
    });
	
	$('[id^="task_creation_date"]').on('click', function(){
	   meeting_id = $(this).data('meetingid');
	   iteration = $(this).data('iteration');
	   task_creation_date = $(this).data('taskcreationdate');
	   $('#popup_task_creation_date').val(task_creation_date);
	   $('#modalContent').append("<input type='hidden' id='hidden_meeting_id' value='"+meeting_id+"'><input type='hidden' id='hidden_iteration' value='"+iteration+"'><input type='hidden' id='hidden_task_creation_date' value='"+task_creation_date+"'>");
	   $('#modalTaskCreationDate').modal('show');
	});
	
	$('[id^="destination_date"]').on('click', function(){
	   meeting_id = $(this).data('meetingid');
	   iteration = $(this).data('iteration');  
	   chapter = $(this).data('chapter'); 
	   subject = $(this).data('name');
	   area = $(this).data('area');
	   destination_date = $(this).data('destinationdate');	   
	   $('#popup_destination_date_input').val(destination_date);
	   $('#modalContent').append("<input type='hidden' id='hidden_meeting_id' value='"+meeting_id+"'><input type='hidden' id='hidden_iteration' value='"+iteration+"'><input type='hidden' id='hidden_chapter' value='"+chapter+"'><input type='hidden' id='hidden_subject' value='"+subject+"'><input type='hidden' id='hidden_area' value='"+area+"'><input type='hidden' id='hidden_destination_date' value='"+destination_date+"'>");
	   $('#modalDestinationDate').modal('show');
	});
	
	$('[id^="popup_destination_date_input"]').on('change', function(){
        meeting_id = $('#hidden_meeting_id').val();
	    iteration = $('#hidden_iteration').val();
	    chapter = $('#hidden_chapter').val();
	    subject = $('#hidden_subject').val();
	    area = $('#hidden_area').val();
	    
		$('.modal-title').html("<img src='images/status-icon.png' alt='status icon' width='20' height='20'>&nbsp;&nbsp;עדכון תאריך יעד&nbsp;&nbsp;<img src='images/status-icon.png' alt='status icon' width='20' height='20'>");
	    $('.subtitle').html(chapter+"<br/>"+subject+"&nbsp;|&nbsp;"+area).css('line-height','1.1em');
	   
	    let form_data = new FormData();
        form_data.append('id_meeting',meeting_id);
	    form_data.append('lang',$('#lang').val());
		
	    $.ajax({
			type: 'POST',
			url: 'fill_task_active_remarks.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,
			success: function(data){	
			    $('#task_active_remarks_target_date').html(data);
			},
	    });   
	   
	   $('#modalContent').append("<input type='hidden' id='hidden_meeting_id' value='"+meeting_id+"'><input type='hidden' id='hidden_iteration' value='"+iteration+"'>");
	   $('#modalTaskFollowupDelayTargetDate').modal('show');
    });

    $('[id^="_progress_status"]').on('change', function(){	
		meeting_id = $(this).data('meetingid');
	    iteration = $(this).data('iteration');
	    progress_status_id = $(this).data('progresstatusid');
	    destination_date = $(this).data('destinationdate');
	    chapter = $(this).data('chapter');
	    subject = $(this).data('name');
	    area = $(this).data('area');
	    
		$('.modal-title').html("<img src='images/status-icon.png' alt='status icon' width='20' height='20'>&nbsp;&nbsp;עדכון סטטוס&nbsp;&nbsp;<img src='images/status-icon.png' alt='status icon' width='20' height='20'>");
		$('.subtitle').html(chapter+"<br/>"+subject+"&nbsp;|&nbsp;"+area).css('line-height','1.1em');
	    
		let form_data1 = new FormData();
		form_data1.append('id_progress_status',$(this).val());
		
		$.ajax({
			type: 'POST',
			url: 'get_progress_status.php',
			data: form_data1,
			cache: false,
			processData: false,
			contentType: false,
			success: function(data){
                data = JSON.parse(data);			
				$('#div_new_progress_status')
                .css({'color':data.progress_status_color,'background-color':data.progress_status_bgcolor})
                .text(data.progress_status_name_he);
			},
	    });
	    
		let form_data2 = new FormData();
        form_data2.append('id_meeting',meeting_id);
		form_data2.append('lang',$('#lang').val());
	   
	    $.ajax({
			type: 'POST',
			url: 'fill_task_active_remarks.php',
			data: form_data2,
			cache: false,
			processData: false,
			contentType: false,
			success: function(data){	
			    $('#task_active_remarks_progress_status').html(data);
			},
	    });
	   	
	   $('#new_destination_date').val(destination_date);
	   $('#modalContent input[type="hidden"]').remove();
	   $('#modalContent').append("<input type='hidden' id='hidden_meeting_id' value='"+meeting_id+"'><input type='hidden' id='hidden_iteration' value='"+iteration+"'><input type='hidden' id='hidden_remark' value='"+remark+"'>");
	   
	   let form_data3 = new FormData();
	   form_data3.append('id_progress_status',$(this).val());
		
	   $.ajax({
			type: 'POST',
			url: 'get_progress_status.php',
			data: form_data3,
			cache: false,
			processData: false,
			contentType: false,
			success: function(data){
                data = JSON.parse(data);

                let all_ids_to_edit = $('input[name="meetings_to_update_cbx[]"]:checked').map(function() {
					return $(this).val();
				}).get().join(',');				
			
				if(data.progress_status_name_he == 'ארכיון' || data.progress_status_name_he == 'בהמתנה'){					
					setData($('#hidden_meeting_id').val(), $('#hidden_iteration').val(),'id_progress_status',1,0,'for_closing');  

					const meetingId = $('#hidden_meeting_id').val();
					const row = $('#meetings_table tr.meeting_' + meetingId);

					row.find('.status-cell')
					   .text(data.progress_status_name_he)
					   .css({'color': data.progress_status_color, 'background-color': data.progress_status_bgcolor});		
                    		
					setlocalStorage(meetingId, $('#hidden_iteration').val());
				}
				else {
					if(all_ids_to_edit == "")
						$('#modalTaskFollowupChangeStatus').modal('show');
					else {	
                        setData(meeting_id,iteration,'id_progress_status',1,0,'for_closing');								
				    }				
					
					setlocalStorage(meeting_id,iteration);
				}	
				
				localStorage.removeItem('is_modal_task_actions_opened')
			},
	    });
    })
	
	$('[id^="task_actions"]').on('click', function(){	
		project_id = $(this).data('projectid'); 	   
	    meeting_id = $(this).data('meetingid');  				
		lang = $(this).data('lang'); 
	    iteration = $(this).data('iteration'); 
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
	    reminder_time = $(this).data('remindertime');
		reminder_date = $(this).data('reminderdate'); 
	    
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
				setBellBcgColor(parseInt(task_details[22]) || 0);
			},
	    });
	    setEmergencyTaskCSS(is_priority);
	   
	    $.ajax({
			type: 'POST',
			url: 'check_if_new_task.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,
		    success: function(data){
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
	   $('#modalContent').append("<input type='hidden' id='hidden_meeting_id' value='"+meeting_id+"'><input type='hidden' id='hidden_iteration' value='"+iteration+"'><input type='hidden' id='hidden_project_id' value='"+project_id+"'><input type='hidden' id='hidden_lang' value='"+lang+"'><input type='hidden' id='hidden_user_id' value='"+user_id+"'><input type='hidden' id='hidden_chapter' value='"+chapter+"'><input type='hidden' id='hidden_name' value='"+subject+"'><input type='hidden' id='hidden_area' value='"+area+"'><input type='hidden' id='hidden_recipient' value='"+recipient+"'><input type='hidden' id='hidden_responsible_id' value='"+responsible_id+"'><input type='hidden' id='hidden_destination_date' value='"+destination_date+"'><input type='hidden' id='hidden_progress_status_id' value='"+progress_status_id+"'><input type='hidden' id='hidden_is_priority' value='"+is_priority+"'><input type='hidden' id='hidden_remark' value='"+remark+"'><input type='hidden' id='hidden_track_responsible_id' value='"+track_responsible_id+"'><input type='hidden' id='hidden_track_type' value='"+track_type+"'><input type='hidden' id='hidden_reminder_date' value='"+reminder_date+"'><input type='hidden' id='hidden_reminder_time' value='"+reminder_time+"'>");  
	   $('#modalTaskFollowupActions').modal('show');
    });
	
	$('[id="continuous_btn"]').on('click', function(){
		$('#modalTaskFollowupActions').one('hidden.bs.modal', function(){
			$('[name="progress_status"]').prop('checked', false).prop('disabled', false);
			$('#modalContinuousTask').modal('show');
		});
		$('#modalTaskFollowupActions').modal('hide');
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
	});
	
	$('[id="update_btn"]').on('click', function(){
		meeting_id = $('#hidden_meeting_id').val();
		project_id = $('#hidden_project_id').val();
        destination_date = 	$('#hidden_destination_date').val();	
	
		let form_data = new FormData();
        form_data.append('id_meeting',meeting_id);
		form_data.append('id_project',project_id);
		form_data.append('is_updates',1);
		form_data.append('lang',$('#project_lang').val());

		if($('#project_lang').val() == "HE")
			$('.modal-title').html("<img src='images/status-icon.png' alt='status icon' width='20' height='20'>&nbsp;&nbsp;עדכון&nbsp;&nbsp;<img src='images/status-icon.png' alt='status icon' width='20' height='20'>");
        else
		    $('.modal-title').html("Update");
        
        $('.subtitle').html(chapter+"<br/>"+subject+"&nbsp;|&nbsp;"+area).css('line-height','1.1em');		
		
	    $.ajax({
			type: 'POST',
			url: 'fill_task_active_remarks.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,
			success: function(data){     				
			    $('#task_active_remarks_progress_status_update').html(data);
			},
	    });
		
	    $('#new_destination_date_update').val(destination_date);
		
		let selectedId = $('#hidden_progress_status_id').val();
        $('#progress_status_update').val(selectedId);
		
	    $('#modalTaskFollowupActions').modal('hide');
	    $('#modalUpdateTask').modal('show');
	});
	
    $('[id="tracking_btn"]').on('click', function(){
        meeting_id = $('#hidden_meeting_id').val(); 
		iteration = $('#hidden_iteration').val(); 
	    subject = $('#hidden_name').val();
	    area = $('#hidden_area').val();
		reminder_time = $('#hidden_reminder_time').val();	
	    reminder_date = $('#hidden_reminder_date').val();	
	    
		if(reminder_date != '0000-00-00') {
		  $('#div_reminder_date').show();
	      $('#reminder_date').val(reminder_date);
	    }
	    else 
		  $('#div_reminder_date').hide();
	 
	    $('#modalTaskTracking .modal-title').html("<i class='fas fa-bullseye' style='font-size:22px;color:#888;'></i>&nbsp;&nbsp;מעקב אקטיבי&nbsp;&nbsp;<i class='fas fa-bullseye' style='font-size:22px;color:#888;'></i>");
		$('.subtitle').html(chapter+"<br/>"+subject+"&nbsp;|&nbsp;"+area).css('line-height','1.1em');
	  
	    $('#div_reminder_date').css('display','block');
	 
	    if($('#hidden_reminder_time').val() == 0) {
		  $('#div_reminder_date').css('display','none');
		  $('#not_reminders').prop('checked',true);
	    }
	   
	    if($('#hidden_reminder_time').val() == 1)
		  $('#reminder_tomorrow').prop('checked',true);
	    if($('#hidden_reminder_time').val() == 2)
		  $('#reminder_after_three_days').prop('checked',true);
	    if($('#hidden_reminder_time').val() == 3)
		  $('#reminder_after_one_week').prop('checked',true);
	    if($('#hidden_reminder_time').val() == 4)
		  $('#reminder_after_two_weeks').prop('checked',true);
	    if($('#hidden_reminder_time').val() == 5)
		  $('#reminder_after_one_month').prop('checked',true);
	    if($('#hidden_reminder_time').val() == 6) 
		  $('#reminder_after_selected_date').prop('checked',true);
	   
        let savedResp = parseInt($('#hidden_track_responsible_id').val()) || 0;
	    $('#users option').each(function() {
	        if(savedResp === 0){
	            if($(this).val() == $('#hidden_user_id').val())
	                $(this).prop('selected', true);
	        } else {
	            if($(this).val() == savedResp)
	                $(this).prop('selected', true);
	        }
	    });
	    $('#users').css('background-color', savedResp === 0 ? '#fffacd' : '');

	    let form_data = new FormData();
		form_data.append('id_meeting',meeting_id);
		form_data.append('isTracking',1);
		
	    $.ajax({
			type: 'POST',
			url: 'fill_task_active_remarks.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,
			success: function(data){
				$('#task_active_remarks_tracking').html(data);
			},
	    });
	   
	    $('#modalTaskFollowupActions').modal('hide');
	    $('#modalTaskTracking').modal('show');
    });
	
	$('[id="users"]').on('change', function() {
		$(this).css('background-color', '');
		setData($('#hidden_meeting_id').val(),$('#hidden_iteration').val(),'track_responsible_id',0,0,'popup');
	});
	
	$('[id="send_email_btn"]').on('click', function(){
       meeting_id = $('#hidden_meeting_id').val(); 
       iteration = $('#hidden_iteration').val();  
	   subject = $('#hidden_name').val();
	   area = $('#hidden_area').val();
	   $('.modal-title').html(subject+' - '+area);
	   $('#email_recipient').val($('#hidden_recipient').val());
	   $('#modalTaskFollowupActions').modal('hide');
	   $('#modalSendEmail').modal('show');
    });
	
	$('#send_whatsapp_btn,#send_tasks_btn').on('click', function() {	
	    meeting_id = 0;
		if($('#hidden_meeting_id').val() > 0)
			meeting_id = $('#hidden_meeting_id').val();
        
		project_id = $('#project_id').val();
        if($('#hidden_project_id').val() > 0)
			project_id = $('#hidden_project_id').val(); 
		
		iteration = 0;
		if($('#hidden_iteration').val() > 0)
			iteration = $('#hidden_iteration').val();

	    $('#modalTaskFollowupActions').modal('hide');
		
		let all_ids_to_edit = $('input[name="meetings_to_update_cbx[]"]:checked').map(function() {
			return $(this).val();
        }).get().join(',');
	   
	    let form_data = new FormData();
		
        if(all_ids_to_edit == '') 
		    form_data.append('id_meeting',meeting_id);
		if(all_ids_to_edit != '') 
            form_data.append('id_project',project_id);			
		
		form_data.append('all_ids_to_edit',all_ids_to_edit);
	   
	    $.ajax({
			type: 'POST',
			url: 'task_details.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,
			success: function(data){				
            	let task_details = data.split('|~|');		
				
				let content;
				let is_all_ids_to_edit = 0;
			    
				if(all_ids_to_edit == "") 
				   content = fillContentTaskDetails(meeting_id,iteration,task_details,false);
				else 
				   content = fillMultiTasks(meeting_id,task_details)
			    
				if(all_ids_to_edit.indexOf(",") !== -1)
					is_all_ids_to_edit = 1;			
				
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
						success: function(response) {
							const imageUrl = '<?= BASE_URL ?>'+response;
							shareImage(imageUrl,meeting_id,project_id,iteration,is_all_ids_to_edit); 							
						}, 
				   });
                })	
			},
	   });
     });
	
	 $('[id="link_prev_task"]').on('click', function(){
	   	let form_data = new FormData();
        form_data.append('from','meetings');
        form_data.append('id_project',$('#hidden_project_id').val());
		form_data.append('sql',$('#sql').val());
		
		$.ajax({
			type: 'POST',
			url: 'fill_meeting_ids.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,
			success: function(data) {
				let meeting_ids = data;
				navigateTasks(meeting_ids,'prev');
			}, 
		});		   
	});
	
	$('[id="link_next_task"]').on('click', function(){
	   	let form_data = new FormData();
        form_data.append('from','meetings');
        form_data.append('id_project',$('#hidden_project_id').val());
		form_data.append('sql',$('#sql').val());
		
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
	
	let modalFilterBySupplier = $('#modalFilterBySupplier');
    let modalFilterByPeriodNewTasks = $('#modalFilterByPeriodNewTasks');
    let modalFilterByPeriodLate = $('#modalFilterByPeriodLate');
    let modalFilterByProgressStatus = $('#modalFilterByProgressStatus');
    let modalFilterByTask = $('#modalFilterByTask');
	let modalTaskDescription = $('#modalTaskDescription');
	let modalTaskFollowupActions = $('#modalTaskFollowupActions');
	var _taskModalFullyShown = false;
	var _suppressScrollOnHide = false;
	let modalTaskFollowupChangeStatus = $('#modalTaskFollowupChangeStatus');
	let modalContinuousTask = $('#modalContinuousTask');
	let modalTaskCreationDate = $('#modalTaskCreationDate');
	let modalDestinationDate = $('#modalDestinationDate');
	let modalTaskFollowupDelayTargetDate = $('#modalTaskFollowupDelayTargetDate');
	let modalTaskTracking = $('#modalTaskTracking');
	let modalSendEmail = $('#modalSendEmail');
	let modalHistoryTasks = $('#modalHistoryTasks');
	let modalUpdateTask = $('#modalUpdateTask');
	
	$(document).on('mousedown', function(event){
		const $target = $(event.target);

    if($target.closest('#modalFilterBySupplier').length ||
        $target.closest('#modalFilterByPeriodNewTasks').length ||
        $target.closest('#modalFilterByPeriodLate').length ||
        $target.closest('#modalFilterByProgressStatus').length ||
        $target.closest('#modalFilterByTask').length ||
        $target.closest('#modalTaskDescription').length ||
        $target.closest('#modalTaskFollowupActions .modal-content').length ||
        $target.closest('#modalTaskFollowupChangeStatus').length ||
        $target.closest('#modalTaskCreationDate').length ||
        $target.closest('#modalDestinationDate').length ||
        $target.closest('#modalTaskFollowupDelayTargetDate').length ||
        $target.closest('#modalTaskTracking').length ||
        $target.closest('#modalSendEmail').length ||
        $target.closest('#modalHistoryTasks').length ||
        $target.closest('#modalContinuousTask').length ||
        $target.closest('#modalUpdateTask .modal-content').length){
        return;
    }

    if(modalTaskFollowupActions.is(':visible')){
        if(!_suppressScrollOnHide)
            _suppressScrollOnHide = (localStorage.getItem('is_modal_task_actions_opened') === 'true');
        localStorage.removeItem('is_modal_task_actions_opened');
        const _mEl = document.getElementById('modalTaskFollowupActions');
        const _mInst = bootstrap.Modal.getInstance(_mEl);
        if(_mInst) {
            if(_mInst._isTransitioning) _mInst._isTransitioning = false;
            _mInst.hide();
        } else {
            $('#modalTaskFollowupActions').modal('hide');
        }
        return;
    }

    if(modalUpdateTask.is(':visible')){
        const _mElU = document.getElementById('modalUpdateTask');
        const _mInstU = bootstrap.Modal.getInstance(_mElU);
        if(_mInstU) {
            if(_mInstU._isTransitioning) _mInstU._isTransitioning = false;
            _mInstU.hide();
        } else {
            $('#modalUpdateTask').modal('hide');
        }
        return;
    }

    if(modalFilterBySupplier.is(':visible') ||
        modalFilterByPeriodNewTasks.is(':visible') ||
        modalFilterByPeriodLate.is(':visible') ||
        modalFilterByProgressStatus.is(':visible') ||
        modalFilterByTask.is(':visible') ||
        modalTaskDescription.is(':visible') ||
        modalTaskFollowupChangeStatus.is(':visible') ||
        modalTaskCreationDate.is(':visible') ||
        modalDestinationDate.is(':visible') ||
        modalTaskFollowupDelayTargetDate.is(':visible') ||
        modalTaskTracking.is(':visible') ||
        modalSendEmail.is(':visible') ||
        modalHistoryTasks.is(':visible') ||
        modalContinuousTask.is(':visible')){
			let url = 'meetings.php?project_id=' + $('#project_id').val();
			if($('#hidden_iteration').val())
				url += '&row=row_' + $('#hidden_iteration').val();
			if($('#is_specific_filter').val())
				url += '&is_specific_filter=1';
			const _navLang = new URLSearchParams(window.location.search).get('lang');
			if(_navLang) url += '&lang=' + _navLang;
			location.href = url;
    }
	});
});

const shortRowStyle = 'overflow:hidden; white-space:nowrap; text-overflow:ellipsis; display:block; width:100%; height:22px; line-height:22px; font-size:12px;';
const fullRowStyle  = 'overflow:auto; white-space:normal; height:auto; line-height:normal; display:block; font-size:12px;';

function applyShortMode($td, maxChars){
    // Save original HTML once (preserves <br>/<div> line breaks)
    if($td.data('fullHtml') === undefined)
        $td.data('fullHtml', $td.html());
    const plainText = $td.text().trim();
    const shortText = plainText.length > maxChars ? plainText.substring(0, maxChars) + ' …' : plainText;
    $td.html('<div style="' + shortRowStyle + '">' + shortText + '</div>');
}

function applyFullMode($td){
    const fullHtml = $td.data('fullHtml');
    if(fullHtml !== undefined)
        $td.html(fullHtml);  // restore original HTML with line breaks
}

$(document).on('change', 'input[name="options"]', function (){
    localStorage.setItem('full_short_' + reportId, this.id === 'option2' ? 'short' : 'full');
    const isShort = this.id === 'option2';
    const maxChars = 100;
    $('td[id^="td_subject_"], td[id^="td_area_"], td[id^="td_description_"]').each(function (){
        if(isShort) applyShortMode($(this), maxChars);
        else        applyFullMode($(this));
    });

    $('.tr-image-row').toggle(!isShort);

    $('select[id^="task_"], select[id^="responsible_"], select[id^="pass_on_"], select[id^="_progress_status_"]').css({
        'display': 'inline-block',
        'visibility': 'visible'
    });

    $('input[id^="task_creation_date_"], input[id^="destination_date_"]').css({
        'display': 'inline-block',
        'visibility': 'visible',
        'height': ''
    });
});

$(document).on('mouseenter', 'tr[id*="row_"]', function(){
    if(!$('#option2').is(':checked')) return;
    $(this).find('td[id^="td_subject_"], td[id^="td_area_"], td[id^="td_description_"]').each(function(){
        applyFullMode($(this));
    });
    $(this).next('.tr-image-row').show();
});

$(document).on('mouseleave', 'tr[id*="row_"]', function(){
    if(!$('#option2').is(':checked')) return;
    const maxChars = 100;
    $(this).find('td[id^="td_subject_"], td[id^="td_area_"], td[id^="td_description_"]').each(function(){
        applyShortMode($(this), maxChars);
    });
    $(this).next('.tr-image-row').hide();
});

$(document).on('click', 'tr[id*="row_"]', function(){
    const row = $(this);
    $('tr.row-darken').removeClass('row-darken');
    row.addClass('row-darken');
});

$(document).on('click','#save_update_task_btn', function (){
	let form_data = new FormData();
	form_data.append('from','meetings');
	form_data.append('id_project',$('#hidden_project_id').val());
	form_data.append('sql',$('#sql').val());
	
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
				   
			if($('#progress_status_update option:selected').text().trim() == 'ארכיון' ||
				$('#progress_status_update option:selected').text().trim() == 'בוצע/נמסר'){
					index++;
					if(index >= meeting_ids_array.length) 
						index = 0;
			}
			
			let next_meeting_id = meeting_ids_array[index];				
			localStorage.setItem('next_meeting_id',next_meeting_id);
			setData(current_meeting_id,$('#hidden_iteration').val(),'update_task',1,0,'for_closing');
		}
   });
});

function checkAllItems(){
	if($('#check_all').is(':checked')){
        $('.meetings_to_update_cbx').prop('checked', true);	
		$('[id^="meetings_to_update_cbx_"]').each(function(){
			let checked_row = $(this).attr('id').replace('meetings_to_update_cbx_', '');

			['td_cbx_meetings_to_update_',
			'td_count_',
			'td_subject_',
			'td_area_',
			'td_description_',
			'td_task_',
			'td_responsible_',
			'td_pass_on_',
			'td_task_creation_date_',
			'td_destination_date_',
			'td_progress_status_'
			].forEach(function(prefix) {
				$('#'+prefix+checked_row).addClass('in-strong-relief');
			});
		});

		$('#apply_tasks_to_rdv_btn')
			.removeClass('disabled-link')
			.prop('disabled',false)
			.css('background-color','red')
			.css('color','#474d5d');
		
		$('#send_tasks_btn')
		    .removeClass('disabled-link')
			.prop('disabled',false);
		
		$('#img_plus_rdv').attr('src','images/actif-plus-icon.png');
		$('#task_label,#pdf_label,#share_label').css('color','red');
    } 
	else {
        $('.meetings_to_update_cbx').prop('checked', false);  
		
        $('#apply_tasks_to_rdv_btn')
		    .addClass('disabled-link')
			.prop('disabled',true)
			.css('background-color','#f5f4fe')
			.css('color','#474d5d');	
			
		$('#send_tasks_btn')
		    .addClass('disabled-link')
			.prop('disabled',true)	
			
	    $('[id^="meetings_to_update_cbx_"]').each(function() {
			let checked_row = $(this).attr('id').replace('meetings_to_update_cbx_', '');

			['td_cbx_meetings_to_update_',
			'td_count_',
			'td_subject_',
			'td_area_',
			'td_description_',
			'td_task_',
			'td_responsible_',
			'td_pass_on_',
			'td_task_creation_date_',
			'td_destination_date_',
			'td_progress_status_'
			].forEach(function(prefix) {
				$('#'+prefix+checked_row).removeClass('in-strong-relief');
			});
		});
		
		$('#img_plus_rdv').attr('src','images/plus-icon.png');
		$('#task_label,#pdf_label,#share_label').css('color','#1A5276');
    }
}

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
		success: function(data) {
			$('#link_next_task').trigger('click');
		}, 
	});
}

function setReportData(field){
	let form_data = new FormData();
	form_data.append('is_specific_filter',$('#is_specific_filter').val());
	form_data.append('id_custom_report',$('#id_custom_report').val());
	form_data.append('id_rdv_report',$('#id_rdv_report').val());
	form_data.append('field',field);
	
	if(field == 'lang'){
		let lang = $('#select_lang').val();
	    form_data.append('lang',lang);
	}
	
	if(field == 'picture'){
		let is_images = $("#toggle_switch_images").is(":checked") ? 1 : 0;
		form_data.append('is_images',is_images);
	}
	
	if(field == 'colors'){
		let is_colors = $("#toggle_switch_colors").is(":checked") ? 1 : 0;
		form_data.append('is_colors',is_colors);
	}

	if(field == 'highlight'){
		form_data.append('period_new_tasks', $('#period_new_tasks_toolbar').val());
	}

	if(field == 'table_view'){
		let columns_list = '';
		$('#columns_list:checked').each(function(i){
			columns_list+= $(this).val()+',';
		});
		columns_list = columns_list.substring(0,columns_list.length - 1);
		
		form_data.append('period_new_tasks',$('#period_new_tasks_toolbar').val());
	    form_data.append('columns_list',columns_list);
	}
	
	$.ajax({
		type: 'POST',
		url: 'set_report_data.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,
		success: function(data){
			window.location.reload();
		}
	});	
} 

function makeFilter(){
	if($('#supplier_filter').val() == 'undefined')
	   window.location = 'add_custom_report.php?id='+$('#id_custom_report').val()+'&project_id='+$('#project_id').val()+'&task_filter='+$('#task_filter').val()+'&progress_status_filter='+$('#progress_status_filter').val()+'&supplier_filter='+$('#get_supplier_filter').val()+'&period_new_task_filter='+$('#period_new_task_filter').val()+'&period_late_filter='+$('#period_late_filter').val()+'&search_filter='+$('#input_search').val()+'&is_specific_filter=1';
    else if($('#id_project_status_report').val() != 'undefined') 
	   window.location = 'add_custom_report.php?id='+$('#id_project_status_report').val()+'&project_id='+$('#project_id').val()+'&task_filter='+$('#task_filter').val()+'&progress_status_filter='+$('#progress_status_filter').val()+'&supplier_filter='+$('#get_supplier_filter').val()+'&period_new_task_filter='+$('#period_new_task_filter').val()+'&period_late_filter='+$('#period_late_filter').val()+'&search_filter='+$('#input_search').val()+'&is_specific_filter=1';
}

function setCurrentReportSession(custom_report_id){
	let form_data = new FormData();
    form_data.append('id_project',$('#project_id').val());
    form_data.append('id_custom_report',custom_report_id);

	const isRdvContext = parseInt($('#id_rdv_report').val()) > 0;
	if(isRdvContext){
		form_data.append('rdv_override', '1');
		form_data.append('is_images',       $('#toggle_switch_images').is(':checked') ? 1 : 0);
		form_data.append('is_colors',       $('#toggle_switch_colors').is(':checked') ? 1 : 0);
		form_data.append('lang',            $('#select_lang').val());
		form_data.append('period_new_tasks',$('#period_new_tasks_toolbar').val());
		form_data.append('columns_list',    $('#columns_list').val());
	}

	$.ajax({
		type: 'POST',
		url: 'set_current_report_session.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,
		success: function(data){
			if(isRdvContext){
				const rdvId = $('#report_type').val();
				const crId  = 'general_' + custom_report_id;
				['full_short','is_data_sorted','sort_select_1','sort_select_2','sort_select_3']
					.forEach(function(k){
						const val = localStorage.getItem(k + '_' + rdvId);
						if(val !== null) localStorage.setItem(k + '_' + crId, val);
					});
			}
			location.href = 'meetings.php?project_id='+$('#project_id').val();
		}
	});
}

function setFilter(id_report,id,search_text,filter_type){
	let subject_str = $('#subject_str').val();
    let area_str = $('#area_str').val();
	let description_str = $('#description_str').val();
	let chapters = $('#chapters_ids').val();
	let tasks_types = $('#tasks_types_ids').val();
	let tasks = $('#tasks_ids').val();
	let period_new_tasks = $('#period_new_tasks').val();
	
	if(filter_type == 'supplier_filter')
		setCurrentReportSession($('#id_project_status_report').val());			

	if(filter_type == 'task_filter')
		tasks = id;
	
	let responsibles = $('#responsibles_ids').val();
	let and_or_responsibles = $('#and_or_responsibles').val();
	let pass_ons = $('#pass_ons_ids').val();
	
	if(filter_type == 'supplier_filter'){
		and_or_responsibles = 'AND';
		let _id = id.substr(id.indexOf('-')+1);
		if(_id.length > 0) 
		   responsibles = _id;		
	}
	
	let progress_status = $('#progress_status_ids').val();
	if(filter_type == 'progress_status_filter')
        progress_status = id;	
	
    let period_creation_date = $('#period_creation_date').val();
    let creation_date_start = $('#creation_date_start').val();
    let creation_date_end = $('#creation_date_end').val();

	if(filter_type == 'period_new_task_filter'){	    
		period_creation_date = id;			
	   
		let startDate = new Date();
		if(period_creation_date == 'today')
		  startDate.setDate(startDate.getDate() + 0);
		if(period_creation_date == 'three_days')
		  startDate.setDate(startDate.getDate() - 3);
		if(period_creation_date == 'one_week')
		  startDate.setDate(startDate.getDate() - 7);
		if(period_creation_date == 'two_weeks')
		  startDate.setDate(startDate.getDate() - 14);
		if(period_creation_date == 'one_month')
		  startDate.setMonth(startDate.getMonth() - 1);
		if(period_creation_date == 'two_months')
		  startDate.setMonth(startDate.getMonth() - 2);
		if(period_creation_date == 'one_year')
		  startDate.setFullYear(startDate.getFullYear() - 1);
		if(period_creation_date == 'two_years')
		  startDate.setFullYear(startDate.getFullYear() - 2);
		let formatedStartDate = formatDate(startDate);  
		creation_date_start = formatedStartDate;

		let endDate = new Date();
		let formatedEndDate = formatDate(endDate);  
		creation_date_end = formatedEndDate;			
    }
	
	let period_destination_date = $('#period_destination_date').val();
	let destination_date_start = $('#destination_date_start').val();
	let destination_date_end = $('#destination_date_end').val();
	
	if(filter_type == 'period_late_filter'){
		period_destination_date = id;
			  
		let startDate = new Date();	
		if(period_destination_date == 'three_days')
		  startDate.setDate(startDate.getDate() - 3);
		if(period_destination_date == 'one_week')
		  startDate.setDate(startDate.getDate() - 7);
		if(period_destination_date == 'two_weeks')
		  startDate.setDate(startDate.getDate() - 14);
		if(period_destination_date == 'one_month')
		  startDate.setMonth(startDate.getMonth() - 1);
		if(period_destination_date == 'two_months')
		  startDate.setMonth(startDate.getMonth() - 2);
		let formatedStartDate = formatDate(startDate);  
		destination_date_start = formatedStartDate;

		let endDate = new Date();
		let formatedEndDate = formatDate(endDate);  
		destination_date_end = formatedEndDate;	       
    }

	let is_appears_cond = (filter_type == 'search_filter' && search_text.trim() != '') ? 'is_appears IN(0,1)' : 'is_appears = 1';
	let sql = 'SELECT c.name AS name,m.id AS id,m.is_priority AS is_priority,m.id_user AS id_user,m.id_task_type AS id_task_type,m.id_chapter AS id_chapter,m.subject AS subject,m.ids_rdv AS ids_rdv,m.area,m.description,m.id_task,m.id_responsible,m.id_pass_on,m.task_creation_date,m.destination_date,m.id_progress_status,m.updated_date AS updated_date,m.image1 AS image1,m.is_appears_img1 AS is_appears_img1,m.image1_width AS image1_width,m.image1_height AS image1_height,m.image2 AS image2,m.image2_width AS image2_width,m.image2_height AS image2_height,m.is_appears_img2 AS is_appears_img2,m.is_change_row_style AS is_change_row_style,m.id_track_responsible AS id_track_responsible,m.track_type AS track_type,m.reminder_time AS reminder_time,m.reminder_date AS reminder_date FROM dne_meetings m LEFT JOIN dne_chapters c ON m.id_chapter = c.id LEFT JOIN dne_tasks t ON m.id_task = t.id WHERE m.id_project ='+$('#project_id').val()+ ' AND m.' + is_appears_cond + ' ';

	let params = [];

	if(chapters != '')
	   params.push('m.id_chapter IN('+chapters+')');

	if(tasks_types != '')
	   params.push('m.id_task_type IN('+tasks_types+')');	

    if(filter_type == 'search_filter') 
	   params.push("(m.subject LIKE '%"+search_text+"%' OR m.area LIKE '%"+search_text+"%' OR m.description LIKE '%"+search_text+"%')");
	else {
		if(subject_str != '')
	       params.push("m.subject LIKE '%"+subject_str+"%'");
	   
		if(area_str != '')
		   params.push("m.area LIKE '%"+area_str+"%'");

		if(description_str != '')
		   params.push("m.description LIKE '%"+description_str+"%'");
	}
	
	if(tasks != '')
	   params.push('m.id_task IN('+tasks+')');
  
    if(and_or_responsibles == 'AND') {
		if(responsibles != '') 
			params.push('m.id_responsible IN('+responsibles+')');
		
		if(pass_ons != '')
			params.push('m.id_pass_on IN('+pass_ons+')');	
	}
	else if(and_or_responsibles == 'OR'){
		if(responsibles != '' && pass_ons != '')
		    params.push('(m.id_responsible IN('+responsibles+') OR m.id_pass_on IN('+pass_ons+'))');
		if(responsibles == '' && pass_ons != '')
			params.push('m.id_pass_on IN('+pass_ons+')');
		if(responsibles != '' && pass_ons == '')
			params.push('m.id_responsible IN('+responsibles+')');
	}
       
	if(progress_status != '' && filter_type != 'search_filter')
	  params.push('m.id_progress_status IN('+progress_status+')');

	let creation_date = '';
	if(creation_date_start != '0000-00-00' && creation_date_end != '0000-00-00')
	  creation_date = "m.task_creation_date BETWEEN '"+creation_date_start+"' AND '"+creation_date_end+"'";

	else if(creation_date_start != '0000-00-00' && creation_date_end == '0000-00-00')
	creation_date = "m.task_creation_date >= '"+creation_date_start+"'";

	else if(creation_date_start == '0000-00-00' && creation_date_end != '0000-00-00')
	creation_date = "m.task_creation_date <= '"+creation_date_end+"'";

	if(creation_date != '')
	params.push(creation_date);

	let destination_date = '';
	if(destination_date_start != '0000-00-00' && destination_date_end != '0000-00-00')
	   destination_date = "m.destination_date BETWEEN '"+destination_date_start+"' AND '"+destination_date_end+"'";

	else if(destination_date_start != '0000-00-00' && destination_date_end == '0000-00-00')
	   destination_date = "m.destination_date >= '"+destination_date_start+"'";

	else if(destination_date_start == '0000-00-00' && destination_date_end != '0000-00-00')
	   destination_date = "m.destination_date <= '"+destination_date_end+"'";

	if(destination_date != '')
	   params.push(destination_date);
   
    if($('#id_rdv_report').val() > 0)
		params.push("FIND_IN_SET("+$('#id_rdv_report').val()+",ids_rdv) > 0");

	let params_str = '';
	if(params.length > 0)
	  params_str = params.join(' AND ');

	if(params_str != '')
	  sql+= ' AND '+params_str;  
	   
	let columns_list = $('#columns_list').val();
	let lang = $('#lang').val();
	let is_images = $('#is_images').val();
	let is_colors = $('#is_colors').val();
    let form_data = new FormData();
	form_data.append('chapters_list',chapters);
	form_data.append('tasks_types_list',tasks_types);
	form_data.append('tasks_list',tasks);
	form_data.append('responsibles_list',responsibles);
	form_data.append('and_or_responsibles',and_or_responsibles);
	form_data.append('pass_ons_list',pass_ons);
	form_data.append('progress_status_list',progress_status);
	form_data.append('period_creation_date',period_creation_date);
	form_data.append('creation_date_start',creation_date_start);
	form_data.append('creation_date_end',creation_date_end);
	form_data.append('period_new_tasks',period_new_tasks);
	form_data.append('period_destination_date',period_destination_date);
	form_data.append('destination_date_start',destination_date_start);
	form_data.append('destination_date_end',destination_date_end);
	form_data.append('sql',sql);
	form_data.append('columns_list',columns_list);
	form_data.append('lang',lang);
	form_data.append('is_images',is_images);
	form_data.append('is_colors',is_colors);
	
    $.ajax({
		type: 'POST',
		url: 'set_session_filter_meetings.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,
		success: function(data){
		   location.href = 'meetings.php?id='+id_report+'&project_id='+$('#project_id').val()+'&task_filter='+$('input[name="task_filter"]:checked').val()+'&progress_status_filter='+$('input[name="progress_status_filter"]:checked').val()+'&supplier_filter='+$('input[name="supplier_filter"]:checked').val()+'&period_new_task_filter='+$('input[name="period_new_task_filter"]:checked').val()+'&period_late_filter='+$('input[name="period_late_filter"]:checked').val()+'&search_filter='+$('#input_search').val()+'&is_specific_filter=1';
		},
	});
}

function toReportSettings(){
	let all_ids_to_print = '';
	
    $('input[type="checkbox"][id^="meetings_to_update_cbx"]').each(function() {
       if ($(this).prop('checked')) {
	      all_ids_to_print+= $(this).val()+',';
	   }
    });
	
	all_ids_to_print = all_ids_to_print.substring(0,all_ids_to_print.length - 1);

	location.href='report_settings.php?project_id='+$('#project_id').val()+'&report_type='+$('#report_type').val()+'&task_filter='+$('#task_filter').val()+'&progress_status_filter='+$('#progress_status_filter').val()+'&supplier_filter='+$('#get_supplier_filter').val()+'&period_new_task_filter='+$('#period_new_task_filter').val()+'&is_specific_filter='+$('#is_specific_filter').val()+'&all_ids_to_print='+all_ids_to_print+'&lang='+$('#lang').val();
}

function toAddRdv(from){
	let all_ids_to_edit = $('input[name="meetings_to_update_cbx[]"]:checked').map(function() {
		return $(this).val();
    }).get().join(',');
	
	location.href='add_rdv.php?project_id='+$('#project_id').val()+'&id=0&is_specific_filter='+$('#is_specific_filter').val()+'&all_ids_to_edit='+all_ids_to_edit+'&from='+from+'&lang='+$('#lang').val();
}

$('#to_add_meeting_btn').click (function (e){  
	let form_data = new FormData();
	form_data.append('report_date',$('#report_date').val());

	$.ajax({
		type: 'POST',
		url: 'set_report_date_session.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,
		success: function(data){
		  let url = 'add_meeting.php?id=0&project_id='+$('#project_id').val();
		  if($('#is_specific_filter').val())
			  url+= '&is_specific_filter=1';
		 
		  if($('#id_rdv_report').val() > 0)
			 url+= '&id_rdv='+$('#id_rdv_report').val();
		  location.href = url;
		},
	});        
})
</script>

<style>
.btn {
    padding: 0px 5px !important;
	box-shadow: 3px 3px 8px rgba(180, 180, 180, 0.6);
}

.colorGreen {
	color: green;
}

#a_project_title:hover {
	color: grey;
}

@media (max-width: 1024px) {
	.btn {
	    font-size: 13px;
	}
}

th {
    border-right: 1px solid #d6d2d2!important;
    border-left: 1px solid #d6d2d2!important;
}

td {
	border: 1px solid #d6d2d2!important;
}

.width60Percents {
	width: 62%;
}

.width40Percents {
	width: 36%;
}

.row-darken td[id^="td_cbx_meetings_to_update_"],
.row-darken td[id^="td_count_"],
.row-darken td[id^="td_description_"],
.row-darken td[id^="td_subject_"],
.row-darken td[id^="td_area_"] {
    position: relative;
}

.row-darken td[id^="td_cbx_meetings_to_update_"]::after,
.row-darken td[id^="td_count_"]::after,
.row-darken td[id^="td_description_"]::after,
.row-darken td[id^="td_subject_"]::after,
.row-darken td[id^="td_area_"]::after {
    content: "";
    position: absolute;
    inset: 0;
    background-color: #99ccff;
    pointer-events: none;
    z-index: 0;
}

.row-darken td[id^="td_cbx_meetings_to_update_"] > *,
.row-darken td[id^="td_count_"] > *,
.row-darken td[id^="td_description_"] > *,
.row-darken td[id^="td_subject_"] > *,
.row-darken td[id^="td_area_"] > * {
    position: relative;
    z-index: 1;
    background-color: transparent !important;
}

td[id^="td_description_"] > div,
td[id^="td_subject_"] > div,
td[id^="td_area_"] > div {
    display: flex;
    flex-direction: column;
    flex: 1 1 auto;
    height: 100%;
    width: 100%;
    background-color: inherit !important;
    position: relative;
    z-index: 1;
}

#meetings_table {
    table-layout: fixed;
    width: 100%;
}

#meetings_table td {
    word-wrap: break-word;
    overflow-wrap: break-word;
}

td[id^="td_description_"] {
    word-wrap: break-word;
    overflow-wrap: break-word;
}

td[id^="td_description_"] > div {
    white-space: normal;
    word-wrap: break-word;
    overflow-wrap: anywhere;
    max-height: 130px;
    overflow-y: auto !important;
}

td[id^="td_description_"] span {
    white-space: normal !important;
    word-wrap: break-word !important;
    overflow-wrap: anywhere !important;
    display: inline !important;
}

@media (max-width: 900px) {
    #meetings_table {
        font-size: 11px;
    }
    #meetings_table th,
    #meetings_table td {
        padding: 3px 4px !important;
        font-size: 11px;
    }
}

@media (max-width: 600px) {
    #meetings_table th,
    #meetings_table td {
        font-size: 10px;
        padding: 2px 3px !important;
    }
}

.btn-group {
	width: 180px;
}

.btn-group-toggle .btn {
    font-size: 11px;
	height: 30px;
    line-height: 18px;
}

.btn-group-toggle label:has(input:not(:checked)) {
    opacity: 0.45;
}

.btn-group-toggle input[type="radio"] {
    display: none;
}

span.sf-hl {
    font-weight: bold;
    font-style: italic;
    display: contents;
}
.modal.fade .modal-dialog { transition-duration: .15s !important; }
.modal-backdrop.fade { transition-duration: .1s !important; }
</style>

<script>
$(function() {
    let term = $('#search_filter').val();
    if (!term) return;

    let esc = term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    let re = new RegExp('(' + esc + ')', 'gi');

    function highlightEl(el) {
        let html = el.innerHTML;
        let result = html.replace(/(<[^>]+>|[^<]+)/g, function(part) {
            if (part.charAt(0) === '<') return part;
            re.lastIndex = 0;
            return part.replace(re, '<span class="sf-hl">$1</span>');
        });
        re.lastIndex = 0;
        if (result !== html) {
            let ce = el.getAttribute('contenteditable');
            el.removeAttribute('contenteditable');
            el.innerHTML = result;
            if (ce !== null) el.setAttribute('contenteditable', ce);
        }
    }

    document.querySelectorAll('[id^="subject_"],[id^="area_"],[id^="description_"]').forEach(highlightEl);
});
</script>
