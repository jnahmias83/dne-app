<?php
include 'include/header.php';
include 'functions/functions.php';

$id = @$_GET['id'];
$from = @$_GET['from'];

$sup_type = "S";
$des_type = "D";
$manager_type = "M";
$etrp_type = "E";

$query = $mysqli->prepare("SELECT * FROM dne_projects WHERE id = ?");
$query->bind_param("i",$id);
$query->execute();
$query->store_result();
$project = fetch_unique($query);

$query = $mysqli->prepare("SELECT distinct sfow.name_he AS sfow_name_he,sfow.id AS sfow_id
                          FROM dne_suppliers s 
                          LEFT JOIN dne_sup_field_of_work sfow ON s.id_field_of_work = sfow.id 
						  WHERE sfow.sup_type = ? AND sfow.nickname <> ''
						  ORDER BY sfow.name_he ASC");
$query->bind_param("s",$des_type);
$query->execute();
$query->store_result();
$des_field_of_work = fetch($query);

$query = $mysqli->prepare("SELECT distinct sfow.name_he AS sfow_name_he,
                          sfow.id AS sfow_id
                          FROM dne_suppliers s 
                          LEFT JOIN dne_sup_field_of_work sfow ON s.id_field_of_work = sfow.id 
						  WHERE sfow.sup_type = ? AND sfow.nickname <> ''
						  ORDER BY sfow.name_he ASC");
$query->bind_param("s",$sup_type);
$query->execute();
$query->store_result();
$sup_field_of_work = fetch($query);

$query = $mysqli->prepare("SELECT ps.id AS id_projects_suppliers,
                          ps.id_project AS id_project,s.id AS s_id,
						  s.phone AS phone,s.email_office AS email_office,
						  s.name AS s_name,s.name_he AS s_name_he,
						  sfow.name AS sfow_name,sfow.name_he AS sfow_name_he
						  FROM dne_projects_suppliers AS ps 
						  LEFT JOIN dne_projects AS p ON ps.id_project = p.id 
						  LEFT JOIN dne_suppliers AS s ON ps.id_supplier = s.id
						  LEFT JOIN dne_sup_field_of_work sfow ON s.id_field_of_work = sfow.id
						  WHERE p.id = ? AND (s.type = ? OR s.type = ? OR s.type = ?)
						  ORDER BY sfow.name_he,s.name_he");
$query->bind_param("isss",$id,$des_type,$manager_type,$etrp_type);
$query->execute(); 
$query->store_result();
$existing_des_proj_num_rows = $query->num_rows;
$existing_des_proj = fetch($query);

$query = $mysqli->prepare("SELECT ps.id AS id_projects_suppliers,
                          ps.id_project AS id_project,s.id AS s_id,
						  s.phone AS phone,s.email_office AS email_office,
						  s.name AS s_name,s.name_he AS s_name_he,
						  sfow.name AS sfow_name,sfow.name_he AS sfow_name_he
						  FROM dne_projects_suppliers AS ps 
						  LEFT JOIN dne_projects AS p ON ps.id_project = p.id 
						  LEFT JOIN dne_suppliers AS s ON ps.id_supplier = s.id
						  LEFT JOIN dne_sup_field_of_work sfow ON s.id_field_of_work = sfow.id
						  WHERE p.id = ? AND s.type = ?
						  ORDER BY sfow.name_he,s.name_he");
$query->bind_param("is",$id,$sup_type);
$query->execute(); 
$query->store_result();
$existing_sup_proj_num_rows = $query->num_rows;
$existing_sup_proj = fetch($query);

if(isset($_SERVER['HTTP_REFERER'])){
    $previous_url = $_SERVER['HTTP_REFERER'];
    $previous_page = basename(parse_url($previous_url, PHP_URL_PATH));
	if($previous_page == 'budget.php' || $previous_page == 'accounts_payments.php' || 
	   $previous_page == 'payments_wires.php' || $previous_page == 'payments_missing_invoice.php' ||
	   $previous_page == 'orders.php' || $previous_page == 'add_order.php' ||
	   $previous_page == 'accounts.php' || $previous_page == 'add_account.php' ||
	   $previous_page == 'payments.php' || $previous_page == 'add_payment.php' ||
	   $previous_page == 'budget_costs_eval.php' || $previous_page == 'add_cost_eval.php')
		include 'menu_budget_reports.php';
	else 
        include 'menu_tasks.php';
}
?>          

		<form method="post" action="" enctype="multipart/form-data" class="form-inline">
			<input type="hidden" id="id" value="<?=@$id?>" />
			<input type="hidden" id="from" value="<?=@$from?>" />
			<input type="hidden" id="project_name" name="project_name" value="<?=htmlspecialchars(@$project->name)?>" />	
            <input type="hidden" id="project_name_he" name="project_name_he" value="<?=htmlspecialchars(@$project->name_he)?>" />
			
			<div class="container">
				<div class="row alignCenter marginTop25">
					<div class="col-12">
						<a href="projects.php"><img src="images/davidnahmias_logo.png" width="170px" height="170px" /></a>
					</div>
			    </div>
			
			    <div class="row marginTop15 title">	
					<div class="col-12">
						<a id="a_project_title" class="text-decoration-none color-1A5276" href="projects.php">
						    <strong class="fontSize33 line-height-1em"><?=@$project->nickname?></strong>
							<div id="project_name_title"></div>
					    </a>
                        <div id="title"></div> 						
					</div>				
			    </div>															

				<div class="row title-2">	
					<div id="designers_list_label" class="col-12"></div>					
				</div>
				
				<?php if($existing_des_proj_num_rows > 0) { ?>
					<div class="row marginTop10 fontSize14 dir-rtl">
						<div align="center" class="col-12 mx-2">
							<table id="designers_list" border="1" cellpadding="5" cellspacing="5">
								<tr class="bgColorSilver">
									<th class="th_domain alignCenter" width="130px;"></th>
									<th class="th_name alignCenter" width="180px;"></th>
									<th class="th_phone alignCenter" width="120px;"></th>
									<th class="th_email alignCenter" width="200px;"></th>
									<th width="40px;">&nbsp;</th>									
								</tr>
								<?php foreach($existing_des_proj as $item) { ?>			
								<input type="hidden" class="hidden_sfow_name_<?=@$item->id_projects_suppliers?>" value="<?php if(strlen(@$item->sfow_name) > 12) echo mb_substr(@$item->sfow_name,0,12,'UTF-8');else echo @$item->sfow_name?>" />
								<input type="hidden" class="hidden_sfow_name_he_<?=@$item->id_projects_suppliers?>" value="<?php if(strlen(@$item->sfow_name_he) > 12) echo mb_substr(@$item->sfow_name_he,0,12,'UTF-8');else echo @$item->sfow_name_he?>" />		
								<input type="hidden" class="hidden_s_name_<?=@$item->id_projects_suppliers?>" value="<?php if(strlen(@$item->s_name) > 20) echo mb_substr(@$item->s_name,0,20,'UTF-8');else echo @$item->s_name?>" />
								<input type="hidden" class="hidden_s_name_he_<?=@$item->id_projects_suppliers?>" value="<?php if(strlen(@$item->s_name_he) > 20) echo mb_substr(@$item->s_name_he,0,20,'UTF-8');else echo @$item->s_name_he?>" />
								
								<tr class="height35">
									<td class="td_designer_elem"><a class="sfow_name_<?=@$item->id_projects_suppliers?>"></a></td>
									<td class="td_designer_elem"><a class="s_name_<?=@$item->id_projects_suppliers?>" href="add_supplier.php?type_sup=D&id=<?=@$item->s_id?>&project_id=<?=@$id?>&from=add_sup_to_proj"></a></td>
									<td class="alignCenter"><?=@$item->phone?></td>
									<td class="alignCenter"><?=@$item->email_office?></td>
									<td class="alignCenter"><img class="delete_img cursor-pointer" src="images/delete.svg" title="" onclick="return removeSupplierFromProject(<?=@$item->id_projects_suppliers?>);" /></td>	
								</tr>
								<?php } ?>
							</table>
						</div>
					</div>
					<div class="row alignCenter marginTop15 fontSize22 dir-rtl">	
						<div class="col-12">
						    <a class="text-decoration-none marginLeft8 cursor-pointer" onclick="toProjectSuppliersPdfReport('D')">
						       <img src="images/file-pdf-solid.svg" width="70" height="40" />
					        </a> 				
						</div>
					</div>
				<?php } ?>
				<div class="row marginTop20 dir-rtl">
					<div id="div_designer_btns" align="center" class="col-12">
						<input type="button" id="save_des_btn" name="save_btn" class="btn mb-2" />						
						<select id="designers_domain" class="padding10 width220 height40 fontSize14">
							<option class="choose_designer_domain" value="0"></option>
							<?php 
								foreach($des_field_of_work as $item) {
									$query = $mysqli->prepare("SELECT COUNT(id) AS num_sups FROM dne_suppliers 
									                          WHERE id_field_of_work = ? AND type = ?");
									$query->bind_param("is",$item->sfow_id,$des_type);
									$query->execute();
									$query->store_result();
									$query = fetch_unique($query);
									$num_sups = $query->num_sups;
									
									$query = $mysqli->prepare("SELECT COUNT(ps.id_supplier) AS num_sups_proj
															  FROM dne_projects_suppliers ps
															  LEFT JOIN dne_projects p ON ps.id_project = p.id
															  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
															  WHERE p.id = ? AND s.id_field_of_work = ? AND s.type = ?");
									$query->bind_param("iis",$id,$item->id,$des_type);
									$query->execute();
									$query->store_result();
									$query = fetch_unique($query);
									$num_sups_proj = $query->num_sups_proj;
									if($num_sups_proj < $num_sups) {
									?>
										<option value="<?=$item->sfow_id?>">
											<?=@$item->sfow_name_he?>
										</option>
									<?php
									}
								} ?>										
						</select>
						&nbsp;
						<span id="span_designers_name"></span>
					</div>
				</div>	
				
				<div class="row title-2">	
					<div id="suppliers_list_label" class="col-12"></div>					
				</div>					
				
				<?php if($existing_sup_proj_num_rows > 0) { ?>
					<div class="row marginTop10 fontSize14 dir-rtl">
						<div align="center" class="col-12 mx-2">
							<table id="suppliers_list" border="1" cellpadding="5" cellspacing="5">
								<tr class="bgColorSilver">
									<th class="th_domain alignCenter" width="130px;"></th>
									<th class="th_name alignCenter" width="180px;"></th>
									<th class="th_phone alignCenter" width="120px;"></th>
									<th class="th_email alignCenter" width="200px;"></th>
									<th width="40px;">&nbsp;</th>								
								</tr>
								<?php foreach($existing_sup_proj as $item) { ?>
								<input type="hidden" class="hidden_sfow_name_<?=@$item->id_projects_suppliers?>" value="<?php if(strlen(@$item->sfow_name) > 12) echo mb_substr(@$item->sfow_name,0,12,'UTF-8');else echo @$item->sfow_name?>" />
								<input type="hidden" class="hidden_sfow_name_he_<?=@$item->id_projects_suppliers?>" value="<?php if(strlen(@$item->sfow_name_he) > 12) echo mb_substr(@$item->sfow_name_he,0,12,'UTF-8');else echo @$item->sfow_name_he?>" />						
								<input type="hidden" class="hidden_s_name_<?=@$item->id_projects_suppliers?>" value="<?php if(strlen(@$item->s_name) > 20) echo mb_substr(@$item->s_name,0,20,'UTF-8');else echo @$item->s_name?>" />
								<input type="hidden" class="hidden_s_name_he_<?=@$item->id_projects_suppliers?>" value="<?php if(strlen(@$item->s_name_he) > 20) echo mb_substr(@$item->s_name_he,0,20,'UTF-8');else echo @$item->s_name_he?>" />
									
								<tr class="height35">
									<td class="td_supplier_elem"><a class="sfow_name_<?=@$item->id_projects_suppliers?>"></a></td>
									<td class="td_supplier_elem"><a class="s_name_<?=@$item->id_projects_suppliers?>" href="add_supplier.php?type_sup=S&id=<?=@$item->s_id?>&project_id=<?=@$id?>&from=add_sup_to_proj"></a></td>
									<td class="alignCenter"><?=@$item->phone?></td>
									<td class="alignCenter"><?=@$item->email_office?></td>
									<td class="alignCenter"><img class="delete_img cursor-pointer" src="images/delete.svg" onclick="return removeSupplierFromProject(<?=@$item->id_projects_suppliers?>);" /></td>	
								</tr>
								<?php } ?>
							</table>
						</div>
					</div>
					
					<div class="row marginTop15 alignCenter fontSize22 dir-rtl">	
						<div class="col-12">
							<a class="text-decoration-none marginLeft8 cursor-pointer" onclick="toProjectSuppliersPdfReport('S')">
						       <img src="images/file-pdf-solid.svg" width="70" height="40" />
					        </a> 		
						</div>
					</div>
				<?php } ?>			
				<div class="row marginTop20 dir-rtl">
					<div id="div_supplier_btns" align="center" class="col-12">
						<input type="button" id="save_sup_btn" name="save_btn" class="btn mb-2" />						
						&nbsp;
						<select id="suppliers_domain" class="padding10 width220 height40 fontSize14">
							<option class="choose_supplier_domain" value="0"></option>
							<?php 
								foreach($sup_field_of_work as $item) {
									$query = $mysqli->prepare("SELECT COUNT(id) AS num_sups FROM dne_suppliers 
									                          WHERE id_field_of_work = ? AND type = ?");
									$query->bind_param("is",$item->id,$sup_type);
									$query->execute();
									$query->store_result();
									$query = fetch_unique($query);
									$num_sups = $query->num_sups;
									
									$query = $mysqli->prepare("SELECT COUNT(ps.id_supplier) AS num_sups_proj
															  FROM dne_projects_suppliers ps
															  LEFT JOIN dne_projects p ON ps.id_project = p.id
															  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
															  WHERE p.id = ? AND s.id_field_of_work = ? AND s.type = ?");
									$query->bind_param("iis",$id,$item->id,$sup_type);
									$query->execute();
									$query->store_result();
									$query = fetch_unique($query);
									$num_sups_proj = $query->num_sups_proj;			
									?>
										<option value="<?=$item->sfow_id?>">
											<?=@$item->sfow_name_he?>
										</option>
									<?php
								}
							?>										
						</select>
						&nbsp;
						<span id="span_suppliers_name"></span>
					</div>
				</div>
				
				<div class="row marginTop20 alignCenter">
					<div class="col-12">
						<select id="lang" name="lang" class="width100 height35 border-color-initial">
							<option value="HE" selected>עברית</option>
							<option value="EN">English</option>					   
						</select>   
					</div>
				</div>
            </div>			
		</form>
    </body>
</html>

<script>
$(document).ready(function() {
   setLabels();
   
   function setLabels(){ 
      $('#create_pdf_btn').css({"direction":"rtl","margin-right":"10px"});
	  $('#lang').css({"padding-left":"10px"});
	  
	  if($('#lang').val() == 'EN'){
		  $('#project_name_title').html('<span class="fontSize26 font-weight-bold cursor-pointer">Project '+$('#project_name').val()+'</span>');
          $('#title').html('<span class="fontSize26 font-weight-bold cursor-pointer">Contacts List</span>');
		  $('#designers_list_label').html('List of planners and consultants');
		  $('#designers_list').css({"direction":"ltr"});
		  $('.th_domain').html('Domain');
		  $('.th_name').html('Name');
		  $('.th_phone').html('Phone');
		  $('.th_email').html('Email');
		  $('.td_designer_elem').css({"text-align":"left","padding-left":"5px"});
		  
		  $('a[class^="s_name"]').each(function () {
			  let elem = 'hidden_'+$(this).attr('class');
			  $(this).html($('.'+elem).val());
		  });
		  
		  $('a[class^="sfow_name"]').each(function () {
			  let elem = 'hidden_'+$(this).attr('class');
			  $(this).html($('.'+elem).val());
			  $(this).css({"text-decoration":"none"});
		  });
		 
		  $('.delete_img').attr('title','Delete');
          $('#div_designer_btns').css({"direction":"ltr"});		 
		  $('.create_pdf_btn').val("Create PDF");
		  $('.create_pdf_btn').css({"direction":"ltr"});
		  $('#save_des_btn').val('Add new designer');
		  $('#save_des_btn').css({"direction":"ltr","margin-right":"10px"});
		  $('.choose_designer_domain').html('-- Choose designer domain --')
		  $('#suppliers_list_label').html('List of contractors and suppliers');
		  $('#suppliers_list').css({"direction":"ltr"});
		  $('.td_supplier_elem').css({"text-align":"left","padding-left":"5px"});
		  $('#div_supplier_btns').css({"direction":"ltr"});	
		  $('#save_sup_btn').val('Add new supplier');
		  $('.choose_supplier_domain').html('-- Choose supplier domain --');
	  }
	  else if($('#lang').val() == 'HE') {
		   $('#project_name_title').html('<span class="fontSize26 font-weight-bold cursor-pointer font-family-david">פרוייקט '+$('#project_name_he').val()+'</span>');
		   $('#title').html("<span class='fontSize26 font-weight-bold font-family-david cursor-pointer'>רשימת אנשי קשר</span>"); 
		   $('#designers_list_label').html('רשימת מתכננים ויועצים');
		   $('#designers_list').css({"direction":"rtl"});
		   $('.th_domain').html('תחום');
		   $('.th_name').html('שם');
		   $('.th_phone').html('טלפון');
		   $('.th_email').html("דוא''ל");
		   $('.td_designer_elem').css({"text-align":"right","padding-right":"5px"});
		
		  $('a[class^="s_name"]').each(function () {
			  let elem = 'hidden_s_name_he_'+$(this).attr('class').substring(7);
			  $(this).html($('.'+elem).val());
		  });
		  
		  $('a[class^="sfow_name"]').each(function () {
			  let elem = 'hidden_sfow_name_he_'+$(this).attr('class').substring(10);
			  $(this).html($('.'+elem).val());
			  $(this).css({"text-decoration":"none"});
		  });
		  
		  $('.delete_img').attr('title','מחיקה');
		  $('#div_designer_btns').css({"direction":"rtl"});	
		  $('.create_pdf_btn').val("הפקת PDF");
		  $('.create_pdf_btn').css({"direction":"rtl","margin-left":"10px"});
		  $('#save_des_btn').val('הוסף מתכנן חדש');
		  $('#save_des_btn').css({"direction":"rtl","margin-left":"10px"});
		  $('.choose_designer_domain').html('-- בחור תחום מתכנן --');
		  $('#suppliers_list_label').html('רשימת קבלנים וספקים');
		  $('#suppliers_list').css({"direction":"rtl"});
		  $('.td_supplier_elem').css({"text-align":"right","padding-right":"5px"});
		  $('#div_supplier_btns').css({"direction":"rtl"});	
		  $('#save_sup_btn').val('הוסף ספק חדש');
		  $('.choose_supplier_domain').html('-- בחור תחום ספק --');
	  }
   }
   
   $("#lang").change(function(){
      setLabels();
   });
});

function toProjectSuppliersPdfReport(supplier_type) { 
    let url = (supplier_type == 'D')? 'sup_to_proj_report.php?sup_type=D&project_id='+$('#id').val()+'&lang='+$('#lang').val():'sup_to_proj_report.php?sup_type=S&project_id='+$('#id').val()+'&lang='+$('#lang').val();	
	window.open(url,'_blank');
}

function removeSupplierFromProject(ps_id) {
	if(confirm("Are you sure to remove this supplier?")) {
        let form_data = new FormData();	
		form_data.append('id_projects_suppliers',ps_id);	
     
		$.ajax({
			type: 'POST',
			url: 'sup_from_proj_delete.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,			
			success: function(data){
				location.href = 'add_sup_to_proj.php?id='+$('#id').val();				
			},
		});		
    }
    return false;
}

$('#suppliers_domain').on('change', function (){
	$.post('populate_select_sup_name.php',{id_project:$('#id').val(),sup_type:'S',id_field_of_work:$(this).val()},function(data){
		$('#span_suppliers_name').html(data);
	});
});

$('#designers_domain').on('change', function (){
	$.post('populate_select_sup_name.php',{id_project:$('#id').val(),sup_type:'D',id_field_of_work:$(this).val()},function(data){
		$('#span_designers_name').html(data);
	});
});

$('#save_sup_btn').click (function (e){ 
	let form_data = new FormData();	
	
	form_data.append('id_project',$('#id').val());
	form_data.append('id_supplier',$('#suppliers_name').val());			
	$.ajax({
		type: 'POST',
		url: 'sup_to_proj_insert.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,			
		success: function(data){
			location.href = 'add_responsible.php?id=0&project_id='+$('#id').val()+'&from=addProject&for=projectgroup&ps_id='+data;
		},
	});
});

$('#save_des_btn').click (function (e){
	let form_data = new FormData();
	form_data.append('id_project',$('#id').val());
	form_data.append('id_supplier',$('#designers_name').val());
	$.ajax({
		type: 'POST',
		url: 'sup_to_proj_insert.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,
		success: function(data){
			location.href = 'add_responsible.php?id=0&project_id='+$('#id').val()+'&from=addProject&for=projectgroup&ps_id='+data;
		},
	});												       			   
});
</script>

<style>
table,th,td{
	border:1px solid black;
}

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
</style>