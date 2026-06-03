<?php
include 'functions/functions.php';

$query = $mysqli->prepare("SELECT s.type AS s_type FROM dne_projects_suppliers ps
                          LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
						  WHERE ps.id = ?");
$query->bind_param("i",$_POST['id_projects_suppliers']);
$query->execute();
$query->store_result();
$supplier = fetch_unique($query);
echo $supplier->s_type;
?>