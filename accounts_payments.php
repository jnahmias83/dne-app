<?php 
include 'include/header.php';
include 'functions/functions.php';

$ps_id = @$_GET['ps_id'];
$from = @$_GET['from'];
$lang_screen = @$_GET['lang_screen'];

$query = $mysqli->prepare("SELECT id_project FROM dne_projects_suppliers 
                           WHERE id = ?");
$query->bind_param("i",$ps_id);
$query->execute(); 
$query->store_result();
$project = fetch_unique($query);

$query = $mysqli->prepare("SELECT p.id AS p_id,p.nickname AS p_nickname,
                          p.name AS p_name,p.name_he AS p_name_he,
                          sfow.name AS sfow_name,sfow.name_he AS sfow_name_he,
						  s.name AS s_name,s.name_he AS s_name_he
						  FROM dne_projects_suppliers ps
						  LEFT JOIN dne_projects p ON ps.id_project = p.id
						  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
						  LEFT JOIN dne_sup_field_of_work sfow ON s.id_field_of_work = sfow.id
						  WHERE ps.id = ?");
$query->bind_param("i",$ps_id);
$query->execute(); 
$query->store_result();
$supplier = fetch_unique($query);

$query = $mysqli->prepare("SELECT o.id AS o_id,o.sum_order AS sum_order,
                          o.pdf_order AS pdf_order,
						  o.signature_date AS signature_date,
						  o.description AS description,o.vat AS vat
                          FROM dne_orders o 
						  LEFT JOIN dne_projects_suppliers ps ON o.id_projects_suppliers = ps.id
						  LEFT JOIN dne_projects p ON ps.id_project = p.id
						  WHERE ps.id = ? ORDER BY o.signature_date");
$query->bind_param("i",$ps_id);
$query->execute(); 
$query->store_result();
$orders_num_rows = $query->num_rows;
$orders = fetch($query);

$query = $mysqli->prepare("SELECT * FROM dne_payments2 
                          WHERE id_projects_suppliers = ?");
$query->bind_param("i",$ps_id);
$query->execute(); 
$query->store_result();
$payments_num_rows = $query->num_rows;

$zero = 0.0;
$query = $mysqli->prepare("SELECT * FROM dne_accounts 
                          WHERE id_projects_suppliers = ? AND approved_amount > ?");
$query->bind_param("id",$ps_id,$zero);
$query->execute(); 
$query->store_result();
$accounts_num_rows = $query->num_rows;

$accounts_payments_num_rows = $payments_num_rows + $accounts_num_rows + 1;

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
						  LEFT JOIN dne_projects_suppliers ps ON ap.id_projects_suppliers = ps.id 
						  WHERE ps.id = ?
						  ORDER BY ap.account_payment_date");
$query->bind_param("i",$ps_id);
$query->execute(); 
$query->store_result();
$accounts_payments = fetch($query);

$zero = 0.0;
$query = $mysqli->prepare("SELECT id,description,submit_date,submitted_account,pdf_submission 
                          FROM dne_accounts WHERE id_projects_suppliers = ? AND approved_amount = ?
						  ORDER BY submit_date");
$query->bind_param("id",$ps_id,$zero);
$query->execute(); 
$query->store_result();
$not_approved_accounts_num_rows = $query->num_rows;
$not_approved_accounts = fetch($query);

$query = $mysqli->prepare("SELECT vat FROM dne_vat");
$query->execute(); 
$query->store_result();
$vat = fetch_unique($query);

include 'menu_budget_reports.php';
?>
		<form method="post" action="" enctype="multipart/form-data" class="form-inline">	
			<input type="hidden" id="project_id" value="<?=@$supplier->p_id?>" />
			<input type="hidden" id="project_name" name="project_name" value="<?=htmlspecialchars(@$supplier->p_name)?>" />	
            <input type="hidden" id="project_name_he" name="project_name_he" value="<?=htmlspecialchars(@$supplier->p_name_he)?>" />
			<input type="hidden" id="from" value="<?=@$from?>" />
			<input type="hidden" id="id_projects_suppliers" value="<?=@$ps_id?>" />
            <input type="hidden" id="s_name" value="<?=@$supplier->s_name?>" />
			<input type="hidden" id="s_name_he" value="<?=$supplier->s_name_he?>" />
			<input type="hidden" id="sfow_name" value="<?=@$supplier->sfow_name?>" />
			<input type="hidden" id="sfow_name_he" value="<?=@$supplier->sfow_name_he?>" />
			
			<div class="container">
			    <div class="row alignCenter marginTop25">
					<div class="col-12">
						<a href="projects.php"><img src="images/davidnahmias_logo.png" width="170px" height="170px" /></a>
					</div>
			    </div>
				
				<div class="row marginTop15 title">	
					<div class="col-12">
						<a id="a_project_title" class="text-decoration-none color-1A5276" href="<?php if($from == "payments_wires") echo "payments_wires.php?project_id=".@$project->id_project.'&lang_screen='.@$lang_screen;else echo "budget.php?project_id=".@$supplier->p_id."&lang_screen=".@$lang_screen?>">
						    <strong class="fontSize33 line-height-1em"><?=@$supplier->p_nickname?></strong>
							<div id="project_name_title"></div>
					    </a>
                        <div id="title"></div>					
					</div>				
			    </div>
				
				<div class="d-flex justify-content-center my-3">
					<div id="div_btns" class="d-flex flex-column align-items-center gap-2 bgColor-cbddec p-3 rounded">					
						<div class="d-flex justify-content-center gap-2">
							<a id="btn_add_order" onclick="location.href='add_order.php?project_id=<?=@$supplier->p_id?>&ps_id=<?=@$ps_id?>&from=accounts_payments&lang_screen=<?=@$lang_screen?>';"></a>
							<a id="btn_add_account" onclick="location.href='add_account.php?project_id=<?=@$supplier->p_id?>&ps_id=<?=@$ps_id?>&from=accounts_payments&lang_screen=<?=@$lang_screen?>';"></a>				
							<a id="btn_add_payment" onclick="location.href='add_payment.php?project_id=<?=@$supplier->p_id?>&ps_id=<?=@$ps_id?>&from=accounts_payments&lang_screen=<?=@$lang_screen?>';"></a>
						</div>
						<div class="text-center">
							<a class="text-decoration-none cursor-pointer d-inline-flex flex-column align-items-center"
							   onclick="toSupplierPdfReport();">
							   <img src="images/file-pdf-solid.svg" width="50" height="30" alt="PDF Icon" />
							   <?php if(@$lang_screen == 'HE'){ ?>
								  <strong class="font-family-david mt-1">דו''ח</strong>
							   <?php } else { ?>
								  <strong class="fontSize13">Report</strong>
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
					    
				<div class="margin-top-10-x-auto flex justify-content-center width60Percents">
					<div class="width100Percents">
						<div class="row title">	
							<div class="col-12" id="div_title_orders_list"></div>
						</div>
			
						<div class="marginTop10">	
							<table id="orders_list" class="fontSize13">
								<tr class="height40 bgColorSilver">
									<th class="alignCenter th_iteration border-black"></th>
									<th class="alignCenter border-black" id="th_signature_date"></th>
									<th class="th_description alignCenter border-black"></th>
									<th class="alignCenter border-black" id="th_sum_order"></th>
									<?php if($orders_num_rows > 0) { ?><th></th> <?php } ?>
								</tr>
								
								<?php 
								$total_sum_orders = 0;
								$total_sum_orders_vat_included = 0;
								$count = 0;
								foreach($orders as $item){		
									$signature_date = '';
									if(@$item->signature_date != '0000-00-00')
										$signature_date = smartDate(@$item->signature_date, @$lang_screen);				
									
									$sum_orders_display = '';
									if(@$item->sum_order != 0.00) {
										$sum_orders = @$item->sum_order;										
										$sum_orders_display = number_format(
											$sum_orders,
											($sum_orders == floor($sum_orders)?0:2),'.',','
										).'&#8362;';
										$sum_order_vat_included = @$item->sum_order*(1+(@$item->vat/100)); 
									}

									$total_sum_orders +=$item->sum_order;
									$total_sum_orders_vat_included += $sum_order_vat_included;										
									$count++;
								?>
									<tr class="height25">
										<td class="alignCenter border-black"><a href="add_order.php?id=<?=@$item->o_id?>&project_id=<?=@$supplier->p_id?>&ps_id=<?=@$ps_id?>&from=accounts_payments&lang_screen=<?=@$lang_screen?>"><?=@$count?></a></td>
										<td class="th_orders_item border-black"><?=@$signature_date?></td>
										<td class="th_orders_item border-black"><?=@$item->description?></td>
										<td class="th_orders_item border-black"><?php if(@$item->pdf_order != '') { ?><a href="uploads/<?=@$item->pdf_order?>" title="View PDF" target="_blank"><?=@$sum_orders_display?></a><?php } else { echo @$sum_orders_display; } ?></td>
										<?php if($orders_num_rows > 0) { ?>
											 <td class="alignCenter"><img src="images/delete.svg" class="cursor-pointer" title="Remove" onclick="return removeOrder(<?=@$item-> o_id?>);" /></td>	
										<?php } ?>
									</tr>
								<?php 
								}										
									
								$total_sum_orders_display = '';
								$total_sum_orders_vat_included_display = '';
								
								if($orders_num_rows > 0){ 
									$total_sum_orders_display = number_format(
										$total_sum_orders,
										(floor($total_sum_orders) == $total_sum_orders?0:2),'.',','
									).'&#8362;';

									$total_sum_orders_vat_included_display = number_format(
										$total_sum_orders_vat_included,
										(floor($total_sum_orders_vat_included) == $total_sum_orders_vat_included ? 0 : 2),'.',','
									).'&#8362;';
								}
								else {
									$total_sum_orders_display = '';	
									$total_sum_orders_vat_included_display = '';	
								}
								?>						
								<tr class="height30">
									<td class="border-black bgColorBeige" id="th_total_orders" colspan="3"></td>
									<td class="th_orders_item border-black bgColorBeige"><strong><?=@$total_sum_orders_display?></strong></td>
									<?php if($orders_num_rows > 0) { ?> <td class="bgColorBeige"></td> <?php } ?>
								</tr>
							</table>		
						</div>
				
						<div class="row title marginTop10">	
							<div class="col-12" id="div_title_accounts_payments_list"></div>
						</div>
							
						<div class="marginTop10">	
							<table id="accounts_payments_list" class="marginTop15 fontSize13">
								<tr class="height40 bgColorSilver">
									<th class="alignCenter th_iteration border-black"></th>
									<th class="alignCenter border-black" id="th_date"></th>
									<th class="alignCenter border-black" id="th_account_payment"></th>
									<th class="th_description alignCenter border-black"></th>
									<th class="alignCenter border-black" id="th_accounts"></th>
									<th class="alignCenter border-black" id="th_payments" colspan="2"></th>
								</tr>
						
								<?php 
								$total_approved_amount_vat_included = 0;
								$total_paid_amount_vat_included = 0;
								$total_paid_amount_vat_excluded = 0;
								
								$count = 0;
								foreach($accounts_payments as $item){
									if(@$item->account_payment_type == 'payment' || @$item->account_payment_type == 'account' && @$item->approved_amount > 0) {							
										if(@$item->account_payment_type == 'account'){
										   $description = $item->a_description;
										   $record_id = $item->a_id;
										}
										else { 
										   $description = $item->p_description;
										   $record_id = $item->p_id;	   
										}
										
										$account_payment_date = '';
										if(@$item->account_payment_date != '0000-00-00')
											$account_payment_date = smartDate(@$item->account_payment_date, @$lang_screen);				
										
										$approved_amount = @$item->approved_amount;	
										$approved_amount_display = number_format(@$approved_amount,(@$approved_amount == floor(@$approved_amount)?0:2), '.', ',').'&#8362;';	
										
										$paid_amount_vat_included_display = '';
										if(@$item->paid_amount_vat_included != 0.00){
											$paid_amount_vat_included = @$item->paid_amount_vat_included;
											$paid_amount_vat_included_display = number_format(
												@$paid_amount_vat_included,
												(@$paid_amount_vat_included == floor(@$paid_amount_vat_included) ? 0 : 2),'.',','
											).'&#8362;';	
										}
										
										$total_approved_amount_vat_included +=$item->approved_amount;
										$total_paid_amount_vat_included +=$item->paid_amount_vat_included;
										$total_paid_amount_vat_excluded +=$item->paid_amount_vat_excluded;
										
										$count++;
										?>
											<input type="hidden" id="hidden_account_payment_type_<?=@$item->ap_id?>" value="<?=@$item->account_payment_type?>" />
											
											<tr class="height25">
												<td class="alignCenter border-black"><a href="<?php if(@$item->account_payment_type == 'account') echo 'add_account';else echo 'add_payment'?>.php?id=<?php if(@$item->account_payment_type == 'account') echo @$item->a_id;else echo @$item->p_id?>&project_id=<?=@$supplier->p_id?>&ps_id=<?=@$ps_id?>&from=accounts_payments&lang_screen=<?=@$lang_screen?>"><?=@$count?></a></td>
												<td class="th_accounts_payments_item border-black"><?=@$account_payment_date?></td>
												<td class="th_accounts_payments_item border-black"><a id="a_account_payment_type_<?=@$item->ap_id?>"><?=@$item->account_payment_type?></a></td>
												<td class="th_accounts_payments_item border-black"><?=@$description?></td>
												<td class="th_accounts_payments_item border-black alignCenter" dir="ltr"><?php if(@$item->account_payment_type == 'account') { if(@$item->pdf_approval != '') { ?><a href="uploads/<?=@$item->pdf_approval?>" title="View PDF" target="_blank"><?=@$approved_amount_display?></a><?php } else { echo @$approved_amount_display; }} ?></td>
												<td class="th_accounts_payments_item border-black" dir="ltr"><?php if(@$item->account_payment_type == 'payment'){ if(@$item->pdf_payment != '') { ?><a href="uploads/<?=@$item->pdf_payment?>"  title="View PDF" target="_blank"><span dir="ltr" class="colorRed">-<?=@$paid_amount_vat_included_display?></span></a><?php } else { echo "<span dir='ltr' class='colorRed'>-".@$paid_amount_vat_included_display.'</span>'; }} ?></td>
												<?php if($accounts_payments_num_rows > 0) { ?> <td class="alignCenter"><img src="images/delete.svg" class="cursor-pointer" title="Remove" onclick="return removeAccountPayment('<?=@$item->account_payment_type?>',<?=@$record_id?>);" /></td>	<?php } ?>
											</tr>
								<?php }}	
																								
								$total_approved_amount_vat_included_display = '';
								$total_paid_amount_vat_included_display = '';
								$total_paid_amount_vat_excluded_display = '';	
								$pending_payment_display = '';
								$pending_payment_vat_excluded_display = '';
								$percent_paid_display = '';
								$percent_paid_vat_excluded_display = '';
								$percent_paid_from_orders_vat_excluded_display = ''; 
								$percent_to_pay_from_orders_vat_included_display = '';

								if($accounts_payments_num_rows > 0) {
									$total_approved_amount_vat_included_display = number_format(
										@$total_approved_amount_vat_included,
										(@$total_approved_amount_vat_included == floor(@$total_approved_amount_vat_included)?0:2),'.',','
									).'&#8362;';
								
									$total_paid_amount_vat_included_display = number_format(
										@$total_paid_amount_vat_included,
										(@$total_paid_amount_vat_included == floor(@$total_paid_amount_vat_included)?0:2),'.',','
									).'&#8362;';
									
									$total_paid_amount_vat_excluded_display = number_format(
										@$total_paid_amount_vat_excluded,
										(@$total_paid_amount_vat_excluded == floor(@$total_paid_amount_vat_excluded) ?0:2),'.',','
									).'&#8362;';											
									
									$pending_payment_display = '-';
									if($total_approved_amount_vat_included-$total_paid_amount_vat_included >= 0) {
										$pending_payment = $total_approved_amount_vat_included-$total_paid_amount_vat_included;									
										$pending_payment_display = number_format(
											$pending_payment,
											($pending_payment == floor($pending_payment)?0:2),'.',','
										).'&#8362;';

										$pending_payment_vat_excluded = $pending_payment/(1+(@$vat->vat/100));
										$pending_payment_vat_excluded_display = number_format(
											$pending_payment_vat_excluded,
											($pending_payment_vat_excluded == floor($pending_payment_vat_excluded)?0:2),'.',','
										).'&#8362;';
									}										
									
									if($total_approved_amount_vat_included-$total_paid_amount_vat_included < 0) 
									   $pending_payment_display = '0.00&#8362';
									
									if($total_sum_orders > 0) {
										$percent = (@$total_paid_amount_vat_excluded/@$total_sum_orders)*100;
										$percent_paid_display = number_format(
											$percent,($percent == floor($percent)?0:2),'.',','
										).'%';
										
										$percent_from_orders = (@$pending_payment_vat_excluded/@$total_sum_orders)*100;
										$percent_paid_from_orders_vat_excluded_display = number_format(
											$percent_from_orders,($percent_from_orders == floor($percent_from_orders)?0:2),'.',','
										).'%';
									}
									
									if($total_sum_orders > 0) 
									    $percent_paid = (@$total_paid_amount_vat_excluded/@$total_sum_orders)*100;
										$percent_paid_display = number_format(
											$percent_paid,
											($percent_paid == floor($percent_paid) ? 0 : 2),'.',','
										).'%';
					
									$remaining_to_pay_vat_included = $total_sum_orders_vat_included - $total_paid_amount_vat_included;					
									$remaining_to_pay_vat_included_display = number_format(
									$remaining_to_pay_vat_included,
										($remaining_to_pay_vat_included == floor($remaining_to_pay_vat_included)?0:2),'.',','
									).'&#8362;';
						
									$percent_to_pay_from_orders_vat_included = ($remaining_to_pay_vat_included/$total_sum_orders_vat_included)*100;
									$percent_to_pay_from_orders_vat_included_display = number_format(
										$percent_to_pay_from_orders_vat_included,
										($percent_to_pay_from_orders_vat_included == floor($percent_to_pay_from_orders_vat_included)?0:2),'.',','
									).'%';
								}
								?>

								<input type="hidden" id="remaining_to_pay_vat_included" value="<?=@$remaining_to_pay_vat_included_display?>" />
								<input type="hidden" id="percent_to_pay_from_orders_vat_included" value="<?=@$percent_to_pay_from_orders_vat_included_display?>" />
								<input type="hidden" id="total_paid_amount_vat_included_val" value="<?=@$total_paid_amount_vat_included_display?>" />
								<input type="hidden" id="percent_paid_display_val" value="<?=@$percent_paid_display?>" />
								
								<tr class="height30">
									<td class="border-black bgColorSkyblue" id="th_total_accounts_payments" colspan="4"></td>
									<td class="th_accounts_payments_item border-black bgColorSkyblue" dir="ltr"><strong><?=@$total_approved_amount_vat_included_display?></strong></td>
									<td class="th_accounts_payments_item border-black bgColorSkyblue" colspan="2" dir="ltr"><strong><?='-'.@$total_paid_amount_vat_included_display?></strong></td>
								</tr>
								<tr class="height30">
									<td class="bgColorWhite" colspan="4"></td>
									<td <?php if($accounts_payments_num_rows > 0) { echo 'colspan=3';} else echo 'colspan=2';?> class="border-black" id="pending_payment_cell" dir="ltr"><span id="span_pending_payment"></span>&nbsp;<?=@$pending_payment_display?></td>
								</tr>
							</table>
								
							<table id="table_summary" class="marginTop15 fontSize13 font-weight-bold">
								<tr>
									<td id="td_summary_header" colspan="3" class="bgColorSkyblue border-black alignCenter"></td>
								</tr>
								<tr>
									<td id="td_to_pay_label" class="bgColorSkyblue border-black alignCenter padding-4x-4y"></td>
									<td class="bgColorSkyblue border-black alignCenter padding-4x-4y" dir="ltr"><?=@$pending_payment_display?></td>
									<td class="bgColorSkyblue border-black alignCenter padding-4x-4y"></td>
								</tr>
								<tr>
									<td id="td_paid_label" class="bgColorSkyblue border-black alignCenter padding-4x-4y"></td>
									<td class="bgColorSkyblue border-black alignCenter padding-4x-4y" dir="ltr"><?=@$total_paid_amount_vat_included_display?></td>
									<td id="td_paid_pct" class="bgColorSkyblue border-black alignCenter padding-4x-4y"></td>
								</tr>
								<tr>
									<td id="td_remaining_label" class="bgColorSkyblue border-black alignCenter padding-4x-4y"></td>
									<td class="bgColorSkyblue border-black alignCenter padding-4x-4y" dir="ltr"><?=@$remaining_to_pay_vat_included_display?></td>
									<td id="td_remaining_pct" class="bgColorSkyblue border-black alignCenter padding-4x-4y"></td>
								</tr>
							</table>
						</div>
				
						<?php if(@$not_approved_accounts_num_rows > 0) { ?>
							<div class="row title marginTop10">	
							   <div id="div_title_not_approved_accounts_list" class="col-12"></div>
							</div>
							
							<div class="row fontSize13 marginTop15">
								<div class="col-12">
									<table align="center" id="not_approved_accounts_list">
										<tr class="height40 bgColorSilver">
											<th class="alignCenter th_iteration border-black"></th>
											<th class="th_description alignCenter border-black"></th>
											<th class="alignCenter border-black" id="th_submit_date"></th>
											<th class="alignCenter border-black" id="th_submitted_account"></th>
											<th></th>
										</tr>
										
										<?php
										$count = 0;
										foreach($not_approved_accounts as $item) { 
											$submit_date = '';
											if(@$item->submit_date != '0000-00-00')
											  $submit_date = smartDate(@$item->submit_date, @$lang_screen);				
											
											$submitted_account_display = '';
											if(@$item->submitted_account > 0) {
												$submitted_account = @$item->submitted_account;	
												$submitted_account_display = number_format(
													@$submitted_account,
													(@$submitted_account == floor(@$submitted_account)?0:2),'.',','
												).'&#8362;';
											}
											
											$count++;
										?>					
										   <tr class="height25">
												<td class="alignCenter border-black"><a href="add_account.php?id=<?=@$item->id?>&project_id=<?=@$supplier->p_id?>&ps_id=<?=@$ps_id?>&from=accounts_payments&lang_screen=<?=@$lang_screen?>"><?=@$count?></a></td>
												<td class="th_not_approved_accounts_item border-black"><?=@$item->description?></td>
												<td class="th_not_approved_accounts_item border-black"><?=@$submit_date?></td>
												<td class="th_not_approved_accounts_item border-black" dir="ltr"><?php if(@$item->pdf_submission != '') { ?><a href="uploads/<?=@$item->pdf_submission?>" title="View PDF" target="_blank"><?=@$submitted_account_display?></a><?php } else { echo @$submitted_account_display; } ?></td>
												<td class="alignCenter"><img src="images/delete.svg" class="cursor-pointer" title="Remove" onclick="return removeAccount(<?=@$item->id?>)" /></td>
											</tr>
										<?php } ?>
									</table>
								</div>	
							</div>
						<?php } ?>
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
	  
	  if($('#lang').val() == 'EN') {
		  $('#project_name_title').html("<span class='fontSize26 font-weight-bold cursor-pointer'>Project "+$('#project_name').val()+"</span>");
		  $('#title').html('<span class="fontSize25 font-weight-bold text-decoration-underline">'+$('#s_name').val()+'</span><br/><span class="fontSize24">'+$('#sfow_name').val()+'</span><br/><span class="fontSize23">Status of orders and payments</span>');
		  $('#div_btns').css({"direction":"ltr"});
		  $('#th_date').html('Date');
		  $('.th_description').html('Description');
		  $('#orders_list').css({"direction":"ltr","width":"100%"});
		  $('#accounts_payments_list').css({"direction":"ltr","width":"100%"});
		  $('#not_approved_accounts_list').css({"direction":"ltr","width":"100%"});
		  $('#th_total_accounts_payments').html('<strong>Total VAT included</strong>');
		  $('#th_total_orders').css({"text-align":"right",'padding-right':'5px'});
		  $('#th_total_accounts_payments').css({"text-align":"right",'padding-right':'5px'});
		  $('.th_accounts_payments_item').css({"text-align":"left",'padding-left':'5px'});
		  $('.th_orders_item').css({"text-align":"left",'padding-left':'5px'});
		  $('#th_pending_payments').css({"background-color":"white","border":"none"});
		  $('#pending_payment_cell').css({"text-align":"center","font-weight":"bold"});
		  $(".bgColorWhite").removeClass("td_white_he");
		  $(".bgColorWhite").addClass("td_white_en");
		  $('.th_not_approved_accounts_item').css({"text-align":"left",'padding-left':'5px'});
		  $('#create_pdf_btn').css({"direction":"rtl","margin-right":"10px"});
		  $('#lang').css({"padding-left":"10px"});
		  $('#btn_add_order').html("<div class='alignCenter border-black borderRadius10 padding-4x-4y'><i class='fa-solid fa-plus colorGrey'></i><br/><strong class='fontSize13'>Order</strong></div>");
		  $('#btn_add_account').html("<div class='alignCenter border-black borderRadius10 padding-4x-4y'><i class='fa-solid fa-plus colorGrey'></i><br/><strong class='fontSize13'>Account</strong></div>");
		  $('#btn_add_payment').html("<div class='alignCenter border-black borderRadius10 padding-4x-4y'><i class='fa-solid fa-plus colorGrey'></i><br/><strong class='fontSize13'>Payment</strong></div>");
		  $('#div_title_orders_list').html('Orders');
		  $('#div_title_orders_list').css({"text-align":"left","font-size":"22px"});
		  $('.th_iteration').html('&#x2116;');
		  $('#th_signature_date').html('Date');
		  $('#th_sum_order').html('Amount <br/> <span class=fontSize13><em>VAT excluded</em></span>');
		  $('#div_title_accounts_payments_list').html('Accounts / Payments');
		  $('#div_title_accounts_payments_list').css({"text-align":"left","font-size":"22px"});
		  $('#th_account_payment').html('Account/<br/>Payment');
		  $('#th_accounts').html('Accounts<br/><span style="font-size:12px;">VAT Included</span>');
		  $('#th_total_orders').html("<strong>Total orders - excluding VAT</strong>");
		  $('#th_payments').html('Payments<br/><span style="font-size:12px;">VAT Included</span>');
		  $('#span_pending_payment').html('<strong>To be paid</strong>');
		  $('#div_title_not_approved_accounts_list').html('Not Approved Accounts List');
		  $('#div_title_not_approved_accounts_list').css({"text-align":"left","font-size":"22px"});
		  $('#th_submitted_account').html('Submitted<br/>Account');
		  $('#th_submit_date').html('Submission<br/>Date');
		  $('#table_summary').css({"direction":"ltr","float":"right"});
		  $('#td_summary_header').html('<strong>VAT included</strong>');
		  $('#td_to_pay_label').html('<strong>To be paid</strong>');
		  $('#td_paid_label').html('<strong>Paid</strong>');
		  $('#td_remaining_label').html('<strong>Remaining to pay</strong>');
		  $('#td_paid_pct').html('<strong>' + $('#percent_paid_display_val').val() + '</strong>');
		  $('#td_remaining_pct').html('<strong>' + $('#percent_to_pay_from_orders_vat_included').val() + '</strong>');
			 
		  $('a[id^="a_account_payment_type"]').each(function () {
			  let elem = 'hidden_'+$(this).attr('id').substring($(this).attr('id').indexOf('a_')+2);
			  if($('#'+elem).val() == 'account')
				  $(this).html('account');
			  else if($('#'+elem).val() == 'payment')
				  $(this).html('payment');
		  });
		  
		  $('#create_pdf_btn').val("Create PDF");
		  $('#create_pdf_btn').css({"direction":"ltr","margin-right":"10px"});
	  }
	  else if($('#lang').val() == 'HE') {
		  $('#project_name_title').html("<span class='fontSize26 font-weight-bold font-family-david cursor-pointer'>פרוייקט "+$('#project_name_he').val()+"</span>");
		  $('#title').html('<span class="fontSize25 font-weight-bold text-decoration-underline font-family-david">'+$('#s_name_he').val()+'</span><br/><span class="fontSize24 font-family-david">'+$('#sfow_name_he').val()+"</span><br/><span class='fontSize23 font-family-david'>סטטוס הזמנות ותשלומים</span>");
		  $('#div_btns').css({"direction":"rtl"});
		  $('#btn_add_order').html("<div class='alignCenter border-black borderRadius10 padding-4x-4y'><i class='fa-solid fa-plus colorGrey'></i><br/><strong class='fontSize13'>הזמנה</strong></div>"); 
		  $('#btn_add_account').html("<div class='alignCenter border-black borderRadius10 padding-4x-4y'><i class='fa-solid fa-plus colorGrey'></i><br/><strong class='fontSize13'>חשבון</strong></div>");
		  $('#btn_add_payment').html("<div class='alignCenter border-black borderRadius10 padding-4x-4y'><i class='fa-solid fa-plus colorGrey'></i><br/><strong class='fontSize13'>תשלום</strong></div>");
		  $('#div_title_orders_list').html('הזמנות');
		  $('#div_title_orders_list').css({"text-align":"right","font-size":"22px"});
		  $('#th_signature_date').html('תאריך <br/> חתימה');
		  $('#th_sum_order').html("סה''כ </br> <span class=fontSize13><em>לא כולל מע''מ</em></span>");
		  $('#div_title_accounts_payments_list').html('חשבונות/תשלומים שבוצעו');
		  $('#div_title_accounts_payments_list').css({"text-align":"right","font-size":"22px"});
		  $('#th_date').html('תאריך');
		  $('#th_account_payment').html('חשבון/<br/>תשלום');
		  $('.th_description').html('תיאור');
		  $('#th_accounts').html("חשבונות לתשלום<br/><span style='font-size:12px;'>כולל מע''מ</span>");
		  $('#th_payments').html("תשלומים<br/><span style='font-size:12px;'>כולל מע''מ</span>");
		  $('#span_pending_payment').html('סכום לתשלום');
		  $('#orders_list').css({"direction":"rtl","width":"100%"});
		  $('#accounts_payments_list').css({"direction":"rtl","width":"100%"});
		  $('#not_approved_accounts_list').css({"direction":"rtl","width":"100%"});
		  $('.th_iteration').html("מס'");
		  $('#th_total_orders').html("<strong>סה''כ הזמנות - לא כולל מע''מ</strong>");
		  $('#th_total_orders').css({"text-align":"left",'padding-left':'5px'},{});
		  $('#th_total_accounts_payments').css({"text-align":"left",'padding-left':'5px'},{});
		  $('.th_accounts_payments_item').css({"text-align":"right",'padding-right':'5px'});
		  $('.th_orders_item').css({"text-align":"right",'padding-right':'5px'});
		  $('#pending_payment_cell').css({"text-align":"center","font-weight":"bold"});
		  $(".bgColorWhite").removeClass("td_white_en");
		  $(".bgColorWhite").addClass("td_white_he");
		  $('#div_title_not_approved_accounts_list').html('רשימת חשבונות לא מאושרים');
		  $('#div_title_not_approved_accounts_list').css({"text-align":"right","font-size":"22px"});
		  $('#th_submitted_account').html('חשבון שהוגש');
		  $('#th_submit_date').html('תאריך ההגשה');
		  $('#th_total_accounts_payments').html("<strong>סה''כ כולל מע''מ</strong>");
		  $('.th_not_approved_accounts_item').css({"text-align":"right",'padding-right':'5px'});
		  $('#table_summary').css({"direction":"rtl","float":"left"});
		  $('#td_summary_header').html("<strong>כולל מע''מ</strong>");
		  $('#td_to_pay_label').html('<strong>לתשלום</strong>');
		  $('#td_paid_label').html('<strong>שולם</strong>');
		  $('#td_remaining_label').html('<strong>שאר לשלם</strong>');
		  $('#td_paid_pct').html('<strong>' + $('#percent_paid_display_val').val() + '</strong>');
		  $('#td_remaining_pct').html('<strong>' + $('#percent_to_pay_from_orders_vat_included').val() + '</strong>');
		  
		   $('a[id^="a_account_payment_type"]').each(function () {
			  let elem = 'hidden_'+$(this).attr('id').substring($(this).attr('id').indexOf('a_')+2);
			  if($('#'+elem).val() == 'account')
				  $(this).html('חשבון');
			  else if($('#'+elem).val() == 'payment')
				  $(this).html('תשלום');
		   })
		   
		   $('#create_pdf_btn').val("הפקת PDF");
		   $('#create_pdf_btn').css({"direction":"rtl","margin-left":"10px"});
	  }
   }
   
   $("#lang").change(function(){     
	    setLabels();
		const url = new URL(window.location.href);
		url.searchParams.set('lang_screen',$(this).val());
		window.location.href = url.toString();
   });
});

function toSupplierPdfReport() {
	window.open('accounts_payments_report.php?project_id='+$('#project_id').val()+'&ps_id='+$('#id_projects_suppliers').val()+'&lang='+$('#lang').val(),'_blank');
}
</script>

<style>
tr:nth-of-type(even) {
  background-color: #dedede;
}

.btn {
	background-color:#218FD6;
	color: white;
}

.btn:hover {
   background-color: #3370d6;
   color: white;
}

#a_project_title:hover {
	color: grey;
}

.td_white_en {
    border-bottom: 1px solid white !important;
	border-left: 1px solid white !important;
    border-collapse: collapse;
}

.td_white_he {
    border-bottom: 1px solid white !important;
	border-right: 1px solid white !important;
    border-collapse: collapse;
}
</style>