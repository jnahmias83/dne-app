<?php
include 'include/header.php';
include 'functions/functions.php';

$query = $mysqli->prepare("SELECT * FROM dne_logos LIMIT 1");
$query->execute();
$query->store_result();
$logo = fetch_unique($query);

$id = @$_GET['id'];

$domain_color = '#000000';
$domain_bgcolor = '#ffffff';

if($id > 0) {
	$query = $mysqli->prepare("SELECT * FROM dne_sup_field_of_work WHERE id = ?");
	$query->bind_param("i",$id );
	$query->execute();
	$query->store_result();
	$domain = fetch_unique($query);
	
	if($domain->color != '')
		$domain_color = $domain->color;
	
	if($domain->bgcolor != '')
		$domain_bgcolor = $domain->bgcolor;
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
					<div class="row title mx-2 fontSize20 alignCenter dir-rtl">
						<div class="col-12">
							<span>הוספת/עדכון תחום</span>
						</div>
					</div>
				<?php } ?>
				
					<div class="row marginTop15 dir-rtl">
				    <div class="col-2"></div>
					<div class="col-8 padding0">
						<div class="card width95Percents alignCenter dir-rtl">
						    <div class="card-header bgColorSilver"><strong>הוספת/עדכון תחום</strong></div>
							<div class="card-body">
							    <p>
								<?php
								if($id == 0) { ?>
									<select id="sup_type" class="width100 height40 paddingLeft10">
									   <option value="S">ספק</option>
									   <option value="D">מתכנן</option>		 
									</select>
								<?php }
								else { 
										$type = 'ספק';
										if(@$domain->sup_type == 'D')
										   $type = 'מתכנן';
								?>
									<input type="text" class="marginTop10 paddingRight10" name="supplier_type" id="supplier_type" value="<?=@$type?>" disabled="true" />
								<?php } ?> 
								</p>
								
								<div class="row flex width90Percents margin-top-20-x-auto alignCenter">
									<div class="width50Percents">
										<strong>שם בעברית</strong>
										<br/>
										<input type="text" class="marginTop5 paddingRight5 width90Percents" name="domain_name_he" id="domain_name_he" placeholder="*שם בעברית" value="<?=@$domain->name_he?>" />
									</div>	
									<div class="width50Percents">		 			
										<strong>Name</strong>	
										<br/>
										<input type="text" class="marginTop5 paddingRight5 width90Percents" name="domain_name" id="domain_name" placeholder="*Name" value="<?=@$domain->name?>" />			
									</div>				
								</div> 

                                <div class="row flex width90Percents margin-top-20-x-auto alignCenter">
									<div class="width50Percents">
										<strong>כינוי בעברית</strong>
										<br/>
										<input type="text" class="marginTop5 paddingRight5 width90Percents" name="domain_nickname_he" id="domain_nickname_he" placeholder="*כינוי בעברית" value="<?=@$domain->nickname_he?>" />
									</div>			
									<div class="width50Percents">
										<strong>Nickname</strong>
										<br/>
										<input type="text" class="marginTop5 paddingRight5 width90Percents" name="domain_nickname" id="domain_nickname" placeholder="*Nickname" value="<?=@$domain->nickname?>" />
									</div>  								
								</div>

								<div class="row flex width90Percents margin-top-20-x-auto alignCenter">
									<div class="width50Percents">
										<strong>גוון רקע</strong>
										<br/>
										<input type="color" class="marginTop5 width70Percents" name="domain_bgcolor" id="domain_bgcolor" placeholder="Bgcolor" value="<?=@$domain_bgcolor?>" />				
									</div>	
									<div class="width50Percents">
										<strong>גוון גופן</strong>
										<br/>
										<input type="color" class="marginTop5 width70Percents" name="domain_color" id="domain_color" placeholder="Color" value="<?=@$domain_color?>" />
									</div>		
								</div>

                                <div class="row marginTop20 alignCenter">
									<div class="col-12">
										<div id="div_message_alert_down"></div>
										<input type="button" id="save_btn" name="save_btn" class="btn colorWhite bgColorBlue mb-2" value="שמור" />						
										<input type="button" id="cancel_btn" class="btn bgColorBlack colorWhite marginRight8 mb-2" value="ביטול" />
									</div>
								</div>									
							</div>
						</div>
					</div>
					<div class="col-2"></div>
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
	form_data.append('sup_type',$('#sup_type').val());
	form_data.append('domain_name',$('#domain_name').val());
	form_data.append('domain_name_he',$('#domain_name_he').val());
	form_data.append('domain_nickname',$('#domain_nickname').val());
	form_data.append('domain_nickname_he',$('#domain_nickname_he').val());
	form_data.append('domain_color',$('#domain_color').val());
	form_data.append('domain_bgcolor',$('#domain_bgcolor').val());
	$.ajax({
		type: 'POST',
		url: 'domain_insert.php',
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
				alert('The supplier name or the supplier nickname contains hebrew characters.');
			else if(data == 'englishchars')
				alert('The supplier hebrew name contains english characters.');
			else if(data == 'empty')	{
				if($('#domain_name').val().length == 0)			
					$('#domain_name').css('border-color','red');
				else if(!($('#domain_name').val().length == 0))
					$('#domain_name').css('border-color','initial');	
				
				if($('#domain_name_he').val().length == 0)			
					$('#domain_name_he').css('border-color','red');
				else if(!($('#domain_name_he').val().length == 0))
					$('#domain_name_he').css('border-color','initial');	
				
				if($('#domain_nickname').val().length == 0)			
					$('#domain_nickname').css('border-color','red');
				else if(!($('#domain_nickname').val().length == 0))
					$('#domain_nickname').css('border-color','initial');

                if($('#domain_nickname_he').val().length == 0)			
					$('#domain_nickname_he').css('border-color','red');
				else if(!($('#domain_nickname_he').val().length == 0))
					$('#domain_nickname_he').css('border-color','initial');				
				
				$('#div_message_alert_down').html("<span style=color:red;font-size:13px;>Please fill all the mandatory fields</span>"); 
			}
			else
				location.href = 'domains.php?lang_screen=HE';		
		},
	});												       			   
})

$('#cancel_btn').click(function(){
    location.href = 'domains.php?lang_screen=HE';	
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