<?php
include 'include/header.php';
include 'functions/functions.php';

$query = $mysqli->prepare("SELECT * FROM dne_logos LIMIT 1");
$query->execute();
$query->store_result();
$logo = fetch_unique($query);

$query = $mysqli->prepare("SELECT * FROM dne_global_progress_status");
$query->execute(); 
$query->store_result();
$progress_status_num_rows = $query->num_rows;
$progress_status = fetch($query);
?>


		<form method="post" action="" enctype="multipart/form-data" class="form-inline">						
			<div class="container">
				<div class="row alignCenter marginTop25">
					<div class="col-md-12">
						<a href="projects.php"><img src="images/<?=@$logo->logo?>" width="120" height="114" /></a>
					</div>
			    </div>
				<div class="row marginTop5 title fontSize20">	
					<div class="col-12">
					   רשימת סטסטוסי התקדמות			
					</div>					
			    </div>
				<div class="row alignCenter">
					<div class="col-12">
						<input type="button" value="הוסף סטטוס התקדמות חדש" class="btn marginTop20 mb-2" onclick="location.href='add_global_progress_status.php?id=0';" />
					</div>
				</div>
						
				<?php if($progress_status_num_rows > 0) { ?>		
					<div class="row marginTop10 alignCenter">
						<div align="center" class="col-12 mx-2">
							<div class="table-responsive">
								<table id="progress_status_list" border="1" dir="rtl">						
									<tr class="bgColorSilver height50">
										<th class="alignCenter" width="100px;">שם</th>
										<th class="alignCenter" width="100px;">שם <br/> בעברית</th>
										<th class="alignCenter" width="90px;">צבע גופן</th>
										<th class="alignCenter" width="90px;">צבע רקע</th>
										<th class="alignCenter" width="40px;">&nbsp;</th>
										<?php if($progress_status_num_rows > 7) { ?>
										   <th class="alignCenter" width="40px;">&nbsp;</th>
										<?php } ?>
									</tr>
				
									<?php
									$count = 0;
									foreach($progress_status as $item) {
										?>
										<tr class="height30 fontSize14">
											<td class="alignLeft paddingLeft5"><?=@$item->name?></td>
											<td class="alignRight paddingRight5"><?=simplifyStatusLabel(@$item->name_he)?></td>
											<td class="alignRight paddingRight5"><input type="color" class="width90" disabled="true" value="<?=@$item->color?>" /></td>
											<td class="alignRight paddingRight5"><input type="color" class="width90" disabled="true" value="<?=@$item->bgcolor?>" /></td>
											<td class="alignCenter"><img src="images/edit-button.svg" class="cursor-pointer" title="עדכן" onclick="location.href='add_global_progress_status.php?id=<?=@$item->id?>'" /></td>									
											<?php if(!(@$item->name_he == ' ' || @$item->name_he == 'בביצוע' || @$item->name_he == 'איחור' || @$item->name_he == 'בוצע/נמסר' || @$item->name_he == 'בהמתנה' || @$item->name_he == 'ארכיון' || @$item->name_he == 'הנחיה/החלטה')) { ?>
												<td class="alignCenter">
												   <img src="images/delete.svg" class="cursor-pointer" title="מחק" onclick="return removeProgressStatus(<?=@$item->id?>);" />	
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
function removeProgressStatus(id){
	if(confirm("האם אתה בטוח למחוק את הסטטוס ההתקדמות הזה ?")) {
        let form_data = new FormData();	
		form_data.append('global',1);
		form_data.append('id',id);			
		$.ajax({
			type: 'POST',
			url: 'progress_status_delete.php',
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