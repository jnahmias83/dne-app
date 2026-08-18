<?php
session_start();
require_once('tcpdf_min/config/tcpdf_config.php');
require_once('tcpdf_min/tcpdf.php');
include 'functions/functions.php';
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

$query = $mysqli->prepare("SELECT logo_stread FROM dne_logos");
$query->execute();
$query->store_result();
$logo = fetch_unique($query);

$zero = 0.0;
$query = $mysqli->prepare("SELECT p.nickname AS p_nickame,s.name_he AS s_name_he,a.id AS account_id,
                          a.submitted_account AS submitted_account
                          FROM dne_accounts a
						  JOIN dne_projects_suppliers ps ON a.id_projects_suppliers = ps.id
						  JOIN dne_projects p ON ps.id_project = p.id
						  JOIN dne_suppliers s ON ps.id_supplier = s.id
						  WHERE a.submitted_account > ? AND a.approved_amount = ?");
$query->bind_param("dd",$zero,$zero);
$query->execute(); 
$query->store_result();
$not_approved_accounts = fetch($query);

$html = '<table width="100%" style="margin-top:15px;"><tr><td style="text-align:center;"><img src="uploads/'.@$logo->logo_stread.'" /><br/><br/></td></tr>';
$html.= '<tr><td width="15px;">&nbsp;</td><td style="text-align:center;padding-top:30px;"><span dir="rtl"><strong><u>רשימת החשבונות הלא מאושרים<br/>'.substr(date('Y-m-d'),8,2).'/'.substr(date('Y-m-d'),5,2).'/'.substr(date('Y-m-d'),0,4).'</u></strong></span></td></tr></table>';
$html.= '<div class="row">';
$html.= '<div class="col-md-12">';
$html.= '<table dir="rtl" style="margin-top:25px;"><tr><td width="28%">&nbsp;</td><td><table cellpadding="4">';
$html.='<tr height="30px" style="font-size:12px;font-weight:bold;background-color:silver;">';
$html.='<th width="30px;" style="text-align:center;border:1px solid black;">#</th>';
$html.='<th width="100px;" style="text-align:center;border:1px solid black;">שם הפרוייקט</th>';
$html.='<th width="160px;" style="text-align:center;border:1px solid black;">שם הספק</th>';
$html.='<th width="160px;" style="text-align:center;border:1px solid black;">חשבון שהוגש</th>';
$html.='</tr>';

$count = 0;

foreach($not_approved_accounts as $item) { 
    $count++;
	
	$submitted_account = '';
	if(@$item->submitted_account != 0.00)
	   $submitted_account = number_format(@$item->submitted_account,2,'.',',').'&nbsp;&#8362;';
	
    $html.='<tr height="30px;" style="font-size:12px;">';
	$html.='<td width="30px;" style="text-align:center;border:1px solid black;">'.$count.'</td>';
	$html.='<td width="100px;" style="text-align:right;padding-right:12px;border:1px solid black;">'.@$item->p_nickame.'</td>';
	$html.='<td width="160px;" style="text-align:right;padding-right:12px;border:1px solid black;">&nbsp;'.@$item->s_name_he.'</td>';	
    $html.='<td width="160px;" style="direction:ltr;text-align:right;padding-right:12px;border:1px solid black;">'.$submitted_account.'</td>';
    $html.='</tr>';
}

$html.='</table></td></tr></table></div></div>';

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
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
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->setFontSubsetting(true);
$pdf->SetAlpha(1);
$pdf->SetTextShadow(['enabled' => false]);
$pdf->SetFont('dejavusans','',12,'',true);
$pdf->SetTextColor(0,0,0);
$pdf->setPrintHeader(false);
$pdf->setRTL(true);
$pdf->AddPage();
$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
ob_end_clean();
$pdf_name = substr(date('Y-m-d'),0,4).substr(date('Y-m-d'),5,2).substr(date('Y-m-d'),8,2).'-Not-Approved-Accounts-List.pdf';
$pdf->Output($pdf_name,'I');
?>