<?php 
include 'functions/functions.php';
session_start();

if(isset($_POST['image1'])) {
    $image1_name = uniqid().'_'.PATHINFO(BASENAME(@$_POST['image1_name']),PATHINFO_FILENAME).'_rotated_'.@$_POST['image1_rotation'].'.jpg';
    $image1_data = $_POST['image1'];
    $image1_data = preg_replace('/^data:image\/[a-z]+;base64,/','',$image1_data);
    $image1_data = base64_decode($image1_data);
    $image1_path = 'uploads/'.$image1_name;
    file_put_contents($image1_path,$image1_data);
	list($image1_width,$image1_height) = getimagesize($image1_path);
	$_SESSION['task_image1_data'] = $image1_name.'____'.$image1_width.'____'.$image1_height.'____'.$_POST['image1_rotation'];
}
else if(isset($_POST['image2'])) {
    $image2_name = uniqid().'_'.PATHINFO(BASENAME(@$_POST['image2_name']),PATHINFO_FILENAME).'_rotated_'.@$_POST['image2_rotation'].'.jpg';
    $image2_data = $_POST['image2'];
    $image2_data = preg_replace('/^data:image\/[a-z]+;base64,/','',$image2_data);
    $image2_data = base64_decode($image2_data);
    $image2_path = 'uploads/'.$image2_name;
    file_put_contents($image2_path,$image2_data);
	list($image2_width,$image2_height) = getimagesize($image2_path);
	$_SESSION['task_image2_data'] = $image2_name.'____'.$image2_width.'____'.$image2_height.'____'.$_POST['image2_rotation'];
}			
?>