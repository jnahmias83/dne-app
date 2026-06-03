<?php
session_start();
require_once('tcpdf_min/config/tcpdf_config.php');
require_once('tcpdf_min/tcpdf.php');
include 'functions/functions.php';
error_reporting(E_ALL);
ini_set('display_errors', '1');

$project_id = @$_GET['project_id'];
$lang = @$_GET['lang'];

$query = $mysqli->prepare("SELECT * FROM dne_projects WHERE id = ?");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$project = fetch_unique($query);

$query = $mysqli->prepare("SELECT logo_stread FROM dne_logos");
$query->execute();
$query->store_result();
$logo = fetch_unique($query);

if($lang == 'EN') {
   $title = 'Project '.@$project->name."<br/>Accounts Report";
   $dir_table = 'ltr';
   $style_table = "margin-top:25px;";
   $style_td = "text-align:left;padding-left:2px;";
   $name_sort = 's.name';
   $text_align = 'text-align:left';
   $padding = 'padding-left';  
}
else if($lang == 'HE') {
	$title = 'פרוייקט '.@$project->name_he."<br/>דו''ח חשבונות";
    $dir_table = 'rtl';
	$style_table = "margin-top:25px;margin-left:1%;";
	$style_td = "text-align:right;padding-right:12px;";
	$name_sort = 's.name_he';
	$text_align = 'text-align:right';
    $padding = 'padding-right';
}

$title.= '<br/>'.substr(date('Y-m-d'),8,2).'/'.substr(date('Y-m-d'),5,2).'/'.substr(date('Y-m-d'),0,4);
		  
