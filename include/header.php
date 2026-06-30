<?php
ini_set('display_errors','1');
error_reporting(E_ALL);

if (session_status() == PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime',86400);
    ini_set('session.gc_probability',0);
    
    session_set_cookie_params([
        'lifetime' => 86400,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https'),
        'httponly' => true,  
        'samesite' => 'Lax'
    ]);
    session_start();
}

if((strpos($_SERVER['REQUEST_URI'],'login') === false 
   && strpos($_SERVER['REQUEST_URI'],'register') === false) 
   && !isset($_SESSION['id_user'])){	
   header('Location:login.php');
}

$projects_label = 'פרוייקטים';
$suppliers_label = 'ספקים';
$designers_label = 'DESIGNERS';
$domains_label = 'DOMAINS';

$projects_list_array = explode(',',@$_SESSION['projects_list']);
?>

<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <title>DNE</title>

  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
  <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"
          integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p"
          crossorigin="anonymous"></script>
		  
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous" />

  <script src="https://html2canvas.hertzen.com/dist/html2canvas.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />

  <link href="assets/dataTables/css/jquery.dataTables.css" rel="stylesheet" type="text/css" />
  <link href="assets/dataTables/css/jquery.dataTables_themeroller.css" rel="stylesheet" type="text/css" />
  <script src="assets/dataTables/js/jquery.dataTables.js" type="text/javascript"></script>
  <script src="assets/dataTables/js/jquery.dataTables.columnFilter.js" type="text/javascript"></script>

  <link href="css/custom.css?v=<?=filemtime(__DIR__.'/../css/custom.css')?>" rel="stylesheet" type="text/css" />
  <link rel="stylesheet" type="text/css" href="css/style.css?v=<?=filemtime(__DIR__.'/../css/style.css')?>" />

  <script src="js/script.js?v=<?=filemtime(__DIR__.'/../js/script.js')?>" type="text/javascript"></script>
</head>

<html>
    <body>
	    <?php
        if(strpos($_SERVER['REQUEST_URI'],'login') === false && strpos($_SERVER['REQUEST_URI'],'pdf_report_a') === false &&
		   strpos($_SERVER['REQUEST_URI'],'pdf_report_b') === false && strpos($_SERVER['REQUEST_URI'],'pdf_report_c') === false) { ?>
		    <nav class="navbar navbar-expand-md navbar-dark bg-4c555e">
			    <div class="container-fluid px-3">
					<div class="collapse navbar-collapse d-flex justify-content-between align-items-center" id="navbarSupportedContent">
					    <ul class="navbar-nav dir-rtl">
							<li class="nav-item dropdown">
							    <a class="nav-link dropdown-toggle" id="projectsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
								    פרוייקטים
							    </a>
							    <ul class="dropdown-menu dropdown-menu-scrollable min-with-3rem cursor-pointer alignCenter dir-rtl" aria-labelledby="projectsDropdown">
									<?php for($i = 0; $i < sizeof($projects_list_array); $i++) {
									  $project_details_array = explode('-', $projects_list_array[$i]);
									?>
									  <li>
										<a class="dropdown-item alignCenter dir-rtl" onclick="toProjectHome(<?=@$project_details_array[0]?>)">
										  <?=@$project_details_array[1]?>
										</a>
									  </li>
									<?php } ?>
							    </ul>
							</li>
							<li class="nav-item dropdown">
								<a class="nav-link dropdown-toggle" id="suppliersDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
									ספקים
							    </a>
								<ul class="dropdown-menu min-with-3rem dir-rtl alignCenter" aria-labelledby="suppliersDropdown">
									<li><a class="dropdown-item" href="suppliers.php?type=D">מתכננים/יועצים</a></li>
									<li><a class="dropdown-item" href="suppliers.php?type=S">ספקים/קבלנים</a></li>
									<li><a class="dropdown-item" href="domains.php?lang_screen=HE">תחומי פעילות</a></li>
								</ul>
							</li>
					    </ul>				
						<ul class="navbar-nav dir-rtl align-items-center flex-row">
							<li class="nav-item dropdown text-center px-2 d-flex align-items-center gap-2">	
								<a class="nav-link cursor-pointer" id="home_link">
									<img src="images/white_home_icon.png" alt="home icon" width="30" height="30" />
								</a>
								<a class="nav-link" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
									<div class="padding-4x-4y borderRadius20 border-2px-solid-white font-weight-bold">									
										<span class="fontSize13 colorWhite"><?= @$_SESSION['user_nickname'] ?></span>
									</div>	
								</a>
								<ul class="dropdown-menu min-width-3rem dir-rtl text-end" aria-labelledby="userDropdown">
									<li><a class="dropdown-item" href="add_project.php?id=0">הוסף פרוייקט</a></li>
									<li><a class="dropdown-item" href="projects.php?is_inactive_project=1">פרוייקטים רדומים</a></li>
									<?php if(@$_SESSION['user_role'] == 'admin') { ?>
										<li><a class="dropdown-item" href="users.php">ניהול משתמשים</a></li>
									<?php } ?>
									<li><a class="dropdown-item" href="vat.php">מע''מ</a></li>
									<li><a class="dropdown-item" href="tasks_managment.php">ניהול סוגי משימות</a></li>
									<li><a class="dropdown-item" href="progress_status_managment.php">ניהול סטטוס</a></li>
									<li><a class="dropdown-item" href="meeting_types_managment.php">ניהול סוגי ישיבות</a></li>
									<li><a class="dropdown-item" href="graphic_settings.php">הגדרות גוונים ורקע</a></li>
									<li><a class="dropdown-item" href="logo_managment.php">תמונות לוגו</a></li>
								</ul>
							</li>
                         </ul>
					</div>
			    </div>
            </nav>
	    <?php }	?>
		
		<script>
		$(document).on('click','#home_link',function(){
			$('#left_initial_content').show();
			$('#left_new_content').hide();
			localStorage.removeItem('target');
			localStorage.removeItem('prId');
			localStorage.removeItem('bgcolor');
			location.href = 'projects.php?filter_by=projects';
		});
		
		function toProjectHome(project_id){
			let form_data = new FormData();	
	        form_data.append('id_project',project_id);
	
	
			$.ajax({
				type: 'POST',
				url: 'set_id_project_session.php',
				data: form_data,
				cache: false,
				processData: false,
				contentType: false,			
				success: function(data){ 
				   location.href = 'meetings.php?project_id='+project_id;		
				},
			});				
		}
		</script>
		
		<style>
		.navbar-nav {
			flex-direction: row;
		}
		
		.navbar .dropdown {
			position: relative; 
		}

		.navbar .dropdown-menu {
			position: absolute;  
			top: 100%;
            min-width: 5rem;			
			margin-top: 0;       
			z-index: 1050;      
		}
		
		.dropdown-item {
			padding: 1px 7px;
			font-size: 13px;
		}
		
		@media (max-width: 1024px) {
			.nav-link {
				font-size: 12px;
			}
		}

		@media (max-width: 850px) {
			.nav-link {
				font-size: 10px;
			}
		}

		@media (max-width: 768px) {
			.nav-link {
				font-size: 9px;
			}
		}

		@media (max-width: 620px) {
			.nav-link {
				font-size: 8px;
			}		
		}
		</style>