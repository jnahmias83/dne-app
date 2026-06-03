<?php
include 'include/header.php';
include 'functions/functions.php';

$project_id = @$_GET['project_id'];
$lang_screen = @$_GET['lang_screen'];

$query = $mysqli->prepare("SELECT * FROM dne_projects WHERE id = ?");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$project = fetch_unique($query);


$query = $mysqli->prepare("SELECT ps.id AS id_projects_suppliers,
                          SUM(a.approved_amount) AS sum_approved_amount,
                          SUM(p.paid_amount) AS sum_paid_amount,
						  SUM(a.approved_amount)-SUM(COALESCE(p.paid_amount,0)) AS sum_to_pay_amount,
						  s.id AS s_id,s.name AS s_name,s.name_he AS s_name_he,
						  s.bank_account_owner AS bank_account_owner,
						  s.bank_name AS bank_name,s.type AS s_type,
						  s.bank_branche AS bank_branche,
						  s.bank_account_number AS bank_account_number,
						  s.swift AS swift,s.iban AS iban,
						  ps.is_appears_pdf_wires AS is_appears_pdf_wires
						  FROM dne_accounts_payments ap
						  LEFT JOIN dne_accounts a ON ap.id_account = a.id
						  LEFT JOIN dne_payments2 p ON ap.id_payment = p.id
						  LEFT JOIN dne_projects_suppliers ps on ap.id_projects_suppliers = ps.id
						  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id 
						  WHERE ps.id_project = ?
						  GROUP BY ps.id ORDER BY s.type,s.name");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$accounts_payments = fetch($query);

include 'menu_budget_reports.php';
?>

		<form method="post" action="" class="form-inline">	
		    <input type="hidden" id="project_id" name="project_id" value="<?=@$project->id?>" />

            <div class="container">
			    <input type="hidden" id="id_project" name="id_project" value="<?=@$project_id?>" />	
			    <input type="hidden" id="project_name" name="project_name" value="<?=htmlspecialchars(@$project->name)?>" />	
                <input type="hidden" id="project_name_he" name="project_name_he" value="<?=htmlspecialchars(@$project->name_he)?>" />
			  
			    <div class="row alignCenter marginTop25">
					<div class="col-md-12">
						<a href="projects.php"><img src="images/davidnahmias_logo.png" width="170px" height="170px" /></a>
					</div>
			    </div>
			    <div class="row marginTop15 title">	
					<div class="col-md-12">
						<a id="a_project_title" class="text-decoration-none color-1A5276" href="budget.php?project_id=<?=@$project_id?>&lang_screen=<?=@$lang_screen?>">
						    <strong class="fontSize33 line-height-1em"><?=@$project->nickname?></strong>
							<div id="project_name_title"></div>
					    </a>
                        <div id="title" class="colorBlue2"></div> 						
					</div>				
			    </div>
				
				<div class="row justify-content-center marginTop10">
					<div id="div_btns" class="bgColor-cbddec padding10 rounded px-3 py-2 d-inline-block" style="min-width: 280px; max-width: 100%; width: fit-content;">    
						<div class="row">
							<div class="col-12 text-center">
								<a onclick="toPaymentsWiresPdfReport();" class="text-decoration-none cursor-pointer d-flex flex-column align-items-center">
									<img src="images/file-pdf-solid.svg" width="50" height="30" alt="PDF Icon" />
									<?php if (@$lang_screen == 'HE') { ?>
										<strong class="font-family-david mt-1 text-center">דו''ח</strong>
									<?php } else { ?>
										<strong class="fontSize13 mt-1 text-center">Report</strong>
									<?php } ?>     
								</a>
							</div>
						</div>
						<div class="row mt-1">
							<div class="col-12 text-center">
								<label class="d-inline-flex align-items-center me-3">
									<input type="checkbox" id="add_budget_report" class="me-1" />
									<span id="add_budget_report_span" class="text-nowrap"></span>
								</label>

								<label class="d-inline-flex align-items-center">
									<input type="checkbox" id="add_suppliers_balances_reports" class="me-1" />
									<span id="add_sup_balances_reports_span" class="text-nowrap"></span>
								</label>
							</div>
						</div>
					</div>
				</div>
				
				<div class="row marginTop20 alignCenter">
					<div id="div_btns_down" class="col-12">
						<select id="lang" name="lang" class="width100 height35 border-color-initial">
						   <option value="HE" <?php if(@$lang_screen == 'HE') echo 'selected'?>>עברית</option>
						   <option value="EN"<?php if(@$lang_screen == 'EN') echo 'selected'?>>English</option>					   
						</select>      
					</div>
				</div>				

				<div class="row alignCenter paddingTop20">
					<div align="center" class="col-md-12 mx-2">
						<table border="1" id="payments_wires_list" cellpadding="2" cellspacing="2">		
							<thead>   
								<tr class="fontSize14 height30 bgColorSilver">
									<th width="30px;" class="alignCenter">&nbsp;</th>
									<th id="th_iteration" width="30px;" class="alignCenter">&#x2116;</th>
									<th id="th_supplier_name" width="120px;" class="alignCenter"></th>
									<th id="th_amount_to_pay" width="110px" class="alignCenter"></th>
									<th id="th_swift_iban" width="140px;" class="alignCenter"></th>
									<th id="th_account" width="80px;" class="alignCenter"></th>
									<th id="th_branch" width="40px;" class="alignCenter"></th>
									<th id="th_bank" width="40px;" class="alignCenter"></th>
									<th id="th_account_name" width="170px;" class="alignCenter"></th>		
								</tr>
							</thead>
							
							<tbody>
								<?php
								$count = 0;
								
								$total_sum_to_pay_amount = 0;
								
								foreach($accounts_payments as $item){		 					
									if($item->sum_to_pay_amount > 0){										
										$count++;
										
										$sum_to_pay_amount = '';
										if($item->sum_to_pay_amount > 0) {
											$sum_to_pay_amount = @$item->sum_to_pay_amount;
											$total_sum_to_pay_amount +=@$item->sum_to_pay_amount;
										}
										?>
										
										<input type="hidden" id="hidden_s_name_<?=@$item->id_projects_suppliers?>" value="<?php if(strlen(@$item->s_name) > 12) echo mb_substr(@$item->s_name,0,12,'UTF-8');else echo @$item->s_name?>" />
										<input type="hidden" id="hidden_s_name_he_<?=@$item->id_projects_suppliers?>" value="<?php if(strlen(@$item->s_name_he) > 12) echo mb_substr(@$item->s_name_he,0,12,'UTF-8');else echo @$item->s_name_he?>" />
														
										<tr class="height25 fontSize14">
										    <td class="alignCenter"><input type="checkbox" id="cb_<?=@$item->id_projects_suppliers?>" <?php if(@$item->is_appears_pdf_wires == 1) echo "checked";?> onclick="setIsAppearsPDFWires(<?=@$item->id_projects_suppliers?>);" /></td>									
											<td class="alignCenter cursor-pointer"><a href="accounts_payments.php?ps_id=<?=@$item->id_projects_suppliers?>&from=payments_wires&lang_screen=<?=@$lang_screen?>"><?=@$count?></a></td>
											<td class="td_payment_wire_elem"><a id="s_name_<?=@$item->id_projects_suppliers?>" href="add_supplier.php?type_sup=<?=@$item->s_type?>&id=<?=@$item->s_id?>&project_id=<?=@$project_id?>&from=payments_wires&lang_screen=<?=@$lang_screen?>"></a></td>
											<td class="alignCenter">
												<strong>
													<?=$sum_to_pay_amount_display = isset($sum_to_pay_amount)
													? ((floor($sum_to_pay_amount) == $sum_to_pay_amount)
														? number_format($sum_to_pay_amount,0,'.',',')
														: number_format($sum_to_pay_amount,2,'.',','))
													:'';
													?>&#8362;
												</strong>
											</td>
											<td class="alignCenter"><?=@$item->swift.'&nbsp'.@$item->iban?></td>
											<td class="alignCenter"><?=@$item->bank_account_number?></td>
											<td class="alignCenter"><?=@$item->bank_branche?></td>
											<td class="alignCenter"><?=@$item->bank_name?></td>
											<td class="td_payment_wire_elem"><?php if(strlen(@$item->bank_account_owner) > 15) echo mb_substr(@$item->bank_account_owner,0,15,'UTF-8');else echo @$item->bank_account_owner?></td>
										</tr>
										<?php
									}
								}
								
								$val = floatval(@$total_sum_to_pay_amount);
								$total_sum_to_pay_amount_display =
									number_format($val, ($val == floor($val)?0:2),'.',',')
									.'&#8362;';								
								?>
								<tr class="height25 fontSize13">
									<td colspan="3">&nbsp;</td>
									<td class="alignCenter bgColorSkyblue"><strong><span id="total_amount_to_pay_span" class="fontSize13"></span><br/><?=@$total_sum_to_pay_amount_display?></strong></td>
									<td colspan="8">&nbsp;</td>
								</tr>
							</tbody>
						</table>	
					</div>
				</div>
			</div>
		</form>
	</body>
</html>

<script>
$(document).ready(function(){
   setLabels();
   
   function setLabels(){
	  $('#create_pdf_btn').css({"direction":"rtl","margin-right":"10px"});
	  $('#lang').css({"padding-left":"10px"});
	  
	  if($('#lang').val() == 'EN'){
		  $('#project_name_title').html("<span class='fontSize26 font-weight-bold cursor-pointer'>Project "+$('#project_name').val()+"</span>");
		  $('#title').html("<span class='fontSize26 font-weight-bold cursor-pointer'>Payments to be executed Report </span>");
		  $('#div_btns').css({"direction":"ltr"});
		  $('#payments_wires_list').css({"direction":"ltr"});
		  $('#th_iteration').html('&#x2116;');  
		  $('#th_supplier_name').html('Supplier <br/> Name');
          $('#th_amount_to_pay').html('Amount <br> to pay');
          $('#th_swift_iban').html('Swift + Iban'); 
          $('#th_account').html('Account');
          $('#th_branch').html('Branch');
          $('#th_bank').html('Bank');
          $('#th_account_name').html('Account Name');	
          $('.td_payment_wire_elem').css({"text-align":"left","padding-left":"5px"});
          $('#total_amount_to_pay_span').html('Total amount to pay');
		  $('#add_budget_report_span').html('Join budget report');	
		  $('#add_budget_report').css({"margin-right":"10px"});
		  $('#add_sup_balances_reports_span').html('Join suppliers reports');
          $('#add_suppliers_balances_reports').css({"margin-right":"8px"});
		  			  	  
		  $('a[id^="s_name"]').each(function () {
			  let elem = 'hidden_'+$(this).attr('id');
			  $(this).html($('#'+elem).val());
		  });
		  
		  $('#create_pdf_btn').css({"direction":"ltr","margin-right":"10px"});
	  }
	  else if($('#lang').val() == 'HE'){
		   $('#project_name_title').html("<span class='fontSize26 font-weight-bold font-family-david cursor-pointer'>פרוייקט "+$('#project_name_he').val()+"</span>");
		   $('#title').html("<span class='fontSize26 font-weight-bold font-family-david cursor-pointer'>דו''ח תשלומים לביצוע</span>");
		   $('#div_btns').css({"direction":"rtl"});
		   $('#payments_wires_list').css({"direction":"rtl"});
		   $('#th_iteration').html("מס'");  
		   $('#th_supplier_name').html('שם ספק'); 
		   $('#th_amount_to_pay').html('סכום לתשלום');  
		   $('#th_swift_iban').html('Swift + Iban');  
           $('#th_account').html('חשבון');
           $('#th_branch').html('סניף');
           $('#th_bank').html('בנק');	
           $('#th_account_name').html('שם חשבון'); 
           $('.td_payment_wire_elem').css({"text-align":"right","padding-right":"5px"}); 
		   $('#total_amount_to_pay_span').html("סה''כ לתשלום");
		   $('#add_budget_report').css({"margin-left":"4px"});
		   $('#add_budget_report_span').html("צרף דו''ח תקציב");
		   $('#add_budget_report').css({"margin-left":"10px"});
		   $('#add_sup_balances_reports_span').html('צרף דוחות ספקים');	
           $('#add_suppliers_balances_reports').css({"margin-left":"6px"});
		   	   
		   $('a[id^="s_name"]').each(function () {
			  let elem = 'hidden_s_name_he_'+$(this).attr('id').substring(7);
			  $(this).html($('#'+elem).val());
		   });
		  
		   $('#create_pdf_btn').css({"direction":"rtl","margin-left":"10px"});
	  }
   }
   
   $("#lang").change(function(){
        setLabels();
	    const url = new URL(window.location.href);
	    url.searchParams.set('lang_screen',$(this).val());
	   
	    let form_data = new FormData();	
	    form_data.append('lang',$(this).val());			
		$.ajax({
			type: 'POST',
			url: 'set_session_lang.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,			
			success: function(data){
				window.location.href = url.toString();		
			},
		});		
   });
});

function setIsAppearsPDFWires(id_projects_suppliers){
	let isChecked = $('#cb_'+id_projects_suppliers).is(':checked');
	let isAppearsPDFWires = isChecked? 1:0;
	
	let form_data = new FormData();
	form_data.append('id_projects_suppliers',id_projects_suppliers);
	form_data.append('is_appears_pdf_wires',isAppearsPDFWires);
	
	$.ajax({
		 type: 'POST',
		 url: 'set_is_appears_pdf_wires.php',
		 data: form_data,
		 cache: false,
		 processData: false,
		 contentType: false,			
		 success: function(data){ 
	         location.reload();
		 },
	 })
}

function toPaymentsWiresPdfReport(){
	let addBudgetReport = 0;
    if($('#add_budget_report').is(":checked"))
		addBudgetReport = 1;
	
	let addSuppliersBalancesReports = 0;
    if($('#add_suppliers_balances_reports').is(":checked"))
		addSuppliersBalancesReports = 1;
	
	let addCurrentPaymentsReport = 0;
    if($('#add_current_payments_report').is(":checked"))
		addCurrentPaymentsReport = 1;
	
	window.open('payments_wires_report.php?project_id='+$('#project_id').val()+'&abr='+addBudgetReport+'&asbr='+addSuppliersBalancesReports+'&acpr='+addCurrentPaymentsReport+'&lang='+$('#lang').val(),'_blank');
}
</script>

<style>
.btn {
	background-color:#218FD6;
	color: white;
}

.btn:hover {
   background-color:#3370d6;
   color: white;
}

#a_project_title:hover {
	color: grey;
}

#div_btns {
   width: 90%;
   max-width: 340px;
   min-width: 280px;
   margin: auto;
   border-radius: 10px;
   box-sizing: border-box;
}
</style>