<?php
include 'include/header.php';
include 'functions/functions.php';

$query = $mysqli->prepare("SELECT * FROM dne_logos LIMIT 1");
$query->execute();
$query->store_result();
$logo = fetch_unique($query);

$sup_type = 'E';
$query = $mysqli->prepare("SELECT color,bgcolor FROM dne_sup_field_of_work WHERE sup_type = ?");
$query->bind_param("s",$sup_type);
$query->execute(); 
$sfow = fetch_unique($query);

$query = $mysqli->prepare("SELECT bgcolor FROM dne_global_bgcolor_new_task LIMIT 1");
$query->execute(); 
$query->store_result();
$bg_color_new_task = fetch_unique($query);

$query = $mysqli->prepare("SELECT * FROM dne_inputs_colors LIMIT 1");
$query->execute(); 
$query->store_result();
$bg_color_inputs = fetch_unique($query);
?>
		<form method="post" action="" class="form-inline">	
			<div class="container">
			    <div class="row marginTop25 alignCenter">
					<div class="col-md-12">
						<a href="projects.php"><img src="images/<?=@$logo->logo?>" width="120" height="114" /></a>
					</div>
				</div>
				
				<div class="row title marginTop15 fontSize20 font-weight-bold">
				   <div class="col-md-12">
					   הגדרות גרפיקה
					   <br/>
					   <?=substr(date('Y-m-d'),8,2).'/'.substr(date('Y-m-d'),5,2).'/'.substr(date('Y-m-d'),0,4)?>
				   </div>
				</div>
				
				<h5 class="marginTop20 alignCenter">יזם</h5>	
				
				<div class="row dir-rtl alignCenter">
					<div class="color-picker-group">
						<div class="color-picker">
							<strong>צבע גופן</strong>
							<br/>
							<input type="color" id="etrp_color" name="etrp_color" class="marginTop5 width100" onchange="setEtrpData(this.value,'');" value="<?=@$sfow->color?>" />
						</div>
						<div class="color-picker">
							<strong>צבע רקע</strong>
							<br/>
							<input type="color" id="etrp_bgcolor" name="etrp_bgcolor" class="marginTop5 width100" onchange="setEtrpData('',this.value);" value="<?=@$sfow->bgcolor?>" />
						</div>
					</div>
					
					<h5 class="marginTop20">רקע למשימה חדשה</h5>

					<div class="row">
						<div class="col-12">
							<strong>צבע רקע</strong>
							<br/>
							<input type="color" id="new_task_bgcolor" name="new_task_bgcolor" class="marginTop5 width100" onchange="setNewTaskData(this.value);" value="<?=@$bg_color_new_task->bgcolor?>" />
						</div>
					</div>
				</div>
							
				<h5 class="marginTop20 alignCenter">צבע רקע שדות קלט</h5>

				<div class="row dir-rtl alignCenter">
					<div class="color-picker-group">
						<div class="color-picker">   
							<strong>רק</strong>
							<br/>
							<input type="color" id="empty_bgcolor" name="empty_bgcolor" class="width100" onchange="setInputsBgColor('empty_bgcolor',this.value);" value="<?=@$bg_color_inputs->empty_bgcolor?>" />
						</div>
						<div class="color-picker">
							<strong>ברירת מחדל</strong>
							<br/>
							<input type="color" id="default_bgcolor" name="default_bgcolor" class="width100" onchange="setInputsBgColor('default_bgcolor',this.value);" value="<?=@$bg_color_inputs->default_bgcolor?>" />
						</div>
						<div class="color-picker">
							<strong>מלא</strong>
							<br/>
							<input type="color" id="filled_bgcolor" name="filled_bgcolor" class="width100" onchange="setInputsBgColor('filled_bgcolor',this.value);" value="<?=@$bg_color_inputs->filled_bgcolor?>" />
						</div>
					</div>
				</div>
				
				<h5 class="marginTop20 alignCenter dir-rtl">צבע רקע A עד I</h5>
				
				<div class="row dir-rtl alignCenter">
					<div class="color-picker-group">
						<div class="color-picker">   
							<strong class="fontSize11">רקע משימות שלי</strong>
							<br/>
							<input type="color" id="a_bgcolor" name="a_bgcolor" class="width100" onchange="setInputsBgColor('a_bgcolor',this.value);" value="<?=@$bg_color_inputs->a_bgcolor?>" />
						</div>
						<div class="color-picker">
							<strong class="fontSize11">רקע מעקב אקטיבי</strong>
							<br/>
							<input type="color" id="b_bgcolor" name="b_bgcolor" class="width100" onchange="setInputsBgColor('b_bgcolor',this.value);" value="<?=@$bg_color_inputs->b_bgcolor?>" />
						</div>
						<div class="color-picker">
							<strong class="fontSize11">רקע תקציב</strong>
							<br/>
							<input type="color" id="c_bgcolor" name="c_bgcolor" class="width100" onchange="setInputsBgColor('c_bgcolor',this.value);" value="<?=@$bg_color_inputs->c_bgcolor?>" />
						</div>
						<div class="color-picker">
							<strong class="fontSize11">רקע מה חדש</strong>
							<br/>
							<input type="color" id="d_bgcolor" name="d_bgcolor" class="width100" onchange="setInputsBgColor('d_bgcolor',this.value);" value="<?=@$bg_color_inputs->d_bgcolor?>" />
						</div>
						<div class="color-picker">
							<strong class="fontSize11">גוון משימות שלי</strong>
							<br/>
							<input type="color" id="e_bgcolor" name="e_bgcolor" class="width100" onchange="setInputsBgColor('e_bgcolor',this.value);" value="<?=@$bg_color_inputs->e_bgcolor?>" />
						</div>
					</div>
				</div>
				
				<div class="row marginTop5 dir-rtl alignCenter">
					<div class="color-picker-group">	
						<div class="color-picker">
							<strong class="fontSize11">גוון מעקב אקטיבי</strong>
							<br/>
							<input type="color" id="f_bgcolor" name="f_bgcolor" class="width100" onchange="setInputsBgColor('f_bgcolor',this.value);" value="<?=@$bg_color_inputs->f_bgcolor?>" />
						</div>
						<div class="color-picker">
							<strong class="fontSize11">גוון תקציב</strong>
							<br/>
							<input type="color" id="g_bgcolor" name="g_bgcolor" class="width100" onchange="setInputsBgColor('g_bgcolor',this.value);" value="<?=@$bg_color_inputs->g_bgcolor?>" />
						</div>
						<div class="color-picker">
							<strong class="fontSize11">גוון מה חדש</strong>
							<br/>
							<input type="color" id="h_bgcolor" name="h_bgcolor" class="width100" onchange="setInputsBgColor('h_bgcolor',this.value);" value="<?=@$bg_color_inputs->h_bgcolor?>" />
						</div>
						<div class="color-picker">
							<strong class="fontSize11">רקע To Do Today</strong>
							<br/>
							<input type="color" id="i_bgcolor" name="i_bgcolor" class="width100" onchange="setInputsBgColor('i_bgcolor',this.value);" value="<?=@$bg_color_inputs->i_bgcolor?>" />
						</div>
					</div>
				</div>
			</div>
		</form>
	</body>
</html>

<script>
function setEtrpData(color,bgcolor){
	let form_data = new FormData();	
	form_data.append('color',color);
	form_data.append('bgcolor',bgcolor);
	
	$.ajax({
		type: 'POST',
		url: 'setEtrpData.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,			
		success: function(data){
			location.reload(true);			
		},
	});		
}

function setInputsBgColor(field,value){
	let form_data = new FormData();  	
	form_data.append('field',field);
	form_data.append('value',value);
	
	$.ajax({
		type: 'POST',
		url: 'set_inputs_bgcolor.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,			
		success: function(data){
			location.reload(true);			
		},
	});		
}

function setNewTaskData(bgcolor){
	let form_data = new FormData();	
	form_data.append('bgcolor',bgcolor);
	
	$.ajax({
		type: 'POST',
		url: 'setNewTaskData.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,			
		success: function(data){
			location.reload(true);			
		},
	});		
}
</script>

<style>
.color-picker-group {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 15px;
}

.color-picker {
    flex: 1 1 200px; 
    max-width: 100px;
	text-align: center;
}
</style>