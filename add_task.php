<?php
include 'include/header.php';
include 'functions/functions.php';

$id = @$_GET['id'];
$project_id = @$_GET['project_id'];
$query = $mysqli->prepare("SELECT * FROM dne_tasks WHERE id = ? 
                          AND id_project = ?");
$query->bind_param("ii",$id,$project_id);
$query->execute();
$query->store_result();
$task = fetch_unique($query);

$query = $mysqli->prepare("SELECT * FROM dne_projects WHERE id = ?");
$query->bind_param("i",$project_id );
$query->execute();
$query->store_result();
$project = fetch_unique($query);   

$title = 'הוסף סוג משימה';

if(@$id > 0)
	$title = @$task->name_he;

include 'menu_tasks.php';
?>

		<form method="post" action="" enctype="multipart/form-data" class="form-inline">
			<input type="hidden" id="id" value="<?=@$id?>" />
			<input type="hidden" id="project_id" value="<?=@$project_id?>" />	

			<div class="container">
			    <div class="row alignCenter marginTop25">
					<div class="col-12">
						<a href="projects.php"><img src="images/davidnahmias_logo.png" width="170px" height="170px" /></a>
					</div>
			    </div>
			   
			    <div class="row marginTop15 title dir-rtl">	
					<div class="col-12">
						<a id="a_project_title" class="text-decoration-none color-1A5276" href="meetings.php?project_id=<?=@$project_id?>">
						    <strong class="fontSize33 line-height-1em"><?=@$project->nickname?></strong>
							<div class="fontSize26 font-family-david">פרוייקט <?=@$project->name_he?></div>
					    </a>		
					</div>				
			    </div>
				
		        <div class="row marginTop10">
				    <div class="col-1"></div>
				    <div class="col-10 card bg-f2f6f9 border-frame borderRadius15 padding0">
						<div class="card-header bgColor-1a5276 colorWhite alignCenter fontSize20" style="border-top-left-radius:15px;border-top-right-radius:15px;"><strong><?=@$title?></strong></div>	   
				        <div class="card-body">
							<div class="flex margin-top-15-x-auto width50Percents dir-rtl">
								<div class="width50Percents">	
									<strong class="text-size">שם</strong>
									<br/>	
									<input type="text" class="paddingRight10 marginTop5 width160" name="name" id="name" placeholder="*שם" value="<?=@$task->name?>" />	
								</div>
				
								<div class="width50Percents">	
									<strong>שם בעברית</strong>
									<br/>	
									<input type="text" class="paddingRight10 marginTop5 width160" name="name_he" id="name_he" placeholder="*שם בעברית" value="<?=@$task->name_he?>" 
									<?php if(@$id > 0 && (@$task->name_he == 'תכנון' || @$task->name_he == 'ביצוע' || @$task->name_he == 'ניהול' || @$task->name_he == 'בקרת איכות' || @$task->name_he == 'הנחיית ביצוע' || @$task->name_he == 'סטטוס ביצוע' || @$task->name_he == 'בקשה/שאילתה')) echo "readonly";?> />		
								</div>
							</div>
				
							<div class="flex margin-top-15-x-auto width50Percents dir-rtl">
								<div class="width50Percents">
									<strong>צבע גופן</strong>
									<br/>						
									<input type="color" class="marginTop5 width160" name="color" id="color" placeholder="צבע גופן" value="<?=@$task->color?>" />
								</div>
								
								<div class="width50Percents">
									<strong>צבע רקע</strong>
									<br/>						
									<input type="color" class="marginTop5 width160" name="bgcolor" id="bgcolor" placeholder="צבע רקע" value="<?php if($id == 0) echo '#ffffff';else echo @$task->bgcolor;?>" />
								</div>
							</div>
				
							<div class="flex margin-top-15-x-auto width50Percents dir-rtl">
								<div class="width50Percents">
									<strong>עמודי רקע</strong>
									<br/>						
									<div class="marginTop5 paddingRight10 width80Percents border-black">
										<div class="max-height-120 text-size overflow-y-scroll">
											<div class="flex">
												<div class="paddingTop5 width20Percents">
													<input type="checkbox" id="bgcolor_subject" name="bgcolor_columns[]" value="subject" <?php if(strpos(@$task->bgcolor_columns,'subject') !== false) echo 'checked';?> />  
												</div>
												<div class="width80Percents">
													נושא/תחום
												</div>
											</div>
										   
											<div class="flex">
												<div class="paddingTop5 width20Percents">
													<input type="checkbox" id="bgcolor_area" name="bgcolor_columns[]" value="area" <?php if(strpos(@$task->bgcolor_columns,'area') !== false) echo 'checked'; ?> />  
												</div>
												<div class="width80Percents">
													איזור/נושא
												</div>
											</div>
										   
											<div class="flex">
												<div class="paddingTop5 width20Percents">
													<input type="checkbox" id="bgcolor_description" name="bgcolor_columns[]" value="description" <?php if(strpos(@$task->bgcolor_columns,'description') !== false) echo 'checked'; ?> />  
												</div>
												<div class="width80Percents">
													תיאור
												</div>
											</div>

											<div class="flex">
												<div class="paddingTop5 width20Percents">
													<input type="checkbox" id="bgcolor_task" name="bgcolor_columns[]" value="_task" <?php if(strpos(@$task->bgcolor_columns,'_task') !== false) echo 'checked';?> />  
												</div>
												<div class="width80Percents">
													 סוגי משימה
												</div>
											</div>									    
																		   
											<div class="flex">
												<div class="paddingTop5 width20Percents">
													<input type="checkbox" id="bgcolor_responsible" name="bgcolor_columns[]" value="responsible" <?php if(strpos(@$task->bgcolor_columns,'responsible') !== false) echo 'checked'; ?> />  
												</div>
												<div class="width80Percents">
													 אחראיים
												</div>
											</div>
											
											<div class="flex">
												<div class="paddingTop5 width20Percents">
													<input type="checkbox" id="bgcolor_pass_on" name="bgcolor_columns[]" value="pass on" <?php if(strpos(@$task->bgcolor_columns,'pass on') !== false) echo 'checked'; ?> />  
												</div>
												<div class="width80Percents">
													 להעביר ל/לאשר מול
												</div>
											</div>
											
											<div class="flex">
												<div class="paddingTop5 width20Percents">
													<input type="checkbox" id="bgcolor_task_creation" name="bgcolor_columns[]" value="task creation" <?php if(strpos(@$task->bgcolor_columns,'task creation') !== false) echo 'checked'; ?> />  
												</div>
												<div class="width80Percents">
													 תאריך יצירת משימה
												</div>
											</div>
											
											<div class="flex">
												<div class="paddingTop5 width20Percents">
													<input type="checkbox" id="bgcolor_destination_date" name="bgcolor_columns[]" value="destination date" <?php if(strpos(@$task->bgcolor_columns,'destination date') !== false) echo 'checked'; ?> />  
												</div>
												<div class="width80Percents">
													 תאריך יעד
												</div>
											</div>
											
											<div class="flex">
												<div class="paddingTop5 width20Percents">
													<input type="checkbox" id="bgcolor_progress_status" name="bgcolor_columns[]" value="progress status" <?php if(strpos(@$task->bgcolor_columns,'progress status') !== false) echo 'checked'; ?> />  
												</div>
												<div class="width80Percents">
													 סטטוס התקדמות
												</div>
											</div>
										</div>
									</div>
								</div>
								
								<div class="width50Percents">
									<strong>עמודי טקסט</strong>
									<br/>						
									<div class="marginTop5 paddingRight10 width80Percents border-black">
										<div class="max-height-120 text-size overflow-y-scroll">
											<div class="flex">
												<div class="paddingTop5 width20Percents">
													<input type="checkbox" id="text_subject" name="text_columns[]" value="subject" <?php if(strpos(@$task->text_columns,'subject') !== false) echo 'checked';?> />  
												</div>
												<div class="width80Percents">
													נושא/תחום
												</div>
											</div>
										   
											<div class="flex">
												<div class="paddingTop5 width20Percents">
													<input type="checkbox" id="text_area" name="text_columns[]" value="area" <?php if(strpos(@$task->text_columns,'area') !== false) echo 'checked';?> />  
												</div>
												<div class="width80Percents">
													איזור/נושא
												</div>
											</div>
										   
											<div class="flex">
												<div class="paddingTop5 width20Percents">
													<input type="checkbox" id="text_description" name="text_columns[]" value="description" <?php if(strpos(@$task->text_columns,'description') !== false) echo 'checked';?> />  
												</div>
												<div class="width80Percents">
													תיאור
												</div>
											</div>

											<div class="flex">
												<div class="paddingTop5 width20Percents">
													<input type="checkbox" id="text_task" name="text_columns[]" value="_task" <?php if(strpos(@$task->text_columns,'_task') !== false) echo 'checked';?> />  
												</div>
												<div class="width80Percents">
													 סוגי משימה
												</div>
											</div>										
																		   
											<div class="flex">
												<div class="paddingTop5 width20Percents">
													<input type="checkbox" id="text_responsible" name="text_columns[]" value="responsible" <?php if(strpos(@$task->text_columns,'responsible') !== false) echo 'checked';?> />  
												</div>
												<div class="width80Percents">
													 אחראיים
												</div>
											</div>
											
											<div class="flex">
												<div class="paddingTop5 width20Percents">
													<input type="checkbox" id="text_pass_on" name="text_columns[]" value="pass on" <?php if(strpos(@$task->text_columns,'pass on') !== false) echo 'checked';?> />  
												</div>
												<div class="width80Percents">
													 להעביר ל/לאשר מול
												</div>
											</div>
											
											<div class="flex">
												<div class="paddingTop5 width20Percents">
													<input type="checkbox" id="text_task_creation" name="text_columns[]" value="task creation" <?php if(strpos(@$task->text_columns,'task creation') !== false) echo 'checked';?> />  
												</div>
												<div class="width80Percents">
													 תאריך יצירת משימה
												</div>
											</div>
											
											<div class="flex">
												<div class="paddingTop5 width20Percents">
													<input type="checkbox" id="text_destination_date" name="text_columns[]" value="destination date" <?php if(strpos(@$task->text_columns,'destination date') !== false) echo 'checked';?> />  
												</div>
												<div class="width80Percents">
													 תאריך יעד
												</div>
											</div>
											
											<div class="flex">
												<div class="paddingTop5 width20Percents">
													<input type="checkbox" id="text_progress_status" name="text_columns[]" value="progress status" <?php if(strpos(@$task->text_columns,'progress status') !== false) echo 'checked';?> />  
												</div>
												<div class="width80Percents">
													 סטטוס התקדמות
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>

							<div class="row marginTop20 alignCenter dir-rtl">
								<div class="col-12">
									<div id="div_message_alert_down"></div>	
									<div class="marginTop10">
										<input type="button" id="save_btn" name="save_btn" class="btn bgColorBlue colorWhite mb-2" value="שמור" />		
										<input type="button" id="cancel_btn" class="btn marginRight10 bgColorBlack colorWhite mb-2" value="ביטול" />
									</div>					
								</div>
							</div>
						</div>
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
$('#save_btn').click (function (e){
	let bgcolor_columns = $('input[name="bgcolor_columns[]"]:checked')
    .map(function() {
          return this.value;
    }).get().join(',');
	
	let text_columns = $('input[name="text_columns[]"]:checked')
    .map(function() {
          return this.value;
    }).get().join(',');
	
	
	let form_data = new FormData();	
	form_data.append('id',$('#id').val());
	form_data.append('id_project',$('#project_id').val());
	form_data.append('name',$('#name').val());
	form_data.append('name_he',$('#name_he').val());
	form_data.append('color',$('#color').val());
	form_data.append('bgcolor',$('#bgcolor').val());
	form_data.append('bgcolor_columns',bgcolor_columns);
	form_data.append('text_columns',text_columns);
	
	$.ajax({
		type: 'POST',
		url: 'task_insert.php',
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
            if(data == 'hebrewchars') 
				alert('The name contains hebrew characters.');
			else if(data == 'englishchars')
				alert('The hebrew name contains english characters.');						
			else if(data == 'empty'){
				if($('#name').val().length == 0)			
					$('#name').css('border-color','red');
				else if(!($('#name').val().length == 0))
					$('#name').css('border-color','initial');	
				
				if($('#name_he').val().length == 0)			
					$('#name_he').css('border-color','red');
				else if(!($('#name_he').val().length == 0))
					$('#name_he').css('border-color','initial');	
				
				$('#div_message_alert_down').html("<span style=color:red;font-size:13px;>נא למלה את כל השדות החובות</span>");
			}
			else if(data == 'exists') 
				$('#div_message_alert_down').html("<span style=color:red;font-size:13px;>סוג משימה זה כבר קיים בפרוייקט זה</span>");
			else 
				location.href = 'tasks.php?project_id='+$('#project_id').val();			
		}
	});											       			   
})

$('#cancel_btn').click(function(){
    location.href = "tasks.php?project_id="+$('#project_id').val();	
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

@media (max-width: 1024px) {
	.text-size {
	   font-size: 0.9rem;
    }
}

@media (max-width: 768px) {
	.text-size {
	   font-size: 0.8rem;
    }
}

@media (max-width: 640px) {
	.text-size {
	   font-size: 0.7rem;
    }
}

@media (max-width: 520px) {
	.text-size {
	   font-size: 0.6rem;
    }
}
</style>