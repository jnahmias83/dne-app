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

$query = $mysqli->prepare("SELECT r.id AS id,r.email AS email,r.phone AS phone,
                          r.name AS name,r.id_projects_suppliers AS id_projects_suppliers,
                          r.role,r.color AS color,r.bgcolor AS bgcolor,
                          r.is_active AS is_active						  
                          FROM dne_responsibles r 
						  LEFT JOIN dne_projects_suppliers ps ON r.id_projects_suppliers = ps.id 
						  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id 
						  WHERE r.id_project = ? ORDER BY r.name");
$query->bind_param("i",$project_id);
$query->execute(); 
$query->store_result();
$responsibles_num_rows = $query->num_rows;
$responsibles = fetch($query);

include 'menu_tasks.php';
?>

        <form method="post" action="" class="form-inline">	
		    <input type="hidden" id="project_id" name="project_id" value="<?=@$project->id?>" />

			<div class="container">
			    <div class="row alignCenter marginTop25">
					<div class="col-md-12">
						<a href="projects.php"><img src="images/davidnahmias_logo.png" width="170px" height="170px" /></a>
					</div>
			    </div>
				
				<div class="row marginTop15 title">	
					<div class="col-md-12">
						<a id="a_project_title" class="text-decoration-none color-1A5276" href="meetings.php?project_id=<?=@$project_id?>&task_filter=<?=@$task_filter?>&progress_status_filter=<?=@$progress_status_filter?>&supplier_filter=<?=@$supplier_filter?>&period_new_task_filter=<?=@$period_new_task_filter?>&period_late_filter=<?=@$period_late_filter?>&is_specific_filter=<?=@$is_specific_filter?>">
						    <strong class="fontSize33 line-height-1em"><?=@$project->nickname?></strong>
							<div class="fontSize26 font-weight-bold font-family-david cursor-pointer">פרוייקט <?=@$project->name_he?></div>
					    </a>
                        <div class='fontSize26 font-weight-bold font-family-david cursor-pointer'>צוות הפרוייקט</div>					
					</div>				
			    </div>
				
				<div class="row marginTop10 alignCenter dir-rtl">
					<div class="col-md-12">
						<a class="btn" onclick="location.href='add_responsible.php?id=0&project_id=<?=@$project_id?>';">הוסף <i class="fa fa-user"></i></a>
					</div>
				</div>
				
				<br/>

				<?php if($responsibles_num_rows > 0) { ?>		
					<div class="row fontSize14 alignCenter">
						<div align="center" class="col-md-12 table-responsive mx-2">
							<table id="responsibles_list" class="" border="1" dir="rtl">						
								<tr class="bgColorSilver height50">   
								    <th width="20px;" class="alignCenter"></th>
									<th width="130px;" class="alignCenter"><i class="fa fa-user"></i><br/>שם</th>
									<th width="160px;" class="alignCenter">משרד/חברה</th>
									<th width="60px;" class="alignCenter">גופן</th>
									<th width="60px;" class="alignCenter">רקע</th>
									<th width="220px;" class="alignCenter">דוא''ל</th>
									<th width="120px;" class="alignCenter"><i class="fa fa-user"></i><br/>טלפון</th>
									<th width="120px;" class="alignCenter">תפקיד</th>
									<th width="40px;" class="alignCenter">&nbsp;</th>
									<th width="40px;" class="alignCenter">&nbsp;</th>
								</tr>
			
								<?php
								$count = 0;
								foreach($responsibles as $item) { 
									$query = $mysqli->prepare("SELECT s.name_he AS name_he
															  FROM dne_projects_suppliers ps
															  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
															  WHERE ps.id = ?");
									$query->bind_param("i",$item->id_projects_suppliers);
									$query->execute();
									$query->store_result();
									$supplier = fetch_unique($query);
									
									$role = "";
									if(@$item->role == "project_manager")
									    $role = "מנהל פרויקט";
									else if(@$item->role == "inspector")
									    $role = "מפקח";
									else if(@$item->role == "programmer")
									    $role = "מתכנן";
									else if(@$item->role == "supplier_contractor")
									    $role = "ספק/קבלן";
									else if(@$item->role == "entrepreneur")
									    $role = "יזם";
									else if(@$item->role == "entrepreneur_team")
									    $role = "צוות יזם";
								?>
									<tr class="height35">
									    <td class="alignCenter"><input type="checkbox" id="is_active_<?=@$item->id?>" name="is_active_<?=@$item->id?>" value="<?=@$item->is_active?>" <?php if(@$item->is_active == 1) echo "checked"?> onchange="setResponsibleActive(<?=@$item->id?>);" /></td>
										<td class="alignRight paddingRight5"><?=@$item->name?></td>
										<td class="alignRight paddingRight5"><?=@$supplier->name_he?></td>
										<td class="alignRight paddingRight5"><input type="color" class="width60" disabled="true" value="<?=@$item->color?>" /></td>
										<td class="alignRight paddingRight5"><input type="color" class="width60" disabled="true" value="<?=@$item->bgcolor?>" /></td>
										<td class="alignRight paddingRight5"><?=@$item->email?></td>
										<td class="alignRight paddingRight5"><?=@$item->phone?></td>
										<td class="alignRight paddingRight5"><?=@$role?></td>
										<td class="alignCenter"><img src="images/edit-button.svg" class="cursor-pointer" title="עדכן" onclick="location.href='add_responsible.php?id=<?=@$item->id?>&project_id=<?=@$project_id?>'" /></td>									
										<td class="alignCenter"><img src="images/delete.svg" class="cursor-pointer" title="מחק" onclick="return removeResponsible(<?=@$item->id?>);" /></td>	
									</tr>
									<?php
								}
								?>
							</table>		
						</div>
					</div>
					
					<div class="row alignCenter">
						<div class="col-md-12">
						   <input type="button" value="ברירת מחדל גופנים ורקעים" class="btn marginTop20 mb-2" onclick="setDefaultColorAndBgColor();" />
						</div>
					</div> 
				<?php } ?>
			</div>
		</form> 
	</body>
</html>

<script>
function removeResponsible(id) {
	if(confirm("האם אתה בטוח למחוק את האחראי הזה ?")) {
        let form_data = new FormData();	
		form_data.append('id',id);			
		$.ajax({
			type: 'POST',
			url: 'responsible_delete.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,			
			success: function(data){
				if(data == 'notallowedtoremove')
					alert('אי אפשר למחוק איש צוות שיש בו משימות');
				else 
					location.reload(true);				
			},
		});		
    }
    return false;
}

function setDefaultColorAndBgColor() {
	let form_data = new FormData();	
	form_data.append('project_id',$('#project_id').val());
	$.ajax({
		type: 'POST',
		url: 'setResponsiblesData.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,			
		success: function(data){  
           window.location.reload();
		},
	});												       			   
}

function setResponsibleActive(id_responsible) {
	let is_active = 0;
	let checkbox = $('#is_active_'+id_responsible);
	if(checkbox.is(':checked')) 
       is_active = 1;	   
	   
	let form_data = new FormData();	
	form_data.append('id_responsible',id_responsible);
	form_data.append('is_active',is_active);
	
	$.ajax({
		type: 'POST',
		url: 'set_responsible_active.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,			
		success: function(data){ 
           window.location.reload();
		},
	});	
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