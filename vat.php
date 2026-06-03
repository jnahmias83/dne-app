<?php
include 'include/header.php';
include 'functions/functions.php';

$query = $mysqli->prepare("SELECT * FROM dne_logos LIMIT 1");
$query->execute();
$query->store_result();
$logo = fetch_unique($query);

$query = $mysqli->prepare("SELECT vat FROM dne_vat");
$query->execute(); 
$query->store_result();
$vat = fetch_unique($query);
?>
        <form method="post" action="" class="form-inline">	
			<div class="container">
			    <div class="row marginTop25 alignCenter">
					<div class="col-12">
						<a href="projects.php"><img src="images/<?=@$logo->logo?>" width="120" height="114" /></a>
					</div>
				</div>
				
				<div class="row marginTop20 dir-rtl alignCenter">	
					<div class="row">
						<div class="col-12">
							<strong>מע''ם</strong>
							<input type="text" class="width90 marginRight8" id="vat" name="vat" value="<?=@$vat->vat?>" onchange="setVat();" />&nbsp;%
						</div>		        		
					</div>					
				</div>
			</div>
		</form>
	</body>
</html>

<script>
function setVat(){
	let form_data = new FormData();	
	form_data.append('vat',$('#vat').val());
	
	$.ajax({
		type: 'POST',
		url: 'set_vat.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,			
		success: function(data){
			location.reload(true);			
		},
	});		
}
</script>