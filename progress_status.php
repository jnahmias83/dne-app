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

$query = $mysqli->prepare("SELECT * FROM dne_progress_status 
                          WHERE id_project = ? ORDER BY (id_display IS NULL OR id_display = 0), id_display, id");
$query->bind_param("i",$project_id);
$query->execute(); 
$query->store_result();
$progress_status_num_rows = $query->num_rows;
$progress_status = fetch($query);

include 'menu_tasks.php';
?>

        <form method="post" action="" class="form-inline">	
		    <input type="hidden" id="project_id" value=<?=@$project_id?> />		
			
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
                        <div class='fontSize26 font-weight-bold font-family-david cursor-pointer'>רשימת סטטוסי התקדמות</div>					
					</div>				
			    </div>
				
				<div class="row marginTop20 alignCenter dir-rtl">
					<div class="col-12">
						<input type="button" value="חזור לרשימת המשימות" class="btn marginLeft10" onclick="location.href='meetings.php?&project_id=<?=@$project_id?>&task_filter=<?=@$task_filter?>&progress_status_filter=<?=@$progress_status_filter?>&supplier_filter=<?=@$supplier_filter?>&period_new_task_filter=<?=@$period_new_task_filter?>&period_late_filter=<?=@$period_late_filter?>&is_specific_filter=<?=@$is_specific_filter?>'" />		
						<a class="btn marginLeft10" onclick="location.href='add_progress_status.php?id=0&project_id=<?=@$project_id?>';"><i class="fa fa-plus"></i> סטטוסי ההתקדמות</a>
						<input type="button" value="Default" class="btn" onclick="FillGlobalProgressStatus();" />
					</div>
				</div>
				
				<br/>

				<?php if($progress_status_num_rows > 0) { ?>		
					<div class="row fontSize14 alignCenter">
						<div align="center" class="col-12 mx-2">
							<table id="progress_status_list" border="1" dir="rtl">						
								<tr class="bgColorSilver height50">
								    <th class="alignCenter" width="50px;">&nbsp;</th>
									<th width="120px;" class="alignCenter">שם</th>
									<th width="120px;" class="alignCenter">שם בעברית</th>
									<th width="90px;" class="alignCenter">צבע גופן</th>
									<th width="90px;" class="alignCenter">צבע רקע</th>
									<th width="40px;" class="alignCenter">&nbsp;</th>
									<?php if($progress_status_num_rows > 7) { ?>
									   <th width="40px;" class="alignCenter">&nbsp;</th>
									<?php } ?>
								</tr>
			
								<tbody id="progress_status_tbody">
								<?php
								$count = 0;
								foreach($progress_status as $item) {
									?>
									<tr class="height35" data-status-id="<?=@$item->id?>">
									    <td class="alignCenter">
											<span class="status-drag-handle" title="גרור לשינוי סדר"><i class="fa-solid fa-bars"></i></span>
										</td>
									    <td class="alignLeft paddingLeft5"><?=@$item->name?></td>
										<td class="alignRight paddingRight5"><?=simplifyStatusLabel(@$item->name_he)?></td>
										<td class="alignRight paddingRight5"><input type="color" class="width90" disabled="true" value="<?=@$item->color?>" /></td>
										<td class="alignRight paddingRight5"><input type="color" class="width90" disabled="true" value="<?=@$item->bgcolor?>" /></td>
										<td class="alignCenter"><img src="images/edit-button.svg" class="cursor-pointer" title="עדכן" onclick="location.href='add_progress_status.php?id=<?=@$item->id?>&project_id=<?=@$project_id?>'" /></td>									
										<?php if(!(@$item->name_he == ' ' || @$item->name_he == 'בביצוע' || @$item->name_he == 'איחור' || @$item->name_he == 'בוצע/נמסר' || @$item->name_he == 'בהמתנה' || @$item->name_he == 'ארכיון' || @$item->name_he == 'הנחיה/החלטה')) { ?>
										   <td class="alignCenter">
											  <img src="images/delete.svg" class="cursor-pointer" title="מחק" onclick="return removeProgressStatus(<?=@$item->id?>);" />	
										   </td>
									   <?php } else '&nbsp;' ?>
									</tr>
									<?php
								}
								?>
								</tbody>
							</table>
						</div>
					</div>
				<?php } ?>
			</div>
		</form> 
	</body>
</html>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
(function(){
	let tbody = document.getElementById('progress_status_tbody');
	if(!tbody) return;

	Sortable.create(tbody, {
		handle: '.status-drag-handle',
		animation: 150,
		onEnd: function(){
			let order = Array.prototype.map.call(
				tbody.querySelectorAll('tr[data-status-id]'),
				function(tr){ return tr.getAttribute('data-status-id'); }
			);
			let form_data = new FormData();
			form_data.append('id_project', $('#project_id').val());
			form_data.append('order', order.join(','));
			$.ajax({
				type: 'POST',
				url: 'reorder_progress_status.php',
				data: form_data,
				cache: false,
				processData: false,
				contentType: false,
			});
		}
	});
})();

function FillGlobalProgressStatus() {
	let form_data = new FormData();	
	form_data.append('id_project',$('#project_id').val());
	
	$.ajax({
		type: 'POST',
		url: 'fill_global_progress_status.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,			
		success: function(data){
			location.reload(true);			
		},
	});		
}

function removeProgressStatus(id) {
	if(confirm("האם אתה בטוח למחוק את הסטטוס ההתקדמות הזה ?")) {
        let form_data = new FormData();	
		form_data.append('global',0);
		form_data.append('id',id);			
		$.ajax({
			type: 'POST',
			url: 'progress_status_delete.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,
			success: function(data){
				if(data == 'notallowedtoremove')
					alert('לא ניתן למחוק את סטטוס ההתקדמות הזה כי הוא משוייך למשימה אחת או יותר.')
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

.status-drag-handle {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	width: 26px;
	height: 26px;
	cursor: grab;
	touch-action: none;
	color: #555;
	font-size: 14px;
}
.status-drag-handle:active {
	cursor: grabbing;
}
#progress_status_tbody tr.sortable-ghost {
	opacity: 0.4;
}
</style>