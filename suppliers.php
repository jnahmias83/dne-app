<?php
include 'include/header.php';
include 'functions/functions.php';

$query = $mysqli->prepare("SELECT * FROM dne_logos LIMIT 1");
$query->execute();
$query->store_result();
$logo = fetch_unique($query);

$type_sup = @$_GET['type'];

if($type_sup == 'S') {
	$type = 'S';
	$list_label = 'רשימת קבלנים וספקים';
	$title_add_btn = "ספק/קבלן";

	$query = $mysqli->prepare("SELECT * FROM dne_suppliers WHERE type = ? ORDER BY name");
	$query->bind_param("s",$type);
	$query->execute();
	$query->store_result();
	$suppliers = fetch($query);
}
else if($type_sup == 'D') {
	$type = 'D';
	$list_label = 'רשימת מתכננים ויועצים';
    $title_add_btn = "מתכנן/יועץ";

    $query = $mysqli->prepare("SELECT * FROM dne_suppliers WHERE type = ? ORDER BY name");
    $query->bind_param("s",$type);
    $query->execute();
    $query->store_result();
    $designers = fetch($query);
}

$alert_not_allowed_delete = ($type_sup == 'D')
	? 'אי אפשר למחוק מתכנן זה כי יש לאנשי הצוות שלו משימות.'
	: 'אי אפשר למחוק ספק זה כי יש לאנשי הצוות שלו משימות.';

$title_add_btn .= "&nbsp;<i class='fa fa-plus'></i>";

$name_label = 'שם באנגלית';
$name_he_label = 'שם בעברית';
$nickname_label = 'Nickname';
$domain_label = 'תחום';
$phone_label = 'טלפון';
$cellular_label = 'נייד';
$email_office_label = "דוא''ל";
$edite_label = 'עדכן';
$remove_label = 'מחוק';
$alert_confirme_delete = ($type_sup == 'D')
	? 'האם אתה בטוח שברצונך למחוק מתכנן זה?'
	: 'האם אתה בטוח שברצונך למחוק ספק זה?';
