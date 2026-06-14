<?php
include 'include/header.php';
include 'functions/functions.php';

$id = @$_GET['id'];

$iteration = @$_GET['iteration'];
$lang = 'HE';

if(@$_GET['lang'] != '')
	$lang = @$_GET['lang'];

$all_ids_to_edit = @$_GET['all_ids_to_edit'];
$is_specific_filter = @$_GET['is_specific_filter'];
$is_continious = @$_GET['iscontinious'];
$project_id = @$_GET['project_id'];
$chapter_id = @$_GET['chapter_id'];
$task_id = @$_GET['task_id'];
$responsible_id = @$_GET['responsible_id'];
$pass_on_id = @$_GET['pass_on_id'];
$fromProjectsList = 0;
$fromProjectHome = @$_GET['fromProjectHome'];
$fromResumeRdvs = @$_GET['fromResumeRdvs'];
$id_rdv_report = @$_GET['id_rdv_report'];

$query = $mysqli->prepare("SELECT empty_bgcolor,default_bgcolor,
                          filled_bgcolor FROM dne_inputs_colors LIMIT 1");
$query->execute(); 
$query->store_result();
$bg_color_inputs = fetch_unique($query);
$empty_bgcolor = @$bg_color_inputs->empty_bgcolor;
$default_bgcolor = @$bg_color_inputs->default_bgcolor;
$filled_bgcolor = @$bg_color_inputs->filled_bgcolor;

$query = $mysqli->prepare("SELECT nickname FROM dne_users WHERE id = ?");
$query->bind_param("i",$_SESSION['id_user']);
$query->execute();
$query->store_result();
$user = fetch_unique($query);

$user_label = @$user->nickname;

if(@$lang == "HE"){
	$dir = 'dir-rtl';
	$align = 'alignRight';
	$padding_5 = 'paddingRight5';
    $padding_10 = 'paddingRight10';
	$margin_8 = 'marginRight8';
	$created_by_label = "נוצר ע''י";
	$task_creation_date_label =  "תאריך יצירת משימה";
	$predecessor_label = 'פרדצסור';
	$task_associated_with_reports_label = "דוחו''ת";
	$chapter_label = "פרק";
	$select_chapter_label = "בחר פרק";
	$subject_label = "נושא/תחום";
	$area_label = "אזור/נושא";
	$description_label = "תאור";
	$responsible_label = "אחראי";
	$pass_on_label = "למסור ל";
	$select_staff_member_label = "בחר איש צוות";
	$tasks_types_label = "סוגי משימות";
	$select_task_type_label = "בחר סוג משימה";
	$is_agrees_label = "תאריך אספקה/פגישה מוסכם";
	$is_reminds_label = "נא לתזכר אותי 3 ימים לפני תאריך היעד";
	$target_date_label = "תאריך יעד";
	$image1_label = " תמונה 1";
	$image2_label = "תמונה 2";
	$choose_file_label = "בחר קובץ";
	$delete_label = "מחק";
	$save_label = "שמור";
    $cancel_label = "ביטול";
	$send_whatsapp_label = "שמור ושתף";
	$progress_tracking_and_remarks_label = "מעקב התקדמות והערות";
	$progress_status_label = "סטטוס התקדמות";
	$defer_to_label = "לדחות ל";
	@$remark_label = "הערה";
	@$loading_in_progress_label = "הטעינה מתבצעת...";
}
else {
	$dir = 'dir-ltr';
	$align = 'alignLeft';
	$margin_8 = 'marginLeft8';
	$padding_5 = 'paddingLeft5';
	$padding_10 = 'paddingLeft10';
	$created_by_label = "Created By";
	$task_creation_date_label = "Task Creation Date";
	$predecessor_label = 'Predecessor';
	$task_associated_with_reports_label = 'Reports';
	$chapter_label = "Chapter";
	$select_chapter_label = "Select chapter";
	$subject_label = "Subject/Domain";
	$area_label = "Area/Subject";
	$description_label = "Description";
	$responsible_label = "Responsible";
	$pass_on_label = "Deliver To";
	$select_staff_member_label = "Select staff member";
	$tasks_types_label = "Tasks Types";
	$is_agrees_label = "Delivery date/agreement meeting";
	$is_reminds_label = "Please remind me 3 days before the target date";
	$target_date_label = "Target Date";
	$image1_label = "Image 1";
	$image2_label = "Image 2";
	$choose_file_label = "Choose File";
	$delete_label = "Delete";
	$save_label = "Save";
	$cancel_label = "Cancel";
	$send_whatsapp_label = "Save & share";
	$progress_tracking_and_remarks_label = "Progress tracking and remarks";
	$progress_status_label = "Progress Status";
	$defer_to_label = "Defer To";
	@$remark_label = "Remark";
	@$loading_in_progress_label = "Loading in progress...";
}

if(strpos($project_id,'from_') !== false) {
	$project_id_array = explode('_',$project_id);
	$project_id = $project_id_array[1]; 
	$fromProjectsList = 1;
}

$id_rdv = @$_GET['id_rdv'];

$query = $mysqli->prepare("SELECT id_rdv_report FROM dne_log_current_report WHERE id_project = ?");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$log_current_report = fetch_unique($query);
$id_rdv_report = @$log_current_report->id_rdv_report;

$query = $mysqli->prepare("SELECT * FROM dne_projects WHERE id = ?");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$project = fetch_unique($query); 

$query = $mysqli->prepare("SELECT * FROM dne_tasks_actions");
$query->execute();
$query->store_result();
$tasks_actions = fetch($query);

$query = $mysqli->prepare("SELECT * FROM dne_progress_status 
                          WHERE id_project = ?");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$progress_status = fetch($query);

$is_user_active = 1;
$query = $mysqli->prepare("SELECT * FROM dne_users 
                          WHERE is_user_active = ?");
$query->bind_param("i",$is_user_active);
$query->execute();
$query->store_result();
$users = fetch($query);

if($fromResumeRdvs == 1 && $id == 0) {
	$query = $mysqli->prepare("SELECT * FROM dne_rdv WHERE id = ?");
    $query->bind_param("i",$id_rdv);
	$query->execute();
	$query->store_result();
	$rdv = fetch_unique($query);
	$task_creation_date = @$rdv->rdv_date;
}

$task_creation_date = date("Y-m-d"); 
$destination_date = date("Y-m-d", strtotime($task_creation_date." + 7 days"));

$title = 'New Task';
if(@$lang == 'HE')
	$title = 'משימה חדשה';

$query = $mysqli->prepare("SELECT r.*
						 FROM dne_rdv r
						 WHERE r.id_project = ?
						 AND r.id IN (
								SELECT DISTINCT r2.id
								FROM dne_rdv r2
								JOIN dne_meetings m 
								ON m.id_project = r2.id_project
								WHERE m.id = ?
								AND FIND_IN_SET(r2.id,m.ids_rdv)
						 ) 
						 ORDER BY r.rdv_date DESC");
$query->bind_param("ii",$project_id,$id);
$query->execute();
$query->store_result();
$associated_rdvs_num_rows = $query->num_rows;
$associated_rdvs = fetch($query);

$query = $mysqli->prepare("SELECT r.*
						 FROM dne_rdv r
						 WHERE r.id_project = ?
						 AND r.id NOT IN (
								SELECT DISTINCT r2.id
								FROM dne_rdv r2
								JOIN dne_meetings m 
								ON m.id_project = r2.id_project
								WHERE m.id = ?
								AND FIND_IN_SET(r2.id,m.ids_rdv)
						 ) 
						 ORDER BY r.rdv_date DESC");
$query->bind_param("ii",$project_id,$id);
$query->execute();
$query->store_result();
$rdvs_num_rows = $query->num_rows;
$rdvs = fetch($query);

if($id > 0) {
    $query = $mysqli->prepare("SELECT * FROM dne_meetings WHERE id = ?");
    $query->bind_param("i",$id);
    $query->execute();
    $query->store_result();
    $meeting = fetch_unique($query);
	
	$query = $mysqli->prepare("SELECT email FROM dne_responsibles WHERE id = ?");
    $query->bind_param("i",$meeting->id_responsible);
    $query->execute();
    $query->store_result();
    $responsible = fetch_unique($query);
   
    $query = $mysqli->prepare("SELECT * FROM dne_users WHERE id = ?");
    $query->bind_param("i",$meeting->id_user);
    $query->execute();
    $query->store_result();
    $meeting_user = fetch_unique($query);
   
    $new_label = "חדשה";
    $query = "SELECT destination_date FROM dne_log_meeting_updates 
			  WHERE id_meeting = ? AND action = ? LIMIT 1";
    $query = $mysqli->prepare($query);
    $query->bind_param('is',$id,$new_label);   
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

    $remark = '';
    $one = 1;																				
    $query = $mysqli->prepare("SELECT remark FROM dne_log_meeting_updates 
							  WHERE id_meeting = ? 
							  AND is_remark_appears_log = ?
							  ORDER BY id DESC LIMIT 1");
    $query->bind_param("ii",$id,$one);
    $query->execute();
    $query->store_result();
    $query = fetch_unique($query);	
    $remark = @$query->remark;
    
	$title = 'Task';  
	if(@$lang == 'HE')
		$title = 'משימה'; 
	
    $user_label = @$meeting_user->nickname;
   
    if(@$meeting->task_creation_date != '0000-00-00')
     $task_creation_date = @$meeting->task_creation_date;
 
    if(@$meeting->destination_date != '0000-00-00') 
     $destination_date = @$meeting->destination_date;
}

$query = $mysqli->prepare("SELECT * FROM dne_chapters 
                          WHERE id_project = ? 
						  ORDER BY name ASC");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$chapters = fetch($query);

$is_active = 1;	
$query = $mysqli->prepare("SELECT * FROM dne_responsibles 
                          WHERE id_project = ? AND is_active = ? 
						  ORDER BY name ASC");
$query->bind_param("ii",$project_id,$is_active);
$query->execute();
$query->store_result();
$responsibles = fetch($query);

$is_appears_tasks_list = 1;

$query = $mysqli->prepare("SELECT * FROM dne_tasks WHERE id_project = ? AND is_appears_tasks_list = ?
                          ORDER BY name_he ASC");
$query->bind_param("ii",$project_id,$is_appears_tasks_list);
$query->execute();
$query->store_result();
$tasks = fetch($query);

$query = $mysqli->prepare("SELECT * FROM dne_progress_status 
                          WHERE id_project = ?");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$progress_status = fetch($query);

include 'menu_tasks.php';
?>

		<form method="post" action="" enctype="multipart/form-data" class="form-inline">
			<input type="hidden" id="id" value="<?=@$id?>" />
			<input type="hidden" id="task_creation_date_hidden" value="<?=@$meeting->task_creation_date?>" />
			<input type="hidden" id="id_connected_user" value="<?=@$_SESSION['id_user']?>" />
			<input type="hidden" id="id_user" value="<?=@$meeting->id_user?>" />
			<input type="hidden" id="iteration" value="<?=@$iteration?>" />
			<input type="hidden" id="all_ids_to_edit" value="<?=@$all_ids_to_edit?>" />
			<input type="hidden" id="is_specific_filter" value="<?=@$is_specific_filter?>" />
			<input type="hidden" id="project_id" value="<?=@$project_id?>" />
			<input type="hidden" id="chapter_data" value="<?=@$meeting->id_chapter?>" />
			<input type="hidden" id="subject_data" value="<?=@$meeting->subject?>" />
			<input type="hidden" id="area_data" value="<?=@$meeting->area?>" />
			<input type="hidden" id="recipient_data" value="<?=@$responsible->email?>" />
			<input type="hidden" id="responsible_id_data" value="<?=@$meeting->id_responsible?>" />
			<input type="hidden" id="remark_data" value="<?=@$remark?>" />
			<input type="hidden" id="track_type_data" value="<?=@$meeting->track_type?>" />
			<input type="hidden" id="is_priority_data" value="<?=@$meeting->is_priority?>" />
			<input type="hidden" id="user_id_data" value="<?=@$meeting->id_user?>" />
			<input type="hidden" id="track_responsible_id_data" value="<?=@$meeting->id_track_responsible?>" />
			<input type="hidden" id="reminder_date_data" value="<?=@$meeting->reminder_date?>" />
			<input type="hidden" id="reminder_time_data" value="<?=@$meeting->reminder_time?>" />
			<input type="hidden" id="chapter_id" value="<?=@$chapter_id?>" />
			<input type="hidden" id="task_id" value="<?=@$task_id?>" />
			<input type="hidden" id="fromProjectHome" value="<?=@$fromProjectHome?>" />
			<input type="hidden" id="fromResumeRdvs" value="<?=@$fromResumeRdvs?>" />	
			<input type="hidden" id="fromProjectsList" value="<?=@$fromProjectsList?>" />
            <input type="hidden" id="id_rdv" value="<?=@$id_rdv?>" />	
			<input type="hidden" id="id_rdv_report" value="<?=@$id_rdv_report?>" />			
            <input type="hidden" id="is_appears_img1_hidden" value="<?=@$meeting->is_appears_img1?>" />		
            <input type="hidden" id="is_appears_img2_hidden" value="<?=@$meeting->is_appears_img2?>" />			
            <input type="hidden" id="id_task_action" value="<?=@$id_task_action?>" />
			<input type="hidden" id="progress_status_hidden" value="<?=@$meeting->id_progress_status?>" />
			<input type="hidden" id="image1_hidden" value="<?=@$meeting->image1?>" />
			<input type="hidden" id="image1_width" value="<?=@$meeting->image1_width?>" />
            <input type="hidden" id="image1_height" value="<?=@$meeting->image1_height?>" />
			<input type="hidden" id="image1_rotation" value="<?=@$meeting->image1_rotation?>" />
			<input type="hidden" id="image2_hidden" value="<?=@$meeting->image2?>" />
			<input type="hidden" id="image2_width" value="<?=@$meeting->image2_width?>" />
            <input type="hidden" id="image2_height" value="<?=@$meeting->image2_height?>" />
			<input type="hidden" id="image2_rotation" value="<?=@$meeting->image2_rotation?>" />
			<input type="hidden" id="is_continious" value="<?=@$is_continious?>" />
			<input type="hidden" id="empty_bgcolor" value="<?=@$empty_bgcolor?>" />
			<input type="hidden" id="default_bgcolor" value="<?=@$default_bgcolor?>" />
			<input type="hidden" id="filled_bgcolor" value="<?=@$filled_bgcolor?>" />
			<input type="hidden" id="responsible_email" value="<?=@$responsible->email?>" />
			<input type="hidden" id="lang" value="<?=@$lang?>" />
			
            <div class="container">
			    <br/>
				
				<div class="width500" id="contentToScreenshot"></div>
			
				<div class="row alignCenter marginTop25">
					<div class="col-12">
						<a href="projects.php"><img src="images/davidnahmias_logo.png" width="170px" height="170px" /></a>
					</div>
			    </div>
				
				<div class="row marginTop15 title <?=@$dir?>">	
					<div class="col-12">
						<a id="a_project_title" class="text-decoration-none color-1A5276" href="meetings.php?project_id=<?=@$project_id?>&is_specific_filter=<?=@$is_specific_filter?>">				
						    <strong class="fontSize33 line-height-1em"><?=@$project->nickname?></strong>
							<?php if(@$lang == 'HE') { ?>
								<div class="fontSize26 font-family-david">פרוייקט <?=@$project->name_he?></div>
							<?php } 
							else { ?>
								<div class="fontSize26">Project <?=@$project->name?></div>
							 <?php } ?>
					    </a>		
					</div>				
			    </div>
				
			    <div class="row marginTop10">
				   <div class="col-12 col-lg-6 offset-lg-3 card bg-f2f6f9 border-frame borderRadius15 padding0">
						<div class="card-header bgColor-1a5276 colorWhite alignCenter fontSize20" style="border-top-left-radius:15px;border-top-right-radius:15px;"><strong><?=@$title?></strong></div>	   
				        <div class="card-body pt-0 px-2"> 
							<div class="row alignCenter <?=@$dir?>">   
								<div class="col-6 col-md-6 col-lg-4 d-flex align-items-start mt-1">
									<strong class="text-size"><?=@$created_by_label?>:</strong>
									<span class="<?=@$margin_8?> text-size"><?=@$user_label?></span>
								</div>

								<div class="col-6 col-md-6 col-lg-8 d-flex flex-column align-items-end task-header-right mt-1">
									<div class="d-flex align-items-center">
										<strong class="text-size"><?=@$task_creation_date_label?>:</strong>
										<?php if($id == 0) { ?>
											<input type="date" class="text-size width150 alignCenter <?=@$margin_8?>" 
												   name="task_creation_date" id="task_creation_date" 
												   value="<?=date('Y-m-d')?>" />
										<?php } else { ?>
											<span class="<?=@$margin_8?> text-size">
												<?=smartDate(@$task_creation_date)?>
											</span>
										<?php } ?>
									</div>
							  
									<?php if(@$id > 0 && $associated_rdvs_num_rows > 0) { ?>
										<div class="mt-2">
											<input type="button" id="display_associated_rdv_btn" 
												   class="btn bgColorSilver colorWhite <?=@$margin_8?> mb-2 align-self-end" 
												   value="<?=@$task_associated_with_reports_label?>" 
												   onclick="$('#div_associated_rdvs').css('display','block');" />
										</div>

										<div id="div_associated_rdvs" class="mx-auto w-100 w-md-60 display-none mt-2">
											<div class="border-frame py-2 px-3 border-black">
												<div class="max-height-180 overflow-y-scroll">
													<?php foreach($associated_rdvs as $item){ ?>    
														<div class="d-flex align-items-center mb-2">
															<div class="me-2">
																<input type="checkbox" id="rdv_<?=@$item->id?>" name="rdvs[]" value="<?=@$item->id?>" checked />
															</div>
															<div>
																<?=smartDate(@$item->rdv_date).'-'.@$item->rdv_name?>
															</div>
														</div>
													<?php } ?>
												</div>
											</div>
										</div>
									<?php } ?>
								</div>
							</div>

                            <div class="row alignCenter <?=@$dir?>">
								<div class="col-12">
									<a id="he" class="text-decoration-none text-size <?php if(@$lang == 'HE') echo 'font-weight-bold colorGreen';?> cursor-pointer" onclick="setFieldDirection('HE')">ע</a>&nbsp;| 
									<a id="en" class="text-decoration-none text-size <?php if(@$lang == 'EN') echo 'font-weight-bold colorGreen';?> cursor-pointer" onclick="setFieldDirection('EN')">EN</a>
								</div>
							</div>	    	
													
						    <?php
							    if($id > 0 && @$meeting->id_parent != 0) {
								  $query = $mysqli->prepare("SELECT subject FROM dne_meetings WHERE id = ?");
								  $query->bind_param("i",$meeting->id_parent);
								  $query->execute();
								  $query->store_result();
								  $predecessor = fetch_unique($query);
								  ?>
								  <div class="row marginTop10 fontSize16 alignCenter <?=@$dir?>">
								      <strong><?=@$predecessor_label?>:</strong><span class="<?=@$margin_8?>"><?=@$predecessor->subject?></span>
							      </div>	
								  <?php  
							} ?>
				
							<div class="row marginTop10 alignCenter <?=@$dir?>">
								<div class="col-12">
									<strong class="text-size"><?=@$chapter_label?></strong>
									<br/>
									<select id="chapter" class="marginTop5 width220 <?=@$dir?> <?=@$padding_10?> text-size">	
										<option value="0"><?=@$select_chapter_label?></option>
										<?php 
										foreach($chapters as $item) {
										?>
											<option value="<?=@$item->id?>" <?php if(($item->id === @$meeting->id_chapter) || ($item->id == @$chapter_id)) echo "selected";?>>
												<?=@$item->name?>
											</option>
											<?php
										}
										?>						
									</select>
								</div>
							</div>	
							
							<div class="row flex width80Percents margin-top-10-x-auto <?=@$dir?> alignCenter">
								<div class="width50Percents text-size <?=@$align?> <?=$padding_5?>">				
									<strong class="text-size"><?=@$subject_label?></strong>				
									<br/>
									<div id="div-subject" class="marginTop5 width100Percents cursor-pointer">
										<div name="subject" id="subject" contenteditable="true" class="editable height40 cursor-pointer">
											<?=html_entity_decode(@$meeting->subject)?>
										</div>
									</div>    
								</div>
								
								<div class="width50Percents text-size <?=@$align?> <?=$padding_5?>">
									<strong class="text-size"><?=@$area_label?></strong>
									<br/>
									<div id="div-area" class="marginTop5 width100Percents cursor-pointer">
								        <div name="area" id="area" contenteditable="true" class="editable height40 cursor-pointer"><?=html_entity_decode(@$meeting->area)?></div>
								    </div>					
								</div>
							</div>								
						
							<div class="row marginTop10 alignCenter <?=@$dir?>">
								<div class="col-12 paddingTop2">
									<strong class="text-size"><?=@$description_label?></strong>	
								</div>
							</div>
							
							<div class="row dir-rtl">
								<div class="col-2"></div>
								<div class="col-8">            							
									<div class="marginTop5 <?=@$dir?> <?=@$padding_10?> width100Percents height-auto cursor-pointer">
								        <div name="description" id="description" contenteditable="true" class="editable cursor-pointer"><?=html_entity_decode(@$meeting->description)?></div>
								    </div>
								</div>
								<div class="col-2"></div>
							</div>
							
							<div class="row marginTop10 <?=@$dir?> alignCenter">
								<div class="col-12">
									<strong class="text-size"><?=@$target_date_label?></strong>					
									<div class="marginTop5">
										<input type="date" class="text-size width150 alignCenter" name="destination_date" id="destination_date" value="<?=@$destination_date?>" />
									</div>
								</div>
							</div>
							
							<div class="p-2">
								<div class="d-flex flex-column align-items-center" style="direction:rtl; max-width:400px; margin:auto;">
									<div class="d-flex align-items-center justify-content-center mb-2" style="gap:0.5rem;">
										<input type="checkbox" id="is_agrees" <?php if(@$meeting->is_agrees) echo 'checked'; ?>>
										<label for="is_agrees" class="mb-0"><?=@$is_agrees_label?></label>
									</div>

									<div class="d-flex align-items-center justify-content-center" style="gap:0.5rem;">
										<i class="fa-solid fa-bell colorRed"></i>
										<input type="checkbox" id="is_reminds" <?php if(@$meeting->is_reminds) echo 'checked'; ?>>
										<label for="is_reminds" class="mb-0"><?=@$is_reminds_label?></label>
									</div>
								</div>
							</div>

							<div class="flex justify-content-space-around flex-wrap marginTop20 <?=@$dir?>">
								<div class="alignCenter width-one-of-three">   
									<strong class="text-size"><?=@$responsible_label?></strong>
									<div class="row marginTop10">
										<div class="col-12">
											<select id="responsible" class="width80Percents <?=@$dir?> <?=@$padding_10?> text-size" onchange="makeOperations('responsible',this.value);">
												<option value="0"><?=@$select_staff_member_label?></option>			
												<?php 
												foreach($responsibles as $item) {
												?>
													<option value="<?=@$item->id?>" <?php if(($item->id === @$meeting->id_responsible) || ($item->id == @$responsible_id)) echo "selected";?>>
														<?=@$item->name?>
													</option>
													<?php
												}
												?>						
											</select>
										</div>
									</div>
								</div>
								
								<div class="alignCenter width-one-of-three">   
									<strong class="text-size"><?=@$pass_on_label?></strong>      
									<div class="row marginTop10">
										<div class="col-12">
											<select id="pass_on" class="width80Percents <?=@$dir?> <?=$padding_10?> text-size" onchange="makeOperations('pass_on',this.value);">
												<option value="0"><?=@$select_staff_member_label?></option>			
												<?php 
												foreach($responsibles as $item) {
												?>
													<option value="<?=@$item->id?>" <?php if(($item->id === @$meeting->id_pass_on) || ($item->id == @$pass_on_id)) echo "selected";?>>
														<?=@$item->name?>
													</option>
													<?php
												}
												?>						
											</select>
										</div>
									</div>
								</div>
						
								<div class="alignCenter width-one-of-three">
									<strong class="text-size"><?=@$tasks_types_label?></strong>
									<div class="row marginTop10">
										<div class="col-12">
											<span id="tasks_list_span">
												<select id="task" class="width80Percents <?=@$dir?> <?=@$padding_10?> text-size" onchange="makeOperations('task',this.value);">		
													<option value="0"><?=@$select_task_type_label?></option>								
													<?php 
													foreach($tasks as $item) {
													?>
														<option value="<?=@$item->id?>" <?php if(($item->id === @$meeting->id_task) || ($item->id == @$task_id)) echo "selected";?>>
															<?=@$item->name_he?>
														</option>
														<?php
													}
													?>						
												</select>	
											</span>
										</div>
									</div>
								</div>
							</div>										
							
							<div class="row flex margin-top-10-x-auto <?=@$dir?> width100Percents alignCenter">
								<div class="width50Percents">
									<input type="checkbox" id="is_appears_img1" <?php if(@$meeting->is_appears_img1 == 1) echo 'checked';?> />
									&nbsp;
									<strong class="text-size"><?=@$image1_label?></strong>
									<br/>
                                    
									<label for="image1" class="custom-file-upload"><?=@$choose_file_label?></label>
                                    <input id="image1" name="image1" class="file-upload" type="file" accept=".jpg,.jpeg" hidden>		
                                    
									<div id="div-delete-image1" class="row marginTop20 <?php echo(!empty($meeting->image1))?"display-block":"display-none";?>">
									    <div class="col-12">
									        <img src="images/delete.svg" class="cursor-pointer" title="<?=@$delete_label?>" onclick="deleteTaskImage('image1');" />
									    </div>
									</div>
									<div id="div-image1" class="row marginTop25 <?php echo(!empty($meeting->image1))?"display-block":"display-none";?>">
									    <div class="col-12">
											<div class="image1-container" id="image1-container">
                                                <img id="preview-image1" src="uploads/<?=@$meeting->image1?>" alt="Chosen image" width="180" height="150">
                                            </div>
										</div>
									</div>
									<!--<div id="div-rotations-image1" class="row marginTop15 <?php echo(!empty($meeting->image1))?"display-block":"display-none";?>">
										<div class="col-12">
											<label><input type="radio" name="rotation-image1" value="0" <?php if(@$id == 0 ||(@$id > 0 && @$meeting->image1_rotation == 0)) echo "checked";?>> 0°</label>
											<label><input type="radio" name="rotation-image1" value="90" <?php if(@$meeting->image1_rotation == 90) echo "checked";?>> 90°</label>
											<label><input type="radio" name="rotation-image1" value="180" <?php if(@$meeting->image1_rotation == 180) echo "checked";?>> 180°</label>
											<label><input type="radio" name="rotation-image1" value="270" <?php if(@$meeting->image1_rotation == 270) echo "checked";?>> 270° </label>			
                                        </div>										
									</div>!-->	
								</div>	
                                <div class="width50Percents">
									<input type="checkbox" id="is_appears_img2" <?php if(@$meeting->is_appears_img2 == 1) echo 'checked';?> />
									&nbsp;
									<strong class="text-size"><?=@$image2_label?></strong>
									<br/>
									
                                    <label for="image2" class="custom-file-upload"><?=@$choose_file_label?></label>
                                    <input id="image2" name="image2" class="file-upload" type="file" accept=".jpg,.jpeg" hidden>		
                                    
									<div id="div-delete-image2" class="row marginTop20 <?php echo(!empty($meeting->image2))?"display-block":"display-none";?>">
									    <div class="col-12">
									        <img src="images/delete.svg" class="cursor-pointer" title="<?=@$delete_label?>" onclick="deleteTaskImage('image2');" />
									    </div>
									</div>
									<div id="div-image2" class="row marginTop25 <?php echo(!empty($meeting->image2))?"display-block":"display-none";?>">
									    <div class="col-12">
										    <div class="image2-container" id="image2-container">
                                                <img id="preview-image2" src="uploads/<?=@$meeting->image2?>" alt="Chosen image" width="180" height="150">
                                            </div>
										</div>
									</div>
                                    <!--<div id="div-rotations-image2" class="row marginTop15 <?php echo(!empty($meeting->image2))?"display-block":"display-none";?>">
										<div class="col-12">
											<label><input type="radio" name="rotation-image2" value="0" <?php if(@$id == 0 ||(@$id > 0 && @$meeting->image2_rotation == 0)) echo "checked";?>> 0°</label>
											<label><input type="radio" name="rotation-image2" value="90" <?php if(@$meeting->image2_rotation == 90) echo "checked";?>> 90°</label>
											<label><input type="radio" name="rotation-image2" value="180" <?php if(@$meeting->image2_rotation == 180) echo "checked";?>> 180°</label>
											<label><input type="radio" name="rotation-image2" value="270" <?php if(@$meeting->image2_rotation == 270) echo "checked";?>> 270° </label>			
                                        </div>										
									</div>!-->										
								</div>										
							</div>
							<div class="row marginTop15 alignCenter <?=@$dir?>">
								<div class="col-12">
									<div id="div_message_alert_down"></div>
									<input type="button" id="save_btn" name="save_btn" class="btn bgColorBlue colorWhite mb-2" value="<?=@$save_label?>" />					
									<input type="button" id="send_whatsapp_btn" name="send_whatsapp_btn" class="btn whatsapp-btn bgColorBlue colorWhite <?=@$margin_8?> mb-2" value="<?=@$send_whatsapp_label?>" />
									<input type="button" id="cancel_btn" class="btn bgColorBlack colorWhite <?=@$margin_8?> mb-2" value="<?=@$cancel_label?>" />						
								</div>
							</div>	
					    </div>
				    </div>					
			    </div>		    
			</div>					
		</form> 
		
		<div id="progress-popup">
            <p><?=@$loading_in_progress_label?></p>
            <div id="div-progress-bar">
                <div id="progress-bar"></div>
            </div>
        </div>
    </body>
</html>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
let image1 = '', image2 = '';
let is_appears_img1,is_appears_img2 = 0;
let image1_compressing = false, image2_compressing = false;

function compressFile(file, maxDim, quality) {
    maxDim = maxDim || 1920;
    quality = quality || 0.85;
    return new Promise(function(resolve) {
        let reader = new FileReader();
        reader.onload = function(e) {
            let img = new Image();
            img.onload = function() {
                let w = img.width, h = img.height;
                if (w > maxDim || h > maxDim) {
                    if (w >= h) { h = Math.round(h * maxDim / w); w = maxDim; }
                    else { w = Math.round(w * maxDim / h); h = maxDim; }
                }
                let canvas = document.createElement('canvas');
                canvas.width = w; canvas.height = h;
                canvas.getContext('2d').drawImage(img, 0, 0, w, h);
                let dataURL = canvas.toDataURL('image/jpeg', quality);
                let byteString = atob(dataURL.split(',')[1]);
                let ab = new ArrayBuffer(byteString.length);
                let ia = new Uint8Array(ab);
                for (let i = 0; i < byteString.length; i++) ia[i] = byteString.charCodeAt(i);
                let blob = new Blob([ab], {type: 'image/jpeg'});
                let baseName = file.name.replace(/\.[^/.]+$/, '');
                let compressedFile = new File([blob], baseName + '.jpg', {type: 'image/jpeg'});
                resolve({file: compressedFile, dataURL: dataURL});
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    });
}
let div_image1 = $("#div-image1"); 
let div_image2 = $("#div-image2"); 
let div_delete_image1 = $("#div-delete-image1"); 
let div_delete_image2 = $("#div-delete-image2"); 
let preview_image1 = $('#preview-image1');
let preview_image2 = $('#preview-image2');
let image1_container = $('#image1-container');
let image2_container = $('#image2-container');
let div_rotations_image1 = $('#div-rotations-image1');
let div_rotations_image2 = $('#div-rotations-image2');

$(document).ready(function(){    
	let meeting_id;
	let iteration;
	let subject;
	let area;
	let remark;
		
	if($('#id').val() == 0){
		$('#chapter,#subject,#area,#description,#task,#responsible,#pass_on').css('background-color',$('#empty_bgcolor').val());
		
		if($('#chapter_id').val() > 0)
			$('#chapter').css('background-color',$('#default_bgcolor').val());
		else
            $('#chapter').css('background-color',$('#empty_bgcolor').val());
		
		if($('#task_id').val() > 0)
		    $('#task,#responsible,#pass_on').css('background-color',$('#default_bgcolor').val());
		else 
		    $('#task,#responsible,#pass_on').css('background-color',$('#empty_bgcolor').val());
		
		$('#task_creation_date,#destination_date').css('background-color',$('#default_bgcolor').val());
	}
	else if($('#id').val() > 0){
		$('#chapter,#subject,#area,#task,#responsible,#pass_on,#progress_status').css('background-color',$('#filled_bgcolor').val());
		$('#destination_date,#new_destination_date').css('background-color',$('#default_bgcolor').val());
		
		if($('#description').html().replace(/<[^>]*>/g,'').replace(/&nbsp;/g,' ').trim().length == 0)
			$('#description').css('background-color',$('#empty_bgcolor').val());
        else 
            $('#description').css('background-color',$('#filled_bgcolor').val());		
	}

    $(document).on('change','#chapter', function (){
		if($(this).val() > 0)
		   $(this).css('background-color',$('#filled_bgcolor').val());
	   
		let form_data = new FormData();
		form_data.append('id_project', $('#project_id').val());
		form_data.append('id_chapter', $(this).val());
		form_data.append('id_user', $('#id_connected_user').val());	
				
		$.ajax({
			type: 'POST',
			url: 'changes_chapter_task.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,			
			success: function(data){
                if(data != 'nodefaultdata') {
				   let data_array = data.split('-');
				   $('#task').val(data_array[0]);
                   $('#responsible').val(data_array[1]);
                   $('#pass_on').val(data_array[2]);
                   $('#task,#responsible,#pass_on').css('background-color',$('#default_bgcolor').val());				   
				}
                else if(data == 'nodefaultdata') {
				   $('#task,#responsible,#pass_on').val(0);
                   $('#task,#responsible,#pass_on').css('background-color',$('#empty_bgcolor').val());
				}					
			}	
		});
	});

    $(document).on('change','#task,#responsible,#pass_on', function (){
		if($(this).val() > 0)
		   $(this).css('background-color',$('#filled_bgcolor').val());
        else 
			$(this).css('background-color',$('#empty_bgcolor').val());
    });
	
	function updateBg(field){
		let text = $('#'+field).text().replace(/\u00A0|\u200B/g, '').trim();

		let $wrapper = $('#div-'+field);
		let $field = $('#'+field);

		let filled = $('#filled_bgcolor').val();
		let empty = $('#empty_bgcolor').val();

		let bgcolor = text.length > 0 ? filled :empty;

		$wrapper.css('background-color',bgcolor);
		$field.css('background-color',bgcolor); 
	}

	$(document).on('blur input keyup paste','#subject,#area',function (){
		let fieldId = $(this).attr('id');
		updateBg(fieldId);
	});

	$(document).on('change','#image1', function (){
		let file = $('#image1')[0].files[0];
		let image1_name = file.name;

		let hebrewCharsRegex = /[\u0590-\u05FF]/;
		if (hebrewCharsRegex.test(image1_name)) {
			alert('The file name contains Hebrew characters.');
			$('#image1').val('');
			$('#is_appears_img1').prop('checked',false);
			return;
		}

		$('#is_appears_img1').prop('checked',true);
		is_appears_img1 = 1;
		$('#div-image1').show();

		image1_compressing = true;
		compressFile(file).then(function(result) {
			image1 = result.file;
			preview_image1.attr("src", result.dataURL);
			div_delete_image1.css("display","block");
			div_image1.css("display","block");
			image1_container.css("display","block");
			div_rotations_image1.css("display","block");
		}).catch(function() {
			image1 = file;
			let r = new FileReader();
			r.onload = function(e) { preview_image1.attr("src", e.target.result); };
			r.readAsDataURL(file);
			div_delete_image1.css("display","block");
			div_image1.css("display","block");
			image1_container.css("display","block");
			div_rotations_image1.css("display","block");
		}).finally(function() {
			image1_compressing = false;
		});
	});
	
	$(document).on('change','#image2', function (){
		let file = $('#image2')[0].files[0];
		let image2_name = file.name;

		let hebrewCharsRegex = /[\u0590-\u05FF]/;
		if (hebrewCharsRegex.test(image2_name)){
			alert('The file name contains Hebrew characters.');
			$('#image2').val('');
			$('#is_appears_img2').prop('checked',false);
			return;
		}

		$('#is_appears_img2').prop('checked',true);
		is_appears_img2 = 1;
		$('#div-image2').show();

		image2_compressing = true;
		compressFile(file).then(function(result) {
			image2 = result.file;
			preview_image2.attr("src", result.dataURL);
			div_delete_image2.css("display","block");
			div_image2.css("display","block");
			image2_container.css("display","block");
			div_rotations_image2.css("display","block");
		}).catch(function() {
			image2 = file;
			let r = new FileReader();
			r.onload = function(e) { preview_image2.attr("src", e.target.result); };
			r.readAsDataURL(file);
			div_delete_image2.css("display","block");
			div_image2.css("display","block");
			image2_container.css("display","block");
			div_rotations_image2.css("display","block");
		}).finally(function() {
			image2_compressing = false;
		});
	});

	function loadTaskImage(image,angle,imageSrc){	
		if (image === 'image1') {
			let _image1 = new Image();
			_image1.onload = function (){
				preview_image1.css('transform', `rotate(${angle}deg)`);
				let canvas_image1 = document.createElement('canvas');
				let ctx_image1 = canvas_image1.getContext('2d');

				canvas_image1.width = _image1.width;
				canvas_image1.height = _image1.height;

				ctx_image1.clearRect(0,0,canvas_image1.width,canvas_image1.height);
				ctx_image1.save();
				ctx_image1.translate(canvas_image1.width/2,canvas_image1.height/2);
				ctx_image1.rotate((angle*Math.PI)/180);
				ctx_image1.drawImage(_image1,-_image1.width/2,-_image1.height/2);
				ctx_image1.restore();

				let rotated_image1 = canvas_image1.toDataURL('image/jpeg', 0.85);

				let form_data = new FormData();
				
				form_data.append('image1', rotated_image1);
				
				if($('#id').val() == 0)
				   form_data.append('image1_name', $('#image1')[0].files[0].name);
				else 
				   form_data.append('image1_name', $('#image1_hidden').val());
				
				form_data.append('image1_rotation', angle);  	

				$.ajax({
					type: 'POST',
					url: 'save_task_image.php',
					data: form_data,
					cache: false,
					processData: false,
					contentType: false,			
					success: function(data){      
					}	
				});
			};

			_image1.src = imageSrc;
		}
		else if (image === 'image2'){
			let _image2 = new Image();
			_image2.onload = function (){
				preview_image2.css('transform',`rotate(${angle}deg)`);
				let canvas_image2 = document.createElement('canvas');
				let ctx_image2 = canvas_image2.getContext('2d');

				canvas_image2.width = _image2.width;
				canvas_image2.height = _image2.height;

				ctx_image2.clearRect(0,0,canvas_image2.width,canvas_image2.height);
				ctx_image2.save();
				ctx_image2.translate(canvas_image2.width/2,canvas_image2.height/2);
				ctx_image2.rotate((angle*Math.PI)/180);
				ctx_image2.drawImage(_image2,-_image2.width/2,-_image2.height/2);
				ctx_image2.restore();

				let rotated_image2 = canvas_image2.toDataURL('image/jpeg', 0.85);

				let form_data = new FormData();
				form_data.append('image2', rotated_image2);
				
				if($('#id').val() == 0)
				   form_data.append('image2_name', $('#image2')[0].files[0].name);
				else 
				   form_data.append('image2_name', $('#image2_hidden').val());
				
				form_data.append('image2_rotation', angle);

				$.ajax({
					type: 'POST',
					url: 'save_task_image.php',
					data: form_data,
					cache: false,
					processData: false,
					contentType: false,
					success: function(data){ 	   
					}	
				});
			};

			_image2.src = imageSrc;
		}
	}
	
	$('input[name="rotation-image1"]').on('change', function (){
		let angle = $(this).val();
		let imageSrc = preview_image1.attr('src'); 
		loadTaskImage('image1',angle,imageSrc);
	});
	
	$('input[name="rotation-image2"]').on('change', function (){
		let angle = $(this).val();
		let imageSrc = preview_image2.attr('src'); 
		loadTaskImage('image2',angle,imageSrc);
	});
	
	if($('#id').val() > 0) {
       is_appears_img1 = $('#is_appears_img1_hidden').val();
	   is_appears_img2 = $('#is_appears_img2_hidden').val();
	}
	
	$("#is_appears_img1").change(function(){
		if(this.checked) {
		   is_appears_img1 = 1;
		}
		else {
		   is_appears_img1 = 0;
		}
	});
	
	$("#is_appears_img2").change(function(){
		if(this.checked){
		   is_appears_img2 = 1;
		}
		else {
		   is_appears_img2 = 0;
		}
	});
	
	$("#task_creation_date").change(function(){
	   let selectedDate = $(this).val();
	   
	   if(selectedDate) {
		  let currentDate = new Date(selectedDate);
		  let futureDate = new Date(currentDate);	 
		  futureDate.setDate(currentDate.getDate() + 7);
	  
		  let year = futureDate.getFullYear();
		  let month = String(futureDate.getMonth() + 1).padStart(2, '0');
		  let day = String(futureDate.getDate()).padStart(2, '0');
		  
		  let formattedFutureDate = year + '-' + month + '-' + day;
		  $(this).val(selectedDate);
		  $('#destination_date').val(formattedFutureDate);
	   }
	});
});

function setFieldDirection(lang){
	if(lang == 'EN'){
		$('#subject,#area,#description').css({'direction':'ltr','text-align':'left','padding-left':'10px','cursor':'pointer'});
	    $('#he').removeClass('font-weight-bold');
		$('#he').removeClass('colorGreen');
		$('#en').addClass('font-weight-bold');
		$('#en').addClass('colorGreen');
	}
	else {
		$('#subject,#area,#description').css({'direction':'rtl','text-align':'right','padding-right':'10px','cursor':'pointer'});
		$('#en').removeClass('font-weight-bold');
		$('#en').removeClass('colorGreen');
		$('#he').addClass('font-weight-bold');
		$('#he').addClass('colorGreen');
	}
}

function deleteTaskImage(image){
    let form_data = new FormData();
	form_data.append('image',image);
	form_data.append('id_meeting',$('#id').val());
	
	$.ajax({
		type: 'POST',
		url: 'delete_task_image.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,			
		success: function(data){			
		    $('#div-'+image).hide();
		},
	});				
}

function makeOperations(from,id_record){
	if(from == 'responsible' && $('#id').val() == 0) {
		let form_data = new FormData();	
		form_data.append('id_project',$('#project_id').val());
		form_data.append('id_responsible',id_record);
	
		$.ajax({
			type: 'POST',
			url: 'set_kind_of_task.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,			
			success: function(data){ 
			   $('#task').val(data);	
               $('#task').css("background-color",$('#filled_bgcolor').val());			   
			},
		});
	}	
}

function remarkClear(){
    let form_data = new FormData();	
	form_data.append('meeting_id',$('#id').val());
	form_data.append('id_task_action',$('#id_task_action').val());	
	form_data.append('id_progress_status',$('#progress_status').val());	
    form_data.append('id_user',$('#id_user').val());	
		
	$.ajax({
		type: 'POST',
		url: 'add_empty_remark.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,			
		success: function(data){
			window.location.reload();		
		},
	});	
	
	$('#remark').html('');
}

function saveMeetingData(share_doc){
	if (image1_compressing || image2_compressing) {
		alert('Veuillez patienter, le traitement de l\'image est en cours...');
		return;
	}

	let action = 'חדשה';
	
	let subject = $('#subject').text().trim();
	let area = $('#area').text().trim();
	let description = $('#description').html();
	let is_agrees = $("#is_agrees").prop("checked") ? 1 : 0;
	let is_reminds = $("#is_reminds").prop("checked") ? 1 : 0;
	let id_progress_status;
	
	let form_data = new FormData();
    form_data.append('id', $('#id').val());
	
	if($('#id').val() == 0){
		let id_rdv = '';
		if($('#id_rdv').val().length > 0)
			id_rdv = $('#id_rdv').val();
		if($('#id_rdv_report').val().length > 0)
			id_rdv = $('#id_rdv_report').val();
		form_data.append('id_rdv', id_rdv);
		
		id_progress_status = $('#progress_status').val();
	}
   
    let task_creation_date = $('#task_creation_date').val();
    if($('#id').val() > 0){
		action = 'עריכה';
		
		let ids_rdv_checked = $('input[name="rdvs[]"]:checked').map(function(){
           return $(this).val();
        }).get().join(',');
		
		id_progress_status = $('#progress_status_hidden').val();
		
		form_data.append('ids_rdv_checked', ids_rdv_checked);
        task_creation_date = $('#task_creation_date_hidden').val();
        form_data.append('all_ids_to_edit', $('#all_ids_to_edit').val());
        form_data.append('update_type', 'regular_update');
    }
	
	form_data.append('action', action);
	form_data.append('id_project', $('#project_id').val());
	form_data.append('id_chapter', $('#chapter').val());
	form_data.append('subject', subject);
	form_data.append('area', area);
	form_data.append('description', description);
	form_data.append('id_task', $('#task').val());
	form_data.append('id_responsible', $('#responsible').val());
	form_data.append('id_pass_on', $('#pass_on').val());
	form_data.append('task_creation_date', task_creation_date);
	form_data.append('destination_date', $('#destination_date').val());
	form_data.append('id_progress_status', id_progress_status);
	form_data.append('image1', image1 || '');
    form_data.append('is_appears_img1', is_appears_img1);
    form_data.append('image2', image2 || '');
    form_data.append('is_appears_img2', is_appears_img2);
	form_data.append('lang', $('#lang').val());
	form_data.append('is_agrees', is_agrees);
	form_data.append('is_reminds', is_reminds);

	$.ajax({
		type: 'POST',
		url: 'meeting_insert.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,
		beforeSend: function(){
			$('#save_btn').prop('disabled', true);
			$("#progress-popup").show();
			let progress = 0;
			let interval = setInterval(function(){
				if (progress < 90) {
					progress += 10;
					$("#progress-bar").css("width", progress + "%");
				}
			}, 200);
			$("#progress-popup").data("interval", interval);
		},
		success: function(data){
			clearInterval($("#progress-popup").data("interval"));
			$("#progress-popup").hide();
			$('#save_btn').prop('disabled', false);
			if(data == 'empty'){
				checkEmptyFields();
			} else if (data == 'filesizeexceeded'){
				alert('Image file size exceeded!');
			} else {
				handleSuccess($('#id').val(),share_doc,data);
			}
		},
		error: function(){
			clearInterval($("#progress-popup").data("interval"));
			$("#progress-popup").hide();
			$('#save_btn').prop('disabled', false);
			alert('Error...');
		}
	});
}

function checkEmptyFields(){
    if ($('#chapter').val() == 0)
        $('#chapter').css('border-color', 'red');
    else
        $('#chapter').css('border-color', 'initial');
    
    if ($('#subject').val().length == 0)
        $('#subject').css('border-color', 'red');
    else
        $('#subject').css('border-color', 'initial');
    
    if ($('#area').val().length == 0)
        $('#area').css('border-color', 'red');
    else
        $('#area').css('border-color', 'initial');
    
    if ($('#task').val() == 0)
        $('#task').css('border-color', 'red');
    else
        $('#task').css('border-color', 'initial');
    
    if ($('#responsible').val() == 0)
        $('#responsible').css('border-color', 'red');
    else
        $('#responsible').css('border-color', 'initial');
    
    if ($('#pass_on').val() == 0)
        $('#pass_on').css('border-color', 'red');
    else
        $('#pass_on').css('border-color', 'initial');
    
    $('#div_message_alert_down').html("<span style=color:red;font-size:13px;>Please fill all the mandatory fields</span>");
}

function redirectUrl(meeting_id){
	const url = new URL(window.location.href);
    const rowParam = url.searchParams.get('row');
	
	let iteration = $('#iteration').val();
	if($('#iteration').val() == '')
		iteration = 1;
	
    if(meeting_id.includes('inserted_')) 
		meeting_id = meeting_id.replace('inserted_','');
	
	localStorage.setItem('is_modal_task_actions_opened',true);
	localStorage.setItem('project_id',$('#project_id').val());		
	localStorage.setItem('meeting_id',meeting_id);	
	localStorage.setItem('iteration',iteration);
	localStorage.setItem('subject',$('#hidden_name').val());
	localStorage.setItem('area',$('#hidden_area').val());
	localStorage.setItem('recipient',$('#responsible_email').val());
	localStorage.setItem('responsible_id',$('#hidden_responsible_id').val());
	localStorage.setItem('is_priority',$('#hidden_is_priority').val());
	localStorage.setItem('remark',$('#hidden_remark').val());
	localStorage.setItem('track_type',$('#hidden_track_type').val());
	localStorage.setItem('updated_id','meeting_'+meeting_id);
	
	if ($('#fromResumeRdvs').val() > 0){
		let form_data = new FormData();
        form_data.append('id_project', $('#project_id').val());
        form_data.append('id_rdv', $('#id_rdv').val());

		$.ajax({
			type: 'POST',
			url: 'setSessionRdv.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,
			success: function(data2){
				let url1 = 'meetings.php?project_id=' + $('#project_id').val();
				if ($('#is_specific_filter').val())
					url1 += '&is_specific_filter=1';
				
				if ($('#id_rdv').val() > 0)
					url1 += '&id_rdv=' + $('#id_rdv').val();
				
				location.href = url1;
			},
		});
	} 
	else if ($('#fromProjectHome').val() > 0){
		let url2 = 'project_home.php?id='+$('#project_id').val();
		location.href = url2;	
	} 
	else if ($('#fromProjectsList').val() > 0){			
		let url3 = 'projects.php';
		location.href = url3;			
	}
	else {
		let url4 = 'meetings.php?project_id='+$('#project_id').val();
		if ($('#id').val() > 0)
			url4 += '&row=meeting_'+meeting_id;
		if ($('#is_specific_filter').val())
			url4 += '&is_specific_filter=1';
		if ($('#id_rdv').val() > 0)
			url4 += '&id_rdv='+$('#id_rdv').val();		
		location.href = url4;	
	}
}

function handleSuccess(meeting_id,share_doc,_inserted_meeting_id){
    if (!share_doc){
		if(_inserted_meeting_id == 'updated')
			redirectUrl(meeting_id);
	    else 
			redirectUrl(_inserted_meeting_id);
    } 
	else {
        let form_data = new FormData();
		let inserted_meeting_id = _inserted_meeting_id.replace('inserted_','');
		let iteration = inserted_meeting_id;
		
        if($('#id').val() > 0) {	
		   inserted_meeting_id = $('#id').val();
		   _inserted_meeting_id = $('#iteration').val();
		}
		  
		form_data.append('id_meeting',inserted_meeting_id);
	
		$.ajax({
			type: 'POST',
			url: 'task_details.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,
			success: function(data){
				let task_details = data.split('|~|');
				let content = fillContentTaskDetails(inserted_meeting_id,'',task_details,false);
				$('#contentToScreenshot').html(content);				
					
				const element = document.getElementById('contentToScreenshot');
				const width = element.offsetWidth;
				const height = element.offsetHeight;

				html2canvas(element,{
				scale: 1 
				}).then(function(canvas){
				let finalCanvas = document.createElement('canvas');
				finalCanvas.width = width;
				finalCanvas.height = height;
						
				let ctx = finalCanvas.getContext('2d');
				ctx.drawImage(canvas,0,0,width,height);
						
				const imageData = canvas.toDataURL('image/jpeg');

				$.ajax({
					type: 'POST',
					url: 'save_image.php',
					data: {imageData:imageData,meeting_id:inserted_meeting_id},
					success: function(response){
						    const imageUrl = '<?= BASE_URL ?>'+response;
							shareImage(imageUrl,inserted_meeting_id,$('#project_id').val(),_inserted_meeting_id,0);						
					}, 
				});
			  });
		   },
	   });
    }
}

$('#save_btn').on('click', function(){
    saveMeetingData(false);
});

$('#send_whatsapp_btn').click (function (e){ 
   saveMeetingData(true);
});

$('#cancel_btn').click(function(){
    if($('#fromResumeRdvs').val()){
	   let url1 = 'meetings.php?project_id='+$('#project_id').val();
	   if($('#is_specific_filter').val())
		   url1+= '&is_specific_filter=1';
	   
	   if($('#id_rdv').val() > 0)
		   url1+= '&id_rdv='+$('#id_rdv').val();
	 
	   location.href = url1;   
    }
	else if(($('#fromProjectsList').val() == 1)){
		let url2 = 'projects.php';
		location.href = url2;	
	}
	else {
		let url3 = 'meetings.php?project_id='+$('#project_id').val();
		if($('#id').val() > 0)
			url3 += '&row=row_'+$('#iteration').val();
		if($('#is_specific_filter').val())
		   url3+= '&is_specific_filter=1';
		if($('#id_rdv').val() > 0)
		  url3+= '&id_rdv='+$('#id_rdv').val();
		location.href = url3;	
	}
})
</script>

<style>
.rtl-textarea {
  direction: rtl;        
  text-align: right;      
  vertical-align: top;   
  height: 100px;
  width: 100%;
  resize: vertical;
}

.btn:hover {
   color: white;
}

.bgColorBlack:hover {
	background-color: #45484d;
}

.bgColorBlue:hover {
	background-color:#3370d6;
}

#a_project_title:hover {
	color: grey;
}

.editable {
    max-height: 100px;
    overflow-y: auto; 
    padding: 8px;
    background-color: transparent; 
}

#progress-popup {
	display: none;
	position: fixed;
	top: 50%;
	left: 50%;
	transform: translate(-50%,-50%);
	width: 300px; 
	background: white; 
	border: 1px solid #ccc; 
	border-radius: 8px; 
	padding: 20px; 
	box-shadow: 0 4px 8px rgba(0,0,0,0.1); 
	text-align: center; 
	z-index: 1000;
}

#div-progress-bar {
	width: 100%; 
	background: #f0f0f0; 
	border-radius: 5px; 
	overflow: hidden; 
	margin-top: 10px;
}

#progress-bar {
	width: 0%; 
	height: 20px; 
	background: #4caf50;
}

.whatsapp-btn {
    background-image: url('images/share-icon.svg');
    background-repeat: no-repeat;
    background-position: left center;
    background-size: 35px 25px;     
    padding-left: 35px;        
}

@media (max-width: 1500px) {
    .width90Percents {
	   max-width: 300px;
    }
}

@media (max-width: 1024px) {
	.text-size {
	   font-size: 0.8rem;
    }
}

@media (max-width: 768px) {
	.text-size {
	   font-size: 0.7rem;
    }
	
	.width-one-of-three {
        width: 100%;
        margin-bottom: 1rem;
    }
	
	.width-one-of-three select {
        width: 90%;       
        max-width: 200px; 
        margin: 0 auto;   
        display: block; 
    }
}

@media (max-width: 640px) {
	.text-size {
	   font-size: 0.6rem;
    }
}

@media (max-width: 520px) {
	.text-size {
	   font-size: 0.5rem;
    }
}
</style>