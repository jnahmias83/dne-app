<?php
include 'include/header.php';
include 'functions/functions.php';

$project_id = @$_GET['project_id'];
$is_specific_filter = @$_GET['is_specific_filter'];
$id = @$_GET['id'];
$all_ids_to_edit = @$_GET['all_ids_to_edit'];
$from = @$_GET['from'];
$lang_get = @$_GET['lang'];

$query = $mysqli->prepare("SELECT id,rdv_name,rdv_date FROM dne_rdv 
                          WHERE id_project = ? ORDER BY rdv_date DESC");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$rdvs = fetch($query);

$query = $mysqli->prepare("SELECT * FROM dne_projects WHERE id = ?");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$project = fetch_unique($query);

$query = $mysqli->prepare("SELECT empty_bgcolor,default_bgcolor,filled_bgcolor FROM dne_inputs_colors LIMIT 1");
$query->execute(); 
$query->store_result();
$bg_color_inputs = fetch_unique($query);
$empty_bgcolor = @$bg_color_inputs->empty_bgcolor;
$default_bgcolor = @$bg_color_inputs->default_bgcolor;
$filled_bgcolor = @$bg_color_inputs->filled_bgcolor;

$query = $mysqli->prepare("SELECT * FROM dne_meetings_types");
$query->execute();
$query->store_result();
$meetings_types = fetch($query); 

$query = $mysqli->prepare("SELECT * FROM dne_responsibles WHERE id_project = ? ORDER BY name ASC");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$responsibles = fetch($query);

$rdv_date = date('Y-m-d');

if($id > 0) {
   $query = $mysqli->prepare("SELECT * FROM dne_rdv WHERE id = ?");
   $query->bind_param("i",$id);
   $query->execute();
   $query->store_result();
   $rdv = fetch_unique($query);
   
   if($rdv->rdv_date != '0000-00-00') {
	   $rdv_date = $rdv->rdv_date;
   }
}

$rdv_persons_array = explode(",",@$rdv->rdv_persons);

include 'menu_tasks.php';
?>

		<form method="post" action="" class="form-inline">
		    <input type="hidden" id="id" value="<?=@$id?>" />
			<input type="hidden" id="project_id" value="<?=@$project_id?>" />	
			<input type="hidden" id="is_specific_filter" value="<?=@$is_specific_filter?>" />
			<input type="hidden" id="all_ids_to_edit" value="<?=@$all_ids_to_edit?>" />
			<input type="hidden" id="lang" value="<?=@$_SESSION['lang']?>" />
			<input type="hidden" id="empty_bgcolor" value="<?=@$empty_bgcolor?>" />
			<input type="hidden" id="default_bgcolor" value="<?=@$default_bgcolor?>" />
			<input type="hidden" id="filled_bgcolor" value="<?=@$filled_bgcolor?>" />

            <div class="container">
			    <div class="row alignCenter marginTop25">
					<div class="col-12">
						<a href="projects.php"><img src="images/davidnahmias_logo.png" width="170px" height="170px" /></a>
					</div>
			    </div>
				
				<div class="row marginTop15 title dir-rtl">	
					<div class="col-12">
						<a id="a_project_title" class="text-decoration-none color-1A5276" href="meetings.php?project_id=<?=@$project_id?>&task_filter=<?=@$task_filter?>&progress_status_filter=<?=@$progress_status_filter?>&supplier_filter=<?=@$supplier_filter?>&period_new_task_filter=<?=@$period_new_task_filter?>">
						    <strong class="fontSize33 line-height-1em"><?=@$project->nickname?></strong>
							<div class="fontSize26 font-family-david">פרוייקט <?=@$project->name_he?></div>
					    </a>		
					</div>       					
			    </div>
				
				<?php if(@$from != 'addRdv' && @$all_ids_to_edit != '') { ?>
				    <div class="row marginTop15 title dir-rtl">	
						<div class="col-12 fontSize18 colorBlack">
						   שייך משימות שנבחרו לישיבה ...
						</div>
					</div>
					
					<div class="row marginTop15 alignCenter dir-rtl">
						<div class="col-12">
							<select id="rdv" class="width400 marginTop5 paddingRight10 text-size">
								<option value="0">------ בחר ישיבה ------</option>
								 <?php 
								foreach($rdvs as $item) { ?>
									<option value="<?=@$item->id?>">
										<?=substr($item->rdv_date,8,2).'/'.substr($item->rdv_date,5,2).'/'.substr($item->rdv_date,0,4).'-'.@$item->rdv_name?>
									</option>
								<?php
								}
								?>			
						    </select>
						</div>
					</div>		
				<?php } 
				else { 
				    if(@$id == 0) { ?>
						<div class="row marginTop5 title">	
							<div class="col-12">
								<div class="fontSize26 font-weight-bold font-family-david cursor-pointer">ישיבה חדשה</div>
							</div>
						</div>
						
						<div class="row marginTop5 alignCenter">
							<div class="col-12">
								<input type="button" id="duplicate_rdv_btn" class="borderRadius10" value="שכפול ישיבה קיימת" onclick="$('#div_select_existing_rdv').toggle();" />	
							</div>
						</div>
					<?php } ?>	
					
					<div id="div_select_existing_rdv" class="row marginTop10 dir-rtl display-none">	
						<div class="row title dir-rtl">	
							<div class="col-12">
								<span>בחר ישיבה קיימת</span>
							</div>
						</div>	
						
						<div class="flex flex-wrap justify-content-center align-items-center width40Percents margin-0-x-auto alignCenter">
							<div class="col-12">
								<select id="existing_rdv" class="width300 marginTop5 paddingRight10 text-size">
									<option value="0">------ בחר ישיבה ------</option>
									 <?php 
									foreach($rdvs as $item) { ?>
										<option value="<?=@$item->id?>">
											<?=substr($item->rdv_date,8,2).'/'.substr($item->rdv_date,5,2).'/'.substr($item->rdv_date,0,4).'-'.@$item->rdv_name?>
										</option>
									<?php
									}
									?>			
								</select>
							</div>	
						</div>						
					</div>					
 
					<div class="row marginTop15 alignCenter">
						<div class="col-12">
							<strong>סוג ישיבה</strong>
							<br/>
							<select id="id_meetings_types" class="marginTop5 height30 paddingRight10 dir-rtl">	
								<option value="0">--- רשימת סוגי ישיבות ----</option>
								<?php 
								foreach($meetings_types as $item) {
								?>
									<option value="<?=@$item->id?>" <?php if($item->id == @$rdv->id_meetings_types) echo "selected";?>>
										<?=@$item->name_he?>
									</option>
									<?php
								}
								?>						
							</select>
						</div>
					</div>	
				
					<div class="row marginTop15 alignCenter">
						<div class="col-12">
							<strong>שפה ?</strong>
							&nbsp;
							<input type="radio" id="lang_he" name="lang" value="HE" <?php if((@$id == 0 && @$lang_get == 'HE') || @$rdv->rdv_lang == 'HE') echo "checked";?> />&nbsp;עברית
							&nbsp;
							<input type="radio" id="lang_en" name="lang" value="EN" <?php if((@$id == 0 && @$lang_get == 'EN') || @$rdv->rdv_lang == 'EN') echo "checked";?> />&nbsp;אנגלית
						</div>
					</div>

					<div class="row flex width-responsive margin-top-20-x-auto alignCenter dir-rtl">
						<div class="width50Percents">
							<strong>תאריך</strong>
							<br/>						
							<input type="date" class="marginTop5 alignCenter" name="rdv_date" id="rdv_date" value="<?=@$rdv_date?>" />
						</div>
						<div class="width50Percents">	
							<strong>נושא</strong>	
							<br/>							
							<input type="text" class="marginTop5 paddingRight5" name="rdv_name" id="rdv_name" placeholder="נושא" value="<?=@$rdv->rdv_name?>" />
						</div>
					</div>
					
					<div class="margin-top-10-x-auto width-responsive">
						<div class="row dir-rtl alignCenter">
							<div class="col-12">
								<strong>משתתפים</strong>
							</div>
						</div>

						<div class="row marginTop10 alignCenter dir-rtl">					
							<div class="col-12 padding10 alignRight border-black">		
								<?php 
								foreach($responsibles as $item) {
								?>
									<input type="checkbox" name="rdv_persons[]" value="<?=@$item->id?>" <?php if(in_array($item->id,$rdv_persons_array)) echo 'checked' ?> />
									<span class="marginRight2 person-label"><?=$item->name?></span>
									<br/>
									<?php
								}
								?>						
							</div>		
						</div>						
					</div>
					
					<div class="row marginTop20 dir-rtl alignCenter">
						<div class="col-12">
							<div id="div_message_alert_down"></div>
							<input type="button" id="save_btn" name="save_btn" class="btn bgColorBlue colorWhite mb-2" value="שמור" />					
							<input type="button" id="cancel_btn" class="btn bgColorBlack marginRight8 colorWhite mb-2" value="ביטול" />						
						</div>
					</div>					
					
					<?php if(@$id == 0) { ?>
					    <div class="row marginTop10 dir-rtl alignCenter">
							<div class="col-12 font-weight-bold fontSize18 dir-rtl">
							     עליך לבחור משימות ולשייך אותן לישובה זאת.
							</div>
						</div>
					<?php } ?>
				<?php } ?>
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
	if($('#id').val() == 0){
		$('#id_meetings_types').css('background-color',$('#empty_bgcolor').val());
		$('#rdv_date').css('background-color',$('#default_bgcolor').val());
		$('#rdv_name').css('background-color',$('#empty_bgcolor').val());
	}
	else {
		$('#id_meetings_types,#rdv_date,#rdv_name').css('background-color',$('#filled_bgcolor').val());
	}
});

