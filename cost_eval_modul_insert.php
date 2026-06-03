<?php
include 'functions/functions.php';

if(empty($_POST['unit']) || empty($_POST['quantity']) || empty($_POST['price'])) {
	echo "empty";
}
else {
	if($_POST['action'] == 'insert') {
		$query = "INSERT INTO dne_costs_eval_modul (id_budget_costs_eval,description,unit,quantity,price) 
				  VALUES(?,?,?,?,?)";
		$query = $mysqli->prepare($query);
		$query->bind_param('issid',$_POST['id_budget_costs_eval'],$_POST['description'],$_POST['unit'],$_POST['quantity'],$_POST['price']);   
		$query->execute();
		echo "inserted";
	}
	else if($_POST['action'] == 'update') {
		$query = "UPDATE dne_costs_eval_modul SET description = ?,unit = ?,quantity = ?,price = ? WHERE id = ?";
		$query = $mysqli->prepare($query);
		$query->bind_param('ssidi',$_POST['description'],$_POST['unit'],$_POST['quantity'],$_POST['price'],$_POST['id']);
		$query->execute();
	}
}
?>