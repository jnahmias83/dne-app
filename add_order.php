<?php
include 'include/header.php';
include 'functions/functions.php';

$id = @$_GET['id'];
$project_id = @$_GET['project_id'];
$ps_id = @$_GET['ps_id'];
$from = @$_GET['from'];
$lang_screen = @$_GET['lang_screen'];

$query = $mysqli->prepare("SELECT o.id AS id,o.id_projects_suppliers AS id_projects_suppliers,
                          o.sum_order AS sum_order,o.pdf_order AS pdf_order,o.vat AS vat,
						  o.signature_date AS signature_date,o.description AS description,
						  s.name_he AS s_name_he
						  FROM dne_orders o
						  LEFT JOIN dne_projects_suppliers ps ON o.id_projects_suppliers = ps.id
						  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
						  WHERE o.id = ?");
$query->bind_param("i",$id);
$query->execute();
$query->store_result();
$order = fetch_unique($query);

$query = $mysqli->prepare("SELECT * FROM dne_vat");
$query->execute();
$query->store_result();
$vat = fetch_unique($query);

$query = $mysqli->prepare("SELECT * FROM dne_projects WHERE id = ?");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$project = fetch_unique($query);   

$type = 'E';
$query = $mysqli->prepare("SELECT ps.id AS id,s.name_he AS s_name_he
                          FROM dne_projects_suppliers ps
						  LEFT JOIN dne_projects p ON ps.id_project = p.id
						  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
						  WHERE ps.id_project = ? AND s.type <> ?
                          ORDER BY s_name_he ASC");
$query->bind_param("is",$project_id,$type);
$query->execute();
$query->store_result();
$suppliers = fetch($query);

$title = 'הזמנה חדשה'; 

if(@$id > 0) 
	$title = 'אישור הזמנה חדשה';

include 'menu_budget_reports.php';  
?>

		<form method="post" action="" enctype="multipart/form-data" class="form-inline">
			<input type="hidden" id="id" value="<?=@$id?>" />
			<input type="hidden" id="project_id" value="<?=@$project_id?>" />
			<input type="hidden" id="ps_id" value="<?=@$ps_id?>" />
			<input type="hidden" id="from" value="<?=@$from?>" />
			<input type="hidden" id="lang_screen" value="<?=@$lang_screen?>" />

			<div class="container">
			    <br/>
			
				<div class="row alignCenter marginTop25">
					<div class="col-12">
						<a href="projects.php"><img src="images/davidnahmias_logo.png" width="170px" height="170px" /></a>
					</div>
			    </div>
				
				<div class="row marginTop15 title dir-rtl">	
					<div class="col-12">
						<a id="a_project_title" class="text-decoration-none color-1A5276" href="budget.php?project_id=<?=@$project_id?>&lang_screen=<?=@$lang_screen?>">
						    <strong class="fontSize33 line-height-1em"><?=@$project->nickname?></strong>
							<div class="fontSize26 font-family-david">פרוייקט <?=@$project->name_he?></div>
					    </a>		
					</div>				
			    </div>
				
				<?php if($id > 0) { ?>
				    <div class="row title font-family-david dir-rtl">
						<div class="col-12 font-weight-bold fontSize26">
							<?=@$order->s_name_he?>
						</div>
				    </div>
			    <?php } ?>
				
				<div class="row marginTop10">
				   <div class="col-1"></div>
				   <div class="col-10 card bg-f2f6f9 border-frame borderRadius15 padding0">
						<div class="card-header bgColor-1a5276 colorWhite alignCenter fontSize20" style="border-top-left-radius:15px;border-top-right-radius:15px;"><strong><?=@$title?></strong></div>	  
				        <div class="card-body">
							<div class="row">
								<div class="col-2"></div>
								<div class="col-8">
									<div class="row alignCenter dir-rtl">
										<div class="col-12">
											<strong>ספק</strong>
										</div>
									</div>		
									
									<div class="row marginTop10 alignCenter dir-rtl">
										<div class="col-12">
											<select id="suppliers">							
												<?php 
													foreach($suppliers as $item) {
													?>
														<option value="<?=@$item->id?>" <?php if(($id == 0 && $item->id == @$ps_id) ||($id > 0 && $item->id == @$order->id_projects_suppliers)) echo "selected";?>>
															<?=@$item->s_name_he?>
														</option>
														<?php
													}
												?>										
											</select>
										</div>
									</div>
						
									<div class="row marginTop10 fontSize14 alignCenter dir-rtl">
										<div class="col-12">
											<strong>מע''מ</strong>
											&nbsp;						
											<input type="text" class="width60" name="vat" id="vat" placeholder="*VAT" value="<?php if($id == 0) echo @$vat->vat;else echo $order->vat?>" />&nbsp;%	
										</div>
									</div>		
							
									<div class="row marginTop10 alignCenter dir-rtl">
										<div class="col-12">
											<strong class="fontSize14">סכום הזמנה לא כולל מע''מ</strong>
											<br/>									
											<input type="text" class="marginTop10 paddingRight10 width200" name="sum_order" id="sum_order" placeholder="סכום הזמנה לא כולל מע''מ" value="<?=@$order->sum_order?>" onchange="$('#signature_date').val('<?=date('Y-m-d')?>')" />	
										</div>
									</div>
							
									<div class="row marginTop10 alignCenter dir-rtl">
										<div class="col-12">
											<strong>PDF הזמנה</strong>
											<br/>					
											<input type="file" class="marginTop10" name="pdf_order" id="pdf_order" accept=".pdf" />
											<?php if($id > 0) { ?>&nbsp;<a href="uploads/<?=@$order->pdf_order?>" target="_blank"><?=@$order->pdf_order?></a><?php } ?>			
										</div>
									</div>	
									
									<div class="row marginTop10 alignCenter dir-rtl">
										<div class="col-12">
											<strong>תאריך חתימה</strong>
											<br/>						
											<input type="date" class="marginTop10 paddingRight10 alignRight" name="signature_date" id="signature_date" value="<?=@$order->signature_date?>" />	
										</div>
									</div>
							
									<div class="row marginTop10 alignCenter dir-rtl">
										<div class="col-12">
											<strong>תיאור</strong>			
										</div>
									</div>
						
									<div class="row dir-rtl">
										<div class="col-3"></div>
										<div class="col-6 paddingTop2">
											<div class="alignRight"> 
											   <a class="text-decoration-none" onclick="$('#description').css({'direction':'ltr','padding-left':'10px'})">EN</a>&nbsp;|
											   <a class="text-decoration-none" onclick="$('#description').css({'direction':'rtl','padding-right':'10px'})">HE</a>	
											</div>
											<textarea class="marginTop5 width95Percents paddingRight10" name="description" id="description" rows="5" cols="18" placeholder="תיאור..."><?=@$order->description?></textarea>
										</div>
										<div class="col-3"></div>
									</div>
						
									<div class="row marginTop20 alignCenter">
										<div class="col-12">
											<div id="div_message_alert_down"></div>	
											<input type="button" id="cancel_btn" class="btn bgColorBlack colorWhite marginRight8 mb-2" value="ביטול" />
											<input type="button" id="save_btn" name="save_btn" class="btn bgColorBlue colorWhite mb-2" 
											value="שמור" />						
										</div>
									</div>				    
								</div>
								<div class="col-2"></div>					
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
$('#suppliers').chosen();

$('#save_btn').click(function(e) {
	let pdf_file = $('#pdf_order')[0].files[0];

	function doSubmit(form_data) {
		$.ajax({
			type: 'POST',
			url: 'order_insert.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,
			timeout: 60000,
			beforeSend: function() {
				$("#progress-popup").show();
				$("#div_message_alert_down").html('');
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
			complete: function() {
				clearInterval($("#progress-popup").data("interval"));
				$("#progress-bar").css("width", "100%");
				setTimeout(function() {
					$("#progress-popup").hide();
					$("#progress-bar").css("width", "0%");
				}, 300);
			},
			error: function(xhr, status, error) {
				$('#div_message_alert_down').html("<span style='color:red;font-size:13px;'>Upload failed (" + status + "). Please try again.</span>");
			},
			success: function(data) {
				if(data == 'empty' || data.indexOf('no_file') === 0) {
					if($('#sum_order').val().length == 0)
						$('#sum_order').css('border-color','red');
					else
						$('#sum_order').css('border-color','initial');
					if($('#signature_date').val().length == 0)
						$('#signature_date').css('border-color','red');
					else
						$('#signature_date').css('border-color','initial');
					if($('#pdf_order').val().length == 0)
						$('#pdf_order').css('border-color','red');
					else
						$('#pdf_order').css('border-color','initial');
					$('#div_message_alert_down').html("<span style='color:red;font-size:13px;'>Please fill all the mandatory fields</span>");
				} else if(data == 'inserted' || data == 'updated') {
					let url;
					if($('#from').val() == 'budget')
						url = 'budget.php?project_id='+$('#project_id').val()+'&lang_screen='+$('#lang_screen').val();
					else if($('#from').val() == 'accounts_payments')
						url = 'accounts_payments.php?ps_id='+$('#ps_id').val()+'&lang_screen='+$('#lang_screen').val();
					else
						url = 'orders.php?project_id='+$('#project_id').val()+'&lang_screen='+$('#lang_screen').val();
					location.href = url;
				} else {
					$('#div_message_alert_down').html("<span style='color:red;font-size:13px;'>Upload error. Please try again.</span>");
				}
			}
		});
	}

	let form_data = new FormData();
	form_data.append('id', $('#id').val());
	form_data.append('id_projects_suppliers', $('#suppliers').val());
	form_data.append('sum_order', $('#sum_order').val());
	form_data.append('vat', $('#vat').val());
	form_data.append('signature_date', $('#signature_date').val());
	form_data.append('description', $('#description').val());

	if (pdf_file) {
		if (pdf_file.size === 0) {
			$('#div_message_alert_down').html("<span style='color:red;font-size:13px;'>File not ready. Please download it first and try again.</span>");
			return;
		}
		let reader = new FileReader();
		reader.onload = function(e) {
			let blob = new Blob([e.target.result], { type: pdf_file.type || 'application/pdf' });
			form_data.append('pdf_order', blob, pdf_file.name);
			doSubmit(form_data);
		};
		reader.onerror = function() {
			$('#div_message_alert_down').html("<span style='color:red;font-size:13px;'>Cannot read file. Please try again.</span>");
		};
		reader.readAsArrayBuffer(pdf_file);
	} else {
		doSubmit(form_data);
	}
})
$('#cancel_btn').click(function(){
    let url;
    if($('#from').val() == 'budget')
     url = 'budget.php?project_id='+$('#project_id').val()+'&lang_screen='+$('#lang_screen').val();
	else if($('#from').val() == 'accounts_payments')
	   url = 'accounts_payments.php?ps_id='+$('#ps_id').val()+'&lang_screen='+$('#lang_screen').val();
	else
	   url = 'orders.php?project_id='+$('#project_id').val()+'&lang_screen='+$('#lang_screen').val();
	location.href = url;
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

.chosen-container .chosen-results .active-result {
    color: black;
}
</style>