<?php
session_start();
if(empty($_SESSION['id_user']))
	header('Location:login.php');
else header('Location:projects.php');
?>