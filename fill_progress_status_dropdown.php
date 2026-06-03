<?php 
include 'functions/functions.php';

$query = $mysqli->prepare("SELECT id,name_he FROM dne_progress_status WHERE id_project = ?");
$query->bind_param("i",$_POST['id_project']);
$query->execute();
$query->store_result();
$progress_status = fetch($query);

echo "<input type='hidden' id='hidden_meeting_id' value='".@$_POST['id_meeting']."'>";
echo "<input type='hidden' id='hidden_iteration' value='".@$_POST['iteration']."'>";
echo "<div>";
echo "<select id='progress_status' class='height35 paddingRight10 alignRight border-none'>";
echo "<option value='0'>בחר סטטוס התקדמות</option>";
foreach($progress_status as $item) {
	$option = "<option value='".htmlspecialchars($item->id)."'";
	if($item->id == $_POST['id_progress_status'])
	   $option.= " selected";
	$option .= ">".$item->name_he."</option>";
	echo $option;
}
echo '</select>';
echo "</div>";
echo "<div id='div_change_ps_remark' class='display-none'>";
echo "<div class='marginTop10 marginBottom10'>";
echo "<textarea id='popup_remark' class='width300 height100'></textarea>";
echo "</div>";
echo "<div class='marginBottom10'>";
echo "<input type='button' id='save_new_ps_with_remark' class='btn-primary padding-5x-7y font-weight-bold marginLeft10' value='שמור הערה' />";
echo "<input type='button' id='save_new_ps_without_remark' class='padding-5x-7y font-weight-bold bgColorBlack colorWhite' value='אין צורך' />";
echo "</div>";
echo "</div>";
?>

<script>
$('[id="progress_status"]').on('change', function() {
	$('#div_change_ps_remark').css('display','block');
});

$('[id="save_new_ps_with_remark"]').on('click', function() {
	setData($('#hidden_meeting_id').val(),$('#hidden_iteration').val(),'id_progress_status',1,1,'popup2');
    sessionStorage.setItem('is_modal_task_actions_opened',true);
    sessionStorage.setItem('project_id',$('#hidden_project_id').val());
	sessionStorage.setItem('meeting_id',$('#hidden_meeting_id').val());
	sessionStorage.setItem('iteration',$('#hidden_iteration').val());
	sessionStorage.setItem('subject',$('#hidden_name').val());
	sessionStorage.setItem('area',$('#hidden_area').val());
	sessionStorage.setItem('recipient',$('#hidden_recipient').val());
	sessionStorage.setItem('responsible_id',$('#hidden_responsible_id').val());
	sessionStorage.setItem('is_priority',$('#hidden_is_priority').val());
	window.location.reload();
});

$('[id="save_new_ps_without_remark"]').on('click', function() {
	setData($('#hidden_meeting_id').val(),$('#hidden_iteration').val(),'id_progress_status',0,1,'popup2');
    sessionStorage.setItem('is_modal_task_actions_opened',true);
    sessionStorage.setItem('project_id',$('#hidden_project_id').val());
	sessionStorage.setItem('meeting_id',$('#hidden_meeting_id').val());
	sessionStorage.setItem('iteration',$('#hidden_iteration').val());
	sessionStorage.setItem('subject',$('#hidden_name').val());
	sessionStorage.setItem('area',$('#hidden_area').val());
	sessionStorage.setItem('recipient',$('#hidden_recipient').val());
	sessionStorage.setItem('responsible_id',$('#hidden_responsible_id').val());
	sessionStorage.setItem('is_priority',$('#hidden_is_priority').val());
	window.location.reload();
});
</script>