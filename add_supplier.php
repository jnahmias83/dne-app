<?php
include 'include/header.php';
include 'functions/functions.php';

$id = @$_GET['id'];
$project_id = @$_GET['project_id'];
$type_sup = @$_GET['type_sup'];
$from = @$_GET['from'];

$query = $mysqli->prepare("SELECT * FROM dne_logos LIMIT 1");
$query->execute();
$query->store_result();
$logo = fetch_unique($query);

$sup_type = 'S';
$des_type = 'D';

if($type_sup == 'S') 
	$title_page = 'פרטי ספק/קבלן <br/> הוספה/עדכון'; 

if($type_sup == 'D') 
	 $title_page = 'פרטי מתכנן <br/> הוספה/עדכון';
 
if($id > 0) {
	$query = $mysqli->prepare("SELECT * FROM dne_suppliers WHERE id = ?");
	$query->bind_param("i",$id);
	$query->execute();
	$query->store_result();
	$supplier = fetch_unique($query);
}

$query = $mysqli->prepare("SELECT DISTINCT name_he,id FROM dne_sup_field_of_work WHERE sup_type = ? ORDER BY name_he ASC");
$query->bind_param("s",$sup_type);
$query->execute();
$query->store_result();
$sup_field_of_work = fetch($query);

$query = $mysqli->prepare("SELECT DISTINCT name_he,id FROM dne_sup_field_of_work WHERE sup_type = ? ORDER BY name_he ASC");
$query->bind_param("s",$des_type);
$query->execute();
$query->store_result();
$des_field_of_work = fetch($query);

