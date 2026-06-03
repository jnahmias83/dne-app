<?php
session_start();
include 'functions/functions.php';

$zero = 0;
$query = "UPDATE dne_log_current_report SET id_custom_report = ?,id_rdv_report = ? WHERE id_project = ?";
$query = $mysqli->prepare($query);
$query->bind_param('iii',$zero,$_POST['id_rdv'],$_POST['id_project']);
$query->execute();

$_SESSION['filter_is_all_chapters_checked'] = '';
$_SESSION['filter_chapters_list'] = 'empty';
$_SESSION['filter_tasks_types_list'] = 'empty';
$_SESSION['filter_tasks_list'] = 'empty';
$_SESSION['filter_is_all_responsibles_checked'] = '';
$_SESSION['filter_responsibles_list'] = 'empty';
$_SESSION['filter_and_or_responsibles'] = '';
$_SESSION['filter_is_all_pass_ons_checked'] = '';
$_SESSION['filter_pass_ons_list'] = 'empty';
$_SESSION['filter_progress_status_list'] = 'empty';
$_SESSION['filter_subject'] = '';
$_SESSION['filter_area'] = '';
$_SESSION['filter_description'] = '';
$_SESSION['filter_period_creation_date'] = '';
$_SESSION['filter_creation_date_start'] = '0000-00-00';
$_SESSION['filter_creation_date_end'] = '0000-00-00';
$_SESSION['filter_period_destination_date'] = '';
$_SESSION['filter_destination_date_start'] = '0000-00-00';
$_SESSION['filter_destination_date_end'] = '0000-00-00';
$_SESSION['filter_is_images'] = '';
$_SESSION['filter_is_colors'] = '';
$_SESSION['filter_lang'] = '';
$_SESSION['filter_period_new_tasks'] = '';
$_SESSION['filter_columns_list'] = '';
?>