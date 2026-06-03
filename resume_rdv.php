<?php
include 'include/header.php';
include 'functions/functions.php';

$project_id = @$_GET['project_id'];

$query = $mysqli->prepare("SELECT * FROM dne_projects WHERE id = ?");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$project = fetch_unique($query);

$query = $mysqli->prepare("SELECT * FROM dne_rdv 
                          WHERE id_project = ? 
						  ORDER by rdv_date DESC");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$rdvs = fetch($query);

include 'menu_tasks.php';
?>
		<div class="container">
			<form method="post" action="" class="form-inline">
				<input type="hidden" id="project_id" name="project_id" value="<?=@$project->id?>" />
				
				<div class="row alignCenter marginTop25">
					<div class="col-md-12">
						<a href="projects.php"><img src="images/davidnahmias_logo.png" width="170px" height="170px" /></a>
					</div>
				</div>
				
				<div class="row marginTop15 title dir-rtl">	
					<div class="col-md-12">
						<a id="a_project_title" class="text-decoration-none color-1A5276" href="meetings.php?project_id=<?=@$project_id?>">
							<strong class="fontSize33 line-height-1em"><?=@$project->nickname?></strong>
							<div class="fontSize26 font-family-david">פרוייקט <?=@$project->name_he?></div>
						</a>		
					</div>				
				</div>	
			   
				<div class="row marginTop10 font-weight-bold title">
				   <div class="col-md-12 fontSize26 font-family-david">
					   בחירת ישיבה
				   </div>
				</div>
			   
				<div class="m-2 marginTop5 row alignCenter">
					<div class="col-md-12">
						<select id="rdv" class="width height35 dir-rtl" onchange="toTasksReport();">
							<option value="0">------ בחר ישיבה ------</option>
							 <?php 
							foreach($rdvs as $item) {
							?>
								<option value="<?=@$item->id?>" <?php if($item->id == @$_SESSION['id_rdv']) echo "selected";?>>
									<?=substr($item->rdv_date,8,2).'/'.substr($item->rdv_date,5,2).'/'.substr($item->rdv_date,0,4).'-'.@$item->rdv_name?>
								</option>
								<?php
							}
							?>			
						</select>
					</div>
				</div>	
			</form>
		</div>
	</body>
</html>

<script>
function toUpdateRdv() {
	location.href='add_rdv.php?project_id='+$('#project_id').val()+'&id='+$('#rdv').val();
}

function toTasksReport(){
	let form_data = new FormData();
	form_data.append('id_project',$('#project_id').val()); 					 
	form_data.append('id_rdv',$('#rdv').val());
	
	$.ajax({
		type: 'POST',
		url: 'setSessionRdv.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,			
		success: function(data){  
		   location.href = 'meetings.php?project_id='+$('#project_id').val();   
		},
	});	
}	
</script>

<style>
#a_project_title:hover {
	color: grey;
}
</style>