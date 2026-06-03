<?php
session_start();
include 'functions/functions.php';
error_reporting(E_ALL);
ini_set('display_errors', '1');	

$query = $mysqli->prepare("SELECT s.id AS id,s.name_he AS s_name_he
                          FROM dne_suppliers s 
						  LEFT JOIN dne_sup_field_of_work sfow ON s.id_field_of_work = sfow.id 
						  WHERE s.id_field_of_work = ? ORDER BY s.name_he");
$query->bind_param("i",$_POST['id_field_of_work']);
$query->execute();
$query->store_result();
$query = fetch($query);

if($_POST['sup_type'] == 'S') {
    echo "<select id='suppliers_name' style='height:35px;'>";
	foreach($query as $item) {
		$query = $mysqli->prepare("SELECT * FROM dne_projects_suppliers ps
								  LEFT JOIN dne_projects p ON ps.id_project = p.id
								  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
								  WHERE p.id = ? AND s.id = ?");
		$query->bind_param("ii",$_POST['id_project'],$item->id);
		$query->execute();
		$query->store_result();
		$count = $query->num_rows;
		if($count == 0) 
		   echo "<option value=\"".htmlspecialchars($item->id)."\">".$item->s_name_he."</option>";
	}
	echo '</select>';
}
else if($_POST['sup_type'] == 'D') {
    echo "<select id='designers_name' class='height35'>";
	foreach($query as $item) {
		$query = $mysqli->prepare("SELECT * FROM dne_projects_suppliers ps
								  LEFT JOIN dne_projects p ON ps.id_project = p.id
								  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
								  WHERE p.id = ? AND s.id = ?");
		$query->bind_param("ii",$_POST['id_project'],$item->id);
		$query->execute();
		$query->store_result();
		$count = $query->num_rows;
		if($count == 0) 
		   echo "<option class='paddingRight10' value=\"".htmlspecialchars($item->id)."\">".$item->s_name_he."</option>";
	}
	echo '</select>';
}
?>