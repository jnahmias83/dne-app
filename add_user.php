<?php
include 'include/header.php';
include 'functions/functions.php';

$query = $mysqli->prepare("SELECT * FROM dne_logos LIMIT 1");
$query->execute();
$query->store_result();
$logo = fetch_unique($query);

$id = @$_GET['id'];

$query = $mysqli->prepare("SELECT * FROM dne_users WHERE id = ?");
$query->bind_param("i",$id);
$query->execute();
$query->store_result();
$user = fetch_unique($query);  

$dir = 'dir-rtl';
?>
		<form method="post" action="" enctype="multipart/form-data" class="form-inline">
			<input type="hidden" id="id" value="<?=@$id?>" />

			<div class="container">
			    <br/>
			
				<div class="row alignCenter marginTop10">
					<div class="col-12">
						<a href="projects.php"><img src="images/<?=@$logo->logo?>" width="120" height="114" /></a>
					</div>
			    </div>
				
			    <?php
				if($id == 0) { ?>
					<div class="row marginTop10 title <?=@$dir?> fontSize20">
						<div class="col-12">
							הוספת משתמש
						</div>
					</div>
				<?php }
				else if($id > 0) { ?>
				   <div class="row title marginTop10 <?=@$dir?> fontSize20">
						<div class="col-12">
							<?=@$user->lastname.' '.@$user->firstname?>
						</div>
					</div>
				<?php } ?>	
				
				<div class="row <?=@$dir?> marginTop20">
				   <div class="col-4">
				       <strong>שם משפחה:</strong>
						<br/>	
						<input type="text" class="width250" name="lastname" id="lastname" placeholder="*שם משפחה" value="<?=@$user->lastname?>" />	
                   </div>
                   <div class="col-4">
				        <strong>שם פרטי:</strong>
						<br/>	
						<input type="text" class="width250" name="firstname" id="firstname" placeholder="*שם פרטי" value="<?=@$user->firstname?>" />	
                   </div>
                   <div class="col-4">
				     
						<strong>כינוי:</strong>
						<br/>	
						<input type="text" class="width250" name="nickname" id="nickname" placeholder="*כינוי" value="<?=@$user->nickname?>" />	 			   
				    </div>
				</div>
				
				<div class="row <?=@$dir?> marginTop20">
					<div class="col-4">	
						<strong>דוא''ל:</strong>
						<br/>	
						<input type="email" class="width250" name="email" id="email" placeholder="*דוא''ל" value="<?=@$user->email?>" />	
					</div>
					<div class="col-4">
					    <strong>שם משתמש:</strong>
						<br/>	
						<input type="text" class="width250" name="username" id="username" placeholder="*שם משתמש" value="<?=@$user->username?>" />		
					</div>
					<div class="col-4">
					    <strong>סיסמה:</strong>
						<br/>	
						<input type="password" class="width250" name="password" id="password" placeholder="*סיסמה" value="<?=@$user->password?>" />		
					</div>
				</div>
				
				<div class="row <?=@$dir?> marginTop20">
					<div class="col-12">
					    <strong>הרשאות</strong>
						<br/>	
						<input type="text" class="width250" name="privileges" id="privileges" placeholder="הרשאות" value="<?=@$user->privileges?>" />		
					</div>
				</div>
						
				<div class="row <?=@$dir?> marginTop10">
					<div class="col-4"></div>
					<div class="col-4 alignCenter">
						<div id="div_message_alert_down"></div>	
						<input type="button" id="save_btn" name="save_btn" class="btn marginTop5 bgColorBlue colorWhite mb-2" value="שמור" />		
						<input type="button" id="cancel_btn" class="btn marginRight10 marginTop5 bgColorBlack colorWhite mb-2" value="ביטול" />				
					</div>
					<div class="col-4"></div>
				</div>				
            </div>			
		</form>

        <div id="progress-popup">
            <p>Loading in progress...</p>
            <div id="div-progress-bar">
                <div id="progress-bar"></div>
            </div>
        </div>		
    </body>
</html>

<script>
$('#save_btn').click (function (e){ 
  let upperCase = new RegExp('[A-Z]');
  let lowerCase = new RegExp('[a-z]');
  let digit = new RegExp('[0-9]');

  if($('#password').val().match(upperCase) && $('#password').val().match(lowerCase) && $('#password').val().match(digit) && $('#password').val().length>=8) {
    let form_data = new FormData();	
	form_data.append('id',$('#id').val());
	form_data.append('lastname',$('#lastname').val());
	form_data.append('firstname',$('#firstname').val());
	form_data.append('nickname',$('#nickname').val());
	form_data.append('email',$('#email').val());
	form_data.append('username',$('#username').val());
	form_data.append('password',$('#password').val());
	form_data.append('privileges',$('#privileges').val());
	$.ajax({
		type: 'POST',
		url: 'user_insert.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,	
        beforeSend: function() {
			$("#progress-popup").show();
			let progress = 0;
			let interval = setInterval(function() {
				if (progress < 90) {
					progress += 10;
					$("#progress-bar").css("width", progress + "%");
				} else {
					clearInterval(interval);
				}
			}, 200);
			$("#progress-popup").data("interval", interval);
		},		
		success: function(data){ 
			if(data == 'empty')	{		
				if($('#lastname').val().length == 0)			
					$('#lastname').css('border-color','red');
				else if(!($('#lastname').val().length == 0))
					$('#lastname').css('border-color','initial');

                if($('#firstname').val().length == 0)			
					$('#firstname').css('border-color','red');
				else if(!($('#firstname').val().length == 0))
					$('#firstname').css('border-color','initial');
                
				if($('#nickname').val().length == 0)			
					$('#nickname').css('border-color','red');
				else if(!($('#nickname').val().length == 0))
					$('#nickname').css('border-color','initial');

				if($('#email').val().length == 0)			
					$('#email').css('border-color','red');
				else if(!($('#email').val().length == 0))
					$('#email').css('border-color','initial');

                if($('#username').val().length == 0)			
					$('#username').css('border-color','red');
				else if(!($('#username').val().length == 0))
					$('#username').css('border-color','initial');

                if($('#password').val().length == 0)			
					$('#password').css('border-color','red');
				else if(!($('#password').val().length == 0))
					$('#password').css('border-color','initial');				
				
				$('#div_message_alert_down').html("<span style=color:red;font-size:13px;>Please fill all mandatory fields</span>");
			}
			else if(data == 'exists')
				$('#div_message_alert_down').html("<span style=color:red;font-size:13px;>This user already exists on this system</span>");
			else
				location.href = 'users.php';		
		}
	});	 
  }
  else
     $('#div_message_alert_down').html("<span style=color:red;font-size:13px;>Your password must contain at least one uppercase letter, one lowercase letter, one number and eight characters</span>");	  
})

$('#cancel_btn').click(function(){
    location.href = 'users.php';
})
</script>

<style>
.btn:hover {
   color: white;
}

.bgColorBlack:hover {
	background-color: #45484d;
}

.bgColorBlue:hover {
	background-color:#3370d6;
}
</style>