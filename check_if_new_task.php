<?php
$is_new_task = 1;
if(!in_array($_POST['id_meeting'],explode(',',$_POST['wn_meeting_ids']))  
   && !in_array($_POST['id_meeting'],explode(',',$_POST['wn_tracking_ids'])))
	$is_new_task = 0;
echo $is_new_task;
?>