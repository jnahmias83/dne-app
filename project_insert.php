<?php
ob_start();
include 'functions/functions.php';
session_start();

$_SESSION['lang'] = @$_POST['project_lang'];

if(empty($_POST['project_name']) || empty($_POST['project_name_he']) || 
   empty($_POST['project_nickname'])){
	  ob_end_clean(); echo "empty";
}
else {
	if(containsHebrew($_POST['project_name'])) {
		ob_end_clean(); echo "hebrewchars";
	} else if(containsEnglishCharacters($_POST['project_name_he'])) {
		ob_end_clean(); echo "englishchars";
	} else {
		if(strlen($_POST['project_nickname']) > 7 || 
		   strlen($_POST['project_initiator_nickname']) > 7){
			ob_end_clean(); echo 'uptosevencharacters';
		}
		else {
			if($_POST['id'] == 0){
				$query = "INSERT INTO dne_projects (name,name_he,nickname,lang,created_date) VALUES(?,?,?,?,?)";
				$query = $mysqli->prepare($query);
				$query->bind_param('sssss',$_POST['project_name'],$_POST['project_name_he'],
				                   strtoupper($_POST['project_nickname']),$_POST['project_lang'],date('Y-m-d'));
				$query->execute();
				$id_project = $query->insert_id;
				
				$entrepreneur_type = 'E';
				$field_of_work_id = 113;
				
				$query = "INSERT INTO dne_suppliers (name_he,nickname_he,email_office,
				          type,id_field_of_work) VALUES(?,?,?,?,?)";
				$query = $mysqli->prepare($query);
				$query->bind_param('ssssi',$_POST['project_initiator_name'],
				                   $_POST['project_initiator_nickname'],
								   $_POST['project_initiator_email'],
								   $entrepreneur_type,$field_of_work_id);
				$query->execute();
				$entrepreneur_id = $query->insert_id;

                $query = $mysqli->prepare("SELECT ps.id FROM dne_projects_suppliers ps
				                           LEFT JOIN dne_projects p ON ps.id_project = p.id
										   LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
										   WHERE p.id = ? AND s.type = ?");
				$query->bind_param("s",$entrepreneur_type);
				$query->execute();
				$query->store_result();	
				
				if(@$query->num_rows == 0){
					$query = "INSERT INTO dne_projects_suppliers (id_project,id_supplier) 
							  VALUES(?,?)";
					$query = $mysqli->prepare($query);
					$query->bind_param('ii',$id_project,$entrepreneur_id);
					$query->execute();
				}

                $manager_type = 'M';
			
				$query = $mysqli->prepare("SELECT * FROM dne_suppliers WHERE type = ?");
				$query->bind_param("s",$manager_type);
				$query->execute();
				$query->store_result();
				$manager_sup = fetch_unique($query);
				$id_manager_sup = @$manager_sup->id;
				
				$query = "INSERT INTO dne_projects_suppliers (id_project,id_supplier) 
				          VALUES(?,?)";
				$query = $mysqli->prepare($query);
				$query->bind_param('ii',$id_project,$id_manager_sup);
				$query->execute();				
				
				$query = $mysqli->prepare("SELECT * FROM dne_global_progress_status");
				$query->execute();
				$query->store_result();
				$global_progress_status = fetch($query);

				foreach($global_progress_status as $item){
					$query = $mysqli->prepare("SELECT * FROM dne_progress_status 
											  WHERE id_project = ? AND name_he = ?");
					$query->bind_param("is",$id_project,$item->name_he);
					$query->execute();
					$query->store_result();
					$num_progress_status = $query->num_rows;
					$current_task = fetch_unique($query);
		
					if($num_progress_status == 0){
						$query = "INSERT INTO dne_progress_status (id_project,name,name_he,
								  color,bgcolor) VALUES(?,?,?,?,?)";
						$query = $mysqli->prepare($query);
						$query->bind_param('issss',$id_project,$item->name,$item->name_he,
										   $item->color,$item->bgcolor);  
						$query->execute();
					}
					else {  
						$query = "UPDATE dne_progress_status SET name = ?,color = ?,
						          bgcolor = ? WHERE name_he = ? AND id_project = ?";
						$query = $mysqli->prepare($query);
						$query->bind_param('ssssi',$item->name,$item->color,$item->bgcolor,
						$current_task->name_he,$id_project);  
						$query->execute();
					}
                }
					
				$query = $mysqli->prepare("SELECT * FROM dne_global_tasks");
				$query->execute();
				$query->store_result();
				$global_tasks = fetch($query);
                
				$count = 1;
				foreach($global_tasks as $item){
					$query = $mysqli->prepare("SELECT * FROM dne_tasks 
					                          WHERE id_project = ? AND name_he = ?");
					$query->bind_param("is",$id_project,$item->name_he);
					$query->execute();
					$query->store_result();
					$num_tasks = $query->num_rows;
					$current_task = fetch_unique($query);
					
					if($num_tasks == 0) {
						$query = "INSERT INTO dne_tasks (id_display,id_project,name,name_he,
						          color,bgcolor) VALUES(?,?,?,?,?,?)";
						$query = $mysqli->prepare($query);
						$query->bind_param('iissss',$count,$id_project,$item->name,
						                   $item->name_he,$item->color,$item->bgcolor);  
						$query->execute();
					}
					else {
						$query = "UPDATE dne_tasks SET name = ?, color = ?,bgcolor = ? 
						          WHERE name_he = ? AND id_project = ?";
						$query = $mysqli->prepare($query);
						$query->bind_param('ssssi',$item->name,$item->color,$item->bgcolor,
						                   $current_task->name_he,$id_project);  
						$query->execute();
					}
					$count++;
				}
			
				$query = "INSERT INTO dne_log_create_pdf (id_project,last_pdf_created_date) VALUES(?,?)";
				$query = $mysqli->prepare($query);
				$query->bind_param('is',$id_project,date('Y-m-d',strtotime("-1 days")));  
				$query->execute();
				
				$query = "INSERT INTO dne_log_current_report (id_project) VALUES(?)";
				$query = $mysqli->prepare($query);
				$query->bind_param('i',$id_project);  
				$query->execute();
			
				ob_end_clean(); echo $id_project;
		    }
			else if($_POST['id'] > 0){
			    $entrepreneur_type = 'E';
			    $field_of_work_id = 113;
			   
			    $query = $mysqli->prepare("SELECT ps.id_supplier AS id_supplier,ps.id 
				                          FROM dne_projects_suppliers ps
										  LEFT JOIN dne_suppliers s ON s.id = ps.id_supplier
										  WHERE id_project = ? AND s.type = ?");
			    $query->bind_param("is",$_POST['id'],$entrepreneur_type);
			    $query->execute();
			    $query->store_result();
			    $entrepreneur_num_rows = $query->num_rows;
			    $entrepreneur = fetch_unique($query);
			   
				if($entrepreneur_num_rows > 0){
				   $query = "DELETE FROM dne_suppliers WHERE id = ?";
				   $query = $mysqli->prepare($query);
				   $query->bind_param('i',$entrepreneur->id_supplier);
				   $query->execute();
				   
				   $query = "DELETE FROM dne_projects_suppliers WHERE id = ?";
				   $query = $mysqli->prepare($query);
				   $query->bind_param('i',$entrepreneur->id);
				   $query->execute();
				}
				
				$query = "INSERT INTO dne_suppliers (name_he,nickname_he,email_office,type,id_field_of_work) VALUES(?,?,?,?,?)";
				$query = $mysqli->prepare($query);
				$query->bind_param('ssssi',$_POST['project_initiator_name'],$_POST['project_initiator_nickname'],$_POST['project_initiator_email'],
								   $entrepreneur_type,$field_of_work_id);
				$query->execute();
				$entrepreneur_id = $query->insert_id;
				
				$query = "INSERT INTO dne_projects_suppliers (id_project,id_supplier) values(?,?)";
				$query = $mysqli->prepare($query);
				$query->bind_param('ii',$_POST['id'],$entrepreneur_id);
				$query->execute();
					
				$query = "UPDATE dne_projects SET name = ?,name_he = ?,nickname = ?,
						 is_project_active = ?,lang = ?,updated_date = ? WHERE id = ?";
				$query = $mysqli->prepare($query);
				$query->bind_param('sssissi',$_POST['project_name'],$_POST['project_name_he'],
				                   strtoupper($_POST['project_nickname']),$_POST['is_project_active'],
								   $_POST['project_lang'],date("Y-m-d"),$_POST['id']);	
				$query->execute();
				ob_end_clean(); echo 'updated';
			}
		}
	}
}
?>