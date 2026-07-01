<?php
require_once('tcpdf_min/config/tcpdf_config.php');
require_once('tcpdf_min/tcpdf.php');
include 'functions/functions.php';
error_reporting(E_ALL);
ini_set('display_errors', '1');

$ps_id = @$_GET['ps_id'];
$project_id = @$_GET['project_id'];
$lang = @$_GET['lang'];

$query = $mysqli->prepare("SELECT nickname FROM dne_projects WHERE id = ?");
$query->bind_param("i",$project_id);
$query->execute(); 
$query->store_result();
$project = fetch_unique($query);

$query = $mysqli->prepare("SELECT p.id AS p_id,p.nickname AS p_nickname,p.name AS p_name,p.name_he AS p_name_he,
                          sfow.name AS sfow_name,sfow.name_he AS sfow_name_he,
                          s.name AS s_name,s.name_he AS s_name_he
                          FROM dne_projects_suppliers ps
						  LEFT JOIN dne_projects p ON ps.id_project = p.id
						  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
						  LEFT JOIN dne_sup_field_of_work sfow ON s.id_field_of_work = sfow.id
						  WHERE ps.id = ?");
$query->bind_param("i",$ps_id);
$query->execute(); 
$query->store_result();
$supplier = fetch_unique($query);

$query = $mysqli->prepare("SELECT o.sum_order AS sum_order,o.pdf_order AS pdf_order,o.signature_date AS signature_date,
                          o.description AS description,o.vat AS vat
                          FROM dne_orders o 
						  LEFT JOIN dne_projects_suppliers ps ON o.id_projects_suppliers = ps.id
						  LEFT JOIN dne_projects p ON ps.id_project = p.id
						  WHERE ps.id = ? ORDER BY o.signature_date");
$query->bind_param("i",$ps_id);
$query->execute(); 
$query->store_result();
$orders_num_rows = $query->num_rows;
$orders = fetch($query);

$query = $mysqli->prepare("SELECT * FROM dne_payments2 WHERE id_projects_suppliers = ?");
$query->bind_param("i",$ps_id);
$query->execute(); 
$query->store_result();

$query = $mysqli->prepare("SELECT * FROM dne_payments2 WHERE id_projects_suppliers = ?");
$query->bind_param("i",$ps_id);
$query->execute(); 
$query->store_result();
$payments_num_rows = $query->num_rows;

$zero = 0.0;
$query = $mysqli->prepare("SELECT * FROM dne_accounts WHERE id_projects_suppliers = ? AND approved_amount > ?");
$query->bind_param("id",$ps_id,$zero);
$query->execute(); 
$query->store_result();
$accounts_num_rows = $query->num_rows;

$accounts_payments_num_rows = $payments_num_rows + $accounts_num_rows + 1;

$query = $mysqli->prepare("SELECT ap.id AS ap_id,p.id AS p_id,a.id AS a_id,
                          ap.account_payment_date AS account_payment_date,
                          ap.account_payment_type AS account_payment_type,p.description AS p_description,
						  a.description AS a_description,p.pdf_payment AS pdf_payment,
						  p.paid_amount AS paid_amount_vat_included,
						  p.paid_amount_vat_excluded AS paid_amount_vat_excluded,
						  a.approved_amount AS approved_amount
						  FROM dne_accounts_payments ap
						  LEFT JOIN dne_accounts a ON ap.id_account = a.id
						  LEFT JOIN dne_payments2 p ON ap.id_payment = p.id		 
						  LEFT JOIN dne_projects_suppliers ps ON ap.id_projects_suppliers = ps.id 
						  WHERE ps.id = ?
						  ORDER BY ap.account_payment_date");
$query->bind_param("i",$ps_id);
$query->execute(); 
$query->store_result();
$accounts_payments = fetch($query);

$zero = 0.0;
$query = $mysqli->prepare("SELECT description,submit_date,submitted_account,pdf_submission 
                          FROM dne_accounts WHERE id_projects_suppliers = ? AND approved_amount = ?
						  ORDER BY submit_date");
$query->bind_param("id",$ps_id,$zero);
$query->execute(); 
$query->store_result();
$not_approved_accounts_num_rows = $query->num_rows;
$not_approved_accounts = fetch($query);

$query = $mysqli->prepare("SELECT vat FROM dne_vat");
$query->execute(); 
$query->store_result();
$vat = fetch_unique($query);

$dir_table = 'ltr';
$style_td_totals = "text-align:right;padding-right:12px;";
$text_align = 'text-align:left';
$padding = 'padding-left';
$border = 'border-left';

if($lang == 'EN'){
   $title = '<table style="font-weight:bold;"><tr><td><u>Project '.$supplier->p_name.'</u></td></tr><tr><td><u>Status of orders and payments</u></td></tr><tr style="font-size:18px;"><td>'.$supplier->s_name.'</td></tr><tr><td>'.@$supplier->sfow_name.'</td></tr><tr><td>'.substr(date('Y-m-d'),8,2).'/'.substr(date('Y-m-d'),5,2).'/'.substr(date('Y-m-d'),0,4).'</td></tr></table>';
   $margin_orders_list_label = '4%';
   $orders_list_label = 'Orders';
   $table_orders_down_text = "This table contains only the original orders without the additions/cancellations/changes and does not constitute an estimate of the actual total of the final orders.";
   $margin_accounts_payments_list_label = '4%';
   $accounts_payments_list_label = 'Accounts / Payments';
   $margin_not_approved_accounts_list_label = '4%';
   $table_ap_down_text = "(1) Approval of an account does not constitute confirmation of the completion/quality of the supply or the work.";
   $not_approved_accounts_list_label = 'Submitted accounts and not yet approved';      
}
else if($lang == 'HE'){
    $title = '<table style="font-weight:bold;"><tr><td><u>פרוייקט '.$supplier->p_name_he."</u></td></tr><tr><td><u>סטטוס חשבונות ותשלומים</u></td></tr>".'<tr style="font-size:18px;"><td>'.$supplier->s_name_he.'</td></tr><tr><td>'.@$supplier->sfow_name_he.'</td></tr><tr><td>'.substr(date('Y-m-d'),8,2).'/'.substr(date('Y-m-d'),5,2).'/'.substr(date('Y-m-d'),0,4).'</td></tr></table>';
	$dir_table = 'rtl';
	$style_td_totals = "text-align:left;padding-left:12px;";
	$margin_orders_list_label = '4%';
	$orders_list_label = 'הזמנות';
    $table_orders_down_text = "טבלה זו מכילה רק את ההזמנות המקוריות ללא התוספות / ביטולים / שינויים ולכן לא מהווה אומדן של סך ההזמנות הסופית בפועל.";
	$margin_accounts_payments_list_label = '4%';
	$accounts_payments_list_label = 'חשבונות / תשלומים שבוצעו';
	$margin_not_approved_accounts_list_label = '4%';
    $table_ap_down_text = "אישור מנהל הפרויקט על חשבון שמוגש אינו מהווה אישור על השלמה/טיב האספקה והעבודה וכן אינו בהכרח תואם את ההזמנה באשר לפריסה ותנאי התשלומים.תשלום בפועל על ידי המזמין מהווה הסכמה לנ''ל."; 
	$not_approved_accounts_list_label = 'חשבונות שהוגשו וטרם אושרו';  
	$text_align = 'text-align:right';
	$padding = 'padding-right';
	$border = 'border-right';
}

$border_bottom = 'border-bottom:1px solid white';

class PDF extends TCPDF {
    public function Footer() { 
		$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		
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
		
		$query = "SELECT s.nickname AS supplier_nickname
			     FROM dne_projects_suppliers ps
				 LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
				 WHERE ps.id=".@$_GET['ps_id'];
		$result = $mysqli->query($query);
		if ($result->num_rows > 0) {
			$row = $result->fetch_assoc();
			$supplier_nickname = $row['supplier_nickname'];
		} else {
			$supplier_nickname = 'No Data Found';
		}
		
	    $pdf_file_name = substr(date('Y-m-d'),0,4).substr(date('Y-m-d'),5,2).substr(date('Y-m-d'),8,2).'-Supplier Budget Report-'.$supplier_nickname.'-'.$project_nickname.'.pdf';
	
		$this->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);  
        $this->SetFont('freesans', '', 10);
        $this->SetY(-25);
		$this->Ln();
        $this->Cell(0, 0, '', 'T');

        $this->SetX($this->lMargin);

        $pagePagination = 'Page ' . $this->getAliasNumPage() . ' of ' . $this->getAliasNbPages();
	
	    if(@$_GET['lang'] == 'HE') {
		    $this->setPrintFooter(true);
			$this->Cell(40, 10, $project_name, 0, 0, 'R');
            $this->Cell(70, 10, $pagePagination, 0, 0, 'C');
            $this->Cell(88, 10, $pdf_file_name, 0, 0, 'L');
		}
		else {
			$this->setPrintFooter(false);
			$this->Cell(70, 10, $project_name, 0, 0, 'L');
            $this->Cell(70, 10, $pagePagination, 0, 0, 'C');
            $this->Cell(58, 10, $pdf_file_name, 0, 0, 'R');
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

$html = '<table width="90%"><tr><td style="text-align:center;"><img src="images/davidnahmias_stripe.png" /><br/></td></tr>';
$html.= '<tr><td style="text-align:center;"><span dir="'.$dir_table.'">'.$title.'</span></td></tr></table>';
$html.= '<table dir="'.$dir_table.'"><tr><td colspan="2">&nbsp;</td></tr><tr><td width="'.$margin_orders_list_label.'">&nbsp;</td><td><strong style="font-size:18px;">'.@$orders_list_label.'</strong></td></tr></table>';
$html.= '<table dir="'.$dir_table.'"><tr><td width="4%">&nbsp;</td><td><table cellpadding="4">';
$html.='<tr style="font-size:0.5px;"><td colspan="4"></td></tr>';
$html.='<tr height="35px;" style="font-size:12px;font-weight:bold;background-color:silver;">';
$html.='<th width="30px;" style="text-align:center;border:1px solid black;">'.getLang2($lang,'supplier_number').'</th>';
$html.='<th width="70px;" style="text-align:center;border:1px solid black;">'.getLang2($lang,'signature_date').'</th>';
$html.='<th width="320px;" style="text-align:center;border:1px solid black;">'.getLang2($lang,'description').'</th>';
$html.='<th width="100px;" style="text-align:center;border:1px solid black;">'.getLang2($lang,'total_orders_h').'</th>';
$html.='</tr>';

$total_sum_orders = 0;
$total_sum_orders_vat_included = 0;
		
$count = 0;		
foreach($orders as $item){	
    $data_bg_color = '#ffffff';
	if($count%2 == 0) 
	   $data_bg_color = '#dedede';
   
    if($count == $orders_num_rows)
	   $border_bottom = 'border:1px solid black';

	$signature_date = smartDate(@$item->signature_date, $lang);
	
	$sum_orders_display = '';
	if(@$item->sum_order != 0.00) {
		$sum_orders = @$item->sum_order;
		$sum_orders_display = '';
		if(isset($sum_orders)){
			$sum_orders_display = (floor($sum_orders) == $sum_orders)
				?number_format($sum_orders,0,'.',',').'&nbsp;&#x20aa;'
				:number_format($sum_orders,2,'.',',').'&nbsp;&#x20aa;';
		}
		$sum_order_vat_included = @$item->sum_order*(1+(@$item->vat/100)); 
	}

	$total_sum_orders +=$item->sum_order;
	$total_sum_orders_vat_included += $sum_order_vat_included;
	$count++;
	
	$html.='<tr height="25px;" style="font-size:12px;background-color:'.@$data_bg_color.'">';
	$html.='<td style="text-align:center;border:1px solid black;'.@$border_bottom.'">&nbsp;'.@$count.'</td>';
	$html.='<td style="'.@$text_align.';'.@$padding.':12px;border:1px solid black;'.@$border_bottom.'">&nbsp;'.@$signature_date.'</td>';
	$html.='<td style="'.@$text_align.';'.@$padding.'12px;border:1px solid black;'.@$border_bottom.'">&nbsp;'.@$item->description.'</td>';
	$html.='<td style="direction:ltr;'.@$text_align.';'.@$padding.':5px;border:1px solid black;'.@$border_bottom.'">&nbsp;'.@$sum_orders_display.'</td>';
	$html.='</tr>';
}

$total_sum_orders_display = '';
$total_sum_orders_vat_included_display = '';
if($orders_num_rows > 0){ 
	if(isset($total_sum_orders)){
		$total_sum_orders_display = (floor($total_sum_orders) == $total_sum_orders)
			?number_format($total_sum_orders, 0,'.',',').'&nbsp;&#8362;'
			:number_format($total_sum_orders, 2,'.',',').'&nbsp;&#8362;';
    }
	
	if(isset($total_sum_orders_vat_included)){
    $total_sum_orders_vat_included_display = (floor($total_sum_orders_vat_included) == $total_sum_orders_vat_included)
        ?number_format($total_sum_orders_vat_included,0,'.',',').'&#8362;'
        :number_format($total_sum_orders_vat_included,2,'.',',').'&#8362;';
    }
}
else {
	$total_sum_orders = '';	
	$total_sum_orders_vat_included_display = '';
}

$html.='<tr style="background-color:#dcf1fa;font-size:12px;">';
$html.='<td colspan="3" style="'.$style_td_totals.'border:1px solid black;"><strong>'.getLang2($lang,'accounts_payments_total_orders').'</strong></td>';
$html.='<td style="direction:ltr;border:1px solid black;"><strong>'.@$total_sum_orders_display.'</strong></td>';
$html.='</tr>';
$html.='<tr style="font-size:10px;"><td colspan="3">'.@$table_orders_down_text.'</td></tr>';
$html.='</table></td></tr></table>';
$html.='<table dir="'.$dir_table.'"><tr><td colspan="2" height="5px">&nbsp;</td></tr><tr><td width="'.$margin_accounts_payments_list_label.'">&nbsp;</td><td><strong style="font-size:18px;">'.@$accounts_payments_list_label.'</strong></td></tr></table>';
$html.= '<table dir="'.$dir_table.'"><tr><td width="4%"></td><td><table cellpadding="4">';
$html.='<tr style="font-size:0.5px;"><td colspan="6"></td></tr>';
$html.='<tr height="35px;" style="font-size:12px;background-color:silver;">';
$html.='<th width="30px;" style="text-align:center;border:1px solid black;"><strong>'.getLang2($lang,'supplier_number').'</strong></th>';
$html.='<th width="80px;" style="text-align:center;border:1px solid black;"><strong>'.getLang2($lang,'date').'</strong></th>';
$html.='<th width="80px;" style="text-align:center;border:1px solid black;"><strong>'.getLang2($lang,'account_payment').'</strong></th>';
$html.='<th width="280px;" style="text-align:center;border:1px solid black;"><strong>'.getLang2($lang,'description').'</strong></th>';
$html.='<th width="100px;" style="text-align:center;border:1px solid black;">'.getLang2($lang,'account_including_vat').'</th>';
$html.='<th width="100px;" style="text-align:center;border:1px solid black;">'.getLang2($lang,'payment_including_vat').'</th>';
$html.='</tr>';	

$total_approved_amount_vat_included = 0;
$total_paid_amount_vat_included = 0;
$total_paid_amount_vat_excluded = 0;

$count = 0;	
foreach($accounts_payments as $item) {
	$data_bg_color = ($count%2 == 0) ? '#dedede' : '#ffffff';
	
    if($count == $accounts_payments_num_rows)
	   $border_bottom = 'border:1px solid black';
   
	if(@$item->account_payment_type == 'payment' || @$item->account_payment_type == 'account' && @$item->approved_amount > 0) {							
		if(@$item->account_payment_type == 'account') {		
		   $account_payment_type = strtolower(getLang2($lang,'account'));
		   $description = $item->a_description;
		}
		else {
		   $account_payment_type = strtolower(getLang2($lang,'payment'));
		   $description = $item->p_description;
		}
		
		$account_payment_date = smartDate(@$item->account_payment_date, $lang);
		
		if(@$item->account_payment_type == 'account') {
		   $approved_amount = @$item->approved_amount;	
			if(isset($approved_amount)){
				$approved_amount_display = (floor($approved_amount) == $approved_amount)
					? number_format($approved_amount, 0,'.',',').'&nbsp;&#x20aa;'
					: number_format($approved_amount, 2,'.',',').'&nbsp;&#x20aa;';
			}
		}
		
		$paid_amount_vat_included_display = '';
		if(@$item->paid_amount_vat_included != 0.00) {
			$paid_amount_vat_included = @$item->paid_amount_vat_included;
			if(isset($paid_amount_vat_included)){
				$paid_amount_vat_included_display = (floor($paid_amount_vat_included) == $paid_amount_vat_included)
					?number_format($paid_amount_vat_included,0,'.',',').'&nbsp;&#x20aa;'
					:number_format($paid_amount_vat_included,2,'.',',').'&nbsp;&#x20aa;';
			}
		}

		$total_approved_amount_vat_included +=$item->approved_amount;
		$total_paid_amount_vat_included +=$item->paid_amount_vat_included;
		$total_paid_amount_vat_excluded +=$item->paid_amount_vat_excluded;
		
		$count++;
			
		$html.='<tr height="25px;" style="font-size:12px;background-color:'.@$data_bg_color.'">';	
		$html.='<td style="text-align:center;border:1px solid black;'.@$border_bottom.'">&nbsp;'.@$count.'</td>';
		$html.='<td style="'.@$text_align.';'.@$padding.':12px;border:1px solid black;'.@$border_bottom.'">&nbsp;'.@$account_payment_date.'</td>';
		$html.='<td style="text-align:center;border:1px solid black;'.@$border_bottom.'">&nbsp;'.@$account_payment_type.'</td>';
		$html.='<td style="'.@$text_align.';'.@$padding.':12px;border:1px solid black;'.@$border_bottom.'">&nbsp;'.@$description.'</td>';
		$html.='<td style="direction:ltr;'.@$text_align.';'.@$padding.':5px;border:1px solid black;'.@$border_bottom.'">&nbsp;';
		if($item->account_payment_type == 'account')
			$html.= @$approved_amount_display;
		$html.= '</td>';
		$html.='<td dir="ltr" style="'.@$text_align.';'.@$padding.':5px;border:1px solid black;'.@$border_bottom.'">';
		if($item->account_payment_type == 'payment') 
		   $html.=  '<span dir="ltr" style="color:red;">-'.@$paid_amount_vat_included_display.'</span>';
		$html.='</td>';
		$html.='</tr>';
}}
				
$total_approved_amount_vat_included_display = '';
$total_paid_amount_vat_included_display = '';
$total_paid_amount_vat_excluded_display = '';	
$pending_payment_vat_excluded_display = '';
$percent_paid_display = '';
$percent_paid_vat_excluded_display = '';
$percent_paid_from_orders_vat_excluded_display = '';
$percent_to_pay_from_orders_vat_included_display = '';
$remaining_to_pay_vat_included_display = '';

if($accounts_payments_num_rows > 0){
    if(isset($total_approved_amount_vat_included)){
        $total_approved_amount_vat_included_display = (floor($total_approved_amount_vat_included) == $total_approved_amount_vat_included)
        ?number_format($total_approved_amount_vat_included,0,'.',',').'&nbsp;&#x20aa;'
        :number_format($total_approved_amount_vat_included,2,'.',',').'&nbsp;&#x20aa;';
    }

	if(isset($total_paid_amount_vat_included)){
		$total_paid_amount_vat_included_display = (floor($total_paid_amount_vat_included) == $total_paid_amount_vat_included)
			?number_format($total_paid_amount_vat_included,0,'.',',').'&nbsp;&#x20aa;'
			:number_format($total_paid_amount_vat_included,2,'.',',').'&nbsp;&#x20aa;';
	}

	if(isset($total_paid_amount_vat_excluded)){
		$total_paid_amount_vat_excluded_display = (floor($total_paid_amount_vat_excluded) == $total_paid_amount_vat_excluded)
			?number_format($total_paid_amount_vat_excluded,0,'.',',').'&nbsp;&#x20aa;'
			:number_format($total_paid_amount_vat_excluded,2,'.',',').'&nbsp;&#x20aa;';
	}

    $pending_payment_display = '-';
	
	if($total_approved_amount_vat_included-$total_paid_amount_vat_included >= 0) {
		$pending_payment = $total_approved_amount_vat_included-$total_paid_amount_vat_included;
		if(isset($pending_payment)){
			$pending_payment_display = (floor($pending_payment) == $pending_payment)
				?number_format($pending_payment,0,'.',',').'&#8362;'
				:number_format($pending_payment,2,'.',',').'&#8362;';
		}

		$pending_payment_vat_excluded = $pending_payment/(1+(@$vat->vat/100));
		if(isset($pending_payment_vat_excluded)){
			$pending_payment_vat_excluded_display = (floor($pending_payment_vat_excluded) == $pending_payment_vat_excluded)
				?number_format($pending_payment_vat_excluded, 0,'.',',').'&#8362;'
				:number_format($pending_payment_vat_excluded, 2,'.',',').'&#8362;';
		}
	}
	
	if($total_approved_amount_vat_included-$total_paid_amount_vat_included < 0) {
		$pending_payment_display = '0&#8362;';
		$pending_payment = 0;
	}

	// E = C_HT / A
	$percent_paid = 0;
	$percent_paid_display = '';
	if ($total_sum_orders > 0) {
		$percent_paid = ($total_paid_amount_vat_excluded / $total_sum_orders) * 100;
		$percent_paid_display = (floor($percent_paid) == $percent_paid)
			?number_format($percent_paid,0,'.',',').'%'
			:number_format($percent_paid,2,'.',',').'%';
	}

	// F = 100 - E
	$percent_to_pay = ($total_sum_orders > 0) ? (100 - $percent_paid) : 0;
	$percent_to_pay_from_orders_vat_included_display = ($total_sum_orders > 0)
		? ((floor($percent_to_pay) == $percent_to_pay)
			?number_format($percent_to_pay,0,'.',',').'%'
			:number_format($percent_to_pay,2,'.',',').'%')
		: '';

	// שאר לשלם montant = F/100 × A × (1+TVA)
	$remaining_to_pay_vat_included = ($total_sum_orders > 0)
		? ($percent_to_pay / 100) * $total_sum_orders * (1 + (@$vat->vat/100))
		: 0;
	$remaining_to_pay_vat_included_display = (floor($remaining_to_pay_vat_included) == $remaining_to_pay_vat_included)
		?number_format($remaining_to_pay_vat_included,0,'.',',').'&#8362;'
		:number_format($remaining_to_pay_vat_included,2,'.',',').'&#8362;';

	// % לתשלום = B_HT / A
	$percent_pending_display = '';
	if ($total_sum_orders > 0 && isset($pending_payment_vat_excluded)) {
		$pct_p = ($pending_payment_vat_excluded / $total_sum_orders) * 100;
		$percent_pending_display = (floor($pct_p) == $pct_p)
			?number_format($pct_p,0,'.',',').'%'
			:number_format($pct_p,2,'.',',').'%';
	}

	if(@$lang == 'HE')
		$remaining_to_pay_text = "נשאר לשלם (כולל מע''מ) ".@$remaining_to_pay_vat_included_display.' ('.@$percent_to_pay_from_orders_vat_included_display.')';
    else
        $remaining_to_pay_text = "It's remaining to pay(VAT included) ".@$remaining_to_pay_vat_included_display.' ('.@$percent_to_pay_from_orders_vat_included_display.')';		
}				
	
$html.='<tr style="background-color:#dcf1fa;font-size:12px;">';
$html.='<td colspan="4" style="'.$style_td_totals.'border:1px solid black;"><strong>'.($lang=='HE' ? "סה''כ כולל מע''מ" : 'Total VAT included').'</strong></td>';
$html.='<td style="direction:ltr;'.@$text_align.';'.@$padding.':5px;border:1px solid black;"><strong>'.@$total_approved_amount_vat_included_display.'</strong></td>';
$html.='<td dir="ltr" style="'.@$text_align.';'.@$padding.':5px;border:1px solid black;"><strong>-'.@$total_paid_amount_vat_included_display.'</strong></td>';
$html.='</tr>';
$html.='<tr style="font-size:10px;text-align:center;"><td colspan="4">'.@$table_ap_down_text.'</td><td style="border:1px solid black;background-color:silver;direction:ltr;text-align:center;" colspan="2"><div style="font-size:4px;">&nbsp;</div><strong style="font-size:12px;">'.getLang2($lang,'pending_payment').'&nbsp;'.@$pending_payment_display.'</strong></td></tr>';
$html.='</table>';
$html.='</td></tr></table>';

$vat_incl_header    = ($lang == 'HE') ? "כולל מע''מ"    : 'VAT included';
$to_pay_label       = ($lang == 'HE') ? 'לתשלום'        : 'To be paid';
$paid_label         = ($lang == 'HE') ? 'שולם'          : 'Paid';
$remaining_label    = ($lang == 'HE') ? 'שאר לשלם'      : 'Remaining to pay';

$html.='<table><tr><td height="10">&nbsp;</td></tr></table>';
$html.='<table style="font-weight:bold;" cellpadding="5">';
$html.='<tr style="font-size:10px;">';
$html.='<td width="50%"></td>';
$html.='<td colspan="3" style="text-align:center;border:1px solid black;background-color:#dcf1fa;" width="50%"><strong>'.$vat_incl_header.'</strong></td>';
$html.='</tr>';
$html.='<tr style="font-size:10px;">';
$html.='<td width="50%"></td>';
$html.='<td style="text-align:center;border:1px solid black;background-color:#dcf1fa;" width="17%"><strong>'.$to_pay_label.'</strong></td>';
$html.='<td style="direction:ltr;text-align:center;border:1px solid black;background-color:#dcf1fa;" width="17%"><strong>'.@$pending_payment_display.'</strong></td>';
$html.='<td style="text-align:center;border:1px solid black;background-color:#dcf1fa;" width="16%"><strong>'.@$percent_pending_display.'</strong></td>';
$html.='</tr>';
$html.='<tr style="font-size:10px;">';
$html.='<td width="50%"></td>';
$html.='<td style="text-align:center;border:1px solid black;background-color:#dcf1fa;" width="17%"><strong>'.$paid_label.'</strong></td>';
$html.='<td style="direction:ltr;text-align:center;border:1px solid black;background-color:#dcf1fa;" width="17%"><strong>'.@$total_paid_amount_vat_included_display.'</strong></td>';
$html.='<td style="text-align:center;border:1px solid black;background-color:#dcf1fa;" width="16%"><strong>'.@$percent_paid_display.'</strong></td>';
$html.='</tr>';
$html.='<tr style="font-size:10px;">';
$html.='<td width="50%"></td>';
$html.='<td style="text-align:center;border:1px solid black;background-color:#dcf1fa;" width="17%"><strong>'.$remaining_label.'</strong></td>';
$html.='<td style="direction:ltr;text-align:center;border:1px solid black;background-color:#dcf1fa;" width="17%"><strong>'.@$remaining_to_pay_vat_included_display.'</strong></td>';
$html.='<td style="text-align:center;border:1px solid black;background-color:#dcf1fa;" width="16%"><strong>'.@$percent_to_pay_from_orders_vat_included_display.'</strong></td>';
$html.='</tr>';
$html.='</table>';

if($not_approved_accounts_num_rows > 0){
    $html.= '<table dir="'.$dir_table.'"><tr><td height="5px">&nbsp;</td></tr><tr><td width="'.$margin_not_approved_accounts_list_label.'">&nbsp;</td><td><strong style="font-size:18px;">'.@$not_approved_accounts_list_label.'</strong></td></tr></table>';
	$html.= '<table dir="'.$dir_table.'" style="margin-top:10px"><tr><td width="4%">&nbsp;</td><td><table cellpadding="4">';
	$html.='<tr style="font-size:0.5px;"><td colspan="4"></td></tr>';
	$html.='<tr height="35px;" style="font-size:12px;background-color:silver;">';
	$html.='<th width="30px;" style="text-align:center;border:1px solid black;"><strong>'.getLang2($lang,'supplier_number').'</strong></th>';
	$html.='<th width="320px;" style="text-align:center;border:1px solid black;"><strong>'.getLang2($lang,'description').'</strong></th>';
	$html.='<th width="100px;" style="text-align:center;border:1px solid black;"><strong>'.getLang2($lang,'submit_date').'</strong></th>';
	$html.='<th width="100px;" style="text-align:center;border:1px solid black;">'.getLang2($lang,'submitted_account_vat_included').'</th>';
	$html.='</tr>';
	
	$count = 0;
	foreach($not_approved_accounts as $item){ 
	    $data_bg_color = '#ffffff';
	    if($count%2 == 0) 
	       $data_bg_color = '#dedede';
	   
	    if($count == $not_approved_accounts_num_rows)
	       $border_bottom = 'border:1px solid black';
   
		$submit_date = smartDate(@$item->submit_date, $lang);
		
		$submitted_account_display = '';
		if(@$item->submitted_account > 0) {
			$submitted_account = @$item->submitted_account;	
			$submitted_account_display = isset($submitted_account) 
			?((floor($submitted_account) == $submitted_account)
				?number_format($submitted_account,0, '.',',').'&#8362;'
				:number_format($submitted_account,2,'.',',').'&#8362;')
			:'';
		}
		
		$count++;
		
		$html.='<tr height="25px;" style="font-size:12px;background-color:'.@$data_bg_color.'">';	
		$html.='<td style="text-align:center;border:1px solid black;'.@$border_bottom.'">&nbsp;'.@$count.'</td>';
		$html.='<td style="'.@$text_align.';'.@$padding.':12px;border:1px solid black;'.@$border_bottom.'">&nbsp;'.@$item->description.'</td>';
		$html.='<td style="text-align:center;border:1px solid black;'.@$border_bottom.'">&nbsp;'.@$submit_date.'</td>';
		$html.='<td style="'.@$text_align.';'.@$padding.':12px;border:1px solid black;'.@$border_bottom.'">&nbsp;'.@$submitted_account_display.'</td>';
	    $html.='</tr>';	
	}
	
	$html.='</table></td></tr></table>';
}

if($lang == "HE") 
	$pdf->setRTL(true);

$pdf->AddPage();
$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
ob_end_clean();
$pdf_name = substr(date('Y-m-d'),0,4).substr(date('Y-m-d'),5,2).substr(date('Y-m-d'),8,2).'-Supplier Budget Report-'.$supplier->s_name.'-'.$project->nickname.'.pdf';
$pdf->Output($pdf_name,'I');
?>