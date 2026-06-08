<?php
include 'include/header.php';
include 'functions/functions.php';

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

$query = $mysqli->prepare("SELECT * FROM dne_meetings WHERE id_project =  ? AND id_progress_status <> ?");
$query->bind_param("ii",$project_id,$id_progress_status);
$query->execute();
$query->store_result();
$all_meetings_num_rows = $query->num_rows;
	
$query = $mysqli->prepare("SELECT * FROM dne_responsibles WHERE id_project = ?");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$responsibles = fetch($query);

$query = $mysqli->prepare("SELECT * FROM dne_tasks WHERE id_project = ?");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$tasks = fetch($query);

$query = $mysqli->prepare("SELECT * FROM dne_areas WHERE id_project = ?");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$areas = fetch($query);

$query = $mysqli->prepare("SELECT * FROM dne_progress_status WHERE id_project = ?");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$progress_status_s = fetch($query);
?>

        <form method="post" action="" class="form-inline">
		    <input type="hidden" id="project_id" name="project_id" value="<?=@$project->id?>" />
			
			<div class="container">
			    <div class="row alignCenter marginTop25">
					<div class="col-md-12">
						<a href="projects.php"><img src="images/davidnahmias_logo.png" width="170px" height="170px" /></a>
					</div>
			    </div>
				
			    <div class="row title">	
					<div class="col-md-12">
						<a style="text-decoration:underline;" href="meetings.php?project_id=<?=@$project_id?>">
							<?='<div style=font-size:22px;font-weight:bold>'.@$project->nickname.'</div>'.@$project->name_he.'<br/>רשימת דוחות'?>
					    </a>
					</div>					
			    </div>
			
				<div class="row" style="text-align:center;">
					<div class="col-md-12">
						<input type="button" value="חזור לרשימת המשימות" class="btn marginTop20 marginRight10 mb-2" onclick="location.href='meetings.php?&project_id=<?=@$project_id?>';" />
					</div>
				</div>
				
				<div class="row" style="margin-top:25px;text-align:center;direction:rtl;">
					<div class="col-md-12">
					   <strong style="font-size:16px;">תאריך דיווח:&nbsp;</strong>
					   <input type="date" class="form-inline" id="report_date" name="report_date" value="<?=date('Y-m-d')?>" />
					</div>
				</div>
				
				<div class="row" style="text-align:center;direction:rtl;">
					<div class="col-md-12">
					   <input type="button" id="to_add_meeting_btn" value="הוסך משימה" class="btn marginTop20 mb-2" />
					</div>
				</div>
				
				<br/>

				<?php if($all_meetings_num_rows > 0) { ?>		
					<div class="row" style="font-size:12px;text-align:center;direction:rtl;">
						<div align="center" class="col-md-12 mx-1">
							<table border="1">							
								<tr style="background-color:silver;height:50px;">
									<th width="80px;" colspan="2" width="40px;" style="text-align:center;">&nbsp;</th>
									<th width="120px;" style="text-align:center;">נושא/תחום</th>
									<th width="120px;" style="text-align:center;">איזור/נושא</th>
									<th width="330px;" style="text-align:center;font-size:13px;">תיאור</th>
									<th width="120px;" style="text-align:center;">סוג משימה</th>
									<th width="120px;" style="text-align:center;">אחראי</th>
									<th width="120px;" style="text-align:center;font-size:13px;">להעביר ל/ <br/> לאשר מול</th>
									<th width="75px;" style="text-align:center;">תאריך <br/> יצירת משימה</th>
									<th width="75px;" style="text-align:center;">תאריך יעד</th>
									<th width="125px;" style="text-align:center;">סטטוס <br/> התקדמות</th>
									<th width="30px;" style="text-align:center;">&nbsp;</th>
									<th width="30px;" style="text-align:center;">&nbsp;</th>
								</tr>
			
								<?php
								$query = $mysqli->prepare("SELECT * FROM dne_chapters WHERE id_project = ? ORDER BY id_display");
								$query->bind_param("i",$project_id);
								$query->execute(); 
								$query->store_result();
								$chapters = fetch($query);
								
								foreach($chapters as $item) {	
									$chapter_id = $item->id;
									$is_appears = 1;
									
									$query = $mysqli->prepare("SELECT * FROM dne_meetings 
															  WHERE id_project = ? AND id_chapter = ? AND is_appears = ? AND id_progress_status <> ?
															  ORDER BY subject,id_area,destination_date DESC");
									$query->bind_param("iiii",$project_id,$chapter_id,$is_appears,$id_progress_status);
									$query->execute(); 
									$query->store_result();
									$meetings_num_rows = $query->num_rows;
									$meetings = fetch($query);
									
									if($meetings_num_rows > 0) {
									?>
										<tr style="background-color:#a3def0;height:40px;">
										  <td colspan="13" style="text-align:right;padding-right:5px;">
											  <strong><?=@$item->name?></strong>
										  </td>
										</tr>
										<?php 
										$count = 0;
										foreach($meetings as $item) {
											$meeting_id = @$item->id;
											$id_rdv = @$item->id_rdv;
											$id_chapter = @$item->id_chapter;
											$id_task_type = @$item->id_task_type;
											$subject = @$item->subject;
											$area = @$item->area;
											$description = @$item->description;
											$task_id = @$item->id_task;
											$responsible_id = @$item->id_responsible;
											$pass_on_id = @$item->id_pass_on;
											
											$image1 = @$item->image1;
											$is_appears_img1 = @$item->is_appears_img1;
											
											$bgcolor_num = 'white';
											
											if(@$item->image1 <> '' && @$item->is_appears_img1 == 1) {
											   $bgcolor_num = 'green';
											}
											
											if(@$item->image1 <> '' && @$item->is_appears_img1 == 0) {
											  $bgcolor_num = '#C9FFC2';
											}
											
											$is_change_row_style = @$item->is_change_row_style;
											
											$update_cell_bgcolor = 'background-color:white';
											
											$task_creation_date = '';
											if(@$item->task_creation_date != '0000-00-00')
												$task_creation_date = @$item->task_creation_date;
											
											$destination_date = '';
											if(@$item->destination_date != '0000-00-00')
												$destination_date = @$item->destination_date;
											
											$progress_status_id = @$item->id_progress_status;
											
											$is_appears_img1 = @$item->is_appears_img1;
											
											$updated_date = @$item->updated_date;
								
											$subject_bg_color = 'background-color:white';
											
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
											
											if($destination_date < date('Y-m-d')) { 
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
											
											$count++;
											?>
											<input type="hidden" id="is_appears_img1_<?=@$meeting_id?>" value="<?=@$is_appears_img1?>" />
											<input type="hidden" id="is_appears_img2_<?=@$meeting_id?>" value="<?=@$is_appears_img2?>" />
											<input type="hidden" id="is_appears_img3_<?=@$meeting_id?>" value="<?=@$is_appears_img3?>" />
											<tr>
												<td style="text-align:center;"><a onclick="DuplicateRecord(<?=@$meeting_id?>)" title="עותק" style="text-decoration:underline;cursor:pointer;"><i class="fa fa-plus"></i></a></td>
												<td style="text-align:center;"><a onclick="location.href='add_meeting.php?&project_id=<?=@$project_id?>&id=<?=@$meeting_id?>'" title="עדכן" style="text-decoration:underline;cursor:pointer;"><?=@$count?></a></td>
												<td style="text-align:right;padding-right:5px;<?=@$subject_bg_color?>;">
													<div style="text-align:center;color:red;font-size:10px;" id="div_message_alert_down"></div>
													<input type="text" name="subject_<?=@$meeting_id?>" id="subject_<?=@$meeting_id?>" value="<?=@$subject?>" style="direction:rtl;width:98%;font-size:12px;<?=@$subject_bg_color?>;" />
												</td>	
												<td style="text-align:right;padding-right:5px;<?=@$area_bg_color?>;">
													<select id="area_<?=@$meeting_id?>" class="form-control" style="direction:rtl;width:98%;font-size:12px;<?=@$area_bg_color?>;font-weight:bold;">	
														<?php 
														foreach($areas as $item) {
														?>
															<option value="<?=@$item->id?>" <?php if($item->id == @$area_id) echo "selected";?>>
																<strong><?=@$item->name?></strong>
															</option>
															<?php
														}
														?>						
													</select>
												</td>
												<td style="text-align:right;padding-right:5px;<?=@$description_bg_color?>;"><textarea name="description_<?=@$meeting_id?>" id="description_<?=@$meeting_id?>" style="direction:rtl;width:98%;font-size:12px;<?=@$description_bg_color?>;"><?=@$description?></textarea></td>
												<td style="text-align:right;padding-right:5px;background-color:<?=@$task_bgcolor?>;">
													<select id="task_<?=@$meeting_id?>" class="form-control" style="direction:rtl;width:98%;font-size:12px;color:<?=@$task_color?>;background-color:<?=@$task_bgcolor?>;font-weight:bold;">	
														<?php 
														foreach($tasks as $item) {
														?>
															<option value="<?=@$item->id?>" <?php if($item->id == @$task_id) echo "selected";?>>
																<strong><?=@$item->name?></strong>
															</option>
															<?php
														}
														?>						
													</select>
												</td>
												<td style="text-align:right;padding-right:5px;background-color:<?=@$responsible_bgcolor?>;">
													<select id="responsible_<?=@$meeting_id?>" class="form-control" style="direction:rtl;width:98%;font-size:12px;color:<?=@$responsible_color?>;background-color:<?=@$responsible_bgcolor?>;font-weight:bold;">	
														<?php 
														foreach($responsibles as $item) {
														?>
															<option value="<?=@$item->id?>" <?php if($item->id == @$responsible_id) echo "selected";?>>
																<strong><?=@$item->name?></strong>
															</option>
															<?php
														}
														?>						
													</select>
												</td>
												<td style="text-align:right;padding-right:5px;<?=@$pass_on_bg_color?>;">
													<select id="pass_on_<?=@$meeting_id?>" class="form-control" style="direction:rtl;width:98%;font-size:12px;<?=@$pass_on_bg_color?>;font-weight:bold;">	
														<?php 
														foreach($responsibles as $item) {
														?>
															<option value="<?=@$item->id?>" <?php if($item->id == @$pass_on_id) echo "selected";?>>
																<strong><?=@$item->name?></strong>
															</option>
															<?php
														}
														?>						
													</select>
												</td>
												<td style="text-align:right;padding-right:5px;<?=@$task_creation_date_bg_color?>;">
													<input type="date" name="task_creation_date_<?=@$meeting_id?>" id="task_creation_date_<?=@$meeting_id?>" value="<?=@$task_creation_date?>" style="direction:rtl;width:98%;font-size:13px;<?=@$task_creation_date_bg_color?>;" />
												</td>
												<td style="text-align:right;padding-right:5px;font-weight:bold;<?=@$dest_date_color?>;<?=@$dest_date_bg_color?>;font-weight:bold;">		
													<input type="date" name="destination_date_<?=@$meeting_id?>" id="destination_date_<?=@$meeting_id?>" value="<?=@$destination_date?>" style="direction:rtl;width:98%;font-size:13px;<?=@$dest_date_color?>;<?=@$dest_date_bg_color?>;font-weight:bold;" />	
												</td>
												<td style="text-align:right;padding-right:5px;color:<?=@$progress_status_color?>;background-color:<?=@$progress_status_bgcolor?>;">
													<select id="progress_status_<?=@$meeting_id?>" class="form-control" style="direction:rtl;width:98%;font-size:12px;background-color:<?=@$progress_status_bgcolor?>;font-weight:bold;">	
														<option>---סטטוס---</option>
														<?php 
														foreach($progress_status_s as $item) {
														?>
															<option value="<?=@$item->id?>" <?php if($item->id == @$progress_status_id) echo "selected";?>>
																<strong><?=@$item->name?></strong>
															</option>
															<?php
														}
														?>						
													</select>
												</td>
												<td style="text-align:center;"><img src="images/edit-button.svg" style="padding:10px;cursor:pointer;" title="Edite" onclick="editeMeeting(<?=@$meeting_id?>);" /></td>									
												<td style="text-align:center;"><img src="images/delete.svg" style="cursor:pointer;" title="מחק" onclick="return removeMeeting(<?=@$meeting_id?>);" /></td>	
											</tr>
											<?php
										}
									}
								}
								?>
							</table>		
						</div>
					</div>			
					<div class="row" style="text-align:center;">
						<div class="col-md-12">
						   <input type="button" value="דו'ח סטטוס פרוייקט" class="btn marginTop20" onclick="toNewMeetingsReport();" />
						</div>
					</div>
				<?php }  ?>
			</div>
		</form> 
	</body>
</html>

<script>
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
		   location.href='add_meeting.php?id=0&project_id='+$('#project_id').val();
		},
	});						       			   
})

