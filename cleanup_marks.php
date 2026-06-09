<?php
include 'functions/functions.php';

$fields = ['subject', 'area', 'description'];
$total = 0;

foreach ($fields as $field) {
    $result = $mysqli->query(
        "UPDATE dne_meetings SET `$field` = REPLACE(REPLACE(`$field`, '<mark>', ''), '</mark>', '')
         WHERE `$field` LIKE '%<mark>%' OR `$field` LIKE '%</mark>%'"
    );
    $affected = $mysqli->affected_rows;
    echo "$field : $affected lignes nettoyées<br>";
    $total += $affected;
}

echo "<br><strong>Total : $total lignes mises à jour.</strong>";
?>
