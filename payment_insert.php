<?php
include 'functions/functions.php';
error_reporting(E_ALL);
ini_set('display_errors', '1');	

$query = $mysqli->prepare("SELECT p.name AS name
                          FROM dne_projects_suppliers ps
						  LEFT JOIN dne_projects p ON ps.id_project = p.id
						  WHERE ps.id = ?");
$query->bind_param("i",$_POST['id_projects_suppliers']);
$query->execute();
$query->store_result();
$project = fetch_unique($query);

$pdf_payment_name = '';
$pdf_invoice_name = '';

$paid_amount_vat_excluded = 0;
if($_POST['paid_amount'] != 0)
   $paid_amount_vat_excluded = $_POST['paid_amount']/(1+($_POST['vat']/100));

if(empty($_POST['paid_amount'])) 
  echo 'empty';
else {
	if($_POST['id'] == 0) {
		if(isset($_FILES['pdf_payment']['name'])) {
			$pdf_payment_name = 'pdf_payment_'.$project->name.'_'.str_replace(' ','_',$_FILES['pdf_payment']['name']);
			move_uploaded_file($_FILES['pdf_payment']['tmp_name'],'uploads/'.$pdf_payment_name);
		}
		
		if(isset($_FILES['pdf_invoice']['name'])) {
			$pdf_invoice_name = 'pdf_invoice_'.$project->name.'_'.str_replace(' ','_',$_FILES['pdf_invoice']['name']);
			move_uploaded_file($_FILES['pdf_invoice']['tmp_name'],'uploads/'.$pdf_invoice_name);
		}
	
		$query = "INSERT INTO dne_payments2 (id_projects_suppliers,description,pdf_payment,payment_date,paid_amount,
				  paid_amount_vat_excluded,pdf_invoice,invoice_date,vat,created_date) 
				  VALUES(?,?,?,?,?,?,?,?,?,?)";
		$query = $mysqli->prepare($query);
		$query->bind_param('isssddssds',$_POST['id_projects_suppliers'],$_POST['description'],$pdf_payment_name,$_POST['payment_date'],
						   $_POST['paid_amount'],$paid_amount_vat_excluded,$pdf_invoice_name,$_POST['invoice_date'],$_POST['vat'],date('Y-m-d'));   
		$query->execute();
		
		$id_payment = $query->insert_id;
		
		$account_payment_type = 'payment';
		
		$query = "INSERT INTO dne_accounts_payments (id_payment,id_projects_suppliers,account_payment_date,account_payment_type) 
		          VALUES(?,?,?,?)";
		$query = $mysqli->prepare($query);
		$query->bind_param('iiss',$id_payment,$_POST['id_projects_suppliers'],$_POST['payment_date'],$account_payment_type);   
		$query->execute();
		
		echo "inserted";
	}
	else if($_POST['id'] > 0) {
		$query = "UPDATE dne_payments2 SET id_projects_suppliers = ?,description = ?,payment_date = ?,paid_amount = ?,
				  paid_amount_vat_excluded = ?,invoice_date = ?,vat = ?,updated_date = ? WHERE id = ?";
		$query = $mysqli->prepare($query);
		$query->bind_param('issddsdsi',$_POST['id_projects_suppliers'],$_POST['description'],$_POST['payment_date'],$_POST['paid_amount'],
						  $paid_amount_vat_excluded,$_POST['invoice_date'],$_POST['vat'],date("Y-m-d"),$_POST['id']);	
		$query->execute();
		
		$query = "UPDATE dne_accounts_payments SET id_projects_suppliers = ?,account_payment_date = ? WHERE id_payment = ?";
		$query = $mysqli->prepare($query);
		$query->bind_param('isi',$_POST['id_projects_suppliers'],$_POST['payment_date'],$_POST['id']);	
		$query->execute();
		
		if(isset($_FILES['pdf_payment']['name'])) {
			$pdf_payment_name = 'pdf_payment_'.$project->name.'_'.str_replace(' ','_',$_FILES['pdf_payment']['name']);
			move_uploaded_file($_FILES['pdf_payment']['tmp_name'],'uploads/'.$pdf_payment_name);
			
			$query = "UPDATE dne_payments2 SET pdf_payment = ? WHERE id = ?";
			$query = $mysqli->prepare($query);
			$query->bind_param('si',$pdf_payment_name,$_POST['id']);	
			$query->execute();
		}
	
		if(isset($_FILES['pdf_invoice']['name'])) {
			$pdf_invoice_name = 'pdf_invoice_'.$project->name.'_'.str_replace(' ','_',$_FILES['pdf_invoice']['name']);
			move_uploaded_file($_FILES['pdf_invoice']['tmp_name'],'uploads/'.$pdf_invoice_name);
			
			$query = "UPDATE dne_payments2 SET pdf_invoice = ? WHERE id = ?";
			$query = $mysqli->prepare($query);
			$query->bind_param('si',$pdf_invoice_name,$_POST['id']);	
			$query->execute();
		}
		echo 'updated';
	}
}
?>