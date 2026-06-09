function redirectToAddTaskForThisChapter(chapter_id){
	let form_data = new FormData();		
	form_data.append('id_chapter',chapter_id);
	form_data.append('report_date',$('#report_date').val());
	
	$.ajax({
		type: 'POST',
		url: 'set_report_date_session.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,			
		success: function(data){			
		    let url = 'add_meeting.php?id=0&chapter_id='+chapter_id+'&project_id='+$('#project_id').val();
		    let data_array = data.split('_');
			if(data_array[0] != 0)
				url+= '&task_id='+data_array[0]+'&responsible_id='+data_array[1]+'&pass_on_id='+data_array[2];
			if($('#is_specific_filter').val()) 
   			   url+= '&is_specific_filter=1';
		    location.href = url;
		},
	});
}

function duplicateRecord(meeting_id,iteration,from){
	let form_data = new FormData();	
	form_data.append('table_name','dne_meetings');
	form_data.append('id_project',$('#hidden_project_id').val());
	form_data.append('id',meeting_id);

	$.ajax({
		type: 'POST',
		url: 'duplicate_record.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,			
		success: function(data){  
		    let url;
		    if(from == 'fromMeetings'){
		      url = 'add_meeting.php?project_id='+$('#project_id').val()+'&iteration='+iteration+'&id='+data;
			  if($('#is_specific_filter').val())
				url+='&is_specific_filter=1';
		    }
			else if(from == 'fromProjectHome'){
		        url = 'add_meeting.php?project_id='+$('#project_id').val()+'&id='+meeting_id+'&fromProjectHome=1'; 
	        }
            else 
               url = 'add_meeting.php?project_id='+from+'&id='+data;
			
		    location.href = url;
		},
	});				
}

function continuousTask(meeting_id,progress_status,iteration,from){
	let form_data = new FormData();	
	form_data.append('is_continuous_task',1);
	form_data.append('table_name','dne_meetings');
	form_data.append('id_project',$('#hidden_project_id').val());
	form_data.append('id',meeting_id);
	form_data.append('progress_status',progress_status);
	
	$.ajax({
		type: 'POST',
		url: 'duplicate_record.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,			
		success: function(data){  
		    let url;
		    if(from == 'fromMeetings') {
		      url = 'add_meeting.php?project_id='+$('#hidden_project_id').val()+'&iteration='+iteration+'&id='+data;
			  if($('#is_specific_filter').val())
				url+='&is_specific_filter=1';
			  url+= '&iscontinious=1';
		    }
			else if(from == 'fromProjectHome') {
		        url = 'add_meeting.php?project_id='+$('#project_id').val()+'&id='+meeting_id+'&fromProjectHome=1'; 
	        }
            else 
               url = 'add_meeting.php?project_id='+from+'&id='+data+'&iscontinious=1';;  
			
		    location.href = url;
		},
	});	
}

