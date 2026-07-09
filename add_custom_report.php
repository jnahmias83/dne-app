<?php
include 'include/header.php';
include 'functions/functions.php';

$id = @$_GET['id'];
$project_id = @$_GET['project_id'];
$period_new_task_filter = @$_GET['period_new_task_filter'];
$period_late_filter = @$_GET['period_late_filter'];
$task_filter = @$_GET['task_filter'];
$progress_status_filter = @$_GET['progress_status_filter'];
$get_supplier_filter = @$_GET['supplier_filter'];
$supplier_filter = $get_supplier_filter;
if($get_supplier_filter != 'not')
  $supplier_filter = substr($get_supplier_filter,strpos($get_supplier_filter,'-')+1);
$search_filter = @$_GET['search_filter'];
$is_specific_filter = @$_GET['is_specific_filter'];

$query = $mysqli->prepare("SELECT * FROM dne_projects WHERE id = ?");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$project = fetch_unique($query);

$query = $mysqli->prepare("SELECT * FROM dne_log_current_report WHERE id_project = ?");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$log_current_report = fetch_unique($query);
$id_custom_report = @$log_current_report->id_custom_report;
$id_rdv_report = @$log_current_report->id_rdv_report;

if($id_rdv_report > 0){
	$query = $mysqli->prepare("SELECT id,rdv_lang,columns_list FROM dne_rdv WHERE id = ?");
	$query->bind_param("i",$id_rdv_report);
	$query->execute();
	$query->store_result();
	$rdv = fetch_unique($query);
	$display_rdvs_list = 'display:block'; 
}

$sup_type = 'S';
$des_type = 'D';
$etrp_type = 'E';
$manager_type = 'M';

