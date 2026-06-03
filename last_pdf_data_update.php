<?php
include 'functions/functions.php';
error_reporting(E_ALL);
ini_set('display_errors', '1');

$query = $mysqli->prepare("SELECT * FROM dne_projects WHERE id = ?");
$query->bind_param("i",$_POST['id_project']);
$query->execute();
$query->store_result();
$project = fetch_unique($query);

if($_POST['from'] === 'previewTasksReport' || $_POST['from'] === 'custumReports') {
    $last_pdf_created = substr(date('Y-m-d'),0,4).substr(date('Y-m-d'),5,2).substr(date('Y-m-d'),8,2).'-Tasks Report-'.$project->name.'.pdf';

	$query = "UPDATE dne_meetings SET last_pdf_created = ?,last_pdf_created_date = ? WHERE id_project = ?";
	$query = $mysqli->prepare($query);
	$query->bind_param('ssi',$last_pdf_created,date('Y-m-d'),$_POST['id_project']);	
	$query->execute();
	
	$query = $mysqli->prepare("SELECT * FROM dne_log_create_pdf WHERE id_project = ? AND last_pdf_created_date = ?");
	$query->bind_param('is',$_POST['id_project'],date('Y-m-d'));	
	$query->execute(); 
	$query->store_result();
	
	if($query->num_rows === 0) {
		$query = "INSERT INTO dne_log_create_pdf (id_project,last_pdf_created_date) VALUES(?,?)";
		$query = $mysqli->prepare($query);
		$query->bind_param('is',$_POST['id_project'],date('Y-m-d'));   
		$query->execute();
	}
}
?>