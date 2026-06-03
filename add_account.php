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

$query = $mysqli->prepare("SELECT a.id AS id,a.id_projects_suppliers,a.description AS description,
                          a.pdf_submission AS pdf_submission,a.submit_date AS submit_date,
						  a.submitted_account AS submitted_account,a.pdf_approval AS pdf_approval,
						  a.approval_date AS approval_date,a.approved_amount AS approved_amount,a.vat AS vat,
                          s.name_he AS s_name_he						  
						  FROM dne_accounts a 				  
						  LEFT JOIN dne_projects_suppliers ps ON a.id_projects_suppliers = ps.id
						  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
						  WHERE a.id = ?");
$query->bind_param("i",$id);
$query->execute();
$query->store_result();
$account = fetch_unique($query);

$query = $mysqli->prepare("SELECT * FROM dne_vat");
$query->execute();
$query->store_result();
$vat = fetch_unique($query);

$title = 'חשבון חדש';

if(@$from == 'projects') {
	$query = $mysqli->prepare("SELECT ps.id AS id,s.name_he AS s_name_he
							  FROM dne_projects_suppliers ps
							  LEFT JOIN dne_projects p ON ps.id_project = p.id
							  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id	  
							  ORDER BY s_name_he ASC");
	$query->execute();
	$query->store_result();
	$suppliers = fetch($query);
}
else {
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
}

if($id > 0) {
   $submit_date = @$account->submit_date;
   $approval_date = @$account->approval_date;
   $title = 'אישור חשבון ספק';
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
				
				<?php if(@$id > 0) { ?>
					<div class="row title font-family-david dir-rtl">
						<div class="col-12 font-weight-bold fontSize26">
							<?=@$account->s_name_he?>
						</div>
					</div>  				   			  
				<? } ?>
				
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
									<select id="suppliers" <?php if(@$from == 'projects') echo "disabled"?>>							
										<?php foreach($suppliers as $item) { ?> 
											<option value="<?=@$item->id?>" <?php if(($id == 0 && $item->id == @$ps_id) || ($item->id == @$account->id_projects_suppliers)) echo "selected"?>>
												<?=@$item->s_name_he?>
											</option>
											<?php } ?>										
									</select>
								</div>
							</div>

                            <div class="row marginTop20 alignCenter dir-rtl">
								<div class="col-12 paddingTop2">
									<strong>תיאור</strong>					
								</div>
							</div>

                            <div class="row alignRight dir-rtl">
								<div class="col-4"></div>
								<div class="col-4">
									<div class="row">
										<div class="col-12 alignRight">
											<a class="text-decoration-none" onclick="$('#description').css({'direction':'ltr','padding-left':'10px'})">EN</a>&nbsp;|
											<a class="text-decoration-none" onclick="$('#description').css({'direction':'rtl','padding-right':'10px'})">HE</a>	
											
											<div class="paddingTop2">
											   <textarea class="marginTop5 width80Percents paddingRight10" name="description" id="description" rows="5" placeholder="תיאור..."><?=@$account->description?></textarea>
											</div>
										</div>				
									</div>
								</div>
								<div class="col-4"></div>
							</div>
                            
                            <div class="row marginTop15 dir-rtl">
								<div class="col-2"></div>
								<div class="col-4 padding0">
									<div class="card width95Percents alignCenter">
										<div class="card-header bgColorSilver"><strong>חשבון שהוגש</strong></div>
										<div class="card-body">
											<p>
												<strong class="label-font-size">PDF הגשה</strong>
												<br/>					
												<input type="file" class="marginTop5" name="pdf_submission" id="pdf_submission" />
												<?php if($id > 0) { ?>&nbsp;<a href="uploads/<?=@$account->pdf_submission?>" target="_blank"><?=@$account->pdf_submission?></a><?php } ?>			
											</p>
											<p>
											   <strong class="label-font-size">תאריך הגשה</strong>
											   <br/>					
											   <input type="date" name="submit_date" id="submit_date" class="marginTop5 paddingRight10 alignRight date-font-size width80Percents" value="<?=@$submit_date?>" />	
											</p>
											<p>
												<strong class="label-font-size">סך כולל מע''מ</strong>
												<br/>									
												<input type="text" class="marginTop5 paddingRight10 alignRight label-font-size width80Percents" name="submitted_account" id="submitted_account" placeholder="סך כולל מע''מ" value="<?=@$account->submitted_account?>" onchange="$('#submit_date').val('<?=date('Y-m-d')?>')" />&nbsp;&#8362;						
											</p>
										</div>
									</div>
								</div>
								<div class="col-4 padding0">
									<div class="card width95Percents alignCenter">
										<div class="card-header bgColorSilver"><strong>חשבון מאושר</strong></div>
										<div class="card-body">
											<p>
											   <strong class="label-font-size">PDF אישור</strong>
											   <br/>					
											   <input type="file" class="marginTop5" name="pdf_approval" id="pdf_approval" />
											   <?php if($id > 0) { ?>&nbsp;<a href="uploads/<?=@$account->pdf_approval?>" target="_blank"><?=@$account->pdf_approval?></a><?php } ?>			
											</p>
											<p>
											   <strong class="label-font-size">תאריך אישור</strong>
											   <br/>					
											   <input type="date" class="marginTop5 paddingRight10 alignRight date-font-size width80Percents" name="approval_date" id="approval_date" value="<?=@$approval_date?>" />	
											</p>
											<p>
												<strong class="label-font-size">סך מאושר לתשלום כולל מע''מ</strong>
												<br/>	
												
												<input type="text" class="marginTop5 paddingRight10 alignRight width80Percents label-font-size" name="approved_amount" id="approved_amount" placeholder="סך מאושר לתשלום כולל מע''מ" value="<?=@$account->approved_amount?>" onchange="$('#approval_date').val('<?=date('Y-m-d')?>')" />&nbsp;&#8362;		
												<?php if($id == 0) { ?>
												   <br/><br/>
												   <input type="checkbox" id="create_order_cb" name="create_order_cb" />&nbsp;לייצר הזמנה תואמת לחשבון זה
												<?php } ?>
											</p>
											
											<div class="row fontSize14">
												<div class="col-12">                   					
													<strong class="label-font-size">מע''מ</strong>
													<br/>		
													<input type="text" class="width60 paddingRight10" name="vat" id="vat" placeholder="VAT" value="<?php if($id == 0) echo @$vat->vat;else echo @$account->vat?>" />&nbsp;%	
												</div>
											</div>			
										</div>
									</div>
								</div>
								<div class="col-2"></div>
							</div>

                            <div class="row marginTop10 alignCenter">
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
var pdf_submission;
var pdf_approval;

$(document).on('change','#pdf_submission',function() {
    pdf_submission = $('#pdf_submission')[0].files[0];
});

$(document).on('change','#pdf_approval',function() {
    pdf_approval = $('#pdf_approval')[0].files[0];
});

$('#suppliers').chosen();

let create_order_from_account = 0;

$('#create_order_cb').click (function (e){  
    if ($(this).is(':checked'))
      create_order_from_account = 1;
    else if(!($(this).is(':checked'))) 
	  create_order_from_account = 0;
});

$('#save_btn').click (function (e){  
	var form_data = new FormData();	
	form_data.append('id',$('#id').val());
	form_data.append('id_projects_suppliers',$('#suppliers').val());
	form_data.append('description',$('#description').val());
	form_data.append('pdf_submission',pdf_submission);
	form_data.append('submit_date',$('#submit_date').val());
	form_data.append('submitted_account',$('#submitted_account').val());
	form_data.append('pdf_approval',pdf_approval);
	form_data.append('approval_date',$('#approval_date').val());
	form_data.append('approved_amount',$('#approved_amount').val());
	form_data.append('create_order_from_account',create_order_from_account);
	form_data.append('vat',$('#vat').val());
	
	$.ajax({
		type: 'POST',
		url: 'account_insert.php',
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
			let url;
			if($('#from').val() == 'not_app_acts')
			   url = 'not_approved_accounts.php';
			else if($('#from').val() == 'budget')
			   url = 'budget.php?project_id='+$('#project_id').val()+'&lang_screen='+$('#lang_screen').val();
			else if($('#from').val() == 'accounts_payments')
			   url = 'accounts_payments.php?ps_id='+$('#ps_id').val()+'&lang_screen='+$('#lang_screen').val();
			else if($('#from').val() == 'projects')
			   url = 'projects.php';
			else 
			   url = 'accounts.php?project_id='+$('#project_id').val()+$('#lang_screen').val();
			location.href = url;
	    },
	});												       			   
})

$('#cancel_btn').click(function(){
	let url;
	if($('#from').val() == 'not_app_acts')
	   url = 'not_approved_accounts.php';
    else if($('#from').val() == 'budget')
	   url = 'budget.php?project_id='+$('#project_id').val()+'&lang_screen='+$('#lang_screen').val();
	else if($('#from').val() == 'accounts_payments')
	   url = 'accounts_payments.php?ps_id='+$('#ps_id').val()+'&lang_screen='+$('#lang_screen').val();
    else if($('#from').val() == 'projects')
	   url = 'projects.php';
	else 
	   url = 'accounts.php?project_id='+$('#project_id').val()+'&lang_screen='+$('#lang_screen').val();
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
		font-size: 0.6rem;
	}
	
	.date-font-size {
		font-size: 0.7rem;
	}
}

@media (max-width: 600px) {
	.label-font-size {
		font-size: 0.5rem;
	}
	
	.date-font-size {
		font-size: 0.6rem;
	}
}
</style>