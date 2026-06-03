<?php
include 'include/header.php';
include 'functions/functions.php';

$project_id = @$_GET['project_id'];
$lang_screen = @$_GET['lang_screen'];

$query = $mysqli->prepare("SELECT * FROM dne_projects WHERE id = ?");
$query->bind_param("i",$project_id );
$query->execute();
$query->store_result();
$project = fetch_unique($query);

$missing_invoice = '';
$query = $mysqli->prepare("SELECT p.id AS id,p.id_projects_suppliers AS id_projects_suppliers,
                          p.payment_date AS payment_date,p.pdf_payment AS pdf_payment,
						  p.paid_amount AS paid_amount_vat_included,s.name AS s_name,
						  s.name_he AS s_name_he,ps.id AS ps_id,
						  s.email_office AS s_email_office,s.mobile AS s_mobile
						  FROM dne_payments2 p
						  LEFT JOIN dne_projects_suppliers ps on p.id_projects_suppliers = ps.id
						  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
						  WHERE ps.id_project = ? AND p.pdf_invoice = ?
						  ORDER BY p.payment_date");
$query->bind_param("is",$project_id,$missing_invoice);
$query->execute(); 
$query->store_result();
$payments_missing_invoice_num_rows = $query->num_rows;
$payments_missing_invoice = fetch($query);

include 'menu_budget_reports.php';
?>
		<form method="post" action="" class="form-inline">
            <input type="hidden" id="id_project" name="id_project" value="<?=@$project_id?>" />	
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
						<a id="a_project_title" class="text-decoration-none color-1A5276" href="budget.php?project_id=<?=@$project_id?>&lang_screen=<?=@$lang_screen?>">
						    <strong class="fontSize33 line-height-1em"><?=@$project->nickname?></strong>
							<div id="project_name_title"></div>
					    </a>
                        <div id="title"></div> 						
					</div>				
			    </div>
				
				<div class="margin-top-10-x-auto d-flex justify-content-center">
					<div id="div_btns" class="bgColor-cbddec padding10 text-center">
						<a class="text-decoration-none cursor-pointer d-flex flex-column align-items-center" onclick="toPaymentsMissingInvoicePdfReport();">
							<img src="images/file-pdf-solid.svg" width="50" height="30" alt="PDF Icon" />
							<?php if(@$lang_screen == 'HE'){ ?>
								<strong class="font-family-david mt-1">דו''ח</strong>
							<?php } else { ?>
								<strong class="fontSize13 mt-1">Report</strong>
							<?php } ?>
						</a>
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
				
				<?php if($payments_missing_invoice_num_rows > 0) { ?>
					<div class="row fontSize14">
						<div align="center" class="col-md-12">
							<table id="payments_missing_invoice_list" class="marginTop10" border="1">
								<thead>	   
									<tr class="bgColorSilver height50">
										<th id="th_iteration" width="40px;" class="alignCenter">&#x2116;</th>
										<th id="th_supplier_name" width="140px;" class="alignCenter"></th>
										<th id="th_payment_date" width="80px;" class="alignCenter"></th>
										<th id="th_paid_amount" width="100px;" class="alignCenter"></th>
										<th id="th_email" width="200px;" class="alignCenter"></th>
										<th id="th_mobile" width="100px;" class="alignCenter"></th>
										<th width="40px" class="alignCenter">&nbsp;</th>									
									</tr>
								</thead>
								
								<tbody>
									<?php
									$count = 0;
									foreach($payments_missing_invoice as $item) {
										$count++;
										
										$payment_date = '';
										if(@$item->payment_date != '0000-00-00')
											$payment_date = substr(@$item->payment_date,8,2).'/'.substr(@$item->payment_date,5,2).'/'.substr(@$item->payment_date,2,2);
										
										$paid_amount_vat_included = '';
										if(@$item->paid_amount_vat_included != 0.00)
											$paid_amount_vat_included = isset($item->paid_amount_vat_included)
											? ((floor($item->paid_amount_vat_included) == $item->paid_amount_vat_included)
												?number_format($item->paid_amount_vat_included,0,'.',',').'&nbsp;&#8362'
												:number_format($item->paid_amount_vat_included,2,'.',',').'&nbsp;&#8362')
											:'';
										?>
										
										<input type="hidden" id="hidden_s_name_<?=@$item->id_projects_suppliers?>" value="<?php if(strlen(@$item->s_name) > 15) echo mb_substr(@$item->s_name,0,15,'UTF-8');else echo @$item->s_name?>" />
										<input type="hidden" id="hidden_s_name_he_<?=@$item->id_projects_suppliers?>" value="<?php if(strlen(@$item->s_name_he) > 15) echo mb_substr(@$item->s_name_he,0,15,'UTF-8');else echo @$item->s_name_he?>" />
										
										<tr class="height30">
											<td class="alignCenter"><a href="add_payment.php?id=<?=@$item->id?>&project_id=<?=@$project_id?>&from=payments_missing_invoice&lang_screen=<?=@$lang_screen?>"><?=@$count?></a></td>
											<td class="td_payment_elem"><a id="s_name_<?=@$item->id_projects_suppliers?>" href="accounts_payments.php?ps_id=<?=@$item->ps_id?>&from=payments_missing_invoice&lang_screen=<?=@$lang_screen?>"><?=@$item->nickname?></a></td>			
											<td class="td_payment_elem"><?=$payment_date?></td>
											<td class="td_payment_elem"><?php if(@$item->pdf_payment != '') { ?><a href="uploads/<?=@$item->pdf_payment?>" title="View PDF" target="_blank"><?=@$paid_amount_vat_included?></a><?php } else { echo @$paid_amount_vat_included; }?></td>
											<td class="td_payment_elem alignCenter"><?=@$item->s_email_office?></td>
											<td class="td_payment_elem alignCenter"><?=@$item->s_mobile?></td>
											<td class="td_payment_elem alignCenter">
											   <a class="cursor-pointer" onclick="send_email('<?=@$item->s_email_office?>','<?=@$item->payment_date?>','<?=@$item->paid_amount_vat_included?>')">
												  <img src="images/at-solid.svg" width="30" height="20"/>
											   </a>
											</td>
										</tr>
										<?php
									} ?>
								</tbody>
							</table>		
						</div>
					</div>					
				<?php } ?>
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
		  $('#title').html("<span class='fontSize26 font-weight-bold cursor-pointer'>Missing Invoices Report</span>");
		  $('#div_btns').css({"direction":"ltr"});
		  $('#payments_missing_invoice_list').css({"direction":"ltr"});
		  $('#th_iteration').html('&#x2116;');  
          $('#th_supplier_name').html('Supplier <br/> Name'); 
		  $('#th_payment_date').html('Payment <br/> Date'); 
		  $('#th_paid_amount').html('Paid <br/> Amount'); 
		  $('#th_email').html('Email'); 
		  $('#th_mobile').html('Mobile'); 
		  $('.td_payment_elem').css({"text-align":"left","padding-left":"5px"});
		  	  
		  $('a[id^="s_name"]').each(function () {
			  let elem = 'hidden_'+$(this).attr('id');
			  $(this).html($('#'+elem).val());
		  });
		  
		  $('#create_pdf_btn').val("Create PDF");
		  $('#create_pdf_btn').css({"direction":"ltr","margin-right":"10px"});
	  }
	  else if($('#lang').val() == 'HE') {
		   $('#project_name_title').html("<span class='fontSize26 font-weight-bold font-family-david cursor-pointer'>פרוייקט "+$('#project_name_he').val()+"</span>");
		   $('#title').html("<span class='fontSize26 font-weight-bold font-family-david cursor-pointer'>דו''ח חשבוניות חסרות</span>");
		   $('#div_btns').css({"direction":"rtl"});
		   $('#payments_missing_invoice_list').css({"direction":"rtl"});
		   $('#th_iteration').html("מס'");  
           $('#th_supplier_name').html('שם ספק');  	
           $('#th_payment_date').html('תאריך <br/> תשלום'); 
		   $('#th_paid_amount').html('סכום <br/> שולם'); 
		   $('#th_email').html("דוא''ל"); 
		   $('#th_mobile').html("נייד"); 
		   $('.td_payment_elem').css({"text-align":"right","padding-right":"5px"});
		
		   $('a[id^="s_name"]').each(function () {
			  let elem = 'hidden_s_name_he_'+$(this).attr('id').substring(7);
			  $(this).html($('#'+elem).val());
		   });
		  
		  $('#create_pdf_btn').val("הפקת PDF");
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

function toPaymentsMissingInvoicePdfReport() {
	window.open('payments_missing_invoice_report.php?project_id='+$('#id_project').val()+'&lang='+$('#lang').val(),'_blank');
}

function send_email(email,payment_date,paid_amount_vat_included) {
	let form_data = new FormData();
	form_data.append('from','payments_missing_invoice');
	form_data.append('project_name_he',$('#project_name_he').val());
	form_data.append('email_recipient',email);
	form_data.append('payment_date',payment_date);
	form_data.append('paid_amount_vat_included',paid_amount_vat_included);
	   
	$.ajax({
		type: 'POST',
		url: 'send_email.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,
		success: function(data){
		   if(data == 'Email sent successfully')
			  alert(data);
		},
	});
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
    max-width: 120px; 
    min-width: 80px;
    border-radius: 10px;
}
</style>