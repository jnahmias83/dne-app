<?php 
include 'functions/functions.php';

$query = "UPDATE dne_users SET is_user_active = ? WHERE id = ?";
$query = $mysqli->prepare($query);
$query->bind_param('ii',$_POST['is_user_active'],$_POST['id']);	
$query->execute();
?>