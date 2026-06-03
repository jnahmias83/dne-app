<?php
require_once('tcpdf_min/config/tcpdf_config.php');
require_once('tcpdf_min/tcpdf.php');
include 'functions/functions.php';
error_reporting(E_ALL);
ini_set('display_errors', '1');

$project_id = @$_GET['project_id'];
$lang = @$_GET['lang'];
$asr = @$_GET['asr'];

$query = $mysqli->prepare("SELECT * FROM dne_projects WHERE id = ?");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$project = fetch_unique($query);

$query = $mysqli->prepare("SELECT logo_stread FROM dne_logos");
$query->execute();
$query->store_result();
$logo = fetch_unique($query);

$query = $mysqli->prepare("SELECT * FROM dne_vat");
$query->execute(); 
$query->store_result();
$vat = fetch_unique($query);

if($lang == 'EN') {
	$title = 'Project '.@$project->name."<br/>Budget Report";
	$dir_table = 'ltr';
	$style_table = "margin-top:25px;";
	$style_td = "text-align:left;padding-left:12px;";
	$style_td_totals = "text-align:righ;padding-right:12px;";
	$name_sort = 's.name';
	$text_align = 'text-align:left';
	$padding = 'padding-left';
}
else if($lang == 'HE') {
	$title = 'פרוייקט '.@$project->name_he."<br/>דו''ח תקציב";
    $dir_table = 'rtl';
	$style_table = "margin-top:12px;margin-left:4%;";
	$style_td = "text-align:right;padding-right:12px;";
	$style_td_totals = "text-align:left;padding-left:12px;";
	$name_sort = 's.name_he';
	$text_align = 'text-align:right';
	$padding = 'padding-right';
}

$query = $mysqli->prepare("SELECT ps.id AS id_projects_suppliers,SUM(a.approved_amount) AS sum_approved_amount,
                          SUM(p.paid_amount) AS sum_paid_amount,s.name AS name,s.bank_name AS bank_name,  
						  s.iban AS iban,ps.is_appears_pdf_wires AS is_appears_pdf_wires,
						  s.name AS s_name,s.name_he AS s_name_he
						  FROM dne_accounts_payments ap
						  LEFT JOIN dne_accounts a ON ap.id_account = a.id
						  LEFT JOIN dne_payments2 p ON ap.id_payment = p.id
						  LEFT JOIN dne_projects_suppliers ps on ap.id_projects_suppliers = ps.id
						  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id 
						  WHERE ps.id_project = ?
						  GROUP BY ps.id ORDER BY s.type,".$name_sort);
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$accounts_payments = fetch($query);

$title.= '<br/>'.substr(date('Y-m-d'),8,2).'/'.substr(date('Y-m-d'),5,2).'/'.substr(date('Y-m-d'),0,4);

$query = $mysqli->prepare("SELECT o.id_projects_suppliers AS id_projects_suppliers,
                          SUM(o.sum_order) AS total_sum_order,o.vat AS vat,
						  s.name AS s_name,s.name_he AS s_name_he,
						  sfow.name AS sfow_name,sfow.name_he AS sfow_name_he
                          FROM dne_orders o 
						  LEFT JOIN dne_projects_suppliers ps on o.id_projects_suppliers = ps.id 
						  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
						  LEFT JOIN dne_sup_field_of_work sfow ON s.id_field_of_work = sfow.id
						  WHERE ps.id_project = ? 
						  GROUP BY o.id_projects_suppliers ORDER BY s.type,".$name_sort);
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$orders_num_rows = $query->num_rows;
$orders = fetch($query);

$total_sum_order = 0;
$total_sum_order_vat_included = 0;

$total_sum_paid = 0;
$total_sum_paid_vat_included = 0;

$total_balance = 0;
$total_balance_vat_included = 0;

$total_to_pay = 0;

$count1 = 0;

class PDF extends TCPDF {
    public function Footer() { 
		$mysqli = new mysqli('localhost','davidnahmias','JmDn27091975','dn-engineering');
		
        if ($mysqli->connect_error) {
            die("Connection failed: " . $mysqli->connect_error);
        }
        
		$mysqli->set_charset("utf8");
		
        $query = "SELECT nickname,name,name_he FROM dne_projects WHERE id =".@$_GET['project_id'];
        $result = $mysqli->query($query);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
			if(@$_GET['lang'] == 'HE')
               $project_name = 'פרוייקט '.$row['name_he'];
		    else
			   $project_name = 'Project '.$row['name'];
		   
			$project_nickname = $row['nickname'];
	    } else {
            $project_name = $project_nickname = 'No Data Found';
        }
		
	    $pdf_file_name = substr(date('Y-m-d'),0,4).substr(date('Y-m-d'),5,2).substr(date('Y-m-d'),8,2).'-Budget Report-'.$project_nickname.'.pdf';
	
		$this->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);  
        $this->SetFont('freesans', '', 10);
        $this->SetY(-25);
		$this->Ln();
        $this->Cell(0, 0, '', 'T');

        $this->SetX($this->lMargin);

        $pagePagination = 'Page ' . $this->getAliasNumPage() . ' of ' . $this->getAliasNbPages();
	
	    if(@$_GET['lang'] == 'HE') {
		    $this->setPrintFooter(true);
			$this->Cell(60, 10, $project_name, 0, 0, 'R');
            $this->Cell(60, 10, $pagePagination, 0, 0, 'C');
            $this->Cell(78, 10, $pdf_file_name, 0, 0, 'L');
		}
		else {
			$this->setPrintFooter(false);
			$this->Cell(60, 10, $project_name, 0, 0, 'L');
            $this->Cell(100, 10, $pagePagination, 0, 0, 'C');
            $this->Cell(37, 10, $pdf_file_name, 0, 0, 'R');
		}
    }
}

