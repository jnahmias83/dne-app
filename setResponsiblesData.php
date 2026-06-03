<?php 
include 'functions/functions.php';

$query = $mysqli->prepare("SELECT id,id_projects_suppliers FROM dne_responsibles WHERE id_project = ?");
$query->bind_param("i",$_POST['project_id']);
$query->execute(); 
$query->store_result();
$responsibles = fetch($query);

foreach ($responsibles as $item) {
   $query = $mysqli->prepare("SELECT s.type AS type,sfow.bgcolor AS bgcolor,sfow.color AS color
                             FROM dne_projects_suppliers ps 
							 LEFT JOIN dne_projects p ON ps.id_project = p.id
							 LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
							 LEFT JOIN dne_sup_field_of_work sfow ON s.id_field_of_work = sfow.id
							 WHERE ps.id = ?");
   $query->bind_param("i",$item->id_projects_suppliers);
   $query->execute(); 
   $query->store_result();
   $query = fetch_unique($query);
   $bgcolor = $query->bgcolor;
   $color = $query->color;
   $type = $query->type;
   
   if($type != 'E') {
	  $query = "UPDATE dne_responsibles SET bgcolor = ?,color = ? WHERE id = ?";
      $query = $mysqli->prepare($query);
      $query->bind_param('ssi',$bgcolor,$color,$item->id);
      $query->execute();
   }
}
?>