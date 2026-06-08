<?php
include 'include/header.php';
include 'functions/functions.php';

$id = @$_GET['id'];
$project_id = @$_GET['project_id'];

$query = $mysqli->prepare("SELECT * FROM dne_areas WHERE id = ? AND id_project = ?");
$query->bind_param("ii",$id,$project_id);
$query->execute();
$query->store_result();
$area = fetch_unique($query);

$query = $mysqli->prepare("SELECT * FROM dne_projects WHERE id = ?");
$query->bind_param("i",$project_id );
$query->execute();
$query->store_result();
$project = fetch_unique($query);   

if($_SESSION['lang'] == 'EN') {
  $dir = 'dir-ltr';
  $add_area_label = 'Add area to the project';
  $name_label = "Name";
  $title_save_btn = 'Save';
  $title_cancel_btn = 'Cancel';
  $alert_mandatory_fields = 'Please fill all the mandatory fields.';
}

if($_SESSION['lang'] == 'FR') {
   $dir = 'dir-ltr';
   $add_area_label = 'Ajouter une région au projet';
   $name_label = "Nom";
   $title_save_btn = 'Sauvegarder';
   $title_cancel_btn = 'Annuler';
   $alert_mandatory_fields = 'Merci de remplir tous les champs obligatoirs.';
}

if($_SESSION['lang'] == 'HE') {
   $dir = 'dir-rtl';
   $add_area_label = 'הוסף איזור לפרוייקט';
   $name_label = "שם";
   $title_save_btn = 'שמור';
   $title_cancel_btn = 'ביטול';
   $alert_mandatory_fields = 'נא למלה את כל השדות החובות.';
}
?>
		<form method="post" action="" enctype="multipart/form-data" class="form-inline">
			<input type="hidden" id="id" value="<?=@$id?>" />
			<input type="hidden" id="project_id" value="<?=@$project_id?>" />
			
			<div class="container">
			    <br/>
			
				<div class="row alignCenter marginTop25">
					<div class="col-md-12">
						<a href="projects.php"><img src="images/davidnahmias_logo.png" width="170px" height="170px" /></a>
					</div>
			    </div>
			   <?php
				if($id == 0) { ?>
					<div class="row title <?=@$dir?>">
						<div class="col-md-12">
							<?=@$add_area_label?> <span class="colorGreen"><?=@$project->name?></span>
						</div>
					</div>
				<?php }
				else if($id > 0) { ?>
				   <div class="row title <?=@$dir?>">
						<div class="col-md-12">
							<span class="colorGreen"><?=@$area->name?></span>
						</div>
					</div>
				<?php } ?>			
				
				<div class="row <?=@$dir?> marginTop20">
					<div class="col-md-12">	
						<strong><?=@$name_label?>:</strong>
						<br/>	
						<input type="text" class="form-control width250" name="name" id="name" placeholder="*<?=@$name_label?>" value="<?=@$area->name?>" />	
					</div>
				</div>
						
				<div class="row <?=@$dir?> marginTop10">
					<div class="col-md-12">
						<div class="marginTop10" id="div_message_alert_down"></div>	
						<input type="button" id="save_btn" name="save_btn" class="btn marginTop5 bgColorBlue colorWhite mb-2" value="<?=@$title_save_btn?>" />		
						<input type="button" id="cancel_btn" class="btn marginRight10 marginTop5 bgColorBlack colorWhite mb-2" value="<?=@$title_cancel_btn?>" />				
					</div>
				</div>				
			</div>	
		</form>       
    </body>
</html>

<script>
$('#save_btn').click (function (e){ 
	let form_data = new FormData();	
	form_data.append('id',$('#id').val());
	form_data.append('id_project',$('#project_id').val());
	form_data.append('name',$('#name').val());
	$.ajax({
		type: 'POST',
		url: 'area_insert.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,			
		success: function(data){ 
			if(data == 'empty')	{		
				if($('#name').val().length == 0)			
					$('#name').css('border-color','red');
				else if(!($('#name').val().length == 0))
					$('#name').css('border-color','initial');	
				
				$('#div_message_alert_down').html("<span style=color:red;font-size:13px;><?=@$alert_mandatory_fields?></span>");
			}
			else
				location.href = 'areas.php?project_id='+$('#project_id').val();		
		},
	});												       			   
})

$('#cancel_btn').click(function(){
    location.href = "areas.php?project_id="+$('#project_id').val();	
})
</script>

<style>
.title {
	font-size: 22px;
	color: #349feb;
	margin-top: 20px;
	direction: rtl;
}

.dir-ltr {
   direction: ltr;
}

.dir-rtl {
   direction: rtl;
}

.alignCenter {
	text-align: center;
}

.bgColorBlack {
	background-color: black;
}

.bgColorBlue {
	background-color:#218FD6;
}

.marginTop5 {
  margin-top: 5px;
}

.marginTop10 {
	margin-top: 10px;
}

.marginTop20 {
	margin-top: 20px;
}

.marginTop25 {
  margin-top: 25px;
}

.marginRight10 {
	margin-right: 10px;
}

.width250 {
	width: 250px;
}

.colorWhite {
	color: white;
}

.colorGreen {
	color:#5bbd8d;
}

.btn:hover {
   color: white;
}

.bgColorBlack:hover {
	background-color: #45484d;
}

.bgColorBlue:hover {
	background-color:#3370d6;
}
</style>