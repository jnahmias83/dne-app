<?php
include 'include/header.php';
include 'functions/functions.php';
error_reporting(E_ALL);
ini_set('display_errors', '1');

$id = @$_GET['id'];

$query = $mysqli->prepare("SELECT ps.id_project AS id_project,p.nickname AS nickname,p.name AS name,ps.code_ps AS code_ps,ps.in_charge_name AS in_charge_name,ps.in_charge_phone AS in_charge_phone,ps.in_charge_email AS in_charge_email
                          FROM dne_projects_suppliers ps
						  LEFT JOIN dne_projects p ON ps.id_project = p.id
						  WHERE ps.id = ?");
$query->bind_param("i",$id);
$query->execute();
$query->store_result();
$projects_suppliers = fetch_unique($query);
?>          

		<form method="post" action="" enctype="multipart/form-data" class="form-inline">
			<input type="hidden" id="id" value="<?=@$id?>" />
			<input type="hidden" id="project_id" value="<?=@$projects_suppliers->id_project?>" />
			
			<div class="row" style="margin-top:25px;text-align:center;">
			    <div class="col-md-12">
				    <img src="images/davidnahmias_logo.png" width="170px" height="170px" />
				</div>
			</div>
			<div class="row" style="margin-top:20px;text-align:center;">
				<div class="col-md-12" style="font-size:20px;">
					<span style="color:blue;"><?=@$projects_suppliers->nickname."<br/>".@$projects_suppliers->name."<br/>".substr(date('Y-m-d'),8,2)."/".substr(date('Y-m-d'),5,2)."/".substr(date('Y-m-d'),0,4)?></span>
				</div>
			</div>
			
			<div class="row" style="margin-top:20px;margin-left:30%;">
				<div class="col-md-12" style="padding-top:2px;">
					<input type="text" class="form-control" style="width:60%;" name="in_charge_name" id="in_charge_name" placeholder="In charge name" value="<?=@$projects_suppliers->in_charge_name?>" />	
				</div>
			</div>
			
			<div class="row" style="margin-top:20px;margin-left:30%;">
				<div class="col-md-12" style="padding-top:2px;">
					<input type="text" class="form-control" style="width:60%;" name="in_charge_phone" id="in_charge_phone" placeholder="In charge phone" value="<?=@$projects_suppliers->in_charge_phone?>" />	
				</div>
			</div>
			
			<div class="row" style="margin-top:20px;margin-left:30%;">
				<div class="col-md-12" style="padding-top:2px;">
					<input type="text" class="form-control" style="width:60%;" name="in_charge_email" id="in_charge_email" placeholder="In charge email" value="<?=@$projects_suppliers->in_charge_email?>" />	
				</div>
			</div>
																							
			<div class="row" style="margin-left:30%;">
				<div class="col-md-12">
					<div id="div_message_alert_down" style="margin-top:10px;"></div>	
					<input type="button" id="cancel_btn" style="margin-top:20px;background-color:black;margin-right:8px;" class="btn btn-primary mb-2" value="Cancel" />
					<input type="button" id="save_btn" name="save_btn" style="margin-top:20px;background-color:#218FD6;" class="btn btn-primary mb-2" 
					value="Save" />						
				</div>
			</div>					
		</form>
    </body>
</html>

<script>
$('#save_btn').click (function (e){   
	var form_data = new FormData();	
	form_data.append('id',$('#id').val());
	form_data.append('in_charge_name',$('#in_charge_name').val());
	form_data.append('in_charge_phone',$('#in_charge_phone').val());
	form_data.append('in_charge_email',$('#in_charge_email').val());
	$.ajax({
		type: 'POST',
		url: 'in_charge_update_db.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,			
		success: function(data){  
		    location.href = 'add_sup_to_proj.php?id='+$('#project_id').val();					
		},
	});												       			   
})

$('#cancel_btn').click(function(){
    location.href = 'add_sup_to_proj.php?id='+$('#project_id').val();	
})
</script>