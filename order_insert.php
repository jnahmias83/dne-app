<?php
include 'functions/functions.php';

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
else if($_POST['id'] == 0 && !isset($_FILES['pdf_order']['name'])) {
	echo "empty";
}
else {
	if($_POST['id'] == 0){
		if(isset($_FILES['pdf_order']) && $_FILES['pdf_order']['error'] === UPLOAD_ERR_OK) {
			$original_name = basename($_FILES['pdf_order']['name']);
			$extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
			if(!in_array($extension, ['pdf'])) { echo "no_file"; exit; }
			$clean_project_name = preg_replace('/[^A-Za-z0-9_\-]/', '_', $project->name);
			$pdf_order_name = 'pdf_order_'.$clean_project_name.'_'.time().'.'.$extension;
			move_uploaded_file($_FILES['pdf_order']['tmp_name'],'uploads/'.$pdf_order_name);
		} else {
			$upload_err = isset($_FILES['pdf_order']['error']) ? $_FILES['pdf_order']['error'] : -1;
			echo "no_file_" . $upload_err;
			exit;
		}
	
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
			$original_name = basename($_FILES['pdf_order']['name']);
			$extension = pathinfo($original_name, PATHINFO_EXTENSION);
			$clean_project_name = preg_replace('/[^A-Za-z0-9_\-]/', '_', $project->name ?? 'project');
			$pdf_order_name = 'pdf_order_' . $clean_project_name . '_' . time() . '.' . $extension;
			$upload_path = 'uploads/' . $pdf_order_name;
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