function setData(meeting_id,iteration,field,isRemark,forShare,screen_type){
	let task_created_by_elem = '#task_created_by_'+meeting_id;
	let subject_elem = '#subject_'+meeting_id;
	let area_elem = '#area_'+meeting_id;
	let description_elem = '#description_'+meeting_id;
	let id_task_elem = '#task_'+meeting_id;
	let id_responsible_elem = '#responsible_'+meeting_id;
	let id_pass_on_elem = '#pass_on_'+meeting_id;
	let task_creation_date_elem = '#popup_task_creation_date';
	let destination_date_elem = '#popup_destination_date_input';
	//let destination_date_elem = '#destination_date_'+meeting_id;
	if(forShare)
	    destination_date_elem = '#popup_destination_date';
	let id_progress_status_elem = '#_progress_status_'+meeting_id;
	
	if(forShare)
	    progress_status_elem = '#progress_status';
	
    let all_ids_to_edit = $('input[name="meetings_to_update_cbx[]"]:checked').map(function(){
		return $(this).val();
    }).get().join(',');

    let ids_remark_checked = $('input[name="log_meeting_updates[]"]:checked').map(function(){
		return $(this).val();
    }).get().join(',');
	
	let ids_remark_is_updates_checked = $('input[name="log_meeting_updates_is_updates[]"]:checked').map(function(){
		return $(this).val();
    }).get().join(',');
	
	let form_data = new FormData();
    form_data.append('id_project',$('#project_id').val());	
	form_data.append('meeting_id',meeting_id);
	form_data.append('forShare',forShare);
	form_data.append('all_ids_to_edit',all_ids_to_edit);
    
	if(field == 'subject'){
		form_data.append('field',field);
		form_data.append('subject',$(subject_elem).html());
	}
    else if(field == 'area'){
		form_data.append('field',field);
		form_data.append('area',$(area_elem).html());
	}
	else if(field == 'description'){
		form_data.append('field',field);
		form_data.append('description',$('#new_description').html());
	}
	else if(field == 'id_task'){
		form_data.append('field',field);
		form_data.append('id_task',$(id_task_elem).val());
	}
	else if(field == 'id_responsible'){
		form_data.append('field',field);
		form_data.append('id_responsible',$(id_responsible_elem).val());
	}
	else if(field == 'id_pass_on') {
		form_data.append('field',field);
		form_data.append('id_pass_on',$(id_pass_on_elem).val());
	}
	else if(field == 'task_creation_date'){
		form_data.append('field',field);
		form_data.append('task_creation_date',$(task_creation_date_elem).val());	
	}
	else if(field == 'destination_date'){
		form_data.append('field',field);
		form_data.append('ids_remark_checked',ids_remark_checked);
		form_data.append('id_user',$(task_created_by_elem).val());
		form_data.append('destination_date',$(destination_date_elem).val());
		form_data.append('isRemark',isRemark);
		form_data.append('remark',$('#remark_delay_target_date').html());
	}
	else if(field == 'id_progress_status'){
		form_data.append('field',field);
		form_data.append('ids_remark_checked',ids_remark_checked);
		form_data.append('id_user',$(task_created_by_elem).val());
		
		if(screen_type != 'popup2')
		   form_data.append('remark',$('#remark_changes_status').html());
	    else 
		   form_data.append('remark',$('#popup_remark').val());
		
		form_data.append('isRemark',isRemark);
		form_data.append('new_destination_date',$('#new_destination_date').val());
		form_data.append('screen_type',screen_type);
		form_data.append('field',field);
		form_data.append('id_progress_status',$(id_progress_status_elem).val());
	}
	else if(field == 'update_task'){
		form_data.append('ids_remark_checked',ids_remark_is_updates_checked);
		form_data.append('id_user',$(task_created_by_elem).val());		
		form_data.append('remark',$('#remark_changes_status_update').html());    
		form_data.append('isRemark',isRemark);
		form_data.append('new_destination_date',$('#new_destination_date_update').val());
		form_data.append('screen_type',screen_type);
		form_data.append('field',field);
		form_data.append('id_progress_status',$('#progress_status_update').val());	
	}
	else if(field == 'track_responsible_id'){	
		form_data.append('field',field);
		form_data.append('id_track_responsible',$('#users').val());
	}
	
	$.ajax({
		type: 'POST',
		url: 'set_data.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,			
		success: function(data){
			let url;
			if(!forShare || (forShare && iteration != '')){
				if(field == 'id_progress_status') 
			        $('#modalTaskFollowupRemark').modal('hide');			
				url = 'meetings.php?project_id='+$('#project_id').val()+'&row=row_'+iteration;
				
				if($('#is_specific_filter').val())
				    url+='&is_specific_filter=1';
			}
			
			if(screen_type == 'add_meeting'){
			   let url_get = new URL(window.location.href);
			   let params = new URLSearchParams(url_get.search);
			   let all_ids_to_edit = params.get('all_ids_to_edit');
               
			   url = 'add_meeting.php?project_id='+$('#project_id').val()+'&id='+meeting_id+'&iteration='+iteration+'&all_ids_to_edit='+all_ids_to_edit;		
			}
			
		    if(!forShare && iteration == ''){
			   url = 'projects.php';
			}
			
			if(field == 'update_task'){
				let form_data1 = new FormData();
				form_data1.append('id_meeting', localStorage.getItem('next_meeting_id'));
				
				$.ajax({
					type: 'POST',
					url: 'get_meeting_item.php',
					data: form_data1,
					cache: false,
					processData: false,
					contentType: false,
					success: function(data1){		
						let data_array = data1.split('-');
						$('#hidden_is_priority').val(data_array[0]);
						$('#hidden_track_type').val(data_array[1]);
						setEmergencyTaskCSS(data_array[0]);
						setBellBcgColor(parseInt(data_array[1]) || 0);

						let form_data2 = new FormData();
						form_data2.append('id_meeting', localStorage.getItem('next_meeting_id'));
						form_data2.append('all_ids_to_edit', '');						

						$.ajax({
							type: 'POST',
							url: 'task_details.php',
							data: form_data2,
							cache: false,
							processData: false,
							contentType: false,
							success: function(data2){	
								let task_details = data2.split('|~|');
								let content = fillContentTaskDetails(localStorage.getItem('next_meeting_id'), '', task_details, true);		
								$('#div_content_task_details').html(content);    
								$('#modalUpdateTask').modal('hide');
								$('#modalTaskFollowupActions').modal('show'); 
								$(this).off('hidden.bs.modal');                                       
								setlocalStorage(localStorage.getItem('next_meeting_id'), iteration);
							},
							error: function(xhr, status, error) {}
						});
					},					
				});	
			}		
			
	        if(screen_type != 'popup' && screen_type != 'popup2' && (iteration != ''))
				location.href = url;	
		}
	});	
}

