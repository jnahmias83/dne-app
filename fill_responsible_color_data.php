<?php
include 'functions/functions.php';

$query = $mysqli->prepare("SELECT sfow.color AS sfow_color,sfow.bgcolor AS sfow_bgcolor 
                          FROM dne_projects_suppliers ps 
						  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id 
						  LEFT JOIN dne_sup_field_of_work sfow ON s.id_field_of_work = sfow.id 
						  WHERE ps.id = ?");
$query->bind_param("i",$_POST['id_projects_suppliers']);
$query->execute();
$query->store_result();
$supplier = fetch_unique($query);
echo $supplier->sfow_color.','.$supplier->sfow_bgcolor;
?>