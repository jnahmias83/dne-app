<?php
include 'functions/functions.php';
ini_set('display_errors', '0');
ini_set('log_errors', '1');

if($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && empty($_FILES) && (int)@$_SERVER['CONTENT_LENGTH'] > 0) {
	echo 'too_large';
	exit;
}

// Echec d'une requete : on l'ecrit dans le journal du serveur et on le renvoie a l'ecran
// (avant, l'echec etait silencieux : "inserted" etait renvoye meme si rien n'etait enregistre)
$payment_answered = false;
function paymentDbError($query){
	global $payment_answered;
	$payment_answered = true;
	error_log('payment_insert.php : '.$query->error);
	echo 'error: '.$query->error;
	exit;
}

// Arret brutal du script (erreur fatale non capturable) : on renvoie quand meme la cause a l'ecran
register_shutdown_function(function(){
	global $payment_answered;
	if($payment_answered) return;
	$e = error_get_last();
	$msg = $e ? $e['message'].' (ligne '.$e['line'].')' : 'arret du script sans reponse';
	error_log('payment_insert.php fatal : '.$msg);
	echo 'error: '.$msg;
});

try {

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
$today = date('Y-m-d');

// (float) : meme resultat qu'en PHP 7.4 (texte non numerique = 0) ; sous PHP 8 (serveur),
// diviser un texte non numerique faisait planter le script et le paiement n'etait pas enregistre
$paid_amount_vat_excluded = 0;
$paid_amount_number = (float)@$_POST['paid_amount'];
$vat_number = (float)@$_POST['vat'];
if($paid_amount_number != 0 && $vat_number != -100)
   $paid_amount_vat_excluded = $paid_amount_number/(1+($vat_number/100));

if(empty($_POST['paid_amount']))
  { $payment_answered = true; echo 'empty'; }
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
						   $_POST['paid_amount'],$paid_amount_vat_excluded,$pdf_invoice_name,$_POST['invoice_date'],$_POST['vat'],$today);
		if(!$query->execute()) paymentDbError($query);

		$id_payment = $query->insert_id;

		$account_payment_type = 'payment';

		$query = "INSERT INTO dne_accounts_payments (id_payment,id_projects_suppliers,account_payment_date,account_payment_type)
		          VALUES(?,?,?,?)";
		$query = $mysqli->prepare($query);
		$query->bind_param('iiss',$id_payment,$_POST['id_projects_suppliers'],$_POST['payment_date'],$account_payment_type);
		if(!$query->execute()) paymentDbError($query);

		$payment_answered = true; echo "inserted";
	}
	else if($_POST['id'] > 0) {
		$query = "UPDATE dne_payments2 SET id_projects_suppliers = ?,description = ?,payment_date = ?,paid_amount = ?,
				  paid_amount_vat_excluded = ?,invoice_date = ?,vat = ?,updated_date = ? WHERE id = ?";
		$query = $mysqli->prepare($query);
		$query->bind_param('issddsdsi',$_POST['id_projects_suppliers'],$_POST['description'],$_POST['payment_date'],$_POST['paid_amount'],
						  $paid_amount_vat_excluded,$_POST['invoice_date'],$_POST['vat'],$today,$_POST['id']);
		if(!$query->execute()) paymentDbError($query);

		$query = "UPDATE dne_accounts_payments SET id_projects_suppliers = ?,account_payment_date = ? WHERE id_payment = ?";
		$query = $mysqli->prepare($query);
		$query->bind_param('isi',$_POST['id_projects_suppliers'],$_POST['payment_date'],$_POST['id']);
		if(!$query->execute()) paymentDbError($query);

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
		$payment_answered = true; echo 'updated';
	}
}

} catch (Throwable $e) {
	error_log('payment_insert.php : '.$e->getMessage().' (ligne '.$e->getLine().')');
	$payment_answered = true; echo 'error: '.$e->getMessage().' (ligne '.$e->getLine().')';
}
?>