function setIsRemarkAppearsLog(log_id,log_type){
	let is_remark_appears_log = 0;
	if($('#is_remark_appears_log_'+log_id).is(':checked'))
		is_remark_appears_log = 1;
	
	let form_data = new FormData();
	form_data.append('id',log_id);
	form_data.append('type',log_type);
	form_data.append('is_remark_appears_log',is_remark_appears_log);
	
	$.ajax({
		type: 'POST',
		url: 'set_is_remark_appears_log.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,			
		success: function(data){
			localStorage.setItem('is_modal_tasks_hystory_opened',true);
			localStorage.setItem('meeting_id',$('#hidden_meeting_id').val());
			localStorage.setItem('iteration',$('#hidden_iteration').val());
			window.location.reload();
		},
	});	
}

function sortTaskFollowupData(sort_by){
    localStorage.setItem('sort_by',sort_by);
	localStorage.setItem('is_modal_tasks_hystory_opened',true);
	localStorage.setItem('project_id',$('#hidden_project_id').val());
	localStorage.setItem('meeting_id',$('#hidden_meeting_id').val());
	window.location.reload();
}

function setTextColor(element,checkbox,color){
	if(checkbox.checked) 
		$('#'+element).addClass(color);	 
	else 
		$('#'+element).removeClass(color);	
}

function toEditeMeeting(meeting_id,iteration,from){
	let all_ids_to_edit = '';
	$('input[type="checkbox"][id^="meetings_to_update_cbx"]').each(function(){
       if ($(this).prop('checked')) {
	      all_ids_to_edit+= $(this).val()+',';
	   }
    });
	
	all_ids_to_edit = all_ids_to_edit.substring(0,all_ids_to_edit.length - 1);
	
	let url;
	if(from == 'fromMeetings'){
		url = 'add_meeting.php?project_id='+$('#project_id').val()+'&id='+meeting_id+'&iteration='+iteration+'&all_ids_to_edit='+all_ids_to_edit;
		if($('#is_specific_filter').val())
		   url+='&is_specific_filter=1';
	}
	else if(from == 'fromProjectHome'){
		url = 'add_meeting.php?project_id='+$('#project_id').val()+'&id='+meeting_id+'&fromProjectHome=1'; 
	}
	else 
	   url = 'add_meeting.php?project_id='+from+'&id='+meeting_id;

    url += '&lang='+$('#hidden_lang').val();
	
	location.href = url;
}

function removeMeeting(id,iteration){
	if(confirm("האם אתה בטוח למחוק את המשימה הזאת ?")){
        let form_data = new FormData();	
		form_data.append('id',id);	
		
		$.ajax({
			type: 'POST',
			url: 'meeting_delete.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,			
			success: function(data){
				let url = 'meetings.php?project_id='+$('#project_id').val();	
				if($('#is_specific_filter').val())
				    url+= '&is_specific_filter=1';
				location.href = url;		
			},
		});
    }
    return false;
}

function removeResponsible(id){
	if(confirm("האם אתה בטוח למחוק את האחראי הזה ?")){
        let form_data = new FormData();	
		form_data.append('id',id);	
		
		$.ajax({
			type: 'POST',
			url: 'responsible_delete.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,			
			success: function(data){
				location.reload(true);			
			},
		});		
    }
    return false;
}

function removeOrder(id){
	if(confirm("Are you sure to remove this order?")){
        let form_data = new FormData();	
		form_data.append('id',id);
		
		$.ajax({
			type: 'POST',
			url: 'order_delete.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,			
			success: function(data){
				location.reload(true);			
			},
		});		
    }
    return false;
}

function removeAccount(id){
	if(confirm("Are you sure to remove this account?")){
        let form_data = new FormData();	
		form_data.append('id',id);
		
		$.ajax({
			type: 'POST',
			url: 'account_delete.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,			
			success: function(data){
				location.reload(true);			
			},
		});		
    }
    return false;
}

function removePayment(id){
	if(confirm("Are you sure to remove this payment?")){
        let form_data = new FormData();	
		form_data.append('id',id);	
		
		$.ajax({
			type: 'POST',
			url: 'payment_delete.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,			
			success: function(data){
				location.reload(true);			
			},
		});		
    }
    return false;
}

function removeAccountPayment(ap_type,record_id){
	if(ap_type == 'account') 
		removeAccount(record_id);
	else 
		removePayment(record_id);
}

