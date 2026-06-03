<?php
include 'functions/functions.php';
error_reporting(E_ALL);
ini_set('display_errors', '1');

if(empty($_POST['supplier_name']) || empty($_POST['supplier_email_office'])) {
	echo "empty";
}
else {
	if(containsHebrew($_POST['supplier_name']) || containsHebrew($_POST['supplier_nickname']))
		echo "hebrewchars";
	else if(containsEnglishCharacters($_POST['supplier_name_he']))
		echo "englishchars";
	else if(!empty($_POST['supplier_bank_name']) && !(isNumeric($_POST['supplier_bank_name']) && strlen($_POST['supplier_bank_name']) == 2))
	  echo "illegalbankname";
	else if(!empty($_POST['supplier_bank_branche']) && !(isNumeric($_POST['supplier_bank_branche']) && strlen($_POST['supplier_bank_branche']) == 3))
	    echo "illegalbankbranche";
    else {
		if($_POST['id'] == 0){
			$id_field_of_work = @$_POST['id_field_of_work'];
			$manager_name_he = 'ניהול פרויקט';
			$manager_type = 'M';
			
			$allow_insert_supplier = true;
			
			if($_POST['supplier_type'] == 'M'){
				$query = $mysqli->prepare("SELECT id FROM dne_sup_field_of_work WHERE name_he = ?");
				$query->bind_param('s',$manager_name_he);
				$query->execute();
				$query->store_result();
				$query = fetch_unique($query);
				$id_field_of_work = @$query->id;
				
				$query = $mysqli->prepare("SELECT id FROM dne_suppliers WHERE type = ?");
				$query->bind_param('s',$manager_type);
				$query->execute();
				$query->store_result();
				if(@$query->num_rows > 0)
					$allow_insert_supplier = false;
			}
			
			if($allow_insert_supplier){
				$query = "INSERT INTO dne_suppliers (name,name_he,nickname,type,id_field_of_work,phone,mobile,
						  email_office,bank_account_owner,bank_name,bank_branche,bank_account_number,swift,iban,
						  created_date) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
				$query = $mysqli->prepare($query);
				$query->bind_param('ssssissssssssss',$_POST['supplier_name'],$_POST['supplier_name_he'],
								    $_POST['supplier_nickname'],$_POST['supplier_type'],$id_field_of_work,
								    $_POST['supplier_phone'],$_POST['supplier_mobile'],
								    $_POST['supplier_email_office'],$_POST['supplier_account_owner'],
								    $_POST['supplier_bank_name'],$_POST['supplier_bank_branche'],
								    $_POST['supplier_account_number'],$_POST['supplier_swift'],
								    $_POST['supplier_iban'],date('Y-m-d'));				    
				$query->execute();
			}
			
			echo "inserted";
        }
		else if($_POST['id'] > 0){
			$query = "UPDATE dne_suppliers SET name = ?,name_he = ?,nickname = ?,phone = ?,mobile = ?,
			          email_office = ?,bank_account_owner = ?,bank_name = ?,bank_branche = ?,
					  bank_account_number = ?,swift = ?,iban = ?,updated_date = ? WHERE id = ?";
			$query = $mysqli->prepare($query);
			$query->bind_param('sssssssssssssi',$_POST['supplier_name'],$_POST['supplier_name_he'],
			                   $_POST['supplier_nickname'],$_POST['supplier_phone'],$_POST['supplier_mobile'],
							   $_POST['supplier_email_office'],$_POST['supplier_account_owner'],
							   $_POST['supplier_bank_name'],$_POST['supplier_bank_branche'],
							   $_POST['supplier_account_number'],$_POST['supplier_swift'],
							   $_POST['supplier_iban'],date("Y-m-d"),$_POST['id']);
			$query->execute();
			
			if($_POST['supplier_type'] != 'M'){
				$query = "UPDATE dne_suppliers SET id_field_of_work = ? WHERE id = ?";
				$query = $mysqli->prepare($query);
				$query->bind_param('ii',$_POST['id_field_of_work'],$_POST['id']);
				$query->execute();
			}
			echo 'updated';
		}
	}
}
?>