$pdf = new PDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
$pdf->setFooterData(array(0,64,0), array(0,64,128));
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->SetPageOrientation('P');
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->setFontSubsetting(true);
$pdf->SetAlpha(1);
$pdf->SetTextShadow(['enabled' => false]);
$pdf->SetFont('dejavusans','',12,'',true);
$pdf->SetTextColor(0,0,0);
$pdf->setPrintHeader(false);

$html_header = '<table width="100%" style="margin-top:15px;"><tr><td style="text-align:center;"><img src="uploads/'.@$logo->logo_stread.'" /><br/></td></tr>';
$html1_body.= '<tr><td width="12px;">&nbsp;</td><td style="text-align:center;"><span dir="'.$dir_table.'"><strong><u>'.$title.'</u></strong></span></td></tr></table>';
$html1_body.= '<div class="row">';
$html1_body.= '<div class="col-md-12">';
$html1_body.= '<table dir="'.$dir_table.'" style="'.$style_table.'"><tr><td width="1%">&nbsp;</td><td><table cellpadding="4">';
$html1_body.='<tr height="35px;" style="font-size:12px;font-weight:bold;">';
$html1_body.='<th colspan="3" width="290px;" style="text-align:center;background-color:silver;border:1px solid black;" >'.getLang2($lang,'supplier').'</th>';
$html1_body.='<th colspan="3" width="270px;" style="text-align:center;border:1px solid black;background-color:#f5f5dc;">'.getLang2($lang,'ht').'</th>';
$html1_body.='<th width="20px;" style="background-color:white;border-left:1px solid black;border-right:1px solid black;">&nbsp;</th>';
$html1_body.='<th width="110px;" style="text-align:center;border:1px solid black;background-color:#dcf1fa;">'.getLang2($lang,'ttc').'</th>';
$html1_body.='</tr>';
$html1_body.='<tr height="35px;" style="font-size:12px;font-weight:bold;">';
$html1_body.='<th width="30px;" style="text-align:center;border:1px solid black;">'.getLang2($lang,'supplier_number').'</th>';
$html1_body.='<th width="130px;" style="text-align:center;border:1px solid black;">'.getLang2($lang,'name').'</th>';
$html1_body.='<th width="130px;" style="text-align:center;border:1px solid black;">'.getLang2($lang,'domain').'</th>';
$html1_body.='<th width="90px;" style="text-align:center;border:1px solid black;">'.getLang2($lang,'total_orders').'</th>';
$html1_body.='<th width="90px;" style="text-align:center;border:1px solid black;">'.getLang2($lang,'paid_amount').'</th>';
$html1_body.='<th width="90px;" style="text-align:center;border:1px solid black;">'.getLang2($lang,'order_balance').'</th>';
$html1_body.='<th width="20px" style="background-color:white;border-left:1px solid black;border-right:1px solid black;">&nbsp;</th>';
$html1_body.='<th width="110px;" style="text-align:center;border:1px solid black;">'.getLang2($lang,'account_to_be_paid').'</th>';
$html1_body.='</tr>';

$border_bottom = 'border-bottom:1px solid white';

