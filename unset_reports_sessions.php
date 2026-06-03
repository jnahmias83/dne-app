<?php
session_start();

if(isset($_SESSION['report_date']) || isset($_SESSION['sql']) || isset($_SESSION['project_status_report']) ||
   isset($_SESSION['id_rdv']) || isset($_SESSION['current_report'])){
	unset($_SESSION['report_date']);
	unset($_SESSION['sql']);
	unset($_SESSION['project_status_report']);
	unset($_SESSION['id_rdv']);
	unset($_SESSION['current_report']);
}

$_SESSION['id_project'] = $_POST['id_project'];
?>