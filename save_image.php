<?php
include 'functions/functions.php';

if(isset($_POST['imageData'])) {
    $imageData = $_POST['imageData'];
    $imageData = str_replace('data:image/jpeg;base64,', '', $imageData);
    $decodedImage = base64_decode($imageData);
    $filePath = 'images_tmp/' . uniqid() . '.jpg';
    file_put_contents($filePath, $decodedImage);
    echo $filePath;
} else {
    http_response_code(400);
    echo 'Error: Image data not provided';
}
?>