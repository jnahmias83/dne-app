<?php
include 'include/header.php';
include 'functions/functions.php';

$project_id = @$_GET['project_id'];
$period_new_task_filter = @$_GET['period_new_task_filter'];
$period_late_filter = @$_GET['period_late_filter'];
$task_filter = @$_GET['task_filter'];
$progress_status_filter = @$_GET['progress_status_filter'];
$supplier_filter = @$_GET['supplier_filter'];
$is_specific_filter = @$_GET['is_specific_filter'];

$query = $mysqli->prepare("SELECT * FROM dne_projects WHERE id = ?");
$query->bind_param("i",$project_id );
$query->execute();
$query->store_result();
$project = fetch_unique($query);

$query = $mysqli->prepare("SELECT max(id_display) AS max_id,
                          min(id_display) AS min_id 
						  FROM dne_tasks WHERE id_project = ?");
$query->bind_param("i",$project_id);
$query->execute(); 
$query->store_result();
$task = fetch_unique($query);

$query = $mysqli->prepare("SELECT * FROM dne_tasks WHERE id_project = ? ORDER BY id_display");
$query->bind_param("i",$project_id);
$query->execute(); 
$query->store_result();
$tasks_num_rows = $query->num_rows;
$tasks = fetch($query);

include 'menu_tasks.php';
?>

        <form method="post" action="" class="form-inline">	
		    <input type="hidden" id="id_project" name="id_project" value="<?=@$project_id?>" />
			
			<div class="container">
			    <div class="row alignCenter marginTop25">
					<div class="col-12">
						<a href="projects.php"><img src="images/davidnahmias_logo.png" width="170px" height="170px" /></a>
					</div>
			    </div>						
				
				<div class="row marginTop15 title">	
					<div class="col-12">
						<a id="a_project_title" class="text-decoration-none color-1A5276" href="meetings.php?project_id=<?=@$project_id?>&task_filter=<?=@$task_filter?>&progress_status_filter=<?=@$progress_status_filter?>&supplier_filter=<?=@$supplier_filter?>&period_new_task_filter=<?=@$period_new_task_filter?>&period_late_filter=<?=@$period_late_filter?>&is_specific_filter=<?=@$is_specific_filter?>">
						    <strong class="fontSize33 line-height-1em"><?=@$project->nickname?></strong>
							<div class="fontSize26 font-weight-bold font-family-david cursor-pointer">פרוייקט <?=@$project->name_he?></div>
					    </a>
                        <div class='fontSize26 font-weight-bold font-family-david cursor-pointer'>רשימת סוגי משימות</div>					
					</div>				
			    </div>
				
				<div class="row marginTop20 alignCenter dir-rtl">
					<div class="col-12">
						<input type="button" value="חזור לרשימת המשימות" class="btn marginLeft10" onclick="location.href='meetings.php?&project_id=<?=@$project_id?>&task_filter=<?=@$task_filter?>&progress_status_filter=<?=@$progress_status_filter?>&supplier_filter=<?=@$supplier_filter?>&period_new_task_filter=<?=@$period_new_task_filter?>&period_late_filter=<?=@$period_late_filter?>&is_specific_filter=<?=@$is_specific_filter?>';" />				
						<a class="btn marginLeft10" onclick="location.href='add_task.php?id=0&project_id=<?=@$project_id?>';"><i class="fa fa-plus"></i> סוג משימה</a>
						<input type="button" value="Default" class="btn" onclick="FillGlobalTasks();" />
					</div>
				</div>
				
				<br/>

				<?php if($tasks_num_rows > 0) { ?>		
					<div class="row fontSize14 alignCenter">
						<div align="center" class="col-12 mx-2">
							<table id="tasks_list" border="1" dir="rtl">						
								<tr class="bgColorSilver height50">
								    <th class="width70 alignCenter" colspan="2">&nbsp;</th>
									<th width="110px;" class="alignCenter">שם</th>
									<th width="110px;" class="alignCenter">שם בעברית</th>
									<th width="90px;" class="alignCenter">צבע גופן</th>
									<th width="90px;" class="alignCenter">צבע רקע</th>
									<th width="40px;" class="alignCenter">&nbsp;</th>
									<?php if($tasks_num_rows > 8) { ?>
									   <th width="40px;" class="alignCenter">&nbsp;</th>
									<?php } ?>
								</tr>
			
								<?php
								$count = 0;
								foreach($tasks as $item) { ?>
									<tr class="height35">
									    <td class="alignCenter"> 
											<?php if($item->id_display === $task->min_id) { ?>
												<a onclick="mooveRecord(<?=@$item->id?>,<?=@$item->id_display?>,'down');">&darr;</a>
											<?php } else if($item->id_display === $task->max_id) { ?>
												<a onclick="mooveRecord(<?=@$item->id?>,<?=@$item->id_display?>,'up');">&uarr;</a>
											<?php } else { ?>
											   <a onclick="mooveRecord(<?=@$item->id?>,<?=@$item->id_display?>,'down');">&darr;</a> 
											   &nbsp;&nbsp;  
											   <a onclick="mooveRecord(<?=@$item->id?>,<?=@$item->id_display?>,'up');">&uarr;</a>
											<?php } ?>
										</td>
										<td class="alignCenter"><input type="checkbox" id="cb_<?=@$item->id?>" <?php if(@$item->is_appears_tasks_list == 1) echo "checked";?> onclick="setIsAppearsTasksList(<?=@$item->id?>);" /></td>	
									    <td class="alignLeft paddingLeft5"><?=@$item->name?></td>
										<td class="alignRight paddingRight5"><?=@$item->name_he?></td>
										<td class="alignRight paddingRight5"><input type="color" class="width90" disabled="true" value="<?=@$item->color?>" /></td>
										<td class="alignRight paddingRight5"><input type="color" class="width90" disabled="true" value="<?=@$item->bgcolor?>" /></td>								
										<td class="alignCenter"><img src="images/edit-button.svg" class="cursor-pointer" title="עדכן" onclick="location.href='add_task.php?id=<?=@$item->id?>&project_id=<?=@$project_id?>'" /></td>									
										<?php if(!(@$item->name_he == 'תכנון' || @$item->name_he == 'ביצוע' || @$item->name_he == 'ניהול' || @$item->name_he == 'יזם' || @$item->name_he == 'בקרת איכות' || @$item->name_he == 'הנחיית ביצוע' || @$item->name_he == 'סטטוס ביצוע' || @$item->name_he == 'בקשה/שאילתה')) { ?>
											<td class="alignCenter">
											   <img src="images/delete.svg" class="cursor-pointer" title="מחק" onclick="return removeTask(<?=@$item->id?>);" />	
											</td>
										<?php } ?>
									</tr>
									<?php
								}
								?>
							</table>		
						</div>
					</div>
				<?php } ?>
			</div>
		</form> 
	</body>
</html>

<script>
function FillGlobalTasks(){
	let form_data = new FormData();	
	form_data.append('id_project',$('#id_project').val());			
	$.ajax({
		type: 'POST',
		url: 'fill_global_tasks.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,			
		success: function(data){
			location.reload(true);			
		},
	});		
}

function mooveRecord(id,id_display,direction){
	let form_data = new FormData();	
	form_data.append('id',id);
	form_data.append('id_display',id_display);	
	form_data.append('id_project',$('#id_project').val());
	form_data.append('direction',direction);
	$.ajax({
		type: 'POST',
		url: 'moove_task.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,			
		success: function(data){
			window.location.reload(true);				
		},
	});
}

function setIsAppearsTasksList(id_task){
	let isChecked = $('#cb_'+id_task).is(':checked');
	let isAppearsTasksList = isChecked? 1:0;
	
	let form_data = new FormData();
	form_data.append('id_task',id_task);
	form_data.append('is_appears_tasks_list',isAppearsTasksList);
	
	$.ajax({
		 type: 'POST',
		 url: 'set_is_appears_tasks_list.php',
		 data: form_data,
		 cache: false,
		 processData: false,
		 contentType: false,			
		 success: function(data){ 
	         location.reload();
		 },
	 })
}

function removeTask(id){
	if(confirm("האם אתה בטוח למחוק את סוג המשימה זו ?")) {
        let form_data = new FormData();	
		form_data.append('global',0);	
		form_data.append('id',id);			
		$.ajax({
			type: 'POST',
			url: 'task_delete.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,			
			success: function(data){
				if(data == 'notallowedtoremove')
					alert('לא ניתן למחוק את סוג המשימה הזא כי הוא משוייך למשימה אחת או יותר.')
				else 
					location.reload(true);			
			},
		});		
    }
    return false;
}
</script>

<style>
.btn {
   color: white;
   background-color: #218FD6;
}

.btn:hover {
   color: white;
   background-color: #3370d6;
}

#a_project_title:hover {
	color: grey;
}
</style>