$('#id_meetings_types,#rdv_date,#rdv_name').change(function () {
    let $field = $(this);

    if(($field.attr('id') === 'rdv_name' && $field.val().trim().length === 0) ||
        ($field.attr('id') === 'id_meetings_types' && $field.val() === "0")){
        $field.css('background-color',$('#empty_bgcolor').val());
    } 
	else {
        $field.css('background-color', $('#filled_bgcolor').val());
    }
});



$('#existing_rdv').change (function (e){
    let $select = $(this);
 	
	let form_data = new FormData();	
	form_data.append('id_rdv',$(this).val()); 	
	
	$.ajax({
		type: 'POST',
		url: 'get_rdv_data.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,	
        dataType: 'json',		
		success: function(data){
			$select.css('background-color',$('#filled_bgcolor').val());
			$('#id_meetings_types').val(data.id_meetings_types).trigger('change').css('background-color',$('#default_bgcolor').val());
			$('input[name="lang"][value="'+data.rdv_lang+'"]').prop('checked',true);
			$('#rdv_name').val(data.rdv_name).css('background-color',$('#default_bgcolor').val());
			$('input[name="rdv_persons[]"]').prop('checked',false);
            
			$('.person-label').css('color', 'black');
			
			if(data.rdv_persons){
				let persons = data.rdv_persons.split(',');
				persons.forEach(function(personId){
					$('input[name="rdv_persons[]"][value="'+personId.trim()+'"]')
					.prop('checked',true)
					.next('.person-label')
                    .css('color','red');
				});
		    }   		   
		},
	});	
});

