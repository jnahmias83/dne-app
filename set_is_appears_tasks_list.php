<?php 
include 'functions/functions.php';

$query = "UPDATE dne_tasks SET is_appears_tasks_list = ? WHERE id = ?";
$query = $mysqli->prepare($query);
$query->bind_param('ii',$_POST['is_appears_tasks_list'],$_POST['id_task']);
$query->execute();
?>