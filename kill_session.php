<?php
session_start();

if(isset($_SESSION['id_user'])){
	session_unset();
}
?>