?>

        <form method="post" action="" class="form-inline">
		    <input type="hidden" id="type" value="<?=@$type?>" />
			
            <div class="container">
			    <div class="row alignCenter marginTop25">
					<div class="col-12">
						<a href="projects.php"><img src="images/<?=@$logo->logo?>" width="170" height="170" /></a>
					</div>
			    </div>
				
				<div class="row marginTop5 title fontSize22">
						<div class="col-12">
							<?=@$list_label?>
						</div>					
				</div>
			
				<div class="row marginTop10 alignCenter">
					<div class="col-12">
						<a class="btn" onclick="location.href='add_supplier.php?type_sup=<?=@$type_sup?>&id=0';" ><?=@$title_add_btn?></a>
					</div>
				</div>		
				
				<?php if($_GET['type'] == 'S') { ?>
					<div class="row fontSize18 marginTop5 dir-rtl">
						<div class="col-12 mx-2">
							<div class="table-responsive overflow-x-scroll">
								<table class="table" id="suppliers_list" border="1">
									<thead>
										<tr>
										    <th><?=@$name_he_label?></th>
											<th><?=@$name_label?></th>	
											<th><?=@$nickname_label?></th>
											<th><?=@$domain_label?></th>
											<th><?=@$phone_label?></th>
											<th><?=@$cellular_label?></th>
											<th><?=@$email_office_label?></th>						
											<th width="30px;">&nbsp;</th>
											<th width="30px;">&nbsp;</th>
										</tr>										
									</thead>
									
									<tfoot>
										<tr>
											<th><?=@$name_he_label?></th>
											<th><?=@$name_label?></th>
											<th><?=@$nickname_label?></th>
											<th><?=@$domain_label?></th>
											<th><?=@$phone_label?></th>
											<th><?=@$cellular_label?></th>
											<th><?=@$email_office_label?></th>							
											<th width="30px;">&nbsp;</th>
											<th width="30px;">&nbsp;</th>
										</tr>										
									</tfoot>
								
									<?php
									foreach($suppliers as $item) {
										$query = $mysqli->prepare("SELECT * FROM dne_sup_field_of_work WHERE id = ?");
										$query->bind_param("i",$item->id_field_of_work);
										$query->execute();
										$query->store_result();
										$sup_field_of_work = fetch_unique($query);
										?>
										<tr>
                                            <td><?=@$item->name_he?></td>										
											<td><?=@$item->name?></td>			
											<td><?=@$item->nickname?></td>
											<td><?=$sup_field_of_work->name_he?></td>
											<td><?=@$item->phone?></td>
											<td><?=@$item->mobile?></td>
											<td><?=@$item->email_office?></td>
											<td><img src="images/edit-button.svg" class="paddingTop10 cursor-pointer" title="<?=@$edite_label?>" onclick="location.href='add_supplier.php?type_sup=S&id=<?=@$item->id?>'" /></td>
											<td><img src="images/delete.svg" class="paddingTop10 cursor-pointer" title="<?=@$remove_label?>" onclick="return removeSupplier(<?=@$item->id?>);" /></td>										
										</tr>
										<?php
									}
									?>
								</table>		
							</div>	
						</div>
					</div>	
				<?php } 
				else if($_GET['type'] == 'D') { ?>		
					<div class="row fontSize18 marginTop15 dir-rtl">
						<div class="col-12 mx-2">
							<div class="table-responsive">
								<table class="table" id="designers_list" border="1">
									<thead>
										<tr>
										    <th><?=@$name_he_label?></th>
											<th><?=@$name_label?></th>		
											<th><?=@$nickname_label?></th>
											<th><?=@$domain_label?></th>
											<th><?=@$phone_label?></th>
											<th><?=@$cellular_label?></th>
											<th><?=@$email_office_label?></th>				
											<th width="30px;">&nbsp;</th>
											<th width="30px;">&nbsp;</th>
										</tr>										
									</thead>
									
									<tfoot>
										<tr>
											<th><?=@$name_he_label?></th>
											<th><?=@$name_label?></th>
											<th><?=@$nickname_label?></th>
											<th><?=@$domain_label?></th>
											<th><?=@$phone_label?></th>
											<th><?=@$cellular_label?></th>
											<th><?=@$email_office_label?></th>									
											<th width="30px;">&nbsp;</th>
											<th width="30px;">&nbsp;</th>
										</tr>										
									</tfoot>
							
									<?php
									foreach($designers as $item) {
										$query = $mysqli->prepare("SELECT * FROM dne_sup_field_of_work WHERE id = ?");
										$query->bind_param("i",$item->id_field_of_work);
										$query->execute();
										$query->store_result();
										$sup_field_of_work = fetch_unique($query);
										?>
										<tr>
										    <td><?=@$item->name_he?></td>
											<td><?=@$item->name?></td>
											<td><?=@$item->nickname?></td>
											<td><?=@$sup_field_of_work->name_he?></td>
											<td><?=@$item->phone?></td>
											<td><?=@$item->mobile?></td>
											<td><?=@$item->email_office?></td>
											<td><img src="images/edit-button.svg" class="paddingTop10 cursor-pointer" title="<?=@$edite_label?>" onclick="location.href='add_supplier.php?type_sup=D&id=<?=@$item->id?>'" /></td>
											<td><img src="images/delete.svg" class="paddingTop10 cursor-pointer" title="<?=@$remove_label?>" onclick="return removeSupplier(<?=@$item->id?>);" /></td>										
										</tr>
										<?php
									}
									?>
								</table>		
							</div>	
						</div>
					</div>
				<?php } ?>
		    </div>
		</form> 
	</body>
</html>

<script>
$(document).ready(function () {
	$('#suppliers_list').dataTable( {
        "aLengthMenu": [25, 50]
    });
	
	jQuery('#suppliers_list').dataTable().columnFilter({
		aoColumns: [ 
		   {type: "text"},		
		   {type: "text"},
		   {type: "text"},
		   {type: "text"},
		   {type: "text"},
	       {type: "text"},
		   {type: "text"},
			null,
			null
		]
	});
	
	$('#designers_list').dataTable( {
        "aLengthMenu": [25, 50]
    });
	
	jQuery('#designers_list').dataTable().columnFilter({
		aoColumns: [ 
		   {type: "text"},		
		   {type: "text"},
		   {type: "text"},
		   {type: "text"},
		   {type: "text"},
	       {type: "text"},
		   {type: "text"},
			null,
			null
		]
	});
});

function removeSupplier(s_id) {
	if(confirm("<?=@$alert_confirme_delete?>")) {
        let form_data = new FormData();	
		form_data.append('id_supplier',s_id);			
		$.ajax({
			type: 'POST',
			url: 'supplier_delete.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,
			success: function(data){
				if(data == 'notallowedtoremove')
					alert('<?=@$alert_not_allowed_delete?>')
				else
					location.href = 'suppliers.php?type='+$('#type').val();
			},
		});
    }
    return false;
}
</script>

<style>
.btn {
	background-color: #218FD6;
	color: white;
	margin-top: 10px;
}

.btn:hover {
   background-color: #3370d6;
   color: white;
}
</style>