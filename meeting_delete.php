<?php
include 'functions/functions.php';

$query = $mysqli->prepare("DELETE FROM dne_log_news WHERE id_meeting = ?");
$query->bind_param("i",$_POST['id']);
$query->execute();

$query = $mysqli->prepare("DELETE FROM dne_log_meeting_updates WHERE id_meeting = ?");
$query->bind_param("i",$_POST['id']);
$query->execute();

$query = $mysqli->prepare("DELETE FROM dne_log_meeting_tracking WHERE id_meeting = ?");
$query->bind_param("i",$_POST['id']);
$query->execute();

$query = $mysqli->prepare("DELETE FROM dne_meetings WHERE id = ?");
$query->bind_param("i",$_POST['id']);
$query->execute();
?>