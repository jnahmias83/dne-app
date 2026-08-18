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

$query = $mysqli->prepare("SELECT o.id_projects_suppliers AS id_projects_suppliers,o.id AS id,
                          ps.id_supplier AS id_supplier,o.sum_order AS sum_order,o.pdf_order AS pdf_order,
						  o.signature_date AS signature_date,o.description AS description,
						  s.name AS s_name,s.name_he AS s_name_he,
						  sfow.name AS sfow_name,sfow.name_he AS sfow_name_he,ps.id AS ps_id
                          FROM dne_orders o
						  LEFT JOIN dne_projects_suppliers ps ON o.id_projects_suppliers = ps.id
						  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
						  LEFT JOIN dne_sup_field_of_work sfow ON s.id_field_of_work = sfow.id
						  WHERE ps.id_project = ? ORDER BY o.signature_date,s.type,s.name");
$query->bind_param("i",$project_id);
$query->execute(); 
$query->store_result();
$orders_num_rows = $query->num_rows;
$orders = fetch($query);

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
					<div id="div_btns" class="d-inline-flex flex-column align-items-center gap-2 bgColorBrown pt-3 px-3 pb-3 rounded-4">
						<div class="d-flex justify-content-center">
					    	<a id="add_new_order_btn" onclick="location.href='add_order.php?id=0&project_id=<?=@$project_id?>&lang_screen=<?=@$lang_screen?>'"></a>
						</div>
						<div class="text-center mt-2">
					    	<a class="text-decoration-none cursor-pointer d-flex flex-column align-items-center" onclick="toOrdersPdfReport();">
								<img src="images/file-pdf-solid.svg" width="50" height="30" alt="PDF Icon" />
								<?php if(@$lang_screen == 'HE'){ ?>
									<strong class="font-family-david mt-1 text-center">דו''ח</strong>
								<?php } else { ?>
									<strong class="fontSize13 mt-1 text-center">Report</strong>
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

				<?php if($orders_num_rows > 0) { ?>		
					<div class="row marginTop15 fontSize14 alignCenter">
						<div align="center" class="col-md-12 mx-2">
							<table id="orders_list" border="1">						
								<thead>
									<tr class="height50">
										<th id="th_iteration" width="40px;" class="alignCenter"></th>
										<th id="th_signature_date" width="80px;" class="alignCenter"></th>
										<th id="th_supplier_name" width="130px;" class="alignCenter"></th>
										<th id="th_supplier_domain" width="130px;" class="alignCenter"></th>
										<th id="th_description" width="250px" class="alignCenter"></th>
										<th id="th_total_orders" width="120px;" class="alignCenter">Total <br/> orders</th>
										<th width="40px;" class="alignCenter">&nbsp;</th>
										<th width="40px;" class="alignCenter">&nbsp;</th>
									</tr>
								</thead>
			
			                    <tbody>
									<?php
									$count = 0;
									$total_sum_orders = 0;
									foreach($orders as $item) {
										$count++;
										
										$signature_date = '';
										if(@$item->signature_date != '0000-00-00')
											$signature_date = smartDate(@$item->signature_date, @$lang_screen);
										
										$sum_order = '';
										if(@$item->sum_order != 0.00)
											$sum_order = isset($item->sum_order)
											? ((floor($item->sum_order) == $item->sum_order)
												?number_format($item->sum_order,0,'.',',')
												:number_format($item->sum_order,2,'.',','))
											:'';

										
										$total_sum_orders += $item->sum_order;
										?>
										<input type="hidden" id="hidden_s_name_<?=@$item->id_projects_suppliers?>" value="<?php if(strlen(@$item->s_name) > 15) echo mb_substr(@$item->s_name,0,15,'UTF-8');else echo @$item->s_name?>" />
										<input type="hidden" id="hidden_s_name_he_<?=@$item->id_projects_suppliers?>" value="<?php if(strlen(@$item->s_name_he) > 15) echo mb_substr(@$item->s_name_he,0,15,'UTF-8');else echo @$item->s_name_he?>" />
										<input type="hidden" id="hidden_sfow_name_<?=@$item->id_projects_suppliers?>" value="<?php if(strlen(@$item->sfow_name) > 15) echo mb_substr(@$item->sfow_name,0,15,'UTF-8');else echo @$item->sfow_name?>" />
										<input type="hidden" id="hidden_sfow_name_he_<?=@$item->id_projects_suppliers?>" value="<?php if(strlen(@$item->sfow_name_he) > 15) echo mb_substr(@$item->sfow_name_he,0,15,'UTF-8');else echo @$item->sfow_name_he?>" />
										
										<tr class="height35">
											<td class="alignCenter"><a href="add_order.php?id=<?=@$item->id?>&project_id=<?=@$project_id?>&lang_screen=<?=@$lang_screen?>"><?=@$count?></a></td>
											<td class="alignCenter"><?=@$signature_date?></td>
											<td class="td_order_elem"><a id="s_name_<?=@$item->id_projects_suppliers?>" href="accounts_payments.php?ps_id=<?=@$item->ps_id?>&from=orders"></a></td>	
											<td class="td_order_elem"><a id="sfow_name_<?=@$item->id_projects_suppliers?>"></a></td>
											<td class="td_order_elem"><?=@$item->description?></td>										
											<td class="td_order_elem"><?php if(@$item->pdf_order != '') { ?><a href="uploads/<?=@$item->pdf_order?>" title="View PDF" target="_blank"><?=@$sum_order.'&#8362'?></a><?php } else { echo @$sum_order.'&#8362'; }?></td>
											<td class="alignCenter"><img src="images/edit-button.svg" class="cursor-pointer" title="Edite" onclick="location.href='add_order.php?id=<?=@$item->id?>&project_id=<?=@$project_id?>&lang_screen=<?=@$lang_screen?>'" /></td>									
											<td class="alignCenter"><img src="images/delete.svg" class="cursor-pointer" title="Remove" onclick="return removeOrder(<?=@$item->id?>);" /></td>	
										</tr>
										<?php
									}
									
									$total_sum_orders = isset($total_sum_orders)
									? ((floor($total_sum_orders) == $total_sum_orders)
										?number_format($total_sum_orders,0,'.',',')
										:number_format($total_sum_orders,2,'.',','))
									:'';
									?>
									
									<tr class="height30 bgColorSkyblue">
									   <td id="td_total_orders" colspan="5"></td>
									   <td class="alignLeft paddingLeft5"><strong><?=@$total_sum_orders?>&nbsp;&#8362;</strong></td>
									   <td colspan="2">&nbsp;</td>
									</tr>
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
		  $('#title').html("<span class='fontSize26 font-weight-bold cursor-pointer'>Orders Report</span>");
          $('#div_btns').css({"direction":"ltr","text-align":"center"});
		  $('#add_new_order_btn').html("<div class='alignCenter border-black borderRadius10 padding-4x-4y bgColorWhite'><i class='fa-solid fa-plus colorGrey'></i><br/><strong class='fontSize13'>Order</strong></div>");
		  $('#orders_list').css({"direction":"ltr"});
		  $('#th_iteration').html('&#x2116;');  
		  $('#th_signature_date').html('Signature <br/> Date');
          $('#th_supplier_name').html('Supplier <br/> Name'); 
          $('#th_supplier_domain').html('Supplier <br/> Domain');
          $('#th_description').html('Description'); 
          $('#th_total_orders').html('Total <br/> Orders'); 
          $('.td_order_elem').css({"text-align":"left","padding-left":"5px"});
          $('#td_total_orders').html('<strong>Total Orders</strong>');
		  $('#td_total_orders').css({"text-align":"right","padding-right":"5px"});
		  
		  $('a[id^="s_name"]').each(function () {
			  let elem = 'hidden_'+$(this).attr('id');
			  $(this).html($('#'+elem).val());
		   });
		  
		   $('a[id^="sfow_name"]').each(function () {
			  let elem = 'hidden_'+$(this).attr('id');
			  $(this).html($('#'+elem).val());
		  });
		  
		  $('#create_pdf_btn').val("Create PDF");
		  $('#create_pdf_btn').css({"direction":"ltr","margin-right":"10px"});
	  }
	  else if($('#lang').val() == 'HE') {
		   $('#project_name_title').html("<span class='fontSize26 font-weight-bold font-family-david cursor-pointer'>פרוייקט "+$('#project_name_he').val()+"</span>");
		   $('#title').html("<span class='fontSize26 font-weight-bold font-family-david cursor-pointer'>דו''ח הזמנות</span>");
		   $('#div_btns').css({"direction":"rtl","text-align":"center"});
		   $('#add_new_order_btn').html("<div class='alignCenter border-black borderRadius10 padding-4x-4y bgColorWhite'><i class='fa-solid fa-plus colorGrey'></i><br/><strong class='fontSize13'>הזמנה</strong></div>");
		   $('#orders_list').css({"direction":"rtl"});
		   $('#th_iteration').html("מס'");  
           $('#th_signature_date').html('תאריך חתימה');
           $('#th_supplier_name').html('שם ספק');  	
           $('#th_supplier_domain').html('תחום ספק'); 
           $('#th_description').html('תיאור');
           $('#th_total_orders').html('סך הזמנות'); 
           $('.td_order_elem').css({"text-align":"right","padding-right":"5px"});		
           $('#td_total_orders').html("<strong>סה''כ הזמנות</strong>");
		   $('#td_total_orders').css({"text-align":"left","padding-left":"5px"});		   
		  
		    $('a[id^="s_name"]').each(function () {
			  let elem = 'hidden_s_name_he_'+$(this).attr('id').substring(7);
			  $(this).html($('#'+elem).val());
		   });
		  
		  $('a[id^="sfow_name"]').each(function () {
			  let elem = 'hidden_sfow_name_he_'+$(this).attr('id').substring(10);
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

function toOrdersPdfReport() {
	window.open('orders_report.php?project_id='+$('#id_project').val()+'&lang='+$('#lang').val(),'_blank');
}
</script>

<style>
tr:nth-of-type(even) {
     background-color: #dedede!important;
}

thead tr:last-of-type {
    background-color: silver!important;
}

tbody tr:last-of-type {
    background-color: #dcf1fa!important;
}

.btn {
	background-color: #218FD6;
	color: white;
}

.btn:hover {
   background-color: #3370d6;
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