$name_label = 'Name';
$name_he_label = 'שם בעברית';
$nickname_label = 'Nickname';
$phone_label = 'Phone';
$cellular_label = 'Cellular';
$email_office_label = 'Email office';
$bank_details_label = 'Bank details';
$account_owner_label = 'Account owner';
$account_owner_explain_label = 'שם חברה כפי שהיא מופיעה <br/> בהזמנה/חשבונית ';
$bank_label = 'Bank';
$branch_label = 'Branch';
$account_number_label = 'Account #';
$swift_label = 'Swift';
$iban_label = 'Iban';
$title_save_btn = 'שמור';
$title_cancel_btn = 'ביטול';
$alert_mandatory_fields = 'Please fill all the mandatory fields.';
$alert_duplicate_domain = 'Please enter a domain that not already exist on the following list.';
?>          

		<form method="post" action="" enctype="multipart/form-data" class="form-inline">
			<input type="hidden" id="id" value="<?=@$id?>" />
            <input type="hidden" id="project_id" value="<?=@$project_id?>" /> 			
			<input type="hidden" id="type" value="<?=@$type_sup?>" />
			<input type="hidden" id="from" value="<?=@$from?>" />
			
			<div class="container">
			    <br/>

				<div class="row alignCenter">
					<div class="col-12">
						<a href="projects.php"><img src="images/<?=@$logo->logo?>" width="120" height="114" /></a>
					</div>
			    </div>
			
				<div class="row marginTop5">
					<div class="col-12 title fontSize20">
						<?=@$title_page?>
					</div>
				</div>

                <div class="row marginTop10">
				    <div class="col-2"></div>
					<div class="col-8 border-frame">
					    <div class="flex justify-content-space-around flex-wrap marginTop20 dir-rtl">
							<div class="alignCenter width-one-of-three">
                                <strong class="text-size">
								   <?php if(@$type_sup == 'S') echo 'שם ספק קבלן בעברית';else if(@$type_sup == 'D') echo 'שם מתכנן בעברית';else if(@$type_sup == 'M') echo 'שם מנהל בעברית';?>
								</strong>
								
								<div class="row marginTop10">
									<div class="col-12">
									    <input type="text" class="paddingRight10 width90Percents" name="supplier_name_he" id="supplier_name_he" placeholder="<?=@$name_he_label?>" value="<?=@$supplier->name_he?>" />    
									</div>
								</div>
							</div>

                            <div class="alignCenter width-one-of-three">
                                <strong class="text-size">EN</strong>
								
								<div class="row marginTop10">
									<div class="col-12">
									    <input type="text" class="paddingLeft10 width90Percents alignLeft" name="supplier_name" id="supplier_name" placeholder="<?=@$name_label?>" value="<?=@$supplier->name?>" />    
									</div>
								</div>
							</div>

                            <div class="alignCenter width-one-of-three">
                                <strong class="text-size">
								   Nickname EN
								</strong>
								
								<div class="row marginTop10">
									<div class="col-12">
									    <input type="text" class="paddingLeft10 width90Percents alignLeft" name="supplier_nickname" id="supplier_nickname" placeholder="<?=@$nickname_label?>" value="<?=@$supplier->nickname?>" />    
									</div>
								</div>
							</div>							
                        </div>

                        <?php if($type_sup != 'M'){ ?>
							<div id="suppliers_list_div">							
								<div class="row marginTop20 alignCenter">
									<div class="col-12">
										<strong class="text-size">
											<?php if(@$type_sup == 'S') echo 'תחום פעילות';else if(@$type_sup == 'D') echo 'תחום יעוץ/תכנון'?>
										</strong>
									</div>
								</div>												
								<div class="row marginTop10 alignCenter">
									<div class="col-12">
										<select id="sup_field_of_work" class="paddingRight10 dir-rtl">							
											<?php 
											if($type_sup == 'S') { 
												foreach($sup_field_of_work as $item) {
												?>
													<option value="<?=@$item->id?>" <?php if($item->id == @$supplier->id_field_of_work) echo "selected";?>>
														<?=@$item->name_he?>
													</option>
													<?php
												}
											}
											else if($type_sup == 'D') { 
												foreach($des_field_of_work as $item) {
													?>
														<option value="<?=@$item->id?>" <?php if($item->id == @$supplier->id_field_of_work) echo "selected";?>>
															<?=@$item->name_he?>
														</option>
														<?php
													}
											}
											?>						
										</select>
									 </div>
								</div>								
							</div>
                        <?php } ?>					   
						
						<div class="flex justify-content-space-around flex-wrap marginTop20">
							<div class="alignCenter width-one-of-three">
							    <strong class="text-size"><?=@$cellular_label?></strong>
								
							    <div class="row marginTop10">
									<div class="col-12">				
						                <input type="text" class="paddingLeft10 width90Percents" name="supplier_mobile" id="supplier_mobile" placeholder="<?=@$cellular_label?>" value="<?=@$supplier->mobile?>" />	
							        </div>
								</div>
							</div>
							
							<div class="alignCenter width-one-of-three">
                                <strong class="text-size"><?=@$phone_label?></strong>
								 
							    <div class="row marginTop10">
									<div class="col-12">
                                        <input type="text" class="paddingLeft10 width90Percents" name="supplier_phone" id="supplier_phone" placeholder="<?=@$phone_label?>" value="<?=@$supplier->phone?>" />	
							        </div>
								</div>
							</div>
							
							<div class="alignCenter width-one-of-three">
							    <strong class="text-size"><?=@$email_office_label?></strong>
							    
                                <div class="row marginTop10">
									<div class="col-12">								
						                <input type="email" class="paddingLeft10 width90Percents" name="supplier_email_office" id="supplier_email_office" placeholder="*<?=@$email_office_label?>" value="<?=@$supplier->email_office?>" />				    
							        </div>
							    </div>
							</div>
						</div>
						
						<div class="row marginTop20 fontSize18 alignCenter">
							<div class="col-12">
								<?=@$bank_details_label?>
							</div>					
				       </div>
					   
					   <div class="flex justify-content-space-around flex-wrap marginTop20">
							<div class="alignCenter width-one-of-three">
							    <strong class="text-size"><?=@$account_owner_label?></strong>
								
							    <div class="row marginTop10">
									<div class="col-12">			
						                <input type="text" class="paddingLeft10 width90Percents" name="supplier_account_owner" id="supplier_account_owner" placeholder="<?=@$account_owner_label?>" value="<?=@$supplier->bank_account_owner?>" />
                                    </div>	
                                </div>
                                
                                <div class="row marginTop5">
								     <div class="col-12 fontSize12 alignCenter">
									     <?=@$account_owner_explain_label?>
									 </div>
                                </div>								
							</div>
							
							<div class="alignCenter width-one-of-three">
							    <strong class="text-size"><?=@$bank_label?></strong>
								
							    <div class="row marginTop10">
									<div class="col-12">				
						                <input type="text" class="paddingLeft10 width90Percents" name="supplier_bank_name" id="supplier_bank_name" placeholder="<?=@$bank_label?>" value="<?=@$supplier->bank_name?>" />				 
							        </div>
							    </div>
							</div>
							
							<div class="alignCenter width-one-of-three">
							    <strong class="text-size"><?=@$branch_label?></strong>
								
							    <div class="row marginTop10">
									<div class="col-12">		
						                <input type="text" class="paddingLeft10 width90Percents" name="supplier_bank_branche" id="supplier_bank_branche" placeholder="<?=@$branch_label?>" value="<?=@$supplier->bank_branche?>" />
							        </div>
								</div>
							</div>
						</div>
						
						<div class="flex justify-content-space-around flex-wrap marginTop20">
						    <div class="alignCenter width-one-of-three">
                                <strong class="text-size"><?=@$account_number_label?></strong>
								
								<div class="row marginTop10">
									<div class="col-12">	
									    <input type="text" class="paddingLeft10 width90Percents" name="supplier_account_number" id="supplier_account_number" placeholder="<?=@$account_number_label?>" value="<?=@$supplier->bank_account_number?>" />	
									</div>
								</div>
							</div>

                            <div class="alignCenter width-one-of-three">
                                <strong class="text-size"><?=@$swift_label?></strong>
								
								<div class="row marginTop10">
									<div class="col-12">	
									    <input type="text" class="paddingLeft10 width90Percents" name="supplier_swift" id="supplier_swift" placeholder="<?=@$swift_label?>" value="<?=@$supplier->swift?>" />	
									</div>
								</div>
						    </div>

                            <div class="alignCenter width-one-of-three">
                                 <strong class="text-size"><?=@$iban_label?></strong>
								 
								 <div class="row marginTop10">
									<div class="col-12">	
									    <input type="text" class="paddingLeft10 width90Percents" name="supplier_iban" id="supplier_iban" placeholder="<?=@$iban_label?>" value="<?=@$supplier->iban?>" />	
									</div>
								</div>
						    </div>							
						</div>
						
						<div class="row marginTop20 alignCenter dir-rtl">
							<div class="col-12">
								<div id="div_message_alert_down"></div>	
								<input type="button" id="cancel_btn" class="btn marginTop10 bgColorBlack colorWhite marginLeft8 mb-2" value="<?=@$title_cancel_btn?>" />
								<input type="button" id="save_btn" name="save_btn" class="btn marginTop10 colorWhite bgColorBlue mb-2" value="<?=@$title_save_btn?>" />						
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
$('#field_of_work_name').on('change', function (){
	$('#suppliers_list_div').hide();
});