$query = $mysqli->prepare("SELECT ps.id AS id,s.name_he AS name_he
                          FROM dne_projects_suppliers ps
						  LEFT JOIN dne_projects p ON ps.id_project = p.id
						  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
						  WHERE ps.id_project = ? AND s.type = ?
                          ORDER BY name_he ASC");
$query->bind_param("is",$project_id,$sup_type);
$query->execute();
$query->store_result();
$suppliers = fetch($query);

$query = $mysqli->prepare("SELECT ps.id AS id,s.name_he AS name_he
                          FROM dne_projects_suppliers ps
						  LEFT JOIN dne_projects p ON ps.id_project = p.id
						  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
						  WHERE ps.id_project = ? AND s.type = ?
                          ORDER BY name_he ASC");
$query->bind_param("is",$project->id,$des_type);
$query->execute();
$query->store_result();
$designers = fetch($query);

$query = $mysqli->prepare("SELECT ps.id AS id,s.name_he AS name_he
                          FROM dne_projects_suppliers ps
						  LEFT JOIN dne_projects p ON ps.id_project = p.id
						  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
						  WHERE ps.id_project = ? AND s.type = ?
                          ORDER BY name_he ASC");
$query->bind_param("is",$project_id,$manager_type);
$query->execute();
$query->store_result();
$manager = fetch_unique($query);

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

$display_div_supplier_filter = 'display:none;';

if($id > 0){
  $query = $mysqli->prepare("SELECT * FROM dne_custom_reports WHERE id = ?");
  $query->bind_param("i",$id);
  $query->execute();
  $query->store_result();
  $custom_report = fetch_unique($query);
 
  if(@$custom_report->id_supplier != 0) 
	 $display_div_supplier_filter = 'display:block;'; 
}

$chapters_array = array();
$chapters_ids_array = array();
$query = $mysqli->prepare("SELECT * FROM dne_chapters 
						  WHERE id_project = ?");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$chapters = fetch($query);
foreach(@$chapters as $chapter){
    $chapters_array[@$chapter->id]= @$chapter->name;
	if(!in_array(@$chapter->id,$chapters_ids_array) && @$chapter->id != '')
	  array_push($chapters_ids_array,@$chapter->id);
}

$tasks_array = array();
$tasks_ids_array = array();
$query = $mysqli->prepare("SELECT * FROM dne_tasks 
						  WHERE id_project = ?");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$tasks = fetch($query);
foreach(@$tasks as $task){
	$tasks_array[@$task->id]= @$task->name_he;
	if(@$custom_report->lang == 'EN')
		$tasks_array[@$task->id]= @$task->name;
	if(!in_array(@$task->id,$tasks_ids_array) && @$task->id != '')
	  array_push($tasks_ids_array,@$task->id);
}

$responsibles_array = array();
$responsibles_ids_array = array();
$pass_ons_array = array();
$pass_ons_ids_array = array();
$query = $mysqli->prepare("SELECT * FROM dne_responsibles 
						  WHERE id_project = ?");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$responsibles = fetch($query);
foreach(@$responsibles as $responsible){
    $responsibles_array[@$responsible->id]= @$responsible->name;
	if(!in_array(@$responsible->id,$responsibles_ids_array) && @$responsible->id != '')
	  array_push($responsibles_ids_array,@$responsible->id);
    
	$pass_ons_array[@$responsible->id]= @$responsible->name; 
    if(!in_array(@$responsible->id,$pass_ons_ids_array) && @$responsible->id != '')
	  array_push($pass_ons_ids_array,@$responsible->id);	
}

$progress_status_array = array();
$progress_status_ids_array = array();
$query = $mysqli->prepare("SELECT * FROM dne_progress_status 
						  WHERE id_project = ?");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$progress_status = fetch($query);
foreach(@$progress_status as $ps){
	$progress_status_array[@$ps->id]= @$ps->name_he;
	
	if(@$custom_report->lang == 'EN')
		$progress_status_array[@$ps->id]= @$ps->name;
	
	if(!in_array(@$ps->id,$progress_status_ids_array) && @$ps->id != '')
	  array_push($progress_status_ids_array,@$ps->id);
}

asort($chapters_array);
asort($tasks_array);
asort($responsibles_array);
asort($pass_ons_array);
asort($progress_status_array);

if ($id > 0) {
	if ($is_specific_filter && !empty($id_rdv_report) && !empty(@$rdv->rdv_lang)) {
		$page_lang = $rdv->rdv_lang;
	} elseif (@$custom_report->lang == 'EN' || @$custom_report->lang == 1) {
		$page_lang = 'EN';
	} elseif (@$custom_report->lang == 'HE' || @$custom_report->lang == 0) {
		$page_lang = 'HE';
	} else {
		$page_lang = !empty($project->lang) ? $project->lang : @$_SESSION['lang'];
	}
} else {
	$page_lang = !empty($project->lang) ? $project->lang : @$_SESSION['lang'];
}

if($page_lang == 'EN'){
  $dir = 'dir-ltr';	
  
  $title_label = 'New/update report';
  
  if(@$is_specific_filter == 1)
	 $title_label = 'Special filter'; 
  
  $report_name_label = 'Report name';
  $pdf_name_label = 'PDF name';
  $title1_label = 'Report title';
  $title2_label = 'Title 2';
  $search_label = 'Search';
  $subject_domain_label = 'Subject/Domain';
  $description_label = 'Description';
  $supplier_report_label = 'Supplier Report';
  $include_pass_on_tasks_label = 'Including delivered tasks &#10; to the supplier';
  $chapters_label = 'Chapters';
  $fluide_reunion_label = 'Fluide/Reunion';
  $areas_label = 'Areas';
  $area_subject_label = 'Area/Subject';
  $task_types_label = 'Task types';
  $responsibles_label = 'Responsibles';
  $transfer_confirm_label = 'Transfer to/<br/>Confirm against';
  $transfer_confirm2_label = 'Transfer to/Confirm against';
  $progress_status_label = 'Progress<br/>status';
  $progress_status2_label = 'Progress status';
  $all_label = 'All';
  $reunions_list_label = 'Reunions list';
  $period_new_tasks_label = 'Highlight new tasks in color';
  $period_choice_label = 'Choose a period';
  $not_mark_label = 'Not mark';
  $today_label = 'Today';
  $three_days_label = '3 days';
  $one_week_label = '1 week';
  $two_weeks_label = '2 weeks';
  $one_month_label = '1 month';
  $two_months_label = '2 months';
  $one_year_label = '1 year';
  $two_years_label = '2 years';
  $task_creation_date_label = 'Task creation date';
  $from_label = 'From';
  $to_label = 'to';
  $destination_date_label = 'Destination date';
  $report_design_label = 'Report design';
  $images_label = 'Images';
  $and_label = 'AND';
  $or_label = 'OR';
  $no_label = 'No';
  $yes_label = 'Yes';
  $yes_middle_report_label = 'Yes in middle report';
  $yes_end_report_label = 'Yes in end report';
  $with_colors_label = 'Report with colors';
  $language_label = 'Language';
  $hebrew_label = 'Hebrew';
  $english_label = 'English';
  $fields_choice_label = 'Fields choice';
  $title_save_btn = 'Save';
  $title_search_btn = 'Research';
  $title_cancel_btn = 'Cancel';
  $alert_mandatory_fields = 'Please fill all the mandatory fields.';
  $margin = 'marginLeft';
  $padding = 'paddingLeft';
  $text_align = 'text-align-left';
}

if($page_lang == "" || $page_lang == 'HE'){
  $dir = 'dir-rtl';
  
  $title_label = "יצירת/עדכון דו''ח";
  if(@$is_specific_filter == 1) 
	 $title_label = "סינון מיוחד";  
 
  $report_name_label = "שם הדו''ח";
  $pdf_name_label = 'שם PDF';
  $title1_label = "כותרת הדו''ח";
  $title2_label = 'כותרת 2';
  $search_label = 'חפש';
  $subject_domain_label = 'נושא/תחום';
  $description_label = 'תיאור';
  $supplier_report_label = "דו''ח ספק";
  $include_pass_on_tasks_label = 'כולל משימות שיימסרו לספק';
  $chapters_label = 'פרקים';
  $fluide_reunion_label = 'שותף/ישיבה';
  $areas_label = 'איזורים';
  $area_subject_label = 'איזור/נושא';
  $task_types_label = 'סוגי משימה';
  $responsibles_label = 'אחראיים';
  $transfer_confirm_label = 'להעביר ל/<br/>לאשר מול';
  $transfer_confirm2_label = 'להעביר ל/לאשר מול';
  $progress_status_label = 'סטטוס<br/>התקדמות';
  $progress_status2_label = 'סטטוס התקדמות';
  $all_label = 'הכל';
  $reunions_list_label = 'רשימת ישיבות';
  $period_new_tasks_label = 'להדגיש משימות חדשות בצבע';
  $period_choice_label = 'בחר תקופה';
  $not_mark_label = 'לא להדגיש';
  $today_label = 'היום';
  $three_days_label = '3 ימים';
  $one_week_label = 'שבוע';
  $two_weeks_label = 'שבועיים';
  $one_month_label = 'חודש';
  $two_months_label = 'חודשיים';
  $one_year_label = 'שנה';
  $two_years_label = 'שנתיים';
  $task_creation_date_label = 'תאריך משימה';
  $from_label = 'מתאריך';
  $to_label = 'עד תאריך';
  $destination_date_label = 'תאריך יעד';
  $report_design_label = "עיצוב דו''ח";
  $images_label = 'תמונות';
  $and_label = 'AND';
  $or_label = 'OR';
  $no_label = 'לא';
  $yes_label = 'כן';
  $yes_middle_report_label = "כן באמצע הדו''ח";
  $yes_end_report_label = "כן בסוף הדו''ח";
  $with_colors_label = "דו''ח צבעוני";
  $language_label = 'שפה';
  $hebrew_label = 'עברית';
  $english_label = 'אנגלית';
  $fields_choice_label = 'בחירת שדות';
  $title_save_btn = 'שמור';
  $title_search_btn = 'חפש';
  $title_cancel_btn = 'בטל';
  $alert_mandatory_fields = 'נא למלה את כל השדות החובות.';
  $margin = 'marginRight';
  $padding = 'paddingRight';
  $text_align = 'alignRight';
}

include 'menu_tasks.php';
?>
        <form method="post" action="" class="form-inline">
		    <input type="hidden" id="id" name="id" value="<?=@$id?>" />
		    <input type="hidden" id="project_id" name="project_id" value="<?=@$project->id?>" />
			<input type="hidden" id="id_custom_report" name="id_custom_report" value="<?=@$id_custom_report?>" />
			<input type="hidden" id="id_rdv_report" name="id_rdv_report" value="<?=@$id_rdv_report?>" />
			<input type="hidden" id="id_supplier" name="id_supplier" value="<?=@$custom_report->id_supplier?>" />
			<input type="hidden" id="chapters_ids" name="chapters_ids" value="<?=implode(',',@$chapters_ids_array)?>" />
			<input type="hidden" id="tasks_ids" name="tasks_ids" value="<?=implode(',',@$tasks_ids_array)?>" />
			<input type="hidden" id="responsibles_ids" name="responsibles_ids" value="<?=implode(',',@$responsibles_ids_array)?>" />
			<input type="hidden" id="pass_ons_ids" name="pass_ons_ids" value="<?=implode(',',@$pass_ons_ids_array)?>" />
			<input type="hidden" id="progress_status_ids" name="progress_status_ids" value="<?=implode(',',@$progress_status_ids_array)?>" />
			<input type="hidden" id="task_filter" value="<?=@$task_filter?>" />
			<input type="hidden" id="progress_status_filter" value="<?=@$progress_status_filter?>" />
			<input type="hidden" id="period_late_filter" value="<?=@$period_late_filter?>" />
			<input type="hidden" id="get_supplier_filter" value="<?=@$get_supplier_filter?>" />
			<input type="hidden" id="period_new_task_filter" value="<?=@$period_new_task_filter?>" />
			<input type="hidden" id="search_filter" value="<?=@$search_filter?>" />
			
			<div class="container">
			    <div class="row alignCenter marginTop25">
					<div class="col-12">
						<a href="projects.php"><img src="images/davidnahmias_logo.png" width="170px" height="170px" /></a>
					</div>
			    </div>
			   
			    <div class="row marginTop15 title dir-rtl">	
					<div class="col-12">
						<a id="a_project_title" class="text-decoration-none color-1A5276" href="meetings.php?project_id=<?=@$project_id?>&task_filter=<?=@$task_filter?>&progress_status_filter=<?=@$progress_status_filter?>&supplier_filter=<?=@$supplier_filter?>&period_new_task_filter=<?=@$period_new_task_filter?>">
						    <strong class="fontSize33 line-height-1em"><?=@$project->nickname?></strong>
							<div class="fontSize26 font-family-david">פרוייקט <?=@$project->name_he?></div>
					    </a>		
					</div>
                    <div class="fontSize26 font-weight-bold font-family-david cursor-pointer"><?=@$title_label?></div>					
			    </div>
				
				<?php if(!@$is_specific_filter) { ?>
					<div class="margin-top-20-x-auto flex flex-wrap widthBloc justify-content-center alignCenter">
						<div class="width50Percents">
							<div class="row <?=@$dir?> alignCenter marginTop10 fontSize13">
								<div class="col-12">									
									<span><strong><?=@$report_name_label?></strong></span>										
									<div class="marginTop5">
										<input type="text" class="width150 <?=@$padding?>5" id="request_name" placeholder="<?=@$report_name_label?>" value="<?=htmlspecialchars(@$custom_report->request_name)?>" />
								    </div>
								</div>
							</div>		
					
							<div class="row <?=@$dir?> alignCenter marginTop10 fontSize13">
								<div class="col-12">
									<span><strong><?=@$pdf_name_label?></strong></span>
									<div class="marginTop5">
										<input type="text" class="width150 <?=@$padding?>5" id="pdf_name" placeholder="<?=@$pdf_name_label?>" value="<?=htmlspecialchars(@$custom_report->pdf_name)?>" />
								    </div>
								</div>
							</div>	
					
							<div class="row <?=@$dir?> alignCenter marginTop10 fontSize13">
								<div class="col-12">
									<span><strong><?=@$title1_label?></strong></span>
									<div class="marginTop5">
										<input type="text" class="width150 <?=@$padding?>5" id="title" placeholder="<?=@$title1_label?>" value="<?=htmlspecialchars(@$custom_report->title)?>" />
								    </div>
								</div>
							</div>
										
							<div class="row <?=@$dir?> alignCenter marginTop10 fontSize13">
								<div class="col-12">
									<span><strong><?=@$title2_label?></strong></span>
									<div class="marginTop5">
										<input type="text" class="width150 <?=@$padding?>5" id="subtitle" placeholder="<?=@$title2_label?>" value="<?=htmlspecialchars(@$custom_report->subtitle)?>" />
								    </diV>
								</div>
							</div>
						</div>
						 
						<div class="width50Percents">
							<div class="row <?=@$dir?> marginTop10">
								<div class="col-12">
									<input type="checkbox" id="is_supplier_report" value="1" <?php if(@$custom_report->id_supplier > 0) echo 'checked';?> />&nbsp;
									<input type="button" class="btn btn-primary width80Percents fontSize13 mb-2" value="<?=@$supplier_report_label?>" href="#" />
								</div>
							</div>
							
							<div id="div_supplier_filter" class="row <?=@$dir?>" style="<?=@$display_div_supplier_filter?>">
								<div class="row <?=@$dir?> alignCenter marginTop20 fontSize13">
									<div class="col-6 mx-2">
										<select id="suppliers" class="height35">
											<option value="0">--- משרד/חברה ---</option>
											<optgroup label="חברה">
												<?php foreach ($suppliers as $item){
													$query = $mysqli->prepare("SELECT * FROM dne_responsibles WHERE id_projects_suppliers = ?");
													$query->bind_param("i",$item->id);
													$query->execute();
													$query->store_result();
													
													if($query->num_rows > 0){ ?>				
														<option value="<?=@$item->id?>" <?php if(@$custom_report->id_supplier == @$item->id) echo 'selected';?>><?=@$item->name_he?></option>
												   <?php } 
												} ?>
											</optgroup>
											<optgroup label="משרד">
												<?php foreach ($designers as $item){ 
													$query = $mysqli->prepare("SELECT * FROM dne_responsibles WHERE id_projects_suppliers = ?");
													$query->bind_param("i",$item->id);
													$query->execute();
													$query->store_result();
													
													if($query->num_rows > 0){ ?>
													   <option value="<?=@$item->id?>" <?php if(@$custom_report->id_supplier == @$item->id) echo 'selected';?>><?=@$item->name_he?></option>
													<?php }											
												} ?>
											</optgroup>
											<optgroup label="ניהול">
												<?php 
												$query = $mysqli->prepare("SELECT * FROM dne_responsibles WHERE id_projects_suppliers = ?");
												$query->bind_param("i",$item->id);
												$query->execute();
												$query->store_result();
												
												if($query->num_rows > 0){ ?>
												   <option value="<?=@$manager->id?>" <?php if(@$custom_report->id_supplier == @$item->id) echo 'selected';?>><?=@$manager->name_he?></option>
												<?php } ?>
											</optgroup>
											<optgroup label="יזם">
												<?php 
												$query = $mysqli->prepare("SELECT * FROM dne_responsibles WHERE id_projects_suppliers = ?");
												$query->bind_param("i",$item->id);
												$query->execute();
												$query->store_result();
												
												if($query->num_rows > 0){ ?>
												   <option value="<?=@$entrepreneur->id?>" <?php if(@$custom_report->id_supplier == @$item->id) echo 'selected';?>><?=@$entrepreneur->name_he?></option>
												<?php } ?>
											</optgroup>
										</select>
									</div>
									<div class="col-6 <?=@$text_align?> mx-2" id="responsibles_list"></div>
								</div> 
								
								<div class="row marginTop20 <?=@$dir?> fontSize13">
									<div class="col-12">
										<input type="checkbox" id="is_include_pass_on_tasks" name="is_include_pass_on_tasks" value="1" <?php if(@$custom_report->is_include_pass_on_tasks == 1){ echo 'checked';}?> />&nbsp;
										<input type="button" class="btn btn-primary width80Percents fontSize13 mb-2" value="<?=nl2br(@$include_pass_on_tasks_label)?>" href="#" />
									</div>
								</div>
							</div>	
						</div>
					</div>
				<?php } ?>
				
				<div class="row <?=@$dir?> marginTop40 fontSize12">
					<div class="col-12 alignCenter">
						<table align="center" border="1">							
							<tr class="bgColorSilver height50">
								<th width="16.66%" class="alignCenter"><?=@$chapters_label?></th>
								<th width="16.66%" class="alignCenter"><?=@$task_types_label?></th>
								<th width="16.66%" class="alignCenter"><?=@$responsibles_label?></th>
								<th width="16.66%" class="alignCenter fontSize13"><?=@$transfer_confirm_label?></th>
								<th width="16.66%" class="alignCenter"><?=@$progress_status_label?></th>
							</tr>
							<tr>
								<td class="padding10 vertical-align-top">
									<input type="checkbox" id="all_chapters" <?php if((!$is_specific_filter && @$custom_report->is_all_chapters_checked) || ($is_specific_filter && @$_SESSION['filter_is_all_chapters_checked'] == 1)) echo 'checked'?> />&nbsp;<?=@$all_label?><br/><br/>
									<?php foreach ($chapters_array as $key=>$value){
										 if(!empty($key) && !empty($value)){ ?>
									<input type="checkbox" id="chapters" value="<?=@$key?>" onclick="SetAllChapters();" <?php if(($id == 0 && @$_SESSION['filter_chapters_list'] == 'empty') || ($id == 0 && !@$is_specific_filter) || ($id > 0 && !$is_specific_filter && @$custom_report->is_all_chapters_checked) || ($id > 0 && $is_specific_filter && @$_SESSION['filter_is_all_chapters_checked'] == 1) || ($id > 0 && $is_specific_filter && @$_SESSION['filter_is_all_chapters_checked'] == '' && $custom_report->is_all_chapters_checked) || ($id > 0 && in_array($key,explode(',',@$custom_report->chapters_list)) && !$is_specific_filter) || (@$_SESSION['filter_chapters_list'] == 'empty' && in_array($key,explode(',',@$custom_report->chapters_list))) || ($is_specific_filter && in_array($key,explode(',',@$_SESSION['filter_chapters_list'])))) echo 'checked';?> />&nbsp;<?=$value?><br/>
										 <?php }
									} ?>
								</td>		
								<td class="padding10 vertical-align-top">
									<input type="checkbox" id="all_tasks" />&nbsp;<?=@$all_label?><br/><br/>
									<?php foreach ($tasks_array as $key=>$value){
										 if(!empty($key) && !empty($value)){ ?>
											<input type="checkbox" id="tasks" value="<?=@$key?>" onclick="SetAllTasks();" <?php if(($id == 0 && @$_SESSION['filter_tasks_list'] == 'empty') || ($id == 0 && !@$is_specific_filter) || ($id > 0 && in_array($key,explode(',',@$custom_report->tasks_list)) && !$is_specific_filter) || (@$_SESSION['filter_tasks_list'] == 'empty' && in_array($key,explode(',',@$custom_report->tasks_list))) || ($is_specific_filter && in_array($key,explode(',',@$_SESSION['filter_tasks_list'])))) echo 'checked';?> />&nbsp;<?=$value?><br/>
										 <?php }
									} ?>
								</td>
								<td class="padding10 vertical-align-top">                    
									<input type="checkbox" id="all_responsibles" <?php if((!$is_specific_filter && @$custom_report->is_all_responsibles_checked) || ($is_specific_filter && @$_SESSION['filter_is_all_responsibles_checked'] == 1)) echo 'checked'?> />&nbsp;<?=@$all_label?><br/><br/> 
									<?php foreach ($responsibles_array as $key=>$value){
										 if(!empty($key) && !empty($value)){ ?>
									        <input type="checkbox" id="responsibles" value="<?=@$key?>" onclick="SetAllResponsibles();" <?php if(($id == 0 && @$_SESSION['filter_responsibles_list'] == 'empty') || ($id == 0 && !@$is_specific_filter) || ($id > 0 && !$is_specific_filter && @$custom_report->is_all_responsibles_checked) || ($id > 0 && $is_specific_filter && @$_SESSION['filter_is_all_responsibles_checked'] == 1) || ($id > 0 && $is_specific_filter && @$_SESSION['filter_is_all_responsibles_checked'] == '' && $custom_report->is_all_responsibles_checked) || ($id > 0 && in_array($key,explode(',',@$custom_report->responsibles_list)) && !$is_specific_filter) || (@$_SESSION['filter_responsibles_list'] == 'empty' && in_array($key,explode(',',@$custom_report->responsibles_list))) || ($is_specific_filter && in_array($key,explode(',',@$_SESSION['filter_responsibles_list'])))) echo 'checked';?> />&nbsp;<?=$value?><br/>
										 <?php }
									} ?>
								</td>
								<td class="padding10 vertical-align-top">
								    <div class="dir-ltr alignCenter">
										<input type="radio" id="and_responsibles" name="and_or_responsibles" value="AND" <?php if(($id == 0 && @$_SESSION['filter_and_or_responsibles'] == '') || ($id == 0 && !$is_specific_filter) || (@$_SESSION['filter_and_or_responsibles'] == '' && @$custom_report->and_or_responsibles == 'AND') || ($is_specific_filter && @$_SESSION['filter_and_or_responsibles'] == 'AND') || ($id > 0 && $custom_report->and_or_responsibles == 'AND' && !@$is_specific_filter)) echo "checked";?> />&nbsp; <?=@$and_label?>
										&nbsp;
										<input type="radio" id="or_responsibles" name="and_or_responsibles" value="OR" <?php if((@$_SESSION['filter_and_or_responsibles'] == '' && @$custom_report->and_or_responsibles == 'OR') || ($is_specific_filter && @$_SESSION['filter_and_or_responsibles'] == 'OR') || ($id > 0 && $custom_report->and_or_responsibles == 'OR' && !@$is_specific_filter)) echo "checked";?> />&nbsp; <?=@$or_label?>
									</div>
									<br/>
									<input type="checkbox" id="all_pass_ons" <?php if((!$is_specific_filter && @$custom_report->is_all_pass_ons_checked) || ($is_specific_filter && @$_SESSION['filter_is_all_pass_ons_checked'] == 1)) echo 'checked'?> />&nbsp;<?=@$all_label?><br/><br/>
									<?php foreach ($pass_ons_array as $key=>$value){
										 if(!empty($key) && !empty($value)){ ?>
											<input type="checkbox" id="pass_ons" value="<?=@$key?>" onclick="SetAllPassOns();" <?php if(($id == 0 && @$_SESSION['filter_pass_ons_list'] == 'empty') || ($id == 0 && !@$is_specific_filter) || ($id > 0 && !$is_specific_filter && @$custom_report->is_all_pass_ons_checked) || ($id > 0 && $is_specific_filter && @$_SESSION['filter_is_all_pass_ons_checked'] == 1) || ($id > 0 && $is_specific_filter && @$_SESSION['filter_is_all_pass_ons_checked'] == '' && $custom_report->is_all_pass_ons_checked) || ($id > 0 && in_array($key,explode(',',@$custom_report->pass_ons_list)) && !$is_specific_filter) || (@$_SESSION['filter_pass_ons_list'] == 'empty' && in_array($key,explode(',',@$custom_report->pass_ons_list))) || ($is_specific_filter && in_array($key,explode(',',@$_SESSION['filter_pass_ons_list'])))) echo 'checked';?> />&nbsp;<?=$value?><br/>
										 <?php }
									} ?>
								</td>
								<td class="padding10 vertical-align-top">
									<input type="checkbox" id="all_progress_status" />&nbsp;<?=@$all_label?><br/><br/>
									<?php foreach ($progress_status_array as $key=>$value){
										 if(!empty($key) && !empty($value)){ ?>
											<input type="checkbox" id="progress_status" value="<?=@$key?>" onclick="SetAllProgressStatus();" <?php if(($id == 0 && @$_SESSION['filter_progress_status_list'] == 'empty' && $value != 'ארכיון') || ($id == 0 && !@$is_specific_filter && $value != 'ארכיון') || ($id > 0 && in_array($key,explode(',',@$custom_report->progress_status_list)) && !$is_specific_filter) || (@$_SESSION['filter_progress_status_list'] == 'empty' && in_array($key,explode(',',@$custom_report->progress_status_list))) || ($is_specific_filter && in_array($key,explode(',',@$_SESSION['filter_progress_status_list'])))) echo 'checked';?> />&nbsp;<?=@$value?><br/>
										 <?php }
									} ?>
								</td>
							</tr>
						</table>		
					</div>
				</div>
				
				<div class="margin-top-10-x-auto flex gap-5 widthBloc">
				    <div class="width50Percents height540 <?=@$padding?>20 bgColorGrey border-frame me-2">
					    <div class="row <?=@$dir?> alignCenter my-4">
							<div class="col-12">
								<span class="padding5 borderBlue"><strong><?=@$report_design_label?></strong></span>
							</div>
					    </div>
				
						<div class="row <?=@$dir?> marginTop10">
							<div class="col-12">					
								<strong><?=@$images_label?> ?</strong>
								&nbsp;
								<input type="radio" id="is_images_no" name="is_images" value="0" <?php if((@$_SESSION['filter_is_images'] == '' && @$custom_report->is_images == 0) || ($is_specific_filter && @$_SESSION['filter_is_images'] == 0) || ($id > 0 && @$custom_report->is_images == 0 && !@$is_specific_filter)) echo "checked";?> />&nbsp; <?=@$no_label?>
								&nbsp;
								<input type="radio" id="is_images_yes_middle_report" name="is_images" value="1"  <?php if(($id == 0 && !$is_specific_filter) || (@$_SESSION['filter_is_images'] == '' && @$custom_report->is_images == 1) || ($is_specific_filter && @$_SESSION['filter_is_images'] == 1) || ($id > 0 && $custom_report->is_images == 1 && !@$is_specific_filter)) echo "checked";?> />&nbsp; <?=@$yes_label?>
							</div>
						</div>
						
						<div class="row <?=@$dir?> marginTop10">
							<div class="col-12">					
								<strong><?=@$with_colors_label?> ?</strong>
								&nbsp;
								<input type="radio" id="is_colors_no" name="is_colors" value="0" <?php if((@$_SESSION['filter_is_colors'] == '' && @$custom_report->is_colors == 0) || ($is_specific_filter && @$_SESSION['filter_is_colors'] == 0) || ($id > 0 && @$custom_report->is_colors == 0 && !@$is_specific_filter)) echo "checked";?> />&nbsp; <?=@$no_label?>
								<input type="radio" id="is_colors_yes" name="is_colors" value="1" <?php if(($id == 0 && @$_SESSION['filter_is_colors'] == '') || ($id == 0 && !@$is_specific_filter) || (@$_SESSION['filter_is_colors'] == '' && @$custom_report->is_colors == 1) || ($is_specific_filter && @$_SESSION['filter_is_colors'] == 1) || ($id > 0 && $custom_report->is_colors == 1 && !@$is_specific_filter)) echo "checked";?> />&nbsp; <?=@$yes_label?>
							</div>
						</div>	

						<div class="row <?=@$dir?> marginTop10">
							<div class="col-12">					
								<strong><?=@$language_label?> ?</strong>
								&nbsp;
								<input type="radio" id="lang_he" name="lang" value="HE" <?php if($page_lang != 'EN') echo "checked";?> />&nbsp; <?=@$hebrew_label?>
								&nbsp;
								<input type="radio" id="lang_en" name="lang" value="EN" <?php if($page_lang == 'EN') echo "checked";?> />&nbsp; <?=@$english_label?>
							</div>
						</div>
						
						<div class="row <?=@$dir?> marginTop10">
							<div class="col-12">					
								<strong><?=@$period_new_tasks_label?> ?</strong>
							</div>
						</div>
						
						<div class="row <?=@$dir?> my-3">
							<div class="col-12">
								<select id="period_new_tasks" class="width120 height26">							
									<option value="0" <?php if((@$is_specific_filter && @$_SESSION['filter_period_new_tasks'] == '' && @$custom_report->period_new_tasks == 0) || (@$is_specific_filter && @$_SESSION['filter_period_new_tasks'] == 0)) echo 'selected'?>><?=@$not_mark_label?></option>
								    <option value="today" <?php if((@$is_specific_filter && @$_SESSION['filter_period_new_tasks'] == '' && @$custom_report->period_new_tasks == 'today') || (@$_SESSION['filter_period_new_tasks'] == 'empty' && @$custom_report->period_new_tasks == 'today') || ($id > 0 && @$custom_report->period_new_tasks == 'today') || ($id > 0 && @$_SESSION['filter_period_new_tasks'] == 'today')) echo 'selected'?>><?=@$today_label?></option>
								    <option value="three_days" <?php if((@$is_specific_filter && @$_SESSION['filter_period_new_tasks'] == '' && @$custom_report->period_new_tasks == 'three_days') || (@$_SESSION['filter_period_new_tasks'] == 'empty' && @$custom_report->period_new_tasks == 'three_days') || ($id > 0 && @$custom_report->period_new_tasks == 'three_days') || ($id > 0 && @$custom_report->period_new_tasks == 'three_days' && @$is_specific_filter) || ($id > 0 && @$_SESSION['filter_period_new_tasks'] == 'three_days')) echo 'selected'?>><?=@$three_days_label?></option>
								    <option value="one_week" <?php if((@$is_specific_filter && @$_SESSION['filter_period_new_tasks'] == '' && @$custom_report->period_new_tasks == 'one_week') || (@$_SESSION['filter_period_new_tasks'] == 'empty' && @$custom_report->period_new_tasks == 'one_week') || ($id > 0 && @$custom_report->period_new_tasks == 'one_week' && !@$is_specific_filter) || ($id > 0 && @$custom_report->period_new_tasks == 'one_week') || ($id > 0 && @$_SESSION['filter_period_new_tasks'] == 'one_week')) echo 'selected'?>><?=@$one_week_label?></option>
								    <option value="two_weeks" <?php if((@$is_specific_filter && @$_SESSION['filter_period_new_tasks'] == '' && @$custom_report->period_new_tasks == 'two_weeks') || (@$_SESSION['filter_period_new_tasks'] == 'empty' && @$custom_report->period_new_tasks == 'two_weeks') || ($id > 0 && @$custom_report->period_new_tasks == 'two_weeks' && !@$is_specific_filter) || ($id > 0 && @$custom_report->period_new_tasks == 'two_weeks') || ($id > 0 && @$custom_report->period_new_tasks == 'two_weeks')) echo 'selected'?>><?=@$two_weeks_label?></option>
								    <option value="one_month" <?php if((@$is_specific_filter && @$_SESSION['filter_period_new_tasks'] == '' && @$custom_report->period_new_tasks == 'one_month') || (@$_SESSION['filter_period_new_tasks'] == 'empty' && @$custom_report->period_new_tasks == 'one_month') || ($id > 0 && @$custom_report->period_new_tasks == 'one_month' && !@$is_specific_filter) || ($id > 0 && @$custom_report->period_new_tasks == 'one_month')) echo 'selected'?>><?=@$one_month_label?></option>
								    <option value="two_months" <?php if((@$is_specific_filter && @$_SESSION['filter_period_new_tasks'] == '' && @$custom_report->period_new_tasks == 'two_months') || (@$_SESSION['filter_period_new_tasks'] == 'empty' && @$custom_report->period_new_tasks == 'two_months') || ($id > 0 && @$custom_report->period_new_tasks == 'two_months' && !@$is_specific_filter) || ($id > 0 && @$custom_report->period_new_tasks == 'two_months')) echo 'selected'?>><?=@$two_months_label?></option>
								    <option value="one_year" <?php if((@$is_specific_filter && @$_SESSION['filter_period_new_tasks'] == '' && @$custom_report->period_new_tasks == 'one_year') || (@$_SESSION['filter_period_new_tasks'] == 'empty' && @$custom_report->period_new_tasks == 'one_year') || ($id > 0 && @$custom_report->period_new_tasks == 'one_year' && !@$is_specific_filter) || ($id > 0 && @$custom_report->period_new_tasks == 'one_year')) echo 'selected'?>><?=@$one_year_label?></option>
								    <option value="two_years" <?php if((@$is_specific_filter && @$_SESSION['filter_period_new_tasks'] == '' && @$custom_report->period_new_tasks == 'two_years') || (@$_SESSION['filter_period_new_tasks'] == 'empty' && @$custom_report->period_new_tasks == 'two_years') || ($id > 0 && @$custom_report->period_new_tasks == 'two_years' && !@$is_specific_filter) || ($id > 0 && @$custom_report->period_new_tasks == 'two_years')) echo 'selected'?>><?=@$two_years_label?></option>
								</select>	
							</div>
						</div>

						<div class="row <?=@$dir?> alignCenter my-4">
							<div class="col-12">
								<span class="padding5 borderBlue"><strong><?=@$fields_choice_label?></strong></span>
							</div>
						</div>	

						<div class="row <?=@$dir?> my-3">
							<div class="col-12">               
								<input id="columns_list" type="checkbox" value="subject" <?php if(($id == 0 && !@$is_specific_filter) || ($id > 0 && @$id_custom_report && strpos($custom_report->columns_list,'subject')!== false && !$is_specific_filter) || (@$id_rdv_report && strpos(@$rdv->columns_list,'subject')!== false) || (@$_SESSION['filter_columns_list'] == '' && strpos(@$custom_report->columns_list,'subject')!== false) || ($is_specific_filter && strpos(@$_SESSION['filter_columns_list'],'subject')!== false)) echo "checked";?> />&nbsp; <?=@$subject_domain_label?>
								<br/>
								<input id="columns_list" type="checkbox" value="area" <?php if(($id == 0 && !@$is_specific_filter) || ($id > 0 && strpos($custom_report->columns_list,'area')!== false && !$is_specific_filter) || (@$id_rdv_report && strpos(@$rdv->columns_list,'area')!== false) || (@$_SESSION['filter_columns_list'] == '' && strpos(@$custom_report->columns_list,'area')!== false) || ($is_specific_filter && strpos(@$_SESSION['filter_columns_list'],'area')!== false)) echo "checked";?> />&nbsp; <?=@$area_subject_label?>
								<br/>
								<input id="columns_list" type="checkbox" value="description" <?php if(($id == 0 && !@$is_specific_filter) || ($id > 0 && strpos($custom_report->columns_list,'description')!== false && !$is_specific_filter) || (@$id_rdv_report && strpos(@$rdv->columns_list,'description')!== false) || (@$_SESSION['filter_columns_list'] == '' && strpos(@$custom_report->columns_list,'description')!== false) || ($is_specific_filter && strpos(@$_SESSION['filter_columns_list'],'description')!== false)) echo "checked";?> />&nbsp; <?=@$description_label?>
								<br/>
								<input id="columns_list" type="checkbox" value="_task" <?php if(($id == 0 && !@$is_specific_filter) || ($id > 0 && strpos($custom_report->columns_list,'_task')!== false && !$is_specific_filter) || (@$id_rdv_report && strpos(@$rdv->columns_list,'_task')!== false) || (@$_SESSION['filter_columns_list'] == '' && strpos(@$custom_report->columns_list,'_task')!== false) || ($is_specific_filter && strpos(@$_SESSION['filter_columns_list'],'_task')!== false)) echo "checked";?> />&nbsp; <?=@$task_types_label?>
								<br/>
								<input id="columns_list" type="checkbox" value="responsible" <?php if(($id == 0 && !@$is_specific_filter) || ($id > 0 && strpos($custom_report->columns_list,'responsible')!== false && !$is_specific_filter) || (@$id_rdv_report && strpos(@$rdv->columns_list,'responsible')!== false) || (@$_SESSION['filter_columns_list'] == '' && strpos(@$custom_report->columns_list,'responsible')!== false) || ($is_specific_filter && strpos(@$_SESSION['filter_columns_list'],'responsible')!== false)) echo "checked";?> />&nbsp; <?=@$responsibles_label?>
								<br/>
								<input id="columns_list" type="checkbox" value="pass on" <?php if(($id == 0 && !@$is_specific_filter) || ($id > 0 && strpos($custom_report->columns_list,'pass on')!== false && !$is_specific_filter) || (@$id_rdv_report && strpos(@$rdv->columns_list,'pass on')!== false) || (@$_SESSION['filter_columns_list'] == '' && strpos(@$custom_report->columns_list,'pass on')!== false) || ($is_specific_filter && strpos(@$_SESSION['filter_columns_list'],'pass on')!== false)) echo "checked";?> />&nbsp; <?=@$transfer_confirm2_label?>
								<br/>
								<input id="columns_list" type="checkbox" value="task creation" <?php if(($id == 0 && !@$is_specific_filter) || ($id > 0 && strpos($custom_report->columns_list,'task creation')!== false && !$is_specific_filter) || (@$id_rdv_report && strpos(@$rdv->columns_list,'task creation')!== false) || (@$_SESSION['filter_columns_list'] == '' && strpos(@$custom_report->columns_list,'task creation')!== false) || ($is_specific_filter && strpos(@$_SESSION['filter_columns_list'],'task creation')!== false)) echo "checked";?> />&nbsp; <?=@$task_creation_date_label?>
								<br/>
								<input id="columns_list" type="checkbox" value="destination date" <?php if(($id == 0 && !@$is_specific_filter) || ($id > 0 && strpos($custom_report->columns_list,'destination date')!== false && !$is_specific_filter) || (@$id_rdv_report && strpos(@$rdv->columns_list,'destination date')!== false) || (@$_SESSION['filter_columns_list'] == '' && strpos(@$custom_report->columns_list,'destination date')!== false) || ($is_specific_filter && strpos(@$_SESSION['filter_columns_list'],'destination date')!== false)) echo "checked";?> />&nbsp; <?=@$destination_date_label?>
								<br/>
								<input id="columns_list" type="checkbox" value="progress status" <?php if(($id == 0 && !@$is_specific_filter) || ($id > 0 && strpos($custom_report->columns_list,'progress status')!== false && !$is_specific_filter) || (@$id_rdv_report && strpos(@$rdv->columns_list,'progress status')!== false) || (@$_SESSION['filter_columns_list'] == '' && strpos(@$custom_report->columns_list,'progress status')!== false) || ($is_specific_filter && strpos(@$_SESSION['filter_columns_list'],'progress status')!== false)) echo "checked";?> />&nbsp; <?=@$progress_status2_label?>
							</div>
						</div>		
					</div>
					
					<div class="width50Percents">
					    <div class="row">
							<div class="col-12 height540 bgColor-cbddec border-frame">
							   <div class="row">
									<div class="col-6">  
										<div class="row <?=@$dir?> alignCenter fontSize13 my-4">
											<div class="col-12">
												<div class="row">
													<div class="col-12">
														<span class="padding5 borderBlue"><strong><?=@$task_creation_date_label?></strong></span>
													</div>
												</div>
												<div class="row <?=$dir?> marginTop20 fontSize13">
													<div class="col-12">
														<select id="period_creation_date" class="width100 height26" onchange="setPeriodFilter('creation_date',this.value);">
															<option value="0"><?=@$period_choice_label?></option>										
															<option value="three_days" <?php if((@$is_specific_filter && @$_SESSION['filter_period_creation_date'] == '' && @$custom_report->period_creation_date == 'three_days') || (@$is_specific_filter && @$_SESSION['filter_period_creation_date'] == 'three_days')) echo 'selected'?>><?=@$three_days_label?></option>
															<option value="one_week" <?php if((@$is_specific_filter && @$_SESSION['filter_period_creation_date'] == '' && @$custom_report->period_creation_date == 'one_week') || (@$is_specific_filter && @$_SESSION['filter_period_creation_date'] == 'one_week')) echo 'selected'?>><?=@$one_week_label?></option>
															<option value="two_weeks" <?php if((@$is_specific_filter && @$_SESSION['filter_period_creation_date'] == '' && @$custom_report->period_creation_date == 'two_weeks') || (@$is_specific_filter && @$_SESSION['filter_period_creation_date'] == 'two_weeks')) echo 'selected'?>><?=@$two_weeks_label?></option>
															<option value="one_month" <?php if((@$is_specific_filter && @$_SESSION['filter_period_creation_date'] == '' && @$custom_report->period_creation_date == 'one_month') || (@$is_specific_filter &&@$_SESSION['filter_period_creation_date'] == 'one_month')) echo 'selected'?>><?=@$one_month_label?></option>	
															<option value="two_months" <?php if((@$is_specific_filter && @$_SESSION['filter_period_creation_date'] == '' && @$custom_report->period_creation_date == 'two_months') || (@$is_specific_filter &&@$_SESSION['filter_period_creation_date'] == 'two_months')) echo 'selected'?>><?=@$two_months_label?></option>
															<option value="one_year" <?php if((@$is_specific_filter && @$_SESSION['filter_period_creation_date'] == '' && @$custom_report->period_creation_date == 'one_year') || (@$is_specific_filter && @$_SESSION['filter_period_creation_date'] == 'one_year')) echo 'selected'?>><?=@$one_year_label?></option>
															<option value="two_years" <?php if((@$is_specific_filter && @$_SESSION['filter_period_creation_date'] == '' && @$custom_report->period_creation_date == 'two_years') || (@$is_specific_filter && @$_SESSION['filter_period_creation_date'] == 'two_years')) echo 'selected'?>><?=@$two_years_label?></option>
														</select>	
													</div>
												</div>
												<div class="row marginTop15">
													<div class="col-12">
													   <strong><?=@$from_label?></strong>
													   <div class="marginTop5">
														   <input type="date" id="creation_date_start" class="paddingLeft10 width100" value="<?php if(@$is_specific_filter && @$_SESSION['filter_creation_date_start'] == '') echo @$custom_report->creation_date_start;else if(@$is_specific_filter && @$_SESSION['filter_creation_date_start'] != '') echo @$_SESSION['filter_creation_date_start']?>" />
													   </div>
													</div>
												</div>
												<div class="row marginTop10" class="row">
													<div class="col-12">
														<strong><?=@$to_label?></strong>
														<div class="marginTop5">
															<input type="date" id="creation_date_end" class="paddingLeft10 width100" value="<?php if(@$is_specific_filter && @$_SESSION['filter_creation_date_end'] == '') echo @$custom_report->creation_date_end;else if(@$is_specific_filter && @$_SESSION['filter_creation_date_end'] != '') echo @$_SESSION['filter_creation_date_end']?>" />
														</div>
													</div>
												</div>
											</div>
										</div>
								
										<div class="row <?=@$dir?> alignCenter marginTop30 fontSize13">
											<div class="col-12">
												<div class="row">
													<div class="col-12">
													   <span class="padding5 borderBlue"><strong><?=@$destination_date_label?></strong></span>
													</div>
												</div>
												<div class="row <?=$dir?> marginTop20 fontSize13">
													<div class="col-12">
														<select id="period_destination_date" class="width100 height26" onchange="setPeriodFilter('destination_date',this.value);">
															<option value="0"><?=@$period_choice_label?></option>										
															<option value="three_days" <?php if((@$_SESSION['filter_period_destination_date'] == '' && @$custom_report->period_destination_date == 'three_days') || (@$_SESSION['filter_period_destination_date'] == 'three_days')) echo 'selected'?>><?=@$three_days_label?></option>
															<option value="one_week" <?php if((@$_SESSION['filter_period_destination_date'] == '' && @$custom_report->period_destination_date == 'one_week') || (@$_SESSION['filter_period_destination_date'] == 'one_week')) echo 'selected'?>><?=@$one_week_label?></option>
															<option value="two_weeks" <?php if((@$_SESSION['filter_period_destination_date'] == '' && @$custom_report->period_destination_date == 'two_weeks') || (@$_SESSION['filter_period_destination_date'] == 'two_weeks')) echo 'selected'?>><?=@$two_weeks_label?></option>
															<option value="one_month" <?php if((@$_SESSION['filter_period_destination_date'] == '' && @$custom_report->period_destination_date == 'one_month') || (@$_SESSION['filter_period_destination_date'] == 'one_month')) echo 'selected'?>><?=@$one_month_label?></option>	
															<option value="two_months" <?php if((@$_SESSION['filter_period_destination_date'] == '' && @$custom_report->period_destination_date == 'two_months') || (@$_SESSION['filter_period_destination_date'] == 'two_months')) echo 'selected'?>><?=@$two_months_label?></option>
															<option value="one_year"  <?php if((@$_SESSION['filter_period_destination_date'] == '' && @$custom_report->period_destination_date == 'one_year') || (@$_SESSION['filter_period_destination_date'] == 'one_year')) echo 'selected'?>><?=@$one_year_label?></option>
															<option value="two_years" <?php if((@$_SESSION['filter_period_destination_date'] == '' && @$custom_report->period_destination_date == 'two_years') || (@$_SESSION['filter_period_destination_date'] == 'two_years')) echo 'selected'?>><?=@$two_years_label?></option>
														</select>	
													</div>
												</div>
												<div class="row marginTop15">
													<div class="col-12">
														<strong><?=@$from_label?></strong>
														<div class="marginTop5">
															<input type="date" id="destination_date_start" class="paddingLeft10 width100" value="<?php if(@$_SESSION['filter_destination_date_start'] == '') echo @$custom_report->destination_date_start;else echo @$_SESSION['filter_destination_date_start']?>" />
														</div>									
													</div>
												</div>
												<div class="row marginTop10">
													<div class="col-12">   
														<strong><?=@$to_label?></strong>
														<div class="marginTop5">
														   <input type="date" id="destination_date_end" class="paddingLeft10 width100" value="<?php if(@$_SESSION['filter_destination_date_end'] == '') echo @$custom_report->destination_date_end;else echo @$_SESSION['filter_destination_date_end']?>" />
														</div>
													</div>
												</div>
											</div>
										</div>	
									</div>
									<div class="col-6">
										<div class="row <?=@$dir?> alignCenter my-4">
											<div class="col-12">
												<span class="padding5 borderBlue"><strong><?=@$search_label?></strong><i class="fa-sharp fa-solid fa-magnifying-glass <?=@$margin?>10"></i></span>
											</div>
										</div>
						
										<div class="row <?=@$dir?> alignCenter marginTop10 fontSize13">
											<div class="col-12">
												<span><strong><?=@$subject_domain_label?></strong></span>
											</div>
										</div>
								
										<div class="row <?=@$dir?> alignCenter marginTop10 fontSize13">
											<div class="col-12">
												<input type="text" class="width120 <?=@$padding?>5" id="subject" placeholder="<?=@$subject_domain_label?>" value="<?php if(@$_SESSION['filter_subject'] == '') echo @$custom_report->subject;else echo @$_SESSION['filter_subject']?>" />
											</div>
										</div>
										
										<div class="row <?=@$dir?> alignCenter marginTop10 fontSize13">
											<div class="col-12">
												<span><strong><?=@$area_subject_label?></strong></span>
											</div>
										</div>
								
										<div class="row <?=@$dir?> alignCenter marginTop10 fontSize13">
											<div class="col-12">
												<input type="text" class="width120 <?=@$padding?>5" id="area" placeholder="<?=@$area_subject_label?>" value="<?php if(@$_SESSION['filter_area'] == '') echo @$custom_report->area;else echo @$_SESSION['filter_area']?>" />
											</div>
										</div>
										
										<div class="row <?=@$dir?> alignCenter marginTop10 fontSize13">
											<div class="col-12">
												<span><strong><?=@$description_label?></strong></span>
											</div>
										</div>
						
										<div class="row <?=@$dir?> alignCenter marginTop10 fontSize13">
											<div class="col-12">
												<input type="text" class="width120 <?=@$padding?>5" id="description" placeholder="<?=@$description_label?>" value="<?php if(@$_SESSION['filter_description'] == '') echo @$custom_report->description;else echo @$_SESSION['filter_description']?>" />
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>  
					</div>	
				</div>
				
				<div class="row marginTop10 <?=@$dir?> alignCenter fontSize13">
					<div class="col-12">
					    <?php if(@$is_specific_filter != 1) { ?>
							<div id="div_message_alert_down"></div>
							<button type="button" id="save_btn" class="btn bgColorBlue colorWhite <?=@$margin?>10 mb-2"><?=@$title_save_btn?></button>
						<?php } 
						    else { ?>
								<button type="button" id="send_btn" class="btn bgColorBlue colorWhite <?=@$margin?>10 mb-2" onclick="toResultSpecialFilters(<?=@$id?>);"><?=@$title_search_btn?><i class="fa-sharp fa-solid fa-magnifying-glass <?=@$margin?>10"></i></button>
						<?php } ?>
						
						<button type="button" id="cancel_btn" class="btn bg-black colorWhite <?=@$margin?>10 mb-2" onclick="location.href='meetings.php?project_id=<?=@$project_id?>'"><?=@$title_cancel_btn?></button>
					</div>
				</div> 
			</div>
		</form>

        <div id="progress-popup">
            <p>Loading in progress...</p>
            <div id="div-progress-bar">
                <div id="progress-bar"></div>
            </div>
        </div>		
	</body>
</html>

<script>
$(document).ready(function() {
  function filterPassOnsToSupplier() {
	  $('#is_include_pass_on_tasks').prop('checked', true);
	  $('#or_responsibles').prop('checked', true);
	  $('input[id="pass_ons"]').prop('checked', false);
	  $('input[id="all_pass_ons"]').prop('checked', false);
	  let ids = ($('#responsibles_ids_hidden').val() || '').split(',').filter(Boolean);
	  ids.forEach(function(id) {
		  $('input[id="pass_ons"][value="' + id + '"]').prop('checked', true);
	  });
  }

  $('#is_supplier_report').on('change', function() {
    $('#div_supplier_filter').toggle();
	$("input[name=and_or_responsibles][value='OR']").prop('checked',true);
	if ($(this).is(':checked')) {
	  if ($('#suppliers').val() != 0)
		filterPassOnsToSupplier();
	  else
		$('#is_include_pass_on_tasks').prop('checked', true);
	} else {
	  $('#is_include_pass_on_tasks').prop('checked', false);
	  $('input[id="pass_ons"]').prop('checked', true);
	  $('input[id="all_pass_ons"]').prop('checked', true);
	}
  });

  $('#suppliers').on('change', function() {
	  let form_data = new FormData();
	  form_data.append('id_projects_suppliers',$('#suppliers').val());

	  $.ajax({
		type: 'POST',
		url: 'getSupplierDetails.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,
		success: function(data) {
			let supplier_data = data.split('_');
			$('#request_name').val(supplier_data[0]);
			$('#pdf_name').val(supplier_data[0]);
			$('#title').val("כלל המשימות");
			$('#subtitle').val(supplier_data[1]);
		},
	  });
  })

  if($('#id_supplier').val() > 0) {
	    $.post('populate_responsibles_list.php',
	        {id_projects_suppliers:$('#id_supplier').val(),all_responsibles_list:$('#responsibles_ids').val()},
			function(data){
		      $('#responsibles_list').html(data);
	        }
        );
  }

  $('#is_include_pass_on_tasks').change(function() {
    if ($(this).is(':checked')) {
	  $('#or_responsibles').prop('checked',true);
	  $('input[id="pass_ons"]').prop('checked', false);
	  $('input[id="all_pass_ons"]').prop('checked', false);

	  $('#responsibles:checked').each(function(i){
	      $('input[id="pass_ons"][value="' + $(this).val() + '"]').prop('checked', true);
	  });
	}
	else {
	  $('input[id="pass_ons"]').prop('checked', true);
	  $('input[id="all_pass_ons"]').prop('checked', true);
	}
  });

  $('#suppliers').on('change', function (){
	  if($(this).val() != 0) {
		  $.post('populate_responsibles_list.php',{id_projects_suppliers:$(this).val(),all_responsibles_list:$('#responsibles_ids').val()},function(data){
			  $('#responsibles_list').html(data);
			  if ($('#is_supplier_report').is(':checked'))
				  filterPassOnsToSupplier();
		  });
	  }
	  else {
		 $('#is_include_pass_on_tasks').prop('checked',false);
		 $('input[id="pass_ons"]').prop('checked', true);
		 $('input[id="all_pass_ons"]').prop('checked', true);
	  }
  });
  
  let count = 0;
  $('input:checkbox[id="tasks"]').each(function () {
	  if($(this).is(':checked'))
		 count++;
  });
	
  if(count == $('#tasks_ids').val().split(",").length)
	   $('#all_tasks').prop('checked',true);
	
	setAllChecked('chapters');
	setAllChecked('responsibles');
	setAllChecked('pass_ons');
	setAllChecked('progress_status');
	  
	function setAllChecked(field){
	  let count = 0;
	  $('input:checkbox[id^="'+field+'"]').each(function () {
		   if($(this).is(':checked'))
			  count++;
	  });
	  
	  if(count == $('#'+field+'_ids').val().split(",").length)
		  $('#all_'+field).prop('checked',true);
	}
});

$('#all_chapters').click(function() {
    if($(this).is(':checked')) {
       $('input:checkbox[id="chapters"]').each(function () {
          $(this).prop("checked",true);
       });
	}
    else {
        $('input:checkbox[id="chapters"]').each(function () {
          $(this).prop("checked",false);
        });
	}
});

function SetAllChapters() {
	let allChecked = true;
	$('input:checkbox[id="chapters"]').each(function () {
          if(!$(this).is(":checked"))
			allChecked = false;
    });
	
	if(allChecked) 
		$('#all_chapters').prop("checked",true);
	else 
		$('#all_chapters').prop("checked",false);
}

$('#all_tasks_types').click(function() {
	$('#div_rdvs_list').toggle();
	
    if($(this).is(':checked')) {
       $('input:checkbox[id^="tasks_types"]').each(function () {
          $(this).prop("checked",true);
       });
	}
    else {
        $('input:checkbox[id^="tasks_types"]').each(function () {
          $(this).prop("checked",false);
        });
	}
});

function SetAllTasksTypes(id) {
	if($('#tasks_types_2').is(":checked")) 
		$('#div_rdvs_list').css('display','block');
	else
	    $('#div_rdvs_list').css('display','none');
  
	let allChecked = true;
	$('input:checkbox[id^="tasks_types"]').each(function () {
          if(!$(this).is(":checked"))
			allChecked = false;
    });
	
	if(allChecked) 
		$('#all_tasks_types').prop("checked",true);
	else 
		$('#all_tasks_types').prop("checked",false);
}

$('#all_tasks').click(function() {
    if($(this).is(':checked')) {
       $('input:checkbox[id="tasks"]').each(function () {
          $(this).prop("checked",true);
       });
	}
    else {
        $('input:checkbox[id="tasks"]').each(function () {
          $(this).prop("checked",false);
        });
	}
});

function SetAllTasks() {
	let allChecked = true;
	$('input:checkbox[id="tasks"]').each(function () {
          if(!$(this).is(":checked"))
			allChecked = false;
    });
	
	if(allChecked) 
		$('#all_tasks').prop("checked",true);
	else 
		$('#all_tasks').prop("checked",false);
}

$('#all_responsibles').click(function() {
    if($(this).is(':checked')) {
       $('input:checkbox[id="responsibles"]').each(function () {
          $(this).prop("checked",true);
       });
	}
    else {
        $('input:checkbox[id="responsibles"]').each(function () {
          $(this).prop("checked",false);
        });
	}
});

function SetAllResponsibles() {
	let allChecked = true;
	$('input:checkbox[id="responsibles"]').each(function () {
          if(!$(this).is(":checked"))
			allChecked = false;
    });
	
	if(allChecked) 
		$('#all_responsibles').prop("checked",true);
	else 
		$('#all_responsibles').prop("checked",false);
}

$('#all_pass_ons').click(function() {
    if($(this).is(':checked')) {
       $('input:checkbox[id="pass_ons"]').each(function () {
          $(this).prop("checked",true);
       });
	}
    else {
        $('input:checkbox[id="pass_ons"]').each(function () {
          $(this).prop("checked",false);
       });
	}
});

function SetAllPassOns() {
	let allChecked = true;
	$('input:checkbox[id="pass_ons"]').each(function () {
          if(!$(this).is(":checked"))
			allChecked = false;
    });
	
	if(allChecked) 
		$('#all_pass_ons').prop("checked",true);
	else 
		$('#all_pass_ons').prop("checked",false);
}

$('#all_progress_status').click(function() {
    if($(this).is(':checked')) {
       $('input:checkbox[id="progress_status"]').each(function () {
          $(this).prop("checked",true);
       });
	}
    else {
        $('input:checkbox[id="progress_status"]').each(function () {
          $(this).prop("checked",false);
       });
	}
});

function SetAllProgressStatus() {
	let allChecked = true;
	$('input:checkbox[id="progress_status"]').each(function () {
          if(!$(this).is(":checked"))
			allChecked = false;
    });
	
	if(allChecked) 
		$('#all_progress_status').prop("checked",true);
	else 
		$('#all_progress_status').prop("checked",false);
}

function setPeriodFilter(field,period) {
	if(field == 'creation_date') {
		if(period != 0) {
			let startDate = new Date();
			if(period == 'three_days') 
			   startDate.setDate(startDate.getDate() - 3);
			if(period == 'one_week') 
			   startDate.setDate(startDate.getDate() - 7);
			if(period == 'two_weeks') 
			   startDate.setDate(startDate.getDate() - 14);
			if(period == 'one_month') 
			   startDate.setMonth(startDate.getMonth() - 1);
			if(period == 'two_months') 
			   startDate.setMonth(startDate.getMonth() - 2);
			if(period == 'one_year') 
			   startDate.setFullYear(startDate.getFullYear() - 1);
			if(period == 'two_years') 
			   startDate.setFullYear(startDate.getFullYear() - 2);	
			let formatedStartDate = formatDate(startDate);  			
			$('#creation_date_start').val(formatedStartDate);	

            let endDate = new Date();
            let formatedEndDate = formatDate(endDate);  	
            $('#creation_date_end').val(formatedEndDate);			
		}
		else {
			$('#creation_date_start').val('');
			$('#creation_date_end').val('');
		}
	}
	
	if(field == 'destination_date') {
		if(period != 0) {
			let startDate = new Date();
			let formatedStartDate = formatDate(startDate);       
			$('#destination_date_start').val(formatedStartDate);
			
			let endDate = new Date();
			if(period == 'three_days') 
			   endDate.setDate(startDate.getDate() + 3);
			if(period == 'one_week') 
			   endDate.setDate(startDate.getDate() + 7);
			if(period == 'two_weeks') 
			   endDate.setDate(startDate.getDate() + 14);
			if(period == 'one_month') 
			   endDate.setMonth(startDate.getMonth() + 1);
			if(period == 'two_months') 
			   endDate.setMonth(startDate.getMonth() + 2);
			if(period == 'one_year') 
			   endDate.setFullYear(startDate.getFullYear() + 1);
			if(period == 'two_years') 
			   endDate.setFullYear(startDate.getFullYear() + 2);	
			let formatedEndDate = formatDate(endDate);  			
			$('#destination_date_end').val(formatedEndDate);	  		
		}
		else {
			$('#destination_date_start').val('');
			$('#destination_date_end').val('');
		}
	}
}

$('#save_btn').click (function (e) {
	let subject = $('#subject').val();
	let area = $('#area').val();
	let description = $('#description').val();
	
	let all_chapters_checked = 0;
	if($('#all_chapters').prop("checked") == true)
        all_chapters_checked = 1;
	
    let chapters = '';
	$('#chapters:checked').each(function(i){
	  chapters+= $(this).val()+',';
	});
	chapters = chapters.substring(0,chapters.length - 1);
	
	let tasks = '';
	$('#tasks:checked').each(function(i){
	  tasks+= $(this).val()+',';
	});
	tasks = tasks.substring(0,tasks.length - 1);
	
	let all_responsibles_checked = 0;
	if($('#all_responsibles').prop("checked") == true)
        all_responsibles_checked = 1;
	
	let responsibles = '';
	$('#responsibles:checked').each(function(i){
	  responsibles+= $(this).val()+',';
	});
	responsibles = responsibles.substring(0,responsibles.length - 1);
	
	let all_pass_ons_checked = 0;
	if($('#all_pass_ons').prop("checked") == true)
        all_pass_ons_checked = 1;
	
	let pass_ons = '';
	$('#pass_ons:checked').each(function(i){
	  pass_ons+= $(this).val()+',';
	});
	pass_ons = pass_ons.substring(0,pass_ons.length - 1);
	
	let progress_status = '';
	$('#progress_status:checked').each(function(i){
	  progress_status+= $(this).val()+',';
	});
	progress_status = progress_status.substring(0,progress_status.length - 1);
	
	let creation_date_start = $('#creation_date_start').val();
	let creation_date_end = $('#creation_date_end').val();
	
	let destination_date_start = $('#destination_date_start').val();
	let destination_date_end = $('#destination_date_end').val();
	
	let sql = 'SELECT c.name AS name,m.id AS id,m.is_priority AS is_priority,m.id_user AS id_user,m.id_task_type AS id_task_type,m.id_chapter AS id_chapter,m.subject AS subject,m.ids_rdv AS ids_rdv,m.area,m.description,m.id_task,m.id_responsible,m.id_pass_on,m.task_creation_date,m.destination_date,m.id_progress_status,m.updated_date AS updated_date,m.image1 AS image1,m.is_appears_img1 AS is_appears_img1,m.image1_width AS image1_width,m.image1_height AS image1_height,m.image2 AS image2,m.is_appears_img2 AS is_appears_img2,m.image2_width AS image2_width,m.image2_height AS image2_height,m.is_change_row_style AS is_change_row_style,m.id_track_responsible AS id_track_responsible,m.track_type AS track_type,m.reminder_time AS reminder_time,m.reminder_date AS reminder_date,m.is_agrees AS is_agrees,m.is_reminds AS is_reminds FROM dne_meetings m LEFT JOIN dne_chapters c ON m.id_chapter = c.id LEFT JOIN dne_tasks t ON m.id_task = t.id LEFT JOIN dne_progress_status ps ON m.id_progress_status = ps.id	LEFT JOIN dne_responsibles r ON m.id_responsible = r.id WHERE m.id_project ='+$('#project_id').val()+ ' AND m.is_appears = 1 ';
	
	let params = [];
	
	if(chapters != '')
        params.push('m.id_chapter IN('+chapters+')');
	
	let is_include_pass_on_tasks = 0;
	if($('input[name="is_include_pass_on_tasks"]:checked').length > 0)
	  is_include_pass_on_tasks = $('input[name="is_include_pass_on_tasks"]:checked').val();
	
	if(subject != '')
	   params.push("m.subject LIKE '%"+subject+"%'");
   
    if(area != '')
	   params.push("m.area LIKE '%"+area+"%'");
	
	if(description != '')
		params.push("m.description LIKE '%"+description+"%'");
	
	if(tasks != '')
        params.push('m.id_task IN('+tasks+')');
	
	let and_or_responsibles = 'AND';
	if($('input[name="and_or_responsibles"]:checked').length > 0)
	  and_or_responsibles = $('input[name="and_or_responsibles"]:checked').val();
	
	if(and_or_responsibles == 'AND') {
		if(responsibles != '') 
			params.push('m.id_responsible IN('+responsibles+')');
		
		if(pass_ons != '')
			params.push('m.id_pass_on IN('+pass_ons+')');	
	}
	else if(and_or_responsibles == 'OR')
        params.push('(m.id_responsible IN('+responsibles+') OR m.id_pass_on IN('+pass_ons+'))');
	
	if(progress_status != '')
        params.push('m.id_progress_status IN('+progress_status+')');
	
	let creation_date = '';
	if(creation_date_start != '' && creation_date_end != '')
		creation_date = "m.task_creation_date BETWEEN '"+creation_date_start+"' AND '"+creation_date_end+"'";
	
	else if(creation_date_start != '' && creation_date_end == '')
		creation_date = "m.task_creation_date >= '"+creation_date_start+"'";
	
	else if(creation_date_start == '' && creation_date_end != '')
		creation_date = "m.task_creation_date <= '"+creation_date_end+"'";
	
	if(creation_date != '')
		params.push(creation_date);
	
	let destination_date = '';
	if(destination_date_start != '' && destination_date_end != '')
		destination_date = "m.destination_date BETWEEN '"+destination_date_start+"' AND '"+destination_date_end+"'";
	
	else if(destination_date_start != '' && destination_date_end == '')
		destination_date = "m.destination_date >= '"+destination_date_start+"'";
	
	else if(destination_date_start == '' && destination_date_end != '')
		destination_date = "m.destination_date <= '"+destination_date_end+"'";
	
	if(destination_date != '')
		params.push(destination_date);
	
	let params_str = '';
	if(params.length > 0)
		params_str = params.join(' AND ');
	
	if(params_str != '')
	   sql+= ' AND '+params_str;
   
    let is_images = 1;
	if($('input[name="is_images"]:checked').length > 0)
	  is_images = $('input[name="is_images"]:checked').val();
  
    let is_colors = 1;
	if($('input[name="is_colors"]:checked').length > 0)
	  is_colors = $('input[name="is_colors"]:checked').val();
  
    let lang = 'HE';
	if($('input[name="lang"]:checked').length > 0)
	  lang = $('input[name="lang"]:checked').val();
    
    let columns_list = '';
	$('#columns_list:checked').each(function(i){
	  columns_list+= $(this).val()+',';
	});
	columns_list = columns_list.substring(0,columns_list.length - 1);
	
	let hebrewCharsRegex = /[\u0590-\u05FF]/;
   
    let form_data = new FormData();	
	form_data.append('report_type','customReport');
	form_data.append('id',$('#id').val());
	form_data.append('id_project',$('#project_id').val());
	form_data.append('request_name',$('#request_name').val());
	form_data.append('pdf_name',$('#pdf_name').val());
	form_data.append('title',$('#title').val());
	form_data.append('id_supplier',$('#suppliers').val());
	form_data.append('is_include_pass_on_tasks',is_include_pass_on_tasks);
	form_data.append('subtitle',$('#subtitle').val());
	form_data.append('subject',subject);
	form_data.append('area',area);
	form_data.append('description',description);
	form_data.append('is_all_chapters_checked',all_chapters_checked);	
	form_data.append('chapters_list',chapters);
	form_data.append('tasks_list',tasks);
	form_data.append('is_all_responsibles_checked',all_responsibles_checked);	
	form_data.append('responsibles_list',responsibles);
	form_data.append('is_all_pass_ons_checked',all_pass_ons_checked);	
	form_data.append('pass_ons_list',pass_ons);
	form_data.append('progress_status_list',progress_status);
	form_data.append('period_creation_date',$('#period_creation_date').val());
	form_data.append('creation_date_start',creation_date_start);
	form_data.append('creation_date_end',creation_date_end);
	form_data.append('period_destination_date',$('#period_destination_date').val());
	form_data.append('destination_date_start',destination_date_start);
	form_data.append('destination_date_end',destination_date_end);
	form_data.append('and_or_responsibles',and_or_responsibles);
	form_data.append('sql_str',sql);
	form_data.append('is_images',is_images);
	form_data.append('is_colors',is_colors);
	form_data.append('lang',lang);
	form_data.append('period_new_tasks',$('#period_new_tasks').val());
	form_data.append('columns_list',columns_list);
		
	$.ajax({
		type: 'POST',
		url: 'custom_report_insert.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,	
        beforeSend: function() {
			$("#progress-popup").show();
			let progress = 0;
			let interval = setInterval(function() {
				if (progress < 90) {
					progress += 10;
					$("#progress-bar").css("width", progress + "%");
				} else {
					clearInterval(interval);
				}
			}, 200);
			$("#progress-popup").data("interval", interval);
		},		
		success: function(data) {
			if(data == 'hebrewchars') {
				alert('The file name contains Hebrew characters.');
			}
			else if(data == 'empty') {		
				if($('#request_name').val().length == 0)			
					$('#request_name').css('border-color','red');
				else if(!($('#request_name').val().length == 0))
					$('#request_name').css('border-color','initial');

                if($('#pdf_name').val().length == 0)			
					$('#pdf_name').css('border-color','red');
				else if(!($('#pdf_name').val().length == 0))
					$('#pdf_name').css('border-color','initial');					
				
				$('#div_message_alert_down').html("<span style=color:red;font-size:13px;><?=@$alert_mandatory_fields?></span>");
			}
	        else {
				location.href = 'custom_reports.php?project_id='+$('#project_id').val();		
		    }
		}
	});	
})

function toResultSpecialFilters(id_report) {
	let id_rdv_report = $('#id_rdv_report').val();
	let subject = $('#subject').val();
	let area = $('#area').val();
	let description = $('#description').val();
	let search_text = $('#search_filter').val();
	
	let all_chapters_checked = 0;
	if($('#all_chapters').prop("checked") == true)
        all_chapters_checked = 1;
	
    let chapters = '';
	$('#chapters:checked').each(function(i) {
	  chapters+= $(this).val()+',';
	});
	chapters = chapters.substring(0,chapters.length - 1);
	
	let tasks = '';
	$('#tasks:checked').each(function(i){
	  tasks+= $(this).val()+',';
	});
	tasks = tasks.substring(0,tasks.length - 1);
	
	let all_responsibles_checked = 0;
	if($('#all_responsibles').prop("checked") == true)
        all_responsibles_checked = 1;
	
	let responsibles = '';
	$('#responsibles:checked').each(function(i){
	  responsibles+= $(this).val()+',';
	});
	responsibles = responsibles.substring(0,responsibles.length - 1);
	
	let and_or_responsibles = 'AND';
	if($('input[name="and_or_responsibles"]:checked').length > 0)
	  and_or_responsibles = $('input[name="and_or_responsibles"]:checked').val();
  
    let all_pass_ons_checked = 0;
	if($('#all_pass_ons').prop("checked") == true)
        all_pass_ons_checked = 1;
	
	let pass_ons = '';
	$('#pass_ons:checked').each(function(i){
	  pass_ons+= $(this).val()+',';
	});
	pass_ons = pass_ons.substring(0,pass_ons.length - 1);
	
	let progress_status = '';
	$('#progress_status:checked').each(function(i){
	  progress_status+= $(this).val()+',';
	});
	progress_status = progress_status.substring(0,progress_status.length - 1);
	
	let creation_date_start = $('#creation_date_start').val();
	let creation_date_end = $('#creation_date_end').val();
	
	let destination_date_start = $('#destination_date_start').val();
	let destination_date_end = $('#destination_date_end').val();
	
	let sql = 'SELECT c.name AS name,m.id AS id,m.is_priority AS is_priority,m.id_user AS id_user,m.id_task_type AS id_task_type,m.id_chapter AS id_chapter,m.subject AS subject,m.ids_rdv AS ids_rdv,m.area,m.description,m.id_task,m.id_responsible,m.id_pass_on,m.task_creation_date,m.destination_date,m.id_progress_status,m.updated_date AS updated_date,m.image1 AS image1,m.is_appears_img1 AS is_appears_img1,m.image1_width AS image1_width,m.image1_height AS image1_height,m.image2 AS image2,m.is_appears_img2 AS is_appears_img2,m.image2_width AS image2_width,m.image2_height AS image2_height,m.is_change_row_style AS is_change_row_style,m.id_track_responsible AS id_track_responsible,m.track_type AS track_type,m.reminder_time AS reminder_time,m.reminder_date AS reminder_date,m.is_agrees AS is_agrees,m.is_reminds AS is_reminds FROM dne_meetings m LEFT JOIN dne_chapters c ON m.id_chapter = c.id LEFT JOIN dne_tasks t ON m.id_task = t.id LEFT JOIN dne_progress_status ps ON m.id_progress_status = ps.id	LEFT JOIN dne_responsibles r ON m.id_responsible = r.id  WHERE m.id_project ='+$('#project_id').val()+ ' AND m.is_appears = 1 ';
	
	let params = [];
	
	if(chapters != '')
        params.push('m.id_chapter IN('+chapters+')');
	
	if(search_text != '') 
	   params.push("(m.subject LIKE '%"+search_text+"%' OR m.area LIKE '%"+search_text+"%' OR m.description LIKE '%"+search_text+"%')");
	else if(search_text == '') {
		if(subject != '')
	       params.push("m.subject LIKE '%"+subject+"%'");
	   
		if(area != '')
		   params.push("m.area LIKE '%"+area+"%'");

		if(description != '')
		   params.push("m.description LIKE '%"+description+"%'");
	}
	
	if(and_or_responsibles == 'AND') {
		if(responsibles != '') 
			params.push('m.id_responsible IN('+responsibles+')');
		
		if(pass_ons != '')
			params.push('m.id_pass_on IN('+pass_ons+')');	
	}
	else if(and_or_responsibles == 'OR')
        params.push('(m.id_responsible IN('+responsibles+') OR m.id_pass_on IN('+pass_ons+'))');
	
	if(progress_status != '')
        params.push('m.id_progress_status IN('+progress_status+')');
	
	let creation_date = '';
	if(creation_date_start != '' && creation_date_end != '')
		creation_date = "m.task_creation_date BETWEEN '"+creation_date_start+"' AND '"+creation_date_end+"'";
	
	else if(creation_date_start != '' && creation_date_end == '')
		creation_date = "m.task_creation_date >= '"+creation_date_start+"'";
	
	else if(creation_date_start == '' && creation_date_end != '')
		creation_date = "m.task_creation_date <= '"+creation_date_end+"'";
	
	if(creation_date != '')
		params.push(creation_date);
	
	let destination_date = '';
	if(destination_date_start != '' && destination_date_end != '')
		destination_date = "m.destination_date BETWEEN '"+destination_date_start+"' AND '"+destination_date_end+"'";
	
	else if(destination_date_start != '' && destination_date_end == '')
		destination_date = "m.destination_date >= '"+destination_date_start+"'";
	
	else if(destination_date_start == '' && destination_date_end != '')
		destination_date = "m.destination_date <= '"+destination_date_end+"'";
	
	if(destination_date != '')
		params.push(destination_date);
	
	if(id_rdv_report > 0)
	   params.push("FIND_IN_SET("+id_rdv_report+",ids_rdv) > 0");
  
	let params_str = '';
	if(params.length > 0)
		params_str = params.join(' AND ');
	
	if(params_str != '')
	   sql+= ' AND '+params_str;
   
    let is_images = 1;
	if($('input[name="is_images"]:checked').length > 0)
	  is_images = $('input[name="is_images"]:checked').val();
  
    let is_colors = 1;
	if($('input[name="is_colors"]:checked').length > 0)
	  is_colors = $('input[name="is_colors"]:checked').val();
  
    let lang = 'HE';
	if($('input[name="lang"]:checked').length > 0)
	  lang = $('input[name="lang"]:checked').val();
    
    let columns_list = '';
	$('#columns_list:checked').each(function(i){
	  columns_list+= $(this).val()+',';
	});
	columns_list = columns_list.substring(0,columns_list.length - 1);
	
	let form_data = new FormData();
	
	if($('#id_custom_report').val() > 0) {
		form_data.append('id_custom_report',$('#id_custom_report').val());
		form_data.append('id_supplier',$('#id_supplier').val());
		form_data.append('pdf_name',$('#pdf_name').val());
	}
	
	form_data.append('is_all_chapters_checked',all_chapters_checked);
	form_data.append('chapters_list',chapters);	
	form_data.append('tasks_list',tasks);	
	form_data.append('is_all_responsibles_checked',all_responsibles_checked);
	form_data.append('responsibles_list',responsibles);
	form_data.append('and_or_responsibles',and_or_responsibles);
    form_data.append('is_all_pass_ons_checked',all_pass_ons_checked);	
	form_data.append('pass_ons_list',pass_ons);
	form_data.append('progress_status_list',progress_status);
	form_data.append('period_creation_date',$('#period_creation_date').val());
	form_data.append('creation_date_start',creation_date_start);
	form_data.append('creation_date_end',creation_date_end);
	form_data.append('period_destination_date',$('#period_destination_date').val());
	form_data.append('destination_date_start',destination_date_start);
	form_data.append('destination_date_end',destination_date_end);
	form_data.append('subject',subject);
	form_data.append('area',area);
	form_data.append('description',description);
	form_data.append('sql',sql);	
	form_data.append('is_images',is_images);
	form_data.append('is_colors',is_colors);
	form_data.append('lang',lang);
	form_data.append('period_new_tasks',$('#period_new_tasks').val());
	form_data.append('columns_list',columns_list);
		
	$.ajax({
		type: 'POST',
		url: 'set_session_filter_meetings.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,
        beforeSend: function() {
			$("#progress-popup").show();
			let progress = 0;
			let interval = setInterval(function() {
				if (progress < 90) {
					progress += 10;
					$("#progress-bar").css("width", progress + "%");
				} else {
					clearInterval(interval);
				}
			}, 200);
			$("#progress-popup").data("interval", interval);
		},		
		success: function(data){
			location.href = 'meetings.php?id='+id_report+'&project_id='+$('#project_id').val()+'&task_filter='+$('#task_filter').val()+'&progress_status_filter='+$('#progress_status_filter').val()+'&supplier_filter='+$('#get_supplier_filter').val()+'&period_new_task_filter='+$('#period_new_task_filter').val()+'&period_late_filter='+$('#period_late_filter').val()+'&search_filter='+$('#search_filter').val()+'&is_specific_filter=1&is_btn_new_bgcolor=1';
		}
	});		
}
</script>

<style>
table,th,td {
   border:1px solid black;
}

.btn:hover {
   color: white;
}

.bgColorBlue:hover {
	background-color: #3370d6;
}

input [type=button] {
  white-space: normal;
  width: 50px;
  -webkit-appearance: none;
  background: #ddd;
  border: none;
}

#a_project_title:hover {
	color: grey;
}

.width50Percents {
	width: 46%;
}

.widthBloc {
	width: 75%;
}

@media (max-width: 1024px) {
    .widthBloc {
		width: 85%;
	}
}

@media (max-width: 850px) {
	.widthBloc {
		width: 100%;
	}
}
</style>