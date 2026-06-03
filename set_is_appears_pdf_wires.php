<?php 
include 'functions/functions.php';

$query = "UPDATE dne_projects_suppliers SET is_appears_pdf_wires = ? WHERE id = ?";
$query = $mysqli->prepare($query);
$query->bind_param('ii',$_POST['is_appears_pdf_wires'],$_POST['id_projects_suppliers']);	
$query->execute();
?>