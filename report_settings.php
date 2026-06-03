<?php 
include 'include/header.php';
include 'functions/functions.php';

$project_id = @$_GET['project_id'];
$report_type = @$_GET['report_type'];
$all_ids_to_print = @$_GET['all_ids_to_print'];
$period_new_task_filter = @$_GET['period_new_task_filter'];
$task_filter = @$_GET['task_filter'];
$progress_status_filter = @$_GET['progress_status_filter'];
$supplier_filter = @$_GET['supplier_filter'];
$all_ids_to_print = @$_GET['all_ids_to_print'];
$is_specific_filter = @$_GET['is_specific_filter'];
$lang = @$_GET['lang'];

$query = $mysqli->prepare("SELECT * FROM dne_projects WHERE id = ?");
$query->bind_param("i",$project_id );
$query->execute();
$query->store_result();
$project = fetch_unique($query);

$report_type_array = explode('_',$report_type);

if($report_type_array[0] == 'general') {
	$query = $mysqli->prepare("SELECT * FROM dne_custom_reports WHERE id = ?");
	
    $query = $mysqli->prepare("SELECT s.nickname AS supplier_nickname,
	                           cr.title AS title,cr.subtitle AS subtitle,
	                           cr.pdf_name AS pdf_name,
							   cr.id_supplier AS id_supplier
							   FROM dne_custom_reports cr
							   LEFT JOIN dne_projects_suppliers ps ON cr.id_supplier = ps.id
							   LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
							   WHERE cr.id = ?");
    $query->bind_param("i",$report_type_array[1]);
    $query->execute();
    $query->store_result();
    $custom_report = fetch_unique($query);
	
	$pdf_file_name = @$custom_report->pdf_name;
    $id_supplier = @$custom_report->id_supplier;

    if(@$id_supplier == 0) {
		$pdf_calendar_date = date('Y-m-d');
		$pdf_date = substr(date('Y-m-d'),0,4).substr(date('Y-m-d'),5,2).substr(date('Y-m-d'),8,2);
		$pdf_text_1 = 'Tasks Report';
		$pdf_text_2 = @$custom_report->pdf_name;
		$pdf_file_name = @$pdf_date.'-'.@$pdf_text_1.'-'.@$pdf_text_2.'-'.@$project->nickname;
		$title1 = @$custom_report->title;
		$title2 = @$custom_report->subtitle;
	}
	else if(@$id_supplier > 0) {  
       $pdf_calendar_date = date('Y-m-d');
       $pdf_date = substr(date('Y-m-d'),0,4).substr(date('Y-m-d'),5,2).substr(date('Y-m-d'),8,2);
	   $supplier_nickname = str_replace('.','',$custom_report->supplier_nickname);	
	   $pdf_text_1 = @$supplier_nickname;
	   $pdf_text_2 = 'Tasks Report';
	   $pdf_file_name = @$pdf_date.'-'.@$pdf_text_1.'-'.@$pdf_text_2.'-'.@$project->nickname;
	   $title1 = @$custom_report->title;
	   $title2 = @$custom_report->subtitle;
	}
}
else if($report_type_array[0] == 'resumeRdv') {
   $query = $mysqli->prepare("SELECT rdv.rdv_name AS rdv_name,
                             rdv.rdv_date AS rdv_date,
							 rdv.rdv_lang AS rdv_lang,
                             mt.name AS meeting_type_name,
                             mt.name_he AS meeting_type_name_he,
                             mt.title AS meeting_title,	
                             mt.title_he AS meeting_title_he							 
							 FROM dne_rdv rdv
							 LEFT JOIN dne_meetings_types mt ON rdv.id_meetings_types = mt.id
							 WHERE rdv.id = ?");
   $query->bind_param("i",$report_type_array[1]);
   $query->execute();
   $query->store_result();
   $rdv = fetch_unique($query);
   
   $pdf_calendar_date = @$rdv->rdv_date;
   $pdf_date = substr(@$rdv->rdv_date,0,4).substr(@$rdv->rdv_date,5,2).substr(@$rdv->rdv_date,8,2);
   
   if(@$rdv->rdv_lang == "HE"){
	   $pdf_text_1 = @$rdv->meeting_type_name;
       $pdf_text_2 = @$rdv->meeting_title;
	   $title1 = @$rdv->meeting_title_he;
   }
   else {
	   $pdf_text_1 = @$rdv->meeting_type_name;
       $pdf_text_2 = @$rdv->meeting_title;
	   $title1 = @$rdv->meeting_title;
   }
   
   $pdf_file_name = @$pdf_date.'-'.@$pdf_text_1.'-'.@$pdf_text_2.'-'.@$project->nickname;
   $title2 = @$rdv->rdv_name;
}

include 'menu_tasks.php';
?>

		<form method="post" action="" class="form-inline">	
			<input type="hidden" id="project_id" name="project_id" value="<?=@$project->id?>" />
			<input type="hidden" id="report_type" name="report_type" value="<?=@$report_type_array[0]?>" />
			<input type="hidden" id="all_ids_to_print" name="all_ids_to_print" value="<?=@$all_ids_to_print?>" />
			<input type="hidden" id="task_filter" name="task_filter" value="<?=@$task_filter?>" />
			<input type="hidden" id="progress_status_filter" name="progress_status_filter" value="<?=@$progress_status_filter?>" />
			<input type="hidden" id="supplier_filter" name="supplier_filter" value="<?=@$supplier_filter?>" />
			<input type="hidden" id="period_new_task_filter" name="period_new_task_filter" value="<?=@$period_new_task_filter?>" />
			<input type="hidden" id="is_specific_filter" name="is_specific_filter" value="<?=@$is_specific_filter?>" />
			<input type="hidden" id="lang" name="lang" value="<?=@$lang?>" />
			
			<div class="container">
                <div class="row alignCenter marginTop25">
					<div class="col-12">
						<a href="projects.php"><img src="images/davidnahmias_logo.png" width="170px" height="170px" /></a>
					</div>
			    </div>
				
				<div class="row marginTop15 title">	
					<div class="col-12">
						<a id="a_project_title" class="text-decoration-none color-1A5276" href="meetings.php?project_id=<?=@$project_id?>&task_filter=<?=@$task_filter?>&progress_status_filter=<?=@$progress_status_filter?>&supplier_filter=<?=@$supplier_filter?>&period_new_task_filter=<?=@$period_new_task_filter?>&is_specific_filter=<?=@$is_specific_filter?>">
						    <strong class="fontSize33 line-height-1em"><?=@$project->nickname?></strong>
							<div class="fontSize26 font-weight-bold font-family-david cursor-pointer">פרוייקט <?=@$project->name_he?></div>
					    </a>
                   	</div>				
			    </div>
				
				<div class="row marginTop10">
				    <div class="col-1"></div>
				    <div class="col-10 card bg-f2f6f9 border-frame borderRadius15 padding0">
						<div class="card-header bgColor-1a5276 colorWhite alignCenter fontSize20" style="border-top-left-radius:15px;border-top-right-radius:15px;"><strong>PDF</strong></div>	   
				        <div class="card-body">
							<div class="row title">	
								<div class="col-12">
									<input type="date" id="pdf_date" class="paddingLeft10 paddingRight10" value="<?=@$pdf_calendar_date?>" />
								</div>
							</div>
				
							<div class="row marginTop20 alignCenter dir-rtl">
								<div class="col-12">
									<strong class="colorBlack">שם PDF</strong>
								</div>
							</div>

							<div class="row marginTop10 alignCenter colorBlack">
								<div class="col-12 fontSize18">
									<input type="text" class="width150 alignCenter" id="pdf_file_name_date" value="<?=@$pdf_date?>" disabled /> -
									<input type="text" class="width190 alignCenter" id="pdf_file_name_text_1" placeholder="Text 1" value="<?=@$pdf_text_1?>" /> -
									<?php if(@$report_type_array[0] != 'projectStatusReport') { ?>
										<input type="text" class="width190 alignCenter" id="pdf_file_name_text_2" placeholder="Text 2" value="<?=@$pdf_text_2?>" /> - 
									<?php } ?>
									<input type="text" class="width100 alignCenter" value="<?=@$project->nickname?>" disabled />.pdf
								</div>
							</div>

							<div class="row marginTop20 alignCenter">
								<div class="col-12">
									<strong>כותרת 1</strong>
								</div>
							</div>
				
							<div class="row marginTop20 alignCenter">
								<div class="col-12">
									 <input type="text" class="width300 alignCenter" id="title1" placeholder="כותרת 1" value="<?=htmlspecialchars(@$title1)?>" />    
								</div>
							</div>
				
							<div class="row marginTop20 alignCenter">
								<div class="col-12">
									<strong>כותרת 2</strong>
								</div>
							</div>
				
							<div class="row marginTop20 alignCenter">
								<div class="col-12">
									 <input type="text" class="width300 alignCenter marginRight10" id="title2" placeholder="כותרת 2" value="<?=htmlspecialchars(@$title2)?>" />    
								</div>
							</div>
							
							<div class="row marginTop20 alignCenter">
								<div class="col-12">
									<strong>כיוון דף</strong>
								</div>
							</div>
				
							<div class="row marginTop10 alignCenter dir-rtl">
								<div class="col-12">
									<input type="radio" id="portrait" name="pdf_direction" value="P" checked />&nbsp; עומד
									<input type="radio" id="landscape" name="pdf_direction" value="L" />&nbsp; שוכב
								</div>
							</div>
								
							<div class="row title marginTop20 alignCenter">
								<a class="text-decoration-none marginLeft8 cursor-pointer" onclick="toTasksReport();">
									<img src="images/file-pdf-solid.svg" width="70" height="40" /><br/><span class="colorGrey fontSize13 font-weight-bold">DOWNLOAD</span>
									
								</a> 
							</div>
						</div>
					</div>
					<div class="col-1"></div>
				</div>		
			</div>
		</form>
		
		<form id="pdfDownloadForm" action="meetings_report.php" method="GET" style="display: none;" target="_self">
			<input type="hidden" name="project_id" id="pdf_project_id">
			<input type="hidden" name="is_specific_filter" id="pdf_is_specific_filter">
			<input type="hidden" name="all_ids_to_print" id="pdf_all_ids_to_print">
			<input type="hidden" name="pdf_direction" id="pdf_direction">
			<input type="hidden" name="mode" value="D">
       </form>
	</body>
</html>

<script>
$(document).ready(function(){	
	$("#pdf_date").change(function(){
	   let selectedDate = $(this).val();
	   $('#pdf_file_name_date').val(selectedDate.substring(0,4)+selectedDate.substring(5,7)+selectedDate.substring(8,10));  
	});
});

function toTasksReport(){
	let pdf_direction = 'L';
	if($('input[name="pdf_direction"]:checked').length > 0)
	  pdf_direction = $('input[name="pdf_direction"]:checked').val();
  
	let form_data = new FormData();	
	form_data.append('date',$('#pdf_file_name_date').val());
	form_data.append('text1',$('#pdf_file_name_text_1').val());
	form_data.append('text2',$('#pdf_file_name_text_2').val());
	form_data.append('title1',$('#title1').val());
	form_data.append('title2',$('#title2').val());
	
	$.ajax({
		type: 'POST',
		url: 'set_report_settings_sessions.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,			
		success: function(data){  
      	    let baseUrl = 'meetings_report.php';
            let params = '?project_id='+$('#project_id').val()
                          +'&is_specific_filter='+$('#is_specific_filter').val()
                          +'&all_ids_to_print='+$('#all_ids_to_print').val()
                          +'&pdf_direction='+pdf_direction
						  +'&lang='+$('#lang').val()
            window.open(baseUrl+params+'&mode=I','_blank');
			
			setTimeout(function (){
					$('#pdf_project_id').val($('#project_id').val());
					$('#pdf_is_specific_filter').val($('#is_specific_filter').val());
					$('#pdf_all_ids_to_print').val($('#all_ids_to_print').val());
					$('#pdf_direction').val(pdf_direction);			
					$('#pdfDownloadForm').submit();
				}, 1000);
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
</style>