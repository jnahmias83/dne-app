<?php
session_start();
require_once('tcpdf_min/config/tcpdf_config.php');
require_once('tcpdf_min/tcpdf.php');
include 'functions/functions.php';
error_reporting(E_ALL);
ini_set('display_errors', '1'); 

$query = $mysqli->prepare("SELECT logo_stread FROM dne_logos");
$query->execute();
$query->store_result();
$logo = fetch_unique($query);

$query = $mysqli->prepare("SELECT * FROM dne_users");
$query->execute(); 
$query->store_result();
$users = fetch($query);

class PDF extends TCPDF {
    public function Footer() { 
		$pdf_file_name = substr(date('Y-m-d'),0,4).substr(date('Y-m-d'),5,2).substr(date('Y-m-d'),8,2).'-Users List.pdf';
	
		$this->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);  
        $this->SetFont('freesans', '', 10);
        $this->SetY(-25);
		$this->Ln();
        $this->Cell(0, 0, '', 'T');

        $this->SetX($this->lMargin);

        $pagePagination = 'Page ' . $this->getAliasNumPage() . ' of ' . $this->getAliasNbPages();
	
		$this->setPrintFooter(true); 
        $this->Cell(260, 10, $pagePagination, 0, 0, 'C');
        $this->Cell(25, 10, $pdf_file_name, 0, 0, 'L');
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
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->setFontSubsetting(true);
$pdf->SetFont('freesans', '', 12);
$pdf->setPrintHeader(false);

$html = '<table width="100%" style="margin-top:15px;"><tr><td style="text-align:center;"><img src="uploads/'.@$logo->logo_stread.'" /><br/><br/></td></tr>';
$html.= '<tr><td width="15px;">&nbsp;</td><td style="text-align:center;padding-top:30px;"><span dir="rtl"><strong><u>רשימת משתמשים<br/>'.substr(date('Y-m-d'),8,2).'/'.substr(date('Y-m-d'),5,2).'/'.substr(date('Y-m-d'),0,4).'</u></strong></span></td></tr></table>';
$html.= '<div class="row">';
$html.= '<div class="col-md-12">';
$html.= '<table dir="rtl" style="margin-top:25px;"><tr><td width="1%">&nbsp;</td><td><table cellpadding="4">';
$html.='<tr height="30px" style="font-size:12px;font-weight:bold;background-color:silver;">';
$html.='<th width="30px;" style="text-align:center;border:1px solid black;">#</th>';
$html.='<th width="80px;" style="text-align:center;border:1px solid black;">שם משפחה</th>';
$html.='<th width="80px;" style="text-align:center;border:1px solid black;">שם פרטי</th>';
$html.='<th width="80px;" style="text-align:center;border:1px solid black;">כינוי</th>';
$html.='<th width="180px;" style="text-align:center;border:1px solid black;">'."דוא''ל".'</th>';
$html.='<th width="180px;" style="text-align:center;border:1px solid black;">שם משתמש</th>';
$html.='<th width="100px;" style="text-align:center;border:1px solid black;">סיסמה</th>';
$html.='<th width="70px;" style="text-align:center;border:1px solid black;">תפקיד</th>';
$html.='<th width="150px;" style="text-align:center;border:1px solid black;">הרשאות</th>';
$html.='</tr>';

$count = 0;

foreach($users as $item) { 
    $count++;
	
	$submitted_account = '';
	if(@$item->submitted_account != 0.00)
	   $submitted_account = number_format(@$item->submitted_account,2,'.',',').'&nbsp;&#8362;';
	
    $html.='<tr height="30px;" style="font-size:12px;">';
	$html.='<td width="30px;" style="text-align:center;border:1px solid black;">'.$count.'</td>';
	$html.='<td width="80px;" style="text-align:right;padding-right:12px;border:1px solid black;">'.@$item->lastname.'</td>';	
	$html.='<td width="80px;" style="text-align:right;padding-right:12px;border:1px solid black;">'.@$item->firstname.'</td>';
	$html.='<td width="80px;" style="text-align:right;padding-right:12px;border:1px solid black;">'.@$item->nickname.'</td>';
	$html.='<td width="180px;" style="text-align:right;padding-right:12px;border:1px solid black;">'.@$item->email.'</td>';
	$html.='<td width="180px;" style="text-align:right;padding-right:12px;border:1px solid black;">'.@$item->username.'</td>';
	$html.='<td width="100px;" style="text-align:right;padding-right:12px;border:1px solid black;">'.@$item->password.'</td>';
	$html.='<td width="70px;" style="text-align:right;padding-right:12px;border:1px solid black;">'.@$item->role.'</td>';
	$html.='<td width="150px;" style="text-align:right;padding-right:12px;border:1px solid black;">'.@$item->privileges.'</td>';
    $html.='</tr>';
}

$html.='</table></td></tr></table></div></div>';

$pdf->setRTL(true);
$pdf->AddPage();

$pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));
$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
ob_end_clean();
$pdf_name = substr(date('Y-m-d'),0,4).substr(date('Y-m-d'),5,2).substr(date('Y-m-d'),8,2).'-Not-Approved-Accounts-List.pdf';
$pdf->Output($pdf_name,'I');
?>