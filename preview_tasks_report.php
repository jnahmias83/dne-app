<?php 
include 'include/header.php';
include 'functions/functions.php';

$project_id = @$_GET['project_id'];
$all_ids_to_print = @$_GET['all_ids_to_print'];
$all_ids_to_print_array = explode(',',$all_ids_to_print);
$period_new_task_filter = @$_GET['period_new_task_filter'];
$task_filter = @$_GET['task_filter'];
$progress_status_filter = @$_GET['progress_status_filter'];
$supplier_filter = @$_GET['supplier_filter'];
$is_specific_filter = @$_GET['is_specific_filter'];
$pdf_direction = @$_GET['pdf_direction'];

$query = $mysqli->prepare("SELECT * FROM dne_projects WHERE id = ?");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$project = fetch_unique($query);

$query = $mysqli->prepare("SELECT * FROM dne_global_bgcolor_new_task LIMIT 1");
$query->execute();
$query->store_result();
$global_bgcolor_new_task = fetch_unique($query);

$query = $mysqli->prepare("SELECT * FROM dne_log_current_report WHERE id_project = ?");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$log_custom_report = fetch_unique($query);
$id_custom_report = $log_custom_report->id_custom_report;
$id_rdv_report = $log_custom_report->id_rdv_report;

if($id_rdv_report > 0) { 
  $query = $mysqli->prepare("SELECT rdv_lang FROM dne_rdv WHERE id = ?");
  $query->bind_param("i",$id_rdv_report);
  $query->execute();
  $query->store_result();
  $query = fetch_unique($query);
  $rdv_lang = @$query->rdv_lang;
}

$query = $mysqli->prepare("SELECT * FROM dne_custom_reports WHERE id_project = ? ORDER BY is_project_status_report DESC");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$custom_reports_num_rows = $query->num_rows;
$custom_reports = fetch($query);

$columns_list_array = ['subject','area','description','_task','responsible','pass on','task creation','destination date','progress status'];

$title = '';

