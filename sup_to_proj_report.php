<?php
require_once('tcpdf_min/config/tcpdf_config.php');
require_once('tcpdf_min/tcpdf.php');
include 'functions/functions.php';
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

$sup_type = @$_GET['sup_type'];
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
   $dir_table = 'ltr';
   $style_table = "margin-top:25px;margin-right:2.3%;";
   $style_tr = 'text-align:left;padding-left:12px;border:1px solid black;';
   $project_name = @$project->name;
   
   if($sup_type == 'S')
       $title_pdf = $project_name.'<br/>List of contractors and suppliers';
   else if($sup_type == 'D')
	   $title_pdf = $project_name.'<br/>List of planners and consultants';
}
else if($lang == 'HE') {
	$dir_table = 'rtl';
    $style_table = "margin-top:25px;margin-left:2.3%;";
	$style_tr = 'text-align:right;padding-right:12px;border:1px solid black;';
	$project_name = @$project->name_he;
	
	if($sup_type == 'S')
       $title_pdf = $project_name.'<br/>רשימת קבלנים וספקים';
    else if($sup_type == 'D')
	   $title_pdf = $project_name.'<br/>רשימת מתכננים ויועצים';
}

$title_pdf.= '<br/>'.substr(date('Y-m-d'),8,2).'/'.substr(date('Y-m-d'),5,2).'/'.substr(date('Y-m-d'),0,4);

$project_nickname = '<div style="font-size:18px;">'.@$project->nickname.'</div>';

$query = $mysqli->prepare("SELECT ps.id AS id,ps.id_project AS id_project,
                          s.name AS s_name,s.name_he AS s_name_he,sfow.name AS sfow_name,sfow.name_he AS sfow_name_he,
                          s.phone AS phone,s.email_office AS email_office
						  FROM dne_projects_suppliers AS ps 
						  LEFT JOIN dne_projects AS p ON ps.id_project = p.id 
						  LEFT JOIN dne_suppliers AS s ON ps.id_supplier = s.id
						  LEFT JOIN dne_sup_field_of_work sfow ON s.id_field_of_work = sfow.id
						  WHERE p.id = ? AND s.type = ?
						  ORDER BY sfow.name_he,s.name_he");
$query->bind_param("is",$project_id,$sup_type);
$query->execute(); 
$query->store_result();
$existing_sup_proj_num_rows = $query->num_rows;
$sup_proj = fetch($query);

$html = '<table width="100%" style="margin-top:15px;"><tr><td style="text-align:center;"><img src="uploads/'.@$logo->logo_stread.'" /><br/><br/></td></tr>';
$html.= '<tr><td style="text-align:center;padding-top:30px;"><span dir="'.$dir_table.'"><strong><u>'.$title_pdf.'</span></u></strong></td></tr></table>';
$html.= '<div class="row">';
$html.= '<div class="col-md-12">';
$html.= '<table dir="'.$dir_table.'" style="'.$style_table.'"><tr><td width="7%">&nbsp;</td><td><table cellpadding="4">';
$html.='<tr height="35px;" style="font-size:12px;font-weight:bold;background-color:silver;">';
$html.='<th width="30px;" style="text-align:center;border:1px solid black;">'.getLang2($lang,'supplier_number').'</th>';
$html.='<th width="120px;" style="text-align:center;border:1px solid black;">'.getLang2($lang,'supplier_domain').'</th>';
$html.='<th width="150px;" style="text-align:center;border:1px solid black;">'.getLang2($lang,'supplier_name').'</th>';
$html.='<th width="100px;" style="text-align:center;border:1px solid black;">'.getLang2($lang,'phone').'</th>';
$html.='<th width="200px;" style="text-align:center;border:1px solid black;">'.getLang2($lang,'email').'</th>';
$html.='</tr>';
$count = 0;

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
		
		$pdf_file_name = '';
		
	    $pdf_file_name = substr(date('Y-m-d'),0,4).substr(date('Y-m-d'),5,2).substr(date('Y-m-d'),8,2).'-';
		if(@$_GET['sup_type'] == 'D') 
			$pdf_file_name .= 'Designers List';
		else 
		    $pdf_file_name .= 'Suppliers List';
		$pdf_file_name .='-'.$project_nickname.'.pdf';
	
		$this->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);  
        $this->SetFont('freesans', '', 10);
        $this->SetY(-25);
		$this->Ln();
        $this->Cell(0, 0, '', 'T');

        $this->SetX($this->lMargin);

        $pagePagination = 'Page ' . $this->getAliasNumPage() . ' of ' . $this->getAliasNbPages();
	
	    if(@$_GET['lang'] == 'HE') {
		    $this->setPrintFooter(true);
			$this->Cell(100, 10, $project_name, 0, 0, 'R');
            $this->Cell(60, 10, $pagePagination, 0, 0, 'C');
            $this->Cell(126, 10, $pdf_file_name, 0, 0, 'L');
		}
		else {
			$this->setPrintFooter(false);
			$this->Cell(100, 10, $project_name, 0, 0, 'L');
            $this->Cell(100, 10, $pagePagination, 0, 0, 'C');
            $this->Cell(85, 10, $pdf_file_name, 0, 0, 'R');
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

foreach($sup_proj as $item) { 
    $count++;
	
    $html.='<tr height="30px;" style="font-size:12px;">'; 
	$html.='<td width="30px;" style="text-align:center;border:1px solid black;">'.$count.'</td>';
	
	$html.='<td width="120px;" style="'.$style_tr.'">';
	
	if($lang != 'HE') {
		if(strlen(@$item->sfow_name) > 18) 
			$html.= mb_substr(@$item->sfow_name,0,18,'UTF-8');
		else  
	       $html.= @$item->sfow_name;
	}
	else {
		if(strlen(@$item->sfow_name_he) > 18) 
			$html.= mb_substr(@$item->sfow_name_he,0,18,'UTF-8');
		else  
	       $html.= @$item->sfow_name_he;
	}
	
	$html.='</td>';
	
	$html.='<td width="150px;" style="'.$style_tr.'">';
	
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
	
	$html.='<td width="100px;" style="text-align:center;border:1px solid black;">&nbsp;'.$item->phone.'</td>';
	$html.='<td width="200px;" style="text-align:center;border:1px solid black;">&nbsp;'.$item->email_office.'</td>';
    $html.='</tr>';
}
$html.='</table></td></tr></table>';
$html.='</div>';
$html.='</div>';

$pdf->setRTL(false);
if($lang == 'HE')
   $pdf->setRTL(true);

$pdf->AddPage();
$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
ob_end_clean();

if($sup_type == 'S')
	$pdf_name = substr(date('Y-m-d'),0,4).substr(date('Y-m-d'),5,2).substr(date('Y-m-d'),8,2).'-Suppliers List-'.$project->nickname.'.pdf';
else if($sup_type == 'D')
	$pdf_name = substr(date('Y-m-d'),0,4).substr(date('Y-m-d'),5,2).substr(date('Y-m-d'),8,2).'-Designers List-'.$project->nickname.'.pdf';
$pdf->Output($pdf_name,'I');
?>