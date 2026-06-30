<?php
//use PHPMailer\PHPMailer\PHPMailer;
//use PHPMailer\PHPMailer\Exception;

require_once dirname(__DIR__) . '/config.php';
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$mysqli->set_charset("utf8");

$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
define('BASE_URL', $protocol . '://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/');

function fetch($result) {    
    $array = array();
    
    if($result instanceof mysqli_stmt)
    {
		$result->execute();
		$result->store_result();
        
        $variables = array();
        $data = array();
        $meta = $result->result_metadata();
        
        while($field = $meta->fetch_field())
            $variables[] = &$data[$field->name];
        
        call_user_func_array(array($result, 'bind_result'), $variables);
        
 
		$array = new stdClass();
        $i=0;
		while($result->fetch())
		{
			$array->$i = new stdClass();
			foreach($data as $k=>$v) $array->$i->$k = $v;
			$i++;
		}
    }
    elseif($result instanceof mysqli_result)
    {
		$array = new stdClass();
        $i=0;
		while($row = $result->fetch_assoc())
		{
			$array->$i = new stdClass();
			foreach($row as $k=>$v) $array->$i->$k = $v;
			$i++;
		}
    }
    return $array;
}

function fetch_unique($result){    
    $array = array();
    
    if($result instanceof mysqli_stmt)
    {  
		$result->execute();
		$result->store_result();
        
        $variables = array();
        $data = array();
        $meta = $result->result_metadata();
        
        while($field = $meta->fetch_field())
            $variables[] = &$data[$field->name];
        
        call_user_func_array(array($result, 'bind_result'), $variables);
        
		$array = new stdClass();
        $i=0;
		while($result->fetch())
		{
			$array->$i = new stdClass();
			foreach($data as $k=>$v) $array->$i->$k = $v;
			$i++;
		}
		if($i==1) {
			$new_object = new stdClass();
			$array=current( (Array)$array );
		} 
    }
    elseif($result instanceof mysqli_result)
    {
		$array = new stdClass();
        $i=0;
		while($row = $result->fetch_assoc())
		{
			$array->$i = new stdClass();
			foreach($row as $k=>$v) $array->$i->$k = $v;
			$i++;
		}
        if($i==1) {
			$new_object = new stdClass();
			$array=current( (Array)$array );
		} 
    }
    
	
    return $array;
}

function convert_array_to_obj_recursive($a) {
    if (is_array($a) ) {
        foreach($a as $k => $v) {
            if (is_integer($k)) {
                $a['index'][$k] = convert_array_to_obj_recursive($v);
            }
            else {
                $a[$k] = convert_array_to_obj_recursive($v);
            }
        }

        return (object) $a;
    }

    return $a; 
}

function get_date_name($date) {
	$tab = explode("-",$date);
	$annee = $tab[0];
	$mois = $tab[1];

	if($mois=='01')$mois='Janvier';
	if($mois=='02')$mois='Février';
	if($mois=='03')$mois='Mars';
	if($mois=='04')$mois='Avril';
	if($mois=='05')$mois='Mai';
	if($mois=='06')$mois='Juin';
	if($mois=='07')$mois='Juillet';
	if($mois=='08')$mois='Aout';
	if($mois=='09')$mois='Septembre';
	if($mois=='10')$mois='Octobre';
	if($mois=='11')$mois='Novembre';
	if($mois=='12')$mois='Décembre'; 
	
	return $mois." ".$annee;
}

function get_month($date) {
	$tab = explode("-",$date);
	$annee = $tab[0];
	$mois = $tab[1];

	if($mois=='01')$mois='Janvier';
	if($mois=='02')$mois='Février';
	if($mois=='03')$mois='Mars';
	if($mois=='04')$mois='Avril';
	if($mois=='05')$mois='Mai';
	if($mois=='06')$mois='Juin';
	if($mois=='07')$mois='Juillet';
	if($mois=='08')$mois='Aout';
	if($mois=='09')$mois='Septembre';
	if($mois=='10')$mois='Octobre';
	if($mois=='11')$mois='Novembre';
	if($mois=='12')$mois='Décembre'; 
	
	return $mois;
}

function get_year($date) {
	$tab = explode("-",$date);
	$annee = $tab[0];
	return $annee;
}

