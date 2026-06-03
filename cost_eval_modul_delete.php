<?php
include 'functions/functions.php';

$query = $mysqli->prepare("DELETE FROM dne_costs_eval_modul WHERE id = ?");
$query->bind_param("i",$_POST['cem_id']);
$query->execute();
?>