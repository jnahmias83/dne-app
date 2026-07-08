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
		$query = $mysqli->prepare("SELECT * FROM dne_projects WHERE is_project_active = ?
		                          ORDER BY nickname");
		$query->bind_param("i",$is_project_active);
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

						<!-- Version: <?=trim(@file_get_contents(__DIR__.'/version.txt'))?> -->
					</div>
					<div class="col-md-4"></div>
				</div>
			</div>
		</form>
	</body>	
</html>

<script>
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
</style>