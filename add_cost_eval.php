<?php
include 'include/header.php';
include 'functions/functions.php';

$id = @$_GET['id'];
$project_id = @$_GET['project_id'];
$lang_screen = @$_GET['lang_screen'];

$sup_type = 'S';
$des_type = 'D';

$display_suppliers_list = 'none';
$display_designers_list = 'none';

$title = 'אומדן עלויות חדש';

if($id > 0) {
	$query = $mysqli->prepare("SELECT * FROM dne_budget_costs_eval WHERE id = ?");
    $query->bind_param("i",$id);
    $query->execute();
    $query->store_result();
    $budget_cost_eval = fetch_unique($query);

    $query = $mysqli->prepare("SELECT * FROM dne_sup_field_of_work WHERE id = ?");
    $query->bind_param("i",$budget_cost_eval->id_field_of_work);
    $query->execute();
    $query->store_result();
    $field_of_work = fetch_unique($query);
	
	$query = $mysqli->prepare("SELECT cem.id AS cem_id,cem.id_budget_costs_eval AS id_budget_costs_eval, cem.description AS description,cem.unit AS unit,cem.quantity AS quantity,cem.price AS price 
	                          FROM dne_costs_eval_modul cem
							  LEFT JOIN dne_budget_costs_eval bce ON cem.id_budget_costs_eval = bce.id
							  WHERE bce.id = ?");
    $query->bind_param("i",$id);
    $query->execute();
    $query->store_result();
	$costs_eval_modul_num_rows = $query->num_rows;
    $costs_eval_modul = fetch($query);
	
	$display_cem_btn = 'none';
	$display_costs_eval_table = 'none';
	
	if($costs_eval_modul_num_rows == 0)
	   $display_cem_btn = 'block';  
  
	if($costs_eval_modul_num_rows > 0) 
	   $display_costs_eval_table = 'block';
	
	if(@$field_of_work->sup_type == 'S') 
	    $display_suppliers_list = 'block';
	else if(@$field_of_work->sup_type == 'D') 
		$display_designers_list = 'block';
	
	$title = 'עדכון אומדן';
}

$query = $mysqli->prepare("SELECT * FROM dne_projects WHERE id = ?");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$project = fetch_unique($query);

$query = $mysqli->prepare("SELECT DISTINCT name,id FROM dne_sup_field_of_work WHERE sup_type = ? ORDER BY name ASC");
$query->bind_param("s",$sup_type);
$query->execute();
$query->store_result();
$sup_domains = fetch($query);

$query = $mysqli->prepare("SELECT DISTINCT name,id FROM dne_sup_field_of_work WHERE sup_type = ? ORDER BY name ASC");
$query->bind_param("s",$des_type);
$query->execute();
$query->store_result();
$des_domains = fetch($query);

$dir = 'dir-rtl';
$supplier_label = "ספק";
$designer_label = "מתכנן";
$suppliers_domains_label = "תחומי ספקים";
$designers_domains_label = "תחומי מתכננים";
$choose_domain_label = 'בחור תחום';
$description_label = "תיאור";
$modul_num_label = "מס'";
$pdf_evaluation_label = 'PDF ערכה';
$modul_desc_label = 'תיאור';
$modul_unit_label = 'יחידה';
$modul_qte_label = 'כמות';
$modul_price_label = 'מחיר';
$modul_total_price_label = "סך מחיר";
$modul_total_price_vat_excluded_label = "סכום סך מחיר לא כולל מע''מ";
$modul_total_price_vat_label = "סכום סך מחיר מע''מ (17%)";
$modul_total_price_vat_included_label = "סכום סך מחיר כולל מע''מ";
$evaluation_cost_label = "מחיר הערכה";
$evaluation_date_label = "תאריך הערכה";
$title_add_cost_evaluation_modul_btn = 'הוסף מודול הערכת עלויות';
$title_save_btn = 'שמור';
$title_modul_save_btn = 'שמור';
$title_cancel_btn = 'ביטול';
$msg_req_fields = 'נא למלאות את כל השדות החובות'; 
$msg_confirm_delete = 'האם אתה בטוח למחוק את הערכת העלויות הזאת ?';

include 'menu_budget_reports.php';
?>
		<form method="post" action="" enctype="multipart/form-data" class="form-inline">
			<input type="hidden" id="id" value="<?=@$id?>" />
			<input type="hidden" id="project_id" value="<?=@$project_id?>" />

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
				   <div class="row marginTop5 title font-family-david dir-rtl">
						<div class="col-12 font-weight-bold line-height-1em fontSize26">
							 <?=@$budget_cost_eval->name?>
						</div>
				   </div>
				<?php } ?>
				
				<div class="row marginTop10">
				   <div class="col-1"></div>
				   <div class="col-10 card bg-f2f6f9 border-frame borderRadius15 padding0">
						<div class="card-header bgColor-1a5276 colorWhite alignCenter fontSize20" style="border-top-left-radius:15px;border-top-right-radius:15px;"><strong><?=@$title?></strong></div>	  
				        <div class="card-body">
							<div class="row marginTop10 alignCenter dir-rtl">
								<div class="col-1"></div>
								<div class="col-10">
									<div class="row">
										<div class="col-12">
											<input type="radio" id="domain_type" name="domain_type" value="S" onclick="displayDomainsList();" <?php if(@$field_of_work->sup_type == 'S') echo 'checked';?> />&nbsp;<?=@$supplier_label?>
											<input type="radio" id="domain_type" name="domain_type" value="D" onclick="displayDomainsList();" <?php if(@$field_of_work->sup_type == 'D') echo 'checked';?> />&nbsp;<?=@$designer_label?>
										</div>
									</div>
				
									<div id="div_suppliers_domains" style="display:<?=@$display_suppliers_list?>">
										<div class="row marginTop20">
											<div class="col-12">
												<strong><?=@$suppliers_domains_label?></strong>
											</div>
										</div>		
												
										<div class="row marginTop10">
											<div class="col-12">
												<select id="suppliers" class="height30 paddingRight10">							
													<option value="0">--- <?=@$choose_domain_label?> ---</option>
													<?php 
														foreach($sup_domains as $item) {
														?>
															<option value="<?=@$item->id?>" <?php if(@$item->id == @$budget_cost_eval->id_field_of_work) echo 'selected';?>>
																<?=@$item->name?>
															</option>
															<?php
														}
													?>										
												</select>
											</div>
										</div>
									</div>
					
									<div id="div_designers_domains" style="display:<?=@$display_designers_list?>">
										<div class="row marginTop20">
											<div class="col-12">
												<strong><?=@$designers_domains_label?></strong>
											</div>
										</div>		
													
										<div class="row marginTop10">
											<div class="col-12">
												<select id="designers" class="height30 paddingRight10">		
													<option value="0">--- <?=@$choose_domain_label?> ---</option>					
													<?php 
														foreach($des_domains as $item) {
														?>
															<option value="<?=@$item->id?>" <?php if(@$item->id == @$budget_cost_eval->id_field_of_work) echo 'selected';?>>
																<?=@$item->name?>
															</option>
															<?php
														}
													?>										
												</select>
											</div>
										</div>
									</div>
				
									<div class="row marginTop20">
										<div class="col-12 paddingTop2">
											<strong><?=@$description_label?></strong>													
										</div>
									</div>
									
									<div class="row dir-rtl">
										<div class="col-3"></div>
										<div class="col-6 paddingTop2">
											<div class="alignRight"> 
											   <a class="text-decoration-none cursor-pointer" onclick="$('#description').css({'direction':'ltr','padding-left':'10px'})">EN</a>&nbsp;|
											   <a class="text-decoration-none cursor-pointer" onclick="$('#description').css({'direction':'rtl','padding-right':'10px'})">HE</a>	
											</div>
											<textarea class="marginTop5 width350 paddingRight10" name="description" id="description" rows="5" cols="18" placeholder="<?=@$description_label?>..."><?=@$budget_cost_eval->description?></textarea>
										</div>
										<div class="col-3"></div>
									</div>
									
									<div class="row marginTop20">
										<div class="col-12">
											<strong><?=@$pdf_evaluation_label?></strong>
											<br/>					
											<input type="file" class="marginTop5" name="pdf_evaluation" id="pdf_evaluation" accept=".pdf" />
											<?php if($id > 0) { ?><div><a href="uploads/<?=@$budget_cost_eval->pdf_evaluation?>" target="_blank"><?=@$budget_cost_eval->pdf_evaluation?></a></div><?php } ?>			
										</div>
									</div>
									
									<div class="row marginTop20" style="display:<?=@$display_cem_btn?>">
										<div class="col-12">
											<input type="button" id="add_costs_eval_modul_btn" class="btn btn-primary mb-2" value="<?=@$title_add_cost_evaluation_modul_btn?>" />
										</div>
									</div>
				
									<?php if($id > 0) { ?>
										<div class="<?=@$dir?> marginTop15 paddingTop10 borderBlue" id="costs_eval_modul_div">
											<div class="row">
												<div class="col-12 my-3">
													<textarea class="marginTop5 width300 paddingRight10" id="cost_eval_description" name="cost_eval_description" rows="3" placeholder="<?=@$modul_desc_label?>..."></textarea>
												</div>
											</div>
											<div class="row paddingTop10 my-2">
												<div class="col-12">
													<input type="text" class="paddingRight10" id="cost_eval_unit" name="cost_eval_unit" placeholder="<?=@$modul_unit_label?>" />
												</div>
											</div>
											<div class="row paddingTop10 my-3">
												<div class="col-12">
													<input type="number" class="paddingRight10" id="cost_eval_quantity" name="cost_eval_quantity" placeholder="<?=@$modul_qte_label?>" />
												</div>
											</div>
											<div class="row paddingTop10 my-2">
												<div class="col-12">
													<input type="text" class="paddingRight10" id="cost_eval_price" name="cost_eval_price" placeholder="<?=@$modul_price_label?>" />
												</div>
											</div>
											<div class="row marginTop10 marginBottom10">
												<div class="col-12">
												<div id="ce_div_message_alert_down"></div>
												   <input type="button" id="save_add_cost_eval_btn" class="btn bgColorBlue colorWhite marginTop10 mb-2" value="<?=@$title_modul_save_btn?>" />
												</div>
											</div>
											<div class="row fontSize14" style="display:<?=@$display_costs_eval_table?>" >
												<div class="col-12 paddingTop20 marginBottom20 mx-3">
													<table align="center" border="1" cellpadding="5" cellspacing="5">
														<tr class="bgColorSilver height50">
															<th class="alignCenter" width="40px;"><?=@$modul_num_label?></th>
															<th class="alignCenter" width="200px;"><?=@$modul_desc_label?></th>
															<th class="alignCenter" width="60px;"><?=@$modul_unit_label?></th>
															<th class="alignCenter" width="80px;"><?=@$modul_qte_label?></th>
															<th class="alignCenter" width="100px;"><?=@$modul_price_label?></th>
															<th class="alignCenter" width="100px;"><?=@$modul_total_price_label?></th>
															<th width="40px;">&nbsp;</th>
															<th width="40px;">&nbsp;</th>
														</tr>
														
														<?php 
														$count = 0;
														$sum_total_price_vat_excluded = 0;
														$sum_total_price_vat = 0;
														$sum_total_price_vat_included = 0;
														
														foreach($costs_eval_modul as $item) { 
															$count++;
															
															$price = number_format(@$item->price,2,'.',',');
															$total_price = ($item->quantity)*($item->price);
															$sum_total_price_vat_excluded += $total_price;
															$sum_total_price_vat_included += 1.17*$total_price;
															$sum_total_price_vat += 0.17*$total_price;
															$total_price = number_format($total_price,2,'.',',');
														?>
														<tr height="35px;">
														   <td class="alignCenter"><?=@$count?></td>
														   <td class="text-align-right"><textarea id="cem_elem_description_<?=@$item->cem_id?>" class="paddingRight5" style="width:100%;"><?=@$item->description?></textarea></td>
														   <td class="text-align-left"><input type="text" id="cem_elem_unit_<?=@$item->cem_id?>" style="width:100%;" value="<?=@$item->unit?>" /></td>
														   <td class="text-align-right"><input type="number" id="cem_elem_quantity_<?=@$item->cem_id?>" style="width:100%;" value="<?=@$item->quantity?>" /></td>
														   <td class="text-align-right"><input type="text" id="cem_elem_price_<?=@$item->cem_id?>" style="width:100%;" value="<?=@$item->price?>" /></td>
														   <td class="text-align-right paddingRight5"><?=@$total_price?></td>
														   <td class="alignCenter"><img src="images/edit-button.svg" style="cursor:pointer;" title="Edite" onclick="editeCostsEvalModul(<?=@$item->cem_id?>);" /></td>
														   <td class="alignCenter"><img src="images/delete.svg" style="cursor:pointer;" title="Remove" onclick="return removeCostsEvalModul(<?=@$item->cem_id?>);" /></td>	
														</tr>
														<?php } 
														$sum_total_price_vat_excluded = number_format($sum_total_price_vat_excluded,2,'.',',');
														$sum_total_price_vat = number_format($sum_total_price_vat,2,'.',',');
														$sum_total_price_vat_included = number_format($sum_total_price_vat_included,2,'.',',');
														?>
														<tr height="35px;">
															<td colspan="5" class="text-align-right paddingRight5 bgColorSilver"><strong><?=@$modul_total_price_vat_excluded_label?></strong></td>
															<td class="text-align-right paddingRight5"><strong><?=@$sum_total_price_vat_excluded?></strong></td>
															<td colspan="2">&nbsp;</td>
														</tr>
														<tr height="35px;">
															<td colspan="5" class="text-align-right paddingRight5 bgColorSilver"><strong><?=@$modul_total_price_vat_label?></strong></td>
															<td class="text-align-right paddingRight5"><strong><?=@$sum_total_price_vat?></strong></td>
															<td colspan="2">&nbsp;</td>
														</tr>
														<tr height="35px;">
															<td colspan="5" class="text-align-right paddingRight5 bgColorSilver"><strong><?=@$modul_total_price_vat_included_label?></strong></td>
															<td class="text-align-right paddingRight5"><strong><?=@$sum_total_price_vat_included?></strong></td>
															<td colspan="2">&nbsp;</td>
														</tr>
													</table>
												</div>
											</div>
										</div>
									<?php } ?>
					
									<div class="row marginTop20">
										<div class="col-12 paddingTop2">
											<?php
											if($id > 0) { ?>
												<strong><?=@$evaluation_cost_label?></strong>
												<br/>
											<?php } ?>					
											<input type="text" class="marginTop10 paddingRight10" name="evaluation_cost" id="evaluation_cost" placeholder="<?=@$evaluation_cost_label?>" value="<?=@$budget_cost_eval->evaluation_cost?>" />	
										</div>
									</div>
				
									<div class="row marginTop20">
										<div class="col-12 paddingTop2">
											<strong><?=@$evaluation_date_label?></strong>
											<br/>						
											<input type="date" class="marginTop10 paddingRight10 alignRight" name="evaluation_date" id="evaluation_date" value="<?=@$budget_cost_eval->evaluation_date?>" />	
										</div>
									</div>
				
									<div class="row marginTop20">
										<div class="col-12">
											<div id="div_message_alert_down"></div>	
											<input type="button" id="cancel_btn" class="btn bgColorBlack colorWhite marginLeft8 mb-2" value="<?=@$title_cancel_btn?>" />
											<input type="button" id="save_btn" name="save_btn" class="btn bgColorBlue colorWhite mb-2" 
											value="<?=@$title_save_btn?>" />						
										</div>
									</div>  
								</div>
								<div class="col-1"></div>
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
let domain_type;

