<?php
session_start();
$_SESSION['pdf_date'] = @$_POST['date'];
$_SESSION['pdf_text1'] = @$_POST['text1'];
$_SESSION['pdf_text2'] = @$_POST['text2'];
$_SESSION['pdf_title1'] = @$_POST['title1'];
$_SESSION['pdf_title2'] = @$_POST['title2'];
?>
