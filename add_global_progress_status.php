<?php
include 'include/header.php';
include 'functions/functions.php';

$query = $mysqli->prepare("SELECT * FROM dne_logos LIMIT 1");
$query->execute();
$query->store_result();
$logo = fetch_unique($query);

$id = @$_GET['id'];

$query = $mysqli->prepare("SELECT * FROM dne_global_progress_status WHERE id = ?");
$query->bind_param("i",$id);
$query->execute();

$query->store_result();
$progress_status = fetch_unique($query);
?>
		<form method="post" action="" enctype="multipart/form-data" class="form-inline">
			<input type="hidden" id="id" value="<?=@$id?>" />					
			<br/>
			
			<div class="container">
			    <div class="row alignCenter marginTop5">
					<div class="col-md-12">
						<a href="projects.php"><img src="images/<?=@$logo->logo?>" width="120" height="114" /></a>
					</div>
			    </div>

				<?php
				if($id == 0) { ?>
					<div class="row title">
						<div class="col-md-12">
							הוסף סטטוס התקדמות
						</div>
					</div>
				<?php }
				else if($id > 0) { ?>
				   <div class="row marginTop20 dir-rtl alignCenter">
						<div class="col-md-12 fontSize20">
							<span style="color:#5bbd8d;"><?=@$progress_status->name_he?></span>
						</div>                                                      
					</div>
				<?php } ?>

                <div class="row marginTop10 dir-rtl alignCenter">
					<div class="col-md-12">	
						<strong>שם</strong>
						<br/>	
						<input type="text" class="marginTop5 width250" name="name" id="name" placeholder="*שם" value="<?=@$progress_status->name?>" />		
					</div>
				</div>				
				
				<div class="row marginTop10 dir-rtl alignCenter">
					<div class="col-md-12">	
						<strong>שם בעברית</strong>
						<br/>	
						<input type="text" class="marginTop5 width250" name="name_he" id="name_he" placeholder="*שם בעברית" value="<?=@$progress_status->name_he?>" 
						<?php if(@$id > 0 && (@$progress_status->name_he == ' ' || @$progress_status->name_he == 'בביצוע' || @$progress_status->name_he == 'איחור' || @$progress_status->name_he == 'בוצע/נמסר' || @$progress_status->name_he == 'ארכיון' || @$progress_status->name_he == 'הנחיה/החלטה')) echo "readonly";?> />	
					</div>
				</div>

				<div class="row marginTop10 dir-rtl alignCenter">
					<div class="col-md-12">
						<strong>צבע גופן</strong>
						<br/>						
						<input type="color" class="marginTop5 width150" name="color" id="color" placeholder="צבע גופן" value="<?=@$progress_status->color?>" />
					</div>
				</div>

				<div class="row marginTop10 dir-rtl alignCenter">
					<div class="col-md-12">
						<strong>צבע רקע</strong>
						<br/>					
						<input type="color" class="marginTop5 width150" name="bgcolor" id="bgcolor" placeholder="צבע רקע" value="<?php if($id == 0) echo '#ffffff';else echo @$progress_status->bgcolor;?>" />
					</div>
				</div>				
						
				<div class="row marginTop10 dir-rtl alignCenter">
					<div class="col-md-12">
						<div id="div_message_alert_down" class="marginTop10"></div>	
						<input type="button" id="save_btn" name="save_btn" class="btn marginTop5 bgColorBlue colorWhite mb-2" value="שמור" />		
						<input type="button" id="cancel_btn" class="btn marginRight10 marginTop5 bgColorBlack colorWhite mb-2" value="ביטול" />				
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
	var form_data = new FormData();	
	form_data.append('id',$('#id').val());
	form_data.append('id_project',0);
	form_data.append('name',$('#name').val());
	form_data.append('name_he',$('#name_he').val());
	form_data.append('color',$('#color').val());
	form_data.append('bgcolor',$('#bgcolor').val());
	$.ajax({
		type: 'POST',
		url: 'progress_status_insert.php',
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
				alert('The name contains hebrew characters.');
			else if(data == 'englishchars')
				alert('The hebrew name contains english characters.');			
			else if(data == 'empty'){
                if($('#name').val().length == 0)			
					$('#name').css('border-color','red');
				else if(!($('#name').val().length == 0))
					$('#name').css('border-color','initial');	
				
				if($('#name_he').val().length == 0)			
					$('#name_he').css('border-color','red');
				else if(!($('#name_he').val().length == 0))
					$('#name_he').css('border-color','initial');	
				
				$('#div_message_alert_down').html("<span style=color:red;font-size:13px;>נא למלה את כל השדות החובות</span>");
			}
			else if(data == 'exists') 
				$('#div_message_alert_down').html("<span style=color:red;font-size:13px;>סטטוס התקדמות זה כבר קיים במערכת</span>");	
			else
				location.href = 'projects.php';		
		}
	});												       			   
})

$('#cancel_btn').click(function(){
    location.href = "projects.php";	
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
	background-color: #3370d6;
}
</style>