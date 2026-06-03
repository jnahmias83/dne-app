<?php
include 'functions/functions.php';
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo $_POST['in_charge_name'];

$query = "UPDATE dne_projects_suppliers SET in_charge_name = ?,in_charge_phone = ?,in_charge_email = ?,updated_date = ? WHERE id = ?";
$query = $mysqli->prepare($query);
$query->bind_param('ssssi',$_POST['in_charge_name'],$_POST['in_charge_phone'],$_POST['in_charge_email'],date("Y-m-d"),$_POST['id']);
$query->execute();
echo 'updated';
?>
