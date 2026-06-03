<?php
include 'functions/functions.php';

$query = $mysqli->prepare("DELETE FROM dne_meetings_types WHERE id = ?");
$query->bind_param("i",$_POST['id']);
$query->execute();
?>