$('#sup_field_of_work').chosen();

$('#save_btn').click (function (e){  
    let supplier_type = 'S';
    if($('#type').val() == 'D') 
	   supplier_type = 'D';
    if($('#type').val() == 'M') 
	   supplier_type = 'M';
  
	let form_data = new FormData();	
	form_data.append('id',$('#id').val());
	form_data.append('supplier_name',$('#supplier_name').val());
	form_data.append('supplier_name_he',$('#supplier_name_he').val());
	form_data.append('supplier_nickname',$('#supplier_nickname').val());
	form_data.append('supplier_type',supplier_type);
	form_data.append('id_field_of_work',$('#sup_field_of_work').val());
	form_data.append('supplier_phone',$('#supplier_phone').val());
	form_data.append('supplier_mobile',$('#supplier_mobile').val());
	form_data.append('supplier_email_office',$('#supplier_email_office').val());
	form_data.append('supplier_account_owner',$('#supplier_account_owner').val());
	form_data.append('supplier_bank_name',$('#supplier_bank_name').val());
	form_data.append('supplier_bank_branche',$('#supplier_bank_branche').val());
	form_data.append('supplier_account_number',$('#supplier_account_number').val());
	form_data.append('supplier_swift',$('#supplier_swift').val());
	form_data.append('supplier_iban',$('#supplier_iban').val());
	
	$.ajax({
		type: 'POST',
		url: 'supplier_insert.php',
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
			else if(data == 'illegalbankname')
				alert('Illegal bank name - numeric & two digits.');
			else if(data == 'illegalbankbranche')
				alert('Illegal bank branche - numeric & three digits.');
			else if(data == 'empty') {
				if($('#supplier_name').val().length == 0)			
					$('#supplier_name').css('border-color','red');
				else if(!($('#supplier_name').val().length == 0))
					$('#supplier_name').css('border-color','initial');	
				
				if($('#supplier_email_office').val().length == 0)			
					$('#supplier_email_office').css('border-color','red');
				else if(!($('#supplier_email_office').val().length == 0))
					$('#supplier_email_office').css('border-color','initial');	
				
				$('#div_message_alert_down').html("<span style=color:red;font-size:13px;><?=@$alert_mandatory_fields?></span>"); 
			}
			else if(data == 'exists') 
				$('#div_message_alert_down').html("<span style=color:red;font-size:13px;><?=@$alert_duplicate_domain?></span>"); 
			else {
			    let url = 'suppliers.php?type='+$('#type').val();
				if($('#type').val() == 'M')
					url = 'users.php';
			    if($('#from').val() == 'add_sup_to_proj')
			       url = 'add_sup_to_proj.php?id='+$('#project_id').val();
			    else if($('#from').val() == 'payments_wires')
			       url = 'payments_wires.php?project_id='+$('#project_id').val();
			    location.href = url;  
			}			   
		},
	});												       			   
})

$('#cancel_btn').click(function(){
	let url = 'suppliers.php?type='+$('#type').val();
	if($('#type').val() == 'M')
		url = 'users.php';
	if($('#from').val() == 'add_sup_to_proj')
	   url = 'add_sup_to_proj.php?id='+$('#project_id').val();
	else if($('#from').val() == 'payments_wires')
	   url = 'payments_wires.php?project_id='+$('#project_id').val();
    location.href = url;  
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

@media (max-width: 1500px) {
	.text-size {
	   font-size: 12px;
    }
}

@media (max-width: 1024px) {
	.text-size {
	   font-size: 11px;
    }
}

@media (max-width: 640px) {
	.text-size {
	   font-size: 0.6rem;
    }
}

@media (max-width: 520px) {
	.text-size {
	   font-size: 0.7rem;
    }
}
</style>