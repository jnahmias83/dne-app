<?php
include 'functions/functions.php';

$meeting_id = @$_POST['meeting_id'];
$from = 'david@nahmias-eng.com';
$to = @$_POST['email_recipient'];

$boundary = md5(time());

$headers = "From: $from\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";

if($_POST['from'] == 'payments_missing_invoice') {
	$subject = 'חסר חשבונית';
	$html_message = "<html><body style='direction:rtl;'>";
	$html_message.= "שלום,<br/><br/> בפרוייקט ".$_POST['project_name_he']." התבצע תשלום בסך "."&#8362;".$_POST['paid_amount_vat_included']." ביום ".substr($_POST['payment_date'],8,2)."/".substr($_POST['payment_date'],5,2)."/".substr($_POST['payment_date'],2,2).".<br/><br/>טרם התקבלה חשבונית מס/קבלה.<br/><br/>אנא להעביר אלינו את המסמך במייל חוזר.<br/><br/>בברכה,<br/><br/>דוד נחמיאס.";
	$html_message .= "</body></html>";
	$body = "--$boundary\r\n";
	$body .= "Content-Type: text/html; charset=UTF-8\r\n";
	$body .= "Content-Transfer-Encoding: base64\r\n\r\n";
	$body .= chunk_split(base64_encode($html_message));
}
else {
	$query = $mysqli->prepare("SELECT p.id AS p_id,p.name_he AS p_name_he,
	                          r1.name AS r_name,r2.name AS po_name,
							  m.destination_date AS destination_date,
						      c.name AS c_name,m.area AS area,
							  t.name_he AS t_name_he,m.subject AS subject,
							  m.description AS description,ps.id AS ps_id,
							  m.task_creation_date AS task_creation_date,
							  ps.name_he AS ps_name_he,m.image1 AS image1,
						      m.image1_width AS image1_width,
							  m.image1_height AS image1_height,
							  m.image2 AS image2,m.image2_width AS image2_width,
							  m.image2_height AS image2_height   
						      FROM dne_meetings m 
						      LEFT JOIN dne_projects p ON m.id_project = p.id 
						      LEFT JOIN dne_responsibles r1 ON m.id_responsible = r1.id 
						      LEFT JOIN dne_responsibles r2 ON m.id_pass_on = r2.id 
						      LEFT JOIN dne_chapters c ON m.id_chapter = c.id 
						      LEFT JOIN dne_progress_status ps ON m.id_progress_status = ps.id
						      LEFT JOIN dne_tasks t ON m.id_task = t.id
						      WHERE m.id = ?");
	$query->bind_param('i',$meeting_id);	
	$query->execute(); 
	$query->store_result();
	$meeting = fetch_unique($query);
	
	if($query->num_rows == 0) {	
		$old_destination_date = date('Y-m-d',strtotime(@$meeting->task_creation_date.'+7 days'));
	}
	else {
		$query = fetch_unique($query);
		if(@$query->destination_date != '0000-00-00')
	        $old_destination_date = @$query->destination_date;
	    else 
			$old_destination_date = date('Y-m-d',strtotime(@$meeting->task_creation_date.'+7 days'));
	}

    $one = 1;
    $empty_remark = '';	
	$query = $mysqli->prepare("SELECT * FROM dne_log_meeting_updates 
							  WHERE id_meeting = ? 
							  AND is_remark_appears_log = ?
							  AND remark <> ?
							  ORDER BY id DESC LIMIT 1");
	$query->bind_param("iis",$meeting_id,$one,$empty_remark);
	$query->execute();
	$query->store_result();	
	$query = fetch_unique($query);
	$remark = @$query->remark;	
	$action_date = @$query->action_date;
										
	$description = @$meeting->description;	
	$all_remarks = '';
	
	foreach($log_meeting_updates as $item){
		$remark = html_entity_decode(@$item->remark);
		$action_date = @$item->action_date;
		
		$all_remarks .= "<div class='marginTop5'>[".substr(@$action_date,8,2).'/'.substr(@$action_date,5,2).'] - '.html_entity_decode(@$remark).'</div>';
		
		if(@$remark != '')
			$description.= "<div class='marginTop5 colorGreen'>[".substr(@$action_date,8,2).'/'.substr(@$action_date,5,2).'] - '.html_entity_decode(@$remark).'</div>';
	}																						
    
	$new_task_label = 'חדשה';
	$query = $mysqli->prepare("SELECT destination_date 
	                          FROM dne_log_meeting_updates 
							  WHERE id_meeting = ? 
							  AND action = ?");
	$query->bind_param("is",$id,$new_task_label);
	$query->execute();
	$query->store_result();	
	
	if($query->num_rows == 0) {	
		$old_destination_date = date('Y-m-d',strtotime(@$meeting->task_creation_date.'+7 days'));
	}
	else {
		$query = fetch_unique($query);
		if(@$query->destination_date != '0000-00-00')
	        $old_destination_date = @$query->destination_date;
	    else 
			$old_destination_date = date('Y-m-d',strtotime(@$meeting->task_creation_date.'+7 days'));
	}
	
	$destination_date = @$meeting->destination_date;
	
	$update_task_label = 'סטטוס/יעד/הערה';
	$continue_task_label = 'משימת המשך';
	$empty_date = '0000-00-00';
	$query = $mysqli->prepare("SELECT COUNT(id) AS count_id
							  FROM dne_log_meeting_updates 
							  WHERE id_meeting = ?
							  AND (action = ? OR action = ?)
							  AND destination_date <> ?");
	$query->bind_param("isss",$meeting_id,$update_task_label,$continue_task_label,$empty_date);
	$query->store_result();	
	$query = fetch_unique($query);
	$count_delay_dest_date = $query->count_id;  
	
	$subject = @$_POST['email_subject'];
	$body_nl2br = nl2br(@$_POST['email_body']);
	$html_message = "<html><body style='direction:rtl;'><h3>הודעה</h3>".$body_nl2br.
	                    "<table align='center' dir='rtl' style='margin-top:15px;' width='65%' border='1' cellspacing='1'>
					      <tr style='background-color:#4d7380;color:white;font-weight:bold;text-align:center;padding:5px;'>
						      <td colspan='3'><u>".@$meeting->p_name_he."</u></td>
						  </tr>
						  <tr style='text-align:center;'>
						      <td style='font-size:14px;border-left:1px solid white;'>משימת ".@$meeting->t_name_he."</td><td><span style='font-size:14px;'>אחראי</span><br/><strong style='font-size:16px;color:red;'>".@$meeting->r_name."</strong></td><td><span style='font-size:14px;'>להעביר ל</span><br/><span style='font-size:16px;'>".@$meeting->po_name."</span></td>
						  </tr>	 
						  <tr style='text-align:center;'>
						      <td colspan='3'><div style='font-size:14px;'>תאריך יעד <span style='color:red;font-size:16px;'>".substr($old_destination_date,8,2)."/".substr($old_destination_date,5,2)."/".substr($old_destination_date,0,4)."</span>";
	if($count_delay_dest_date > 0)
	    $html_message .= " נדחה ".$count_delay_dest_date.' פעמ(ים) ל- '.substr($destination_date,8,2)."/".substr($destination_date,5,2)."/".substr($destination_date,0,4);
						  	  
	$html_message.=	"            </div>
	                          </td>
						   </tr>";
    if(strlen($meeting->ps_name_he) > 1) 
        $html_message .= "<tr style='text-align:center;'>
	                          <td colspan='3'>
							     <div>סטטוס משימה</div>
								 <div>".@$meeting->ps_name_he."</div>
							  </td>     
						  </tr>";		  
	                      
		$html_message .= "<tr style='text-align:center;background-color:#cbddec;'>
						      <td colspan='3'><strong>".@$meeting->c_name."</strong></td>
						  </tr>
						  <tr style='text-align:center;background-color:#cbddec;'>
						      <td colspan='3'><strong>".preg_replace(['#<br\s*/?>#i','#<div[^>]*>|</div>#i'],'',@$meeting->subject)."<span style='font-size:20px;'>-</span>".preg_replace(['#<br\s*/?>#i','#<div[^>]*>|</div>#i'],'',@$meeting->area)."</strong></td>
						  </tr>
						  <tr style='text-align:center;background-color:#cbddec;'>
						     <td colspan='3'>".@$description."</td>
						  </tr>";
						  
    if(@$meeting->image1 != '') {
	    $image1Path = "https://davidnahmiasengineering.com/Development/uploads/".@$meeting->image1;
		$image1_height = 230;
	    $image1_width;
		                                    
		if(@$meeting->image1_height > 0) {
			$ratio_image1 = @$meeting->image1_width/@$meeting->image1_height; 
			$image1_width = $image1_height*$ratio_image1;	
		}
	   
	    $html_message .=    "<tr style='text-align:center;'>
	                            <td colspan='3'><img src='".@$image1Path."' width='".@$image1_width."' height='".@$image1_height."' alt='' /></td>
							</tr>";
	}
	
	if(@$meeting->image2 != '') {
	    $image2Path = "https://davidnahmiasengineering.com/Development/uploads/".@$meeting->image2;
		$image2_height = 230;
	    $image2_width;
		                                    
		if(@$meeting->image2_height > 0) {
			$ratio_image2 = @$meeting->image2_width/@$meeting->image2_height;	
			$image2_width = $ratio_image2*$image2_height;
		}
	   
	    $html_message .=    "<tr style='text-align:center;'>
	                            <td colspan='3'><img src='".@$image2Path."' width='".@$image2_width."' height='".@$image2_height."' alt='' /></td>
							</tr>";
	}
	
	$html_message .=        "<tr style='text-align:center;'>
                                <td colspan='3'><img src='https://davidnahmiasengineering.com/Development/images/davidnahmias_stripe.png' width='480' alt='' /></td>
							 </tr>	
		                </table>
	                </body></html>";
	$image_attachment = @$_POST['img_url'];

	$body = "--$boundary\r\n";
	$body .= "Content-Type: text/html; charset=UTF-8\r\n";
	$body .= "Content-Transfer-Encoding: base64\r\n\r\n";
	$body .= chunk_split(base64_encode($html_message));

	if (file_exists($image_attachment)) {
		$file = fopen($image_attachment, "rb");
		$image_attachment_content = fread($file, filesize($image_attachment));
		fclose($file);
		$image_attachment_content = chunk_split(base64_encode($image_attachment_content));

		$body .= "--$boundary\r\n";
		$body .= "Content-Type: image/jpeg; name=\"" . basename($image_attachment) . "\"\r\n";
		$body .= "Content-Disposition: attachment; filename=\"" . basename($image_attachment) . "\"\r\n";
		$body .= "Content-Transfer-Encoding: base64\r\n";
		$body .= "X-Attachment-Id: " . md5(time()) . "\r\n\r\n";
		$body .= $image_attachment_content;
	}

	$body .= "--$boundary--";
}

if (mail($to,$subject,$body,$headers)) {
	echo "Email sent successfully";
} else {
    echo "Email sending failed.";
}
?>