function displayDomainsList() {
	if($("input:radio[name='domain_type']").is(':checked'))
	    domain_type = $('#domain_type:checked').val();

	if(domain_type == 'S') {
		$('#div_suppliers_domains').css('display','block');
		$('#div_designers_domains').css('display','none');
	}
	else if(domain_type == 'D') {
		$('#div_suppliers_domains').css('display','none');
		$('#div_designers_domains').css('display','block');
	}
}

$('#add_costs_eval_modul_btn').click (function (e){ 
    if($("input:radio[name='domain_type']").is(':checked'))
	    domain_type = $('#domain_type:checked').val();
	
	let id_field_of_work = 0;
	if(domain_type == 'S')
	   id_field_of_work = $('#suppliers').val();
    else if(domain_type == 'D')
	   id_field_of_work = $('#designers').val();
	
	let form_data = new FormData();
	form_data.append('fromAddCem','Yes');
	form_data.append('id',0);
	form_data.append('id_project',$('#project_id').val());
	form_data.append('id_field_of_work',id_field_of_work);
	form_data.append('description',$('#description').val());
	form_data.append('evaluation_cost',$('#evaluation_cost').val());
	form_data.append('evaluation_date',$('#evaluation_date').val());

	let pdf_eval_file_new = $('#pdf_evaluation')[0].files[0];

	function doAjaxCem(fd) {
		$.ajax({
			type: 'POST',
			url: 'cost_eval_insert.php',
			data: fd,
			cache: false,
			processData: false,
			contentType: false,
			timeout: 60000,
			error: function(xhr, status) {
				alert('Upload failed (' + status + '). Please try again.');
			},
			success: function(data) {
				location.href = 'add_cost_eval.php?id='+data+'&project_id='+$('#project_id').val();
			}
		});
	}

	if (pdf_eval_file_new) {
		if (pdf_eval_file_new.size === 0) { alert('File not ready. Please download it first.'); return; }
		let r = new FileReader();
		r.onload = function(e) {
			form_data.append('pdf_evaluation', new Blob([e.target.result], { type: pdf_eval_file_new.type || 'application/pdf' }), pdf_eval_file_new.name);
			doAjaxCem(form_data);
		};
		r.readAsArrayBuffer(pdf_eval_file_new);
	} else {
		doAjaxCem(form_data);
	}		       			   
})

