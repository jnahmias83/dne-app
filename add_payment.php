<?php
include 'include/header.php';
include 'functions/functions.php';

$id = @$_GET['id'];
$project_id = @$_GET['project_id'];
$ps_id = @$_GET['ps_id'];
$from = @$_GET['from'];
$lang_screen = @$_GET['lang_screen'];

$query = $mysqli->prepare("SELECT * FROM dne_projects WHERE id = ?");
$query->bind_param("i",$project_id );
$query->execute(); 
$query->store_result();
$project = fetch_unique($query);

$query = $mysqli->prepare("SELECT p.id AS id,p.id_projects_suppliers AS id_projects_suppliers,
                          p.description AS description,p.pdf_payment AS pdf_payment,
						  p.payment_date AS payment_date,p.paid_amount AS paid_amount,
						  p.pdf_invoice AS pdf_invoice,p.invoice_date AS invoice_date,p.vat AS vat,
						  s.name_he AS s_name_he
                          FROM dne_payments2 p 
						  LEFT JOIN dne_projects_suppliers ps ON p.id_projects_suppliers = ps.id
						  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
						  WHERE p.id = ?");
$query->bind_param("i",$id );
$query->execute();
$query->store_result();
$payment = fetch_unique($query);

$paid_amount = @$payment->paid_amount;

$query = $mysqli->prepare("SELECT ap.id AS ap_id,p.id AS p_id,a.id AS a_id,
                          ap.account_payment_date AS account_payment_date,
						  ap.account_payment_type AS account_payment_type,
						  p.description AS p_description,
						  a.description AS a_description,
						  p.pdf_payment AS pdf_payment,
						  p.paid_amount AS paid_amount_vat_included,
						  p.paid_amount_vat_excluded AS paid_amount_vat_excluded,
						  a.approved_amount AS approved_amount
						  FROM dne_accounts_payments ap
						  LEFT JOIN dne_accounts a ON ap.id_account = a.id
						  LEFT JOIN dne_payments2 p ON ap.id_payment = p.id		 
						  LEFT JOIN dne_projects_suppliers ps 
						  ON ap.id_projects_suppliers = ps.id 
						  WHERE ps.id = ?
						  ORDER BY ap.account_payment_date");
$query->bind_param("i",$ps_id);
$query->execute(); 
$query->store_result();
$accounts_payments = fetch($query);

$total_approved_amount_vat_included = 0;
$total_paid_amount_vat_included = 0;

foreach($accounts_payments as $item) {
	if(@$item->account_payment_type == 'payment' || @$item->account_payment_type == 'account' && @$item->approved_amount > 0) {
		$total_approved_amount_vat_included +=$item->approved_amount;
		$total_paid_amount_vat_included +=$item->paid_amount_vat_included;
	}
}

$pending_payment = '';

if($total_approved_amount_vat_included-$total_paid_amount_vat_included >= 0) {
	$pending_payment = $total_approved_amount_vat_included-$total_paid_amount_vat_included;									
}

if($from == 'accounts_payments' && $pending_payment > 0)
   $paid_amount = @$pending_payment;
   
$query = $mysqli->prepare("SELECT * FROM dne_vat");
$query->execute();
$query->store_result();
$vat = fetch_unique($query);

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

$title = 'תשלום חדש';

$payment_date = date('Y-m-d');
if($id > 0) {
   $title = 'אישור תשלום חדש';
   $payment_date = @$payment->payment_date;
}

include 'menu_budget_reports.php';
?>

		<form method="post" action="" enctype="multipart/form-data" class="form-inline">
			<input type="hidden" id="id" value="<?=@$id?>" />
			<input type="hidden" id="project_id" value="<?=@$project_id?>" />	
			<input type="hidden" id="ps_id" value="<?=@$ps_id?>" />	
			<input type="hidden" id="from" value="<?=@$from?>" />	
			<input type="hidden" id="lang_screen" value="<?=@$lang_screen?>" />

			<div class="container">
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
							<?=@$payment->s_name_he?>
						</div>
				   </div>
			    <?php } ?>
				
				<div class="row marginTop10">
				   <div class="col-1"></div>
				   <div class="col-10 card bg-f2f6f9 border-frame borderRadius15 padding0">
						<div class="card-header bgColor-1a5276 colorWhite alignCenter fontSize20" style="border-top-left-radius:15px;border-top-right-radius:15px;"><strong><?=@$title?></strong></div>	  
				        <div class="card-body">
							<div class="row alignCenter dir-rtl">
								<div class="col-12">
									<strong>ספק</strong>
								</div>
							</div>

                            <div class="row marginTop10 alignCenter dir-rtl">
								<div class="col-12">
									<select id="suppliers" class="width170">							
										<?php 
											foreach($suppliers as $item) {
											?>
												<option value="<?=@$item->id?>" <?php if(($id == 0 && $item->id == @$ps_id) || ($item->id == @$payment->id_projects_suppliers)) echo "selected";?>>
													<?=@$item->s_name_he?>
												</option>
												<?php
											}
										?>										
									</select>
								</div>
							</div>
 
                            <div class="row marginTop20 alignCenter dir-rtl">
								<div class="col-12 paddingTop2">
									<strong>תיאור</strong>					
								</div>
							</div> 
							
							<div class="row dir-rtl">
								<div class="col-4"></div>
								<div class="col-4">
									<a class="text-decoration-none" onclick="$('#description').css({'direction':'ltr','padding-left':'10px'})">EN</a>&nbsp;|
									<a class="text-decoration-none" onclick="$('#description').css({'direction':'rtl','padding-right':'10px'})">HE</a>	
								</div>
								<div class="col-4"></div>
							</div>
							
							<div class="row alignCenter dir-rtl">
								<div class="col-12 paddingTop2">
									<textarea class="marginTop5 paddingRight10 width350" name="description" id="description" rows="5" placeholder="תיאור..."><?=@$payment->description?></textarea>
								</div>
							</div>
							
							<div class="row m-4 dir-rtl">
								<div class="col-2"></div>
								<div class="col-4 padding0">
									<div class="card width95Percents height280 alignCenter">
										<div class="card-header bgColorSilver"><strong class="fontSize14">תשלום שבוצע</strong></div>
										<div class="card-body">
											<p>
											   <strong class="label-font-size">PDF תשלום</strong>
											   <br/>					
											   <input type="file" class="marginTop5 width95Percents" name="pdf_payment" id="pdf_payment" accept=".pdf,application/pdf" />
											   <?php if($id > 0) { ?>&nbsp;<a href="uploads/<?=@$payment->pdf_payment?>" target="_blank"><?=@$payment->pdf_payment?></a><?php } ?>		
											</p>
											<p>
											  <strong class="label-font-size">תאריך תשלום</strong>
											  <br/>					
											  <input type="date" class="marginTop5 width90Percents date-font-size paddingRight10 alignRight" name="payment_date" id="payment_date" value="<?=@$payment_date?>" />	
											</p>
											<p>
												<strong class="label-font-size">סך שולם</strong>
												<br/> 				
											  <input type="text" class="marginTop5 width95Percents paddingRight10 alignRight" name="paid_amount" id="paid_amount" placeholder="סך שולם" value="<?=@$paid_amount?>" />
											</p>
										</div>	
									</div>
								</div>
					
								<div class="col-4 padding0">
									<div class="card width95Percents height280 alignCenter">
										<div class="card-header bgColorSilver"><strong class="fontSize14">חשבונית מס</strong></div>
										<div class="card-body">
										   <p>
											 <strong class="label-font-size">PDF חשבונית</strong>
											 <br/>					
											 <input type="file" class="marginTop5 width95Percents" name="pdf_invoice" id="pdf_invoice" accept=".pdf,application/pdf" />
											 <?php if($id > 0) { ?>&nbsp;<a href="uploads/<?=@$payment->pdf_invoice?>" target="_blank"><?=@$payment->pdf_invoice?></a><?php } ?>		
										   </p>
										   <p>
											 <strong class="label-font-size">תאריך חשבונית</strong>
											 <br/>					
											 <input type="date" class="marginTop5 width90Percents date-font-size paddingRight10 alignRight" name="invoice_date" id="invoice_date" value="<?=@$payment->invoice_date?>" />	
										   </p>
										   
										   <div class="row marginTop20">
												<div class="col-12">                   						
													<strong class="label-font-size">מע''מ</strong>		
													<br/>	
													<input type="text" class="width60 paddingRight10" name="vat" id="vat" placeholder="VAT" value="<?php if($id == 0) echo @$vat->vat;else echo @$payment->vat?>" />&nbsp;%	
											   </div>
										   </div>			
										</div>
									</div>	
								</div>
								<div class="col-2"></div>
							</div>	

							<div class="row alignCenter">
								<div class="col-12">			
									<div id="div_message_alert_down"></div>
									<input type="button" id="cancel_btn" class="btn bgColorBlack colorWhite marginRight8 mb-2" value="ביטול" />
									<input type="button" id="save_btn" name="save_btn" class="btn bgColorBlue colorWhite mb-2" 
									value="שמור" />						
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
$('#suppliers').chosen();

$('#save_btn').click(function(e) {
	let pdf_payment_file = $('#pdf_payment')[0].files[0];
	let pdf_invoice_file = $('#pdf_invoice')[0].files[0];

	function readFileAsBlob(file) {
		return new Promise(function(resolve, reject) {
			if (!file) { resolve(null); return; }
			if (file.size === 0) { reject('empty:' + file.name); return; }
			let reader = new FileReader();
			reader.onload = function(e) {
				resolve({ blob: new Blob([e.target.result], { type: file.type || 'application/pdf' }), name: file.name });
			};
			reader.onerror = function() { reject('read:' + file.name); };
			reader.readAsArrayBuffer(file);
		});
	}

	function doSubmit(form_data) {
		$.ajax({
			type: 'POST',
			url: 'payment_insert.php',
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
				if(data == 'empty') {
					if($('#paid_amount').val().length == 0)
						$('#paid_amount').css('border-color','red');
					else
						$('#paid_amount').css('border-color','initial');
					$('#div_message_alert_down').html("<span style='color:red;font-size:13px;'>Please fill all the mandatory fields</span>");
				} else {
					let url;
					if($('#from').val() == 'budget')
						url = 'budget.php?project_id='+$('#project_id').val()+'&lang_screen='+$('#lang_screen').val();
					else if($('#from').val() == 'accounts_payments')
						url = 'accounts_payments.php?ps_id='+$('#ps_id').val()+'&lang_screen='+$('#lang_screen').val();
					else if($('#from').val() == 'payments_missing_invoice')
						url = 'payments_missing_invoice.php?project_id='+$('#project_id').val()+'&lang_screen='+$('#lang_screen').val();
					else
						url = 'payments.php?project_id='+$('#project_id').val();
					location.href = url;
				}
			}
		});
	}

	let form_data = new FormData();
	form_data.append('id', $('#id').val());
	form_data.append('id_projects_suppliers', $('#suppliers').val());
	form_data.append('description', $('#description').val());
	form_data.append('payment_date', $('#payment_date').val());
	form_data.append('paid_amount', $('#paid_amount').val());
	form_data.append('invoice_date', $('#invoice_date').val());
	form_data.append('vat', $('#vat').val());

	Promise.all([readFileAsBlob(pdf_payment_file), readFileAsBlob(pdf_invoice_file)])
		.then(function(results) {
			if (results[0]) form_data.append('pdf_payment', results[0].blob, results[0].name);
			if (results[1]) form_data.append('pdf_invoice', results[1].blob, results[1].name);
			doSubmit(form_data);
		})
		.catch(function(err) {
			let msg = err.indexOf('empty:') === 0
				? 'File not ready. Please download it first and try again.'
				: 'Cannot read file. Please try again.';
			$('#div_message_alert_down').html("<span style='color:red;font-size:13px;'>" + msg + "</span>");
		});
})
$('#cancel_btn').click(function(){
    let url; 
	if($('#from').val() == 'budget') 
	   url = 'budget.php?project_id='+$('#project_id').val()+'&lang_screen='+$('#lang_screen').val();
	else if($('#from').val() == 'accounts_payments') 
	   url = 'accounts_payments.php?ps_id='+$('#ps_id').val()+'&lang_screen='+$('#lang_screen').val();   
	else if($('#from').val() == 'payments_missing_invoice') 
	   url = 'payments_missing_invoice.php?project_id='+$('#project_id').val()+'&lang_screen='+$('#lang_screen').val();  					   		  
	else 
	   url = 'payments.php?project_id='+$('#project_id').val()+'&lang_screen='+$('#lang_screen').val();  
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

@media (max-width: 1024px) {
	.label-font-size {
		font-size: 14px;
	}
	
	.date-font-size {
		font-size: 14px;
	}
}

@media (max-width: 768px) {
	.label-font-size {
		font-size: 0.7rem;
	}
	
	.date-font-size {
		font-size: 0.8rem;
	}
}

@media (max-width: 600px) {
	.label-font-size {
		font-size: 0.7rem;
	}
	
	.date-font-size {
		font-size: 0.6rem;
	}
}
</style>