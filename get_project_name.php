<?php 
include 'functions/functions.php';

$query = $mysqli->prepare("SELECT name_he FROM dne_projects WHERE id = ?");
$query->bind_param('i',$_POST['project_id']);	
$query->execute(); 
$query->store_result();
$project = fetch_unique($query);
echo @$project->name_he;
?>