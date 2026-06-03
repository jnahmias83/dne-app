<?php
include 'functions/functions.php';

if(empty($_POST['name']) || empty($_POST['name_he'])) {
	echo "empty";
}
else {
	if(containsHebrew($_POST['name']))
		echo "hebrewchars";
	else if(containsEnglishCharacters($_POST['name_he']))
		echo "englishchars";
	else {
		if($_POST['id_project'] != 0) {
			if($_POST['id'] == 0) {
				$query = $mysqli->prepare("SELECT name_he FROM dne_progress_status 
				                          WHERE name_he = ? AND id_project = ?");
				$query->bind_param("si",$_POST['name_he'],$_POST['id_project']);
				$query->execute(); 
				$query->store_result();
			
				if($query->num_rows == 0) {
					$query = "INSERT INTO dne_progress_status (id_project,
					          name,name_he,color,bgcolor,bgcolor_columns,
							  text_columns) VALUES(?,?,?,?,?,?,?)";
					$query = $mysqli->prepare($query);
					$query->bind_param('issssss',$_POST['id_project'],
					                  $_POST['name'],$_POST['name_he'],
					                  $_POST['color'],$_POST['bgcolor'],
									  $_POST['bgcolor_columns'],
									  $_POST['text_columns']);   
					$query->execute();					
					echo "inserted";
				}
				else echo 'exists';
			}
			else if($_POST['id'] > 0) {
				$query = "UPDATE dne_progress_status SET name = ?,
				          name_he = ?,color = ?,bgcolor = ?,
						  bgcolor_columns = ?,text_columns = ? WHERE id = ?";
				$query = $mysqli->prepare($query);
				$query->bind_param('ssssssi',$_POST['name'],
				                   $_POST['name_he'],$_POST['color'],
								   $_POST['bgcolor'],
								   $_POST['bgcolor_columns'],
								   $_POST['text_columns'],
								   $_POST['id']);	
				$query->execute();
				echo 'updated';
			}
		}
		else {
			if($_POST['id'] == 0) {
				$query = $mysqli->prepare("SELECT name_he FROM dne_global_progress_status WHERE name_he = ?");
				$query->bind_param("s",$_POST['name_he']);
				$query->execute(); 
				$query->store_result();
			
				if($query->num_rows == 0) {			
					$query = "INSERT INTO dne_global_progress_status (name,name_he,color,bgcolor) 
							  VALUES(?,?,?,?)";
					$query = $mysqli->prepare($query);
					$query->bind_param('ssss',$_POST['name'],$_POST['name_he'],$_POST['color'],$_POST['bgcolor']);   
					$query->execute();
					echo "inserted";
				}
				else echo 'exists';
			}
			else if($_POST['id'] > 0) {
				$query = "UPDATE dne_global_progress_status SET name = ?,name_he = ?,color = ?,
				          bgcolor = ? WHERE id = ?";
				$query = $mysqli->prepare($query);
				$query->bind_param('ssssi',$_POST['name'],$_POST['name_he'],$_POST['color'],
				                   $_POST['bgcolor'],$_POST['id']);	
				$query->execute();
				echo 'updated';
			}
		}
	}
}
?>