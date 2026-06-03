<?php
include 'functions/functions.php';

if(empty($_POST['lastname']) || empty($_POST['firstname']) || empty($_POST['nickname']) || empty($_POST['email']) ||
   empty($_POST['username']) || empty($_POST['password'])) {
	echo "empty";
}
else {
	$lang = "HE";
	$role = "user";
	
	if($_POST['id'] == 0){	
		$query = $mysqli->prepare("SELECT * FROM dne_users WHERE email = ?");
		$query->bind_param("s",$_POST['email']);
		$query->execute();
		$query->store_result();
		
		if($query->num_rows == 0) {
			$query = "INSERT INTO dne_users (lastname,firstname,nickname,email,username,password,lang,role,privileges) 
					  VALUES(?,?,?,?,?,?,?,?,?)";
			$query = $mysqli->prepare($query);
			$query->bind_param('sssssssss',$_POST['lastname'],$_POST['firstname'],strtoupper($_POST['nickname']),$_POST['email'],$_POST['username'],
							   $_POST['password'],$lang,$role,$_POST['privileges']); 
			$query->execute();
			echo "inserted";
		}
		else echo 'exists';
	}
	else if($_POST['id'] > 0){
		$query = $mysqli->prepare("SELECT * FROM dne_users 
		                          WHERE email = ? AND id <> ?");
		$query->bind_param("si",$_POST['email'],$_POST['id']);
		$query->execute();
		$query->store_result();
	
    	if($query->num_rows == 0) {
			$query = "UPDATE dne_users SET lastname = ?,firstname = ?,
			          nickname = ?,email= ?,username = ?,password = ?,
					  privileges = ? WHERE id = ?";
			$query = $mysqli->prepare($query);
			$query->bind_param('sssssssi',$_POST['lastname'],
			                   $_POST['firstname'],
							   strtoupper($_POST['nickname']),
							   $_POST['email'],$_POST['username'],
			                   $_POST['password'],$_POST['privileges'],
							   $_POST['id']);	
			$query->execute();
			echo 'updated';
	   }
	   else echo 'exists';
	}
}
?>