<?php
include 'functions/functions.php';
session_start();

$_SESSION['report_date'] = @$_POST['report_date'];

$query = "SELECT id_task,id_responsible,id_pass_on FROM dne_chapters 
	      WHERE id = ?";
$query = $mysqli->prepare($query);
$query->bind_param("i",$_POST['id_chapter']);
$query->execute(); 
$query->store_result();
$query = fetch_unique($query);
echo @$query->id_task.'_'.@$query->id_responsible.'_'.@$query->id_pass_on.'_';

$query = $mysqli->prepare("SELECT filled_bgcolor 
                           FROM dne_inputs_colors LIMIT 1");
$query->execute(); 
$query->store_result();
$bg_color_inputs = fetch_unique($query);
$filled_bgcolor = @$bg_color_inputs->filled_bgcolor;
echo $filled_bgcolor;
?>