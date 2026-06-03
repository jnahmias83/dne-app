<?php
include 'include/header.php';
include 'functions/functions.php';

$id = @$_GET['id'];
$project_id = @$_GET['project_id'];
$from = @$_GET['from'];

$query = $mysqli->prepare("SELECT * FROM dne_chapters WHERE id_project = ?");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$chapters_num_rows = @$query->num_rows;

$disabled_next_step_btn = 'disabled';
if($chapters_num_rows > 0)
	$disabled_next_step_btn = '';  

$query = $mysqli->prepare("SELECT * FROM dne_chapters WHERE id = ? 
                          AND id_project = ?");
$query->bind_param("ii",$id,$project_id);
$query->execute();
$query->store_result();
$chapter = fetch_unique($query);

$query = $mysqli->prepare("SELECT * FROM dne_projects WHERE id = ?");
$query->bind_param("i",$project_id );
$query->execute();
$query->store_result();
$project = fetch_unique($query);

$title = 'הוסף פרק';

if(@$id > 0)
	$title = @$chapter->name;

$dir = 'dir-rtl';
$name_label = "שם";
$title_save_btn = 'שמור';
$title_cancel_btn = 'ביטול';
$alert_mandatory_fields = 'נא למלה את כל השדות החובות.';  

include 'menu_tasks.php'; 
?>
		<form method="post" action="" enctype="multipart/form-data" class="form-inline">
			<input type="hidden" id="id" value="<?=@$id?>" />
			<input type="hidden" id="project_id" value="<?=@$project_id?>" />
            <input type="hidden" id="from" value="<?=@$from?>" />			

			<div class="container">
				<div class="row alignCenter marginTop25">
					<div class="col-12">
						<a href="projects.php"><img src="images/davidnahmias_logo.png" width="170px" height="170px" /></a>
					</div>
			    </div>
				
				<div class="row marginTop15 title dir-rtl">	
					<div class="col-12">
						<a id="a_project_title" class="text-decoration-none color-1A5276" href="meetings.php?project_id=<?=@$project_id?>">
						    <strong class="fontSize33 line-height-1em"><?=@$project->nickname?></strong>
							<div class="fontSize26 font-family-david">פרוייקט <?=@$project->name_he?></div>
					    </a>		
					</div>				
			    </div>
				
				<div class="row marginTop10">
				   <div class="col-1"></div>
				   <div class="col-10 card bg-f2f6f9 border-frame borderRadius15 padding0">
						<div class="card-header bgColor-1a5276 colorWhite alignCenter fontSize20" style="border-top-left-radius:15px;border-top-right-radius:15px;"><strong><?=@$title?></strong></div>	  
				        <div class="card-body">
							<div class="row <?=@$dir?> marginTop10 alignCenter">
								<div class="col-12">	
									<strong><?=@$name_label?></strong>
									<br/>	
									<input type="text" class="paddingRight10 marginTop5" name="name" id="name" placeholder="*<?=@$name_label?>" value="<?=@$chapter->name?>" />	
								</div>
							</div>
						
							<div class="row <?=@$dir?> marginTop10 alignCenter">
								<div class="col-12">
									<div id="div_message_alert_down" class="marginTop10"></div>	
									<div class="marginTop10">
										<input type="button" id="save_btn" name="save_btn" class="btn marginTop5 bgColorBlue colorWhite mb-2" value="<?=@$title_save_btn?>" />		
										<?php if(@$from != "addProject"){ ?>
											<input type="button" id="cancel_btn" class="btn marginRight10 marginTop5 bgColorBlack colorWhite mb-2" value="<?=@$title_cancel_btn?>" />		
										<?php } 	
										 if(@$from == "addProject"){ ?>
											<input type="button" class="btn marginRight10 marginTop5 bgColorBlue colorWhite mb-2" value="עבור לדוחות" onclick="location.href='add_default_reports.php?project_id=<?=@$project_id?>'" <?=@$disabled_next_step_btn?>  />
										<?php } ?>	
									</div>						
								</div>
							</div>
 						</div>
					</div>
				</div>
            </div>			
		</form>

        <div id="progress-popup">
            <p>Loading in progress...</p>
            <div id="div-progress-bar">
                <div id="progress-bar"></div>
            </div>
        </div>		
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
		url: 'chapter_insert.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,
        beforeSend: function() {
			$("#progress-popup").show();
			let progress = 0;
			let interval = setInterval(function() {
				if (progress < 90) {
					progress += 10;
					$("#progress-bar").css("width", progress + "%");
				} else {
					clearInterval(interval);
				}
			}, 200);
			$("#progress-popup").data("interval", interval);
		},		
		success: function(data){ 
			if(data == 'empty')	{		
				if($('#name').val().length == 0)			
					$('#name').css('border-color','red');
				else if(!($('#name').val().length == 0))
					$('#name').css('border-color','initial');	
				
				$('#div_message_alert_down').html("<span style=color:red;font-size:13px;><?=@$alert_mandatory_fields?></span>");
			}
			else if(data == 'exists') 
				$('#div_message_alert_down').html("<span style=color:red;font-size:13px;>פרק זה כבר קיים בפרוייקט זה</span>");
			else {
				if($('#from').val() == 'addProject')
					window.location.reload();
			    else	
			       location.href = 'chapters.php?project_id='+$('#project_id').val();
			}				
		}
	});												       			   
})

$('#cancel_btn').click(function(){
    location.href = "chapters.php?project_id="+$('#project_id').val();	
})
</script>

<style>
.btn:hover {
   color: white;
}

.bgColorBlack:hover {
	background-color: #45484d;
}

.bgColorBlue:hover {
	background-color:#3370d6;
}

#a_project_title:hover {
	color: grey;
}
</style>