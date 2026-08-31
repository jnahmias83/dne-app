<?php
// Outil de diagnostic temporaire, lecture seule (ne modifie rien) - a supprimer une fois le probleme resolu.
session_start();
include 'functions/functions.php';

if(empty($_SESSION['id_user'])){
	die('Connecte-toi d\'abord sur le site, puis reviens sur cette page.');
}

$viewer_id = $_SESSION['id_user'];
$subject_search = @$_GET['subject'] ?: 'ghghghhg';

echo "<h2>Diagnostic מה חדש</h2>";
echo "<p>Utilisateur connecte (viewer) : id = <b>{$viewer_id}</b></p>";

$query = $mysqli->prepare("SELECT id, id_project, id_user, subject, id_progress_status FROM dne_meetings WHERE subject LIKE ? ORDER BY id DESC LIMIT 5");
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
	echo "<p>id_project = {$task->id_project} | createur (id_user) = {$task->id_user} | id_progress_status = {$task->id_progress_status}</p>";

	$q = $mysqli->prepare("SELECT name_he FROM dne_progress_status WHERE id = ?");
	$q->bind_param('i', $task->id_progress_status);
	$q->execute();
	$q->store_result();
	$ps = fetch_unique($q);
	echo "<p>Statut actuel : <b>".htmlspecialchars(@$ps->name_he ?: '(aucun)')."</b></p>";

	$q = $mysqli->prepare("SELECT is_project_active, nickname FROM dne_projects WHERE id = ?");
	$q->bind_param('i', $task->id_project);
	$q->execute();
	$q->store_result();
	$proj = fetch_unique($q);
	echo "<p>Projet : ".htmlspecialchars(@$proj->nickname)." | actif = ".(@$proj->is_project_active ? 'OUI' : 'NON')."</p>";

	$q = $mysqli->prepare("SELECT id FROM dne_responsibles WHERE id_project = ? AND id_user = ?");
	$q->bind_param('ii', $task->id_project, $viewer_id);
	$q->execute();
	$q->store_result();
	$isResp = $q->num_rows > 0;
	echo "<p>Le viewer (toi) est-il enregistre comme 'responsible' sur ce projet ? <b>".($isResp ? 'OUI' : 'NON - c\'est probablement la cause !')."</b></p>";

	$q = $mysqli->prepare("SELECT ln.id AS ln_id, lmu.id AS lmu_id, lmu.id_user AS lmu_creator, lmu.is_remark_appears_log, lmu.updated_users
	                       FROM dne_log_news ln
	                       LEFT JOIN dne_log_meeting_updates lmu ON ln.id_log_meeting_updates = lmu.id
	                       WHERE ln.id_meeting = ?");
	$q->bind_param('i', $task->id);
	$q->execute();
	$q->store_result();
	$newsRows = fetch($q);

	if(empty($newsRows)){
		echo "<p style='color:red;'><b>Aucune ligne dans dne_log_news pour cette tache - c'est pour ca qu'elle n'apparait jamais dans מה חדש, pour personne, meme pas pour toi.</b></p>";
	} else {
		foreach($newsRows as $nr){
			echo "<p>dne_log_news id={$nr->ln_id} : log createur={$nr->lmu_creator}, is_remark_appears_log={$nr->is_remark_appears_log}, updated_users='".htmlspecialchars($nr->updated_users)."'</p>";
			$excluded_self = ($nr->lmu_creator == $viewer_id) ? 'OUI (normal, c est toi qui l as fait)' : 'non';
			echo "<p>Exclu car c'est toi le createur de ce log ? {$excluded_self}</p>";
			$already_marked = in_array($viewer_id, array_map('trim', explode(',', $nr->updated_users)));
			echo "<p>Deja marque comme vu par toi (updated_users) ? ".($already_marked ? 'OUI - c\'est probablement la cause !' : 'non')."</p>";
		}
	}
}
