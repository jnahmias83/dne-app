<?php
include 'include/header.php';
include 'functions/functions.php';

$query = $mysqli->prepare("SELECT * FROM dne_logos LIMIT 1");
$query->execute();
$query->store_result();
$logo = fetch_unique($query);

$query = $mysqli->prepare("SELECT * FROM dne_meetings_types");
$query->execute(); 
$query->store_result();
$meetings_types = fetch($query);
?>
		
		<form method="post" action="" class="form-inline">	
			<div class="container">
			    <div class="row marginTop5 alignCenter">
					<div class="col-12">
						<a href="projects.php"><img src="images/<?=@$logo->logo?>" width="120" height="114" /></a>
					</div>
				</div>

                <div class="row marginTop5 title fontSize20 marginTop10">	
					<div class="col-12">
					   רשימת סוגי פגישות
					</div>					
				</div>
				
				<div class="row">
					<div class="col-12 alignCenter">
						<input type="button" value="הוסף סוג פגישה חדש" class="btn marginTop20 mb-2" onclick="location.href='add_meeting_type.php?id=0'" />
					</div>
				</div>
						
				<div class="row marginTop10 fontSize14 alignCenter">
					<div align="center" class="col-12 mx-2">
						<table id="meetings_types_list" border="1" dir="rtl">						
							<tr class="bgColorSilver height50">
								<th class="alignCenter" width="100px;">שם</th>
								<th class="alignCenter" width="100px;">שם<br/>בעברית</th>
								<th class="alignCenter" width="100px;">כותרת 1</th>
								<th class="alignCenter" width="100px;">כותרת 1<br/>בעברית</th>
								<th class="alignCenter" width="100px;">כותרת 2</th>
								<th class="alignCenter" width="100px;">כותרת 2<br/>בעברית</th>
								<th class="alignCenter" width="30px;">&nbsp;</th>
								<th class="alignCenter" width="30px;">&nbsp;</th>
							</tr>
		
							<?php
							$count = 0;
							foreach($meetings_types as $item) {
								?>
								<tr class="height35">
									<td class="alignLeft paddingLeft5"><?=@$item->name?></td>
									<td class="alignRight paddingRight5"><?=@$item->name_he?></td>
									<td class="alignLeft paddingLeft5"><?=@$item->title?></td>
									<td class="alignRight paddingRight5"><?=@$item->title_he?></td>
									<td class="alignLeft paddingLeft5"><?=@$item->subtitle?></td>
									<td class="alignRight paddingRight5"><?=@$item->subtitle_he?></td>
									<td class="alignCenter"><img src="images/edit-button.svg" class="cursor-pointer" title="עדכן" onclick="location.href='add_meeting_type.php?id=<?=@$item->id?>'" /></td>									
									<td class="alignCenter"><img src="images/delete.svg" class="cursor-pointer" title="מחק" onclick="return removeMeetingType(<?=@$item->id?>);" /></td>
								</tr>
								<?php
							}
							?>
						</table>		
					</div>
				</div>				
            </div>
        </form>
    </body>
</html>

<script>
function removeMeetingType(id){
	if(confirm("האם אתה בטוח למחוק את סוג הפגישה הזה ?")) {
        let form_data = new FormData();	
		form_data.append('global',1);
		form_data.append('id',id);			
		$.ajax({
			type: 'POST',
			url: 'meeting_type_delete.php',
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