<?php
include 'functions/functions.php';

$reports_definitions = [
	[
		'request_name' => 'כלל המשימות',
		'pdf_name' => 'General Tasks Report',
		'title' => 'כלל המשימות',
		'is_project_status_report' => 1,
		'extra_where' => '',
		'needs_pass_on_join' => false
	],
	[
		'request_name' => 'משימות שלי',
		'pdf_name' => 'My Tasks Report',
		'title' => 'משימות שלי',
		'is_project_status_report' => 0,
		'extra_where' => ' AND r.id_user = {CURRENT_USER_ID}',
		'needs_pass_on_join' => false
	],
	[
		'request_name' => 'צוות ניהול',
		'pdf_name' => 'Management Team Report',
		'title' => 'צוות ניהול',
		'is_project_status_report' => 0,
		'extra_where' => " AND r.role IN('project_manager','inspector')",
		'needs_pass_on_join' => false
	],
	[
		'request_name' => 'צוות יזם',
		'pdf_name' => 'Entrepreneur Team Report',
		'title' => 'צוות יזם',
		'is_project_status_report' => 0,
		'extra_where' => " AND (r.role IN('entrepreneur','entrepreneur_team') OR rp.role IN('entrepreneur','entrepreneur_team'))",
		'needs_pass_on_join' => true
	],
	[
		'request_name' => 'במעקב',
		'pdf_name' => 'Tracking Report',
		'title' => 'במעקב',
		'is_project_status_report' => 0,
		'extra_where' => ' AND m.track_type = 1 AND m.id_track_responsible <> 0',
		'needs_pass_on_join' => false
	],
];

$columns_list = 'subject,area,description,_task,responsible,pass on,task creation,destination date,progress status';
$is_all_chapters_checked = $is_all_responsibles_checked = $is_all_pass_ons_checked = 1;
$is_images = $is_colors = 1;
$and_or_responsibles = 'AND';

$projects_query = $mysqli->prepare("SELECT id, lang FROM dne_projects");
$projects_query->execute();
$projects_query->store_result();
$projects = fetch($projects_query);
$total_projects = $projects_query->num_rows;

$created = 0;
$updated = 0;