function fillContentTaskDetails(meeting_id,iteration,task_details,forShare){
	let content = '<table dir="rtl" width="100%">';
	content += '<tr class="alignCenter height26"><td colspan="3" class="bgColorBlue2 colorWhite font-weight-bold alignCenter paddingTop2 paddingBottom5">'+(forShare ? task_details[27]+'<br/>' : '')+task_details[1]+'</td></tr>';
	
	content += '<tr class="alignCenter height26">';
	var taskBg    = task_details[26] || '#5b8dd9';
	var taskColor = task_details[28] || '#ffffff';
	content += '<td class="fontSize14 border-left-white"><span class="colorGrey font-weight-bold">משימת</span><br/><span style="display:inline-block;padding:6px 14px;color:'+taskColor+';background-color:'+taskBg+';font-weight:bold;box-sizing:border-box;border-radius:20px;">'+task_details[14]+'</span></td>';
	content += '<td class="fontSize14 border-left-white"><span class="colorGrey font-weight-bold">אחראי</span><br/><strong class="fontSize16 colorRed">'+task_details[2]+'</strong></td>';
	content += '<td class="fontSize14"><span class="colorGrey font-weight-bold">להעביר ל</span><br/><span class="fontSize16 color-4d7380">'+task_details[3]+'</span></td>';
	content += '</tr>';
	
	content += '<tr class="alignCenter height26">';
	content += "<td class='fontSize14' colspan='3'>יצירה<span class='marginRight10 marginLeft10 color-4d7380'>"+task_details[21].substr(8,2)+"/"+task_details[21].substr(5,2)+"/"+task_details[21].substr(2,2)+"</span>יעד";
	content += "<span class='marginRight5 colorRed font-weight-bold'>"+task_details[5].substr(8,2)+"/"+task_details[5].substr(5,2)+"/"+task_details[5].substr(2,2)+"</span>";
    content += "</td>";	
	content +='</tr>';
	
	if(task_details[11].length > 2){
		content +='<tr class="alignCenter height26">';
		content +='<td colspan="3">';	
		content += '<div class="fontSize16 alignCenter colorGrey font-weight-bold">סטטוס משימה</div>';
		content += '<div id="progress_status_list"></div>';
		//populateProgressStatusDropdown(meeting_id,iteration,task_details[0],task_details[10],forShare);
		content += "<div class='fontSize16 alignCenter'><span style='display:inline-block;padding:6px 10px;color:"+task_details[29]+";background-color:"+task_details[30]+";font-weight:bold;box-sizing:border-box;'>"+task_details[11]+"</span></div>";		
		content +='</td>';
		content +='</tr>';
	}
	
	content += '<tr class="alignCenter bgColor-ebf4f5 height26"><td colspan="3" class="border-bottom-white paddingTop5 alignCenter"><strong>'+task_details[6]+'</strong></td></tr>';
	content += '<tr class="alignCenter bgColor-ebf4f5 height26"><td colspan="3" class="border-bottom-white"><strong>'+decodeHtmlAndNl2br(task_details[12]).replace(/<br\s*\/?>|<\/?div[^>]*>/gi, '').trim()+'&nbsp;<span class="fontSize20 colorRed">|</span>&nbsp;'+decodeHtmlAndNl2br(task_details[7]).replace(/<br\s*\/?>|<\/?div[^>]*>/gi, '').trim()+'</strong></td></tr>';
	content += '<tr class="alignCenter bgColor-ebf4f5"><td colspan="3"><p>'+decodeHtmlAndNl2br(task_details[8])+'</p></td></tr>';
	
	if(forShare && task_details[22] == 1){
		let remember_content = '<i class="fa-solid fa-bell-slash colorGrey"></i>';
		if(task_details[24] != '0000-00-00')
			remember_content = '<i class="fa-solid fa-bell colorRed"></i>&nbsp;'+task_details[24].substr(8,2)+'.'+task_details[24].substr(5,2)+'.'+task_details[24].substr(2,2);
		content += '<tr class="alignCenter bg-fceaea"><td colspan="2" class="border-left-white"><p>'+task_details[23]+'</p></td><td>'+remember_content+'</td></tr>';    
	}
	
	if(task_details[9] != ''){
		const image1Path = 'uploads/'+task_details[9];
		let image1_height = 360;
		let ratio_image1 = task_details[16]/task_details[17];
		let image1_width = image1_height*ratio_image1;

		if (image1_width > 430) {
			image1_width = 430;
			image1_height = image1_width/ratio_image1;
		}

		content += '<tr><td colspan="3" class="alignCenter"><img src="'+image1Path+'" width="'+image1_width+'" height="'+image1_height+'" alt="" /></td></tr>';    
	}
	
	if (task_details[18] != ''){
		const image2Path = 'uploads/'+task_details[18];
		let image2_height = 360;
		let ratio_image2 = task_details[19]/task_details[20];
		let image2_width = image2_height*ratio_image2;

		if(image2_width > 430) {
			image2_width = 430;
			image2_height = image2_width/ratio_image2;
		}

		content += '<tr><td colspan="3" class="alignCenter"><img src="'+image2Path+'" width="'+image2_width+'" height="'+image2_height+'" alt="" /></td></tr>';
    }

	content += '<tr class="alignCenter"><td colspan="3"><img src="uploads/'+task_details[25]+'" width="480" alt="" /></td></tr>';
	content += '</table>';
	return content;
}

function fillMultiTasks(meeting_id,task_details){
	let content = '<table dir="rtl" width="100%">';
	content += '<tr class="alignCenter height26"><td class="bgColorBlue2 colorWhite font-weight-bold alignCenter paddingTop2 paddingBottom5 border-blue2">'+task_details[0]+'<br/>'+task_details[1]+'</td></tr>';
	content += task_details[2];
	content += '<tr class="alignCenter"><td colspan="3"><img src="uploads/'+task_details[3]+'" width="480" alt="" /></td></tr>';
	content += '</table>';
	return content;
}

