<?php
include 'functions/functions.php';

$query = $mysqli->prepare("SELECT id FROM dne_responsibles WHERE id_project = ?");
$query->bind_param("i",$_POST['id_project']);
$query->execute(); 
$query->store_result();
$responsibles_num_rows = $query->num_rows;

if(empty($_POST['name'])){
	echo "empty";
}
else if($_POST['for'] == 'admingroup' && $responsibles_num_rows == 0 && $_POST['role'] == 'inspector'){
	echo 'firstnotinspector';
}
else {
	if($_POST['id'] == 0){
		$query = $mysqli->prepare("SELECT name FROM dne_responsibles 
								  WHERE name = ? AND id_project = ?");
		$query->bind_param("si",$_POST['name'],$_POST['id_project']);
		$query->execute(); 
		$query->store_result();
		
		if($query->num_rows == 0){
			$query = "INSERT INTO dne_responsibles (id_project,role,id_user,
		             id_projects_suppliers,name,color,bgcolor,email,phone) 
	                 VALUES(?,?,?,?,?,?,?,?,?)";
			$query = $mysqli->prepare($query);
			$query->bind_param('isiisssss',$_POST['id_project'],
			                  $_POST['role'],$_POST['id_user'],
							  $_POST['id_projects_suppliers'],$_POST['name'],
							  $_POST['color'],$_POST['bgcolor'],
							  $_POST['email'],$_POST['phone']);   
			$query->execute();
			$inserted_responsible = $query->insert_id;
		
			$query = $mysqli->prepare("SELECT * FROM dne_custom_reports 
									  WHERE id_project = ?");
			$query->bind_param("i",$_POST['id_project']);
			$query->execute(); 
			$query->store_result();
			$custom_reports = fetch($query);
			
			foreach ($custom_reports as $item){
			   if($item->is_all_responsibles_checked){
				  if((int)@$item->id_supplier > 0) continue;
					$position_where = strpos($item->sql_str,"WHERE");
					$where_length = strlen($item->sql_str)-$position_where;
					$where_part_sql = substr($item->sql_str,$position_where,$where_length);
					$where_part_sql = preg_replace("/(id_responsible|id_pass_on) IN\((.*?)\)/", "$1 IN($2,$inserted_responsible)",$where_part_sql);
					
					$new_sql_str = 'SELECT c.name AS name,m.id AS id,
									m.id_user AS id_user,
									m.id_task_type AS id_task_type,
									m.id_chapter AS id_chapter,
									m.subject AS subject,m.ids_rdv AS ids_rdv,
									m.area,m.description,m.id_task,
									m.id_responsible,m.id_pass_on,
									m.task_creation_date,m.destination_date,
									m.id_progress_status,
									m.updated_date AS updated_date,
									m.image1 AS image1,
								    m.image1_width AS image1_width,
								    m.image1_height AS image1_height,
								    m.is_appears_img1 AS is_appears_img1,
								    m.image2 AS image2,
								    m.image2_width AS image2_width,
								    m.image2_height AS image2_height,
								    m.is_appears_img2 AS is_appears_img2,
									m.is_change_row_style AS is_change_row_style,
								    m.track_type AS track_type,
								    m.id_track_responsible AS id_track_responsible,
								    m.reminder_time AS reminder_time,
								    m.reminder_date AS reminder_date,
                                    m.is_agrees AS is_agrees,m.is_reminds AS is_reminds									
 									FROM dne_meetings m
                                    LEFT JOIN dne_chapters c ON m.id_chapter = c.id 
									LEFT JOIN dne_tasks t ON m.id_task = t.id 
									LEFT JOIN dne_progress_status ps ON m.id_progress_status = ps.id					
						            LEFT JOIN dne_responsibles r ON m.id_responsible = r.id '.$where_part_sql;									
					$responsibles_list = @$item->responsibles_list;
					
					if($responsibles_list != '')
						$responsibles_list.= ','.$inserted_responsible;
					else 
						$responsibles_list = $inserted_responsible;
					
					$pass_ons_list = @$item->pass_ons_list;
					if($pass_ons_list != '')
						$pass_ons_list.= ','.$inserted_responsible;
					else 
						$pass_ons_list = $inserted_responsible;
					
					$query = "UPDATE dne_custom_reports SET responsibles_list = ?,
							  pass_ons_list = ?,sql_str = ? WHERE id = ?";
					$query = $mysqli->prepare($query);
					$query->bind_param('sssi',$responsibles_list,$pass_ons_list,
									   $new_sql_str,$item->id);	
					$query->execute();
			   }
			}
			rebuild_supplier_doh_lists($mysqli, $_POST['id_project']);
            echo "inserted";
		}
		else echo 'exists';
	}
	else if($_POST['id'] > 0){
		$id_user = $_POST['id_user'];
		if($_POST['role'] != 'project_manager' && $_POST['role'] != 'inspector')
			$id_user = 0;
		
	    $query = "UPDATE dne_responsibles SET role = ?,id_user = ?,
		          id_projects_suppliers = ?,name = ?,color = ?,bgcolor = ?,
	              email = ?,phone = ? WHERE id = ?";
		$query = $mysqli->prepare($query);
		$query->bind_param('siisssssi',$_POST['role'],$id_user,$_POST['id_projects_suppliers'],$_POST['name'],
						   $_POST['color'],$_POST['bgcolor'],$_POST['email'],$_POST['phone'],$_POST['id']);	
		$query->execute();
		echo 'updated';
	}
}
?>