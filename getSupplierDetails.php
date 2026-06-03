<?php 
include 'functions/functions.php';

$query = $mysqli->prepare("SELECT s.name AS s_name,s.name_he AS s_name_he,s.nickname AS s_nickname
						  FROM dne_projects_suppliers ps
						  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
						  WHERE ps.id = ?");
$query->bind_param("i",$_POST['id_projects_suppliers']);
$query->execute();
$query->store_result();
$supplier = fetch_unique($query);
$result = $supplier->s_nickname.'_';
if($supplier->s_name_he != '')
  $result.= $supplier->s_name_he;
else
  $result.= $supplier->s_name;
echo $result;
?>