function changeTargetDate(meeting_id,iteration){
    setData(meeting_id,iteration,'destination_date',0,1,'popup');
}

function populateProgressStatusDropdown(meeting_id,iteration,id_project,id_progress_status){
	let form_data = new FormData();
	form_data.append('id_meeting',meeting_id);
	form_data.append('iteration',iteration);
	form_data.append('id_project',id_project);	
	form_data.append('id_progress_status',id_progress_status);	

	$.ajax({
		type: 'POST',
		url: 'fill_progress_status_dropdown.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,
		success: function(data){
		   $('#progress_status_list').html(data);
		},
	})
}

function decodeHtmlAndNl2br(str){
  let txt = document.createElement("textarea");
  txt.innerHTML = str;
  let decoded = txt.value;

  return decoded.split('\n')
    .filter(line => line.trim() !== '') 
    .map(line =>`<div>${line}</div>`)
    .join('');
}

function setReminderDate (reminder_type){
	let currentDate = new Date();
    let year,month,day;
   
    if(reminder_type == 0){
      $('#div_reminder_date').hide();
      $('#reminder_date').val('0000-00-00');
    }
    else if(reminder_type == 1){  
	   $('#div_reminder_date').show();
	   let date_tomorrow = new Date();
       date_tomorrow.setDate(currentDate.getDate() + 1);	   
	   date_tomorrow = formatDate(date_tomorrow);
	   $('#reminder_date').val(date_tomorrow);
    }
    else if(reminder_type == 2){  
	   $('#div_reminder_date').show();
	   
	   let date_after_three_days = new Date();
       date_after_three_days.setDate(currentDate.getDate() + 3);	   
	   date_after_three_days = formatDate(date_after_three_days);
	   $('#reminder_date').val(date_after_three_days);
    }
	else if(reminder_type == 3){  
	   $('#div_reminder_date').show();
	   
	   let date_after_one_week = new Date();
       date_after_one_week.setDate(currentDate.getDate() + 7);	   
	   date_after_one_week = formatDate(date_after_one_week);
	   $('#reminder_date').val(date_after_one_week);
    }
	else if(reminder_type == 4){  
	   $('#div_reminder_date').show();
	   
	   let date_after_two_weeks = new Date();
       date_after_two_weeks.setDate(currentDate.getDate() + 14);	   
	   date_after_two_weeks = formatDate(date_after_two_weeks);
	   $('#reminder_date').val(date_after_two_weeks);
    }
	else if(reminder_type == 5){  
	   $('#div_reminder_date').show();
	   
	   let date_after_one_month = new Date();
       date_after_one_month.setMonth(currentDate.getMonth() + 1);	   
	   date_after_one_month = formatDate(date_after_one_month);
	   $('#reminder_date').val(date_after_one_month);
    }
	else if(reminder_type == 6){  
	   $('#div_reminder_date').show();
    }
}

function formatDate(date){
	let year = date.getFullYear();
	let month = String(date.getMonth() + 1).padStart(2, '0');
    let day = String(date.getDate()).padStart(2, '0');	  
	let formattedDate = year + '-' + month + '-' + day;
	return formattedDate;
}

function removeTaskFollowup(id){
	if(confirm("האם אתה בטוח למחוק את השורה הזאת ?")){
        let form_data = new FormData();	
		form_data.append('id',id);			
		$.ajax({
			type: 'POST',
			url: 'task_followup_delete.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,			
			success: function(data){
				localStorage.setItem('is_modal_tasks_hystory_opened',true);
			    window.location.reload();
			},
		});		
    }
    return false;
}

function resetTrack(){
	$('input[name="set_reminder_date_radio"][value="0"]').prop('checked', true);
	$('#remark').val("");
}

