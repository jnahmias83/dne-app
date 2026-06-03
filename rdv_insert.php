<?php
include 'functions/functions.php';
session_start();

if(empty($_POST['rdv_name']) || strlen($_POST['rdv_persons']) == 0) {
	echo "empty";
}
else {
	if($_POST['id'] == 0){
		$columns_list = 'subject,area,description,_task,responsible,pass on,task creation,destination date,progress status';
		$query = "INSERT INTO dne_rdv (id_project,rdv_name,id_meetings_types,
		          rdv_persons,rdv_date,rdv_lang,columns_list) 
			      VALUES(?,?,?,?,?,?,?)";
	    $query = $mysqli->prepare($query);
	    $query->bind_param('isissss',$_POST['id_project'],$_POST['rdv_name'],
		                   $_POST['id_meetings_types'],$_POST['rdv_persons'],
						   $_POST['rdv_date'],$_POST['rdv_lang'],$columns_list);   
	    $query->execute();
		$inserted_rdv = $query->insert_id;
		
		$zero = 0;
		$query = "UPDATE dne_log_current_report SET id_custom_report = ?,id_rdv_report = ? WHERE id_project = ?";
		$query = $mysqli->prepare($query);
		$query->bind_param('iii',$zero,$inserted_rdv,$_POST['id_project']);
		$query->execute();
		
		if(isset($_POST['all_ids_to_edit'])){
			$all_ids_to_edit_array = explode(',',$_POST['all_ids_to_edit']);
		
			for($i=0;$i<sizeof($all_ids_to_edit_array);$i++){
				$query = $mysqli->prepare("SELECT ids_rdv FROM dne_meetings WHERE id = ?");
				$query->bind_param("i",$all_ids_to_edit_array[$i]);
				$query->execute();
				$query->store_result();
				$meeting = fetch_unique($query);
				
				$ids_rdv = @$meeting->ids_rdv;
				if(@$ids_rdv == '')
					$ids_rdv = @$inserted_rdv;
				else 
					$ids_rdv.= ','.@$inserted_rdv;			 
				
				$query = "UPDATE dne_meetings SET ids_rdv = ? WHERE id = ?"; 			   
				$query = $mysqli->prepare($query);
				$query->bind_param('si',$ids_rdv,$all_ids_to_edit_array[$i]);   
				$query->execute();
			}
		}
		
	    echo "inserted";
	}
	else {
		$query = "UPDATE dne_rdv SET rdv_name = ?,id_meetings_types = ?,rdv_persons = ? ,rdv_date = ?, 
		          rdv_lang = ? WHERE id = ?"; 			   
	    $query = $mysqli->prepare($query);
	    $query->bind_param('sisssi',$_POST['rdv_name'],$_POST['id_meetings_types'],$_POST['rdv_persons'],
		                   $_POST['rdv_date'],$_POST['rdv_lang'],$_POST['id']);   
	    $query->execute();
	    echo "updated";
	}
}
?>