$('#save_btn').click (function (e){ 
    let rdv_persons = $('input[name="rdv_persons[]"]:checked').map(function() {
		return $(this).val();
    }).get().join(',');
	
	let rdv_lang = 'HE';
	if($('input[name="lang"]:checked').length > 0)
	  rdv_lang = $('input[name="lang"]:checked').val();
	
    let form_data = new FormData();	
	form_data.append('id',$('#id').val());
	form_data.append('id_project',$('#project_id').val());
	form_data.append('rdv_name',$('#rdv_name').val());
	form_data.append('id_meetings_types',$('#id_meetings_types').val());
	form_data.append('rdv_persons',rdv_persons);
	form_data.append('rdv_date',$('#rdv_date').val());
	form_data.append('rdv_lang',rdv_lang);
	form_data.append('all_ids_to_edit',$('#all_ids_to_edit').val());
	
	$.ajax({
		type: 'POST',
		url: 'rdv_insert.php',
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
			if(data == 'empty')	{
				if($('#rdv_name').val().length == 0)			
					$('#rdv_name').css('border-color','red');
				else if(!($('#rdv_name').val().length == 0))
					$('#rdv_name').css('border-color','initial');
				
				$('#div_message_alert_down').html("<span style=color:red;font-size:13px;>Please fill all the mandatory fields</span>"); 
			}
			else 
				location.href = "meetings.php?project_id="+$('#project_id').val();	
		}
	});									       			   
});

$('#rdv').on('change', function (){
	let form_data = new FormData();
	form_data.append('id_rdv',$(this).val());
	form_data.append('id_project',$('#project_id').val());
	form_data.append('all_ids_to_edit',$('#all_ids_to_edit').val());
    
	$.ajax({
		type: 'POST',
		url: 'associate_tasks_to_rdv.php',
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
		   location.href = "meetings.php?project_id="+$('#project_id').val();	
		}
	});					
});	

$('#cancel_btn').click(function(){
    location.href = "meetings.php?project_id="+$('#project_id').val();	
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

.width-responsive {
    width: 100%;
    margin-bottom: 10px; 
}

@media (min-width: 500px) {
    .width-responsive {
       width: 70%
    }
}

@media (min-width: 768px) {
    .width-responsive {
        width: 45%
    }
}

@media (min-width: 1024px) {
    .width-responsive {
       width: 35%
    }
}

@media (min-width: 1200px) {
    .width-responsive {
       width: 30%
    }
}

@media (min-width: 1500px) {
    .width-responsive {
       width: 25%
    }
}
</style>	