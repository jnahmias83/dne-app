<?php
include 'functions/functions.php';

$sup_type = 'S';
$des_type = 'D';

$query = $mysqli->prepare("SELECT * FROM dne_suppliers WHERE id = ?");
$query->bind_param("i",$_POST['id_supplier']);
$query->execute();
$query->store_result();
$supplier = fetch_unique($query);

if($supplier->type == "S") {
    $query = $mysqli->prepare("SELECT max(cast(substr(code_ps,2) as unsigned)) AS max_supplier_code
                              FROM dne_projects_suppliers ps 
                              LEFT JOIN dne_projects p ON ps.id_project = p.id
						      LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
                              WHERE p.id = ? and s.type = ?");
    $query->bind_param("is",$_POST['id_project'],$sup_type);
    $query->execute();
    $query->store_result();
	$query = fetch_unique($query);
	$count = $query->max_supplier_code;
    $count++;
    $code = "S".$count;
}
else if($supplier->type == "D") {
	$query = $mysqli->prepare("SELECT max(cast(substr(code_ps,2) as unsigned)) AS max_supplier_code 
                              FROM dne_projects_suppliers ps 
                              LEFT JOIN dne_projects p ON ps.id_project = p.id
						      LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
                              WHERE p.id = ? and s.type = ?");
    $query->bind_param("is",$_POST['id_project'],$des_type);
    $query->execute();
    $query->store_result();
	$query = fetch_unique($query);
	$count = $query->max_supplier_code;
	$count++;
    $code = "D".$count;
}

$query = "INSERT INTO dne_projects_suppliers (id_project,id_supplier,code_ps,created_date) VALUES(?,?,?,?)";
$query = $mysqli->prepare($query);
$created_date = date('Y-m-d');
$query->bind_param('ssss',$_POST['id_project'],$_POST['id_supplier'],$code,$created_date);
$query->execute();

$inserted_sup_to_proj = $query->insert_id;
echo $inserted_sup_to_proj;
?>