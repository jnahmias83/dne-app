<?php
include 'functions/functions.php';

$query = $mysqli->prepare("SELECT * FROM dne_progress_status WHERE id_project = ?");
$query->bind_param("i", $_POST['id_project']);
$query->execute();
$progress_status = fetch($query);

echo "<option value='0'>--סטטוס--</option>";

foreach ($progress_status as $item) {
    $selected = ((int)$item->id === (int)($_POST['id_progress_status'] ?? 0))?'selected':'';
    $name_he_clean = trim(preg_replace('/\xc2\xa0|\s+/u',' ',strip_tags($item->name_he)));     
    $name_he = ($name_he_clean === '')?'(ללא)':$item->name_he;
    echo "<option value='{$item->id}' $selected>{$name_he}</option>";
}
?>