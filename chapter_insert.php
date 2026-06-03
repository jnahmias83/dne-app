<?php
include 'functions/functions.php';

if(empty($_POST['name'])) {
	echo "empty";
}
else {
	if($_POST['id'] == 0) {
		$query = $mysqli->prepare("SELECT name FROM dne_chapters 
								  WHERE name = ? AND id_project = ?");
		$query->bind_param("si",$_POST['name'],$_POST['id_project']);
		$query->execute(); 
		$query->store_result();
			
		if($query->num_rows == 0) {
			$query = $mysqli->prepare("SELECT max(id_display) AS max_id 
		                              FROM dne_chapters 
									  WHERE id_project = ?");
            $query->bind_param("i",$_POST['id_project']);
			$query->execute(); 
			$query->store_result();
			$chapter = fetch_unique($query);
			$max_id = $chapter->max_id;
			$max_id_plus_one = $max_id+1;
		
			$query = "INSERT INTO dne_chapters (id_project,id_display,name) 
					  VALUES(?,?,?)";
			$query = $mysqli->prepare($query);
			$query->bind_param('iis',$_POST['id_project'],$max_id_plus_one,
							   $_POST['name']); 
			$query->execute();
			$inserted_chapter = $query->insert_id;
			
			$query = $mysqli->prepare("SELECT * FROM dne_custom_reports 
									  WHERE id_project = ?");
			$query->bind_param("i",$_POST['id_project']);
			$query->execute(); 
			$query->store_result();
			$custom_reports = fetch($query);
		
			foreach ($custom_reports as $item) {
			   if($item->is_all_chapters_checked) {
				   $position_where = strpos($item->sql_str,"WHERE");
				   $where_length = strlen($item->sql_str)-$position_where;
				   $where_part_sql = substr($item->sql_str,$position_where,$where_length);
				   $where_part_sql_array = explode(' AND ',$where_part_sql);
			   
				   for($i=0;$i < sizeof($where_part_sql_array);$i++) {
					   if(strpos($where_part_sql_array[$i],'id_chapter')!= false) {
						  $position_chapter_in = strpos($where_part_sql_array[$i],"IN(");
						  $where_chapter_in_length = strlen($where_part_sql_array[$i])-$position_chapter_in;
						  $where_part_chapter_in = substr($where_part_sql_array[$i],$position_chapter_in,$where_chapter_in_length);
						  $chapter_ids = substr($where_part_chapter_in,3,-1);
						  $chapter_ids .= ','.$inserted_chapter; 
						  $new_chapter_in = 'm.id_chapter IN('.$chapter_ids.')';
						  $where_part_sql_array[$i] = $new_chapter_in;  
					   }			   	  
				   }
			   
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
						          LEFT JOIN dne_responsibles r ON m.id_responsible = r.id '.implode(' AND ',$where_part_sql_array);
				   
					$chapters_list = @$item->chapters_list;
					if($chapters_list != '')
					   $chapters_list.= ','.$inserted_chapter;
					else 
						$chapters_list = $inserted_chapter;
				   
					$query = "UPDATE dne_custom_reports SET chapters_list =?,
							  sql_str = ? WHERE id = ?";
					$query = $mysqli->prepare($query);
					$query->bind_param('ssi',$chapters_list,$new_sql_str,$item->id);	
					$query->execute();
			   }
			}
			echo "inserted";
		}
		else echo "exists";
	}
	else if($_POST['id'] > 0) {
		$query = "UPDATE dne_chapters SET name = ? WHERE id = ?";
		$query = $mysqli->prepare($query);
		$query->bind_param('si',$_POST['name'],$_POST['id']);	
		$query->execute();
		echo 'updated';
	}
}
?>