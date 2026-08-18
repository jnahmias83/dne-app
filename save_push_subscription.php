<?php
session_start();
include 'functions/functions.php';

if(empty($_SESSION['id_user']) || empty($_POST['fcm_token'])){
	echo 'error';
	exit;
}

$id_user = $_SESSION['id_user'];
$token = $_POST['fcm_token'];
$device_type = 'android_app';

$query = "INSERT INTO dne_push_subscriptions (id_user,endpoint,device_type)
		 VALUES(?,?,?)
		 ON DUPLICATE KEY UPDATE id_user = VALUES(id_user), device_type = VALUES(device_type)";
$query = $mysqli->prepare($query);
$query->bind_param('iss',$id_user,$token,$device_type);
$query->execute();

echo 'ok';
