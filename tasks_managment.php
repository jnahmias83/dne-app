<?php
include 'include/header.php';
include 'functions/functions.php';

$query = $mysqli->prepare("SELECT * FROM dne_logos LIMIT 1");
$query->execute();
$query->store_result();
$logo = fetch_unique($query);

$query = $mysqli->prepare("SELECT * FROM dne_global_tasks ORDER BY name_he");
$query->execute(); 
$query->store_result();
$tasks_num_rows = $query->num_rows;
$tasks = fetch($query);
?>

		<form>
			<div class="container">
			    <div class="row marginTop25 alignCenter">
					<div class="col-12">
						<a href="projects.php"><img src="images/<?=@$logo->logo?>" width="120" height="114" /></a>
					</div>
				</div>				
				
				<div class="row marginTop5 title fontSize20">	
					<div class="col-12">					
					   רשימת סוגי משימות
					</div>					
				</div>
				
				<div class="row alignCenter">
					<div class="col-12">
						<input type="button" value="הוסף סוג משימה חדש" class="btn marginTop20 mb-2" onclick="location.href='add_global_task.php?id=0';" />
					</div>
				</div>

				<?php if($tasks_num_rows > 0) { ?>		
					<div class="row marginTop10 alignCenter">
						<div align="center" class="col-12 mx-2">
							<div class="table-responsive">
								<table id="tasks_list" border="1" dir="rtl">						
									<tr class="bgColorSilver height50">
										<th class="alignCenter" width="100px;">שם</th>
										<th class="alignCenter" width="100px;">שם <br/> בעברית</th>
										<th class="alignCenter" width="90px;">צבע גופן</th>
										<th class="alignCenter" width="90px;">צבע רקע</th>
										<th class="alignCenter" width="40px;">&nbsp;</th>
										<?php if($tasks_num_rows > 8) { ?>
										   <th width="40px;" class="alignCenter">&nbsp;</th>
										<?php } ?>
									</tr>
				
									<?php
									$count = 0;
									foreach($tasks as $item) {
										?>
										<tr class="height30 fontSize14">
											<td class="alignLeft paddingLeft5"><?=@$item->name?></td>
											<td class="alignRight paddingRight5"><?=@$item->name_he?></td>
											<td class="alignRight paddingRight5"><input type="color" class="width90" disabled="true" value="<?=@$item->color?>" /></td>
											<td class="alignRight paddingRight5"><input type="color" class="width90" disabled="true" value="<?=@$item->bgcolor?>" /></td>								
											<td class="alignCenter"><img src="images/edit-button.svg" class="cursor-pointer" title="עדכן" onclick="location.href='add_global_task.php?id=<?=@$item->id?>'" /></td>									
												<?php if(!(@$item->name_he == 'תכנון' || @$item->name_he == 'ביצוע' || @$item->name_he == 'ניהול' || @$item->name_he == 'בקרת איכות' || @$item->name_he == 'הנחיית ביצוע' || @$item->name_he == 'יזם' || @$item->name_he == 'סטטוס ביצוע' || @$item->name_he == 'בקשה/שאילתה')) { ?>
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
					</div>
				<?php } ?>
			</div>
		</form>
	</body>
</html>

<script>
function removeTask(id){
	if(confirm("האם אתה בטוח למחוק את סוג המשימה זו ?")) {
        let form_data = new FormData();	
		form_data.append('global',1);
		form_data.append('id',id);			
		$.ajax({
			type: 'POST',
			url: 'task_delete.php',
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
	background-color: #218FD6;
	color: white;
	margin-top: 10px;
}

.btn:hover {
   background-color: #3370d6;
   color: white;
}
</style>