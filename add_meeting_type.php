<?php
include 'include/header.php';
include 'functions/functions.php';

$query = $mysqli->prepare("SELECT * FROM dne_logos LIMIT 1");
$query->execute();
$query->store_result();
$logo = fetch_unique($query);

$id = @$_GET['id'];

$query = $mysqli->prepare("SELECT * FROM dne_meetings_types WHERE id = ?");
$query->bind_param("i",$id);
$query->execute();
$query->store_result();
$meeting_type = fetch_unique($query);

$page_lang = !empty($_SESSION['project_lang']) ? $_SESSION['project_lang'] : @$_SESSION['lang'];

if($page_lang == 'EN') {
  $dir = 'dir-ltr';
  $add_meeting_type_label = 'Add meeting type';
  $name_label = "Name";
  $name_he_label = "Name He";
  $title_label = "Title 1";
  $title_he_label = "Title 1 He";
  $subtitle_label = "Title 2";
  $subtitle_he_label = "Title 2 He";
  $title_save_btn = 'Save';
  $title_cancel_btn = 'Cancel';
  $alert_mandatory_fields = 'Please fill all the mandatory fields.';
}

if($page_lang == 'HE' || $page_lang == '') {
   $dir = 'dir-rtl';
   $add_meeting_type_label = 'הוסף סוג פגישה';
   $name_label = "שם";
   $name_he_label = "שם בעברית";
   $title_label = "כותרת 1";
   $title_he_label = "כותרת 1 בעברית";
   $subtitle_label = "כותרת 2";
   $subtitle_he_label = "כותרת 2 בעברית";
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
			
				<div class="row alignCenter marginTop5">
					<div class="col-12">
						<a href="projects.php"><img src="images/<?=@$logo->logo?>" width="170" height="170" /></a>
					</div>
			    </div>
				
			    <?php
				if($id == 0) { ?>
					<div class="row title marginTop10 <?=@$dir?>">
						<div class="col-12 fontSize20">
							<?=@$add_meeting_type_label?>
						</div>
					</div>
				<?php }
				else if($id > 0) { ?>
				   <div class="row title marginTop10 <?=@$dir?>">
						<div class="col-12 fontSize20">
							<span class="colorGreen"><?=@$meeting_type->name?></span>
						</div>
					</div>
				<?php } ?>	

				<div class="row">
				    <div class="col-xm-2 col-md-2 col-lg-3"></div>
					<div class="col-xm-8 col-md-8 col-lg-6">
					     <div class="row <?=@$dir?> marginTop20">
								<div class="col-6">	
									<strong><?=@$name_label?></strong>
									<br/>	
									<input type="text" class="marginTop5 width90Percents" name="name" id="name" placeholder="*שם" value="<?=@$meeting_type->name?>" />	
								</div>
							<div class="col-6">	
								<strong><?=@$name_he_label?></strong>
								<br/>	
								<input type="text" class="marginTop5 width90Percents" name="name_he" id="name_he" placeholder="*שם בעברית" value="<?=@$meeting_type->name_he?>" />	
							</div>
						</div>
				
						<div class="row <?=@$dir?> marginTop20">
							<div class="col-6">	
								<strong><?=@$title_label?></strong>
								<br/>	
								<input type="text" class="marginTop5 width90Percents" name="title" id="title" placeholder="כותרת 1" value="<?=@$meeting_type->title?>" />	
							</div>
							<div class="col-6">	
								<strong><?=@$title_he_label?></strong>
								<br/>	
								<input type="text" class="marginTop5 width90Percents" name="title_he" id="title_he" placeholder="כותרת 1 בעברית" value="<?=@$meeting_type->title_he?>" />	
							</div>
						</div>
				
						<div class="row <?=@$dir?> marginTop20">
							<div class="col-6">	
								<strong><?=@$subtitle_label?></strong>
								<br/>	
								<input type="text" class="marginTop5 width90Percents" name="subtitle" id="subtitle" placeholder="כותרת 2" value="<?=@$meeting_type->subtitle?>" />	
							</div>
							<div class="col-6">	
								<strong><?=@$subtitle_he_label?></strong>
								<br/>	
								<input type="text" class="marginTop5 width90Percents" name="subtitle_he" id="subtitle_he" placeholder="כותרת 2 בעברית" value="<?=@$meeting_type->subtitle_he?>" />	
							</div>
						</div>
						
						<div class="row <?=@$dir?> marginTop20 alignCenter">
							<div class="col-12">
								<div id="div_message_alert_down"></div>	
								<input type="button" id="save_btn" name="save_btn" class="btn bgColorBlue colorWhite mb-2" value="<?=@$title_save_btn?>" />		
								<input type="button" id="cancel_btn" class="btn marginRight10 bgColorBlack colorWhite mb-2" value="<?=@$title_cancel_btn?>" />				
							</div>
						</div>	
					</div>
					<div class="col-xm-2 col-md-2 col-lg-3"></div>
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
	form_data.append('name',$('#name').val());
	form_data.append('name_he',$('#name_he').val());
	form_data.append('title',$('#title').val());
	form_data.append('title_he',$('#title_he').val());
	form_data.append('subtitle',$('#subtitle').val());
	form_data.append('subtitle_he',$('#subtitle_he').val());
	$.ajax({
		type: 'POST',
		url: 'meeting_type_insert.php',
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
                if(data == 'hebrewchars') 
				    alert("The meeting's type name or title or subtitle contains hebrew characters.");
				else if(data == 'englishchars')
				    alert("The meeting's type name or title or subtitle contains english characters.");			
				else if(data == 'empty') {		
				    if($('#name').val().length == 0)			
					   $('#name').css('border-color','red');
				    else if(!($('#name').val().length == 0))
					   $('#name').css('border-color','initial');	
				
					if($('#name_he').val().length == 0)			
						$('#name_he').css('border-color','red');
					else if(!($('#name_he').val().length == 0))
						$('#name_he').css('border-color','initial');
					
					$('#div_message_alert_down').html("<span style=color:red;font-size:13px;><?=@$alert_mandatory_fields?></span>");
				}
			    else
				  location.href = 'projects.php';		
		}
	});												       			   
})

$('#cancel_btn').click(function(){
    location.href = 'projects.php';	
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
</style>