<?php 
include 'include/header.php';
include 'functions/functions.php';

$project_id = @$_GET['project_id'];
$period_new_task_filter = @$_GET['period_new_task_filter'];
$period_late_filter = @$_GET['period_late_filter'];
$task_filter = @$_GET['task_filter'];
$progress_status_filter = @$_GET['progress_status_filter'];
$supplier_filter = @$_GET['supplier_filter'];

$query = $mysqli->prepare("SELECT * FROM dne_projects WHERE id = ?");
$query->bind_param("i",$project_id );
$query->execute();
$query->store_result();
$project = fetch_unique($query);

$query = $mysqli->prepare("SELECT * FROM dne_custom_reports WHERE id_project = ? 
                          ORDER BY is_project_status_report DESC");
$query->bind_param("i",$project_id);
$query->execute(); 
$query->store_result();
$custom_reports_num_rows = $query->num_rows;
$custom_reports = fetch($query);

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
				
				<div class="row marginTop15 title dir-rtl">	
					<div class="col-md-12">
						<a id="a_project_title" class="text-decoration-none color-1A5276" href="meetings.php?project_id=<?=@$project_id?>&task_filter=<?=@$task_filter?>&progress_status_filter=<?=@$progress_status_filter?>&supplier_filter=<?=@$supplier_filter?>&period_new_task_filter=<?=@$period_new_task_filter?>&period_late_filter=<?=@$period_late_filter?>">
						    <strong class="fontSize33 line-height-1em"><?=@$project->nickname?></strong>
							<div class="fontSize26 font-family-david">פרוייקט <?=@$project->name_he?></div>
					    </a>		
					</div>				
			    </div>
				
				<div class="row alignCenter">
					<div class="col-md-12">
						<input type="button" value="יצירת דו''ח חדש" class="btn marginTop20 marginRight10 mb-2" onclick="location.href='add_custom_report.php?id=0&project_id=<?=@$project_id?>';" />		
					</div>
				</div>
				
				<br/>

				<?php if($custom_reports_num_rows > 0) { ?>		
					<div class="row fontSize14 alignCenter">
						<div align="center" class="col-md-12 mx-2">
							<table id="responsibles_list" border="1" dir="rtl">						
								<tr class="bgColorSilver height50">
									<th class="alignCenter" width="180px;">שם</th>
									<th class="alignCenter" width="80px;" colspan="2">&nbsp;</th>
								</tr>
			
								<?php
								$count = 0;
								foreach($custom_reports as $item) { 
								?>
									<tr class="height35">
										<td class="alignRight paddingRight5"><?=@$item->request_name?></td>
										<td class="alignCenter"><img src="images/edit-button.svg" class="cursor-pointer" title="עדכן" onclick="location.href='add_custom_report.php?id=<?=@$item->id?>&project_id=<?=@$project_id?>'" /></td>
										<td class="alignCenter"><img src="images/delete.svg" class="cursor-pointer" title="מחק" onclick="return removeCustomReport(<?=@$item->id?>);" /></td>	
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
function removeCustomReport(id) {
	if(confirm("האם אתה בטוח למחוק את הדו\'\'ח המיוחד הזה ?")) {
        let form_data = new FormData();	
		form_data.append('id',id);			
		$.ajax({
			type: 'POST',
			url: 'custom_report_delete.php',
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