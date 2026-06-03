<?php
include 'include/header.php';
include 'functions/functions.php';

$id = @$_GET['id'];
$project_id = @$_GET['project_id'];
$from = @$_GET['from'];
$for = @$_GET['for'];
$ps_id = @$_GET['ps_id'];

$sup_type = 'S';
$des_type = 'D';
$entr_type = 'E';
$manager_type = 'M';
   
$query = $mysqli->prepare("SELECT * FROM dne_responsibles WHERE id = ?");
$query->bind_param("i",$id);
$query->execute();
$query->store_result();
$responsible = fetch_unique($query);

if(@$ps_id > 0){
	$query = $mysqli->prepare("SELECT ps.id AS id,s.name_he AS s_name_he,s.type AS s_type
                               FROM dne_projects_suppliers ps
						       LEFT JOIN dne_projects p ON ps.id_project = p.id
						       LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
						       WHERE ps.id = ?");
	$query->bind_param("i",$ps_id);
	$query->execute();
	$query->store_result();
	$ps = fetch_unique($query);
}

if($from == 'addProject'){
	if($for == 'admingroup'){
		$title = 'הוסף אחראי לצוות ניהול';
		$title_card = 'בחירת צוות ניהול הפרוייקט';
	}
	else if($for == 'entrepreneurgroup'){
		$title = 'הוסף אחרא לצוות יזם';
		$title_card = 'בחירת צוות יזם הפרוייקט';
	}
	else if($for == 'projectgroup'){
		$title = 'הוסף אחראי לצוות הפרוייקט';
		$title_card = 'בחירת צוות הפרוייקט';
		
		if(@$ps_id > 0){
			$title = 'הוסף אחראי ל'.$ps->s_name_he;
			$title_card = 'בחירת צוות הפרוייקט';
		}
	}
}
else {
    $title = 'הוסף אחראי';
	$title_card = 'הוספה/עדכון אחראי';
}

if(@$id > 0)
	$title = @$responsible->name;	

$display_users = 'display-none';
if(@$responsible->role == 'project_manager' || @$responsible->role == 'inspector') 
    $display_users = 'display-block';

$query = $mysqli->prepare("SELECT * FROM dne_projects WHERE id = ?");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$project = fetch_unique($query);

$query = $mysqli->prepare("SELECT ps.id AS id,s.name_he AS name_he
                          FROM dne_projects_suppliers ps
						  LEFT JOIN dne_projects p ON ps.id_project = p.id
						  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
						  WHERE ps.id_project = ? AND s.type = ?
                          ORDER BY name_he ASC");
$query->bind_param("is",$project_id,$sup_type);
$query->execute();
$query->store_result();
$suppliers = fetch($query);

$query = $mysqli->prepare("SELECT ps.id AS id,s.name_he AS name_he
                          FROM dne_projects_suppliers ps
						  LEFT JOIN dne_projects p ON ps.id_project = p.id
						  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
						  WHERE ps.id_project = ? AND s.type = ?
                          ORDER BY name_he ASC");
$query->bind_param("is",$project_id,$des_type);
$query->execute();
$query->store_result();
$designers = fetch($query);

$query = $mysqli->prepare("SELECT ps.id AS id,s.name_he AS name_he
                          FROM dne_projects_suppliers ps
						  LEFT JOIN dne_projects p ON ps.id_project = p.id
						  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
						  WHERE ps.id_project = ? AND s.type = ? LIMIT 1");
$query->bind_param("is",$project_id,$entr_type);
$query->execute();
$query->store_result();
$entrepreneur = fetch_unique($query);
$id_entr = @$entrepreneur->id;

$query = $mysqli->prepare("SELECT ps.id AS id,s.name_he AS name_he
                          FROM dne_projects_suppliers ps
						  LEFT JOIN dne_projects p ON ps.id_project = p.id 
						  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
						  WHERE ps.id_project = ? AND s.type = ?");
$query->bind_param("is",$project_id,$manager_type);
$query->execute();
$query->store_result();
$manager = fetch_unique($query);
$id_manager = @$manager->id;

$is_user_active = 1;

$query = $mysqli->prepare("SELECT id,nickname FROM dne_users WHERE is_user_active = ? ORDER BY nickname");
$query->bind_param("i",$is_user_active);
$query->execute();
$query->store_result();
$users = fetch($query);

include 'menu_tasks.php';
?>

		<form method="post" action="" enctype="multipart/form-data" class="form-inline">
			<input type="hidden" id="id" value="<?=@$id?>" />
			<input type="hidden" id="project_id" value="<?=@$project_id?>" />
			<input type="hidden" id="ps_id" value="<?=@$ps_id?>" />
			<input type="hidden" id="from" value="<?=@$from?>" />
			<input type="hidden" id="for" value="<?=@$for?>" />
            <input type="hidden" id="id_entr" value="<?=@$id_entr?>" />
            <input type="hidden" id="id_manager" value="<?=@$id_manager?>" />				
            <input type="hidden" id="color_entr" value="<?=@$color_entr?>" />
            <input type="hidden" id="bgcolor_entr" value="<?=@$bgcolor_entr?>" />			

		    <div class="container">
			    <div class="row alignCenter marginTop25">
					<div class="col-12">
						<a href="projects.php"><img src="images/davidnahmias_logo.png" width="170px" height="170px" /></a>
					</div>
			    </div>
				
				<div class="row marginTop15 title dir-rtl">	
					<div class="col-12">
						<a id="a_project_title" class="text-decoration-none color-1A5276" href="meetings.php?project_id=<?=@$project_id?>" />
						    <strong class="fontSize33 line-height-1em"><?=@$project->nickname?></strong>
							<div class="fontSize26 font-family-david">פרוייקט <?=@$project->name_he?></div>
					    </a>		
					</div>				
			    </div>
				
				<div class="row marginTop15">
					<div class="col-1"></div>
					<div class="col-10 card bg-f2f6f9 border-frame borderRadius15 padding0">		
			            <div class="card-header bgColor-1a5276 colorWhite alignCenter fontSize20" style="border-top-left-radius:15px;border-top-right-radius:15px;"><strong><i class="fa fa-user"></i><span class="marginRight10 marginLeft10"><?=@$title_card?></span><i class="fa fa-user"></i></strong></div>	   
				        <div class="card-body">	   
							<div class="row title dir-rtl">	
								<div class="col-12">
									<strong class="fontSize20"><?=@$title?></strong>
								</div>
							</div>

							<?php if(@$for == 'admingroup'){ ?>
									<div class="row marginTop5 alignCenter dir-rtl">
										<div class="col-12">
											<strong>חברת ניהול:</strong> <?=@$manager->name_he?>
										</div>
									</div>
							<?php }
							else if(@$for == 'entrepreneurgroup'){ ?>
									<div class="row marginTop5 alignCenter dir-rtl">
										<div class="col-12">
											<strong>חברת יזם:</strong> <?=@$entrepreneur->name_he?>
										</div>
									</div>
							<?php }
							else if(@$for == 'projectgroup'){
								if(@$ps_id > 0){ ?>
									<div class="row marginTop5 alignCenter dir-rtl">
										<div class="col-12">
											 <?=@$ps->s_name_he?>
										</div>
									</div>
							<?php }}
							else { ?>
								<div class="row marginTop5 alignCenter dir-rtl">
									<div class="col-12">
										<strong>משרד/חברה</strong>
										<br/>
										<select id="suppliers" class="marginTop5 width200 height30" onchange="functionsOnForm()">
											<option value="0">--- משרד/חברה ---</option>
											<optgroup label="חברה">
											<?php foreach($suppliers as $item){ ?>
												<option value="<?=@$item->id?>" <?php if(($item->id == @$responsible->id_projects_suppliers) || ($item->id == $ps_id)) echo 'selected';?>><?=@$item->name_he?></option>
											<?php } ?>
											</optgroup>
											<optgroup label="משרד">
											<?php foreach($designers as $item){ ?>
												<option value="<?=@$item->id?>" <?php if(($item->id == @$responsible->id_projects_suppliers) || ($item->id == $ps_id)) echo 'selected';?>><?=@$item->name_he?></option>
											<?php } ?>
											</optgroup>
											<optgroup label="ניהול">										
												<option value="<?=@$id_manager?>" <?php if(($id_manager == @$responsible->id_projects_suppliers) || ($id_manager == $ps_id)) echo 'selected';?>><?=@$manager->name_he?></option>
											</optgroup>
											<optgroup label="יזם">										
												<option value="<?=@$id_entr?>" <?php if(($id_entr == @$responsible->id_projects_suppliers) || ($id_entr == $ps_id)) echo 'selected';?>><?=@$entrepreneur->name_he?></option>
											</optgroup>						
										</select>
									</div>
								</div>
							<?php } ?>
							
							<div class="row marginTop5 alignCenter dir-rtl">
								<div class="col-12">
									<strong>תפקיד</strong>
									<br/>
									<select id="roles" class="marginTop5 width200 height30">
										<option value="0">---תפקיד---</option>
										<?php if($for == 'admingroup' ||($from != 'addProject')){ ?>
											<option value="project_manager" <?php if(@$responsible->role == 'project_manager') echo 'selected'?>>מנהל פרויקט</option>
											<option value="inspector" <?php if(@$responsible->role == 'inspector') echo "selected"?>>מפקח</option>
										<?php }
                                         if($for == 'entrepreneurgroup' ||($from != 'addProject')){ ?>
											<option value="entrepreneur" <?php if(@$responsible->role == 'entrepreneur') echo 'selected'?>>יזם</option>
											<option value="entrepreneur_team" <?php if(@$responsible->role == 'entrepreneur_team') echo 'selected'?>>צוות יזם</option>
										<?php } 										
										if(($for != 'admingroup' && $for != 'entrepreneurgroup') || ($from != 'addProject')){ ?>
											<option value="programmer" <?php if(@$responsible->role == 'programmer' || @$ps->s_type == 'D') echo 'selected'?>>מתכנן</option>
											<option value="supplier_contractor" <?php if(@$responsible->role == 'supplier_contractor' || @$ps->s_type == 'S') echo 'selected'?>>ספק/קבלן</option>	
										<?php } ?>
									</select>
								</div>
							</div>
						
							<div id="div_users" class="row marginTop5 alignCenter <?=@$display_users?> dir-rtl">
								<div class="col-12">
									<strong>משתמש</strong>
									<br/>
									<select id="users" class="marginTop5 width200 height30">
										<option value="0">---משתמש---</option>
										<?php foreach ($users as $u) { ?>
											<option value="<?=@$u->id?>" <?php if($u->id == @$responsible->id_user) echo 'selected'?>><?=@$u->nickname?></option>
										<?php } ?>
									</select>
								</div>
							</div>
						
							<div class="row marginTop5 alignCenter dir-rtl">
								<div class="col-12">	
									<strong>שם</strong>
									<br/>	
									<input type="text" class="paddingRight10 marginTop5" name="name" id="name" placeholder="*שם" value="<?=@$responsible->name?>" />	
								</div>
							</div>
							
							<div class="row marginTop5 alignCenter dir-rtl">
								<div class="col-12">	
									<strong>דוא''ל</strong>
									<br/>	
									<input type="email" class="paddingRight10 marginTop5" name="email" id="email" placeholder="דוא''ל" value="<?=@$responsible->email?>" />	
								</div>
							</div>
							
							<div class="row marginTop5 alignCenter dir-rtl">
								<div class="col-12">	
									<strong>טלפון</strong>
									<br/>	
									<input type="text" class="paddingRight10 marginTop5" name="phone" id="phone" placeholder="טלפון" value="<?=@$responsible->phone?>" />	
								</div>
							</div>
						
							<?php if(@$from != 'project_data') { ?>
								<div class="row marginTop5 alignCenter dir-rtl">
									<div class="col-12">
										<strong>צבע גופן</strong>
										<br/>						
										<input type="color" class="marginTop5 width200" name="color" id="color" placeholder="צבע גופן" value="<?=@$responsible->color?>" />
									</div>
								</div>

								<div class="row marginTop5 alignCenter dir-rtl">
									<div class="col-12">
										<strong>צבע רקע</strong>
										<br/>					
										<input type="color" class="marginTop5 width200" name="bgcolor" id="bgcolor" placeholder="צבע רקע" value="<?php if($id == 0) echo '#ffffff';else echo @$responsible->bgcolor;?>" />
									</div>
								</div>
							<?php } ?>						
								
							<div class="row marginTop15 alignCenter dir-rtl">
								<div class="col-12">
									<div id="div_message_alert_down"></div>					
									<?php if(@$from == "addProject"){
										if(@$for == 'admingroup'){
											$url = 'add_responsible.php?id=0&project_id='.@$project_id.'&from=addProject&for=entrepreneurgroup';
											$btn_save = 'המשך בחירת צוות ניהול';
											$btn_next_step_val = 'הבא : בחירת צוות יזם';
										}

										if(@$for == 'entrepreneurgroup'){
											$url = 'add_sup_to_proj.php?id='.@$project_id.'&from=addResponsible';
											$btn_save = 'המשך בחירת צוות יזם';
											$btn_next_step_val = 'עבור לצוות הפרוייקט';
										}

										else if(@$for == 'projectgroup'){										
											$url = 'add_sup_to_proj.php?id='.@$project_id.'&from=addResponsible';
											$btn_save = 'המשך נחירת צוות פרוייקט';
											$btn_next_step_val = "<i class='fa fa-plus'></i> ספק לצוות הפרוייקט";
										}
									}
                                    else {
										$btn_save = 'שמור';
									}										
									?>
									
									<?php if($from == 'addProject'){ ?>
										<div class="row text-center justify-content-center">
											<div class="col-12 col-md-4 mb-2"></div>
											<div class="col-12 col-md-4 d-flex justify-content-center">
												<input type="button"
													   id="save_btn"
													   name="save_btn"
													   class="btn fontSize14 bgColorBlue colorWhite"
													   value="<?=@$btn_save?>" />		
											</div> 
											<div class="col-12 col-md-4 alignLeft d-flex justify-content-center">							
												<a class="btn marginRight10 fontSize14 bgColorBlue colorWhite"
												   onclick="location.href='<?=@$url?>'">
													<?=@$btn_next_step_val?>
												</a>			

												<?php if(@$ps_id != ''){ ?>
													<a class="btn marginRight10 fontSize14 bgColorBlue colorWhite"
													   onclick="location.href='add_chapter.php?id=0&project_id=<?=@$project_id?>&from=addProject'">
														הבא : הגדרות
													</a>	
												<?php } ?>
											</div>
										</div>
									<?php }
                                    else { ?>
										<input type="button"
													   id="save_btn"
													   name="save_btn"
													   class="btn fontSize16 bgColorBlue colorWhite mb-2"
													   value="<?=@$btn_save?>" />
										<input type="button"
											   id="cancel_btn"
											   class="btn marginRight10 fontSize16 bgColorBlack colorWhite mb-2"
											   value="ביטול" />
								    <?php } ?>
								</div>
							</div>
						</div>
					</div>
					<div class="col-1"></div>
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
$(document).ready(function (){
	let form_data = new FormData();
	if($('#for').val() == 'admingroup')
		form_data.append('id_projects_suppliers',$('#id_manager').val());
	if($('#for').val() == 'entrepreneurgroup')
		form_data.append('id_projects_suppliers',$('#id_entr').val());
    if($('#for').val() == 'projectgroup')
		form_data.append('id_projects_suppliers',$('#ps_id').val());
	
	$.ajax({
		type: 'POST',
		url: 'fill_data_sup_inputs.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,
		success: function (data){
			data = JSON.parse(data);
			$('#name').val(data.name_he);
			$('#email').val(data.email);
			$('#phone').val(data.phone);
		},
		error: function (){
			console.error('Erreur AJAX');
		}
	});
});

$('#suppliers').change(function(){
	let form_data = new FormData();	
	form_data.append('id_projects_suppliers',$(this).val());
	
	$.ajax({
		type: 'POST',
		url: 'fill_data_sup_inputs.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,			
		success: function(data){
		    data = JSON.parse(data);
			$('#name').val(data.name_he);
			$('#email').val(data.email);
			$('#phone').val(data.phone);
		},
	});						
});

$('#roles').change(function(){
    if($(this).val() == 'project_manager' || $(this).val() == 'inspector')
       $('#div_users').css('display','block');
    else 
	   $('#div_users').css('display','none');
});

function functionsOnForm(){	
	let form_data = new FormData();	
	form_data.append('id_projects_suppliers',$('#suppliers').val());
	
	$.ajax({
		type: 'POST',
		url: 'getSupplierType.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,			
		success: function(data){     			
		   if(data == 'S') 
		      $('#roles').val('supplier_contractor')
           
		   if(data == 'D') 
		      $('#roles').val('programmer')  		  
		},
	});				
	
	$.ajax({
		type: 'POST',
		url: 'fill_responsible_color_data.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,			
		success: function(data){     			
			$('#color').val(data.split(',')[0]);
            $('#bgcolor').val(data.split(',')[1]);			
		},
	});				
}

$('#save_btn').click(function (e){
    let form_data = new FormData();
    form_data.append('for', $('#for').val());

    if ($('#from').val() == 'project_data'){
        form_data.append('role', 'entrepreneur');
        form_data.append('id_projects_suppliers', $('#id_entr').val());
        form_data.append('color', $('#color_entr').val());
        form_data.append('bgcolor', $('#bgcolor_entr').val());
    } 
	else {
        let id_projects_suppliers = $('#suppliers').val();

        if ($('#for').val() == 'admingroup'){
            id_projects_suppliers = $('#id_manager').val();
        } else if ($('#for').val() == 'entrepreneurgroup'){
            id_projects_suppliers = $('#id_entr').val();
        } else if ($('#for').val() == 'projectgroup'){
            id_projects_suppliers = $('#ps_id').val();
        }

        form_data.append('role', $('#roles').val());
        form_data.append('id_projects_suppliers', id_projects_suppliers);
        form_data.append('color', $('#color').val());
        form_data.append('bgcolor', $('#bgcolor').val());
    }

    form_data.append('id', $('#id').val());
    form_data.append('id_project', $('#project_id').val());
    form_data.append('id_user', $('#users').val());
    form_data.append('name', $('#name').val());
    form_data.append('email', $('#email').val());
    form_data.append('phone', $('#phone').val());

    $.ajax({
        type: 'POST',
        url: 'responsible_insert.php',
        data: form_data,
        cache: false,
        processData: false,
        contentType: false,
        beforeSend: function (){
            $("#progress-popup").show();
            let progress = 0;
            let interval = setInterval(function (){
                if (progress < 90){
                    progress += 10;
                    $("#progress-bar").css("width", progress + "%");
                } else {
                    clearInterval(interval);
                }
            }, 200);
            $("#progress-popup").data("interval", interval);
        },
        success: function (data){
            if (data == 'empty'){
                if ($('#name').val().length == 0){
                    $('#name').css('border-color', 'red');
                } else {
                    $('#name').css('border-color', 'initial');
                }
                $('#div_message_alert_down').html("<span style='color:red;font-size:13px;'>נא למלה את כל השדות החובות</span>");
            } else if (data == 'firstnotinspector'){
                $('#div_message_alert_down').html("<span style='color:red;font-size:13px;'>ראשית לבחור מנהל פרוייקט</span>");
				alert('ראשית לבחור מנהל פרוייקט');
            } else if (data == 'exists'){
                $('#div_message_alert_down').html("<span style='color:red;font-size:13px;'>אחראי זה כבר קיים בפרוייקט זה</span>");
            } else {      
                if ($('#from').val() == 'project_data'){
                    let url = 'add_project.php?id=' + $('#project_id').val();
                    window.location.href = url;
                } else if ($('#from').val() == 'addProject' &&
                           ($('#for').val() == 'admingroup' ||
                            $('#for').val() == 'entrepreneurgroup' ||
                            ($('#for').val() == 'projectgroup' && $('#ps_id').val() == ''))){
                    window.location.reload();
                } else if ($('#for').val() == 'projectgroup' && $('#ps_id').val() != '') {
                    let url = 'add_responsible.php?id=0&project_id=' + $('#project_id').val() +
                              '&from=addProject&for=projectgroup&ps_id=' + $('#ps_id').val();
                    window.location.href = url;
                } else {
                    let url = 'responsibles.php?project_id=' + $('#project_id').val();
                    window.location.href = url;
                }
            }
        },    
    });
});											       			   

$('#cancel_btn').click(function(){
    location.href = "responsibles.php?project_id="+$('#project_id').val();	
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
</style>