$query = $mysqli->prepare("SELECT a.id AS id,a.id_projects_suppliers AS id_projects_suppliers,
                          a.description AS description,a.submit_date AS submit_date,a.pdf_submission AS pdf_submission,
						  a.submitted_account AS submitted_account,a.approval_date AS approval_date,
						  a.pdf_approval AS pdf_approval,a.approved_amount AS approved_amount,a.vat AS vat,
						  s.name AS s_name,s.name_he AS s_name_he				  
						  FROM dne_accounts a
						  LEFT JOIN dne_projects_suppliers ps on a.id_projects_suppliers = ps.id
						  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
						  WHERE ps.id_project = ? ORDER BY a.approval_date DESC");
$query->bind_param("i",$project_id);
$query->execute(); 
$query->store_result();
$total_payments_num_rows = $query->num_rows;
$accounts = fetch($query);

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
		
	    $pdf_file_name = substr(date('Y-m-d'),0,4).substr(date('Y-m-d'),5,2).substr(date('Y-m-d'),8,2).'-Accounts Report-'.$project_nickname.'.pdf';
	
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

$html = '<table width="100%" style="margin-top:15px;"><tr><td style="text-align:center;"><img src="uploads/'.@$logo->logo_stread.'" /><br/><br/></td></tr>';
$html.= '<tr><td width="15px;">&nbsp;</td><td style="text-align:center;padding-top:30px;"><span dir="'.$dir_table.'"><strong><u>'.$title.'</u></strong></span></td></tr></table>';
$html.= '<div class="row">';
$html.= '<div class="col-md-12">';
$html.= '<table dir="'.$dir_table.'" style="'.$style_table.'"><tr><td width="1%">&nbsp;</td><td><table cellpadding="4">';
$html.='<tr height="30px" style="font-size:12px;font-weight:bold;background-color:silver;">';
$html.='<th width="30px;" style="text-align:center;border:1px solid black;">#</th>';
$html.='<th width="120px;" style="text-align:center;border:1px solid black;">'.getLang2($lang,'supplier_name').'</th>';
$html.='<th width="150px;" style="text-align:center;border:1px solid black;">'.getLang2($lang,'description').'</th>';
$html.='<th width="70px;" style="text-align:center;border:1px solid black;">'.getLang2($lang,'submit_date').'</th>';
$html.='<th width="100px;" style="text-align:center;border:1px solid black;">'.getLang2($lang,'submitted_account_vat_included').'</th>';
$html.='<th width="70px;" style="text-align:center;border:1px solid black;">'.getLang2($lang,'approval_date').'</th>';
$html.='<th width="100px;" style="text-align:center;border:1px solid black;">'.getLang2($lang,'approved_amount_vat_included').'</th>';
$html.='<th width="50px;" style="text-align:center;border:1px solid black;">'.getLang2($lang,'vat').'</th>';
$html.='</tr>';

$count = 0;

foreach($accounts as $item) { 
    $count++;
	
	$submit_date = '';
	if(@$item->submit_date != '0000-00-00')
		$submit_date = substr(@$item->submit_date,8,2).'/'.substr(@$item->submit_date,5,2).'/'.substr(@$item->submit_date,2,2);
	
	$submitted_account = '';
	if(@$item->submitted_account != 0.00)
	    $submitted_account = isset($item->submitted_account)
		?((floor($item->submitted_account) == $item->submitted_account)
			?number_format($item->submitted_account,0,'.',',').'&nbsp;&#8362;'
			:number_format($item->submitted_account,2,'.',',').'&nbsp;&#8362;')
		:'';
	
	$approval_date = '';
	if(@$item->approval_date != '0000-00-00')
		$approval_date = substr(@$item->approval_date,8,2).'/'.substr(@$item->approval_date,5,2).'/'.substr(@$item->approval_date,2,2);
	
	$approved_amount = '';
	if(@$item->approved_amount != 0.00)
	    $approved_amount = isset($item->approved_amount)
		?((floor($item->approved_amount) == $item->approved_amount)
			?number_format($item->approved_amount,0,'.',',').'&nbsp;&#8362;'
			:number_format($item->approved_amount,2,'.',',').'&nbsp;&#8362;')
		:'';
	
	$invoice_date = '';
	if(@$item->invoice_date != '0000-00-00')
		$invoice_date = substr(@$item->invoice_date,8,2).'/'.substr(@$item->invoice_date,5,2).'/'.substr(@$item->invoice_date,2,2);
	
    $html.='<tr height="30px;" style="font-size:12px;">';
	$html.='<td width="30px;" style="text-align:center;border:1px solid black;">'.$count.'</td>';
	
	$html.='<td width="120px;" style="'.$style_td.'border:1px solid black;">';
	
	if($lang != 'HE') {
		if(strlen(@$item->s_name) > 18) 
			$html.= mb_substr(@$item->s_name,0,18,'UTF-8');
		else  
	       $html.= @$item->s_name;
	}
	else {
		if(strlen(@$item->s_name_he) > 18) 
			$html.= mb_substr(@$item->s_name_he,0,18,'UTF-8');
		else  
	       $html.= @$item->s_name_he;
	}
	
	$html.='</td>';

	$html.='<td width="150px;" style="'.$style_td.'border:1px solid black;">'.$item->description.'</td>';	
    $html.='<td width="70px;" style="'.@$text_align.';'.@$padding.':12px;border:1px solid black;">&nbsp;'.$submit_date.'</td>';
	$html.='<td width="100px;" style="'.@$text_align.';'.@$padding.':12px;border:1px solid black;">'.$submitted_account.'</td>';
	$html.='<td width="70px;" style="'.@$text_align.';'.@$padding.':12px;border:1px solid black;">&nbsp;'.$approval_date.'</td>';   
    $html.='<td width="100px;" style="'.@$text_align.';'.@$padding.':12px;border:1px solid black;">&nbsp;'.$approved_amount.'</td>';	
	$html.='<td width="50px;" style="'.@$text_align.';'.@$padding.':12px;border:1px solid black;">'.number_format($item->vat,0,'.',',').'&nbsp;%</td>';
	$html.='</tr>';
}

$html.='</table></td></tr></table>';
$html.='<br" /></div>';
$html.='</div>';

if($lang == "HE") 
	$pdf->setRTL(true);

$pdf->AddPage();
$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
ob_end_clean();
$pdf_name = substr(date('Y-m-d'),0,4).substr(date('Y-m-d'),5,2).substr(date('Y-m-d'),8,2).'-Accounts Report-'.$project->nickname.'.pdf';
$pdf->Output($pdf_name,'I');
?>