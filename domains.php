<?php
include 'include/header.php';
include 'functions/functions.php';

$query = $mysqli->prepare("SELECT * FROM dne_logos LIMIT 1");
$query->execute();
$query->store_result();
$logo = fetch_unique($query);

$lang_screen = @$_GET['lang_screen'];

$sup_type = 'S';
$des_type = 'D';

$query = $mysqli->prepare("SELECT * FROM dne_sup_field_of_work WHERE sup_type = ? ORDER BY name");
$query->bind_param("s",$sup_type);
$query->execute(); 
$query->store_result();
$sup_fow_num_rows = $query->num_rows;
$sup_domains = fetch($query);

$query = $mysqli->prepare("SELECT * FROM dne_sup_field_of_work WHERE sup_type = ? ORDER BY name");
$query->bind_param("s",$des_type);
$query->execute(); 
$query->store_result();
$des_fow_num_rows = $query->num_rows;
$des_domains = fetch($query);
?>

        <form method="post" action="" class="form-inline">
		    <input type="hidden" id="lang_screen" name="lang_screen" value="<?=@$lang_screen?>" />
		
		    <div class="container">
			    <div class="row alignCenter marginTop25">
					<div class="col-12">
						<a href="projects.php"><img src="images/<?=@$logo->logo?>" width="170" height="170" /></a>
					</div>
			    </div>
				
				<div class="row marginTop5 title">	
					<div id="title" class="col-12"></div>					
			    </div>
			
				<div class="row marginTop10 alignCenter">
					<div class="col-12">
						<a id="btn_add_domain" class="btn marginTop20" onclick="location.href='add_domain.php?id=0'"></a>
					</div>
				</div>
				
				<div class="row marginTop20 alignCenter">
					<div class="col-12">
					   <select id="lang" name="lang" class="width100 height35 border-color-initial">
						   <option value="HE" <?php if($lang_screen == 'HE') echo 'selected'?>>עברית</option>
						   <option value="EN" <?php if($lang_screen == 'EN') echo 'selected'?>>English</option>					   
					   </select>   
					</div>
			   </div>				

				<div class="row marginTop10">
					<?php if($sup_fow_num_rows > 0) { ?>
						<div class="col-12">
							<div class="row title fontSize22">	
								<div class="col-12">
					                <strong id="suppliers_title"></strong>
								</div>					
							</div>
						
							<div class="row fontSize14 marginTop20">
								<div class="col-12 table-responsive mx-2">
									<table id="suppliers_domains_list" cellpadding="2" cellspacing="2" border="1">										
										<thead>
										    <tr class="height35 bgColorSilver">
												<th class="th_name alignCenter"></th>
												<th class="th_nickname alignCenter"></th>
												<th class="th_color alignCenter"></th>
												<th class="th_bgcolor alignCenter"></th>
												<th class="th_related_suppliers alignCenter"></th>
												<th width="30px;">&nbsp;</th>
												<th width="30px;">&nbsp;</th>
										    </tr>		
										</thead>							
                                       
									    <tfoot>
										   <tr>
												<th class="th_name"></th>
												<th class="th_nickname"></th>
												<th class="th_color"></th>
												<th class="th_bgcolor"></th>
												<th class="th_related_suppliers"></th>
												<th width="30px;">&nbsp;</th>
												<th width="30px;">&nbsp;</th>
										    </tr>		
										</tfoot>
									
										<?php
										foreach($sup_domains as $item) {
											$id_domain = @$item->id;
											
											$query = $mysqli->prepare("SELECT s.name AS name,s.name_he AS name_he 
																	  FROM dne_suppliers s
																	  LEFT JOIN dne_sup_field_of_work sfow ON s.id_field_of_work = sfow.id
																	  WHERE sfow.id = ?");
											$query->bind_param("i",$item->id);
											$query->execute(); 
											$query->store_result();
											$suppliers_num_rows = $query->num_rows;
											$suppliers = fetch($query);
											?>
											<tr>					  		
												<?php if($lang_screen == 'EN') { ?>
												    <td class="alignLeft paddingLeft5"><?=@$item->name?></td>
												    <td class="alignLeft paddingLeft5"><?=@$item->nickname?></td>   
												<?php } 
												else if($lang_screen == 'HE'){ ?>
												    <td class="alignRight paddingRight5"><?=@$item->name_he?></td>
													<td class="alignRight paddingRight5"><?=@$item->nickname_he?></td>
												<?php } ?>
												<td class="alignCenter"><input type="color" class="width100" disabled="true" value="<?=@$item->color?>" /></td>
												<td class="alignCenter"><input type="color" class="width100" disabled="true" value="<?=@$item->bgcolor?>" /></td>
												<td <?php if($lang_screen == 'EN') echo "class='alignLeft paddingLeft5'";else if($lang_screen == 'HE') echo "class='alignRight paddingRight5';"?>>
												   <?php 
												   if($suppliers_num_rows > 0) {
													   foreach($suppliers as $item) {
														  if($lang_screen == 'EN') echo @$item->name."<br/>";
														  else echo @$item->name_he."<br/>";
														}
												   }
												   ?>
												</td>
												<td class="alignCenter"><img src="images/edit-button.svg" class="padding10 cursor-pointer" title="עדכן" onclick="location.href='add_domain.php?id=<?=@$id_domain?>'" /></td>										
												<td class="alignCenter"><img src="images/delete.svg" class="cursor-pointer" title="מחוק" onclick="return removeDomain(<?=@$id_domain?>);" /></td>
											</tr>
											<?php
										}
										?>
									</table>	
								</div>						
							</div>	
						</div>
					<?php } 			
					if($des_fow_num_rows > 0) { ?>
						<div class="col-12">
							<div class="row title fontSize22">	
								<div class="col-12">
									<strong id="designers_title"></strong>
								</div>					
							</div>
						
							<div class="row fontSize14 marginTop20">
								<div class="col-12 table-responsive mx-2">
									<table id="designers_domains_list" cellpadding="2" cellspacing="2" border="1">
										<thead>
										    <tr class="height35 bgColorSilver">
												<th class="th_name alignCenter" width="120px;"></th>
												<th class="th_nickname alignCenter" width="120px;"></th>
												<th class="th_color alignCenter" width="30px;"></th>
												<th class="th_bgcolor alignCenter" width="30px;"></th>
												<th class="th_related_suppliers alignCenter" width="150px;"></th>
												<th width="30px;">&nbsp;</th>
												<th width="30px;">&nbsp;</th>
										    </tr>			
										</thead>

                                        <tfoot>
										    <tr>
												<th class="th_name"></th>
												<th class="th_nickname"></th>
												<th class="th_color"></th>
												<th class="th_bgcolor"></th>
												<th class="th_related_suppliers"></th>
												<th width="30px;">&nbsp;</th>
												<th width="30px;">&nbsp;</th>
										    </tr>			
										</tfoot>										
									
										<?php
										foreach($des_domains as $item) {
											$id_domain = @$item->id;
											
											$query = $mysqli->prepare("SELECT s.name AS name,s.name_he AS name_he 
																	  FROM dne_suppliers s
																	  LEFT JOIN dne_sup_field_of_work sfow ON s.id_field_of_work = sfow.id
																	  WHERE sfow.id = ?");
											$query->bind_param("i",$item->id);
											$query->execute(); 
											$query->store_result();
											$designers_num_rows = $query->num_rows;
											$designers = fetch($query);
											?>
											<tr>					  		
												<?php if($lang_screen == 'EN') { ?>
												    <td class="alignLeft paddingLeft5"><?=@$item->name?></td>
												    <td class="alignLeft paddingLeft5"><?=@$item->nickname?></td>   
												<?php } 
												else if($lang_screen == 'HE'){ ?>
												    <td class="alignRight paddingRight5"><?=@$item->name_he?></td>
													<td class="alignRight paddingRight5"><?=@$item->nickname_he?></td>
												<?php } ?>
												<td class="alignCenter"><input type="color" class="width100" disabled="true" value="<?=@$item->color?>" /></td>
												<td class="alignCenter"><input type="color" class="width100" disabled="true" value="<?=@$item->bgcolor?>" /></td>
												<td <?php if($lang_screen == 'EN') echo "class='alignLeft paddingLeft5'";else if($lang_screen == 'HE') echo "class='alignRight paddingRight5';"?>>
												   <?php 
												   if($designers_num_rows > 0) {
													   foreach($designers as $item) {
														  if($lang_screen == 'EN') echo @$item->name."<br/>";
														  else echo @$item->name_he."<br/>";
														}
												   }
												   ?>
												</td>
												<td class="alignCenter"><img src="images/edit-button.svg" class="padding10 cursor-pointer" title="עדכן" onclick="location.href='add_domain.php?id=<?=@$id_domain?>'" /></td>		
												<td class="alignCenter"><img src="images/delete.svg" class="cursor-pointer" title="מחוק" onclick="return removeDomain(<?=@$id_domain?>);" /></td>											
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
			</div>
		</form> 
	</body>
</html>

<script>
$(document).ready(function () {
   setLabels();
   
   function setLabels() {
	   $('#lang').css({"padding-left":"10px"});
	   
	   if($('#lang_screen').val() == 'EN') {
		 $('#title').html('<span style="font-size:26px;"><strong>Suppliers activity domains</strong></span>'); 
         $('#suppliers_title').html('Suppliers &amp; Contractors');
		 $('#suppliers_domains_list').css({"direction":"ltr"});	 
		 $('#btn_add_domain').html("<i class='fa fa-plus'></i>&nbsp;Activity Domain");
		 $('#designers_title').html('Planners &amp; Consultants');
		 $('#designers_domains_list').css({"direction":"ltr"});
		 $('.th_name').html('Name');	 
         $('.th_nickname').html('Nickname');
		 $('.th_color').html('Color');
		 $('.th_bgcolor').html('BgColor');
		 $('.th_related_suppliers').html('Related <br/> Suppliers');
	   }
	   else if($('#lang_screen').val() == 'HE') {
		   $('#title').html('<span style="font-size:26px;"><strong>תחומי פעילות ספקים</strong></span>');
		   $('#suppliers_title').html('ספקים וקבלנים'); 
		   $('#suppliers_domains_list').css({"direction":"rtl"});	   
		   $('#btn_add_domain').html("תחום פעילות&nbsp;<i class='fa fa-plus'></i>");
		   $('#designers_title').html('מתכננים ויועצים');
		   $('#designers_domains_list').css({"direction":"rtl"});
		   $('.th_name').html('שם');		 
           $('.th_nickname').html('כינוי');	
           $('.th_color').html('גוון');		
           $('.th_bgcolor').html('גוון רקע');
           $('.th_related_suppliers').html('ספקים <br/> רשומים');		   
	   }
   }
   
   $("#lang").change(function(){
      location.href = 'domains.php?lang_screen='+$(this).val();
       setLabels();
   });
   
   $('#suppliers_domains_list').dataTable( {
        "aLengthMenu": [25, 50]
   });
	
   jQuery('#suppliers_domains_list').dataTable().columnFilter({
		aoColumns: [ 
		   {type: "text"},		
		   {type: "text"},
		   {type: "text"},
		   {type: "text"},
		   {type: "text"},
			null,
			null
		]
	});
	
	$('#designers_domains_list').dataTable( {
        "aLengthMenu": [25, 50]
    });
	
	jQuery('#designers_domains_list').dataTable().columnFilter({
		aoColumns: [ 
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

function removeDomain(id) {
	if(confirm("Are you sure to remove this domain?")) {
        let form_data = new FormData();
		form_data.append('id',id);			
		$.ajax({
			type: 'POST',
			url: 'domain_delete.php',
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
	background-color: #218FD6;
	color: white;
	margin-top: 10px;
}

.btn:hover {
   background-color: #3370d6;
   color: white;
}
</style>