<?php
require_once('tcpdf_min/config/tcpdf_config.php');
require_once('tcpdf_min/tcpdf.php');
include 'functions/functions.php';

$project_id = @$_GET['project_id'];

$query = $mysqli->prepare("SELECT * FROM dne_projects WHERE id = ?");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$project = fetch_unique($query);

$query = $mysqli->prepare("SELECT logo_stread FROM dne_logos");
$query->execute();
$query->store_result();
$logo = fetch_unique($query);

$_SESSION['lang'] = 'HE';

$dir_table = 'ltr';
$style_table = "margin-top:25px;";
$style_td = 'text-align:left;padding-left:12px;';
$text_align = 'text-align:left';
$padding = 'padding-left';

if($_SESSION['lang'] == 'EN') {
   $title = @$project->name.'<br/>Budget Costs Evaluation';
}
else if($_SESSION['lang'] == 'FR') {
   $title = @$project->name.'<br/>Evaluation Cost';
}
else if($_SESSION['lang'] == 'HE') {
    $dir_table = 'rtl';
	$style_table = "margin-top:25px;margin-left:2.3%;";
	$style_td = 'text-align:right;padding-right:12px;';
	$title = @$project->name_he.'<br/>עלויות הערכת תקציב';
	$text_align = 'text-align:right';
    $padding = 'padding-right';
}

$title.= '<br/>'.substr(date('Y-m-d'),8,2).'/'.substr(date('Y-m-d'),5,2).'/'.substr(date('Y-m-d'),0,4);

$query = $mysqli->prepare("SELECT * FROM dne_projects WHERE id = ?");
$query->bind_param("i",$project_id );
$query->execute();
$query->store_result();
$project = fetch_unique($query);

$query = $mysqli->prepare("SELECT bce.name AS name,bce.description AS description,bce.evaluation_cost AS evaluation_cost,
                          bce.evaluation_date AS evaluation_date,sfow.name AS sfow_name,sfow.name_he AS sfow_name_he
                          FROM dne_budget_costs_eval bce
						  LEFT JOIN dne_sup_field_of_work sfow ON bce.id_field_of_work = sfow.id
						  WHERE id_project = ?
						  ORDER BY bce.name DESC");
$query->bind_param("i",$project_id);
$query->execute(); 
$query->store_result();
$budget_eval_costs_num_rows = $query->num_rows;
$budget_costs_eval = fetch($query);

class PDF extends TCPDF {
    public function Footer() { 
		$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		
        if ($mysqli->connect_error) {
            die("Connection failed: " . $mysqli->connect_error);
        }
        
		$mysqli->set_charset("utf8");
		
        $query = "SELECT nickname,name_he FROM dne_projects WHERE id =".@$_GET['project_id'];
        $result = $mysqli->query($query);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();	
			$project_name = 'פרוייקט '.$row['name_he'];   
			$project_nickname = $row['nickname'];
	    } else {
            $project_name = $project_nickname = 'No Data Found';
        }
		
		
	    $pdf_file_name = substr(date('Y-m-d'),0,4).substr(date('Y-m-d'),5,2).substr(date('Y-m-d'),8,2).'-Budget Costs Eval-'.$project_nickname.'.pdf';
	
		$this->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);  
        $this->SetFont('freesans', '', 10);
        $this->SetY(-25);
		$this->Ln();
        $this->Cell(0, 0, '', 'T');

        $this->SetX($this->lMargin);

        $pagePagination = 'Page ' . $this->getAliasNumPage() . ' of ' . $this->getAliasNbPages();
	
		$this->setPrintFooter(true);
	    $this->Cell(60, 10, $project_name, 0, 0, 'R');
        $this->Cell(60, 10, $pagePagination, 0, 0, 'C');
        $this->Cell(78, 10, $pdf_file_name, 0, 0, 'L');	
    }
}

$html = '<table width="100%" style="margin-top:15px;"><tr><td style="text-align:center;"><img src="uploads/'.@$logo->logo_stread.'" /><br/><br/></td></tr>';
$html.= '<tr><td style="text-align:center;padding-top:30px;"><span dir="'.$dir_table.'"><strong><u>'.$title.'</span></u></strong></td></tr></table>';
$html.= '<div class="row">';
$html.= '<div class="col-md-12">';
$html.= '<table dir="'.$dir_table.'" style="'.$style_table.'"><tr><td width="1%">&nbsp;</td><td><table cellpadding="5">';
$html.='<tr height="35px;" style="font-size:12px;font-weight:bold;background-color:silver;">';
$html.='<th width="30px;" style="text-align:center;border:1px solid black;">#</th>';
$html.='<th width="160px;" style="text-align:center;border:1px solid black;">'.getLang('supplier_name').'</th>';
$html.='<th width="100px;" style="text-align:center;border:1px solid black;">'.getLang('supplier_domain').'</th>';
$html.='<th width="200px;" style="text-align:center;border:1px solid black;">'.getLang('Description').'</th>';
$html.='<th width="120px;" style="text-align:center;border:1px solid black;">'.getLang('evaluation_cost').'</th>';
$html.='<th width="80px;" style="text-align:center;border:1px solid black;">'.getLang('evaluation_date').'</th>';
$html.='</tr>';

$count = 0;

foreach($budget_costs_eval as $item) {
    $evaluation_date = '';
	if($item->evaluation_date != '0000-00-00') 
		$evaluation_date = substr($item->evaluation_date,8,2).'/'.substr($item->evaluation_date,5,2).'/'.substr($item->evaluation_date,2,2);
	
	$count++;
	
	$evaluation_cost_display = '';
	if($item->evaluation_cost > 0){
	    $evaluation_cost_display = round($item->evaluation_cost);
	    $evaluation_cost_display = isset($item->evaluation_cost)
		?((floor($item->evaluation_cost) == $item->evaluation_cost)
			?number_format($item->evaluation_cost,0,'.',',').'&nbsp;&#x20aa;'
			:number_format($item->evaluation_cost,2,'.',',').'&nbsp;&#x20aa;')
		:'';
	}
	
    $html.='<tr height="30px;" style="font-size:12px;">'; 
	$html.='<td width="30px;" style="text-align:center;border:1px solid black;">'.$count.'</td>';
	$html.='<td width="160px;" style="'.@$text_align.';'.@$padding.':12px;border:1px solid black;">'.$item->name.'</td>';
	$html.='<td width="100px;" style="'.$style_td.'border:1px solid black;">'.@$item->sfow_name.'</td>';
	$html.='<td width="200px;" style="'.$style_td.'border:1px solid black;">'.@$item->description.'</td>';
	$html.='<td width="120px;" style="'.@$text_align.';'.@$padding.':12px;border:1px solid black;">'.@$evaluation_cost_display.'</td>';
	$html.='<td width="80px;" style="text-align:center;border:1px solid black;">&nbsp;'.$evaluation_date.'</td>';
    $html.='</tr>';
}
$html.='</table></td></tr></table>';
$html.='</div>';
$html.='</div>';

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
$pdf->setRTL(true);
$pdf->AddPage();

$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
ob_end_clean();
$pdf_name = substr(date('Y-m-d'),0,4).substr(date('Y-m-d'),5,2).substr(date('Y-m-d'),8,2).'-Budget_Costs_Eval-'.$project->nickname.'.pdf';
$pdf->Output($pdf_name,'I');
?>