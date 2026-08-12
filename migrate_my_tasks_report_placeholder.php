<?php
include 'functions/functions.php';

$request_name = 'משימות שלי';

$query = $mysqli->prepare("SELECT id, sql_str FROM dne_custom_reports WHERE request_name = ?");
$query->bind_param('s', $request_name);
$query->execute();
$query->store_result();
$reports = fetch($query);

$updated = 0;
$skipped = 0;

foreach($reports as $report){
	$old_sql_str = $report->sql_str;
	$new_sql_str = preg_replace('/AND r\.id_user = \d+/', 'AND r.id_user = {CURRENT_USER_ID}', $old_sql_str, -1, $count);

	if($count > 0){
		$update = $mysqli->prepare("UPDATE dne_custom_reports SET sql_str = ? WHERE id = ?");
		$update->bind_param('si', $new_sql_str, $report->id);
		$update->execute();
		$updated++;
		echo "id ".$report->id." : mis à jour.<br>";
	}
	else {
		$skipped++;
		echo "id ".$report->id." : aucun motif trouvé, ignoré.<br>";
	}
}

echo "<br><strong>Terminé. $updated mis à jour, $skipped ignorés (sur ".sizeof($reports)." rapports 'משימות שלי').</strong>";
?>
