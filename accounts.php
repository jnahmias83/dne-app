<?php
include 'functions/functions.php';
include 'include/header.php';

$project_id = @$_GET['project_id'];
$lang_screen = @$_GET['lang_screen'];

$query = $mysqli->prepare("SELECT * FROM dne_projects WHERE id = ?");
$query->bind_param("i",$project_id );
$query->execute();
$query->store_result();
$project = fetch_unique($query);
	  
$query = $mysqli->prepare("SELECT a.id AS id,a.id_projects_suppliers AS id_projects_suppliers,
                          a.description AS description,a.pdf_submission AS pdf_submission,
						  a.submit_date AS submit_date,a.submitted_account AS submitted_account,
						  a.pdf_approval AS pdf_approval,a.approval_date AS approval_date,
						  a.approved_amount AS approved_amount,a.vat AS vat,
						  s.name AS s_name,s.name_he AS s_name_he,ps.id AS ps_id				  
						  FROM dne_accounts a
						  LEFT JOIN dne_projects_suppliers ps on a.id_projects_suppliers = ps.id
						  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
						  WHERE ps.id_project = ? ORDER BY a.approval_date");
$query->bind_param("i",$project_id);
$query->execute(); 
$query->store_result();
$payments_num_rows = $query->num_rows;
$accounts = fetch($query);

include 'menu_budget_reports.php';
?>

        <form method="post" action="" class="form-inline">
            <input type="hidden" id="id_project" name="id_project" value="<?=@$project_id?>" />	
			<input type="hidden" id="project_name" name="project_name" value="<?=htmlspecialchars(@$project->name)?>" />	
            <input type="hidden" id="project_name_he" name="project_name_he" value="<?=htmlspecialchars(@$project->name_he)?>" />
			
		    <div class="container">
			    <div class="row marginTop25 alignCenter">
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
							<a id="add_new_account_btn" onclick="location.href='add_account.php?id=0&project_id=<?=@$project_id?>&lang_screen=<?=@$lang_screen?>'"></a>
						</div>
						<div class="text-center mt-2">
							<a class="text-decoration-none cursor-pointer d-flex flex-column align-items-center" onclick="toAccountsPdfReport();">
								<img src="images/file-pdf-solid.svg" width="50" height="30" alt="PDF Icon" />
							    <?php if(@$lang_screen == 'HE'){ ?>
							      <strong class="font-family-david mt-1">דו''ח</strong>
							    <?php }
							    else { ?>
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
				
				<?php if($payments_num_rows > 0) {  ?>
					<div class="row fontSize14 marginTop15">
						<div align="center" class="col-md-12 mx-2">
							<table id="accounts_list" border="1">
								<thead>				    
									<tr class="bgColorSilver height50">
										<th id="th_iteration" class="alignCenter" width="50px;"></th>
										<th id="th_supplier_name" class="alignCenter" width="100px;"></th>
										<th id="th_submit_date" class="alignCenter" width="90px;"></th>
										<th id="th_description" class="alignCenter" width="250px;"></th>
										<th id="th_submitted_account_vat_included" class="alignCenter" width="120px;"></th>
										<th id="th_approval_date" class="alignCenter" width="80px;"></th>
										<th id="th_approved_amount_vat_included" class="alignCenter" width="120px;"></th>
										<th id="th_vat" class="alignCenter" width="60px;"></th>											
										<th width="40px;">&nbsp;</th>
										<th width="40px;">&nbsp;</th>
									</tr>
								</thead>
								
								<tbody>
									<?php
									$count = 0;
									foreach($accounts as $item) {
										$count++;
										
										$submit_date = '';
										if(@$item->submit_date != '0000-00-00')
											$submit_date = smartDate(@$item->submit_date, @$lang_screen);
										
										$submitted_account = '';
										if(@$item->submitted_account != 0.00)
										    $submitted_account = isset($item->submitted_account) 
											? (($item->submitted_account == round($item->submitted_account))
												?number_format($item->submitted_account,0,'.',',').'&#8362;'
												:number_format($item->submitted_account,2,'.',',').'&#8362;')
											:'';
										
										$approval_date = '';
										if(@$item->approval_date != '0000-00-00')
											$approval_date = smartDate(@$item->approval_date, @$lang_screen);
										
										$approved_amount = '';
										if(@$item->approved_amount != 0.00)
										    $approved_amount = isset($item->approved_amount)
											? (($item->approved_amount == round($item->approved_amount))
												?number_format($item->approved_amount,0,'.',',').'&#8362;'
												:number_format($item->approved_amount,2,'.',',').'&#8362;')
											:'';
										
										$invoice_date = '';
										if(@$item->invoice_date != '0000-00-00')
											$invoice_date = smartDate(@$item->invoice_date, @$lang_screen);
										?>
										
										<input type="hidden" id="hidden_s_name_<?=@$item->id_projects_suppliers?>" value="<?=@$item->s_name?>" />
										<input type="hidden" id="hidden_s_name_he_<?=@$item->id_projects_suppliers?>" value="<?=@$item->s_name_he?>" />
											
										<tr class="height30">
											<td class="alignCenter"><a href="add_account.php?id=<?=@$item->id?>&project_id=<?=@$project_id?>&lang_screen=<?=@$lang_screen?>"><?=@$count?></a></td>
											<td class="td_account_elem"><a id="s_name_<?=@$item->id_projects_suppliers?>" href="accounts_payments.php?ps_id=<?=@$item->ps_id?>&from=accounts&lang_screen=<?=@$lang_screen?>"></a></td>			
											<td class="alignCenter"><?=$submit_date?></td>							
											<td class="td_account_elem"><?=@$item->description?></td>
											<td class="td_account_elem" dir="ltr"><?php if(@$item->pdf_submission != '') { ?><a href="uploads/<?=@$item->pdf_submission?>" title="View PDF" target="_blank"><?=@$submitted_account?></a><?php } else { echo @$submitted_account; }?></td>
											<td class="td_account_elem"><?=$approval_date?></td>
											<td class="td_account_elem" dir="ltr"><?php if(@$item->pdf_approval != '') { ?><a href="uploads/<?=@$item->pdf_approval?>" title="View PDF" target="_blank"><?=@$approved_amount?></a><?php } else { echo @$approved_amount; }?></td>
											<td class="alignCenter"><?=number_format(@$item->vat,0,'.',',')?>%</td>
											<td class="alignCenter cursor-pointer"><img src="images/edit-button.svg" title="Edite" onclick="location.href='add_account.php?id=<?=@$item->id?>&project_id=<?=@$project_id?>&lang_screen=<?=@$lang_screen?>'" /></td>									
											<td class="alignCenter cursor-pointer"><img src="images/delete.svg" title="Remove" onclick="return removeAccount(<?=@$item->id?>);" /></td>	
										</tr>
										<?php
									}
									?>
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
		  $('#title').html("<span class='fontSize26 font-weight-bold cursor-pointer'>Accounts Report</span>");
          $('#div_btns').css({"direction":"ltr","text-align":"center"});
		  $('#add_new_account_btn').html("<div class='alignCenter border-black borderRadius10 padding-4x-4y bgColorWhite cursor-pointer'><i class='fa-solid fa-plus colorGrey'></i><br/><strong class='fontSize13'>Account</strong></div>");
		  $('#accounts_list').css({"direction":"ltr"});
		  $('#th_iteration').html('&#x2116; <i class="fa-solid fa-sort marginRight5"></i>');  
		  $('#th_submit_date').html('Submit <br/> Date <i class="fa-solid fa-sort marginLeft5"></i>');
          $('#th_supplier_name').html('Supplier <br/> Name <i class="fa-solid fa-sort marginLeft5"></i>'); 
          $('#th_description').html('Description <i class="fa-solid fa-sort marginLeft5"></i>'); 
		  $('#th_submitted_account_vat_included').html('Submitted <br/> account <br/> <em class="fontSize12">VAT included</em> <i class="fa-solid fa-sort marginLeft5"></i>');
		  $('#th_approval_date').html('Approval <br/> Date <i class="fa-solid fa-sort marginLeft5"></i>');
		  $('#th_approved_amount_vat_included').html('Approved <br/> amount <br/> <em class="fontSize12">VAT included</em> <i class="fa-solid fa-sort marginLeft5"></i>');
		  $('#th_vat').html('VAT <i class="fa-solid fa-sort marginLeft5"></i>');
		  $('.td_account_elem').css({"text-align":"left","padding-left":"5px"});
		  
		  $('a[id^="s_name"]').each(function () {
			  let elem = 'hidden_'+$(this).attr('id');
			  $(this).html($('#'+elem).val());
		  });
		    
		  $('#create_pdf_btn').val("Create PDF");
		  $('#create_pdf_btn').css({"direction":"ltr","margin-right":"10px"});
	  }
	  else if($('#lang').val() == 'HE') {
		   $('#project_name_title').html("<span class='fontSize26 font-weight-bold font-family-david cursor-pointer'>פרוייקט "+$('#project_name_he').val()+"</span>");
		   $('#title').html("<span class='fontSize26 font-weight-bold font-family-david cursor-pointer'>דו''ח חשבונות</span>");
		   $('#div_btns').css({"direction":"rtl","text-align":"center"});
		   $('#add_new_account_btn').html("<div class='alignCenter border-black borderRadius10 padding-4x-4y bgColorWhite cursor-pointer'><i class='fa-solid fa-plus colorGrey'></i><br/><strong class='fontSize13'>חשבון</strong></div>");
		   $('#accounts_list').css({"direction":"rtl"});
		   $('#th_iteration').html("מס' <i class='fa-solid fa-sort marginRight5'></i>");  
           $('#th_submit_date').html('תאריך <br/> הגשה <i class="fa-solid fa-sort marginRight5"></i>');
           $('#th_supplier_name').html('שם ספק <i class="fa-solid fa-sort marginRight5"></i>');  	
           $('#th_description').html('תיאור <i class="fa-solid fa-sort marginRight5"></i>');
           $('#th_submitted_account_vat_included').html("חשבון שהוגש<br/><em class=fontSize12>כולל מע''מ</em> <i class='fa-solid fa-sort marginRight5'></i>");
           $('#th_approval_date').html('תאריך <br/> אישור <i class="fa-solid fa-sort marginRight5"></i>');
           $('#th_approved_amount_vat_included').html("חשבון שאושר<br/><em class=fontSize12>כולל מע''מ</em> <i class='fa-solid fa-sort marginRight5'></i>");		   
		   $('#th_vat').html("מע''מ <i class='fa-solid fa-sort marginRight5'></i>");
		   $('.td_account_elem').css({"text-align":"right","padding-right":"5px"});
		
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
   
   $('#accounts_list th').click(function() {
      let $table = $(this).closest('table');
      let columnIndex = $(this).index();
      let sortDirection = $(this).hasClass('asc') ? 'desc' : 'asc';

	  $table.find('th').removeClass('asc desc');
	  $(this).addClass(sortDirection);
	
	  let rows = $table.find('tbody > tr').get();
	  rows.sort(function(rowA, rowB) {
		  let cellA = $(rowA).children('td').eq(columnIndex).text().toUpperCase();
		  let cellB = $(rowB).children('td').eq(columnIndex).text().toUpperCase();
		  if (sortDirection === 'asc') {
			return (cellA < cellB) ? -1 : (cellA > cellB) ? 1 : 0;
		  } else {
			return (cellA > cellB) ? -1 : (cellA < cellB) ? 1 : 0;
		  }
	   });
		
	   $.each(rows, function(index, row) {
		  $table.children('tbody').append(row);
	   });
  });
});

function toAccountsPdfReport() {
	window.open('accounts_report.php?project_id='+$('#id_project').val()+'&lang='+$('#lang').val(),'_blank');
}

function removeAccount(id) {
	if(confirm("Are you sure to remove this account?")) {
        let form_data = new FormData();	
		form_data.append('id',id);			
		$.ajax({
			type: 'POST',
			url: 'account_delete.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,			
			success: function(data){
				location.reload(true);			
			},
		});		
    }
    return false;
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