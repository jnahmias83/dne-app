<?php 
include 'functions/functions.php';

$sql_log_meeting_updates = "SELECT * FROM dne_log_meeting_updates
                            WHERE id_meeting = ?							
							ORDER BY action_date";	
$query = $mysqli->prepare($sql_log_meeting_updates);
$query->bind_param("i",$_POST['id_meeting']);
$query->execute();
$query->store_result();
$log_meeting_updates_num_rows = $query->num_rows;
$log_meeting_updates = fetch($query);

$sql_log_meeting_tracking = "SELECT * FROM dne_log_meeting_tracking
                            WHERE id_meeting = ?							
							ORDER BY action_date";	
$query = $mysqli->prepare($sql_log_meeting_tracking);
$query->bind_param("i",$_POST['id_meeting']);
$query->execute();
$query->store_result();
$log_meeting_tracking_num_rows = $query->num_rows;
$log_meeting_tracking = fetch($query);

$query = $mysqli->prepare("SELECT b_bgcolor FROM dne_inputs_colors LIMIT 1");
$query->execute(); 
$query->store_result();
$bg_color_inputs = fetch_unique($query);

$content =  "<form class='alignCenter'>";        
	           
if($log_meeting_updates_num_rows > 0 || $log_meeting_tracking_num_rows > 0){
	    $content .= 	"<table id='tasks_followup_table' align='center' class='marginTop10 fontsize12' border='1'>
				            <thead>
								<tr class='bgColorSilver alignCenter'>
									<th width='30px'>ID</th>
									<th width='90px'>משתמש</th>
									<th width='80px'>תאריך</th>
									<th width='60px'>אירוע</th>
									<th width='80px'>תאריך יעד/תזכורת</th>
									<th width='20px'></th>
									<th width='80px'>עדכון</th>
									<th width='90px'>סטטוס</th>
                                    <th width='110px'>Update <br/> Users</th>											
								</tr>
				            </thead>
				
							<tbody>";
					
					foreach(@$log_meeting_updates as $item){
                        $query = $mysqli->prepare("SELECT firstname,lastname
 						                          FROM dne_users 
												  WHERE id =?");
						$query->bind_param("i",$item->id_user);
						$query->execute();
						$query->store_result();
						$user = fetch_unique($query);
						
						$query = $mysqli->prepare("SELECT name_he 
						                          FROM dne_progress_status 
												  WHERE id =?");
						$query->bind_param("i",$item->id_progress_status);
						$query->execute();
						$query->store_result();
						$progress_status = fetch_unique($query);
						$progress_status_name_he = @$progress_status->name_he;
						if($progress_status_name_he == ' ')
							$progress_status_name_he = '(ללא)';
						
						$id_log_meeting_update = @$item->id;
						$id_user = @$item->id_user;
						$action_date = @$item->action_date;
                        $action = @$item->action;
						
						$destination_date = '';
						if(@$item->destination_date != '0000-00-00')
							$destination_date = substr(@$item->destination_date,8,2)."/".substr(@$item->destination_date,5,2);
						               					
						$is_remark_appears_log = @$item->is_remark_appears_log;	
						$remark = @$item->remark;
						
						$updated_users_array = explode(',',@$item->updated_users);
                        $updated_users = '';
						
						for($i=0;$i<sizeof($updated_users_array);$i++){
							$query = $mysqli->prepare("SELECT nickname FROM dne_users 
							                           WHERE id =?");
							$query->bind_param("i",$updated_users_array[$i]);
							$query->execute();
						    $query->store_result();
						    $query = fetch_unique($query);
							$updated_users .= @$query->nickname.',';
						}
						
						$updated_users = substr($updated_users,0,-1);
					
						$is_remark_appears_log_checked = '';
					    if(@$is_remark_appears_log)
							$is_remark_appears_log_checked = 'checked';
						
					    $content .= "<tr class='alignCenter'>
										<td>".@$_POST['id_meeting']."</td>
										<td>".@$user->firstname."&nbsp;".@$user->lastname."</td>
										<td>".@substr(@$action_date,8,2)."/".substr(@$action_date,5,2)."/".substr(@$action_date,0,4)."</td>
										<td>".@$action."</td>
										<td>".@$destination_date."</td>
										<td>
											<input type='checkbox' ".@$is_remark_appears_log_checked.
											" id='is_remark_appears_log_".@$id_log_meeting_update."'".
											" onchange=\"setIsRemarkAppearsLog('".@$id_log_meeting_update."','meeting_updates')\" />
										</td>
										<td>
											<div>".html_entity_decode(@$remark)."</div>
										</td>
										<td>".@$progress_status_name_he."</td>
										<td>".@$updated_users."</td>
									</tr>";
					}
					
					foreach(@$log_meeting_tracking as $item){
                        $query = $mysqli->prepare("SELECT firstname,lastname
 						                          FROM dne_users 
												  WHERE id =?");
						$query->bind_param("i",$item->id_user);
						$query->execute();
						$query->store_result();
						$user = fetch_unique($query);
						
						$id_log_meeting_tracking = @$item->id;
						$id_user = @$item->id_user;
						$action_date = @$item->action_date;
						
						$query = $mysqli->prepare("SELECT m.reminder_date AS reminder_date
												   FROM dne_log_meeting_tracking lmt
												   LEFT JOIN dne_meetings m ON lmt.id_meeting = m.id			   
												   WHERE m.id = ? LIMIT 1");
						$query->bind_param("i",$item->id_meeting);
						$query->execute();
						$query->store_result();
						$query = fetch_unique($query);									
						
						$reminder_date = '';
						if(@$query->reminder_date != '0000-00-00')
							$reminder_date = substr(@$query->reminder_date,8,2)."/".substr(@$query->reminder_date,5,2);
						               					
						$is_remark_appears_log = @$item->is_remark_appears_log;	
						$remark = @$item->remark;
						
						$updated_users_array = explode(',',@$item->updated_users);
                        $updated_users = '';
						
						for($i=0;$i<sizeof($updated_users_array);$i++){
							$query = $mysqli->prepare("SELECT nickname FROM dne_users 
							                           WHERE id =?");
							$query->bind_param("i",$updated_users_array[$i]);
							$query->execute();
						    $query->store_result();
						    $query = fetch_unique($query);
							$updated_users .= @$query->nickname.',';
						}
						
						$updated_users = substr($updated_users,0,-1);
					
						$is_remark_appears_log_checked = '';
					    if(@$is_remark_appears_log)
							$is_remark_appears_log_checked = 'checked';
						
					    $content .= "<tr class='alignCenter' style='background-color:".@$bg_color_inputs->b_bgcolor."'>
										<td>".@$_POST['id_meeting']."</td>
										<td>".@$user->firstname."&nbsp;".@$user->lastname."</td>
										<td>".@substr(@$action_date,8,2)."/".substr(@$action_date,5,2)."/".substr(@$action_date,0,4)."</td>
										<td>מעקב</td>
										<td>".@$reminder_date."</td>
										<td>
											<input type='checkbox' ".@$is_remark_appears_log_checked.
											" id='is_remark_appears_log_".@$id_log_meeting_tracking."'".
											" onchange=\"setIsRemarkAppearsLog('".@$id_log_meeting_tracking."','meeting_tracking')\" />
										</td>
										<td>
											<div>".html_entity_decode(@$remark)."</div>
										</td>
										<td></td>
										<td>".@$updated_users."</td>
									</tr>";
					}
				
				    $content .=    "</tbody>
					            </table>";
}
else 	
	$content .= 'No history';
				
$content .=   "</form>";
echo $content;
?>