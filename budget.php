<?php
include 'include/header.php';
include 'functions/functions.php';

$project_id = @$_GET['project_id'];
$from = @$_GET['from'];
$lang_screen = @$_GET['lang_screen'];

$query = $mysqli->prepare("SELECT * FROM dne_projects WHERE id = ?");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$project = fetch_unique($query);

$query = $mysqli->prepare("SELECT * FROM dne_vat");
$query->execute(); 
$query->store_result();
$vat = fetch_unique($query);

$query_orders = "SELECT o.id_projects_suppliers AS id_projects_suppliers,
                 SUM(o.sum_order) AS total_sum_order,o.vat AS vat,
				 s.name AS s_name,s.name_he AS s_name_he,
				 sfow.name AS sfow_name,sfow.name_he AS sfow_name_he
                 FROM dne_orders o 
				 LEFT JOIN dne_projects_suppliers ps on o.id_projects_suppliers = ps.id
				 LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id						  
				 LEFT JOIN dne_sup_field_of_work sfow ON s.id_field_of_work = sfow.id
				 WHERE ps.id_project = ? 
				 GROUP BY o.id_projects_suppliers ORDER BY ";
if($lang_screen == 'EN')
   $query_orders .= "s_name ASC";
else if($lang_screen == 'HE') 
   $query_orders .= "s_name_he ASC";

$query = $mysqli->prepare(@$query_orders);
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$orders = fetch($query);

$total_sum_order = 0;
$total_sum_order_vat_included = 0;

$total_sum_paid = 0;
$total_sum_paid_vat_included = 0;

$total_balance = 0;
$total_balance_vat_included = 0;

$total_to_pay = 0;

$count = 0;

