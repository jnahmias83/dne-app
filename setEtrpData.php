<?php 
include 'functions/functions.php';

$sup_type = 'E';

if($_POST['color'] != '') {
	$query = "UPDATE dne_sup_field_of_work SET color = ? WHERE sup_type = ?";
    $query = $mysqli->prepare($query);
    $query->bind_param('ss',$_POST['color'],$sup_type);	
    $query->execute();
}

else if($_POST['bgcolor'] != '') {
	$query = "UPDATE dne_sup_field_of_work SET bgcolor = ? WHERE sup_type = ?";
    $query = $mysqli->prepare($query);
    $query->bind_param('ss',$_POST['bgcolor'],$sup_type);	
    $query->execute();
}
?>