<?php
include 'include/header.php';
include 'functions/functions.php';

$project_id = @$_GET['id'];

$query = $mysqli->prepare("SELECT * FROM dne_projects WHERE id = ?");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$project = fetch_unique($query);

$is_user_active = 1;
$query = $mysqli->prepare("SELECT * FROM dne_users WHERE is_user_active = ?");
$query->bind_param("i",$is_user_active);
$query->execute();
$query->store_result();
$active_users = fetch($query);

$label_track = 'מעקב';
$query = $mysqli->prepare("SELECT id FROM dne_tasks_actions 
						  WHERE name_he = ? ");
$query->bind_param('s',$label_track);	
$query->execute(); 
$query->store_result();
$query = fetch_unique($query);
$id_task_action = @$query->id;

$ps1 = 'ארכיון';
$empty_remark = '';
$is_remark_appears_log = 1;							

$query = $mysqli->prepare("SELECT lmu.id AS lmu_id,
						   lmu.action AS action,
						   lmu.action_date AS action_date,
						   lmu.remark as remark,
						   m.id AS id,m.id_user AS id_user,
					       c.name AS chapter_name,t.name_he AS task_name,
				           m.subject AS subject,m.area AS area,
					       m.description AS description,
				           m.id_responsible AS id_responsible,
					       r.name AS responsible_name,
					       r.email AS responsible_email,
						   m.is_priority AS is_priority,
						   m.track_type AS track_type,
				           m.reminder_time AS reminder_time,					
						   m.id_track_responsible AS id_track_responsible,
                           u.nickname AS user_nickname														   
			               FROM dne_log_meeting_updates lmu 
						   LEFT JOIN dne_users u ON lmu.id_user = u.id
						   LEFT JOIN dne_meetings m ON lmu.id_meeting = m.id  
						   LEFT JOIN dne_chapters c ON m.id_chapter = c.id
				           LEFT JOIN dne_tasks t ON m.id_task = t.id
				           LEFT JOIN dne_responsibles r ON m.id_responsible = r.id
				           LEFT JOIN dne_progress_status ps ON m.id_progress_status = ps.id 
					       WHERE ps.name_he <> ?          						   
			               AND m.id_project = ?
						   AND lmu.remark <> ?
					       AND lmu.id_user <> ?
				    	   AND lmu.is_remark_appears_log = ?
						   AND NOT FIND_IN_SET(?,lmu.updated_users)
						   ORDER BY lmu_id");
$query->bind_param('sisiii',$ps1,$project->id,$empty_remark,$_SESSION['id_user'],$is_remark_appears_log,$_SESSION['id_user']);	
$query->execute(); 
$query->store_result();
$unread_tasks_num_rows = $query->num_rows;
$unread_tasks = fetch($query);

$project_label = 'פרוייקט';
$suppliers_label = 'ספקים';
$budget_report_label = 'תקציב';
$tasks_report_label = 'משימות';
$project_settings_label = 'נתוני פרוייקט';
$what_news_label = 'מה חדש';
?>
        <form method="post" action="" enctype="multipart/form-data" class="form-inline">
			<input type="hidden" id="id" value="<?=@$id?>" />
			<input type="hidden" id="project_id" value="<?=@$project_id?>" />
			
			<div class="container">
			    <div class="width550" id="contentToScreenshot"></div>
				
				<br/>
			
				<div class="row alignCenter marginTop25">
					<div class="col-md-12">
						<a href="projects.php"><img src="images/davidnahmias_logo.png" width="170px" height="170px" /></a>
					</div>
			    </div>
				
				<div class="row marginTop15 color-1A5276 alignCenter">
				    <div class="col-md-12">
					    <div class="fontSize33 line-height-1em"><strong><?=@$project->nickname?></strong></div>
				        <span class="fontSize26 font-weight-bold font-family-david color-1A5276"><?=@$project_label.'&nbsp;'.@$project->name_he?></span>  
				    </div>
				</div>
				
				<div class="row marginTop20 alignCenter dir-rtl">
					<div class="col-12">
					   <a class="btn marginBottom20 bgColorBlue2 fontSize20 padding-20x-10y marginLeft15" onclick="location.href='meetings.php?project_id=<?=@$project_id?>';"><i class="fa-solid fa-list-check marginLeft10"></i><?=@$tasks_report_label?></a>
					   <a class="btn marginBottom20 bgColorBrown fontSize20 padding-20x-10y" onclick="location.href='budget.php?project_id=<?=@$project_id?>&lang_screen=HE'"><i class="fa-solid fa-dollar-sign marginLeft10"></i><?=@$budget_report_label?></a>
					   <br/>  
					   <input type="button" class="btn marginBottom20 bgColorDarkGrey fontSize16 width160 padding-4x-4y marginLeft15" onclick="location.href='add_project.php?id=<?=@$project_id?>'" value="<?=@$project_settings_label?>" />
                       <input type="button" class="btn marginBottom20 bgColorDarkGrey fontSize16 width160 padding-4x-4y" onclick="location.href='add_sup_to_proj.php?id=<?=@$project_id?>';" value="<?=@$suppliers_label?>" /> 						     
					</div>
				</div>
				
				<div id="div_unread_tasks" class="flex margin-0-x-auto width50Percents border-black display-none dir-rtl">
				    <?php if($unread_tasks_num_rows > 0){ ?>
						<div class="width100Percents max-height-180 overflow-y-scroll alignCenter dir-rtl">
							<table align="center" class="dir-rtl" cellpadding="4" width="100%">
								<?php foreach ($unread_tasks as $ut){ 
										$user_id = @$ut->id_user;
										
										$reminder_time = @$ut->reminder_time;	
										$reminder_date = @$ut->reminder_date;
										
										$responsible_name = @$ut->responsible_name;
										if(strlen(@$responsible_name) > 15)
											$responsible_name = mb_substr($responsible_name,0,15,'UTF-8');
										$responsible_email = @$ut->responsible_email;
										
										$id_track_responsible = @$ut->id_track_responsible;
										$track_type = @$ut->track_type;			
										
										$chapter_name = @$ut->chapter_name;
										if(strlen(@$chapter_name) > 15)
										   $chapter_name = mb_substr($chapter_name,0,15,'UTF-8');
								   
										$subject = @$ut->subject;
										if(strlen(@$subject) > 15)
										   $subject = mb_substr(trim(preg_replace('/[\x{00A0}\x{200B}]+/u',' ',strip_tags($subject))),0,15,'UTF-8');
									   
										$area = @$ut->area;
										if(strlen(@$area) > 15)
										   $area = $subject = mb_substr(trim(preg_replace('/[\x{00A0}\x{200B}]+/u',' ',strip_tags($area))),0,15,'UTF-8');									   	

										$align_txt = 'alignRight';
										$padding_txt = 'paddingRight5';
										?>
										<tr class="fontSize13">	
											<td class="border-right-white alignCenter">
												<div class="<?=@$align_txt.' '.$padding_txt?>">
													<input type="checkbox" id="cbx_read_tasks_<?=@$ut->lmu_id?>" name="cbx_read_tasks[]" onclick="setToReadTask(<?=@$ut->lmu_id?>,<?=@$ut->updated_users?>)" checked />
													&nbsp;
													<a class="task_name text-decoration-none cursor-pointer" data-meetingid="<?=@$ut->id?>" data-projectid="<?=@$project->id?>" data-userid="<?=@$user_id?>" data-name="<?=@$ut->subject?>" data-area="<?=@$ut->area?>" data-recipient="<?=@$responsible_email?>" data-responsibleid="<?=@$ut->id_responsible?>" data-ispriority="<?=@$ut->is_priority?>" data-trackresponsibleid="<?=@$id_track_responsible?>" data-tracktype="<?=@$track_type?>" data-reminderdate="<?=@$reminder_date?>" data-remindertime="<?=@$reminder_time?>">
													   <?='<strong>'.@$responsible_name.'</strong>&nbsp;<span class="colorBlue font-weight-bold">|</span>&nbsp;'.@$chapter_name.'&nbsp;<span class="colorBlue font-weight-bold">|</span>&nbsp;'.@$subject.'&nbsp;<span class="colorBlue font-weight-bold">|</span>&nbsp;'.@$area?>
													</a>
												</div>
												<?php if($ut->remark != '') { ?>
													<div class="marginTop10 alignRight">
														<span class="colorGreen font-weight-bold">[<?=substr(@$ut->action_date,8,2).'/'.substr(@$ut->action_date,5,2)?>]</span>&nbsp;<?=html_entity_decode(@$ut->remark)?>
													</div>
										        <?php } ?>
											</td>
										</tr>
								<?php } ?>
							</table>	
						</div>  
					<?php } 
					else { ?>
						<div class="alignCenter">הלוג רק</div>
					<?php } ?>
				</div>
			</div>
		</form>
		
		<div class="modal fade dir-rtl" id="modalTaskFollowupActions" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog border-black">
				<div class="modal-content">
					<div class="modal-body">	
					   <div id="modalContent">
					       <div id="div_content_task_details"></div>
					       <form class="marginTop15 alignCenter">    
							    <div class="row">
							        <div class="flex justify-content-center">
									    <div width="12%">
										    <a class="btn btn-popup borderRadius0 text-dark" onclick="toEditeMeeting($('#hidden_meeting_id').val(),$('#hidden_iteration').val(),'fromProjectHome')">
												<img src="images/edit-button.svg" width="30" height="30" />
												<br/>
												<strong class="fontSize14">עדכון</strong>
									        </a>
										</div>
										<div width="12%">
										     <a class="btn btn-popup text-dark" onclick="duplicateRecord($('#hidden_meeting_id').val(),$('#hidden_iteration').val(),'fromProjectHome')">
												<img src="images/clone-solid.svg" width="30" height="30" />
												<br/>
												<strong class="fontSize14">שכפול</strong>
									         </a>	 
										</div>
										<div width="12%">
										    <a id="continuous_btn" class="btn btn-popup borderRadius0 text-dark text-decoration-none">
												<img src="images/arrow-right-from-bracket-solid.svg" width="30" height="30" />
												<br/>
												<strong class="fontSize14">המשך</strong>
									        </a>
										</div>
										<div width="12%">
										    <a id="history_btn" class="btn btn-popup borderRadius0 text-dark text-decoration-none">
												<img src="images/rectangle-list-regular.svg" width="30" height="30" />
												<br/>
												<strong class="fontSize14">הסטורי-ה</strong>
									        </a>
										</div>
									</div>
								</div>
								
								<div class="row marginTop15">
							        <div class="flex justify-content-center">
										<div width="12%">
										    <a id="reminder_btn" class="btn btn-popup borderRadius0 text-dark text-decoration-none">
												<i class="fa-solid fa-bell fontSize30"></i>
												<br/>
												<strong class="fontSize14">מעקב</strong>
									        </a>
										</div>
										<div width="12%">
										    <a id="send_email_btn" class="btn btn-popup borderRadius0 text-dark text-decoration-none">
												<img src="images/at-solid.svg" width="30" height="30" />
												<br/>
												<strong class="fontSize14">דוא''ל</strong>
									        </a>
										</div>
										<div width="12%">
										    <a id="send_whatsapp_btn" class="btn btn-popup borderRadius0 text-dark text-decoration-none">
												<img src="images/square-whatsapp.svg" width="30" height="30" />
												<br/>
												<strong class="fontSize14">WhatsApp</strong>
									        </a>
										</div>				
									</div>    	
							    </div>
								<div class="row marginTop5 dir-rtl">
								    <div class="col-md-12 alignCenter">
									   <a id="link_next_task"><i class="fa-solid fa-forward marginLeft10 fontSize24 cursor-pointer"></i></a>
									   <a id="link_prev_task"><i class="fa-solid fa-backward fontSize24 cursor-pointer"></i></a>
								    </div>
								</div>
						   </form>
					   </div>
					</div>
				</div>
           </div>
		</div>
		
		<div class="modal fade dir-rtl" id="modalContinuousTask" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
				<div class="modal-content">  
					<div class="modal-body">	
					   <div id="modalContent">
					        <form class="alignCenter">
						       <div class="marginTop15 fontSize18 alignCenter">בדרך ליצור עבורך משימת המשך לפני כן, תציין אם ברצונך לסמן סטטוס משימה כ:</div>
							   <div class="marginTop15">
									<input type="radio" id="done" name="progress_status" value="1" onclick="continuousTask($('#hidden_meeting_id').val(),'בוצע/נמסר','','fromProjectHome')" />&nbsp;בוצע/נמסר
							        <input type="radio" id="archive" name="progress_status" class="marginRight8" value="2" onclick="continuousTask($('#hidden_meeting_id').val(),'ארכיון','','fromProjectHome')" />&nbsp;ארכיון
							        <input type="radio" id="no_change" name="progress_status" class="marginRight8" value="3" onclick="continuousTask($('#hidden_meeting_id').val(),'no_change','','fromProjectHome')" />&nbsp;ללא שינוי
							   </div> 
						   </form>
					  </div>
					</div>
				</div>
            </div>
		</div>
		
		<div class="modal fade dir-rtl" id="modalHistoryTasks" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
				<div class="modal-content">  
					<div class="modal-body">	
					    <div id="modalHistoryTasksContent"></div>
					</div>
				</div>
            </div>
		</div>

        <div class="modal fade dir-rtl" id="modalTaskFollowupRemark" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
				<div class="modal-content">
				   <div class="modal-header">
					  <h5 class="modal-title fontSize26"></h5>
					</div>
					<div class="modal-body">	
					    <div id="modalContent">
					        <div class="container">
								<form class="alignCenter">
									<div class="row">
									    <div class="col-md-1"></div>
										<div class="col-md-10">
										    <div class="marginTop15 flex justify-content-center">
												<div class="width50Percents">
													<div class="fontSize18">מי אחראי מעקב ?</div>
													<div class="marginTop5">
														<select id="users" class="paddingRight8 height30">	
														   <?php 
														   foreach($active_users as $item) { 
														   ?>
																<option value="<?=@$item->id?>">
																	<strong><?=@$item->firstname?> <?=@$item->lastname?></strong>
																</option>
																<?php
														   }
														   ?>						
													   </select>
												   </div>    
											    </div>
											    <div class="width50Percents alignRight">
												   <div>
												       <input type="radio" id="not_in_track" name="track_type" value="3" onclick="resetTrack()" />&nbsp;לא במעקב
												   </div> 
												   <div>
												       <input type="radio" id="manage_action_req" name="track_type" value="1" />&nbsp;<span class="colorOrange">פעולת ניהול נדרשת</span>
												   </div> 
												   <div>
												       <input type="radio" id="awaiting_action_from_sup" name="track_type" value="2" />&nbsp;<span class="colorGreen2">בהמתנה לפעולה מהספק</span>
												   </div>  								   
												</div>
									        </div>    
										</div>
										<div class="col-md-1"></div>
									</div>

                                    <div class="row">
										<div class="col-md-12">
										   <div class="marginTop15 flex justify-content-center borderBlue">
												<div id="div_reminder_date" class="width50Percents">
												   <div class="fontSize18 alignCenter">תזכורת מעקב <img src="images/bell-solid.svg" width="20" height="20" /></div>
												   <div class="marginTop5 alignCenter">
													   <input type="date" class="alignCenter" id="reminder_date" />
												   </div>
												</div>
												<div class="row width50Percents alignRight">
												    <div class="col-md-6">
                                                       <div>
												           <input type="radio" id="not_reminders" name="set_reminder_date_radio" value="0" onclick="setReminderDate('not_reminders')" />&nbsp;אין צורך בתזכורת
												       </div> 
													   <div>
														   <input type="radio" id="reminder_tomorrow" name="set_reminder_date_radio" value="1" onclick="setReminderDate('reminder_tomorrow')" />&nbsp;מחר
													    </div> 
													    <div>
														   <input type="radio" id="reminder_after_three_days" name="set_reminder_date_radio" value="2" onclick="setReminderDate('reminder_after_three_days')" />&nbsp;בעוד 3 ימים
													    </div> 
												    </div>
													<div class="col-md-6">
                                                        <div>
												           <input type="radio" id="reminder_after_one_week" name="set_reminder_date_radio" value="3" onclick="setReminderDate('reminder_after_one_week')" />&nbsp;בעוד שבוע
												        </div> 
													    <div>
														   <input type="radio" id="reminder_after_two_weeks" name="set_reminder_date_radio" value="4" onclick="setReminderDate('reminder_after_two_weeks')" />&nbsp;בעוד שבועיים
													    </div> 
													    <div>
														   <input type="radio" id="reminder_after_one_month" name="set_reminder_date_radio" value="5" onclick="setReminderDate('reminder_after_one_month')" />&nbsp;בעוד חודש
													    </div>
                                                        <div> 
														   <input type="radio" id="reminder_after_selected_date" name="set_reminder_date_radio" value="6" onclick="setReminderDate('reminder_after_selected_date')" />&nbsp;בתאריך ...
													    </div>														
												    </div>
												</div>
									        </div>	  
										</div>
									</div>

                                    <div class="marginTop10 fontSize20 alignCenter">הערה</div>
								    
								    <div class="row">
										<div class="col-3"></div>
										<div class="col-6">
                                            <div class="row">
											    <div class="col-6 alignRight">
												   <a class="text-decoration-none" onclick="$('#remark').css({'direction':'ltr','padding-left':'10px'})">EN</a>&nbsp;|
											       <a class="text-decoration-none" onclick="$('#remark').css({'direction':'rtl','padding-right':'10px'})">HE</a>																			
												</div>
												
												<div class="col-6 alignLeft">
												    <a onclick="$('#remark').val('')">Clear</a>
												</div>
                                            </div>											
											
											<div class="marginTop5 alignCenter">
												<textarea id="remark" class="paddingRight10 width100Percents" rows="5"></textarea>
											</div>
									    </div>
										<div class="col-3"></div>
				                    </div>
                                    
									<div id="div_track_alert" class="marginTop15 colorRed alignCenter"></div>
		   
									<div class="marginTop15 alignCenter">
									  <input type="button" class="btn btn-primary btn-valid borderRadius-0-25-rem width120 font-weight-bold marginLeft10 alignCenter" value="שמור מעקב" onclick="fillAddRemark($('#hidden_meeting_id').val(),'','for_closing')" />
									  <input type="button" class="btn btn-valid borderRadius-0-25-rem width70 bg-dark font-weight-bold alignCenter" value="ביטול" onclick="hidePopup('modalTaskFollowupRemark','',0,'fromProjectHome')" />
									</div>
							   </form>
                           </div>
					  </div>
					</div>
				</div>
           </div>
		</div>

        <div class="modal fade dir-rtl" id="modalSendEmail" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidbackground-color:black;den="true">
            <div class="modal-dialog">
				<div class="modal-content">
				    <div class="modal-header">
					   <h5 class="modal-title fontSize26"></h5>
					</div>
					<div class="modal-body">	
					    <div id="modalContent">
					        <form class="marginTop15 alignCenter">
						        <div class="alignCenter fontSize20">לשלוח ל</div>
								<div>
								    <input id="email_recipient" class="marginTop10 paddingRight10" type="email" placeholder="לשלוח ל" />
								</div>
								<div class="marginTop15">
								    <input type="button" id="set_responsible_email_btn" class="btn borderRadius-0-25-rem btn-primary btn-valid font-weight-bold alignCenter" value="שמור את דוא''ל האחראי" onclick="setResponsibleEmail($('#hidden_responsible_id').val(),'')" />
								</div>
								<div class="marginTop10 alignCenter fontSize20">נושא דוא''ל</div>
                                <div>
								    <input id="email_subject" class="marginTop10 paddingRight10" type="text" placeholder="נושא דוא''ל" />
								</div>									
								<div class="marginTop10 alignCenter fontSize20">גופן דוא''ל</div>
								<div class="marginTop10">
							       <textarea id="email_body" class="paddingRight10 width80Percents" rows="5" placeholder="גופן דוא''ל"></textarea>
							    </div>
								<div class="marginTop15 fontSize20 alignCenter">תאריך תזכורת</div>
							    <div class="marginTop10 alignCenter">
							       <input type="date" class="alignRight paddingRight10" id="email_reminder_date" value="<?=date("Y-m-d",strtotime(date('Y-m-d').'+7 days'))?>" />
							    </div>
							    <div class="marginTop15">
							       <input type="button" class="btn btn-primary btn-valid borderRadius-0-25-rem width70 font-weight-bold marginLeft10 alignCenter" value="שלח" onclick="sendEmail($('#hidden_meeting_id').val(),'','for_closing','fromProjectHome')" />
							       <input type="button" class="btn btn-valid borderRadius-0-25-rem width70 bg-dark font-weight-bold alignCenter" value="ביטול" onclick="hidePopup('modalSendEmail','',0,'fromProjectHome')" />
							    </div>
						    </form>
					    </div>
					</div>
				</div>
            </div>
		</div>	
	</body>
</html>

<script>
$(document).ready(function() { 
    let project_id;
	let meeting_id;
	let iteration;
	let user_id;
	let subject;
	let area;
	let recipient;
	let responsible_id;
	let is_priority;
	let remark;
    let track_responsible_id;
	let track_type;
	let reminder_date;
    let reminder_time;
	
    if(localStorage.getItem("is_modal_task_actions_opened")){
	   project_id = localStorage.getItem("project_id");
	   meeting_id = localStorage.getItem("meeting_id");
	   iteration = localStorage.getItem("iteration");
	   subject = localStorage.getItem("subject");
	   area = localStorage.getItem("area");
	   recipient = localStorage.getItem("recipient");
	   responsible_id = localStorage.getItem("responsible_id");
	   remark = localStorage.getItem("remark");
	   track_type = localStorage.getItem("track_type");
	   is_priority = 1;
	   
	   if(localStorage.getItem("is_priority") == 1 || localStorage.getItem("is_five_priorities"))
	      is_priority = 0;
	  
	   let form_data = new FormData();
       form_data.append('id_meeting',meeting_id);
	   
	   $.ajax({
			type: 'POST',
			url: 'task_details.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,
			success: function(data){
				let task_details = data.split('|~|');
				let content = fillContentTaskDetails(meeting_id,'',task_details,true);
				$('#div_content_task_details').html(content);
			},
	   });
	   
	   setBellBcgColor(track_type);
	   setEmergencyTaskCSS(is_priority);
	   $('#modalContent').append("<input type='hidden' id='hidden_meeting_id' value='"+meeting_id+"'><input type='hidden' id='hidden_iteration' value='"+iteration+"'><input type='hidden' id='hidden_project_id' value='"+project_id+"'><input type='hidden' id='hidden_user_id' value='"+user_id+"'><input type='hidden' id='hidden_name' value='"+subject+"'><input type='hidden' id='hidden_area' value='"+area+"'><input type='hidden' id='hidden_recipient' value='"+recipient+"'><input type='hidden' id='hidden_responsible_id' value='"+responsible_id+"'><input type='hidden' id='hidden_is_priority' value='"+is_priority+"'><input type='hidden' id='hidden_remark' value='"+remark+"'><input type='hidden' id='hidden_track_responsible_id' value='"+track_responsible_id+"'><input type='hidden' id='hidden_reminder_date' value='"+reminder_date+"'><input type='hidden' id='hidden_reminder_time' value='"+reminder_time+"'>"); 
	   $('#modalTaskFollowupActions').modal('show');
	   localStorage.removeItem('is_modal_task_actions_opened');
	}
	
	if(localStorage.getItem("is_modal_tasks_hystory_opened")){
		meeting_id = localStorage.getItem("meeting_id");
		project_id = localStorage.getItem("project_id");
		
		let form_data = new FormData();
        form_data.append('id_meeting',meeting_id);
		form_data.append('id_project',project_id);
	    form_data.append('sort_by',localStorage.getItem("sort_by"));
		
	    $.ajax({
			type: 'POST',
			url: 'fill_tasks_followup_history.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,
			success: function(data){
				$('#modalHistoryTasksContent').html(data);	
			},
	   });
	   
	   $('#modalContent').append("<input type='hidden' id='hidden_meeting_id' value='"+meeting_id+"'><input type='hidden' id='hidden_project_id' value='"+project_id+"'>");
	   $('#modalTaskFollowupActions').modal('hide');
	   $('#modalHistoryTasks').modal('show');
	   localStorage.removeItem('is_modal_tasks_hystory_opened');
	};
	
	$('[class^="task_name"]').on('click', function() {   
	   project_id = $(this).data('projectid');    
	   meeting_id = $(this).data('meetingid');    	   
	   subject = $(this).data('name');
	   user_id = $(this).data('userid');
	   area = $(this).data('area');
	   recipient = $(this).data('recipient');
	   responsible_id = $(this).data('responsibleid');
	   is_priority = $(this).data('ispriority');
	   remark = $(this).data('remark');
	   track_responsible_id = $(this).data('trackresponsibleid');
	   track_type = $(this).data('tracktype');
	   reminder_date = $(this).data('reminderdate');
	   reminder_time = $(this).data('remindertime');
	   let form_data = new FormData();
       form_data.append('id_meeting',meeting_id);
	   
	   $.ajax({
			type: 'POST',
			url: 'task_details.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,
			success: function(data){
				let task_details = data.split('|~|');
				let content = fillContentTaskDetails(meeting_id,'',task_details,true);
				$('#div_content_task_details').html(content);
			},
	   });
	   
	   setBellBcgColor(track_type);
	   setEmergencyTaskCSS(is_priority);
	   $('#modalContent').append("<input type='hidden' id='hidden_meeting_id' value='"+meeting_id+"'><input type='hidden' id='hidden_project_id' value='"+project_id+"'><input type='hidden' id='hidden_user_id' value='"+user_id+"'><input type='hidden' id='hidden_name' value='"+subject+"'><input type='hidden' id='hidden_area' value='"+area+"'><input type='hidden' id='hidden_recipient' value='"+recipient+"'><input type='hidden' id='hidden_responsible_id' value='"+responsible_id+"'><input type='hidden' id='hidden_is_priority' value='"+is_priority+"'><input type='hidden' id='hidden_remark' value='"+remark+"'><input type='hidden' id='hidden_track_responsible_id' value='"+track_responsible_id+"'><input type='hidden' id='hidden_track_type' value='"+track_type+"'><input type='hidden' id='hidden_reminder_date' value='"+reminder_date+"'><input type='hidden' id='hidden_reminder_time' value='"+reminder_time+"'>"); 
	   $('#modalTaskFollowupActions').modal('show');
	});
	
	$('[id="continuous_btn"]').on('click', function() {
		$('#modalTaskFollowupActions').modal('hide');
	    $('#modalContinuousTask').modal('show');
	});
	
	$('[id="history_btn"]').on('click', function() {
		meeting_id = $('#hidden_meeting_id').val();
		project_id = $('#hidden_project_id').val();		
		
		let form_data = new FormData();
        form_data.append('id_meeting',meeting_id);
		form_data.append('id_project',project_id);
	   
	    $.ajax({
			type: 'POST',
			url: 'fill_tasks_followup_history.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,
			success: function(data){
				$('#modalHistoryTasksContent').html(data);	
			},
	   });
	   
	   $('#modalTaskFollowupActions').modal('hide');
	   $('#modalHistoryTasks').modal('show');
	});
	
	$('[id="reminder_btn"]').on('click', function() {
       meeting_id = $('#hidden_meeting_id').val(); 
	   subject = $('#hidden_name').val();
	   area = $('#hidden_area').val();
	   
	   reminder_date = $('#hidden_reminder_date').val();
	   if(reminder_date != '0000-00-00') {
		  $('#div_reminder_date').show();
	      $('#reminder_date').val(reminder_date);
	   }
	   else 
		  $('#div_reminder_date').hide();
	 
	   $('.modal-title').html(subject+' - '+area);
	   
	   if($('#hidden_track_type').val() == 3)
		 $('#not_in_track').prop('checked',true);
	   if($('#hidden_track_type').val() == 1)
		 $('#manage_action_req').prop('checked',true);
	   if($('#hidden_track_type').val() == 2)
		 $('#awaiting_action_from_sup').prop('checked',true);
	 
	   $('#div_reminder_date').css('display','block');
	 
	   if($('#hidden_reminder_time').val() == 0) {
		 $('#div_reminder_date').css('display','none');
		 $('#not_reminders').prop('checked',true);
	   }
	   
	   if($('#hidden_reminder_time').val() == 1)
		 $('#reminder_tomorrow').prop('checked',true);
	   if($('#hidden_reminder_time').val() == 2)
		 $('#reminder_after_three_days').prop('checked',true);
	   if($('#hidden_reminder_time').val() == 3)
		 $('#reminder_after_one_week').prop('checked',true);
	   if($('#hidden_reminder_time').val() == 4)
		 $('#reminder_after_two_weeks').prop('checked',true);
	   if($('#hidden_reminder_time').val() == 5)
		 $('#reminder_after_one_month').prop('checked',true);
	   if($('#hidden_reminder_time').val() == 6) 
		 $('#reminder_after_selected_date').prop('checked',true);
	   
       $('#users option').each(function() {   
		   if(!$('#hidden_track_responsible_id').val()) {
			 if($(this).val() ==  $('#hidden_user_id').val()) 
			   $(this).prop('selected', true);
		   }
		   if($('#hidden_track_responsible_id').val() && ($(this).val() == $('#hidden_track_responsible_id').val()))
			  $(this).prop('selected', true);  
	   });
	   
	   $('#remark').val($('#hidden_remark').val());
	   
	   $('#modalTaskFollowupActions').modal('hide');
	   $('#modalTaskFollowupRemark').modal('show');
    });
	
	$('[name="track_type"]').on('change', function() {
		setData($('#hidden_meeting_id').val(),'','track_type',0,0,'popup');
	});
	
	$('[id="users"]').on('change', function() {
		setData($('#hidden_meeting_id').val(),0,'track_responsible_id',0,0,'popup');
	});
	
	$('[id="send_email_btn"]').on('click', function() {
       meeting_id = $('#hidden_meeting_id').val(); 
	   subject = $('#hidden_name').val();
	   area = $('#hidden_area').val();
	   $('.modal-title').html(subject+' - '+area);
	   $('#email_recipient').val($('#hidden_recipient').val());
	   $('#modalTaskFollowupActions').modal('hide');
	   $('#modalSendEmail').modal('show');
    });
	
	$('[id="send_whatsapp_btn"]').on('click', function() {
       meeting_id = $('#hidden_meeting_id').val(); 
	   project_id = $('#hidden_project_id').val(); 
	   subject = $('#hidden_name').val();
	   area = $('#hidden_area').val()
	   $('#modalTaskFollowupActions').modal('hide');
	   
	   let form_data = new FormData();
       form_data.append('id_meeting',meeting_id);
	   
	   $.ajax({
			type: 'POST',
			url: 'task_details.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,
			success: function(data){
            	let task_details = data.split('|~|');
				let content = fillContentTaskDetails(meeting_id,'',task_details,false);
				$('#contentToScreenshot').html(content);
				
				const element = document.getElementById('contentToScreenshot');
				const width = element.offsetWidth;
                const height = element.offsetHeight;

				html2canvas(element, {
                scale: 1 
                }).then(function(canvas) {
					let finalCanvas = document.createElement('canvas');
                    finalCanvas.width = width;
                    finalCanvas.height = height;
                    
					let ctx = finalCanvas.getContext('2d');
                    ctx.drawImage(canvas, 0, 0, width, height);
					
					const imageData = canvas.toDataURL('image/jpeg');

					$.ajax({
						type: 'POST',
						url: 'save_image.php',
						data: {imageData:imageData,meeting_id:meeting_id},
						success: function(response) {
							const imageUrl = 'https://davidnahmiasengineering.com/Development/'+response;	
							shareImage(imageUrl,meeting_id,project_id,'');
						}, 
				   });
                });
			},
	   });
    });
	
	$('[id="link_prev_task"]').on('click', function() {
	  	let form_data = new FormData();
        form_data.append('from','projects');
        form_data.append('id_project',$('#hidden_project_id').val());
		
		$.ajax({
			type: 'POST',
			url: 'fill_meeting_ids.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,
			success: function(data) {
				let meeting_ids = data;
				navigateTasks(meeting_ids,'prev');
			}, 
		});		   
	});
	
	$('[id="link_next_task"]').on('click', function() {
	   	let form_data = new FormData();
        form_data.append('from','projects');
        form_data.append('id_project',$('#hidden_project_id').val());
		
		$.ajax({
			type: 'POST',
			url: 'fill_meeting_ids.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,
			success: function(data) {
				let meeting_ids = data;
				navigateTasks(meeting_ids,'next');
			}, 
		});		   
	});
	
	let modalTaskFollowupActions = $('#modalTaskFollowupActions');
	let modalTaskFollowupRemark = $('#modalTaskFollowupRemark');
	let modalSendEmail = $('#modalSendEmail');
	
	$(window).click(function(event) {
	    if ($(event.target).is(modalTaskFollowupActions) || 
			$(event.target).is(modalTaskFollowupRemark) ||
			$(event.target).is(modalSendEmail)) {	
		      window.location.reload();
	    }
    });
})

function setToReadTask(log_id,updated_users){
	let form_data = new FormData();
    form_data.append('log_id',log_id);
	form_data.append('updated_users',updated_users);
	
	$.ajax({
		type: 'POST',
		url: 'set_to_read_task.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,
		success: function(data) {
			window.location.reload(true);
		}, 
	});
}
</script>

<style>
.colorBlue {
	color: #349feb;
}

.btn {
	font-size: 18px;
	border-radius: 20px;
	padding: 10px 22px;
	color: white;
	width: 200px;
}

.btn-popup {
	padding: 0px 10px;
	width: 90px!important;
}

.btn-valid {
	padding: 0.375rem 0.75rem;
	font-size: 1rem;
}

.btn:not(.btn-valid):not(.btn-popup):hover {
	background-color: silver!important;
	color: white!important;
}

.btn.btn-valid:hover {
    color: white;
}
</style>