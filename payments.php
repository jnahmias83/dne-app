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
		  
$query = $mysqli->prepare("SELECT p.id AS id,p.id_projects_suppliers AS id_projects_suppliers,
                          p.description AS description,p.payment_date AS payment_date,
						  p.pdf_payment AS pdf_payment,p.paid_amount AS paid_amount_vat_included,
						  p.vat AS vat,p.paid_amount_vat_excluded AS paid_amount_vat_excluded,
						  p.invoice_date AS invoice_date,p.pdf_invoice AS pdf_invoice,s.name AS s_name,	
						  s.name_he AS s_name_he,ps.id AS ps_id
						  FROM dne_payments2 p
						  LEFT JOIN dne_projects_suppliers ps on p.id_projects_suppliers = ps.id
						  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
						  WHERE ps.id_project = ? ORDER BY p.payment_date");
$query->bind_param("i",$project_id);
$query->execute(); 
$query->store_result();
$payments_num_rows = $query->num_rows;
$payments = fetch($query);

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
				
		        <div class="margin-top-10-x-auto alignCenter">
					<div id="div_btns" class="col-4 bgColor-cbddec padding10 borderRadius10 text-center">
						<a id="add_new_payment_btn" onclick="location.href='add_payment.php?id=0&project_id=<?=@$project_id?>&lang_screen=<?=@$lang_screen?>';">
						  <span class="display-block">תשלום</span>
						</a> 
						<a onclick="toPaymentsPdfReport();" class="text-decoration-none cursor-pointer d-flex flex-column align-items-center">
							<img src="images/file-pdf-solid.svg" width="50" height="30" alt="PDF Icon" />
							<?php if(@$lang_screen == 'HE'){ ?>
								<strong class="font-family-david mt-1 text-center">דו''ח</strong>
							<?php } else { ?>
								<strong class="fontSize13 mt-1 text-center">Report</strong>
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
			
				<?php if($payments_num_rows > 0) { ?>
					<div class="row fontSize14 marginTop15">
						<div align="center" class="col-md-12 mx-2">
							<table id="payments_list" class="marginTop10" border="1">
								<thead>								    
								   <tr class="bgColorSilver height50">
										<th id="th_iteration" width="30px;" class="alignCenter">&#x2116;</th>
										<th id="th_supplier_name" width="140px;" class="alignCenter"></th>
										<th id="th_description" width="250px;" class="alignCenter"></th>
										<th id="th_payment_date" width="80px;" class="alignCenter"></th>
										<th id="th_paid_amount" width="120px;" class="alignCenter"></th>	
										<th id="th_invoice_date" width="80px;" class="alignCenter"></th>
										<th id="th_vat" width="30px;" class="alignCenter"></th>
										<th id="th_paid_amount_vat_excluded" width="120px;" class="alignCenter"></th>										
										<th width="40px;">&nbsp;</th>
										<th width="40px;">&nbsp;</th>
									</tr>
								</thead>
								
								<tbody>
									<?php
									$count = 0;
									foreach($payments as $item) {
										$count++;
										
										$payment_date = '';
										if(@$item->payment_date != '0000-00-00')
											$payment_date = smartDate(@$item->payment_date, @$lang_screen);
										
										$paid_amount_vat_included = '';
										if(@$item->paid_amount_vat_included != 0.00)
											$paid_amount_vat_included = isset($item->paid_amount_vat_included)
											? ((floor($item->paid_amount_vat_included) == $item->paid_amount_vat_included)
												?number_format($item->paid_amount_vat_included,0,'.',',').'&nbsp;&#8362'
												:number_format($item->paid_amount_vat_included,2,'.',',').'&nbsp;&#8362')
											:'';
										
										$paid_amount_vat_excluded = '';
										if(@$item->paid_amount_vat_excluded != 0.00)
											$paid_amount_vat_excluded = isset($item->paid_amount_vat_excluded)
											? ((floor($item->paid_amount_vat_excluded) == $item->paid_amount_vat_excluded)
												?number_format($item->paid_amount_vat_excluded,0,'.',',').'&nbsp;&#8362'
												:number_format($item->paid_amount_vat_excluded,2,'.',',').'&nbsp;&#8362')
											:'';
									
										$invoice_date = '';
										if(@$item->invoice_date != '0000-00-00')
											$invoice_date = smartDate(@$item->invoice_date, @$lang_screen);
										?>
										
										<input type="hidden" id="hidden_s_name_<?=@$item->id_projects_suppliers?>" value="<?php if(strlen(@$item->s_name) > 15) echo mb_substr(@$item->s_name,0,15,'UTF-8');else echo @$item->s_name?>" />
										<input type="hidden" id="hidden_s_name_he_<?=@$item->id_projects_suppliers?>" value="<?php if(strlen(@$item->s_name_he) > 15) echo mb_substr(@$item->s_name_he,0,15,'UTF-8');else echo @$item->s_name_he?>" />
										
										<tr class="height30">
											<td class="alignCenter"><a href="add_payment.php?id=<?=@$item->id?>&project_id=<?=@$project_id?>&lang_screen=<?=@$lang_screen?>"><?=@$count?></a></td>
											<td class="td_payment_elem"><a id="s_name_<?=@$item->id_projects_suppliers?>" href="accounts_payments.php?ps_id=<?=@$item->ps_id?>&from=payments&lang_screen=<?=@$lang_screen?>"><?=@$item->nickname?></a></td>			
											<td class="td_payment_elem"><?=@$item->description?></td>
											<td class="td_payment_elem"><?=$payment_date?></td>
											<td class="td_payment_elem"><?php if(@$item->pdf_payment != '') { ?><a href="uploads/<?=@$item->pdf_payment?>" title="View PDF" target="_blank"><?=@$paid_amount_vat_included?></a><?php } else { echo @$paid_amount_vat_included; }?></td>
											<td class="alignCenter"><?php if(@$item->pdf_invoice != '') { ?><a href="uploads/<?=@$item->pdf_invoice?>" title="View PDF" target="_blank"><?=@$invoice_date?></a><?php } else echo @$invoice_date?></td>
											<td class="alignCenter"><?=number_format(@$item->vat,0,'.',',')?>%</td>
											<td class="td_payment_elem"><?=@$paid_amount_vat_excluded?></td>
											<td class="alignCenter"><img src="images/edit-button.svg" class="cursor-pointer" title="Edite" onclick="location.href='add_payment.php?id=<?=@$item->id?>&project_id=<?=@$project_id?>&lang_screen=<?=@$lang_screen?>'" /></td>									
											<td class="alignCenter"><img src="images/delete.svg" class="cursor-pointer" title="Remove" onclick="return removePayment(<?=@$item->id?>);" /></td>	
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
		  $('#title').html("<span class='fontSize26 font-weight-bold cursor-pointer'>Payments Report</span>");
          $('#div_btns').css({"direction":"ltr","text-align":"center"});
		  $('#add_new_payment_btn').html("<div class='alignCenter border-black borderRadius10 padding-4x-4y'><i class='fa-solid fa-plus colorGrey'></i><br/><strong class='fontSize13'>Payment</strong></div>");
		  $('#payments_list').css({"direction":"ltr"});
		  $('#th_iteration').html('&#x2116;');  
          $('#th_supplier_name').html('Supplier <br/> Name'); 
          $('#th_description').html('Description'); 
		  $('#th_payment_date').html('Payment <br/> Date'); 
		  $('#th_paid_amount').html('Paid <br/> Amount'); 
		  $('#th_invoice_date').html('Invoice <br/> Date');
		  $('#th_vat').html('VAT');
		  $('#th_paid_amount_vat_excluded').html('Paid <br/><i>VAT excluded</i>');
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
		   $('#title').html("<span class='fontSize26 font-weight-bold font-family-david cursor-pointer'>דו''ח ביצוע תשלומים</span>");
		   $('#div_btns').css({"direction":"rtl","text-align":"center"});
		   $('#add_new_payment_btn').html("<div class='alignCenter border-black borderRadius10 padding-4x-4y'><i class='fa-solid fa-plus colorGrey'></i><br/><strong class='fontSize13'>תשלום</strong></div>");
		   $('#payments_list').css({"direction":"rtl"});
		   $('#th_iteration').html("מס'");  
           $('#th_supplier_name').html('שם ספק');  	
           $('#th_description').html('תיאור');
           $('#th_payment_date').html('תאריך <br/> תשלום'); 
		   $('#th_paid_amount').html('סכום <br/> שולם'); 
		   $('#th_vat').html("מע''מ");
		   $('#th_invoice_date').html('תאריך <br/> חשבונית');
		   $('#th_paid_amount_vat_excluded').html('שולם לא כולל');
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

function toPaymentsPdfReport() {
	window.open('payments_report.php?project_id='+$('#id_project').val()+'&lang='+$('#lang').val(),'_blank');
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

.margin-top-10-x-auto {
    margin-top: 10px;
    display: flex;
    justify-content: center;
    width: 100%;
}

#div_btns {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 10px;
    border-radius: 10px;
    width: 18%;
    min-width: 180px;
    max-width: 300px;
    box-sizing: border-box;
}

#div_btns a {
    margin: 6px 0;
    max-width: 150px;
    text-align: center;
}

@media (min-width: 600px) {
    #div_btns {
        flex-direction: row;
        justify-content: center;
    }

    #div_btns a {
        margin: 0 10px;
        max-width: 150px;
    }
}

@media screen and (min-width: 1500px) {
    #div_btns {
        width: 16%;
    }
}
</style>