<?php
include 'include/header.php';
include 'functions/functions.php';

$query = $mysqli->prepare("SELECT * FROM dne_logos LIMIT 1");
$query->execute();
$query->store_result();
$logo = fetch_unique($query);

$id = @$_GET['id'];
$period_new_task_filter = @$_GET['period_new_task_filter'];
$period_late_filter = @$_GET['period_late_filter'];
$task_filter = @$_GET['task_filter'];
$progress_status_filter = @$_GET['progress_status_filter'];
$supplier_filter = @$_GET['supplier_filter'];
$is_specific_filter = @$_GET['is_specific_filter'];
$from = @$_GET['from'];
$margin_top = 'marginTop20';

if($id > 0){ 
	$query = $mysqli->prepare("SELECT * FROM dne_projects WHERE id = ?");
    $query->bind_param("i",$id);
	$query->execute();
	$query->store_result();
	$project = fetch_unique(@$query);
	
	$etrp_type = 'E';
	
	$query = $mysqli->prepare("SELECT s.name_he,s.nickname_he,s.email_office
	                          FROM dne_projects_suppliers ps
							  LEFT JOIN dne_projects p ON p.id = ps.id_project
							  LEFT JOIN dne_suppliers s ON s.id = ps.id_supplier
							  WHERE p.id = ? and s.type = ?");
    $query->bind_param("is",$project->id,$etrp_type);
    $query->execute();
	$query->store_result();
	$etrp = fetch_unique($query);	
	
	$margin_top = 'marginTop5';
}

$query = $mysqli->prepare("SELECT bgcolor FROM dne_global_bgcolor_new_task LIMIT 1");
$query->execute(); 
$query->store_result();
$bg_color_new_task = fetch_unique($query);

$title_add_project_btn = 'Add new project';
$project_name_label = "Project name";
$project_name_he_label = "Project name He";
$project_nickname_label = "Project nickname";
$project_client1_label = 'Project client 1';
$project_client2_label = 'Project client 2';
$project_entrepreneur_name_label = "Project entrepreneur name";
$project_entrepreneur_nickname_label = "Project entrepreneur nickname";
$period_new_tasks_label = "Mark new tasks created until before:";
$do_not_mark_label = "Do not mark";
$today_label = "Today";
$three_days_label = "3 days";
$one_week_label = "1 week";
$two_weeks_label = "2 weeks";
$one_month_label = "1 month";
$two_months_label = "2 months";
$one_year_label = "1 year";
$two_years_label = "2 years";
$is_project_active_label = 'Is project active?';
$yes_label = "Yes";
$no_label = "No";
$title_save_btn = 'Save';
$title_cancel_btn = 'Cancel';
$alert_mandat_fields_msg = 'Please fill all the mandatory fields.';

if($from == 'taskslist')
	include 'menu_tasks.php';
?>          

		<form method="" action="" enctype="multipart/form-data" class="form-inline">
			<input type="hidden" id="id" value="<?=@$id?>" />
			<input type="hidden" id="task_filter" value="<?=@$task_filter?>" />
			<input type="hidden" id="progress_status_filter" value="<?=@$progress_status_filter?>" />
			<input type="hidden" id="supplier_filter" value="<?=@$supplier_filter?>" />
			<input type="hidden" id="period_new_task_filter" value="<?=@$period_new_task_filter?>" />
			<input type="hidden" id="period_late_filter" value="<?=@$period_late_filter?>" />
			<input type="hidden" id="is_specific_filter" value="<?=@$is_specific_filter?>" />
            <input type="hidden" id="from" value="<?=@$from?>" />			

			<div class="container">
			    <div class="row alignCenter marginTop25">
					<div class="col-12">
						<a href="projects.php"><img src="images/<?=@$logo->logo?>" width="120" height="114" /></a>
					</div>
			    </div>
				
				<div class="row marginTop10 title alignCenter dir-rtl">
					<?php
					if($id == 0) { ?>
						<div class="col-12 fontSize26 color-1A5276 font-family-david">
							הוסף פרוייקט חדש
						</div>
					<?php } 
					else if($id > 0){
					?>
					<div class="col-12">
						<a id="a_project_title" class="text-decoration-none color-1A5276" href="project_home.php?id=<?=@$id?>">
							<div class=" fontSize33 font-weight-bold line-height-1em">
								<?=@$project->nickname?>
							</div>
							<div class="fontSize26 font-family-david font-weight-bold">
								פרוייקט <?=@$project->name_he?>
							</div>
						</a>
					</div>
					<?php } ?>
				</div>
				
				<div class="row marginTop5">
				    <div class="col-1"></div>
					<div class="col-10 card bg-f2f6f9 border-frame borderRadius15 padding0">
						<div class="card-body">
							<div class="flex justify-content-space-around flex-wrap marginTop10 dir-rtl">
								<div class="width-one-of-three">   
									<strong>שם פרוייקט בעברית</strong>
								</div>
								<div class="width-one-of-three paddingLeft3Percents dir-ltr">
									<strong>Project Name</strong>													
								</div>
								<div class="width-one-of-three paddingLeft3Percents dir-ltr">   
									<strong>Project Nickname</strong>
									<div class="fontSize13">
									   (up to 7 characters)
									</div>				
								</div>
							</div>
						
							<div class="flex justify-content-space-around flex-wrap dir-rtl">
								<div class="width-one-of-three">
									<div class="row marginTop10">
										<div class="col-12">										
											<input type="text" class="paddingRight8 width90Percents height30" name="project_name_he" id="project_name_he" placeholder="*שם פרוייקט בעברית" value="<?=htmlspecialchars(@$project->name_he)?>" />
										</div>
									</div> 	
								</div>
								<div class="width-one-of-three">
									<div class="row marginTop10">
										<div class="col-12">
											 <input type="text" class="paddingLeft8 width90Percents height30 alignLeft" name="project_name" id="project_name" placeholder="*Project Name" value="<?=@$project->name?>" />
										</div>
									</div>
								</div>	
								<div class="width-one-of-three">
									<div class="row marginTop10">
										<div class="col-12">										
											<input type="text" class="paddingLeft8 width90Percents height30 alignLeft" name="project_nickname" id="project_nickname" placeholder="*כינוי פרוייקט" value="<?=@$project->nickname?>" />
										</div>
									</div> 
								</div>
							</div>
									
							<div class="flex justify-content-space-around flex-wrap marginTop20 dir-rtl">	
								<div class="width-one-of-three">
									<strong>שם היזם/קליאנט</strong>													
								</div>
								<div class="width-one-of-three paddingLeft3Percents dir-ltr">
									<strong>Client Nickname</strong>
									<div class="fontSize13">
									   (up to 7 characters)
									</div>								
								</div>
								<div class="width-one-of-three paddingLeft3Percents dir-ltr">
									<strong>Client e-mail</strong>								
								</div>
							</div>
							
							<div class="flex justify-content-space-around flex-wrap dir-rtl">	
								<div class="width-one-of-three">
									<div class="row marginTop5">
										<div class="col-12">										
											<input type="text" class="paddingRight8 width90Percents height30" name="project_initiator_name" id="project_initiator_name" placeholder="שם היזם" value="<?=@$etrp->name_he?>" />
										</div>
									</div> 		
								</div>
								<div class="width-one-of-three">
									<div class="row marginTop10">
										<div class="col-12">	
											<input type="text" class="paddingLeft8 width90Percents height30 alignLeft" name="project_initiator_nickname" id="project_initiator_nickname" placeholder="כינוי היזם" value="<?=@$etrp->nickname_he?>" />
										</div>
									</div>		
								</div>
								<div class="width-one-of-three">
									<div class="row marginTop10">
										<div class="col-12">	
											<input type="text" class="paddingLeft8 width90Percents height30 alignLeft" name="project_initiator_email" id="project_initiator_email" placeholder="דוא''ל היזם" value="<?=@$etrp->email_office?>" />
										</div>
									</div>		
								</div>
							</div>						
						
							<?php if($id > 0 ) { ?>
								<div class="row marginTop10 alignCenter dir-rtl">
									<div class="col-12">
									   <input type="radio" id="is_project_active_1" name="is_project_active" value="1" <?php if($id == 0 || ($id > 0 && $project->is_project_active == 1)) echo "checked";?> />&nbsp; פרוייקט פעיל &nbsp;
									   <input type="radio" id="is_project_active_2" name="is_project_active" value="0" <?php if($id == 0 || ($id > 0 && $project->is_project_active == 0)) echo "checked";?> />&nbsp; פרוייקט לא פעיל &nbsp;
									</div>
								</div>
							<?php } ?>

							<div class="row marginTop10 alignCenter dir-rtl">
								<div class="col-12">
									<strong>שפת ברירת מחדל</strong>&nbsp;
									<select id="project_lang" class="height30 marginRight8">
										<option value="HE" <?php if($id == 0 || (@$project->lang == '' || @$project->lang == 'HE')) echo 'selected';?>>עברית</option>
										<option value="EN" <?php if(@$project->lang == 'EN') echo 'selected';?>>English</option>
									</select>
								</div>
							</div>
																							
							<div class="row alignCenter dir-rtl">
								<div class="col-12">
									<div id="div_message_alert_down"></div>	
									<input type="button" id="save_btn" name="save_btn" class="btn marginTop20 colorWhite bgColorBlue mb-2"
									value="<?php if(@$id == 0) echo "עבור לצוות ניהול";else echo "שמור"?>" />	
									<input type="button" id="cancel_btn" class="btn marginTop20 colorWhite bgColorBlack marginRight8 mb-2" value="ביטול" />								
								</div>
							</div>
                         </div>							
					</div>
					<div class="col-1"></div>
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
$('#save_btn').click (function (e){
	let form_data = new FormData();	
	form_data.append('id',$('#id').val());
	form_data.append('project_name',$('#project_name').val());
	form_data.append('project_name_he',$('#project_name_he').val());
	form_data.append('project_nickname',$('#project_nickname').val());
	form_data.append('project_initiator_name',$('#project_initiator_name').val());
	form_data.append('project_initiator_nickname',$('#project_initiator_nickname').val());
	form_data.append('project_initiator_email',$('#project_initiator_email').val());
	form_data.append('is_project_active',$('input[name="is_project_active"]:checked').val());
	form_data.append('project_lang',$('#project_lang').val());
	$.ajax({
		type: 'POST',
		url: 'project_insert.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,
		beforeSend: function(){
			$("#progress-popup").show();
			let progress = 0;
			let interval = setInterval(function(){
				if (progress < 90){
					progress += 10;
					$("#progress-bar").css("width", progress + "%");
				} else {
					clearInterval(interval);
				}
			}, 200);
			$("#progress-popup").data("interval", interval);
		},          			
		success: function(data){ 	
			if(data == 'hebrewchars') 
				alert('The project name contains hebrew characters.');
			else if(data == 'englishchars')
				alert('The project hebrew name contains english characters.');
			else if(data == 'uptosevencharacters') 
				alert('The project nickname or the project initiator nickname up to 7 characters.');
			else if(data == 'empty') {
				if($('#project_name').val().length == 0)			
					$('#project_name').css('border-color','red');
				else if(!($('#project_name').val().length == 0))
					$('#project_name').css('border-color','initial');	
					
				if($('#project_name_he').val().length == 0)			
					$('#project_name_he').css('border-color','red');
				else if(!($('#project_name_he').val().length == 0))
					$('#project_name_he').css('border-color','initial');	
				
				if($('#project_nickname').val().length == 0)			
					$('#project_nickname').css('border-color','red');
				else if(!($('#project_nickname').val().length == 0))
					$('#project_nickname').css('border-color','initial');	
				
				$('#div_message_alert_down').html("<span style=color:red;font-size:13px;>נא למלאות את השדות החובות</span>"); 
			}
			else {
				let id_project = $('#id').val();
				if(data !== 'updated'){
					id_project = data;
				}

				if($('#from').val() === 'taskslist'){
					location.href = 'meetings.php?project_id='+id_project
							        +'&task_filter='+$('#task_filter').val()
							        +'&progress_status_filter='+$('#progress_status_filter').val()
							        + '&supplier_filter='+$('#supplier_filter').val()
							        + '&period_new_task_filter='+$('#period_new_task_filter').val()
							        + '&period_late_filter='+$('#period_late_filter').val()
							        + '&is_specific_filter='+$('#is_specific_filter').val();
				} 
				else {
					if($('#id').val() == 0){
						location.href = 'add_responsible.php?id=0&project_id='+id_project+'&from=addProject&for=admingroup';
					} else {
						location.href = 'projects.php';
					}
                }
			}				
		},
	});												       			   
});

$('#cancel_btn').click(function(){
    ($('#from').val() == 'taskslist')? location.href = 'meetings.php?project_id='+$('#id').val()+'&task_filter='+$('#task_filter').val()+'&progress_status_filter='+$('#progress_status_filter').val()+'&supplier_filter='+$('#supplier_filter').val()+'&period_new_task_filter='+$('#period_new_task_filter').val()+'&period_late_filter='+$('#period_late_filter').val()+'&is_specific_filter='+$('#is_specific_filter').val():location.href = 'projects.php';
})
</script>

<style>
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
</style>