function editeCostsEvalModul(cem_id) {
	let cem_elem_description = '#cem_elem_description_'+cem_id;
	let cem_elem_unit = '#cem_elem_unit_'+cem_id;
	let cem_elem_quantity = '#cem_elem_quantity_'+cem_id;
	let cem_elem_price = '#cem_elem_price_'+cem_id;
	
	let form_data = new FormData();
	form_data.append('action','update');
	form_data.append('id',cem_id);
	form_data.append('description',$(cem_elem_description).val());
	form_data.append('unit',$(cem_elem_unit).val());
	form_data.append('quantity',$(cem_elem_quantity).val());
	form_data.append('price',$(cem_elem_price).val());
	
	$.ajax({
		type: 'POST',
		url: 'cost_eval_modul_insert.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,			
		success: function(data){ 
			if(data == 'empty')	{
				if($(cem_elem_unit).val().length == 0)			
					$(cem_elem_unit).css('border-color','red');
				else if(!($(cem_elem_unit).val().length == 0))
					$(cem_elem_unit).css('border-color','initial');	
				
				if($(cem_elem_quantity).val().length == 0)			
					$(cem_elem_quantity).css('border-color','red');
				else if(!($(cem_elem_quantity).val().length == 0))
					$(cem_elem_quantity).css('border-color','initial');	
				
				if($(cem_elem_price).val().length == 0)			
					$(cem_elem_price).css('border-color','red');
				else if(!($(cem_elem_price).val().length == 0))
					$(cem_elem_price).css('border-color','initial');	
				
				$('#ce_div_message_alert_down').html("<span style=color:red;font-size:13px;><?=@$msg_req_fields?></span>"); 
			}
			else
				location.reload();
		},
	});
}

