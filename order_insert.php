<?php
include 'functions/functions.php';
ini_set('display_errors', '0');
ini_set('log_errors', '1');

if($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && empty($_FILES) && (int)@$_SERVER['CONTENT_LENGTH'] > 0) {
	echo 'too_large';
	exit;
}

$query = $mysqli->prepare("SELECT p.name AS name
                          FROM dne_projects_suppliers ps
						  LEFT JOIN dne_projects p ON ps.id_project = p.id
						  WHERE ps.id = ?");
$query->bind_param("i",$_POST['id_projects_suppliers']);
$query->execute();
$query->store_result();
$project = fetch_unique($query);

$pdf_order_name = '';

if(empty($_POST['sum_order']) || empty($_POST['vat']) || empty($_POST['signature_date'])) {
	echo "empty";
}
else {
	if($_POST['id'] == 0){
		if(isset($_FILES['pdf_order']) && $_FILES['pdf_order']['error'] === UPLOAD_ERR_OK) {
			$original_name = basename($_FILES['pdf_order']['name']);
			$extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
			// sur tablette le fichier arrive souvent sans extension dans son nom : on accepte si l'extension est pdf, absente, ou si le type MIME est application/pdf. On enregistre toujours en .pdf.
			$is_pdf = ($extension === 'pdf' || $extension === '' || strtolower(@$_FILES['pdf_order']['type']) === 'application/pdf');
			if($is_pdf) {
				$clean_project_name = preg_replace('/[^A-Za-z0-9_\-]/', '_', $project->name);
				$pdf_order_name = 'pdf_order_'.$clean_project_name.'_'.time().'.pdf';
				move_uploaded_file($_FILES['pdf_order']['tmp_name'],'uploads/'.$pdf_order_name);
			}
			else error_log('order_insert.php: fichier pdf_order ignore (pas un PDF), type=' . @$_FILES['pdf_order']['type']);
		}
		else if(isset($_FILES['pdf_order']) && $_FILES['pdf_order']['error'] !== UPLOAD_ERR_NO_FILE) {
			// upload interrompu (reseau tablette) : le PDF est optionnel, on enregistre la commande sans PDF
			error_log('order_insert.php: upload pdf_order echoue, code ' . $_FILES['pdf_order']['error']);
		}
		// pas de PDF fourni : le PDF n'est plus obligatoire, $pdf_order_name reste ''

		$query = "INSERT INTO dne_orders (id_projects_suppliers,sum_order,pdf_order,vat,signature_date,
            	  description,created_date) VALUES (?,?,?,?,?,?,?)";
		$query = $mysqli->prepare($query);
		$query->bind_param('idsdsss',$_POST['id_projects_suppliers'],$_POST['sum_order'],$pdf_order_name,
			              $_POST['vat'],$_POST['signature_date'],$_POST['description'],date('Y-m-d'));
		$query->execute();
		echo "inserted";
	}
	else if($_POST['id'] > 0){
		$query = "UPDATE dne_orders SET id_projects_suppliers = ?,sum_order = ?,vat = ?,
		          signature_date = ?,description = ?,updated_date = ? WHERE id = ?";
		$query = $mysqli->prepare($query);
		$query->bind_param('iddsssi',$_POST['id_projects_suppliers'],$_POST['sum_order'],$_POST['vat'],
						   $_POST['signature_date'],$_POST['description'],date("Y-m-d"),$_POST['id']);	
		$query->execute();
 
		if(isset($_FILES['pdf_order']) && $_FILES['pdf_order']['error'] === UPLOAD_ERR_OK) {
			$clean_project_name = preg_replace('/[^A-Za-z0-9_\-]/', '_', $project->name ?? 'project');
			$pdf_order_name = 'pdf_order_' . $clean_project_name . '_' . time() . '.pdf';
			move_uploaded_file($_FILES['pdf_order']['tmp_name'],'uploads/'.$pdf_order_name);
				
			$query = "UPDATE dne_orders SET pdf_order = ? WHERE id = ?";
			$query = $mysqli->prepare($query);
			$query->bind_param('si',$pdf_order_name,$_POST['id']);	
			$query->execute();
		}
        echo 'updated';
    }
}
?>