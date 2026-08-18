<?php
session_start();
require_once('tcpdf_min/config/tcpdf_config.php');
require_once('tcpdf_min/tcpdf.php');
include 'functions/functions.php';
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

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
    $title = 'Project '.@$project->name."<br/>Orders Report";
	$dir_table = 'ltr';
	$style_table = "margin-top:25px;";
	$style_td = 'text-align:left;padding-left:12px;';
	$name_sort = 's.name';
	$style_td_totals = "text-align:right;padding-right:12px;";
	$text_align = 'text-align:left';
	$padding = 'padding-left';
}
else if($lang == 'HE') {
	$title = 'פרוייקט '.@$project->name_he."<br/>דו''ח הזמנות";
    $dir_table = 'rtl';
	$style_td_totals = "text-align:left;padding-left:12px;";
	$style_table = "margin-top:25px;margin-left:2.3%;";
	$style_td = 'text-align:right;padding-right:12px;';
	$name_sort = 's.name_he';
	$text_align = 'text-align:right';
    $padding = 'padding-right';
}

$title.= '<br/>'.substr(date('Y-m-d'),8,2).'/'.substr(date('Y-m-d'),5,2).'/'.substr(date('Y-m-d'),0,4);

$query = $mysqli->prepare("SELECT o.id_projects_suppliers AS id_projects_suppliers,o.description AS description,o.signature_date AS signature_date,o.sum_order AS sum_order,
                          s.name AS s_name,s.name_he AS s_name_he,sfow.name AS sfow_name,sfow.name_he AS sfow_name_he
                          FROM dne_orders o
                          LEFT JOIN dne_projects_suppliers ps on o.id_projects_suppliers = ps.id
						  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
						  LEFT JOIN dne_sup_field_of_work sfow ON s.id_field_of_work = sfow.id
						  WHERE ps.id_project = ?
						  ORDER BY o.signature_date,s.type,".$name_sort);
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$orders_num_rows = $query->num_rows;
$orders = fetch($query);

$html = '<table width="100%" style="margin-top:15px;"><tr><td style="text-align:center;"><img src="uploads/'.@$logo->logo_stread.'" /><br/><br/></td></tr>';
$html.= '<tr><td style="text-align:center;padding-top:30px;"><span dir="'.$dir_table.'"><strong><u>'.$title.'</u></strong></span></td></tr></table>';
$html.= '<div class="row">';
$html.= '<div class="col-md-12">';
$html.= '<table dir="'.$dir_table.'" style="'.$style_table.'"><tr><td width="1%">&nbsp;</td><td><table cellpadding="4">';
$html.='<tr height="35px;" style="font-size:12px;font-weight:bold;background-color:silver;height:50px;">';
$html.='<th width="30px;" style="text-align:center;border:1px solid black;">#</th>';
$html.='<th width="70px;" style="text-align:center;border:1px solid black;">'.getLang2($lang,'signature_date').'</th>';
$html.='<th width="120px;" style="text-align:center;border:1px solid black;">'.getLang2($lang,'supplier_name').'</th>';
$html.='<th width="120px;" style="text-align:center;border:1px solid black;">'.getLang2($lang,'supplier_domain').'</th>';
$html.='<th width="250px;" style="text-align:center;border:1px solid black;">'.getLang2($lang,'description').'</th>';
$html.='<th width="100px;" style="text-align:center;border:1px solid black;">'.getLang2($lang,'total_orders').'</th>';
$html.='</tr>';

$count = 0;

$total_sum_orders = 0;

$border_bottom = 'border-bottom:1px solid white';

foreach($orders as $item){ 
    $count++;
    $data_bg_color = '#ffffff';
	if($count%2 != 0) 
	   $data_bg_color = '#dedede';
    
	if($count == $orders_num_rows)
	   $border_bottom = 'border-bottom:1px solid black';

	$signature_date = smartDate($item->signature_date, $lang);
	
    $html.='<tr height="30px;" style="font-size:12px;background-color:'.$data_bg_color.';">'; 
	$html.='<td width="30px;" style="text-align:center;border:1px solid black;'.@$border_bottom.'">'.$count.'</td>';
	$html.='<td width="70px;" style="text-align:center;border:1px solid black;'.@$border_bottom.'">&nbsp;'.$signature_date.'</td>';
	$html.='<td width="120px;" style="'.$style_td.'border:1px solid black;'.@$border_bottom.'">';
	
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
	 
	 $html.='<td width="120px;" style="'.$style_td.'border:1px solid black;'.@$border_bottom.'">';
	 
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
	
	$sum_order_display = isset($item->sum_order)
    ?((floor($item->sum_order) == $item->sum_order)
        ?number_format($item->sum_order,0,'.',',').'&nbsp;&#x20aa;'
        :number_format($item->sum_order,2,'.',',').'&nbsp;&#x20aa;')
    :'';
	
	$html.='</td>';
	$html.='<td width="250px;" style="'.$style_td.'border:1px solid black;'.@$border_bottom.'">&nbsp;'.$item->description.'</td>';
	$html.='<td width="100px;" style="direction:ltr;'.@$text_align.';'.@$padding.':12px;border:1px solid black;'.@$border_bottom.'">'.@$sum_order_display.'&nbsp;&#x20aa;</td>';
    $html.='</tr>';
	
	$total_sum_orders += $item->sum_order;
}

$total_sum_orders = isset($total_sum_orders)
?((floor($total_sum_orders) == $total_sum_orders)
	?number_format($total_sum_orders,0,'.',',')
	:number_format($total_sum_orders,2,'.',','))
:'';

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
		
	    $pdf_file_name = substr(date('Y-m-d'),0,4).substr(date('Y-m-d'),5,2).substr(date('Y-m-d'),8,2).'-Orders Report-'.$project_nickname.'.pdf';
	
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

$html.='<tr style="font-size:12px;background-color:#dcf1fa;">';
$html.='<td colspan="5" style="'.$style_td_totals.'border:1px solid black;"><strong>'.getLang2($lang,'total').'</strong></td>';
$html.='<td style="'.@$text_align.';'.@$padding.':5px;border:1px solid black;"><strong>'.$total_sum_orders.'&nbsp;&#x20aa;</strong></td></tr>';
$html.='</table></td></tr></table>';
$html.='</div>';
$html.='</div>';

if($lang == "HE") 
	$pdf->setRTL(true);

$pdf->AddPage();
$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
ob_end_clean();
$pdf_name = substr(date('Y-m-d'),0,4).substr(date('Y-m-d'),5,2).substr(date('Y-m-d'),8,2).'-Orders Report-'.$project->nickname.'.pdf';
$pdf->Output($pdf_name,'I');
?>