function removeCostsEvalModul(cem_id) {
	if(confirm('<?=@$msg_confirm_delete?>')) {
        let form_data = new FormData();	
		form_data.append('cem_id',cem_id);			
		$.ajax({
			type: 'POST',
			url: 'cost_eval_modul_delete.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,			
			success: function(data){
				location.reload();			
			},
		});		
    }
    return false;
}

$('#save_add_cost_eval_btn').click (function (e){ 
	let form_data = new FormData();
	form_data.append('action','insert');
	form_data.append('id_budget_costs_eval',$('#id').val());
	form_data.append('description',$('#cost_eval_description').val());
	form_data.append('unit',$('#cost_eval_unit').val());
	form_data.append('quantity',$('#cost_eval_quantity').val());
	form_data.append('price',$('#cost_eval_price').val());
	
	$.ajax({
		type: 'POST',
		url: 'cost_eval_modul_insert.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,			
		success: function(data){ 
			if(data == 'empty')	{
				if($('#cost_eval_unit').val().length == 0)			
					$('#cost_eval_unit').css('border-color','red');
				else if(!($('#cost_eval_unit').val().length == 0))
					$('#cost_eval_unit').css('border-color','initial');	
				
				if($('#cost_eval_quantity').val().length == 0)			
					$('#cost_eval_quantity').css('border-color','red');
				else if(!($('#cost_eval_quantity').val().length == 0))
					$('#cost_eval_quantity').css('border-color','initial');	
				
				if($('#cost_eval_price').val().length == 0)			
					$('#cost_eval_price').css('border-color','red');
				else if(!($('#cost_eval_price').val().length == 0))
					$('#cost_eval_price').css('border-color','initial');	
				
				$('#ce_div_message_alert_down').html("<span style=color:red;font-size:13px;><?=@$msg_req_fields?></span>"); 
			}
			else
				location.reload();
		},
	});												       			   
})