include 'menu_budget_reports.php';
?>

		<form method="post" action="" class="form-inline">
           	<input type="hidden" id="id_project" name="id_project" value="<?=@$project_id?>" />
	        <input type="hidden" id="lang_screen" name="lang_screen" value="<?=@$lang_screen?>" />					
			<input type="hidden" id="from" name="from" value="<?=@$from?>" />
            <input type="hidden" id="project_name" name="project_name" value="<?=htmlspecialchars(@$project->name)?>" />	
            <input type="hidden" id="project_name_he" name="project_name_he" value="<?=htmlspecialchars(@$project->name_he)?>" /> 			
			
			<div class="container">
			    <div class="row alignCenter marginTop25">
					<div class="col-md-12">
						<a href="projects.php"><img src="images/davidnahmias_logo.png" width="170px" height="170px" /></a>
					</div>
			    </div>
				
			    <div class="row marginTop15 title">	
					<div class="col-md-12">
						<a id="a_project_title" class="text-decoration-none color-1A5276" href="<?php if($from == "projects") echo "projects.php";else echo 'project_home.php?id='.@$project_id?>">
						    <strong class="fontSize33 line-height-1em"><?=@$project->nickname?></strong>
							<div id="project_name_title"></div>
					    </a>
                        <div id="title"></div>					
					</div>				
			    </div>
				
				<div class="d-flex justify-content-center my-3">
					<div id="div_btns" class="d-flex flex-column align-items-center gap-2 bgColor-cbddec p-3 rounded">
						<div class="d-flex justify-content-center gap-2">
							<a id="btn_add_order" onclick="location.href='add_order.php?id=0&project_id=<?=@$project->id?>&from=budget&lang_screen=<?=@$lang_screen?>';"></a>
							<a id="btn_add_account" onclick="location.href='add_account.php?id=0&project_id=<?=@$project->id?>&from=budget&lang_screen=<?=@$lang_screen?>';"></a>
							<a id="btn_add_payment" onclick="location.href='add_payment.php?id=0&project_id=<?=@$project->id?>&from=budget&lang_screen=<?=@$lang_screen?>';"></a>
						</div>
						
						<div class="text-center">
							<input type="checkbox" id="add_supplier_reports" />
							<span id="add_supplier_reports_span"></span>
						</div>

						<div class="text-center">
							<a class="text-decoration-none cursor-pointer d-inline-flex flex-column align-items-center"
							   onclick="toBudgetPdfReport();"> 
								<img src="images/file-pdf-solid.svg" width="50" height="30" alt="PDF Icon" />
								<?php if(@$lang_screen == 'HE'){ ?>
									<strong class="font-family-david mt-1">דו"ח</strong>
								<?php } else { ?>
									<strong class="fontSize13 mt-1">Report</strong>
								<?php } ?>
							</a>
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
				
				<div class="row fontSize14 alignCenter marginTop25">
					<div align="center" class="col-md-12 mx-2">
						<table id="budget_table" cellpadding="2" cellspacing="2">       
							<tr class="height25 fontSize14">
								<th id="th_supplier" class="alignCenter bgColorGrey border-black" colspan="3"></th>
								<th id="th_vat_excluded" class="alignCenter bgColorBeige border-black" colspan="3"></th>
								<th class="bgColorWhite">&nbsp;</th>
								<th id="th_vat_included" class="alignCenter bgColorSkyblue border-black"></th>
							</tr>					
							<tr class="height35 fontSize14">
								<th id="th_iteration" class="alignCenter border-black" width="30px;"></th>
								<th id="th_name" class="alignCenter border-black" width="120px;"></th>
								<th id="th_domain" class="alignCenter border-black" width="120px;"></th>
								<th class="th_total_order alignCenter border-black" width="120px;"></th>
								<th class="th_total_paid alignCenter border-black" width="120px"></th>
								<th class="th_remaining alignCenter border-black" width="120px"></th>
								<th class="bgColorWhite" width="20px">&nbsp;</th>
								<th id="th_accounts_to_be_paid" class="alignCenter border-black" width="120px"></th>
							</tr>

							<?php
							$count = 0;
							foreach($orders as $item) {
								$s_name = @$item->s_name;
								$s_name_he = @$item->s_name_he;
								$sfow_name = @$item->sfow_name;
								$sfow_name_he = @$item->sfow_name_he;
								
								$query = $mysqli->prepare("SELECT SUM(p.paid_amount_vat_excluded) AS sum_paid_amount,
								                          SUM(a.approved_amount)-SUM(COALESCE(p.paid_amount,0)) AS account_to_be_paid		  
														  FROM dne_accounts_payments ap
														  LEFT JOIN dne_accounts a ON ap.id_account = a.id
														  LEFT JOIN dne_payments2 p ON ap.id_payment = p.id
														  LEFT JOIN dne_projects_suppliers ps on ap.id_projects_suppliers = ps.id 
														  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
														  WHERE ps.id = ? ORDER BY s.type,s.name");
								$query->bind_param("i",$item->id_projects_suppliers);
								$query->execute();
								$query->store_result();
								$account_payment = fetch_unique($query);
								
								$count++;
								
								$bg_color_elems = '#ffffff';
								if($count%2 != 0)
								   $bg_color_elems = '#dedede';
							   
								$total_sum_order_elem_display = '';
								if($item->total_sum_order > 0) 
									$total_sum_order_elem_display = isset($item->total_sum_order)
									?((floor($item->total_sum_order) == $item->total_sum_order)
										? number_format($item->total_sum_order,0,'.',',').'&#8362;'
										: number_format($item->total_sum_order,2,'.',',').'&#8362;')
									:'';

								$sum_paid_amount_display = '';
								if($account_payment->sum_paid_amount > 0) 
									$sum_paid_amount_display = isset($account_payment->sum_paid_amount)
									? ((floor($account_payment->sum_paid_amount) == $account_payment->sum_paid_amount)
									?number_format($account_payment->sum_paid_amount,0,'.',',').'&#8362;'
									:number_format($account_payment->sum_paid_amount,2,'.',',').'&#8362;')
									:'';
				
								$balance = round($item->total_sum_order-$account_payment->sum_paid_amount);
	
								$balance_display = '';
								if($balance > 0) 
									$balance_display = isset($balance)
									? ((floor($balance) == $balance)
										? number_format($balance,0,'.',',').'&#8362;'
										: number_format($balance,2,'.',',').'&#8362;')
									:'';
								
								$account_to_be_paid_display = '';
								if($account_payment->account_to_be_paid > 0) { 
									$account_to_be_paid = round($account_payment->account_to_be_paid);
									$total_to_pay += $account_to_be_paid;
									$account_to_be_paid_display = isset($account_to_be_paid)
									? ((floor($account_to_be_paid) == $account_to_be_paid)
										?number_format($account_to_be_paid,0,'.',',').'&nbsp;&#x20aa;'
										:number_format($account_to_be_paid,2,'.',',').'&nbsp;&#x20aa;')
									:'';
								}
								
								$total_sum_order += $item->total_sum_order;
								$total_sum_order_vat_included += $item->total_sum_order*(1+($item->vat/100));	
								
								$total_sum_paid += $account_payment->sum_paid_amount;
								$total_sum_paid_vat_included += $account_payment->sum_paid_amount*(1+($item->vat/100));
								
								$total_balance += $balance;
	                            $total_balance_vat_included += $balance*(1+($item->vat/100));
								?>
						        
								<input type="hidden" id="hidden_s_name_<?=@$item->id_projects_suppliers?>" value="<?php if(strlen(@$s_name) > 16) echo mb_substr(@$s_name,0,16,'UTF-8');else echo @$s_name?>" />
								<input type="hidden" id="hidden_s_name_he_<?=@$item->id_projects_suppliers?>" value="<?php if(strlen(@$s_name_he) > 16) echo mb_substr(@$s_name_he,0,16,'UTF-8');else echo @$s_name_he?>" />
								<input type="hidden" id="hidden_sfow_name_<?=@$item->id_projects_suppliers?>" value="<?php if(strlen(@$sfow_name) > 16) echo mb_substr(@$sfow_name,0,16,'UTF-8');else echo @$sfow_name?>" />
								<input type="hidden" id="hidden_sfow_name_he_<?=@$item->id_projects_suppliers?>" value="<?php if(strlen(@$sfow_name_he) > 16) echo mb_substr(@$sfow_name_he,0,16,'UTF-8');else echo @$sfow_name_he?>" />
								
								<tr class="height25 fontSize14" style="background-color:<?=@$bg_color_elems?>">
								    <td class="alignCenter border-black"><?=@$count?></td>
								    <td class="td_budget_elem border-black"><a id="s_name_<?=@$item->id_projects_suppliers?>" href="accounts_payments.php?ps_id=<?=@$item->id_projects_suppliers?>&from=budget&lang_screen=<?=@$lang_screen?>"></a></td>
								    <td class="td_budget_elem border-black"><a id="sfow_name_<?=@$item->id_projects_suppliers?>"></a></td>	
								    <td width="110px;" class="td_budget_elem border-black"><?=@$total_sum_order_elem_display?></td>
									<td width="110px;" class="td_budget_elem border-black"><?=@$sum_paid_amount_display?></td>
									<td width="110px;" class="td_budget_elem border-black"><?=@$balance_display?></td>
									<td width="20px" class="bgColorWhite">&nbsp;</td>
									<td width="120px;" class="alignCenter border-black"><?=@$account_to_be_paid_display?></td>							
								</tr>
								<?php
							}
							?>
							<tr>
							   <td colspan="8">&nbsp;</td>
							</tr>
							
							<tr class="height35 fontSize14">
								<th colspan="3"></th>	
								<th class="th_total_order alignCenter border-black" width="120px;"></th>
								<th class="th_total_paid alignCenter border-black" width="120px"></th>
								<th class="th_remaining alignCenter border-black" width="120px"></th>
								<th class="bgColorWhite" width="20px">&nbsp;</th>
								<th width="120px"></th>
							</tr>
							
							<tr class="height30 fontSize14 bgColorBeige font-weight-bold">
								<td id="th_total_vat_excluded" class="border-black" colspan="3"></td>
								<td class="td_budget_elem border-black"><?=number_format($total_sum_order,0,'.',',')?>&#8362;</td>
								<td class="td_budget_elem border-black"><?=number_format($total_sum_paid,0,'.',',')?>&#8362;</td>
								<td class="td_budget_elem border-black"><?=number_format($total_balance,0,'.',',')?>&#8362;</td>
								<td width="20px" class="bgColorWhite">&nbsp;</td>
								<td id="th_total_to_be_paid" class="alignCenter bgColorSkyblue border-black"><strong></strong></td>
							</tr>
							
							<tr class="height30 fontSize14 bgColorSkyblue font-weight-bold">
								<td id="th_total_vat_included" class="border-black" colspan="3"></td>
								<td class="td_budget_elem border-black"><?=number_format($total_sum_order_vat_included,0,'.',',')?>&#8362;</td>
								<td class="td_budget_elem border-black"><?=number_format($total_sum_paid_vat_included,0,'.',',')?>&#8362;</td>
								<td class="td_budget_elem border-black"><?=number_format($total_balance_vat_included,0,'.',',')?>&#8362;</td>
								<td class="bgColorWhite" width="20px">&nbsp;</td>
								<td class="alignCenter bgColorSkyblue border-black"><?=number_format($total_to_pay,0,'.',',')?>&#8362;</td>
							</tr>
						</table>            				
					</div>
				</div>
			</div>
		</form> 
	</body>
</html>

<script>
$(document).ready(function() {
   setLabels();
   
   function setLabels() {
	  $('#create_pdf_btn').css({"direction":"rtl","margin-right":"10px"});
	  $('#lang').css({"padding-left":"10px"});
	  
	  if($('#lang_screen').val() == 'EN') {
          $('#project_name_title').html("<span class='fontSize26 font-weight-bold cursor-pointer'>Project "+$('#project_name').val()+"</span>");
		  $('#title').html("<span class='fontSize26 font-weight-bold cursor-pointer'>Budget Report</span>");
		  $('#div_btns').css({"direction":"ltr","text-align":"center"});
		  $('#btn_add_order').html("<div class='alignCenter border-black borderRadius10 padding-4x-4y'><i class='fa-solid fa-plus colorGrey'></i><br/><strong class='fontSize13'>Order</strong></div>");
		  $('#btn_add_account').html("<div class='alignCenter border-black borderRadius10 padding-4x-4y'><i class='fa-solid fa-plus colorGrey'></i><br/><strong class='fontSize13'>Account</strong></div>");
		  $('#btn_add_payment').html("<div class='alignCenter border-black borderRadius10 padding-4x-4y'><i class='fa-solid fa-plus colorGrey'></i><br/><strong class='fontSize13'>Payment</strong></div>");
		  $('#row_cbx').css({"direction":"ltr"});
		  $('#budget_table').css({"direction":"ltr"});
		  $('#th_iteration').html('&#x2116;');
		  $('#th_supplier').html('Supplier');	
          $('#th_vat_excluded').html('VAT excluded');
          $('#th_vat_included').html('VAT included');	
          $('#th_name').html('Name');	
          $('#th_domain').html('Name');		
          $('.th_total_order').html('Total Orders');	
          $('.th_total_paid').html('Total Paid');
          $('.th_remaining').html('Remaining');	
          $('#th_accounts_to_be_paid').html('Accounts to be <br/> paid');
          $('#th_total_vat_excluded').html('Total VAT excluded');
		  $('#th_total_vat_excluded').css({"text-align":"right","padding-right":"10px"});
          $('#th_total_to_be_paid').html('Total to be <br/> paid');	
          $('#th_total_vat_included').html('Total VAT included');
		  $('#th_total_vat_included').css({"text-align":"right","padding-right":"10px"});
          $('.td_budget_elem').css({"text-align":"left","padding-left":"5px"});	
		  $('#add_supplier_reports_span').html('Join suppliers reports');
          $('#add_supplier_reports').css({"margin-right":"8px"});
		  
		  $('a[id^="s_name"]').each(function () {
			  let elem = 'hidden_'+$(this).attr('id');
			  $(this).html($('#'+elem).val());
		  });
		  
		  $('a[id^="sfow_name"]').each(function () {
			  let elem = 'hidden_'+$(this).attr('id');
			  $(this).html($('#'+elem).val());
		  });
		  
		  $('#lang').css({"margin-right":"8px"})
		  $('#create_pdf_btn').val("Create PDF");
		  $('#create_pdf_btn').css({"direction":"ltr","margin-right":"8px"});
		  $('#btn_to_payment_wires').css({"margin-right":"8px"});
		  $('#div_cbs_add_reports').css({"direction":"ltr"}); 
		  $('#btn_to_payment_wires').val('Payment Wires');
		  $('#btn_to_budget_cost_eval').val('Budget Cost Eval');
	  }
	  else if($('#lang_screen').val() == 'HE') {
		  $('#project_name_title').html("<span class='fontSize26 font-weight-bold font-family-david cursor-pointer'>פרוייקט "+$('#project_name_he').val()+"</span>");
		  $('#title').html("<span class='fontSize26 font-weight-bold font-family-david cursor-pointer'>דו''ח תקציב</span>");  
		  $('#div_btns').css({"direction":"rtl","text-align":"center"});
		  $('#btn_add_order').html("<div class='alignCenter border-black borderRadius10 padding-4x-4y'><i class='fa-solid fa-plus colorGrey'></i><br/><strong class='fontSize13'>הזמנה</strong></div>");	  
		  $('#btn_add_account').html("<div class='alignCenter border-black borderRadius10 padding-4x-4y'><i class='fa-solid fa-plus colorGrey'></i><br/><strong class='fontSize13'>חשבון</strong></div>");
		  $('#btn_add_payment').html("<div class='alignCenter border-black borderRadius10 padding-4x-4y'><i class='fa-solid fa-plus colorGrey'></i><br/><strong class='fontSize13'>תשלום</strong></div>");
		  $('#row_cbx').css({"direction":"rtl"});
		  $('#budget_table').css({"direction":"rtl"});
		  $('#th_iteration').html("מס'");
		  $('#th_supplier').html('ספק');
		  $('#th_vat_excluded').html("לא כולל מע''מ");
		  $('#th_total_vat_excluded').css({"text-align":"left","padding-left":"10px"});
		  $('#th_vat_included').html("כולל מע''מ");	
		  $('#th_total_vat_included').css({"text-align":"left","padding-left":"10px"});
		  $('#th_name').html('שם');	
          $('#th_domain').html('תחום');	
		  $('.th_total_order').html('סך הזמנות');	
          $('.th_total_paid').html('סך שולם');
          $('.th_remaining').html('נשאר');	
          $('#th_accounts_to_be_paid').html('חשבונות <br/> לתשלום');
          $('#th_total_vat_excluded').html("סה''כ לא כולל מע''מ");
		  $('#th_total_to_be_paid').html("סה''כ תשלומים <br/> לביצוע");	
          $('#th_total_vat_included').html("סה''כ כולל מע''מ");		
          $('.td_budget_elem').css({"text-align":"right","padding-right":"5px"});
		  $('#div_cbs_add_reports').css({"direction":"rtl"});
		  $('#add_supplier_reports_span').html('צרף דוחות ספקים');	
          $('#add_supplier_reports').css({"margin-left":"6px"});
		   
		  $('a[id^="s_name"]').each(function () {
			  let elem = 'hidden_s_name_he_'+$(this).attr('id').substring(7);
			  $(this).html($('#'+elem).val());
		  });
		  
		  $('a[id^="sfow_name"]').each(function () {
			  let elem = 'hidden_sfow_name_he_'+$(this).attr('id').substring(10);
			  $(this).html($('#'+elem).val());
		  });
		  
		  $('#create_pdf_btn').val("הפקת PDF");
		  $('#create_pdf_btn').css({"direction":"rtl","margin-left":"8px"});
		  $('#btn_to_payment_wires').css({"margin-left":"8px"});
		  $('#btn_to_payment_wires').val('תשלומים לביצוע');
		  $('#btn_to_budget_cost_eval').val('אומדן תקציבי');
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

function toBudgetPdfReport() {
	let addSupplierReports = 0;
    if($('#add_supplier_reports').is(":checked"))
		addSupplierReports = 1;
	
	window.open('budget_report.php?project_id='+$('#id_project').val()+'&asr='+addSupplierReports+'&lang='+$('#lang_screen').val(),'_blank');
}
</script>

<style>
table,th,td {
    border: none!important;
}

.btn {
   color: white;
   background-color: #218FD6;
}

.btn:hover {
   color: white;
   background-color: #3370d6;
}

#a_project_title:hover {
	color: grey;
}
</style>