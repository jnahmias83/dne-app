<?php
include 'functions/functions.php';

$query = $mysqli->prepare("DELETE FROM dne_costs_eval_modul WHERE id_budget_costs_eval = ?");
$query->bind_param("i",$_POST['id']);
$query->execute();

$query = $mysqli->prepare("DELETE FROM dne_budget_costs_eval WHERE id = ?");
$query->bind_param("i",$_POST['id']);
$query->execute();
?>