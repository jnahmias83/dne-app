<?php
include 'functions/functions.php';

$query = "UPDATE dne_responsibles SET email = ? WHERE id = ?";
$query = $mysqli->prepare($query);
$query->bind_param('si',$_POST['email_recipient'],$_POST['responsible_id']);	
$query->execute();
echo "The responsible email updated successfully!";
?>