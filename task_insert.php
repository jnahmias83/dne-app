<?php
include 'functions/functions.php';
error_reporting(E_ALL);
ini_set('display_errors', '1');

if(empty($_POST['name']) || empty($_POST['name_he'])) {
	echo "empty";
}
else {
	if(containsHebrew($_POST['name']))
		echo "hebrewchars";
	else if(containsEnglishCharacters($_POST['name_he']))
		echo "englishchars";
	else {
		if($_POST['id_project'] != 0) {	
			if($_POST['id'] == 0) {
				$query = $mysqli->prepare("SELECT name_he FROM dne_tasks 
										  WHERE name_he = ? 
										  AND id_project = ?");
				$query->bind_param("si",$_POST['name_he'],
				                   $_POST['id_project']);
				$query->execute(); 
				$query->store_result();
			
				if($query->num_rows == 0) {
					$query = $mysqli->prepare("SELECT max(id_display) AS max_id 
		                              FROM dne_tasks 
									  WHERE id_project = ?");
					$query->bind_param("i",$_POST['id_project']);
					$query->execute(); 
					$query->store_result();
					$task = fetch_unique($query);
					$max_id = $task->max_id;
					$max_id_plus_one = $max_id+1;
			
					$query = "INSERT INTO dne_tasks (id_display,id_project,
					          name,name_he,color,bgcolor,bgcolor_columns,
							  text_columns) 
							  VALUES(?,?,?,?,?,?,?,?)";
					$query = $mysqli->prepare($query);
					$query->bind_param('iissssss',$max_id_plus_one,
					                   $_POST['id_project'],$_POST['name'],
									   $_POST['name_he'],$_POST['color'],
									   $_POST['bgcolor'],
									   $_POST['bgcolor_columns'],
									   $_POST['text_columns']);   
					$query->execute();
					$inserted_task = $query->insert_id;
					
					$query = $mysqli->prepare("SELECT * FROM dne_custom_reports 
		                                       WHERE id_project = ?");
					$query->bind_param("i",$_POST['id_project']);
					$query->execute(); 
					$query->store_result();
					$custom_reports = fetch($query);
					
					foreach ($custom_reports as $item) {	  
					    $position_where = strpos($item->sql_str,"WHERE");
					    $where_length = strlen($item->sql_str)-$position_where;
					    $where_part_sql = substr($item->sql_str,$position_where,$where_length);
					    $where_part_sql_array = explode(' AND ',$where_part_sql);
				       
					    for($i=0;$i < sizeof($where_part_sql_array);$i++) {
				            if(strpos($where_part_sql_array[$i],'id_task') !== false && strpos($where_part_sql_array[$i],'id_task_type') === false) {
							   $position_task_in = strpos($where_part_sql_array[$i],"IN(");
							   $where_task_in_length = strlen($where_part_sql_array[$i])-$position_task_in;
							   $where_part_task_in = substr($where_part_sql_array[$i],$position_task_in,$where_task_in_length);
							   $task_ids = substr($where_part_task_in,3,-1); 
							   $task_ids .= ','.$inserted_task;							   
							   $new_task_in = 'm.id_task IN('.$task_ids.')';
							   $where_part_sql_array[$i] = $new_task_in;             						   
						   }   
			            }
		   
			            $new_sql_str = 'SELECT c.name AS name,m.id AS id,
			                            m.id_user AS id_user,
							            m.id_task_type AS id_task_type,
							            m.id_chapter AS id_chapter,
										m.subject AS subject,
							            m.ids_rdv AS ids_rdv,m.area,
										m.description,m.id_task,
										m.id_responsible,m.id_pass_on,
							            m.task_creation_date,
										m.destination_date,
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
						                LEFT JOIN dne_responsibles r ON m.id_responsible = r.id '.implode(' AND ',$where_part_sql_array);
			   
			            $tasks_list = @$item->tasks_list;
						if($tasks_list != '')
							$tasks_list.= ','.$inserted_task;
						else 
							$tasks_list = $inserted_task;
						
						$query = "UPDATE dne_custom_reports 
						          SET tasks_list = ?,sql_str = ? 
								  WHERE id = ?";
						$query = $mysqli->prepare($query);
						$query->bind_param('ssi',$tasks_list,$new_sql_str,
						                   $item->id);	
						$query->execute();
		            }
					
					echo "inserted";
				}
				else echo 'exists';
			}
			
			else if($_POST['id'] > 0) {
				$query = "UPDATE dne_tasks SET name = ?,name_he = ?,
				          color = ?,bgcolor = ?,bgcolor_columns = ?,
						  text_columns = ? WHERE id = ?";
				$query = $mysqli->prepare($query);
				$query->bind_param('ssssssi',$_POST['name'],
				                   $_POST['name_he'],$_POST['color'],
								   $_POST['bgcolor'],
								   $_POST['bgcolor_columns'],
								   $_POST['text_columns'],$_POST['id']);	
				$query->execute();
				echo 'updated';
			}
		}
		else {
			if($_POST['id'] == 0) {
				$query = $mysqli->prepare("SELECT name_he 
				                          FROM dne_global_tasks 
										  WHERE name_he = ?");
				$query->bind_param("s",$_POST['name_he']);
				$query->execute(); 
				$query->store_result();
			
				if($query->num_rows == 0) {
					$query = "INSERT INTO dne_global_tasks (name,name_he,
					          color,bgcolor) VALUES(?,?,?,?)";
					$query = $mysqli->prepare($query);
					$query->bind_param('ssss',$_POST['name'],
					                   $_POST['name_he'],$_POST['color'],
									   $_POST['bgcolor']);   
					$query->execute();
					echo "inserted";
				}
				else echo 'exists';
			}
			else if($_POST['id'] > 0) {
					$query = "UPDATE dne_global_tasks SET name = ?,
					          name_he = ?,color = ?,bgcolor = ? 
							  WHERE id = ?";
					$query = $mysqli->prepare($query);
					$query->bind_param('ssssi',$_POST['name'],
					                   $_POST['name_he'],$_POST['color'],
									   $_POST['bgcolor'],$_POST['id']);	
					$query->execute();
					echo 'updated';
			}
		}
	}
}
?>