function setlocalStorage(id_meeting,iteration){
	localStorage.setItem('is_modal_task_actions_opened',true);
	localStorage.setItem('meeting_id',id_meeting);
	localStorage.setItem('iteration',iteration);
	localStorage.setItem('updated_id','meeting_'+id_meeting);

	if($('#hidden_project_id').length == 0)
		localStorage.setItem('project_id',$('#project_id').val());
	else 
		localStorage.setItem('project_id',$('#hidden_project_id').val());
	
	if($('#hidden_chapter').length == 0)
		localStorage.setItem('chapter',$('#chapter_data').val());
	else 	
		localStorage.setItem('chapter',$('#hidden_chapter').val());
	
	if($('#hidden_name').length == 0)
		localStorage.setItem('subject',$('#subject_data').val());
	else 	
		localStorage.setItem('subject',$('#hidden_name').val());
	
	if($('#hidden_name').length == 0)
		localStorage.setItem('area',$('#area_data').val());
	else 	
		localStorage.setItem('area',$('#hidden_area').val());
	
	if($('#hidden_recipient').length == 0)
		localStorage.setItem('recipient',$('#recipient_data').val());
	else 	
		localStorage.setItem('recipient',$('#hidden_recipient').val());
	
	if($('#hidden_responsible_id').length == 0)
		localStorage.setItem('responsible_id',$('#responsible_id_data').val());
	else 	
		localStorage.setItem('responsible_id',$('#hidden_responsible_id').val());
	
	if($('#hidden_remark').length == 0)
		localStorage.setItem('remark',$('#remark_data').val());
	else 	
		localStorage.setItem('remark',$('#hidden_remark').val());
	
	if($('#hidden_track_type').length == 0)
		localStorage.setItem('track_type',$('#track_type_data').val());
	else 	
		localStorage.setItem('track_type',$('#hidden_track_type').val());
	
	if($('#hidden_is_priority').length == 0)
		localStorage.setItem('is_priority',$('#is_priority_data').val());
	else 	
		localStorage.setItem('is_priority',$('#hidden_is_priority').val());
	
	if($('#hidden_user_id').length == 0)
		localStorage.setItem('user_id',$('#user_id_data').val());
	else 	
		localStorage.setItem('user_id',$('#hidden_user_id').val());
	
	if($('#hidden_track_responsible_id').length == 0)
		localStorage.setItem('track_responsible_id',$('#track_responsible_id_data').val());
	else 	
		localStorage.setItem('track_responsible_id',$('#hidden_track_responsible_id').val());
	
	if($('#hidden_reminder_date').length == 0)
		localStorage.setItem('reminder_date',$('#reminder_date_data').val());
	else 	
		localStorage.setItem('reminder_date',$('#hidden_reminder_date').val());
	
	if($('#hidden_reminder_time').length == 0)
		localStorage.setItem('reminder_time',$('#reminder_time_data').val());
	else 	
		localStorage.setItem('reminder_time',$('#hidden_reminder_time').val());
}

function fillLogTaskTracking(id_meeting,iteration,screen_type){
	let selectedValues = $('input[name="log_meeting_tracking[]"]:checked')
		.map(function() {
			return $(this).val();
		}).get();

	let ids_remark_tracking_checked = selectedValues.join(',');

	let reminder_time = 0;
	if ($('input[name="set_reminder_date_radio"]:checked').length > 0)
		reminder_time = $('input[name="set_reminder_date_radio"]:checked').val();

	let form_data = new FormData();
	form_data.append('id_user', $('#users').val());
	form_data.append('meeting_id', id_meeting);
	form_data.append('remark', $('#new_remark').html());
	form_data.append('reminder_date', $('#reminder_date').val());
	form_data.append('reminder_time', reminder_time);
	form_data.append('ids_remark_tracking_checked', ids_remark_tracking_checked);

	$.ajax({
		type: 'POST',
		url: 'fill_log_meeting_tracking.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,
		success: function(data){
			let url;

			if (iteration !== ''){
				url = 'meetings.php?project_id=' + $('#project_id').val() + '&row=row_' + iteration;
				if ($('#is_specific_filter').val())
					url += '&is_specific_filter=1';
			} else {
				url = 'projects.php';
			}

			if (screen_type !== 'popup' && screen_type !== 'popup2'){
				localStorage.setItem('is_modal_task_actions_opened', true);
				localStorage.setItem('project_id', $('#hidden_project_id').val());
				localStorage.setItem('meeting_id', $('#hidden_meeting_id').val());
				localStorage.setItem('iteration', $('#hidden_iteration').val());
				localStorage.setItem('subject', $('#hidden_name').val());
				localStorage.setItem('area', $('#hidden_area').val());
				localStorage.setItem('recipient', $('#hidden_recipient').val());
				localStorage.setItem('responsible_id', $('#hidden_responsible_id').val());
				localStorage.setItem('is_priority', $('#hidden_is_priority').val());
				localStorage.setItem('remark', $('#hidden_remark').val());
				localStorage.setItem('track_type', 1);
				localStorage.setItem('updated_id','meeting_'+id_meeting);
			}
			
			location.href = url;
		}
	});
}

function setResponsibleEmail(id_responsible,iteration){
	if($('#email_recipient').val() != '') {
	   let form_data = new FormData();
	   form_data.append('responsible_id',id_responsible);
	   form_data.append('email_recipient',$('#email_recipient').val());

	   $.ajax({
			type: 'POST',
			url: 'setResponsibleEmail.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,
			success: function(data){
				let url;	  
				if(iteration != ''){
					url = 'meetings.php?project_id='+$('#project_id').val()+'&row=row_'+iteration;
					if($('#is_specific_filter').val())
					   url+='&is_specific_filter=1';
				}
				else 
				   url = 'projects.php';	  
					
				if(data == 'The responsible email updated successfully!')
				   alert(data);
								
				location.href = url;
			},
	   });
	}
}