foreach($projects as $project){
	$project_id = $project->id;
	$lang = !empty($project->lang) ? $project->lang : 'HE';

	$chapters_ids_array = [];
	$q = $mysqli->prepare("SELECT id FROM dne_chapters WHERE id_project = ?");
	$q->bind_param('i', $project_id);
	$q->execute();
	$q->store_result();
	foreach(fetch($q) as $c) if(@$c->id != '') $chapters_ids_array[] = $c->id;

	$tasks_ids_array = [];
	$q = $mysqli->prepare("SELECT id FROM dne_tasks WHERE id_project = ?");
	$q->bind_param('i', $project_id);
	$q->execute();
	$q->store_result();
	foreach(fetch($q) as $t) if(@$t->id != '') $tasks_ids_array[] = $t->id;

	$responsibles_ids_array = [];
	$q = $mysqli->prepare("SELECT id FROM dne_responsibles WHERE id_project = ?");
	$q->bind_param('i', $project_id);
	$q->execute();
	$q->store_result();
	foreach(fetch($q) as $r) if(@$r->id != '') $responsibles_ids_array[] = $r->id;
	$pass_ons_ids_array = $responsibles_ids_array;

	$progress_status_ids_array = [];
	$q = $mysqli->prepare("SELECT id, name_he, name FROM dne_progress_status WHERE id_project = ?");
	$q->bind_param('i', $project_id);
	$q->execute();
	$q->store_result();
	foreach(fetch($q) as $ps){
		if(@$ps->id != '' && @$ps->name_he != 'ארכיון' && @$ps->name != 'Archive')
			$progress_status_ids_array[] = $ps->id;
	}

	$chapters_list = implode(',', $chapters_ids_array);
	$tasks_list = implode(',', $tasks_ids_array);
	$responsibles_list = implode(',', $responsibles_ids_array);
	$pass_ons_list = implode(',', $pass_ons_ids_array);
	$progress_status_list = implode(',', $progress_status_ids_array);

	foreach($reports_definitions as $def){
		$sql_str = "SELECT c.name AS name,m.id AS id,
					m.is_priority AS is_priority,m.id_user AS id_user,
					m.id_chapter AS id_chapter,m.subject AS subject,
					m.ids_rdv AS ids_rdv,m.area,m.description,m.id_task,
					m.id_responsible,m.id_pass_on,m.task_creation_date,
					m.destination_date,m.id_progress_status,
					m.updated_date AS updated_date,m.image1 AS image1,
					m.is_appears_img1 AS is_appears_img1,
					m.image1_width AS image1_width,
					m.image1_height AS image1_height,m.image2 AS image2,
					m.is_appears_img2 AS is_appears_img2,
					m.image2_width AS image2_width,
					m.image2_height AS image2_height,
					m.is_change_row_style AS is_change_row_style,
					m.id_track_responsible AS id_track_responsible,
					m.track_type AS track_type,
					m.reminder_time AS reminder_time,
					m.reminder_date AS reminder_date,
					m.is_agrees AS is_agrees,m.is_reminds AS is_reminds
					FROM dne_meetings m
					LEFT JOIN dne_chapters c ON m.id_chapter = c.id
					LEFT JOIN dne_tasks t ON m.id_task = t.id
					LEFT JOIN dne_progress_status ps ON m.id_progress_status = ps.id
					LEFT JOIN dne_responsibles r ON m.id_responsible = r.id";

		if($def['needs_pass_on_join'])
			$sql_str .= " LEFT JOIN dne_responsibles rp ON m.id_pass_on = rp.id";

		$sql_str .= " WHERE m.id_project = ".$project_id."
					AND m.is_appears = 1";

		if(sizeof($chapters_ids_array) > 0)
			$sql_str .= " AND m.id_chapter IN(".$chapters_list.")";
		if(sizeof($tasks_ids_array) > 0)
			$sql_str .= " AND m.id_task IN(".$tasks_list.")";
		if(sizeof($responsibles_ids_array) > 0)
			$sql_str .= " AND m.id_responsible IN(".$responsibles_list.")";
		if(sizeof($pass_ons_ids_array) > 0)
			$sql_str .= " AND m.id_pass_on IN(".$pass_ons_list.")";
		if(sizeof($progress_status_ids_array) > 0)
			$sql_str .= " AND m.id_progress_status IN(".$progress_status_list.")";

		$sql_str .= $def['extra_where'];

		$check = $mysqli->prepare("SELECT id FROM dne_custom_reports WHERE id_project = ? AND request_name = ?");
		$check->bind_param('is', $project_id, $def['request_name']);
		$check->execute();
		$check->store_result();

		if($check->num_rows > 0){
			$existing = fetch_unique($check);
			$existing_id = $existing->id;

			$query = "UPDATE dne_custom_reports SET
					  pdf_name=?,is_all_chapters_checked=?,chapters_list=?,
					  tasks_list=?,is_all_responsibles_checked=?,responsibles_list=?,
					  is_all_pass_ons_checked=?,pass_ons_list=?,progress_status_list=?,
					  and_or_responsibles=?,sql_str=?,title=?,is_images=?,is_colors=?,lang=?,
					  columns_list=?,is_project_status_report=?
					  WHERE id=?";
			$upd = $mysqli->prepare($query);
			$upd->bind_param('sissisisssssiissii', $def['pdf_name'], $is_all_chapters_checked, $chapters_list,
							$tasks_list, $is_all_responsibles_checked, $responsibles_list,
							$is_all_pass_ons_checked, $pass_ons_list, $progress_status_list,
							$and_or_responsibles, $sql_str, $def['title'], $is_images, $is_colors, $lang,
							$columns_list, $def['is_project_status_report'], $existing_id);
			$upd->execute();
			$updated++;
		}
		else {
			$query = "INSERT INTO dne_custom_reports (id_project,request_name,
					  pdf_name,is_all_chapters_checked,chapters_list,
					  tasks_list,is_all_responsibles_checked,responsibles_list,
					  is_all_pass_ons_checked,pass_ons_list,progress_status_list,
					  and_or_responsibles,sql_str,title,is_images,is_colors,lang,
					  columns_list,is_project_status_report)
					  VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
			$ins = $mysqli->prepare($query);
			$ins->bind_param('ississisisssssiissi', $project_id,
							$def['request_name'], $def['pdf_name'], $is_all_chapters_checked,
							$chapters_list, $tasks_list,
							$is_all_responsibles_checked, $responsibles_list,
							$is_all_pass_ons_checked, $pass_ons_list,
							$progress_status_list, $and_or_responsibles,
							$sql_str, $def['title'], $is_images, $is_colors, $lang,
							$columns_list, $def['is_project_status_report']);
			$ins->execute();
			$created++;
		}
	}

	echo "Projet ".$project_id." traité.<br>";
}

echo "<br><strong>Terminé. $created rapports créés, $updated rapports mis à jour, sur $total_projects projets (actifs et inactifs).</strong>";
?>
