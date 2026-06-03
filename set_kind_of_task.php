<?php 
include 'functions/functions.php';

$query = $mysqli->prepare("SELECT sfow.sup_type AS sup_type 
                          FROM dne_responsibles r
						  LEFT JOIN dne_projects_suppliers ps ON r.id_projects_suppliers = ps.id
						  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
						  LEFT JOIN dne_sup_field_of_work sfow ON s.id_field_of_work = sfow.id
						  WHERE r.id = ?");
$query->bind_param("i",$_POST['id_responsible']);
$query->execute(); 
$query->store_result();
$query = fetch_unique($query);
$sup_type = $query->sup_type;

$task_designer = 'תכנון';
$task_supplier = 'ביצוע';

if($sup_type == 'S') {
	$query = $mysqli->prepare("SELECT id FROM dne_tasks WHERE name_he = ? AND id_project = ?");
    $query->bind_param("si",$task_supplier,$_POST['id_project']);
    $query->execute(); 
    $query->store_result();
    $query = fetch_unique($query);
	echo $query->id;
}
else if($sup_type == 'D') {
	$query = $mysqli->prepare("SELECT id FROM dne_tasks WHERE name_he = ? AND id_project = ?");
    $query->bind_param("si",$task_designer,$_POST['id_project']);
    $query->execute(); 
    $query->store_result();
    $query = fetch_unique($query);
	echo $query->id;
}
?>