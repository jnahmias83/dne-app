<?php
include 'include/header.php';
include 'functions/functions.php';

$query = $mysqli->prepare("SELECT * FROM dne_logos LIMIT 1");
$query->execute();
$query->store_result();
$logo = fetch_unique($query);

$query = $mysqli->prepare("SELECT * FROM dne_logos LIMIT 1");
$query->execute();
$query->store_result();
$logo = fetch_unique($query); 
?>
		<form method="post" action="" enctype="multipart/form-data" class="form-inline">
			<div class="container">
				<div class="row alignCenter marginTop25">
					<div class="col-12">
						<a href="projects.php"><img src="images/<?=@$logo->logo?>" width="170" height="170" /></a>
					</div>
					
					<div class="row marginTop5 title fontSize20">	
						<div class="col-12">					
						   תמונות לוגו
						</div>					
					</div>
					
					<div class="row flex margin-top-10-x-auto dir-rtl width70Percents alignCenter">
						<div class="width50Percents">
							<strong class="text-size">לוגו</strong>
							<br/>
							
							<label for="logo" class="custom-file-upload">בחר קובץ</label>
							<input id="logo" name="logo" class="file-upload" type="file" hidden>		
							
							<div id="div-logo" class="row marginTop25 <?php echo(!empty($logo->logo))?"display-block":"display-none";?>">
								<div class="col-12">
									<div class="logo-container" id="logo-container">
										<img id="preview-logo" src="uploads/<?=@$logo->logo?>" alt="Chosen image" width="180" height="150">
									</div>
								</div>
							</div>
							
						</div>	
						<div class="width50Percents">					
							<strong class="text-size">לוגו סטריד</strong>
							<br/>
							
							<label for="logo-stread" class="custom-file-upload">בחר קובץ</label>
							<input id="logo-stread" name="logo_stread" class="file-upload" type="file" hidden>		
							
							
							<div id="div-logo-stread" class="row marginTop25 <?php echo(!empty($logo->logo_stread))?"display-block":"display-none";?>">
								<div class="col-12">
									<div class="logo-stread-container" id="logo-stread-container">
										<img id="preview-logo-stread" src="uploads/<?=@$logo->logo_stread?>" alt="Chosen image" width="300" height="100">
									</div>
								</div>
							</div>							
						</div>										
					</div>
					
					<div class="row marginTop15 alignCenter dir-rtl">
						<div class="col-12">
							<div id="div_message_alert_down"></div>
							<input type="button" id="save_btn" name="save_btn" class="btn bgColorBlue colorWhite mb-2" value="שמור" />					
							<input type="button" id="cancel_btn" class="btn bgColorBlack colorWhite marginRight5 mb-2" value="בטל" />						
						</div>
					</div>	
				</div>
			</div>
		</form>
	</body>
</html>

<script>
let logo,logo_stread = '';
let div_logo = $("#div-logo"); 
let div_logo_stread = $("#div-logo-stread"); 
let preview_logo = $('#preview-logo');
let preview_logo_stread = $('#preview-logo-stread');
let logo_container = $('#logo-container');
let logo_stread_container = $('#logo-stread-container');

$(document).on('change','#logo', function (){
	$('#div-logo').show();
	logo = $('#logo')[0].files[0];	
	let logo_name = logo.name;

	let reader = new FileReader();

	reader.onload = function (e) {
		preview_logo.attr("src",e.target.result);	
		div_logo.css("display","block");
		logo_container.css("display","block");
	};
	
	reader.readAsDataURL(logo);
});

$(document).on('change','#logo-stread', function (){
	$('#div-logo-stread').show();
	logo_stread = $('#logo-stread')[0].files[0];	
	let logo_stread_name = logo_stread.name;

	let reader = new FileReader();

	reader.onload = function (e) {
		preview_logo_stread.attr("src",e.target.result);	
		div_logo_stread.css("display","block");
		logo_stread_container.css("display","block");
	};
	
	reader.readAsDataURL(logo_stread);
});

$(document).on('click','#save_btn', function (){
	let form_data = new FormData();
	form_data.append('logo', logo);
	form_data.append('logo_stread', logo_stread);
	
	$.ajax({
		type: 'POST',
		url: 'save_logo.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,			
		success: function(data){
			window.location.reload();
		}	
	});
});

$(document).on('click','#cancel_btn', function (){
	location.href = 'projects.php';
});
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