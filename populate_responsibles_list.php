<?php
session_start();
include 'functions/functions.php';
error_reporting(E_ALL);
ini_set('display_errors', '1');	

$is_active = 1;
$query = $mysqli->prepare("SELECT id,name FROM dne_responsibles 
                          WHERE id_projects_suppliers = ? AND is_active = ?
						  ORDER BY name");
$query->bind_param("ii",$_POST['id_projects_suppliers'],$is_active);
$query->execute();
$query->store_result();
$query_num_rows = $query->num_rows;
$query = fetch($query);

$_SESSION['id_projects_suppliers'] = $_POST['id_projects_suppliers'];

$all_responsibles_list = $_POST['all_responsibles_list'];

echo "<input type='hidden' id='all_responsibles_list' value=\"".$all_responsibles_list."\" />" ;

if($query_num_rows > 0) {
	echo "<div style='margin-top:8px;font-size:12px;font-weight:bold;'>אחראיים:</div>";
    $responsibles_ids = '';
   foreach ($query as $item) {
	  echo "<input type='checkbox' class='responsibles_list' id='responsibles_list' value=\"".$item->id."\" onclick=\"populateResponsiblesFilter('$item->id')\" checked  />&nbsp;<span style='font-size:12px;'>".$item->name."</span><br/>";
      $responsibles_ids .= $item->id.',';
   }
   
   echo "<input type='hidden' id='responsibles_ids_hidden' value=\"".$responsibles_ids."\" />" ;
}
?>

<script>
let responsibles_ids;
let responsibles_ids_array;
let responsibles_list_data_array;

$(document).ready(function() {
	responsibles_ids = $('#responsibles_ids_hidden').val().substring(0, $('#responsibles_ids_hidden').val().length - 1);
	responsibles_ids_array = responsibles_ids.split(',');
	responsibles_list_data_array = $('#all_responsibles_list').val().split(",");	
	console.log(responsibles_list_data_array);
	$("#all_responsibles").prop('checked', false);
	$('input[id="responsibles"]').prop('checked', false);
	
    $.each(responsibles_list_data_array, function(index, value) {
		$.each(responsibles_ids_array, function(index2, value2) {
		    if(value == value2) {
			  $('input[id="responsibles"][value="' + value2 + '"]').prop('checked', true);  
			}
	    });
	});
});

function populateResponsiblesFilter(id_responsible) {
	if($('input[id="responsibles_list"][value="' + id_responsible + '"]').is(':checked')) {
		$("#all_responsibles").prop('checked', false);
		$('input[id="responsibles"]').prop('checked', false);
		
		$.each(responsibles_ids_array, function(index, value) {
		  if($('input[id="responsibles_list"][value="' + value + '"]').is(':checked')) 
			 $('input[id="responsibles"][value="' + value + '"]').prop('checked', true);
		});
	}
	else {
		$('input[id="responsibles"][value="' + id_responsible + '"]').prop('checked', false);
    }
}
</script>