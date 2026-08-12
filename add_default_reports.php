<?php
include 'include/header.php';
include 'functions/functions.php';

$project_id = @$_GET['project_id'];

$query = $mysqli->prepare("SELECT * FROM dne_projects WHERE id = ?");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$project = fetch_unique($query);

$general_tasks = 'כלל המשימות';
$query = $mysqli->prepare("SELECT * FROM dne_custom_reports 
                          WHERE id_project = ? AND request_name = ?");
$query->bind_param("is",$project_id,$general_tasks);
$query->execute();
$query->store_result();
$general_tasks_num_rows = @$query->num_rows;

$gt_btn_disabled = '';
if(@$general_tasks_num_rows > 0)
	$gt_btn_disabled = 'disabled';

$my_tasks = 'משימות שלי';
$query = $mysqli->prepare("SELECT * FROM dne_custom_reports 
                          WHERE id_project = ? AND request_name = ?");
$query->bind_param("is",$project_id,$my_tasks);
$query->execute();
$query->store_result();
$my_tasks_num_rows = @$query->num_rows;

$mt_btn_disabled = '';
if(@$my_tasks_num_rows > 0)
	$mt_btn_disabled = 'disabled';

$management_team = 'צוות ניהול';
$query = $mysqli->prepare("SELECT * FROM dne_custom_reports
                          WHERE id_project = ? AND request_name = ?");
$query->bind_param("is",$project_id,$management_team);
$query->execute();
$query->store_result();
$management_team_num_rows = @$query->num_rows;

$mgt_btn_disabled = '';
if(@$management_team_num_rows > 0)
	$mgt_btn_disabled = 'disabled';

$entrepreneur_team = 'צוות יזם';
$query = $mysqli->prepare("SELECT * FROM dne_custom_reports
                          WHERE id_project = ? AND request_name = ?");
$query->bind_param("is",$project_id,$entrepreneur_team);
$query->execute();
$query->store_result();
$entrepreneur_team_num_rows = @$query->num_rows;

$et_btn_disabled = '';
if(@$entrepreneur_team_num_rows > 0)
	$et_btn_disabled = 'disabled';

$tracking = 'במעקב';
$query = $mysqli->prepare("SELECT * FROM dne_custom_reports
                          WHERE id_project = ? AND request_name = ?");
$query->bind_param("is",$project_id,$tracking);
$query->execute();
$query->store_result();
$tracking_num_rows = @$query->num_rows;

$tr_btn_disabled = '';
if(@$tracking_num_rows > 0)
	$tr_btn_disabled = 'disabled';

$finish_btn_disabled = 'disabled';
if(@$general_tasks_num_rows > 0 && @$my_tasks_num_rows > 0)
	$finish_btn_disabled = '';

include 'menu_tasks.php';
?>
		<form method="post" action="" enctype="multipart/form-data" class="form-inline">
			<input type="hidden" id="project_id" value="<?=@$project_id?>" />
			<input type="hidden" id="project_lang" value="<?=@$project->lang?>" />
			
			<div class="container">
				<div class="row alignCenter marginTop25">
					<div class="col-12">
						<a href="projects.php"><img src="images/davidnahmias_logo.png" width="170px" height="170px" /></a>
					</div>
			    </div>
				
				<div class="row marginTop15 title dir-rtl">	
					<div class="col-12">
						<a id="a_project_title" class="text-decoration-none color-1A5276" href="meetings.php?project_id=<?=@$project_id?>">
						    <strong class="fontSize33 line-height-1em"><?=@$project->nickname?></strong>
							<div class="fontSize26 font-family-david">פרוייקט <?=@$project->name_he?></div>
					    </a>		
					</div>				
			    </div>
				
				<div class="row marginTop15 alignCenter dir-rtl">
					<div class="col-12 default-reports-buttons">
						<input type="button" value="יצירת דו''ח כלל המשימות" class="btn mb-2" onclick="addReport('generalTasksReport')" <?=@$gt_btn_disabled?> />
						<input type="button" value="יצירת דו''ח משימות שלי" class="btn mb-2" onclick="addReport('myTasksReport')" <?=@$mt_btn_disabled?> />
						<input type="button" value="יצירת דו''ח צוות ניהול" class="btn mb-2" onclick="addReport('managementTeamReport')" <?=@$mgt_btn_disabled?> />
						<input type="button" value="יצירת דו''ח צוות יזם" class="btn mb-2" onclick="addReport('entrepreneurTeamReport')" <?=@$et_btn_disabled?> />
						<input type="button" value="יצירת דו''ח במעקב" class="btn mb-2" onclick="addReport('trackingReport')" <?=@$tr_btn_disabled?> />
					</div>
				</div>
				
				<div class="row marginTop15 alignCenter dir-rtl">	
					<div class="col-12">
						<input type="button" value="סיים" class="btn mb-2" onclick="location.href='meetings.php?project_id=<?=@$project_id?>'" <?=@$finish_btn_disabled?> />
					</div>
				</div>
			</div>
		</form>
	</body>
</html>

<script>
function addReport(report_name) {
	let form_data = new FormData();	
	form_data.append('report_type',report_name);
	form_data.append('id_project',$('#project_id').val());
	form_data.append('lang',$('#project_lang').val());
	
	$.ajax({
		type: 'POST',
		url: 'custom_report_insert.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,			
		success: function(data){
			location.reload();
		},
	});	
}
</script>

<style>
.btn {
   color: white;
   background-color: #218FD6;
}

.btn:hover {
   color: white;
   background-color: #3370d6;
}

#a_project_title:hover {
	color: grey;
}

.default-reports-buttons {
	display: flex;
	flex-wrap: wrap;
	justify-content: center;
	gap: 12px;
	max-width: 650px;
	margin: 0 auto;
}

.default-reports-buttons .btn {
	margin: 0;
	flex: 0 1 200px;
}
</style>