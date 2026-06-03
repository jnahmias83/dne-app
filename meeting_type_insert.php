<?php
include 'functions/functions.php';

if(empty($_POST['name']) || empty($_POST['name_he'])) {
	echo "empty";
}
else {
	if(containsHebrew($_POST['name']) || containsHebrew($_POST['title']) || containsHebrew($_POST['subtitle']))
		echo "hebrewchars";
	else if(containsEnglishCharacters($_POST['name_he']) || containsEnglishCharacters($_POST['title_he']) || containsEnglishCharacters($_POST['subtitle_he']))
		echo "englishchars";
	else {
		if($_POST['id'] == 0) {	
			$query = "INSERT INTO dne_meetings_types (name,name_he,title,title_he,subtitle,subtitle_he) 
					  VALUES(?,?,?,?,?,?)";
			$query = $mysqli->prepare($query);
			$query->bind_param('ssssss',$_POST['name'],$_POST['name_he'],$_POST['title'],$_POST['title_he'],$_POST['subtitle'],
							  $_POST['subtitle_he']); 
			$query->execute();
			echo "inserted";
	    }
		else if($_POST['id'] > 0) {
			$query = "UPDATE dne_meetings_types SET name = ?,name_he = ?,title = ?,title_he = ?,subtitle = ?,subtitle_he = ? WHERE id = ?";
			$query = $mysqli->prepare($query);
			$query->bind_param('ssssssi',$_POST['name'],$_POST['name_he'],$_POST['title'],$_POST['title_he'],$_POST['subtitle'],
							   $_POST['subtitle_he'],$_POST['id']);	
			$query->execute();
			echo 'updated';
		}
	}
}
?>