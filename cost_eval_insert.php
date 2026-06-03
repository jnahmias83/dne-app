<?php
include 'functions/functions.php';
error_reporting(E_ALL);
ini_set('display_errors', '1');

$query = $mysqli->prepare("SELECT name,nickname FROM dne_projects WHERE id = ?");
$query->bind_param("i",$_POST['id_project']);
$query->execute();
$query->store_result();
$project = fetch_unique($query);

$query = $mysqli->prepare("SELECT name,nickname FROM dne_sup_field_of_work WHERE id = ?");
$query->bind_param("i",$_POST['id_field_of_work']);
$query->execute();
$query->store_result();
$field_of_work = fetch_unique($query);

$ceb_name = substr(date('Y-m-d'),0,4).substr(date('Y-m-d'),5,2).substr(date('Y-m-d'),8,2).' - ' .@$field_of_work->name.' - Eval - '.@$project->nickname;
$pdf_evaluation_name = '';

if($_POST['id'] == 0) {
    $pdf_evaluation_name = '';
	if(isset($_FILES['pdf_evaluation']['name'])) {
		$pdf_evaluation_name = 'pdf_evaluation_'.$project->name.'_'.str_replace(' ','_',$_FILES['pdf_evaluation']['name']);
		move_uploaded_file($_FILES['pdf_evaluation']['tmp_name'],'uploads/'.$pdf_evaluation_name);
	}
	$query = "INSERT INTO dne_budget_costs_eval (name,id_project,id_field_of_work,description,pdf_evaluation,evaluation_cost,evaluation_date) 
			  VALUES(?,?,?,?,?,?,?)";
	$query = $mysqli->prepare($query);
	$query->bind_param('siissds',$ceb_name,$_POST['id_project'],$_POST['id_field_of_work'],$_POST['description'],$pdf_evaluation_name,$_POST['evaluation_cost'],
	                   $_POST['evaluation_date']); 
	$query->execute();
	if($_POST['fromAddCem'] == 'Yes')
		echo $query->insert_id;
	else echo "inserted";
}
else if($_POST['id'] > 0) {
	$query = "UPDATE dne_budget_costs_eval SET name = ?,id_field_of_work = ?,description = ?,evaluation_cost = ?,evaluation_date = ? WHERE id = ?";
	$query = $mysqli->prepare($query);
	$query->bind_param('sisdsi',$ceb_name,$_POST['id_field_of_work'],$_POST['description'],$_POST['evaluation_cost'],$_POST['evaluation_date'],$_POST['id']);	
	$query->execute();
	
	if(isset($_FILES['pdf_evaluation']['name'])) {
		$pdf_evaluation_name = 'pdf_evaluation_'.$project->name.'_'.str_replace(' ','_',$_FILES['pdf_evaluation']['name']);
		move_uploaded_file($_FILES['pdf_evaluation']['tmp_name'],'uploads/'.$pdf_evaluation_name);
		
		$query = "UPDATE dne_budget_costs_eval SET pdf_evaluation = ? WHERE id = ?";
		$query = $mysqli->prepare($query);
		$query->bind_param('si',$pdf_evaluation_name,$_POST['id']);
		$query->execute();
	}
}
?>