function smartDate($date, $lang = null) {
    if (!$date || $date == '0000-00-00') return '';
    $ts = strtotime($date);
    if (!$ts || $ts <= 0) return '';
    if (!$lang) $lang = @$_SESSION['lang'] ?: 'HE';
    static $months = [
        'FR' => ['','jan','fév','mar','avr','mai','juin','juil','aoû','sep','oct','nov','déc'],
        'EN' => ['','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
        'HE' => ['','ינו','פבר','מרץ','אפר','מאי','יוני','יולי','אוג','ספט','אוק','נוב','דצמ'],
    ];
    $month_num = (int)date('n', $ts);
    $day       = (int)date('j', $ts);
    $year      = date('y', $ts);
    $lang_key  = isset($months[$lang]) ? $lang : 'HE';
    $m         = $months[$lang_key][$month_num];
    return ($ts >= strtotime('-12 months')) ? $day.' '.$m : $m.' '.$year;
}

if (!function_exists('cleanCut')) {
    function cleanCut($string,$length,$cutString = '...') {
		if(strlen($string) <= $length) {
			return $string;
		}
		$str = substr($string,0,$length-strlen($cutString)+1);
		return substr($str,0,strrpos($str,' ')).$cutString;
    }	
}	

if (!function_exists('cleanChaine')) {
    function cleanChaine($String) {
        $Search = array("\\n", "\\r", "\n", "\r", "     ", "    ", "&amp;", " ", "        ", "       ", "      ", "     ", "    ", "   ", "  ", "à", "á", "â", "à", "À", "ç", "ç", "Ç", "é", "è", "ê", "ë", "É", "È", "é", "è", "ê", "í", "ï", "ï", "î", "ñ", "ô", "ò", "ö", "ô", "ó", "Ó", "ù", "û", ";");
        $Replace = array("-","-","-","-", "-", "", "-", "-", "-", "-", "-", "-", "-", "-", "-", "a", "a", "a", "a", "A", "c", "c", "C", "e", "e", "e", "e", "E", "E", "e", "e", "e", "i", "i", "i", "n", "o", "o", "o", "o", "o", "O", "u", "u", "\;");
        $String = str_replace($Search, $Replace, $String);
        $String = str_replace("'", "-", $String);
        $String = str_replace("\\", "-", $String);
            $String=str_replace('!','-',$String);
            $String=str_replace(':','-',$String);
            $String=str_replace('?','-',$String);
            $String=str_replace('’','',$String);
            $String=str_replace(',','',$String);
            $String=str_replace('.','',$String);
            $String=str_replace('---','-',$String);
            $String=str_replace('--','-',$String);
            $String=str_replace('%','',$String);
            $String=rtrim($String,'---');
            $String=rtrim($String,'--');
            $String=rtrim($String,'-');
            $String= strtolower($String);
        return $String;
    }
}

function encryptIt($str) {
    $cryptKey = 'qJB0rGtIn5UB1xG03efyCp';
    $str_encoded = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($cryptKey), $str, MCRYPT_MODE_CBC, md5(md5($cryptKey))));
    return($str_encoded);
}

function decryptIt($str) {
    $cryptKey = 'qJB0rGtIn5UB1xG03efyCp';
    $str_decoded = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($cryptKey), base64_decode($str), MCRYPT_MODE_CBC, md5(md5($cryptKey))), "\0");
    return($str_decoded);
}


function compressImage($source, $destination, $quality) { 
    // Get image info 
    $imgInfo = getimagesize($source); 
    $mime = $imgInfo['mime']; 
     
    // Create a new image from file 
    switch($mime){ 
        case 'image/jpeg': 
            $image = imagecreatefromjpeg($source); 
            imagejpeg($image, $destination, $quality);
            break; 
        case 'image/png': 
            $image = imagecreatefrompng($source); 
            imagepng($image, $destination, $quality);
            break; 
        case 'image/gif': 
            $image = imagecreatefromgif($source); 
            imagegif($image, $destination, $quality);
            break; 
        default: 
            $image = imagecreatefromjpeg($source); 
            imagejpeg($image, $destination, $quality);
    } 
     
    // Return compressed image 
    return $destination; 
}

function resizeImage($file, $max_width, $max_height, $outputFile) {
    // Get current dimensions
    list($original_width, $original_height) = getimagesize($file);
    
    // Calculate the scaling ratio
    $ratio = min($max_width / $original_width, $max_height / $original_height);
    
	
    // Calculate the new dimensions
	$new_width = intval($original_width * $ratio);
    $new_height = intval($original_height * $ratio);
    
    // Create a new true color image
    $image_p = imagecreatetruecolor($new_width, $new_height);
    
    // Get the image type
    $image_type = exif_imagetype($file);
    
    // Create a new image from file
    switch ($image_type) {
        case IMAGETYPE_JPEG:
            $image = imagecreatefromjpeg($file);
            break;
        case IMAGETYPE_PNG:
            $image = imagecreatefrompng($file);
            break;
        case IMAGETYPE_GIF:
            $image = imagecreatefromgif($file);
            break;
        default:
            return false;
    }
    
    // Copy and resize the old image into the new image
    imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $original_width, $original_height);
    
    // Output the new image to file
    switch ($image_type) {
        case IMAGETYPE_JPEG:
            imagejpeg($image_p, $outputFile);
            break;
        case IMAGETYPE_PNG:
            imagepng($image_p, $outputFile);
            break;
        case IMAGETYPE_GIF:
            imagegif($image_p, $outputFile);
            break;
    }
    
    // Free up memory
    imagedestroy($image_p);
    imagedestroy($image);
    
    return true;
} 