function DuplicateRecord(meeting_id) {
	let form_data = new FormData();	
	form_data.append('table_name','dne_meetings');
	form_data.append('id',meeting_id);
	$.ajax({
		type: 'POST',
		url: 'duplicate_record.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,			
		success: function(data){  
		   location.href='meetings.php?project_id='+$('#project_id').val();
		},
	});					
}

function editeMeeting(meeting_id) {
	let subject = '#subject_'+meeting_id;
	let area = '#area_'+meeting_id;
	let description = '#description_'+meeting_id;
	let task = '#task_'+meeting_id;
	let responsible = '#responsible_'+meeting_id;
	let pass_on = '#pass_on_'+meeting_id;
	let task_creation_date = '#task_creation_date_'+meeting_id;
	let destination_date = '#destination_date_'+meeting_id;
	let progress_status = '#progress_status_'+meeting_id;
	let is_appears_img1 = '#is_appears_img1_'+meeting_id;
	let is_appears_img2 = '#is_appears_img2_'+meeting_id;
	let is_appears_img3 = '#is_appears_img3_'+meeting_id;
	
	let form_data = new FormData();
	form_data.append('id',meeting_id);
	form_data.append('id_project',$('#project_id').val());
	form_data.append('subject',$(subject).val());
	form_data.append('id_area',$(area).val());
	form_data.append('description',$(description).val());
	form_data.append('id_task',$(task).val());
	form_data.append('id_responsible',$(responsible).val());
	form_data.append('id_pass_on',$(pass_on).val());
	form_data.append('task_creation_date',$(task_creation_date).val());
	form_data.append('destination_date',$(destination_date).val());
	form_data.append('id_progress_status',$(progress_status).val());
	form_data.append('is_appears_img1',$(is_appears_img1).val());
	form_data.append('is_appears_img2',$(is_appears_img2).val());
	form_data.append('is_appears_img3',$(is_appears_img3).val());
	
	$.ajax({
		type: 'POST',
		url: 'meeting_insert.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,			
		success: function(data){ 
			if(data == 'empty')	{
				if($(subject).val().length == 0)			
					$(subject).css('border-color','red');
				else if(!($(subject).val().length == 0))
					$(subject).css('border-color','initial');	
				
				$('#div_message_alert_down').html("Please fill this field"); 
			}
			else
	            location.reload();
		},
	})
}

function removeMeeting(id) {
	if(confirm("האם אתה בטוח למחוק את המשימה הזאת ?")) {
        let form_data = new FormData();	
		form_data.append('id',id);			
		$.ajax({
			type: 'POST',
			url: 'meeting_delete.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,			
			success: function(data){
				location.reload(true);			
			},
		});		
    }
    return false;
}
function toNewMeetingsReport() {
	let form_data = new FormData();	
	    form_data.append('from','new meetings');
		form_data.append('id_project',$('#project_id').val());			
		$.ajax({
			type: 'POST',
			url: 'last_pdf_data_update.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,			
			success: function(data){
			    window.open('new_meetings_report.php?project_id='+$('#project_id').val(),'_blank');
			},
		});		
}
</script>

<style>
.title {
	font-size: 22px;
	color: #349feb;
	margin-top: 20px;
	direction: rtl;
	text-align: center;
}

a {
    color: inherit;
}

.marginTop20 {
  margin-top: 20px;
}

.marginRight10 {
	margin-right: 10px;
}

.btn {
   color: white;
   background-color:#218FD6;
}

.btn:hover {
   color: white;
   background-color:#3370d6;
}
</style>