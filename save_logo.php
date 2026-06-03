<?php 
require 'functions/functions.php';

$logo_name = '';
$logo_stread_name = '';

$query = $mysqli->prepare("SELECT logo,logo_stread FROM dne_logos LIMIT 1");
$query->execute();
$query->store_result();
$query->bind_result($existing_logo,$existing_logo_stread);
$query->fetch();

$logo_name = !empty($_FILES['logo']['name']) ? $_FILES['logo']['name'] : $existing_logo;
$logo_stread_name = !empty($_FILES['logo_stread']['name']) ? $_FILES['logo_stread']['name'] : $existing_logo_stread;

if(!empty($_FILES['logo']['name'])){
    $imageTemp = $_FILES["logo"]["tmp_name"]; 
    $imageUploadPath = 'uploads/'.$logo_name;
    $fileType = pathinfo($imageUploadPath, PATHINFO_EXTENSION); 
    $allowTypes = array('jpg','png','jpeg','gif'); 

    if(in_array(strtolower($fileType), $allowTypes)){ 
        move_uploaded_file($imageTemp,$imageUploadPath);
    } else {
        $logo_name = $existing_logo;
    }
}

if(!empty($_FILES['logo_stread']['name'])){
    $imageTemp = $_FILES["logo_stread"]["tmp_name"]; 
    $imageUploadPath = 'uploads/'.$logo_stread_name;
    $fileType = pathinfo($imageUploadPath, PATHINFO_EXTENSION); 
    $allowTypes = array('jpg','png','jpeg','gif'); 

    if(in_array(strtolower($fileType), $allowTypes)){ 
        move_uploaded_file($imageTemp,$imageUploadPath);
    } else {
        $logo_stread_name = $existing_logo_stread;
    }
}

$query = "UPDATE dne_logos SET logo = ?, logo_stread = ?"; 
$stmt = $mysqli->prepare($query);
$stmt->bind_param('ss', $logo_name, $logo_stread_name);
$stmt->execute();
?>