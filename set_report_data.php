<?php 
include 'functions/functions.php';
session_start();

if($_POST['is_specific_filter']){
	if($_POST['id_custom_report']){
		$query = $mysqli->prepare("SELECT * FROM dne_custom_reports WHERE id = ?");
		$query->bind_param("i",$_POST['id_custom_report']);
		$query->execute();
		$query->store_result();
		$custom_report = fetch_unique($query);
		
		$is_images = @$custom_report->is_images;
		$is_colors = @$custom_report->is_colors;
		$lang = @$custom_report->lang;
		$period_new_tasks = @$custom_report->period_new_tasks;
		$columns_list = @$custom_report->columns_list;
	}
	if($_POST['id_rdv_report']){
		$query = $mysqli->prepare("SELECT * FROM dne_rdv WHERE id = ?");
		$query->bind_param("i",$_POST['id_rdv_report']);
		$query->execute();
		$query->store_result();
		$rdv = fetch_unique($query);
		
		$is_images = @$rdv->is_images;
		$is_colors = @$rdv->is_colors;
		$lang = @$rdv->lang;
		$period_new_tasks = @$rdv->period_new_tasks;
		$columns_list = @$rdv->columns_list;
	}
	
	if($_POST['field'] == 'picture'){
		$_SESSION['filter_is_images'] = @$is_images;
		if(isset($_POST['is_images']))
			$_SESSION['filter_is_images'] = @$_POST['is_images'];
	}
	else if($_POST['field'] == 'colors'){
		$_SESSION['filter_is_colors'] = @$is_colors;
		if(isset($_POST['is_colors']))
			$_SESSION['filter_is_colors'] = @$_POST['is_colors'];
	}
	else if($_POST['field'] == 'lang'){
		$_SESSION['filter_lang'] = @$lang;
		if(isset($_POST['lang']))
			$_SESSION['filter_lang'] = @$_POST['lang'];
	}
	else if($_POST['field'] == 'table_view'){
		$_SESSION['filter_period_new_tasks'] = @$period_new_tasks;
		if(isset($_POST['period_new_tasks']))
			$_SESSION['filter_period_new_tasks'] = @$_POST['period_new_tasks'];

		$_SESSION['filter_columns_list'] = @$columns_list;
		if(isset($_POST['columns_list']))
			$_SESSION['filter_columns_list'] = @$_POST['columns_list'];
	}
	else if($_POST['field'] == 'highlight'){
		$_SESSION['filter_period_new_tasks'] = @$_POST['period_new_tasks'];
	}

	if($_POST['id_custom_report']){
		if($_POST['field'] == 'lang'){
			$q = $mysqli->prepare("UPDATE dne_custom_reports SET lang = ? WHERE id = ?");
			$q->bind_param('si',$_POST['lang'],$_POST['id_custom_report']);
			$q->execute();
		}
		else if($_POST['field'] == 'picture'){
			$q = $mysqli->prepare("UPDATE dne_custom_reports SET is_images = ? WHERE id = ?");
			$q->bind_param('ii',$_POST['is_images'],$_POST['id_custom_report']);
			$q->execute();
		}
		else if($_POST['field'] == 'colors'){
			$q = $mysqli->prepare("UPDATE dne_custom_reports SET is_colors = ? WHERE id = ?");
			$q->bind_param('ii',$_POST['is_colors'],$_POST['id_custom_report']);
			$q->execute();
		}
		else if($_POST['field'] == 'table_view'){
			$q = $mysqli->prepare("UPDATE dne_custom_reports SET period_new_tasks = ?,columns_list = ? WHERE id = ?");
			$q->bind_param('ssi',$_POST['period_new_tasks'],$_POST['columns_list'],$_POST['id_custom_report']);
			$q->execute();
		}
		else if($_POST['field'] == 'highlight'){
			$q = $mysqli->prepare("UPDATE dne_custom_reports SET period_new_tasks = ? WHERE id = ?");
			$q->bind_param('si',$_POST['period_new_tasks'],$_POST['id_custom_report']);
			$q->execute();
		}
	}
	if($_POST['id_rdv_report']){
		if($_POST['field'] == 'lang'){
			$q = $mysqli->prepare("UPDATE dne_rdv SET rdv_lang = ? WHERE id = ?");
			$q->bind_param('si',$_POST['lang'],$_POST['id_rdv_report']);
			$q->execute();
		}
		else if($_POST['field'] == 'picture'){
			$q = $mysqli->prepare("UPDATE dne_rdv SET is_images = ? WHERE id = ?");
			$q->bind_param('ii',$_POST['is_images'],$_POST['id_rdv_report']);
			$q->execute();
		}
		else if($_POST['field'] == 'colors'){
			$q = $mysqli->prepare("UPDATE dne_rdv SET is_colors = ? WHERE id = ?");
			$q->bind_param('ii',$_POST['is_colors'],$_POST['id_rdv_report']);
			$q->execute();
		}
		else if($_POST['field'] == 'table_view'){
			$q = $mysqli->prepare("UPDATE dne_rdv SET period_new_tasks = ?,columns_list = ? WHERE id = ?");
			$q->bind_param('ssi',$_POST['period_new_tasks'],$_POST['columns_list'],$_POST['id_rdv_report']);
			$q->execute();
		}
		else if($_POST['field'] == 'highlight'){
			$q = $mysqli->prepare("UPDATE dne_rdv SET period_new_tasks = ? WHERE id = ?");
			$q->bind_param('si',$_POST['period_new_tasks'],$_POST['id_rdv_report']);
			$q->execute();
		}
	}
}
else {
	if($_POST['id_custom_report']){
		if($_POST['field'] == 'lang'){
			$query = "UPDATE dne_custom_reports SET lang = ? WHERE id = ?";
			$query = $mysqli->prepare($query);
			$query->bind_param('si',$_POST['lang'],$_POST['id_custom_report']);
	    }
		else if($_POST['field'] == 'picture'){
			$query = "UPDATE dne_custom_reports SET is_images = ? WHERE id = ?";
			$query = $mysqli->prepare($query);
			$query->bind_param('ii',$_POST['is_images'],$_POST['id_custom_report']);	
		}
		else if($_POST['field'] == 'colors'){
			$query = "UPDATE dne_custom_reports SET is_colors = ? WHERE id = ?";
			$query = $mysqli->prepare($query);
			$query->bind_param('ii',$_POST['is_colors'],$_POST['id_custom_report']);	
		}
		else if($_POST['field'] == 'table_view'){
			$query = "UPDATE dne_custom_reports SET period_new_tasks = ?,columns_list = ?
			          WHERE id = ?";
			$query = $mysqli->prepare($query);
			$query->bind_param('ssi',$_POST['period_new_tasks'],$_POST['columns_list'],
			                   $_POST['id_custom_report']);
		}
		else if($_POST['field'] == 'highlight'){
			$query = "UPDATE dne_custom_reports SET period_new_tasks = ? WHERE id = ?";
			$query = $mysqli->prepare($query);
			$query->bind_param('si',$_POST['period_new_tasks'],$_POST['id_custom_report']);
		}
		else {
			$query = "UPDATE dne_custom_reports SET is_images = ?,is_colors = ?,lang = ?,
			          period_new_tasks = ?,columns_list = ? WHERE id = ?";
			$query = $mysqli->prepare($query);
			$query->bind_param('iisssi',$_POST['is_images'],$_POST['is_colors'],$_POST['lang'],
							   $_POST['period_new_tasks'],$_POST['columns_list'],
							   $_POST['id_custom_report']);	
		}
		
		$query->execute();
	}
	if($_POST['id_rdv_report']){
		if($_POST['field'] == 'lang'){
			$query = "UPDATE dne_rdv SET rdv_lang = ? WHERE id = ?";
			$query = $mysqli->prepare($query);
			$query->bind_param('si',$_POST['lang'],$_POST['id_rdv_report']);		
		}
		else if($_POST['field'] == 'picture'){
			$query = "UPDATE dne_rdv SET is_images = ? WHERE id = ?";
			$query = $mysqli->prepare($query);
			$query->bind_param('ii',$_POST['is_images'],$_POST['id_rdv_report']);
		}
		else if($_POST['field'] == 'colors'){
			$query = "UPDATE dne_rdv SET is_colors = ? WHERE id = ?";
			$query = $mysqli->prepare($query);
			$query->bind_param('ii',$_POST['is_colors'],$_POST['id_rdv_report']);
		}
		else if($_POST['field'] == 'table_view'){
			$query = "UPDATE dne_rdv SET period_new_tasks = ?,columns_list = ? WHERE id = ?";
			$query = $mysqli->prepare($query);
			$query->bind_param('ssi',$_POST['period_new_tasks'],$_POST['columns_list'],
			                   $_POST['id_rdv_report']);
		}
		else if($_POST['field'] == 'highlight'){
			$query = "UPDATE dne_rdv SET period_new_tasks = ? WHERE id = ?";
			$query = $mysqli->prepare($query);
			$query->bind_param('si',$_POST['period_new_tasks'],$_POST['id_rdv_report']);
		}
		else {
			$query = "UPDATE dne_rdv SET is_images = ?,is_colors = ?,rdv_lang = ?,period_new_tasks = ?,
					  columns_list = ? WHERE id = ?";
			$query = $mysqli->prepare($query);
			$query->bind_param('iisssi',$_POST['is_images'],$_POST['is_colors'],$_POST['lang'],
							   $_POST['period_new_tasks'],$_POST['columns_list'],$_POST['id_rdv_report']);
		}
		
		$query->execute();
	}
}
?>