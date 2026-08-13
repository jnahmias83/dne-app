<?php
include 'functions/functions.php';

$project_id = @$_GET['project_id'];

if(empty($project_id)){
	echo "Ajoute ?project_id=XX à l'adresse (le numéro visible dans l'URL de meetings.php pour ce projet).";
	exit;
}

$q = $mysqli->prepare("SELECT lang FROM dne_projects WHERE id = ?");
$q->bind_param('i', $project_id);
$q->execute();
$q->store_result();
$project = fetch_unique($q);
echo "<h3>Projet $project_id — lang = ".@$project->lang."</h3>";

$q = $mysqli->prepare("SELECT id, request_name, is_colors, is_project_status_report, period_new_tasks, lang FROM dne_custom_reports WHERE id_project = ?");
$q->bind_param('i', $project_id);
$q->execute();
$q->store_result();
$reports = fetch($q);
echo "<h3>Rapports de ce projet :</h3><table border='1' cellpadding='5'>";
echo "<tr><th>id</th><th>request_name</th><th>is_colors</th><th>is_project_status_report</th><th>period_new_tasks</th><th>lang</th></tr>";
foreach($reports as $r){
	echo "<tr><td>{$r->id}</td><td>{$r->request_name}</td><td>{$r->is_colors}</td><td>{$r->is_project_status_report}</td><td>".htmlspecialchars($r->period_new_tasks)."</td><td>{$r->lang}</td></tr>";
}
echo "</table>";

$q = $mysqli->prepare("SELECT m.id, m.subject, m.task_creation_date, m.is_change_row_style, m.id_progress_status, ps.name_he, ps.name FROM dne_meetings m LEFT JOIN dne_progress_status ps ON m.id_progress_status = ps.id WHERE m.id_project = ? AND m.is_appears = 1 ORDER BY m.id DESC LIMIT 20");
$q->bind_param('i', $project_id);
$q->execute();
$q->store_result();
$tasks = fetch($q);
echo "<h3>20 dernières tâches actives :</h3><table border='1' cellpadding='5'>";
echo "<tr><th>id</th><th>subject</th><th>task_creation_date</th><th>is_change_row_style</th><th>status (he)</th><th>status (en)</th></tr>";
foreach($tasks as $t){
	echo "<tr><td>{$t->id}</td><td>".htmlspecialchars(mb_substr($t->subject,0,40))."</td><td>{$t->task_creation_date}</td><td>{$t->is_change_row_style}</td><td>{$t->name_he}</td><td>{$t->name}</td></tr>";
}
echo "</table>";
?>
