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
echo "<h2>Diagnostic badges / מה חדש</h2>";
echo "<p>Utilisateur connecte (viewer) : id = <b>{$viewer_id}</b></p>";

$ps1 = 'ארכיון'; $ps2 = 'בוצע/נמסר'; $ps3 = 'בהמתנה';

$query = $mysqli->prepare("SELECT id, nickname FROM dne_projects WHERE nickname LIKE '%DNE%' AND is_project_active = 1");
$query->execute();
$query->store_result();
$projects = fetch($query);

if(empty($projects)){
	echo "<p style='color:red;'>Aucun projet actif dont le nom contient 'DNE' trouve.</p>";
	exit;
}

foreach($projects as $pr){
	echo "<hr><h3>Projet id={$pr->id} : ".htmlspecialchars($pr->nickname)."</h3>";

	$q = $mysqli->prepare("SELECT id, name, role, id_user FROM dne_responsibles WHERE id_project = ?");
	$q->bind_param('i', $pr->id);
	$q->execute();
	$q->store_result();
	$resps = fetch($q);
	echo "<p><b>Responsables sur ce projet :</b></p><ul>";
	$viewerIsResp = false;
	foreach($resps as $r){
		$match = ($r->id_user == $viewer_id) ? ' <-- TOI' : '';
		if($r->id_user == $viewer_id) $viewerIsResp = true;
		echo "<li>".htmlspecialchars($r->name)." (role={$r->role}, id_user={$r->id_user}){$match}</li>";
	}
	echo "</ul>";
	echo "<p>Es-tu enregistre comme responsable sur ce projet ? <b>".($viewerIsResp ? 'OUI' : 'NON - c\'est la cause des badges a 0 !')."</b></p>";

	// user_tasks
	$q = $mysqli->prepare("SELECT COUNT(*) AS n FROM dne_meetings m LEFT JOIN dne_responsibles r ON m.id_responsible = r.id LEFT JOIN dne_progress_status ps ON m.id_progress_status = ps.id WHERE ps.name_he <> ? AND ps.name_he <> ? AND ps.name_he <> ? AND m.id_project = ? AND r.id_user = ?");
	$q->bind_param('sssii', $ps1, $ps2, $ps3, $pr->id, $viewer_id);
	$q->execute();
	$q->store_result();
	$row = fetch_unique($q);
	echo "<p>Badge משימות שלי (compte reel) : <b>{$row->n}</b></p>";

	// nombre total de taches actives sur ce projet, peu importe le responsable
	$q = $mysqli->prepare("SELECT COUNT(*) AS n FROM dne_meetings m LEFT JOIN dne_progress_status ps ON m.id_progress_status = ps.id WHERE ps.name_he <> ? AND ps.name_he <> ? AND ps.name_he <> ? AND m.id_project = ?");
	$q->bind_param('sssi', $ps1, $ps2, $ps3, $pr->id);
	$q->execute();
	$q->store_result();
	$row2 = fetch_unique($q);
	echo "<p>Nombre total de taches actives sur ce projet (tous responsables confondus) : <b>{$row2->n}</b></p>";

	// what's new
	$q = $mysqli->prepare("SELECT COUNT(*) AS n FROM dne_log_news ln LEFT JOIN dne_log_meeting_updates lmu ON ln.id_log_meeting_updates = lmu.id INNER JOIN dne_meetings m ON ln.id_meeting = m.id LEFT JOIN dne_progress_status ps ON m.id_progress_status = ps.id WHERE (ps.name_he IS NULL OR (ps.name_he <> ? AND ps.name_he <> ? AND ps.name_he <> ?)) AND m.id_project = ? AND lmu.id_user <> ? AND lmu.is_remark_appears_log = 1");
	$q->bind_param('sssii', $ps1, $ps2, $ps3, $pr->id, $viewer_id);
	$q->execute();
	$q->store_result();
	$row3 = fetch_unique($q);
	echo "<p>Badge מה חדש (compte reel, sans le filtre 'deja vu') : <b>{$row3->n}</b></p>";

	// dernieres taches creees sur ce projet
	$q = $mysqli->prepare("SELECT id, subject, id_user, task_creation_date FROM dne_meetings WHERE id_project = ? ORDER BY id DESC LIMIT 5");
	$q->bind_param('i', $pr->id);
	$q->execute();
	$q->store_result();
	$recent = fetch($q);
	echo "<p><b>5 dernieres taches creees sur ce projet :</b></p><ul>";
	foreach($recent as $t){
		echo "<li>id={$t->id} : ".htmlspecialchars($t->subject)." (createur id_user={$t->id_user}, cree le {$t->task_creation_date})</li>";
	}
	echo "</ul>";
}
