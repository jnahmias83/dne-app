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
	$query = $mysqli->prepare("SELECT ps.id AS id,s.name_he AS s_name_he,s.type AS s_type,IF(s.nickname<>'',s.nickname,s.nickname_he) AS nickname_he,sfow.name_he AS sfow_name_he
                               FROM dne_projects_suppliers ps
						       LEFT JOIN dne_projects p ON ps.id_project = p.id
						       LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
						       LEFT JOIN dne_sup_field_of_work sfow ON s.id_field_of_work = sfow.id
						       WHERE ps.id = ?");
	$query->bind_param("i",$ps_id);
	$query->execute();
	$query->store_result();
	$ps = fetch_unique($query);
}

if($from == 'addProject' || $from == 'addSupToProj'){
	if($for == 'admingroup'){
		$title = 'הוסף אחראי לצוות ניהול';
		$title_card = 'בחירת צוות ניהול הפרוייקט';
	}
	else if($for == 'entrepreneurgroup'){
		$title = 'הוסף אחרא לצוות יזם';
		$title_card = 'בחירת צוות יזם הפרוייקט';
	}
	else if($for == 'projectgroup'){
		if($from == 'addSupToProj'){
			$title_card = 'הוסף/עדכון אחראי';
			$title = @$ps->s_name_he;
		}
		else {
			$title = 'הוסף אחראי לצוות הפרוייקט';
			$title_card = 'בחירת צוות הפרוייקט';

			if(@$ps_id > 0){
				$title = 'הוסף אחראי ל'.$ps->s_name_he;
				$title_card = 'בחירת צוות הפרוייקט';
			}
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

$query = $mysqli->prepare("SELECT default_bgcolor FROM dne_inputs_colors LIMIT 1");
$query->execute();
$query->store_result();
$bg_color_inputs = fetch_unique($query);
$default_bgcolor = @$bg_color_inputs->default_bgcolor;

$query = $mysqli->prepare("SELECT ps.id AS id,s.name_he AS name_he,IF(s.nickname<>'',s.nickname,s.nickname_he) AS nickname_he
                          FROM dne_projects_suppliers ps
						  LEFT JOIN dne_projects p ON ps.id_project = p.id
						  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
						  WHERE ps.id_project = ? AND s.type = ?
                          ORDER BY name_he ASC");
$query->bind_param("is",$project_id,$sup_type);
$query->execute();
$query->store_result();
$suppliers = fetch($query);

$query = $mysqli->prepare("SELECT ps.id AS id,s.name_he AS name_he,IF(s.nickname<>'',s.nickname,s.nickname_he) AS nickname_he
                          FROM dne_projects_suppliers ps
						  LEFT JOIN dne_projects p ON ps.id_project = p.id
						  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
						  WHERE ps.id_project = ? AND s.type = ?
                          ORDER BY name_he ASC");
$query->bind_param("is",$project_id,$des_type);
$query->execute();
$query->store_result();
$designers = fetch($query);

$query = $mysqli->prepare("SELECT ps.id AS id,s.name_he AS name_he,IF(s.nickname<>'',s.nickname,s.nickname_he) AS nickname_he
                          FROM dne_projects_suppliers ps
						  LEFT JOIN dne_projects p ON ps.id_project = p.id
						  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
						  WHERE ps.id_project = ? AND s.type = ? LIMIT 1");
$query->bind_param("is",$project_id,$entr_type);
$query->execute();
$query->store_result();
$entrepreneur = fetch_unique($query);
$id_entr = @$entrepreneur->id;

$query = $mysqli->prepare("SELECT ps.id AS id,s.name_he AS name_he,IF(s.nickname<>'',s.nickname,s.nickname_he) AS nickname_he
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
            <input type="hidden" id="project_nickname" value="<?=htmlspecialchars(@$project->nickname)?>" />
            <input type="hidden" id="default_bgcolor" value="<?=@$default_bgcolor?>" />			

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
				
				<div class="row marginTop15" id="add_responsible_form_row">
					<div class="col-3"></div>
					<div class="col-6 card bg-f2f6f9 border-frame borderRadius15 padding0">
			            <div class="card-header bgColor-1a5276 colorWhite alignCenter fontSize20" style="border-top-left-radius:15px;border-top-right-radius:15px;"><strong><i class="fa fa-user"></i><span class="marginRight10 marginLeft10"><?=@$title_card?></span><i class="fa fa-user"></i></strong></div>	   
				        <div class="card-body">	   
							<?php if(!(@$for == 'projectgroup' && @$ps_id > 0 && @$id == 0)){ ?>
							<div class="row title dir-rtl">
								<div class="col-12">
									<strong class="fontSize20"><?=@$title?></strong>
								</div>
							</div>
							<?php } ?>

							<?php if(@$for == 'admingroup'){ ?>
									<div class="row marginTop5 alignCenter dir-rtl">
										<div class="col-12">
											<strong>חברת ניהול:</strong> <?=@$manager->name_he?>
											<input type="hidden" id="current_nickname" value="<?=htmlspecialchars(@$manager->nickname_he)?>" />
										</div>
									</div>
							<?php }
							else if(@$for == 'entrepreneurgroup'){ ?>
									<div class="row marginTop5 alignCenter dir-rtl">
										<div class="col-12">
											<strong>חברת יזם:</strong> <?=@$entrepreneur->name_he?>
											<input type="hidden" id="current_nickname" value="<?=htmlspecialchars(@$entrepreneur->nickname_he)?>" />
										</div>
									</div>
							<?php }
							else if(@$for == 'projectgroup'){
								if(@$ps_id > 0){ ?>
									<div class="row alignCenter dir-rtl" style="margin-top:-10px;">
										<div class="col-12">
											<strong style="color:#349feb;font-size:18px;"><?=@$ps->s_name_he?></strong>
											<input type="hidden" id="suppliers" value="<?=@$ps_id?>" data-nickname="<?=htmlspecialchars(@$ps->nickname_he)?>" onchange="functionsOnForm()" />
											<strong id="supplier_nickname_display" class="marginRight10" style="display:none;"></strong>
										</div>
									</div>
									<div class="row marginTop5 alignCenter dir-rtl">
										<div class="col-12">
											<strong style="font-size:18px;"><?=@$ps->sfow_name_he?></strong>
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
												<option value="<?=@$item->id?>" data-nickname="<?=htmlspecialchars(@$item->nickname_he)?>" <?php if(($item->id == @$responsible->id_projects_suppliers) || ($item->id == $ps_id)) echo 'selected';?>><?=@$item->name_he?></option>
											<?php } ?>
											</optgroup>
											<optgroup label="משרד">
											<?php foreach($designers as $item){ ?>
												<option value="<?=@$item->id?>" data-nickname="<?=htmlspecialchars(@$item->nickname_he)?>" <?php if(($item->id == @$responsible->id_projects_suppliers) || ($item->id == $ps_id)) echo 'selected';?>><?=@$item->name_he?></option>
											<?php } ?>
											</optgroup>
											<optgroup label="ניהול">
												<option value="<?=@$id_manager?>" data-nickname="<?=htmlspecialchars(@$manager->nickname_he)?>" <?php if(($id_manager == @$responsible->id_projects_suppliers) || ($id_manager == $ps_id)) echo 'selected';?>><?=@$manager->name_he?></option>
											</optgroup>
											<optgroup label="יזם">
												<option value="<?=@$id_entr?>" data-nickname="<?=htmlspecialchars(@$entrepreneur->nickname_he)?>" <?php if(($id_entr == @$responsible->id_projects_suppliers) || ($id_entr == $ps_id)) echo 'selected';?>><?=@$entrepreneur->name_he?></option>
											</optgroup>
										</select>
										<strong id="supplier_nickname_display" class="marginRight10"></strong>
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
								<div class="col-12 d-flex justify-content-center form-flex-row" style="gap:15px;">
									<div>
										<strong>שם פרטי</strong>
										<br/>
										<i id="pick_contact_icon" class="fa fa-address-book cursor-pointer marginLeft5" style="display:none;" title="בחר מהאנשי קשר" onclick="pickContactForFirstname()"></i><input type="text" class="paddingRight10 marginTop5" name="firstname" id="firstname" placeholder="*שם פרטי" value="<?=@$responsible->firstname?>" />
									</div>
									<div>
										<strong>שם משפחה</strong>
										<br/>
										<input type="text" class="paddingRight10 marginTop5" name="lastname" id="lastname" placeholder="שם משפחה" value="<?=@$responsible->lastname?>" />
									</div>
								</div>
							</div>

							<div class="row marginTop5 alignCenter dir-rtl">
								<div class="col-12">
									<strong>My Project</strong>
									<br/>
									<input type="text" dir="rtl" class="paddingRight10 marginTop5" name="name" id="name" placeholder="*שם" <?php if($id > 0) { ?>style="background-color:<?=@$default_bgcolor?>"<?php } ?> value="<?=@$responsible->name?>" />
								</div>
							</div>
							
							<div class="row marginTop5 alignCenter dir-rtl">
								<div class="col-12 d-flex justify-content-center form-flex-row" style="gap:15px;">
									<div>
										<strong>דוא''ל</strong>
										<br/>
										<input type="email" class="paddingRight10 marginTop5" name="email" id="email" placeholder="דוא''ל" value="<?=@$responsible->email?>" />
									</div>
									<div>
										<strong>טלפון</strong>
										<br/>
										<input type="text" class="paddingRight10 marginTop5" name="phone" id="phone" placeholder="טלפון" value="<?=@$responsible->phone?>" />
									</div>
								</div>
							</div>
						
							<?php if(@$from != 'project_data') { ?>
								<div class="row marginTop5 alignCenter dir-rtl">
									<div class="col-12 d-flex justify-content-center form-flex-row" style="gap:15px;">
										<div>
											<strong>צבע גופן</strong>
											<br/>
											<input type="color" class="marginTop5" style="width:40px;height:40px;border-radius:50%;overflow:hidden;padding:0;" name="color" id="color" placeholder="צבע גופן" value="<?php if($id == 0) echo '#000000';else echo @$responsible->color;?>" />
										</div>
										<div>
											<strong>צבע רקע</strong>
											<br/>
											<input type="color" class="marginTop5" style="width:40px;height:40px;border-radius:50%;overflow:hidden;padding:0;" name="bgcolor" id="bgcolor" placeholder="צבע רקע" value="<?php if($id == 0) echo '#ffffff';else echo @$responsible->bgcolor;?>" />
										</div>
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
											$btn_save = '+ איש צוות נוסף';
											$btn_next_step_val = "<i class='fa fa-plus'></i> ספק נוסף";
										}
									}
                                    else {
										$btn_save = 'שמור';
									}										
									?>
									
									<?php if($from == 'addProject'){ ?>
										<div style="position:relative;">
											<div class="d-flex justify-content-center align-items-center" style="gap:10px;flex-wrap:wrap;">
												<?php if(@$for == 'projectgroup'){ ?>
												<a id="save_btn" class="btn fontSize14 bgColorBlue colorWhite" style="white-space:nowrap;">
													<i class="fa fa-plus"></i> איש צוות נוסף
												</a>
												<?php } else { ?>
												<input type="button"
													   id="save_btn"
													   name="save_btn"
													   class="btn fontSize14 bgColorBlue colorWhite" style="white-space:nowrap;"
													   value="<?=@$btn_save?>" />
												<?php } ?>
												<a class="btn fontSize14 bgColorBlue colorWhite" style="white-space:nowrap;"
												   onclick="location.href='<?=@$url?>'">
													<?=@$btn_next_step_val?>
												</a>
											</div>

											<?php if(@$ps_id != ''){ ?>
												<a class="btn fontSize14 bgColorBlue colorWhite" style="white-space:nowrap;position:absolute;left:10px;top:50%;transform:translateY(-50%);"
												   onclick="location.href='add_chapter.php?id=0&project_id=<?=@$project_id?>&from=addProject'">
													<i class="fa fa-arrow-left"></i> הבא : הגדרות
												</a>
											<?php } ?>
										</div>
									<?php }
                                    else if($from == 'addSupToProj'){ ?>
										<div class="d-flex justify-content-center align-items-center" style="gap:10px;">
											<input type="button"
														   id="save_btn"
														   name="save_btn"
														   class="btn fontSize14 bgColorBlue colorWhite" style="white-space:nowrap;flex-shrink:0;"
														   value="<?=@$btn_save?>" />
											<a class="btn fontSize14 bgColorBlue colorWhite" style="white-space:nowrap;flex-shrink:0;"
											   onclick="location.href='add_responsible.php?id=0&project_id=<?=@$project_id?>&from=addSupToProj&for=projectgroup&ps_id=<?=@$ps_id?>'">
												<i class='fa fa-plus'></i> הוסף אחראי נוסף
											</a>
											<a class="btn fontSize14 bgColorBlack colorWhite"
											   onclick="location.href='add_sup_to_proj.php?id=<?=@$project_id?>'">
												סגור
											</a>
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
					<div class="col-3"></div>
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
	if (typeof AndroidNative !== 'undefined' && AndroidNative.pickContact) {
		$('#pick_contact_icon').show();
	}

	let initialNickname = $('#suppliers').is('select') ? $('#suppliers option:selected').data('nickname') : $('#suppliers').data('nickname');
	$('#supplier_nickname_display').text(initialNickname || '');
	if($('#suppliers').is('input') && $('#suppliers').val() > 0 && $('#id').val() == 0){
		autoFillRole();
		autoFillColors();
	}

	if($('#id').val() == 0){
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
			if($('#for').val() != 'admingroup' && $('#for').val() != 'entrepreneurgroup' && $('#for').val() != 'projectgroup'){
				$('#name').val(data.name_he);
			}
			$('#email').val(data.email);
			$('#phone').val(data.phone);
		},
		error: function (){
			console.error('Erreur AJAX');
		}
	});
	}
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

function updateNameFromSupplierAndFirstname(){
	let nickname;
	if($('#suppliers').length){
		nickname = $('#suppliers').is('select') ? $('#suppliers option:selected').data('nickname') : $('#suppliers').data('nickname');
	} else {
		nickname = $('#current_nickname').val();
	}
	let firstname = $('#firstname').val();
	if(nickname && firstname){
		$('#name').val(firstname+'-'+nickname);
		$('#name').css('background-color', $('#default_bgcolor').val());
	}
}

$('#suppliers').on('change', function(){
	let nickname = $(this).is('select') ? $(this).find('option:selected').data('nickname') : $(this).data('nickname');
	$('#supplier_nickname_display').text(nickname || '');
	updateNameFromSupplierAndFirstname();
});

$('#firstname').on('change', function(){
	updateNameFromSupplierAndFirstname();
});

function pickContactForFirstname(){
	if (typeof AndroidNative !== 'undefined' && AndroidNative.pickContact) {
		AndroidNative.pickContact('firstname');
	}
}

function receiveContactData(fieldId, firstname, lastname, phone, email){
	$('#firstname').val(firstname).trigger('change');
	$('#lastname').val(lastname);
	$('#phone').val(phone);
	$('#email').val(email);
}

function autoFillRole(){
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
		   $('#roles option').show();
		   if(data == 'S')
		      $('#roles').val('supplier_contractor');
		   else if(data == 'D')
		      $('#roles').val('programmer');
		   else if(data == 'M'){
		      $('#roles option').each(function(){
		         let val = $(this).val();
		         if(val != '0' && val != 'project_manager' && val != 'inspector') $(this).hide();
		      });
		      $('#roles').val('0');
		   }
		   else if(data == 'E'){
		      $('#roles option').each(function(){
		         let val = $(this).val();
		         if(val != '0' && val != 'entrepreneur' && val != 'entrepreneur_team') $(this).hide();
		      });
		      $('#roles').val('0');
		   }
		},
	});
}

function autoFillColors(){
	let form_data = new FormData();
	form_data.append('id_projects_suppliers',$('#suppliers').val());

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

function functionsOnForm(){
	autoFillRole();
	autoFillColors();
}

$('#save_btn').click(function (e){
    $('#firstname').css('border-color', 'initial');
    if ($('#firstname').val().trim() == ''){
        $('#firstname').css('border-color', 'red');
        $('#div_message_alert_down').html("<span style='color:red;font-size:13px;'>נא למלה את כל השדות החובות</span>");
        return false;
    }

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
    form_data.append('firstname', $('#firstname').val());
    form_data.append('lastname', $('#lastname').val());
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
                if ($('#firstname').val().length == 0){
                    $('#firstname').css('border-color', 'red');
                } else {
                    $('#firstname').css('border-color', 'initial');
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
                } else if ($('#from').val() == 'addSupToProj'){
                    if(data.indexOf('inserted') === 0){
                        let newId = data.split(',')[1];
                        window.location.href = 'add_responsible.php?id=' + newId + '&project_id=' + $('#project_id').val() +
                                  '&from=addSupToProj&for=projectgroup&ps_id=' + $('#ps_id').val();
                    } else {
                        window.location.reload();
                    }
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

@media screen and (max-width: 600px) {
	#add_responsible_form_row > .col-3 {
		display: none;
	}
	#add_responsible_form_row > .col-6 {
		width: 100%;
		max-width: 100%;
		flex: 0 0 100%;
	}
	.form-flex-row {
		flex-wrap: wrap;
	}
	#firstname, #lastname, #email, #phone, #name {
		width: 100%;
		box-sizing: border-box;
	}
	.form-flex-row > div {
		width: 100%;
	}
}

@media (orientation: landscape) and (max-height: 500px) {
	#add_responsible_form_row > .col-3 {
		display: none;
	}
	#add_responsible_form_row > .col-6 {
		width: 100%;
		max-width: 100%;
		flex: 0 0 100%;
	}
	.form-flex-row {
		flex-wrap: wrap;
	}
	#firstname, #lastname, #email, #phone, #name {
		width: 100%;
		box-sizing: border-box;
	}
	.form-flex-row > div {
		width: 100%;
	}
}
</style>