function sendEmail(id_meeting,iteration,screen_type,from){	
	let form_data = new FormData();
    form_data.append('id_meeting',id_meeting);
	$.ajax({
		type: 'POST',
		url: 'task_details.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,
		success: function(data){
			let task_details = data.split('|~|');
			let content = fillContentTaskDetails(id_meeting,iteration,task_details,false);
			$('#contentToScreenshot').html(content);
			
			const element = document.getElementById('contentToScreenshot');
		
			html2canvas(element).then(function(canvas){
				const imageData = canvas.toDataURL('image/jpeg');

				$.ajax({
					type: 'POST',
					url: 'save_image.php',
					data: {imageData:imageData},
					success: function(response){
					    let form_data = new FormData();
                        form_data.append('meeting_id',id_meeting);
 					    form_data.append('img_url',response);
						form_data.append('task_action',"דוא''ל");
						form_data.append('email_recipient',$('#email_recipient').val());
						form_data.append('email_subject',$('#email_subject').val());
						form_data.append('email_body',$('#email_body').val());
						form_data.append('reminder_date',$('#email_reminder_date').val());
						localStorage.setItem('updated_id','meeting_'+id_meeting);
							
						$.ajax({
							type: 'POST',
							url: 'send_email.php',
							data: form_data,
							cache: false,
							processData: false,
							contentType: false,
							success: function(data){								
								if(data == 'Email sent successfully')
								   alert(data); 
							    
								let url;
								if(iteration != ''){
									url = 'meetings.php?project_id='+$('#project_id').val()+'&row=row_'+iteration;
									if($('#is_specific_filter').val())
										url+='&is_specific_filter=1';
								}
								else {
								    if(from == 'fromProjects')
								      url = 'projects.php';
								    else if(from == 'fromProjectHome')
									   url = 'project_home.php?id='+$('#project_id').val();
								}
							   
								if(screen_type != 'popup' && screen_type != 'popup2')
								  location.href = url; 
							},
						});
					}, 
			   });
			});
		},
	});	
}

function hidePopup(modal,iteration,meeting_id,from){
	let modal_to_hide = '#'+modal;
    $(modal_to_hide).modal('hide');
	
	let url_get = new URL(window.location.href);
    let params = new URLSearchParams(url_get.search);
    let all_ids_to_edit = params.get('all_ids_to_edit');
	
	let form_data = new FormData();
	form_data.append('meeting_id',meeting_id);
	
	if(modal == 'modalTaskTracking') {
		$.ajax({
			type: 'POST',
			url: 'set_inactive_tracking.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false
	    });
	}
	
	let url;
	if(iteration != ''){
		url = 'meetings.php?project_id='+$('#project_id').val()+'&row=row_'+iteration;
		if($('#is_specific_filter').val())
			url+='&is_specific_filter=1';
	}
	else {
		if(from == 'fromProjects')
		  url = 'projects.php';
		else if(from == 'fromProjectHome')
		   url = 'project_home.php?id='+$('#project_id').val();
	}
	
	if(modal != 'modalTaskDescription' && modal != 'modalTaskFollowupChangeStatus' 
	   && modal != 'modalTaskFollowupDelayTargetDate'){
		localStorage.setItem('is_modal_task_actions_opened',true);
		localStorage.setItem('project_id',$('#hidden_project_id').val());
		localStorage.setItem('meeting_id',$('#hidden_meeting_id').val());
		localStorage.setItem('iteration',$('#hidden_iteration').val());
		localStorage.setItem('subject',$('#hidden_name').val());
		localStorage.setItem('area',$('#hidden_area').val());
		localStorage.setItem('recipient',$('#hidden_recipient').val());
		localStorage.setItem('responsible_id',$('#hidden_responsible_id').val());
		localStorage.setItem('is_priority',$('#hidden_is_priority').val());
		localStorage.setItem('remark',$('#hidden_remark').val());
		localStorage.setItem('track_type',0);		
	}		
	
	location.href = url;	
}

function setBellBcgColor(track_type){
    if(track_type == 0)
        $('#target-icon-popup').css('color','#888');
    else if(track_type == 1)
        $('#target-icon-popup').css('color','red');
}

function setEmergencyTaskCSS(is_priority){
	if(is_priority == 1) {
	   $("#fa-star").removeClass("fa-regular");
	   $("#fa-star").addClass("fa-solid");
	   $("#fa-star").addClass("colorRed");
	}
	else if(is_priority == 0){
	   $("#fa-star").removeClass("fa-solid");
	   $("#fa-star").removeClass("colorRed");
	   $("#fa-star").addClass("fa-regular");
	   $("#fa-star").addClass("colorBlack");
	}
}

function setEmergencyTask(id_meeting,iteration){
	let form_data = new FormData();
    form_data.append('id_meeting',id_meeting);
	form_data.append('id_project',$('#hidden_project_id').val());
	form_data.append('is_priority',$('#hidden_is_priority').val());

	$.ajax({
		type: 'POST',
		url: 'set_emergency_task.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,
		success: function(data){
            if(data == 'fivepriorities') {
			    alert('קיימים כבר חמש משימות דחופות לפרוייקט זה'); 
				localStorage.setItem('is_five_priorities',true);
			}
            else 
               localStorage.removeItem('is_five_priorities');				
            		
			let url;	  
			if(iteration != '') {
				url = 'meetings.php?project_id='+$('#project_id').val()+'&row=row_'+iteration;
				if($('#is_specific_filter').val())
				    url+='&is_specific_filter=1';
			}
			else 
				url = 'projects.php';		
                
            setlocalStorage(id_meeting,iteration);
			window.location.reload();	
		}			
    })
}

