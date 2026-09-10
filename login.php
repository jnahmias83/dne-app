<?php
include 'include/header.php';
include 'functions/functions.php';

if(isset($_POST['login_btn'])) {
	$query = $mysqli->prepare("SELECT * FROM dne_users WHERE username = ? and password = ?");
    $query->bind_param("ss",$_POST['username'],$_POST['password']);
    $query->execute();
	$query->store_result();
	
	if($query->num_rows > 0) {
		$query = fetch_unique($query);
		$id_user = @$query->id;
		$nickname = @$query->nickname;
		$lang = @$query->lang;
		
		$is_project_active = 1;
		$query = $mysqli->prepare("SELECT p.* FROM dne_projects p
		                          WHERE p.is_project_active = ?
		                          AND EXISTS (
		                               SELECT 1
		                               FROM dne_responsibles r
		                               WHERE r.id_project = p.id
		                               AND r.id_user = ?
		                          )
		                          ORDER BY p.nickname");
		$query->bind_param("ii",$is_project_active,$id_user);
		$query->execute();
		$query->store_result();
		$projects = fetch($query);

		$projects_array = array();
		foreach($projects as $item) {
			array_push($projects_array,$item->id.'-'.$item->nickname);
		}
        
		session_start();
		$_SESSION['projects_list'] = implode(',', $projects_array);			
		$_SESSION['id_user'] = $id_user;
        $_SESSION['user_nickname'] = $nickname;
		$_SESSION['lang'] = $lang;	
		
		$query = $mysqli->prepare("SELECT * FROM dne_users WHERE id = ?");
		$query->bind_param("i",$_SESSION['id_user']);
		$query->execute(); 
		$query->store_result();
		$user = fetch_unique($query);
		$user_role = $user->role;
		$_SESSION['user_role'] = $user_role;	
		
		session_write_close();
		header("Location:projects.php");
	}
	else { 
		$msg_alert = "Invalid username or password!";
	}	
}
?>

        <form method="post" action="" class="form" onsubmit="return validPassword();">   				    					
			<div class="container mt-5">
			    <div class="row">
				    <div class="col-md-4"></div>
				    <div class="col-md-4">
					    <div class="row marginTop25 alignCenter">
							<div class="col-md-12">
								<img src="images/davidnahmias_logo.png" width="170" height="170" />
							</div>
			            </div>
				
						<div class="row marginTop25 alignCenter">
						    <div class="col-md-12">
							    <strong>Username</strong>
								<br/>
								<input type="text" class="marginTop5 width200 height35 paddingLeft5" id="username" name="username" placeholder="Username" />					
							</div>
						</div>
						
						<div class="row marginTop20 alignCenter">
						    <div class="col-md-12">
							    <strong>Password</strong>
								<br/>
								<input type="password" class="marginTop5 width200 height35 paddingLeft5" id="password" name="password" placeholder="Password" />					
							</div>
						</div>
						
						<div id="div_alert" class="row marginTop5 colorRed alignCenter">
						    <div class="col-md-12">
							    <?=@$msg_alert?>
							</div>
						</div>			
						
						<div class="row marginTop15 alignCenter">
							<div class="col-md-12">
								<button type="submit" class="btn" id="login_btn" name="login_btn">Login</button>
							</div>
						</div>

						<?php
						// Le bouton Google ne s'affiche que sur le domaine public (origine autorisee cote Google).
						// Sur dne.local / localhost il reste masque : Google refuserait la connexion de toute facon.
						$google_login_enabled = defined('GOOGLE_CLIENT_ID') && GOOGLE_CLIENT_ID !== ''
							&& stripos(@$_SERVER['HTTP_HOST'], 'davidnahmiasengineering.com') !== false;
						if($google_login_enabled): ?>
						<div class="row marginTop15 alignCenter">
							<div class="col-md-12 google-signin-wrap">
								<div class="google-divider"><span>or</span></div>
								<div id="g_id_onload"
								     data-client_id="<?=htmlspecialchars(GOOGLE_CLIENT_ID)?>"
								     data-callback="handleGoogleCredential"
								     data-auto_prompt="false"></div>
								<div class="g_id_signin" data-type="standard" data-shape="pill" data-theme="outline"
								     data-text="signin_with" data-size="large" data-logo_alignment="left"></div>
								<button type="button" id="google_native_btn" class="btn google-native-btn" style="display:none;"
								        onclick="if(window.AndroidNative&&AndroidNative.googleSignIn){AndroidNative.googleSignIn();}">
									Se connecter avec Google
								</button>
							</div>
						</div>
						<script src="https://accounts.google.com/gsi/client" async defer></script>
						<?php endif; ?>

						<!-- Version: <?=trim(@file_get_contents(__DIR__.'/version.txt'))?> -->
					</div>
					<div class="col-md-4"></div>
				</div>
			</div>
		</form>
	</body>	
</html>

<script>
// Dans l'app DNEMobile (WebView), Google bloque le bouton "Sign in with Google" standard.
// On le masque et on affiche un bouton qui declenche la connexion Google NATIVE de l'app ;
// l'app renverra le jeton via handleGoogleCredential({credential:'...'}).
(function(){
	function swapForNative(){
		if(!(window.AndroidNative && AndroidNative.googleSignIn)) return;
		var gis = document.querySelector('.g_id_signin');
		var onload = document.getElementById('g_id_onload');
		var nativeBtn = document.getElementById('google_native_btn');
		if(gis) gis.style.display = 'none';
		if(onload) onload.style.display = 'none';
		if(nativeBtn) nativeBtn.style.display = 'inline-block';
	}
	if(document.readyState !== 'loading') swapForNative();
	else document.addEventListener('DOMContentLoaded', swapForNative);
})();

function handleGoogleCredential(response){
	if(!response || !response.credential){ return; }
	let fd = new FormData();
	fd.append('credential', response.credential);
	fetch('google_login.php', { method:'POST', body: fd })
		.then(function(r){ return r.text(); })
		.then(function(t){
			t = (t || '').trim();
			if(t === 'ok'){ window.location.href = 'projects.php'; return; }
			let msg;
			if(t === 'not_linked') msg = "This Google account is not linked to a DNE user. Contact the administrator.";
			else if(t === 'not_configured') msg = "Google sign-in is not configured yet.";
			else msg = "Google sign-in failed. Please try again.";
			$('#div_alert').find('div').text(msg);
		})
		.catch(function(){ $('#div_alert').find('div').text("Google sign-in failed. Please try again."); });
}

function validPassword(){
  let upperCase = new RegExp('[A-Z]');
  let lowerCase = new RegExp('[a-z]');
  let digit = new RegExp('[0-9]');

  if($('#password').val().match(upperCase) && $('#password').val().match(lowerCase) && $('#password').val().match(digit) && $('#password').val().length>=8)  
  {
       return true;
  }
  else
  {
       alert('Your password must contain at least one uppercase letter, one lowercase letter, one number and eight characters.');
	   return false;
  }
}
</script>

<style>
.btn {
	background-color:#218FD6;
    color: white;
}

.btn:hover {
   background-color:#3370d6;
   color: white;
}

.google-signin-wrap {
	display: flex;
	flex-direction: column;
	align-items: center;
}
.google-divider {
	display: flex;
	align-items: center;
	width: 220px;
	max-width: 80vw;
	color: #888;
	font-size: 12px;
	margin: 6px 0 12px;
}
.google-divider::before,
.google-divider::after {
	content: "";
	flex: 1;
	height: 1px;
	background: #d0d0d0;
}
.google-divider span {
	padding: 0 10px;
}
.g_id_signin {
	display: inline-block;
}
.google-native-btn {
	background-color: #fff;
	color: #3c4043;
	border: 1px solid #dadce0;
	border-radius: 20px;
	padding: 8px 22px;
	font-size: 14px;
	font-weight: 500;
}
.google-native-btn:hover {
	background-color: #f7f8f8;
	color: #3c4043;
}
</style>