$('#save_btn').click (function (e){ 
    if($("input:radio[name='domain_type']").is(':checked'))
	    domain_type = $('#domain_type:checked').val();
	
	let form_data = new FormData();	
	form_data.append('id',$('#id').val());
	form_data.append('id_project',$('#project_id').val());
	
	if(domain_type == 'S')
	   form_data.append('id_field_of_work',$('#suppliers').val());
    else if(domain_type == 'D')
	   form_data.append('id_field_of_work',$('#designers').val());
	form_data.append('description',$('#description').val());
	form_data.append('evaluation_cost',$('#evaluation_cost').val());
	form_data.append('evaluation_date',$('#evaluation_date').val());

	let pdf_eval_file = $('#pdf_evaluation')[0].files[0];

	function doAjaxSave(fd) {
		$.ajax({
			type: 'POST',
			url: 'cost_eval_insert.php',
			data: fd,
			cache: false,
			processData: false,
			contentType: false,
			timeout: 60000,
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
			complete: function() {
				clearInterval($("#progress-popup").data("interval"));
				$("#progress-bar").css("width","100%");
				setTimeout(function(){ $("#progress-popup").hide(); $("#progress-bar").css("width","0%"); }, 300);
			},
			error: function(xhr, status) {
				alert('Upload failed (' + status + '). Please try again.');
			},
			success: function(data) {
				location.href = 'budget_costs_eval.php?project_id='+$('#project_id').val();
			}
		});
	}

	if (pdf_eval_file) {
		if (pdf_eval_file.size === 0) { alert('File not ready. Please download it first.'); return; }
		let r = new FileReader();
		r.onload = function(e) {
			form_data.append('pdf_evaluation', new Blob([e.target.result], { type: pdf_eval_file.type || 'application/pdf' }), pdf_eval_file.name);
			doAjaxSave(form_data);
		};
		r.readAsArrayBuffer(pdf_eval_file);
	} else {
		doAjaxSave(form_data);
	}										       			   
})

$('#cancel_btn').click(function(){
    location.href = "budget_costs_eval.php?project_id="+$('#project_id').val();
})
</script>

<style>
table,th,td {
   border:1px solid black;
}

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