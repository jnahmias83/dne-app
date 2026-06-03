<?php
include 'functions/functions.php';

$zero = 0;
$empty = '';

if($_POST['image'] == "image1"){
   $query = "UPDATE dne_meetings SET image1 = ?,image1_width = ?,
		     image1_height = ?,is_appears_img1 = ? WHERE id = ?";
   $query = $mysqli->prepare($query);
   $query->bind_param('siiii',$empty,$zero,$zero,$zero,$_POST['id_meeting']);
   $query->execute();
}
else if($_POST['image'] == "image2"){
   $query = "UPDATE dne_meetings SET image2 = ?,image2_width = ?,
		     image2_height = ?,is_appears_img2 = ? WHERE id = ?";
   $query = $mysqli->prepare($query);
   $query->bind_param('siiii',$empty,$zero,$zero,$zero,$_POST['id_meeting']);
   $query->execute();
}  
?>