function getLang($code){
	global $mysqli;
	$query = $mysqli->prepare("SELECT ".$_SESSION['lang']." AS result 
	                          FROM dne_lang WHERE code='".$code."'");
	$query->execute();
    $query->store_result();
    $query = fetch_unique($query);
	return $query->result;
}

function getLang2($lang,$code){
	global $mysqli;
	$query = $mysqli->prepare("SELECT ".$lang." AS result 
	                          FROM dne_lang WHERE code='".$code."'");
	$query->execute();
    $query->store_result();
    $query = fetch_unique($query);
	return $query->result;
}

function isEffectivelyEmpty($field) {
    $text = strip_tags($field); 
    $text = str_replace(["\xc2\xa0","&nbsp;","\xE2\x80\x8B"],'',$text);
    $text = trim($text); 
    return $text === '';
}

function checkIfChangedField($meeting_id,$field){
	global $mysqli;
	
	if($field == 'destination_date')
		$sql = "SELECT id FROM dne_log_meeting_updates 
	            WHERE id_meeting = ".$meeting_id
				." AND destination_date <> '0000-00-00'";
	if($field == 'description')
		$sql = "SELECT id FROM dne_log_meeting_updates 
	            WHERE id_meeting = ".$meeting_id." AND remark <> '' 
				AND is_remark_appears_log = 1";	
	$query = $mysqli->prepare($sql);		
	$query->execute();
    $query->store_result();
    return $query->num_rows;
}

function toAlpha($data){
    $alphabet =   array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
    $alpha_flip = array_flip($alphabet);
    if($data <= 25){
      return $alphabet[$data];
    }
    elseif($data > 25){
      $dividend = ($data + 1);
      $alpha = '';
      $modulo;
      while ($dividend > 0){
        $modulo = ($dividend - 1) % 26;
        $alpha = $alphabet[$modulo] . $alpha;
        $dividend = floor((($dividend - $modulo) / 26));
      } 
      return $alpha;
    }
}

function setTextAlign($text) {
	if(containsHebrew($text))
	   return 'text-align:right';
    else
	   return 'text-align:left';
}

function setPadding($text) {
	if(containsHebrew($text))
	   return 'padding-right';
    else
	   return 'padding-left';
}

function containsHebrew($str) {
   $pattern = '/\p{Hebrew}/u';
   return preg_match($pattern, $str) === 1;
}

function containsEnglishCharacters($inputString) {
    return preg_match('/[a-zA-Z]/', $inputString);
}

function isNumeric($input) {
    return ctype_digit($input);
}

function rebuild_supplier_doh_lists($mysqli, $id_project) {
    $q = $mysqli->prepare("SELECT * FROM dne_custom_reports WHERE id_project = ? AND id_supplier > 0");
    $q->bind_param('i', $id_project);
    $q->execute();
    $q->store_result();
    $supplier_crs = fetch($q);
    foreach($supplier_crs as $cr) {
        if((int)@$cr->id_supplier == 0) continue;
        $qr = $mysqli->prepare("SELECT id FROM dne_responsibles WHERE id_project = ? AND id_projects_suppliers = ?");
        $id_sup = (int)$cr->id_supplier;
        $qr->bind_param('ii', $id_project, $id_sup);
        $qr->execute();
        $qr->store_result();
        $resps = fetch($qr);
        $ids = [];
        foreach($resps as $r) $ids[] = $r->id;
        if(empty($ids)) continue;
        $list = implode(',', $ids);
        $include_po = (int)@$cr->is_include_pass_on_tasks;
        if($include_po) {
            $u = $mysqli->prepare("UPDATE dne_custom_reports SET responsibles_list = ?, pass_ons_list = ? WHERE id = ?");
            $u->bind_param('ssi', $list, $list, $cr->id);
        } else {
            $u = $mysqli->prepare("UPDATE dne_custom_reports SET responsibles_list = ? WHERE id = ?");
            $u->bind_param('si', $list, $cr->id);
        }
        $u->execute();
        if(!empty($cr->sql_str)) {
            $pos = strpos($cr->sql_str, "WHERE");
            if($pos === false) continue;
            $where = substr($cr->sql_str, $pos);
            $where = preg_replace("/id_responsible IN\([^)]*\)/", "id_responsible IN($list)", $where);
            if($include_po) {
                $where = preg_replace("/id_pass_on IN\([^)]*\)/", "id_pass_on IN($list)", $where);
            } else {
                $and_or = strtoupper((string)@$cr->and_or_responsibles);
                if($and_or === 'OR') {
                    // OR mode + is_include_pass_on=0: replace whole (responsible OR pass_on) block
                    // with just id_responsible IN(list) — otherwise OR 1=1 would show all meetings
                    $where = preg_replace(
                        '/\(\s*(m\.)?id_responsible IN\([^)]*\)\s+OR\s+(m\.)?id_pass_on IN\([^)]*\)\s*\)/',
                        '${1}id_responsible IN(' . $list . ')',
                        $where
                    );
                } else {
                    // AND mode: neutralize pass_on condition
                    $where = preg_replace("/(m\.)?id_pass_on IN\([^)]*\)/", "1=1", $where);
                }
            }
            $new_sql = substr($cr->sql_str, 0, $pos) . $where;
            $u2 = $mysqli->prepare("UPDATE dne_custom_reports SET sql_str = ? WHERE id = ?");
            $u2->bind_param('si', $new_sql, $cr->id);
            $u2->execute();
        }
    }
}