async function getProjectName(project_id){
    let form_data = new FormData();
    form_data.append('project_id', project_id);

    return await $.ajax({
        type: 'POST',
        url: 'get_project_name.php',
        data: form_data,
        cache: false,
        processData: false,
        contentType: false,
    });
}

async function shareImage(imageUrl,meeting_id,project_id,iteration,is_all_ids_to_edit){
    try {
        const response = await fetch(imageUrl);
        const blob = await response.blob();
        const file = new File([blob], "image.jpg", { type: blob.type });

        const reader = new FileReader();
        reader.readAsDataURL(file);

        reader.onloadend = async function (){
            const project_name = await getProjectName(project_id);
	  
            if(navigator.canShare && navigator.canShare({ files: [file] })){
                try {
                    await navigator.share({
                        title: "משימה חדשה",
                        text: "שלום,\nשים לב בבקשה למשימה זו בפרוייקט:\n" + project_name,
                        files: [file]
                    });
                } catch (shareError){
                    console.error("Erreur lors du partage :", shareError);
                }
            } else {
                alert("שיתוף קבצים לא נתמך במכשיר הזה.");
            }

            let url;
            let new_iteration = 1;

            if(iteration === 0){
				url = 'meetings.php?project_id=' + $('#project_id').val();
			}
			else if(iteration !== ''){
                if(iteration.indexOf("inserted") >= 0){
                    url = 'meetings.php?project_id=' + $('#project_id').val();
                } else {
                    new_iteration = iteration;
                    url = 'meetings.php?project_id=' + $('#project_id').val() + '&row=row_' + iteration;
                    if ($('#is_specific_filter').val()){
                        url += '&is_specific_filter=1';
                    }
                }
            } else {
                url = 'projects.php';
            }
			
            if(is_all_ids_to_edit)
				location.href = url;
			else {	
				$('#contentToScreenshot').hide();
				$('#modalTaskFollowupActions').modal('show');
				setlocalStorage(meeting_id,iteration);					
				location.href = url;
			}
        };

    } catch (error) {}
}

function navigateTasks(meeting_ids, action){
	let meeting_ids_array = meeting_ids.split(',');
	let current_meeting_id = $('#hidden_meeting_id').val();
	let index = meeting_ids_array.indexOf(current_meeting_id);

	if (action === 'prev') {
		index--;
		if (index < 0) index = meeting_ids_array.length - 1;
	} else if (action === 'next') {
		index++;
		if (index >= meeting_ids_array.length) index = 0;
	}

	let meeting_id = meeting_ids_array[index];

	if (!meeting_id || $.trim(meeting_id) === ''){
		$('#modalTaskFollowupActions').modal('hide');
		window.location.reload();
	}

	$('#hidden_meeting_id').val(meeting_id);

	let form_data1 = new FormData();
	form_data1.append('id_meeting', meeting_id);

	$.ajax({
		type: 'POST',
		url: 'get_meeting_item.php',
		data: form_data1,
		cache: false,
		processData: false,
		contentType: false,
		success: function(data) {
			let data_array = data.split('-');
			$('#hidden_is_priority').val(data_array[0]);
			$('#hidden_track_type').val(data_array[1]);
			setEmergencyTaskCSS(data_array[0]);
			setBellBcgColor(parseInt(data_array[1]) || 0);
		}
	});

	let project_id = $('#hidden_project_id').val();
	let subject = $('#hidden_name').val();
	let area = $('#hidden_area').val();
	let recipient = $('#hidden_recipient').val();
	let responsible_id = $('#hidden_responsible_id').val();

	function setOrUpdateHiddenInput(id,value){
		if ($('#'+id).length) {
			$('#'+id).val(value);
		} else {
			$('#modalContent').append("<input type='hidden' id='" + id + "' value='" + value + "'>");
		}
	}

	setOrUpdateHiddenInput('hidden_meeting_id', meeting_id);
	setOrUpdateHiddenInput('hidden_project_id', project_id);
	setOrUpdateHiddenInput('hidden_name', subject);
	setOrUpdateHiddenInput('hidden_area', area);
	setOrUpdateHiddenInput('hidden_recipient', recipient);
	setOrUpdateHiddenInput('hidden_responsible_id', responsible_id);

	let form_data2 = new FormData();
	form_data2.append('id_meeting',meeting_id);

	$.ajax({
		type: 'POST',
		url: 'task_details.php',
		data: form_data2,
		cache: false,
		processData: false,
		contentType: false,
		success: function(data) {
			let task_details = data.split('|~|');
			let content = fillContentTaskDetails(meeting_id, '', task_details, true);
			$('#div_content_task_details').html(content);
		}
	});

	$('#modalTaskDetails').modal('show');
}