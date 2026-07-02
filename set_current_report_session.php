<?php
session_start();
include 'functions/functions.php';

$zero = 0;
$query = "UPDATE dne_log_current_report SET id_custom_report = ?,
          id_rdv_report = ? WHERE id_project = ?";
$query = $mysqli->prepare($query);
$query->bind_param('iii',$_POST['id_custom_report'],$zero,$_POST['id_project']);
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
$_SESSION['filter_period_new_tasks'] = 'empty';
$_SESSION['filter_columns_list'] = '';

if(@$_POST['rdv_override'] && @$_POST['id_custom_report']){
	$v_is_images = (int)$_POST['is_images'];
	$v_is_colors = (int)$_POST['is_colors'];
	$v_lang      = (string)$_POST['lang'];
	$v_period    = (string)$_POST['period_new_tasks'];
	$v_columns   = (string)$_POST['columns_list'];
	$v_cr_id     = (int)$_POST['id_custom_report'];
	if($v_columns !== ''){
		$q = $mysqli->prepare("UPDATE dne_custom_reports SET is_images=?, is_colors=?, lang=?, period_new_tasks=?, columns_list=? WHERE id=?");
		$q->bind_param('iisssi', $v_is_images, $v_is_colors, $v_lang, $v_period, $v_columns, $v_cr_id);
	} else {
		$q = $mysqli->prepare("UPDATE dne_custom_reports SET is_images=?, is_colors=?, lang=?, period_new_tasks=? WHERE id=?");
		$q->bind_param('iissi', $v_is_images, $v_is_colors, $v_lang, $v_period, $v_cr_id);
	}
	$q->execute();
}
?>