if($id_custom_report > 0) {
	$query = $mysqli->prepare("SELECT * FROM dne_custom_reports WHERE id = ?");
	$query->bind_param("i",$id_custom_report);
	$query->execute();
	$query->store_result();
		
	if($query->num_rows > 0) {
		$custom_report = fetch_unique($query);
		if(!$is_specific_filter) {
			$sql = @$custom_report->sql_str;
			$is_images = @$custom_report->is_images;
			$is_colors = @$custom_report->is_colors;
			$lang = @$custom_report->lang;
			$_SESSION['lang'] = $lang;
			$period_new_tasks = @$custom_report->period_new_tasks;
			$columns_list = @$custom_report->columns_list;	
		}
		else {
			$sql = @$_SESSION['filter_sql'];
			$is_images = @$_SESSION['filter_is_images'];
			$is_colors = @$_SESSION['filter_is_colors'];
			$lang = @$_SESSION['filter_lang'];
			$_SESSION['lang'] = $lang;	
            $period_new_tasks = @$_SESSION['filter_period_new_tasks'];			
			$columns_list = @$_SESSION['filter_columns_list'];
		}
	
		$id_responsibles_part = '';
		if(strpos($sql,"m.id_responsible")!== false) {
			$sql_array = explode(' AND ',$sql);
									
			for($i=1;$i<sizeof($sql_array);$i++) {
				if(strpos($sql_array[$i],'m.id_responsible') !== false) {
					$id_responsibles_part = $sql_array[$i];
					$id_responsibles_part = str_replace('m.id_responsible IN(','',$id_responsibles_part);
					$id_responsibles_part = substr($id_responsibles_part, 0, -1);
				}
			}
			
			$id_responsibles_part_array = explode('OR',$id_responsibles_part);
			$id_responsibles_part = $id_responsibles_part_array[0];
			$id_responsibles_part = str_replace('(','', $id_responsibles_part);
			$id_responsibles_part = str_replace(')','', $id_responsibles_part);
		}

		$id_progress_status_part = '';
		$id_progress_status_array = array();

		if(strpos($sql,"m.id_progress_status")!== false) {
			$sql_array = explode(' AND ',$sql);

			for($i=1;$i<sizeof($sql_array);$i++) {
				if(strpos($sql_array[$i],'m.id_progress_status') !== false) {
					$id_progress_status_part = $sql_array[$i];
					$id_progress_status_part = str_replace('m.id_progress_status IN(','',$id_progress_status_part);
					$id_progress_status_part = substr($id_progress_status_part, 0, -1);
					$id_progress_status_array = explode(',',$id_progress_status_part);
				}
			}
				
			for($i=0;$i<sizeof($id_progress_status_array);$i++) {
				$query = $mysqli->prepare("SELECT name_he FROM dne_progress_status WHERE id = ?");
				$query->bind_param("i",$id_progress_status_array[$i]);
				$query->execute();
				$query->store_result();
				$query = fetch_unique($query);
				
				if(@$query->name_he == 'ארכיון' && sizeof($id_progress_status_array) == 1) 
				   $sql = str_replace('is_appears = 1','is_appears = 0',$sql);
				else if(@$query->name_he == 'ארכיון' && sizeof($id_progress_status_array) > 1) 
				   $sql = str_replace('is_appears = 1','is_appears IN(0,1)',$sql);
		   }
		}	
				
		$query = $mysqli->prepare($sql);
	    $query->execute();
	    $query->store_result();
	    $all_meetings_num_rows = $query->num_rows;
        $all_meetings = fetch($query);
	    $all_meetings_with_images_num_rows = 0;
		
	    foreach($all_meetings as $item) {
			if($item->image1 != '' && $item->is_appears_img1)
			   $all_meetings_with_images_num_rows++;
	    }

		$_SESSION['id_responsibles_part'] = $id_responsibles_part;	
   }
}  
else if($id_rdv_report > 0) {
    if(!@$is_specific_filter) {
	   $is_images = 1;
	   $is_colors = 1;
	   $lang = @$rdv_lang;
	   $period_new_tasks = 0;
	   $columns_list = 'subject,area,description,_task,responsible,pass on,task creation,destination date,progress status';
	   
	   $is_appears = 1;
	   $query = $mysqli->prepare("SELECT * FROM dne_meetings WHERE is_appears = ? AND id_rdv = ?");
	   $query->bind_param("ii",$is_appears,$id_rdv_report);
	   $query->execute();
	   $query->store_result();
	   $all_meetings_num_rows = $query->num_rows;
       $all_meetings = fetch($query);
	   $all_meetings_with_images_num_rows = 0;
		
	   foreach($all_meetings as $item) {
			if($item->image1 != '' && $item->is_appears_img1)
			   $all_meetings_with_images_num_rows++;
	   }
    }
	else {
		$sql = @$_SESSION['filter_sql'];
		$is_images = @$_SESSION['filter_is_images'];
		$is_colors = @$_SESSION['filter_is_colors'];
		$lang = @$_SESSION['filter_lang'];
		$period_new_tasks = @$_SESSION['filter_period_new_tasks'];
		$columns_list = @$_SESSION['filter_columns_list'];
		
		$id_responsibles_part = '';
		if(strpos($sql,"m.id_responsible")!== false) {
			$sql_array = explode(' AND ',$sql);
									
			for($i=1;$i<sizeof($sql_array);$i++) {
				if(strpos($sql_array[$i],'m.id_responsible') !== false) {
					$id_responsibles_part = $sql_array[$i];
					$id_responsibles_part = str_replace('m.id_responsible IN(','',$id_responsibles_part);
					$id_responsibles_part = substr($id_responsibles_part, 0, -1);
				}
			}
			
			$id_responsibles_part_array = explode('OR',$id_responsibles_part);
			$id_responsibles_part = $id_responsibles_part_array[0];
			$id_responsibles_part = str_replace('(','', $id_responsibles_part);
			$id_responsibles_part = str_replace(')','', $id_responsibles_part);
		}

		$id_progress_status_part = '';
		$id_progress_status_array = array();

		if(strpos($sql,"m.id_progress_status")!== false) {
			$sql_array = explode(' AND ',$sql);

			for($i=1;$i<sizeof($sql_array);$i++) {
				if(strpos($sql_array[$i],'m.id_progress_status') !== false) {
					$id_progress_status_part = $sql_array[$i];
					$id_progress_status_part = str_replace('m.id_progress_status IN(','',$id_progress_status_part);
					$id_progress_status_part = substr($id_progress_status_part, 0, -1);
					$id_progress_status_array = explode(',',$id_progress_status_part);
				}
			}
				
			for($i=0;$i<sizeof($id_progress_status_array);$i++) {
				$query = $mysqli->prepare("SELECT name_he FROM dne_progress_status WHERE id = ?");
				$query->bind_param("i",$id_progress_status_array[$i]);
				$query->execute();
				$query->store_result();
				$query = fetch_unique($query);
				
				if($query->name_he == 'ארכיון' && sizeof($id_progress_status_array) == 1) 
				   $sql = str_replace('is_appears = 1','is_appears = 0',$sql);
				else if($query->name_he == 'ארכיון' && sizeof($id_progress_status_array) > 1) 
				   $sql = str_replace('is_appears = 1','is_appears IN(0,1)',$sql);
		   }
		}
		
		$query = $mysqli->prepare($sql);
	    $query->execute();
	    $query->store_result();
	    $all_meetings_num_rows = $query->num_rows;
        $all_meetings = fetch($query);
	    $all_meetings_with_images_num_rows = 0;
		
	    foreach($all_meetings as $item) {
			if($item->image1 != '' && $item->is_appears_img1)
			   $all_meetings_with_images_num_rows++;
	    }
	}
	
	$query = $mysqli->prepare("SELECT rdv.rdv_name AS rdv_name,rdv.rdv_persons AS rdv_persons,mt.name_he AS meeting_name_he 
                              FROM dne_rdv rdv 
							  LEFT JOIN dne_meetings_types mt ON rdv.id_meetings_types = mt.id
							  WHERE rdv.id = ?");
	$query->bind_param("i",$id_rdv_report);
	$query->execute();
	$query->store_result();
	$rdv = fetch_unique($query);
    $rdv_name = @$rdv->rdv_name;
	$meeting_name_he = @$rdv->meeting_name_he;
      
    $rdv_persons_array = explode(',',@$rdv->rdv_persons);
	
	$_SESSION['lang'] = $lang;
}

$project_name = '<span class="fontSize26 font-weight-bold font-family-david">פרוייקט '.htmlspecialchars($project->name_he).'</span>';
if($lang != 'HE')
  $project_name = '<span class="fontSize26 font-weight-bold">Project '.htmlspecialchars($project->name).'</span>';
if($_SESSION['pdf_title1'] != '')
  $title = '<span class="font-weight-bold">'.htmlspecialchars($_SESSION['pdf_title1']).'</span>';
if($_SESSION['pdf_title2'] != '')
  $title .=	'<br/><span class="fontSize23"><u>'.htmlspecialchars($_SESSION['pdf_title2']).'</u></span>';

$title .= '<br/><span class="fontSize23">'.substr($_SESSION['pdf_date'],6,2).'/'.substr($_SESSION['pdf_date'],4,2).'/'.substr($_SESSION['pdf_date'],0,4).'</span>';  
	
$columns_list_array = explode(',',$columns_list);

if(@$lang == 'HE') {
   $dir = 'rtl';
   $padding = 'padding-right';
   $text_align = 'text-align:right';
}
else {
	$dir = 'ltr';
	$padding = 'padding-left';
	$text_align = 'text-align:left';
}

$colspan_image_tr = sizeof($columns_list_array)+4;
	
$query = $mysqli->prepare("SELECT * FROM dne_responsibles WHERE id_project = ? ORDER BY name ASC");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$responsibles = fetch($query);

$query = $mysqli->prepare("SELECT * FROM dne_tasks WHERE id_project = ? ORDER BY name_he ASC");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$tasks = fetch($query);

$query = $mysqli->prepare("SELECT * FROM dne_progress_status WHERE id_project = ?");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$progress_status_s = fetch($query);

include 'menu_tasks.php';
?>
        <form method="post" action="" class="form-inline">
		    <input type="hidden" id="project_id" name="project_id" value="<?=@$project->id?>" />
			<input type="hidden" id="all_ids_to_print" name="all_ids_to_print" value="<?=@$all_ids_to_print?>" />
			<input type="hidden" id="is_specific_filter" value="<?=@$is_specific_filter?>" />
			<input type="hidden" id="pdf_direction" value="<?=@$pdf_direction?>" />
			
			<div class="container">
				<div class="row alignCenter marginTop25">
					<div class="col-md-12">
						<a href="projects.php"><img src="images/davidnahmias_logo.png" width="170px" height="170px" /></a>
					</div>
			    </div>
				
				<div class="row title marginTop15 dir-<?=@$dir?>">
                    <div class="col-md-12">
					    <a id="a_project_title" class="text-decoration-none color-1A5276 cursor-pointer" href="meetings.php?project_id=<?=@$project_id?>&task_filter=<?=@$task_filter?>&progress_status_filter=<?=@$progress_status_filter?>&supplier_filter=<?=@$supplier_filter?>&period_new_task_filter=<?=@$period_new_task_filter?>&is_specific_filter=<?=@$is_specific_filter?>">
						   <div class="fontSize33 line-height-1em"><strong><?=$project->nickname?></strong></div>
						   <div class="fontSize26"><?=@$project_name?></div>
					    </a>
						<div class="color-349feb fontSize26"><?=@$title?></div>
					</div>		
			    </div>	
				
				<div class="row title dir-<?=@$dir?> marginTop20 colorBlack">
					<div class="col-md-12">
						<a class="text-decoration-none marginLeft8 cursor-pointer" onclick="toTasksReport();">
						   <img src="images/file-pdf-solid.svg" width="70" height="40" />
						</a> 
				    </div>
				</div>
				
				<?php 
				if(@$id_rdv_report > 0) { ?>
					<div class="row marginTop20" style="<?=@$margin?>:1px;direction:<?=@$dir?>;">
				   	    <div class="col-md-12">
						    <strong><?=@getLang('participants')?>:</strong>
						</div>
					</div>
					
					<?php foreach($rdv_persons_array as $item) {
					    $query = $mysqli->prepare("SELECT r.name AS name,sfow.name AS sfow_name,sfow.name_he AS sfow_name_he
						 						 FROM dne_responsibles r
												 LEFT JOIN dne_projects_suppliers ps ON ps.id = r.id_projects_suppliers
												 LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
                                                 LEFT JOIN dne_sup_field_of_work sfow ON s.id_field_of_work = sfow.id													 
												 WHERE r.id = ?");
					    $query->bind_param("i",$item);
					    $query->execute();
					    $query->store_result();
					    $responsible = fetch_unique($query);
						$participants = '';
						
						if(@$responsible->sfow_name_he == '') {
						   if(@$responsible->sfow_name != '')
							  $participants .= @$responsible->sfow_name.' - ';
						}
						else
							$participants .= @$responsible->sfow_name_he.' - ';
						
						if(@$responsible->name != '')
						  $participants .= @$responsible->name;
						?>
						
						<div class="row marginRight1" style="direction:<?=@$dir?>;">
				   	       <div class="col-md-12">
						        <?=@$participants?> 
						   </div>
						</div>
						<?php
                    }
				}
				
				if(@$all_meetings_num_rows > 0) { ?>
					<div class="row marginTop20" style="direction:<?=@$dir?>;">
						<div class="col-md-12 mx-2">
							<table border="1">							
								<tr class="bgColorSilver height50">
									
									<?php if(in_array('subject',$columns_list_array)) { ?>
									    <th width="11%" class="alignCenter"><?=@getLang('subject_domain')?></th>
									<?php } 
									
									if(in_array('area',$columns_list_array)) { ?>    
									   <th width="11%" class="alignCenter"><?=@getLang('area_subject')?></th>
									<?php } 
									
									if(in_array('description',$columns_list_array)) { ?>    
									    <th class="alignCenter"><?=@getLang('description')?></th>
									<?php } 
									
									if(in_array('_task',$columns_list_array)) { ?>    
									   <th width="8%" class="alignCenter"><?=@getLang('task_type')?></th>	
									<?php }
									
								    if(in_array('responsible',$columns_list_array)) { ?>    
									   <th width="8%" class="alignCenter"><?=@getLang('responsible')?></th>
									<?php }
									
									if(in_array('pass on',$columns_list_array)) { ?>   
									   <th width="8%" class="alignCenter fontSize13"><?=@getLang('transfer_confirm')?></th>
									<?php }
									
									if(in_array('task creation',$columns_list_array)) { ?>   
								       <th width="8%" class="alignCenter"><?=@getLang('task_creation_date')?></th>
									<?php }
									
									if(in_array('destination date',$columns_list_array)) { ?>   
									   <th width="8%" class="alignCenter"><?=@getLang('destination_date')?></th>
									<?php }
									
									if(in_array('progress status',$columns_list_array)) { ?>   
									   <th width="8%" class="alignCenter"><?=@getLang('progress_status')?></th>
									<?php } ?>
								</tr>
								<?php
								if($id_custom_report > 0 || ($id_rdv_report > 0 && $is_specific_filter)) {
									$position_where = strpos($sql,"WHERE");
									$where_length = strlen($sql)-$position_where;
									$where_part_sql = substr($sql,$position_where,$where_length);
									$where_part_sql_array = explode(' AND ',$where_part_sql);
									
									$chapter_filter = '';
									if(strpos($where_part_sql,"m.id_chapter")!== false) {
										
										for($i=0;$i<sizeof($where_part_sql_array);$i++) {
											if(strpos($where_part_sql_array[$i],'m.id_chapter') !== false) {
												$where_part_sql_array[$i] = str_replace('m.id_chapter','id',$where_part_sql_array[$i]);
											}
										}
										
										$where_part_sql = implode(' AND ',$where_part_sql_array);
										$chapter_filter = $where_part_sql;
										$chapter_filter_array = explode('AND ',$chapter_filter);
										$chapter_filter = $chapter_filter_array[2];
										$chapter_filter = ' AND '.$chapter_filter;
									}
								
									$sql_chapters = "SELECT * FROM dne_chapters WHERE id_project = ? ".$chapter_filter.' ORDER BY id_display';
									$query = $mysqli->prepare($sql_chapters);
									$query->bind_param("i",$project_id);
									$query->execute();
									$query->store_result();
									$chapters = fetch($query);
								}
								else if($id_rdv_report > 0 && !$is_specific_filter) {
								   $query = $mysqli->prepare("SELECT * FROM dne_chapters WHERE id_project = ? ORDER BY id_display");
							       $query->bind_param("i",$project_id);
								   $query->execute(); 
								   $query->store_result();
								   $chapters = fetch($query);
								}
								
								
								foreach($chapters as $item) {
									$chapter_id = $item->id;
									$chapter_name = stripNbspArtifact($item->name);

									if($id_custom_report > 0 || ($id_rdv_report > 0 && $is_specific_filter)) {
									    $sql_array = explode(' AND ',$sql);
								
										for($i=0;$i<sizeof($sql_array);$i++) {
											if(strpos($sql_array[$i],'m.id_chapter') !== false && $i > 0) {
												$sql_array[$i] = 'm.id_chapter ='.$chapter_id;
												$sql = implode(' AND ',$sql_array);
											}
										}
										
										if(strpos($where_part_sql,'m.id_chapter') === false) 
										  $sql.= ' AND m.id_chapter ='.$chapter_id;
									  
										$query = $mysqli->prepare($sql);
										$query->execute();
										$query->store_result();
										$meetings = fetch($query);
										
										$counter_with_image = 0;
										foreach ($meetings as $item) {							
											if(@$all_ids_to_print == '' || in_array($item->id,$all_ids_to_print_array)) 
												$counter_with_image++;
										}
										
										$query = $mysqli->prepare($sql.' ORDER BY m.subject,m.id_area,t.name_he');
										$query->execute();
										$query->store_result();
										$meetings = fetch($query);
									}
									else if($id_rdv_report > 0 && !$is_specific_filter) {
										$is_appears = 1;
										
										$query = $mysqli->prepare('SELECT * FROM dne_meetings 
																 WHERE id_project = ? AND id_chapter = ? AND is_appears = ? 
																 AND id_rdv = ?');
										$query->bind_param("iiii",$project_id,$chapter_id,$is_appears,$id_rdv_report);
										$query->execute();
										$query->store_result();
										$meetings = fetch($query);
										
										$counter_with_image = 0;
										foreach ($meetings as $item) {							
											if(@$all_ids_to_print == '' || in_array($item->id,$all_ids_to_print_array)) 
												$counter_with_image++;
										}
										
									    $query = $mysqli->prepare("SELECT m.id AS id,m.id_chapter AS id_chapter,m.subject AS subject,m.id_rdv AS id_rdv,m.area AS area,
																  m.description AS description,m.id_task AS id_task ,m.id_responsible AS id_responsible,
																  m.id_pass_on AS id_pass_on,m.task_creation_date AS task_creation_date,m.destination_date AS destination_date,
																  m.id_progress_status AS id_progress_status,m.id_task_type AS id_task_type,m.is_change_row_style AS is_change_row_style,
																  m.image1 AS image1,m.is_appears_img1 AS is_appears_img1,
																  m.image2 AS image2,m.is_appears_img2 AS is_appears_img2,
																  m.updated_date AS updated_date
																  FROM dne_meetings m 
																  LEFT JOIN dne_tasks t ON m.id_task = t.id
																  WHERE m.id_project = ? AND m.id_chapter = ? AND m.is_appears = ? AND m.id_rdv = ?
																  ORDER BY m.subject,m.id_area,t.name_he");
										$query->bind_param("iiii",$project_id,$chapter_id,$is_appears,$id_rdv_report);
										$query->execute(); 
										$query->store_result();
										$meetings_num_rows = $query->num_rows;
										$meetings = fetch($query);
									}
									
									if($counter_with_image > 0) {
									?>
										<tr class="bgColorSkyblue height40">
										  <td colspan="14" style="<?=@$text_align?>;<?=@$padding?>:5px;">
											  <strong><?=@$chapter_name?></strong>
										  </td>
										</tr>
										<?php 
										$count = 0;
										foreach($meetings as $item) {
											$user_id = @$item->id_user;
											$task_id = @$item->id_task;
											$meeting_id = @$item->id;
											$id_rdv = @$item->id_rdv;
											$id_chapter = @$item->id_chapter;
											$id_task_type = @$item->id_task_type;
											$subject = stripNbspArtifact(@$item->subject);
											$area = stripNbspArtifact(@$item->area);
											$description = @$item->description;
											
											$change_status_label = 'שינוי סטטוס';
											$change_dest_date_label = 'דחיית תאריך יעד';
											$query = "SELECT id FROM dne_tasks_actions 
											          WHERE name_he IN('".$change_status_label."','".$change_dest_date_label."')";
											$query = $mysqli->prepare($query);   
											$query->execute();
											$query->store_result();
											$tasks_actions = fetch($query);
											
											$task_actions_ids = ' IN(';
											foreach($tasks_actions as $ta) {
										    	$task_actions_ids.= $ta->id.',';
											}
											
											$task_actions_ids = substr($task_actions_ids,0,-1);
											$task_actions_ids .= ') ';
											
											$reminder_label = 'תזכורת';
											$query = "SELECT id FROM dne_tasks_actions WHERE name_he = ?";
											$query = $mysqli->prepare($query);
											$query->bind_param('s',$reminder_label);   
											$query->execute();
											$query->store_result();
											$query = fetch_unique($query);
											$id_task_action_remark = @$query->id;
											
											$is_remark_appears_html = 1;										
											$empty_char = '';
											
											$query = $mysqli->prepare("SELECT * FROM dne_tasks_followup 
											                          WHERE id_meeting = ? 
																	  AND is_remark_appears_html = ?
                                                                      AND remark <> ?
																	  AND id_task_action".@$task_actions_ids. 
																	  "ORDER BY id DESC LIMIT 1");
											$query->bind_param("iis",$meeting_id,$is_remark_appears_html,$empty_char);
											$query->execute();
											$query->store_result();	
											$query = fetch_unique($query);
											$remark_changes_status = @$query->remark;	
											$action_date = @$query->action_date;
											
											$query = $mysqli->prepare("SELECT * FROM dne_tasks_followup 
											                          WHERE id_meeting = ? 
																	  AND is_remark_appears_html = ?
                                                                      AND remark <> ?
																	  AND id_task_action = ? 
																	  ORDER BY id DESC LIMIT 1");
											$query->bind_param("iisi",$meeting_id,$is_remark_appears_html,$empty_char,$id_task_action_remark);
											$query->execute();
											$query->store_result();	
											$query = fetch_unique($query);
											$remark = @$query->remark;	
											$action_date_remark = @$query->action_date;
											
											if(@$remark_changes_status != '')
                                               $description.= "<div class='marginTop5 colorGreen'>[".smartDate(@$action_date, $lang).'] - '.@$remark_changes_status.'</div>';
											
											if(@$remark != '')
                                               $description.= "<div class='marginTop5 colorRed'>[".smartDate(@$action_date_remark, $lang).'] - '.@$remark.'</div>';
											
											$responsible_id = @$item->id_responsible;
											$pass_on_id = @$item->id_pass_on;
											
											$image1 = @$item->image1;
											$is_appears_img1 = @$item->is_appears_img1;
											
											$bgcolor_num = 'white';
											
											if(@$is_colors && @$item->image1 <> '' && @$item->is_appears_img1) {
											   $bgcolor_num = 'green';
											}
											
											if(@$is_colors && @$item->image1 <> '' && !@$item->is_appears_img1) {
											  $bgcolor_num = '#C9FFC2';
											}
											
											$is_change_row_style = @$item->is_change_row_style;
											
											$update_cell_bgcolor = 'background-color:white';
											
											$task_creation_date = '';
											if(@$item->task_creation_date != '0000-00-00')
												$task_creation_date = @$item->task_creation_date;
											
											$destination_date = '';
											if(@$item->destination_date != '0000-00-00')
												$destination_date = @$item->destination_date;
											
											$progress_status_id = @$item->id_progress_status;
											
											$is_appears_img1 = @$item->is_appears_img1;
											
											$updated_date = @$item->updated_date;
								
											$subject_bg_color = 'background-color:white';
											
											$area_bg_color = 'background-color:white';
											 
											$description_bg_color = 'background-color:white';
											
											$query = $mysqli->prepare("SELECT * FROM dne_tasks WHERE id = ?");
											$query->bind_param("i",$item->id_task);
											$query->execute();
											$query->store_result();
											$query = fetch_unique($query);
											$task = @$query->name_he;
											if($lang == 'EN')
											   $task = @$query->name;
											if(@$is_colors) {	
											   $task_color = @$query->color;
											   $task_bgcolor = @$query->bgcolor;
											}
											
											$query = $mysqli->prepare("SELECT * FROM dne_responsibles WHERE id = ?");
											$query->bind_param("i",$item->id_responsible);
											$query->execute();
											$query->store_result();
											$query = fetch_unique($query);
											$responsible = @$query->name;
											if(@$is_colors) {	
											   $responsible_color = @$query->color;
										       $responsible_bgcolor = @$query->bgcolor;
											}
											
											$query = $mysqli->prepare("SELECT * FROM dne_responsibles WHERE id = ?");
											$query->bind_param("i",$item->id_pass_on);
											$query->execute();
											$query->store_result();
											$query = fetch_unique($query);
											$pass_on = @$query->name;
											$pass_on_bg_color = 'background-color:white';
											
											$query = $mysqli->prepare("SELECT * FROM dne_progress_status WHERE id = ?");
											$query->bind_param("i",$item->id_progress_status);
											$query->execute();
											$query->store_result();
											$query = fetch_unique($query);
											$progress_status = @$query->name_he;
											if($lang == 'EN')
											   $progress_status = @$query->name;
											if(@$is_colors) {	
											   $progress_status_color = @$query->color;
											   $progress_status_bgcolor = @$query->bgcolor;
											}
											
											$task_creation_date_color = 'color:black';
											if(@$is_colors && $id_rdv > 0) 
												$task_creation_date_color = 'color:green';
											
											$task_creation_date_bg_color = 'background-color:white';
											
											$dest_date_color = 'color:black';
											$dest_date_bg_color = 'background-color:white';
											
											if(@$is_colors && $destination_date < date('Y-m-d')) { 
											   $dest_date_color = 'color:red;';
											}
											
											if(@$is_colors && $is_change_row_style) {
												if($progress_status == 'בוצע/נמסר') {
												   $subject_bg_color = 'background-color:#dedede';
												   $area_bg_color = 'background-color:#dedede';
												   $description_bg_color = 'background-color:#dedede';
												   $task_bgcolor = '#dedede';
												   $responsible_bgcolor = '#dedede';
												   $pass_on_bg_color = 'background-color:#dedede';
												   $task_creation_date_bg_color = 'background-color:#dedede';
												   $dest_date_color = 'color:#dedede';
												   $dest_date_bg_color = 'background-color:#dedede';
												   $progress_status_bgcolor = '#dedede';
												}
												else if($task == 'בקרת איכות') {
												   $subject_bg_color = 'background-color:#fafd49';
												   $area_bg_color = 'background-color:#fafd49';
												   $description_bg_color = 'background-color:#fafd49';
												}
												else 
													$dest_date_color = 'color:white';
											}
											
											if(@$period_new_tasks == 'today')
												$end_new_tasks_date = $task_creation_date;
											else if(@$period_new_tasks == 'three_days')
												$end_new_tasks_date = date('Y-m-d',strtotime($task_creation_date . '+3 days'));
											else if(@$period_new_tasks == 'one_week')
												$end_new_tasks_date = date('Y-m-d',strtotime($task_creation_date . '+7 days'));
											else if(@$period_new_tasks == 'two_weeks')
												$end_new_tasks_date = date('Y-m-d',strtotime($task_creation_date . '+14 days'));
											else if(@$period_new_tasks == 'one_month')
												$end_new_tasks_date = date('Y-m-d',strtotime($task_creation_date . '+1 month'));
											else if(@$period_new_tasks == 'two_months')
												$end_new_tasks_date = date('Y-m-d',strtotime($task_creation_date . '+2 months'));
											else if(@$period_new_tasks == 'one_year')
												$end_new_tasks_date = date('Y-m-d',strtotime($task_creation_date . '+1 year'));
											else if(@$period_new_tasks == 'two_years')
												$end_new_tasks_date = date('Y-m-d',strtotime($task_creation_date . '+2 years'));
											
											if(strlen(@$period_new_tasks) > 1 && (date('Y-m-d') <= @$end_new_tasks_date) && (@$progress_status != 'בוצע/נמסר') && (@$task != 'בקרת איכות')) {
												$update_cell_bgcolor = 'background-color:'.$global_bgcolor_new_task->bgcolor;
												$subject_bg_color = 'background-color:'.$global_bgcolor_new_task->bgcolor;
												$area_bg_color = 'background-color:'.$global_bgcolor_new_task->bgcolor;
												$description_bg_color = 'background-color:'.$global_bgcolor_new_task->bgcolor;
												$pass_on_bg_color = 'background-color:'.$global_bgcolor_new_task->bgcolor;
												$task_creation_date_bg_color = 'background-color:'.$global_bgcolor_new_task->bgcolor;
												$dest_date_bg_color = 'background-color:'.$global_bgcolor_new_task->bgcolor;
											}
											$count++;  
                                            if(@$all_ids_to_print == '' || in_array($meeting_id,$all_ids_to_print_array)) {											
											?>
											<tr class="fontSize11">		
												<?php if(in_array('subject',$columns_list_array)) { ?>
													<td style="<?=@$text_align?>;<?=@$padding?>:5px;<?=@$subject_bg_color?>;">
														<?=@$subject?>
													</td>
												<?php }

												if(in_array('area',$columns_list_array)) { ?> 
													<td style="<?=@$text_align?>;<?=@$padding?>:5px;<?=@$area_bg_color?>;">
														<?=@$area?>
													</td>
												<?php } 
												
												if(in_array('description',$columns_list_array)) { ?>
												    <td style="<?=@$text_align?>;<?=@$padding?>:5px;<?=@$description_bg_color?>;"><?=html_entity_decode(nl2br(@$description))?></td>
												<?php } 
												
												if(in_array('_task',$columns_list_array)) { ?>
													<td style="<?=@$text_align?>;<?=@$padding?>:5px;background-color:<?=@$task_bgcolor?>">
														<?=@$task?>	
													</td>
											    <?php }
												
												if(in_array('responsible',$columns_list_array)) { ?>
													<td style="<?=@$text_align?>;<?=@$padding?>:5px;background-color:<?=@$responsible_bgcolor?>;">
														<?=@$responsible?>
													</td>
												<?php } 
												
												if(in_array('pass on',$columns_list_array)) { ?>
													<td style="<?=@$text_align?>;<?=@$padding?>:5px;<?=@$pass_on_bg_color?>;">
													    <?=@$pass_on?>
													</td>
												<?php } 
												
												if(in_array('task creation',$columns_list_array)) { ?>
													<td style="<?=@$text_align?>;<?=@$padding?>:5px;<?=@$task_creation_date_bg_color?>;">
														<?=smartDate(@$task_creation_date, $lang)?>
													</td>
												<?php }
												
												if(in_array('destination date',$columns_list_array)) { ?>
													<td style="<?=@$text_align?>;<?=@$padding?>:5px;<?=@$dest_date_color?>;<?=@$dest_date_bg_color?>;">		
														<?=smartDate(@$destination_date, $lang)?>
													</td>
												<?php }
												
												if(in_array('progress status',$columns_list_array)) { ?>
													<td style="<?=@$text_align?>;<?=@$padding?>:5px;color:<?=@$progress_status_color?>;background-color:<?=@$progress_status_bgcolor?>;">
                                                        <?=simplifyStatusLabel(@$progress_status)?>
													</td>
												<?php } ?>		
											</tr>
											<?php 
											if(@$is_images == 1 && $image1 != '' && $is_appears_img1 && strpos($image1,'Snag') === false) { ?>
											    <tr><td colspan="<?=@$colspan_image_tr?>"><img src="<?='uploads/'.$image1?>" height="100" width="100" class="object-fit-fixed" /></td></tr>
											<?php } }
										}
									}
								}
								?>
							</table>		
						</div>
					</div>
					
					<?php } 
					if(@$is_images == 2 && @$all_meetings_with_images_num_rows > 0) { ?>
						<div class="row marginTop20" style="direction:<?=@$dir?>;">
							<h3 class="colorBlack"><?=@getLang('images_concentration')?></h3>
							<div class="col-md-12 alignCenter mx-2">
								<table border="1">							
									<tr class="bgColorSilver height50">
										<?php if(in_array('subject',$columns_list_array)) { ?>
											<th width="11%" class="alignCenter"><?=@getLang('subject_domain')?></th>
										<?php } 
										
										if(in_array('area',$columns_list_array)) { ?>    
										   <th width="11%" class="alignCenter"><?=@getLang('area_subject')?></th>
										<?php } 
										
										if(in_array('description',$columns_list_array)) { ?>    
											<th class="alignCenter fontSize13"><?=@getLang('description')?></th>
										<?php } 
										
										if(in_array('_task',$columns_list_array)) { ?>    
										   <th width="8%" class="alignCenter"><?=@getLang('task_type')?></th>	
										<?php }
										
										if(in_array('responsible',$columns_list_array)) { ?>    
										   <th width="8%" class="alignCenter"><?=@getLang('responsible')?></th>
										<?php }
										
										if(in_array('pass on',$columns_list_array)) { ?>   
										   <th width="8%" class="alignCenter fontSize13"><?=@getLang('transfer_confirm')?></th>
										<?php }
										
										if(in_array('task creation',$columns_list_array)) { ?>   
										   <th width="8%" class="alignCenter"><?=@getLang('task_creation_date')?></th>
										<?php }
										
										if(in_array('destination date',$columns_list_array)) { ?>   
										   <th width="8%" class="alignCenter"><?=@getLang('destination_date')?></th>
										<?php }
										
										if(in_array('progress status',$columns_list_array)) { ?>   
										   <th width="8%" class="alignCenter"><?=@getLang('progress_status')?></th>
										<?php } ?>
								    </tr>
									<?php
									$iteration = 0;
									
									foreach($chapters as $item) {
										$chapter_id = $item->id;
										$chapter_name = stripNbspArtifact($item->name);
										
										if($id_custom_report > 0 || ($id_rdv_report > 0 && $is_specific_filter)) {
											$sql_array = explode(' AND ',$sql);							
									
											for($i=0;$i<sizeof($sql_array);$i++) {
												if(strpos($sql_array[$i],'m.id_chapter') !== false && $i > 0) {
													$sql_array[$i] = 'm.id_chapter ='.$chapter_id;
													$sql = implode(' AND ',$sql_array);
												}
											}
											
											if(strpos($where_part_sql,'m.id_chapter') === false) 
											  $sql.= ' AND m.id_chapter ='.$chapter_id;
											
											$query = $mysqli->prepare($sql);
											$query->execute();
											$query->store_result();
											$meetings = fetch($query);
																			
											$counter_with_image = 0;
											foreach ($meetings as $item) {							
												if((@$all_ids_to_print == '' || in_array($item->id,$all_ids_to_print_array)) && ($item->image1 != '' && $item->is_appears_img1 == 1)) 
													$counter_with_image++;
											}																
											
											$query = $mysqli->prepare($sql." ORDER BY m.subject,m.id_area,t.name_he");
											$query->execute();
											$query->store_result();
											$meetings = fetch($query);	
										}
										else if($id_rdv_report > 0 && !$is_specific_filter) {
											$is_appears = 1;
											
											$query = $mysqli->prepare('SELECT * FROM dne_meetings 
																	  WHERE id_project = ? AND id_chapter = ? AND is_appears = ? 
																	  AND id_rdv = ?');
											$query->bind_param("iiii",$project_id,$chapter_id,$is_appears,$id_rdv_report);
											$query->execute();
											$query->store_result();
											$meetings = fetch($query);
											
											$counter_with_image = 0;
											foreach ($meetings as $item) {							
												if((@$all_ids_to_print == '' || in_array($item->id,$all_ids_to_print_array)) && ($item->image1 != '' && $item->is_appears_img1)) 
													$counter_with_image++;
											}
											
											$query = $mysqli->prepare("SELECT m.id AS id,m.id_chapter AS id_chapter,m.subject AS subject,m.id_rdv AS id_rdv,m.area AS area,
																	  m.description AS description,m.id_task AS id_task ,m.id_responsible AS id_responsible,
																	  m.id_pass_on AS id_pass_on,m.task_creation_date AS task_creation_date,m.destination_date AS destination_date,
																	  m.id_progress_status AS id_progress_status,m.id_task_type AS id_task_type,m.is_change_row_style AS is_change_row_style,
																	  m.image1 AS image1,m.is_appears_img1 AS is_appears_img1,
																	  m.image2 AS image2,m.is_appears_img2 AS is_appears_img2,
																	  m.updated_date AS updated_date
																	  FROM dne_meetings m 
																	  LEFT JOIN dne_tasks t ON m.id_task = t.id
																	  WHERE m.id_project = ? AND m.id_chapter = ? AND m.is_appears = ? AND m.id_rdv = ?				
																	  ORDER BY m.subject,m.id_area,t.name_he");
											$query->bind_param("iiii",$project_id,$chapter_id,$is_appears,$id_rdv_report);
											$query->execute(); 
											$query->store_result();
											$meetings = fetch($query);
										}
										
										if($counter_with_image > 0) {
										?>
											<tr class="bgColorSkyblue height40">
											  <td colspan="14" style="<?=@$text_align?>;<?=@$padding?>:5px;">
												 <strong><?=@$chapter_name?></strong>
											  </td>
											</tr>
											<?php 
											$count = 0;
											foreach($meetings as $item) {
												$user_id = @$item->id_user;
												$task_id = @$item->id_task;
												$meeting_id = @$item->id;
												$id_rdv = @$item->id_rdv;
												$id_chapter = @$item->id_chapter;
												$id_task_type = @$item->id_task_type;
												$subject = stripNbspArtifact(@$item->subject);
												$area = stripNbspArtifact(@$item->area);
												$description = @$item->description;
												
												$change_status_label = 'שינוי סטטוס';
												$change_dest_date_label = 'דחיית תאריך יעד';
												$query = "SELECT id FROM dne_tasks_actions 
														  WHERE name_he IN('".$change_status_label."','".$change_dest_date_label."')";
												$query = $mysqli->prepare($query);   
												$query->execute();
												$query->store_result();
												$tasks_actions = fetch($query);
												
												$task_actions_ids = ' IN(';
												foreach($tasks_actions as $ta) {
													$task_actions_ids.= $ta->id.',';
												}
												
												$task_actions_ids = substr($task_actions_ids,0,-1);
												$task_actions_ids .= ') ';
												
												$reminder_label = 'תזכורת';
												$query = "SELECT id FROM dne_tasks_actions WHERE name_he = ?";
												$query = $mysqli->prepare($query);
												$query->bind_param('s',$reminder_label);   
												$query->execute();
												$query->store_result();
												$query = fetch_unique($query);
												$id_task_action_remark = @$query->id;
												
												$is_remark_appears_html = 1;										
												$empty_char = '';
												
												$query = $mysqli->prepare("SELECT * FROM dne_tasks_followup 
																		  WHERE id_meeting = ? 
																		  AND is_remark_appears_html = ?
																		  AND remark <> ?
																		  AND id_task_action".@$task_actions_ids. 
																		  "ORDER BY id DESC LIMIT 1");
												$query->bind_param("iis",$meeting_id,$is_remark_appears_html,$empty_char);
												$query->execute();
												$query->store_result();	
												$query = fetch_unique($query);
												$remark_changes_status = @$query->remark;	
												$action_date = @$query->action_date;
											
												$query = $mysqli->prepare("SELECT * FROM dne_tasks_followup 
																		  WHERE id_meeting = ? 
																		  AND is_remark_appears_html = ?
																		  AND remark <> ?
																		  AND id_task_action = ? 
																		  ORDER BY id DESC LIMIT 1");
												$query->bind_param("iisi",$meeting_id,$is_remark_appears_html,$empty_char,$id_task_action_remark);
												$query->execute();
												$query->store_result();	
												$query = fetch_unique($query);
												$remark = @$query->remark;	
												$action_date_remark = @$query->action_date;
											
												if(@$remark_changes_status != '')
												   $description.= "<div class='marginTop5 colorGreen'>(".smartDate(@$action_date, $lang).') - '.@$remark_changes_status.'</div>';
												
												if(@$remark != '')
												   $description.= "<div class='marginTop5 colorRed'>(".smartDate(@$action_date_remark, $lang).') - '.@$remark.'</div>';
												
												$responsible_id = @$item->id_responsible;
												$pass_on_id = @$item->id_pass_on;
												
												$image1 = @$item->image1;
												$is_appears_img1 = @$item->is_appears_img1;
												
												$bgcolor_num = 'white';
												
												if(@$is_colors && @$item->image1 != '' && @$item->is_appears_img1) {
												   $bgcolor_num = 'green';
												}
												
												if(@$is_colors && @$item->image1 != '' && !@$item->is_appears_img1) {
												  $bgcolor_num = '#C9FFC2';
												}
												
												$is_change_row_style = @$item->is_change_row_style;
												
												$update_cell_bgcolor = 'background-color:white';
												
												$task_creation_date = '';
												if(@$item->task_creation_date != '0000-00-00')
													$task_creation_date = @$item->task_creation_date;
												
												$destination_date = '';
												if(@$item->destination_date != '0000-00-00')
													$destination_date = @$item->destination_date;
												
												$progress_status_id = @$item->id_progress_status;
												
												$is_appears_img1 = @$item->is_appears_img1;
												
												$updated_date = @$item->updated_date;
									
												$subject_bg_color = 'background-color:white';
												
												$area_bg_color = 'background-color:white';
												 
												$description_bg_color = 'background-color:white';
												
												$query = $mysqli->prepare("SELECT * FROM dne_tasks WHERE id = ?");
												$query->bind_param("i",$item->id_task);
												$query->execute();
												$query->store_result();
												$query = fetch_unique($query);
												$task = @$query->name_he;
												if($lang == 'EN')
													$task = @$query->name;
												if(@$is_colors) {
												  $task_color = @$query->color;
												  $task_bgcolor = @$query->bgcolor;
												}
												
												$query = $mysqli->prepare("SELECT * FROM dne_responsibles WHERE id = ?");
												$query->bind_param("i",$item->id_responsible);
												$query->execute();
												$query->store_result();
												$query = fetch_unique($query);
												$responsible = @$query->name;
												if(@$is_colors) {
												   $responsible_color = @$query->color;
												   $responsible_bgcolor = @$query->bgcolor;
												}
												
												$query = $mysqli->prepare("SELECT * FROM dne_responsibles WHERE id = ?");
												$query->bind_param("i",$item->id_pass_on);
												$query->execute();
												$query->store_result();
												$query = fetch_unique($query);
												$pass_on = @$query->name;
												$pass_on_bg_color = 'background-color:white';
												
												$query = $mysqli->prepare("SELECT * FROM dne_progress_status WHERE id = ?");
												$query->bind_param("i",$item->id_progress_status);
												$query->execute();
												$query->store_result();
												$query = fetch_unique($query);
												$progress_status = @$query->name_he;
												if($lang == 'EN')
													$progress_status = @$query->name;
												if(@$is_colors) {
												   $progress_status_color = @$query->color;
												   $progress_status_bgcolor = @$query->bgcolor;
												}
												
												$task_creation_date_color = 'color:black';
												if(@$is_colors && $id_rdv > 0) 
													$task_creation_date_color = 'color:green';
												
												$task_creation_date_bg_color = 'background-color:white';
												
												$dest_date_color = 'color:black';
												$dest_date_bg_color = 'background-color:white';
												
												if(@$is_colors && $destination_date < date('Y-m-d')) { 
												   $dest_date_color = 'color:red;';
												}
												
												if(@$is_colors && $is_change_row_style) {
													if($progress_status == 'בוצע/נמסר') {
													   $subject_bg_color = 'background-color:#dedede';
													   $area_bg_color = 'background-color:#dedede';
													   $description_bg_color = 'background-color:#dedede';
													   $task_bgcolor = '#dedede';
													   $responsible_bgcolor = '#dedede';
													   $pass_on_bg_color = 'background-color:#dedede';
													   $task_creation_date_bg_color = 'background-color:#dedede';
													   $dest_date_color = 'color:#dedede';
													   $dest_date_bg_color = 'background-color:#dedede';
													   $progress_status_bgcolor = '#dedede';
													}
													else if($task == 'בקרת איכות') {
													   $subject_bg_color = 'background-color:#fafd49';
													   $area_bg_color = 'background-color:#fafd49';
													   $description_bg_color = 'background-color:#fafd49';
													}
													else 
														$dest_date_color = 'color:white';
												}
												
												if(@$period_new_tasks == 'today')
												   $end_new_tasks_date = $task_creation_date;
										    	else if(@$period_new_tasks == 'three_days')
												   $end_new_tasks_date = date('Y-m-d',strtotime($task_creation_date . '+3 days'));
												else if(@$period_new_tasks == 'one_week')
													$end_new_tasks_date = date('Y-m-d',strtotime($task_creation_date . '+7 days'));
												else if(@$period_new_tasks == 'two_weeks')
													$end_new_tasks_date = date('Y-m-d',strtotime($task_creation_date . '+14 days'));
												else if(@$period_new_tasks == 'one_month')
													$end_new_tasks_date = date('Y-m-d',strtotime($task_creation_date . '+1 month'));
												else if(@$period_new_tasks == 'two_months')
													$end_new_tasks_date = date('Y-m-d',strtotime($task_creation_date . '+2 months'));
												else if(@$period_new_tasks == 'one_year')
													$end_new_tasks_date = date('Y-m-d',strtotime($task_creation_date . '+1 year'));
												else if(@$period_new_tasks == 'two_years')
													$end_new_tasks_date = date('Y-m-d',strtotime($task_creation_date . '+2 years'));
											
												if(strlen(@$period_new_tasks) > 1 && (date('Y-m-d') <= @$end_new_tasks_date) && (@$progress_status != 'בוצע/נמסר') && (@$task != 'בקרת איכות')) {
													$update_cell_bgcolor = 'background-color:'.$global_bgcolor_new_task->bgcolor;
													$subject_bg_color = 'background-color:'.$global_bgcolor_new_task->bgcolor;
													$area_bg_color = 'background-color:'.$global_bgcolor_new_task->bgcolor;
													$description_bg_color = 'background-color:'.$global_bgcolor_new_task->bgcolor;
													$pass_on_bg_color = 'background-color:'.$global_bgcolor_new_task->bgcolor;
													$task_creation_date_bg_color = 'background-color:'.$global_bgcolor_new_task->bgcolor;
													$dest_date_bg_color = 'background-color:'.$global_bgcolor_new_task->bgcolor;
												}
												$count++;
												if((@$all_ids_to_print == '' || in_array($meeting_id,$all_ids_to_print_array)) && (@$item->image1 != '' && @$item->is_appears_img1)) { ?>
												<tr class="fontSize11">
													<?php if(in_array('subject',$columns_list_array)) { ?>
														<td style="<?=@$text_align?>;<?=@$padding?>:5px;<?=@$subject_bg_color?>;">
															<?=@$subject?>
														</td>
													<?php }

													if(in_array('area',$columns_list_array)) { ?> 
														<td style="<?=@$text_align?>;<?=@$padding?>:5px;<?=@$area_bg_color?>;">
															<?=@$area?> 
														</td>
													<?php } 
													
													if(in_array('description',$columns_list_array)) { ?>
														<td style="<?=@$text_align?>;<?=@$padding?>:5px;<?=@$description_bg_color?>"><?=html_entity_decode(nl2br(@$description))?></td>
													<?php } 
													
													if(in_array('_task',$columns_list_array)) { ?>
														<td style="<?=@$text_align?>;<?=@$padding?>:5px;background-color:<?=@$task_bgcolor?>">														
														    <?=@$task?>					
														</td>
													<?php }
													
													if(in_array('responsible',$columns_list_array)) { ?>
														<td style="<?=@$text_align?>;<?=@$padding?>:5px;background-color:<?=@$responsible_bgcolor?>;">
															<?=@$responsible?>
														</td>
													<?php } 
													
													if(in_array('pass on',$columns_list_array)) { ?>
														<td style="<?=@$text_align?>;<?=@$padding?>:5px;<?=@$pass_on_bg_color?>;">
    														<?=@$pass_on?>			
														</td>
													<?php } 
													
													if(in_array('task creation',$columns_list_array)) { ?>
														<td style="<?=@$text_align?>;<?=@$padding?>:5px;<?=@$task_creation_date_bg_color?>;">
															<?=smartDate(@$task_creation_date, $lang)?>
														</td>
													<?php }
													
													if(in_array('destination date',$columns_list_array)) { ?>
														<td style="<?=@$text_align?>;<?=@$padding?>:5px;<?=@$dest_date_color?>;<?=@$dest_date_bg_color?>;">		
															<?=smartDate(@$destination_date, $lang)?>
													<?php }
													
													if(in_array('progress status',$columns_list_array)) { ?>
														<td style="<?=@$text_align?>;<?=@$padding?>:5px;color:<?=@$progress_status_color?>;background-color:<?=@$progress_status_bgcolor?>;">
															<?=simplifyStatusLabel(@$progress_status)?>
														</td>
													<?php } ?>
													
								                </tr>
												<?php if(strpos($image1,'Snag') === false) { ?>
												       <tr><td colspan="<?=sizeof(@$columns_list_array)?>"><img src="<?='uploads/'.$image1?>" height="100" width="100" class="object-fit-fixed" /></td></tr>
												<?php } }
											}
										}
									}
									?>
								</table>		
							</div>
							
						</div>
					<?php }	?>
				</div>
		</form> 
	</body>
</html>

<script>
function toTasksReport() {
	let form_data = new FormData();
    form_data.append('id_project',$('#project_id').val());
	form_data.append('from','previewTasksReport');
	
	$.ajax({
		type: 'POST',
		url: 'last_pdf_data_update.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,			
		success: function(data){  
           window.open('meetings_report.php?project_id='+$('#project_id').val()+'&is_specific_filter='+$('#is_specific_filter').val()+'&all_ids_to_print='+$('#all_ids_to_print').val()+'&pdf_direction='+$('#pdf_direction').val(),'_blank');
		},
	});
}
</script>

<style>
#a_project_title:hover {
	color: grey;
}
</style>