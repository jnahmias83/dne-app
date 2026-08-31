<?php
// Outil de diagnostic temporaire, lecture seule (ne modifie rien) - a supprimer une fois le probleme resolu.
ini_set('display_errors', '1');
error_reporting(E_ALL);
session_start();
include 'functions/functions.php';

if(empty($_SESSION['id_user'])){
	die('Connecte-toi d\'abord sur le site, puis reviens sur cette page.');
}

$viewer_id = $_SESSION['id_user'];
$subject_search = @$_GET['subject'] ?: 'ggjhj';
echo "<h2>Diagnostic tri מה חדש</h2>";
echo "<p>Utilisateur connecte (viewer) : id = <b>{$viewer_id}</b></p>";

$query = $mysqli->prepare("SELECT id, id_project, id_user, subject, task_creation_date, updated_date, id_progress_status FROM dne_meetings WHERE subject LIKE ? ORDER BY id DESC LIMIT 5");
$search = '%'.$subject_search.'%';
$query->bind_param('s', $search);
$query->execute();
$query->store_result();
$tasks = fetch($query);

if(empty($tasks)){
	echo "<p style='color:red;'>Aucune tache trouvee avec le sujet contenant '{$subject_search}'.</p>";
	exit;
}

foreach($tasks as $task){
	echo "<hr><h3>Tache id={$task->id} : ".htmlspecialchars($task->subject)."</h3>";
	echo "<p>id_project={$task->id_project} | createur={$task->id_user} | cree le {$task->task_creation_date} | modifie le {$task->updated_date}</p>";

	$q = $mysqli->prepare("SELECT name_he FROM dne_progress_status WHERE id = ?");
	$q->bind_param('i', $task->id_progress_status);
	$q->execute();
	$q->store_result();
	$ps = fetch_unique($q);
	echo "<p>Statut actuel : <b>".htmlspecialchars(@$ps->name_he ?: '(aucun)')."</b></p>";

	$qn = $mysqli->prepare("SELECT ln.id, ln.id_log_meeting_updates FROM dne_log_news ln WHERE ln.id_meeting = ?");
	$qn->bind_param('i', $task->id);
	$qn->execute();
	$qn->store_result();
	$newsRow = fetch_unique($qn);

	if($qn->num_rows == 0){
		echo "<p style='color:red;'><b>Aucune ligne dne_log_news - cette tache n'apparaitra jamais dans מה חדש.</b></p>";
		continue;
	}

	echo "<p>dne_log_news pointe vers lmu_id = <b>{$newsRow->id_log_meeting_updates}</b></p>";

	$ql = $mysqli->prepare("SELECT id, id_user, action, action_date, is_remark_appears_log, updated_users FROM dne_log_meeting_updates WHERE id = ?");
	$ql->bind_param('i', $newsRow->id_log_meeting_updates);
	$ql->execute();
	$ql->store_result();
	$lmu = fetch_unique($ql);

	if($ql->num_rows == 0){
		echo "<p style='color:red;'><b>Cette entree lmu_id n'existe pas/plus - ligne orpheline.</b></p>";
	} else {
		echo "<p><b>C'est CETTE date qui sert au tri :</b> action_date = <b>{$lmu->action_date}</b> (createur={$lmu->id_user}, action=".htmlspecialchars($lmu->action).", is_remark_appears_log={$lmu->is_remark_appears_log}, updated_users='".htmlspecialchars($lmu->updated_users)."')</p>";
	}

	// Toutes les entrees log pour cette tache, pour comparer avec celle utilisee pour le tri
	$qa = $mysqli->prepare("SELECT id, id_user, action, action_date, is_remark_appears_log FROM dne_log_meeting_updates WHERE id_meeting = ? ORDER BY id DESC");
	$qa->bind_param('i', $task->id);
	$qa->execute();
	$qa->store_result();
	$allLogs = fetch($qa);
	echo "<p>Toutes les entrees de log pour cette tache (la plus recente en premier) :</p><ul>";
	foreach($allLogs as $l){
		$isUsed = ($l->id == $newsRow->id_log_meeting_updates) ? ' <-- UTILISEE POUR LE TRI' : '';
		echo "<li>id={$l->id}, action_date={$l->action_date}, createur={$l->id_user}, action=".htmlspecialchars($l->action).", is_remark_appears_log={$l->is_remark_appears_log}{$isUsed}</li>";
	}
	echo "</ul>";
}
