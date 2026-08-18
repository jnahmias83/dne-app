<?php
include 'include/header.php';
include 'functions/functions.php';

$query = $mysqli->prepare("SELECT * FROM dne_logos LIMIT 1");
$query->execute();
$query->store_result();
$logo = fetch_unique($query);

$query = $mysqli->prepare("SELECT * FROM dne_users");
$query->execute(); 
$query->store_result();
$users_num_rows = $query->num_rows;
$users = fetch($query);

$manager_type = 'M';
$id_manager_sup = 0;

$query = $mysqli->prepare("SELECT * FROM dne_suppliers WHERE type = ?");
$query->bind_param("s",$manager_type);
$query->execute();
$query->store_result();
$manager_sup = fetch_unique($query);
$id_manager_sup = @$manager_sup->id;

$url_manager_data = 'add_supplier.php?type_sup=M&id=0';
if($id_manager_sup > 0)
	$url_manager_data = 'add_supplier.php?type_sup=M&id='.$id_manager_sup;
?>

        <form method="post" action="" class="form-inline">	
		    <input type="hidden" id="project_id" value=<?=@$project_id?> />
			
			<div class="container">
			    <div class="row alignCenter marginTop25">
					<div class="col-12">
						<a href="projects.php"><img src="images/<?=@$logo->logo?>" width="170" height="170" /></a>
					</div>
			    </div>
				
				<div class="row title marginTop5 fontSize20 dir-rtl">	
					<div class="col-12">			
						רשימת משתמשים 
					</div>					
				</div>
			
			    <div class="row marginTop10 alignCenter dir-rtl">
					<div class="col-12">
					    <input type="button" value="נתוני מנהל" class="btn marginRight8 mb-2" onclick="location.href='<?=@$url_manager_data?>'" />
						<input type="button" value="הוסף משתמש" class="btn marginRight8 mb-2" onclick="location.href='add_user.php?id=0'" />
					</div>
				</div>						

				<?php if($users_num_rows > 0) { ?>		
					<div class="row marginTop10 alignCenter dir-rtl fontSize14">
						<div align="center" class="col-12 mx-2">
							<table id="users_list" border="1" dir="rtl">						
								<tr class="bgColorSilver height50">
								    <th colspan="2" class="alignCenter width60"></th>
									<th class="alignCenter width100">שם משפחה</th>
									<th class="alignCenter width100">שם פרטי</th>
									<th class="alignCenter width40">כינוי</th>
									<th class="alignCenter width230">דוא''ל</th>
									<th class="alignCenter width200">שם משתמש</th>
									<th class="alignCenter width100">סיסמה</th>
									<th class="alignCenter width70">תַפְקִיד</th>
									<th class="alignCenter width100">הרשאות</th>
								</tr>		
								<?php
								$count = 1;
								foreach($users as $item) {
									?>
									<tr class="height35">
									    <td class="alignCenter"><input type="checkbox" id="cb_<?=@$item->id?>" <?php if(@$item->is_user_active == 1) echo "checked";?> onclick="setIsUserActive(<?=@$item->id?>);" /></td>
										<td class="alignCenter"><a href="add_user.php?id=<?=@$item->id?>"><?=@$count?></a></td>
										<td class="alignRight paddingRight5"><?=@$item->lastname?></td>
										<td class="alignRight paddingRight5"><?=@$item->firstname?></td>
										<td class="alignRight paddingRight5"><?=@$item->nickname?></td>
										<td class="alignRight paddingRight5"><a href="mailto:<?=@$item->email?>"><?=@$item->email?></a></td>
									    <td class="alignRight paddingRight5"><?=@$item->username?></td>
										<td class="alignRight paddingRight5"><?=@$item->password?></td>
										<td class="alignCenter paddingRight5"><?=@$item->role?></td>
										<td class="alignCenter paddingRight5"><?=@$item->privileges?></td>
									</tr>
									<?php
									$count++;
								}
								?>
							</table>		
						</div>
					</div>
					
					<div class="row alignCenter marginTop15 dir-rtl">
						<div class="col-12">
							<a href="users_report.php" target="_blank">
							   <img src="images/file-pdf-solid.svg" width="50" height="30" alt="PDF Icon" />	
							</a>
						</div>
					</div>			
				<?php } ?>
			</div>
		</form> 
	</body>
</html>

<script>
function setIsUserActive(id_user) {
	let isChecked = $('#cb_'+id_user).is(':checked');
	let isUserActive = isChecked? 1:0;
	
	let form_data = new FormData();
	form_data.append('id',id_user);
	form_data.append('is_user_active',isUserActive);
	
	$.ajax({
		 type: 'POST',
		 url: 'set_is_user_active.php',
		 data: form_data,
		 cache: false,
		 processData: false,
		 contentType: false,			
		 success: function(data){ 
	         location.reload();
		 },
	 })
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
</style>