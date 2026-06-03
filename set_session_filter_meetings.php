<?php
session_start();
if($_POST['id_custom_report'] > 0 && $_POST['from'] != 'setFilter') {
	$_SESSION['filter_id_supplier'] = $_POST['id_supplier'];
	$_SESSION['filter_pdf_name'] = $_POST['pdf_name'];
}

$_SESSION['filter_columns_list'] = $_POST['columns_list'];
$_SESSION['filter_lang'] = $_POST['lang'];
$_SESSION['filter_period_new_tasks'] = $_POST['period_new_tasks'];
$_SESSION['filter_is_images'] = $_POST['is_images'];
$_SESSION['filter_is_colors'] = $_POST['is_colors'];
$_SESSION['filter_is_all_chapters_checked'] = $_POST['is_all_chapters_checked'];
$_SESSION['filter_chapters_list'] = $_POST['chapters_list'];
$_SESSION['filter_tasks_types_list'] = $_POST['tasks_types_list'];
$_SESSION['filter_tasks_list'] = $_POST['tasks_list'];
$_SESSION['filter_is_all_responsibles_checked'] = $_POST['is_all_responsibles_checked'];
$_SESSION['filter_responsibles_list'] = $_POST['responsibles_list'];
$_SESSION['filter_and_or_responsibles'] = $_POST['and_or_responsibles'];
$_SESSION['filter_is_all_pass_ons_checked'] = $_POST['is_all_pass_ons_checked'];
$_SESSION['filter_pass_ons_list'] = $_POST['pass_ons_list'];
$_SESSION['filter_progress_status_list'] = $_POST['progress_status_list'];
$_SESSION['filter_period_creation_date'] = $_POST['period_creation_date'];
$_SESSION['filter_creation_date_start'] = $_POST['creation_date_start'];
$_SESSION['filter_creation_date_end'] = $_POST['creation_date_end'];
$_SESSION['filter_period_destination_date'] = $_POST['period_destination_date'];
$_SESSION['filter_destination_date_start'] = $_POST['destination_date_start'];
$_SESSION['filter_destination_date_end'] = $_POST['destination_date_end']; 
$_SESSION['filter_sql'] = $_POST['sql'];
$_SESSION['filter_subject'] = $_POST['subject'];
$_SESSION['filter_area'] = $_POST['area'];
$_SESSION['filter_description'] = $_POST['description'];
?>