foreach($orders as $item){ 
    $count1++;
	
	$s_name = @$item->s_name;
	$s_name_he = @$item->s_name_he;
	$sfow_name = @$item->sfow_name;
	$sfow_name_he = @$item->sfow_name_he;
	
    $data_bg_color = '#ffffff';
	if($count1%2 != 0) 
	   $data_bg_color = '#dedede';
   
    if($count1 == $orders_num_rows)
	   $border_bottom = 'border-bottom:1px solid black';    
   
    $query = $mysqli->prepare("SELECT SUM(p.paid_amount_vat_excluded) AS sum_paid_amount,
	                          SUM(a.approved_amount)-SUM(COALESCE(p.paid_amount,0)) AS account_to_be_paid
							  FROM dne_accounts_payments ap
							  LEFT JOIN dne_accounts a ON ap.id_account = a.id
							  LEFT JOIN dne_payments2 p ON ap.id_payment = p.id
							  LEFT JOIN dne_projects_suppliers ps on ap.id_projects_suppliers = ps.id 
							  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
							  WHERE ps.id = ? ORDER BY s.type,".$name_sort);
    $query->bind_param("i",$item->id_projects_suppliers);
    $query->execute();
    $query->store_result();
    $account_payment = fetch_unique($query);
	
    $total_sum_order_elem_display = '';
	if($item->total_sum_order > 0) 
	    $total_sum_order_elem_display = isset($item->total_sum_order)
		? ((floor($item->total_sum_order) == $item->total_sum_order)
			?number_format($item->total_sum_order,0,'.',',').'&nbsp;&#8362;'
			:number_format($item->total_sum_order,2,'.',',').'&nbsp;&#8362;')
		:'';

	$sum_paid_amount_display = '';
	if($account_payment->sum_paid_amount > 0) 
	   $sum_paid_amount_display = number_format(round($account_payment->sum_paid_amount),0,'.',',').'&nbsp;&#8362;';

	$balance = round($item->total_sum_order-$account_payment->sum_paid_amount);
	
	$balance_display = '';
	if($balance > 0) 
	  $balance_display = number_format($balance,0,'.',',').'&nbsp;&#x20aa;';
	
	$account_to_be_paid = 0;
	$account_to_be_paid_display = '';
	if($account_payment->account_to_be_paid > 0) { 
		$account_to_be_paid = round($account_payment->account_to_be_paid);
		$total_to_pay += $account_to_be_paid;
		$account_to_be_paid_display = number_format($account_to_be_paid,0,'.',',').'&nbsp;&#x20aa;';
	}
	else $account_to_be_paid = '';
	
	$total_sum_order += $item->total_sum_order;
	$total_sum_order_vat_included += $item->total_sum_order*(1+($item->vat/100));	
	
	$total_sum_paid += $account_payment->sum_paid_amount;
	$total_sum_paid_vat_included += $account_payment->sum_paid_amount*(1+($item->vat/100));
	
	$total_balance += $balance;
	$total_balance_vat_included += $balance*(1+($item->vat/100));
	
    $html1_body.='<tr height="30px;" style="font-size:12px;background-color:'.$data_bg_color.';">'; 
	$html1_body.='<td width="30px;" style="text-align:center;border:1px solid black;'.@$border_bottom.'">'.$count1.'</td>';
	$html1_body.='<td width="130px;" style="'.$style_td.'border:1px solid black;'.@$border_bottom.'">&nbsp;';
	
	if($lang != 'HE') {	
		if(strlen(@$s_name) > 18) 
			$html1_body.= mb_substr(@$s_name,0,18,'UTF-8');
		else  
	       $html1_body.= @$s_name;
	}
	else {
		if(strlen(@$s_name_he) > 18) 
			$html1_body.= mb_substr(@$s_name_he,0,18,'UTF-8');
		else  
	       $html1_body.= @$s_name_he;
	}
	
	$html1_body.='</td>';
	
	$html1_body.='<td width="130px;" style="'.$style_td.'border:1px solid black;'.@$border_bottom.'">';
	
	if($lang != 'HE') {	
		if(strlen(@$sfow_name) > 18) 
			$html1_body.= mb_substr(@$sfow_name,0,18,'UTF-8');
		else  
	       $html1_body.= @$sfow_name;
	}
	else {
		if(strlen(@$sfow_name_he) > 18) 
			$html1_body.= mb_substr(@$sfow_name_he,0,18,'UTF-8');
		else  
	       $html1_body.= @$sfow_name_he;
	}
	
	$html1_body.='</td>';
	$html1_body.='<td width="90px;" style="'.@$text_align.';'.@$padding.':12px;border:1px solid black;'.@$border_bottom.'">'.@$total_sum_order_elem_display.'</td>';
	$html1_body.='<td width="90px;" style="'.@$text_align.';'.@$padding.':12px;border:1px solid black;'.@$border_bottom.'">'.@$sum_paid_amount_display.'</td>';
	$html1_body.='<td width="90px;" style="'.@$text_align.';'.@$padding.':12px;border:1px solid black;'.@$border_bottom.'">'.@$balance_display.'</td>';
	$html1_body.='<td style="background-color:white;border-left:1px solid black;border-right:1px solid black;">&nbsp;</td>';
	$html1_body.='<td width="110px;" style="text-align:center;border:1px solid black;'.@$border_bottom.'">'.$account_to_be_paid_display.'</td>';
    $html1_body.='</tr>';
}

$total_to_pay_display = '';
if($total_to_pay != 0)
	$total_to_pay_display = isset($total_to_pay)
    ?number_format($total_to_pay,0,'.',',').'&nbsp;&#x20aa;'
    :'';

$html1_body.='<tr><td colspan="8">&nbsp;</td></tr>';

$html1_body.='<tr style="height:35px;font-size:12px;font-weight:bold;">';
$html1_body.='<th colspan="3"></th>';	
$html1_body.='<th style="text-align:center;border:1px solid black;'.@$border_bottom.'">'.getLang2($lang,'total_orders').'</th>';
$html1_body.='<th style="text-align:center;border:1px solid black;">'.getLang2($lang,'paid_amount').'</th>';
$html1_body.='<th style="text-align:center;border:1px solid black;'.@$border_bottom.'">'.getLang2($lang,'order_balance').'</th>';
$html1_body.='<th colspan="2"></th>';
$html1_body.='</tr>';

$html1_body.='<tr height="35px;" style="font-size:12px;font-weight:bold;background-color:#f5f5dc;">';
$html1_body.='<td colspan="3" style="'.$style_td_totals.'border:1px solid black;">'.getLang2($lang,'total_ht').'</td>';
$html1_body.='<td style="'.@$text_align.';'.@$padding.':12px;border:1px solid black;">'.number_format($total_sum_order,0,'.',',').'&nbsp;&#x20aa;</td>';
$html1_body.='<td style="'.@$text_align.';'.@$padding.':12px;border:1px solid black;">'.number_format($total_sum_paid,0,'.',',').'&nbsp;&#x20aa;</td>';
$html1_body.='<td style="'.@$text_align.';'.@$padding.':12px;border:1px solid black;">'.number_format($total_balance,0,'.',',').'&nbsp;&#x20aa;</td>';
$html1_body.='<td style="background-color:white;border-left:1px solid black;border-right:1px solid black;">&nbsp;</td>';
$html1_body.='<td style="text-align:center;background-color:#dcf1fa;border:1px solid black;">'.getLang2($lang,'total_to_be_paid').'</td>';
$html1_body.='</tr>';

$html1_body.='<tr height="24px;" style="font-size:12px;font-weight:bold;background-color:#dcf1fa;">';
$html1_body.='<td colspan="3" style="'.$style_td_totals.'border:1px solid black;">'.getLang2($lang,'total_ttc').'</td>';
$html1_body.='<td style="'.@$text_align.';'.@$padding.':12px;border:1px solid black;">'.number_format($total_sum_order_vat_included,0,'.',',').'&nbsp;&#x20aa;</td>';
$html1_body.='<td style="'.@$text_align.';'.@$padding.':12px;border:1px solid black;">'.number_format($total_sum_paid_vat_included,0,'.',',').'&nbsp;&#x20aa;</td>';
$html1_body.='<td style="'.@$text_align.';'.@$padding.':12px;border:1px solid black;">'.number_format($total_balance_vat_included,0,'.',',').'&nbsp;&#x20aa;</td>';
$html1_body.='<td style="background-color:white;border-left:1px solid black;border-right:1px solid black;">&nbsp;</td>';
$html1_body.='<td style="text-align:center;background-color:#dcf1fa;border:1px solid black;">'.$total_to_pay_display.'</td>';
$html1_body.='</tr>';
$html1_body.='</table></td></tr></table>';
$html1_body.='</div>';
$html1_body.='</div>';

$html1 = $html_header.$html1_body;

if($lang == "HE") 
	$pdf->setRTL(true);

$pdf->AddPage();
$pdf->writeHTMLCell(0, 0, '', '', $html1, 0, 1, 0, true, '', true);

if($asr == 1){
	foreach($accounts_payments as $item) {	
		$html2_body = '';
			
		$count3 = 0;
		$count4 = 0;
			
		$query = $mysqli->prepare("SELECT p.id AS p_id,p.name AS p_name,p.name_he AS p_name_he,
							      s.name AS s_name,s.name_he AS s_name_he,sfow.name AS sfow_name,sfow.name_he AS sfow_name_he
							      FROM dne_projects_suppliers ps
							      LEFT JOIN dne_projects p ON ps.id_project = p.id
							      LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
							      LEFT JOIN dne_sup_field_of_work sfow ON s.id_field_of_work = sfow.id
							      WHERE ps.id = ?");
		$query->bind_param("i",$item->id_projects_suppliers);
		$query->execute(); 
		$query->store_result();
		$supplier = fetch_unique($query);

		$query = $mysqli->prepare("SELECT o.sum_order AS sum_order,o.signature_date AS signature_date,
								  o.description AS description
								  FROM dne_orders o 
								  LEFT JOIN dne_projects_suppliers ps ON o.id_projects_suppliers = ps.id
								  LEFT JOIN dne_projects p ON ps.id_project = p.id
								  WHERE ps.id = ? ORDER BY o.signature_date");
		$query->bind_param("i",$item->id_projects_suppliers);
		$query->execute(); 
		$query->store_result();
		$elems_orders_num_rows = $query->num_rows;
		$elems_orders = fetch($query);
		
		$query = $mysqli->prepare("SELECT * FROM dne_payments2 WHERE id_projects_suppliers = ?");
		$query->bind_param("i",$item->id_projects_suppliers);
		$query->execute(); 
		$query->store_result();
		$elems_payments_num_rows = $query->num_rows;

		$zero = 0.0;
		$query = $mysqli->prepare("SELECT * FROM dne_accounts WHERE id_projects_suppliers = ? AND approved_amount > ?");
		$query->bind_param("id",$item->id_projects_suppliers,$zero);
		$query->execute(); 
		$query->store_result();
		$elems_accounts_num_rows = $query->num_rows;

		$elems_accounts_payments_num_rows = $elems_payments_num_rows + $elems_accounts_num_rows + 1;

		$query = $mysqli->prepare("SELECT ap.id AS ap_id,p.id AS p_id,a.id AS a_id,
								  ap.account_payment_date AS account_payment_date,
								  ap.account_payment_type AS account_payment_type,p.description AS p_description,
								  a.description AS a_description,p.paid_amount AS paid_amount_vat_included,
								  p.paid_amount_vat_excluded AS paid_amount_vat_excluded,
                                  a.approved_amount AS approved_amount
								  FROM dne_accounts_payments ap
								  LEFT JOIN dne_accounts a ON ap.id_account = a.id
								  LEFT JOIN dne_payments2 p ON ap.id_payment = p.id		 
								  LEFT JOIN dne_projects_suppliers ps ON ap.id_projects_suppliers = ps.id 
								  WHERE ps.id = ? ORDER BY ap.account_payment_date");
		$query->bind_param("i",$item->id_projects_suppliers);
		$query->execute(); 
		$query->store_result();
		$elems_accounts_payments = fetch($query);
			
		$zero = 0.0;
		$query = $mysqli->prepare("SELECT description,submit_date,submitted_account,pdf_submission 
							      FROM dne_accounts WHERE id_projects_suppliers = ? AND approved_amount = ?
								  ORDER BY submit_date");
		$query->bind_param("id",$item->id_projects_suppliers,$zero);
		$query->execute(); 
		$query->store_result();
		$elems_not_approved_accounts_num_rows = $query->num_rows;
		$elems_not_approved_accounts = fetch($query);

		if($lang == 'EN') {
			$title = '<table style="font-weight:bold;"><tr><td><u>Project '.$supplier->p_name.'</u></td></tr><tr><td><u>Status of orders and payments</u></td></tr><tr style="font-size:18px;"><td>'.$supplier->s_name.'</td></tr><tr><td>'.@$supplier->sfow_name.'</td></tr><tr><td>'.substr(date('Y-m-d'),8,2).'/'.substr(date('Y-m-d'),5,2).'/'.substr(date('Y-m-d'),0,4).'</td></tr></table>';
			$margin_orders_list_label = '4%';
			$orders_list_label = 'Orders';
			$table_orders_down_text = "This table contains only the original orders without the additions/cancellations/changes and does not constitute an estimate of the actual total of the final orders.";
			$margin_accounts_payments_list_label = '4%';
			$accounts_payments_list_label = 'Accounts / Payments';
			$table_ap_down_text = "(1) Approval of an account does not constitute confirmation of the completion/quality of the supply or the work.";
			$margin_not_approved_accounts_list_label = '4%';
		    $not_approved_accounts_list_label = 'Submitted accounts and not yet approved';      
		}
		else if($lang == 'HE') {
		    $title = '<table style="font-weight:bold;"><tr><td><u>פרוייקט '.$supplier->p_name_he."</u></td></tr><tr><td><u>סטטוס הזמנות ותשלומים</u></td></tr>".'<tr style="font-size:18px;"><td>'.$supplier->s_name_he.'</td></tr><tr><td>'.@$supplier->sfow_name_he.'</td></tr><tr><td>'.substr(date('Y-m-d'),8,2).'/'.substr(date('Y-m-d'),5,2).'/'.substr(date('Y-m-d'),0,4).'</td></tr></table>';
			$margin_orders_list_label = '4%';
			$orders_list_label = 'הזמנות';
			$table_orders_down_text = "טבלה זאת מרכזת את ההזמנות המקוריות ללא עדכון שותף של כל הביטולים והתוספות אליהן. על כן אין להתיחס אליה כמקור מהימן לגבי היקף ההזמנה בפועל הסופית.";
			$margin_accounts_payments_list_label = '4%';
			$accounts_payments_list_label = 'חשבונות / תשלומים שבוצעו';
			$table_ap_down_text = "אישור מנהל הפרויקט על חשבון שמוגש אינו מהווה אישור על השלמה/טיב האספקה והעבודה וכן אינו בהכרח תואם את ההזמנה באשר לפריסה ותנאי התשלומים.תשלום בפועל על ידי המזמין מהווה הסכמה לנ''ל."; 
			$margin_not_approved_accounts_list_label = '4%';
			$not_approved_accounts_list_label = 'חשבונות שהוגשו וטרם אושרו';  
		}
			
		$html2_body = '<tr><td width="2px;">&nbsp;</td><td style="text-align:center;padding-top:30px;"><span dir="'.$dir_table.'"><strong>'.$title.'</strong></span></td></tr></table>';	
		$html2_body.= '<table dir="'.$dir_table.'"><tr><td width="'.$margin_orders_list_label.'">&nbsp;</td><td><strong style="font-size:18px;">'.$orders_list_label.'</strong></td></tr><tr><td colspan="4" height="20px">&nbsp;</td></tr></table>';
		$html2_body.= '<table dir="'.$dir_table.'" style="margin-top:10px"><tr><td width="4%">&nbsp;</td><td><table cellpadding="4">';
		$html2_body.='<tr height="35px;" style="font-size:12px;font-weight:bold;background-color:silver;">';
		$html2_body.='<th width="30px;" style="text-align:center;border:1px solid black;">'.getLang2($lang,'supplier_number').'</th>';
		$html2_body.='<th width="70px;" style="text-align:center;border:1px solid black;">'.getLang2($lang,'signature_date').'</th>';
		$html2_body.='<th width="320px;" style="text-align:center;border:1px solid black;">'.getLang2($lang,'description').'</th>';
		$html2_body.='<th width="100px;" style="text-align:center;border:1px solid black;">'.getLang2($lang,'total_orders_h').'</th>';
		$html2_body.='</tr>';
			
		$elem_total_sum_orders = 0;
			
		$elem_border_bottom = 'border-bottom:1px solid white';
		
		foreach($elems_orders as $item){ 
			$count3++;
			$data_bg_color = '#ffffff';
				
			if($count3%2 != 0) 
			   $data_bg_color = '#dedede';
			   
			if($count3 == $elems_orders_num_rows)
	           $elem_border_bottom = 'border-bottom:1px solid black';

			$signature_date = '';
			if(@$item->signature_date != '0000-00-00')
				$signature_date = substr(@$item->signature_date,8,2).'/'.substr(@$item->signature_date,5,2).'/'.substr(@$item->signature_date,2,2);				
		
			$sum_orders_display = '';
			if(@$item->sum_order != 0.00){
				$sum_orders = @$item->sum_order;
				$sum_orders_display = isset($sum_orders)
				?number_format($sum_orders,0,'.',',').'&nbsp;&#x20aa;'
				:'';
			}

			$elem_total_sum_orders += $item->sum_order;
				
			$html2_body.='<tr height="25px;" style="font-size:12px;background-color:'.@$data_bg_color.'">';	
			$html2_body.='<td style="text-align:center;border:1px solid black;'.@$elem_border_bottom.'">'.@$count3.'</td>';
			$html2_body.='<td style="'.@$text_align.';'.@$padding.':12px;border:1px solid black;'.@$elem_border_bottom.'">&nbsp;'.@$signature_date.'</td>';
			$html2_body.='<td style="'.@$text_align.';'.@$padding.':12px;border:1px solid black;'.@$elem_border_bottom.'">&nbsp;'.@$item->description.'</td>';
			$html2_body.='<td style="'.@$text_align.';'.@$padding.':5px;border:1px solid black;'.@$elem_border_bottom.'">&nbsp;'.@$sum_orders_display.'</td>';
			$html2_body.='</tr>';
		}
			
		$elem_total_sum_order_display = '';
		if($elems_orders_num_rows > 0) 
			$elem_total_sum_orders_display = isset($elem_total_sum_orders)
			?number_format($elem_total_sum_orders, 0,'.',',').'&nbsp;&#8362;'
			:'';
		else 
			$elem_total_sum_orders = '';	
			
		$html2_body.='<tr style="background-color:#f5f5dc;font-size:12px;">';
		$html2_body.='<td colspan="3" style="'.$style_td_totals.'border:1px solid black;"><strong>'.getLang2($lang,'accounts_payments_total_orders').'</strong></td>';
		$html2_body.='<td style="text-align:right;padding-right:5px;border:1px solid black;"><strong>'.@$elem_total_sum_orders_display.'</strong></td>';
		$html2_body.='</tr>';
		$html2_body.='<tr style="font-size:10px;"><td colspan="3">'.@$table_orders_down_text.'</td></tr>';
		$html2_body.='</table></td></tr><table>';
			
		$html2_body.= '<table dir="'.$dir_table.'"><tr><td colspan="2" height="10px">&nbsp;</td></tr><tr><td width="'.$margin_accounts_payments_list_label.'">&nbsp;</td><td><strong style="font-size:18px;">'.$accounts_payments_list_label.'</strong></td></tr><tr><td colspan="6" height="20px">&nbsp;</td></tr></table>';
		$html2_body.= '<table dir="'.$dir_table.'" style="margin-top:30px"><tr><td width="4%">&nbsp;</td><td><table cellpadding="4">';
		$html2_body.='<tr height="35px;" style="font-size:12px;font-weight:bold;background-color:silver;">';
		$html2_body.='<th width="30px;" style="text-align:center;border:1px solid black;">'.getLang2($lang,'supplier_number').'</th>';
		$html2_body.='<th width="70px;" style="text-align:center;border:1px solid black;">'.getLang2($lang,'date').'</th>';
		$html2_body.='<th width="80px;" style="text-align:center;border:1px solid black;">'.getLang2($lang,'account_payment').'</th>';
		$html2_body.='<th width="250;" style="text-align:center;border:1px solid black;">'.getLang2($lang,'description').'</th>';
		$html2_body.='<th width="100px;" style="text-align:center;border:1px solid black;">'.getLang2($lang,'account_including_vat').'</th>';
		$html2_body.='<th width="100px;" style="text-align:center;border:1px solid black;">'.getLang2($lang,'payment_including_vat').'</th>';
		$html2_body.='</tr>';
			
		$elem_total_approved_amount_vat_included = 0;
		$elem_total_paid_amount_vat_included = 0;
		$elem_total_paid_amount_vat_excluded = 0;
			
		$elem_border_bottom = 'border-bottom:1px solid white';
			
		foreach($elems_accounts_payments as $item){
			$count4++;
				
			$data_bg_color = '#ffffff';
				
			if($count4%2 != 0) 
				$data_bg_color = '#dedede';
			   
			if($count4 == $elems_accounts_payments_num_rows)
				$elem_border_bottom = 'border-bottom:1px solid black';	
				
		    if(@$item->account_payment_type == 'payment' || @$item->account_payment_type == 'account' && @$item->approved_amount > 0.0){	
				if(@$item->account_payment_type == 'account') {		
					$account_payment_type = strtolower(getLang2($lang,'account'));
					$description = $item->a_description;
			    }
				else {
					 $account_payment_type = strtolower(getLang2($lang,'payment'));
					 $description = $item->p_description;
				}
					
				$account_payment_date = '';
				if(@$item->account_payment_date != '0000-00-00')
					$account_payment_date = substr(@$item->account_payment_date,8,2).'/'.substr(@$item->account_payment_date,5,2).'/'.substr(@$item->account_payment_date,2,2);				
						
				if(@$item->account_payment_type == 'account'){
					$approved_amount = @$item->approved_amount;	
					$approved_amount_display = isset($approved_amount)
					?number_format($approved_amount,0,'.',',').'&nbsp;&#x20aa;'
					:'';
				}
					
				$paid_amount_vat_included_display = '';
				if(@$item->paid_amount_vat_included != 0.00){
					$paid_amount_vat_included = @$item->paid_amount_vat_included;
					$paid_amount_vat_included_display = isset($paid_amount_vat_included)
					?number_format($paid_amount_vat_included,0,'.',',') .'&nbsp;&#x20aa;'
					:'';
				}

				$elem_total_approved_amount_vat_included +=$item->approved_amount;
				$elem_total_paid_amount_vat_included +=$item->paid_amount_vat_included;
				$elem_total_paid_amount_vat_excluded +=$item->paid_amount_vat_excluded;
					
				$html2_body.='<tr height="25px;" style="font-size:12px;background-color:'.@$data_bg_color.';">';	
				$html2_body.='<td style="text-align:center;border:1px solid black;'.@$elem_border_bottom.'">'.@$count4.'</td>';
				$html2_body.='<td style="'.@$text_align.';'.@$padding.':12px;border:1px solid black;'.@$elem_border_bottom.'">&nbsp;'.@$account_payment_date.'</td>';
				$html2_body.='<td style="text-align:center;border:1px solid black;'.@$elem_border_bottom.'">&nbsp;'.@$account_payment_type.'</td>';
				$html2_body.='<td style="'.@$text_align.';'.@$padding.':12px;border:1px solid black;'.@$elem_border_bottom.'">&nbsp;'.@$description.'</td>';
				$html2_body.='<td style="'.@$text_align.';'.@$padding.':5px;border:1px solid black;'.@$elem_border_bottom.'">&nbsp;'; 
						
				if($item->account_payment_type == 'account') 
						$html2_body.= @$approved_amount_display;
				$html2_body.= '</td>';
				$html2_body.='<td style="text-align:right;padding-right:5px;border:1px solid black;'.@$elem_border_bottom.'">';
						
				if($item->account_payment_type == 'payment') 
					$html2_body.= '-<span style="color:red;">'.@$paid_amount_vat_included_display.'</span>';
				    $html2_body.='</td>';
					$html2_body.='</tr>';
			}
		}
						
		$elem_total_approved_amount_vat_included_display = '';
		$elem_total_paid_amount_vat_included_display = '';
		$elem_total_paid_amount_vat_excluded_display = '';
		$elem_pending_payment_display = '';

		$elem_total_approved_amount_vat_included_display = '';
		$elem_total_paid_amount_vat_included_display = '';
		$elem_total_paid_amount_vat_excluded_display = '';
		$elem_pending_payment_display = '';
		$elem_pending_payment_vat_excluded_display = '';
		$elem_percent_paid_display = '';
		$elem_percent_paid_vat_excluded_display = '';
		$elem_percent_paid_from_orders_vat_excluded_display = '';

		if($elems_accounts_payments_num_rows > 0) {
			$elem_total_approved_amount_vat_included_display = isset($elem_total_approved_amount_vat_included)
			? ((floor($elem_total_approved_amount_vat_included) == $elem_total_approved_amount_vat_included)
				?number_format($elem_total_approved_amount_vat_included,0,'.',',').'&nbsp;&#x20aa;'
				:number_format($elem_total_approved_amount_vat_included,2,'.',',').'&nbsp;&#x20aa;')
			:'';
			
			$elem_total_paid_amount_vat_included_display = isset($elem_total_paid_amount_vat_included)
			? ((floor($elem_total_paid_amount_vat_included) == $elem_total_paid_amount_vat_included)
				?number_format($elem_total_paid_amount_vat_included,0,'.',',').'&nbsp;&#x20aa;'
				:number_format($elem_total_paid_amount_vat_included,2,'.',',').'&nbsp;&#x20aa;')
			:'';

			$elem_total_paid_amount_vat_excluded_display = isset($elem_total_paid_amount_vat_excluded)
			? ((floor($elem_total_paid_amount_vat_excluded) == $elem_total_paid_amount_vat_excluded)
				?number_format($elem_total_paid_amount_vat_excluded,0,'.',',').'&nbsp;&#x20aa;'
				:number_format($elem_total_paid_amount_vat_excluded,2,'.',',').'&nbsp;&#x20aa;')
			:'';

			$elem_pending_payment_display = '-';
				
			if($elem_total_approved_amount_vat_included-$elem_total_paid_amount_vat_included >= 0) {
				$elem_pending_payment = $elem_total_approved_amount_vat_included-$elem_total_paid_amount_vat_included;
				$elem_pending_payment_display = isset($elem_pending_payment)
				? ((floor($elem_pending_payment) == $elem_pending_payment)
					?number_format($elem_pending_payment,0,'.',',').'&#8362;'
					:number_format($elem_pending_payment,2,'.',',').'&#8362;')
				:'';
	
				$elem_pending_payment_vat_excluded = $elem_pending_payment/(1+(@$vat->vat/100));
				$elem_pending_payment_vat_excluded_display = isset($elem_pending_payment_vat_excluded)
				? ((floor($elem_pending_payment_vat_excluded) == $elem_pending_payment_vat_excluded)
					?number_format($elem_pending_payment_vat_excluded,0,'.',',').'&#8362;'
					:number_format($elem_pending_payment_vat_excluded,2,'.',',').'&#8362;')
				:'';
			}
				
			if($elem_total_sum_orders > 0){
				if(isset($elem_total_paid_amount_vat_excluded, $elem_total_sum_orders) && $elem_total_sum_orders != 0) {
					$percent = ($elem_total_paid_amount_vat_excluded / $elem_total_sum_orders) * 100;
					$elem_percent_paid_display = (floor($percent) == $percent)
						?number_format($percent,0,'.',',').'%'
						:number_format($percent,2,'.',',').'%';
				}

				if(isset($elem_pending_payment_vat_excluded, $elem_total_sum_orders) && $elem_total_sum_orders != 0){
					$percent = ($elem_pending_payment_vat_excluded / $elem_total_sum_orders)*100;
					$elem_percent_paid_from_orders_vat_excluded_display = (floor($percent) == $percent)
						?number_format($percent,0,'.',',').'%'
						:number_format($percent,2,'.',',').'%';
				}
			}
		}				
	
		$html2_body.='<tr style="background-color:#dcf1fa;font-size:12px;">';
		$html2_body.='<td colspan="4" style="'.$style_td_totals.'border:1px solid black;"><strong>'.getLang2($lang,'total').'</strong></td>';
		$html2_body.='<td style="'.@$text_align.';'.@$padding.':5px;border:1px solid black;"><strong>'.@$elem_total_approved_amount_vat_included_display.'</strong></td>';
		$html2_body.='<td dir="ltr" style="'.@$text_align.';'.@$padding.':5px;border:1px solid black;"><strong>-'.@$elem_total_paid_amount_vat_included_display.'</strong></td>';
		$html2_body.='</tr>';
		$html2_body.='<tr style="font-size:10px;text-align:center;"><td colspan="4" rowspan="2">'.@$table_ap_down_text.'</td><td style="background-color:#dcf1fa;font-size:12px;border:1px solid black;" colspan="2"><strong>'.getLang2($lang,'pending_payment').'&nbsp;'.@$elem_pending_payment_display.'</strong></td></tr>';
		$html2_body.='<tr style="font-size:10px;text-align:center;background-color:#f5f5dc;"><td colspan="2" style="border:1px solid black;"><strong>'.getLang2($lang,'total_to_be_paid_vat_excluded').'<br/>'.@$elem_pending_payment_vat_excluded_display.'&nbsp;('.@$elem_percent_paid_from_orders_vat_excluded_display.')</strong></td></tr>';
		$html2_body.='</table>';
		$html2_body.='</td></tr></table>';
			
		$html2_body.='<table style="margin-top:15px;font-weight:bold;">';
		$html2_body.='<tr><td colspan="3"></td></tr>';
		$html2_body.='<tr style="font-size:10px;">';
		$html2_body.='<td width="44%"></td>';
		$html2_body.='<td style="text-align:center;border:1px solid black;background-color:#f5f5dc;" width="25%">'.getLang2($lang,'paid_vat_excluded').'</td>';
		$html2_body.='<td style="text-align:center;border:1px solid black;background-color:#f5f5dc;" width="25%">'.@$elem_total_paid_amount_vat_excluded_display.'</td>';
		$html2_body.='</tr>';
		$html2_body.='<tr style="font-size:10px;">';
		$html2_body.='<td width="44%"></td>';
		$html2_body.='<td style="text-align:center;border:1px solid black;background-color:#f5f5dc;" width="25%">'.getLang2($lang,'percent_paid').'</td>';
		$html2_body.='<td style="text-align:center;border:1px solid black;background-color:#f5f5dc;" width="25%">'.@$elem_percent_paid_display.'</td>';
		$html2_body.='</tr>';
		$html2_body.='</table>';
			
		if($elems_not_approved_accounts_num_rows > 0) {
			$html2_body.= '<table dir="'.$dir_table.'"><tr><td height="10px">&nbsp;</td></tr><tr><td width="'.$margin_not_approved_accounts_list_label.'">&nbsp;</td><td><strong style="font-size:18px;">'.@$not_approved_accounts_list_label.'</strong></td></tr><tr><td colspan="4" height="20px">&nbsp;</td></tr></table>';
			$html2_body.= '<table dir="'.$dir_table.'" style="'.$style_table.'"><tr><td width="4%">&nbsp;</td><td><table cellpadding="4">';
			$html2_body.='<tr height="35px;" style="font-size:12px;font-weight:bold;background-color:silver;">';
			$html2_body.='<th width="30px;" style="text-align:center;border:1px solid black;">'.getLang2($lang,'supplier_number').'</th>';
			$html2_body.='<th width="200px;" style="text-align:center;border:1px solid black;">'.getLang2($lang,'description').'</th>';
			$html2_body.='<th width="100px;" style="text-align:center;border:1px solid black;">'.getLang2($lang,'submit_date').'</th>';
			$html2_body.='<th width="100px;" style="text-align:center;border:1px solid black;">'.getLang2($lang,'submitted_account').'</th>';
			$html2_body.='</tr>';
		        
			$elem_border_bottom = 'border-bottom:1px solid white';
			$count5 = 0;
				
			foreach($elems_not_approved_accounts as $item) { 
				$data_bg_color = '#ffffff';
					
				if($count5%2 == 0) 
					$data_bg_color = '#dedede';
				   
				if($count5 == $elems_not_approved_accounts_num_rows) 
			        $elem_border_bottom = 'border-bottom:1px solid black';
				   
				$submit_date = '';
				if(@$item->submit_date != '0000-00-00')
				  $submit_date = substr(@$item->submit_date,8,2).'/'.substr(@$item->submit_date,5,2).'/'.substr(@$item->submit_date,2,2);				
					
				$submitted_account_display = '';
				if(@$item->submitted_account > 0) {
					$submitted_account = @$item->submitted_account;	
					$submitted_account_display = isset($submitted_account)
					?number_format($submitted_account,0,'.',',').'&#8362;'
					:'';
				}
					
				$count5++;
					
				$html2_body.='<tr height="25px;" style="font-size:12px;background-color:'.@$data_bg_color.'">';	
				$html2_body.='<td style="'.@$text_align.';'.@$padding.':12px;border:1px solid black;'.@$elem_border_bottom.'">&nbsp;'.@$count5.'</td>';
				$html2_body.='<td style="'.@$text_align.';'.@$padding.':12px;border:1px solid black;'.@$elem_border_bottom.'">&nbsp;'.@$item->description.'</td>';
				$html2_body.='<td style="text-align:center;border:1px solid black;'.@$elem_border_bottom.'">&nbsp;'.@$submit_date.'</td>';
				$html2_body.='<td style="'.@$text_align.';'.@$padding.':12px;border:1px solid black;'.@$elem_border_bottom.'">&nbsp;'.@$submitted_account_display.'</td>';
				$html2_body.='</tr>';	
			}
		
			$html2_body.='</table></td></tr></table>';
		}
		
		$html2 = $html_header.$html2_body;
			
		if($lang == 'HE')
			$pdf->setRTL(true);

		$pdf->AddPage();
		$pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));
		$pdf->writeHTMLCell(0, 0, '', '', $html2, 0, 1, 0, true, '', true);
	}
}

ob_end_clean();
$pdf_name = substr(date('Y-m-d'),0,4).substr(date('Y-m-d'),5,2).substr(date('Y-m-d'),8,2).'-Budget Report-'.$project->nickname.'.pdf';
$pdf->Output($pdf_name,'I');
?>