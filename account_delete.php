<?php
include 'functions/functions.php';

$query = $mysqli->prepare("DELETE FROM dne_accounts_payments WHERE id_account = ?");
$query->bind_param("i",$_POST['id']);
$query->execute();

$query = $mysqli->prepare("DELETE FROM dne_accounts WHERE id = ?");
$query->bind_param("i",$_POST['id']);
$query->execute();
?>