<?php
include 'include/header.php';
include 'functions/functions.php';

$zero = 0.0;
$query = $mysqli->prepare("SELECT p.nickname AS p_nickame,s.name_he AS s_name_he,a.id AS account_id,a.submitted_account AS submitted_account,
                          a.pdf_submission AS pdf_submission
                          FROM dne_accounts a
						  LEFT JOIN dne_projects_suppliers ps ON a.id_projects_suppliers = ps.id
						  LEFT JOIN dne_projects p ON ps.id_project = p.id
						  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
						  WHERE a.submitted_account > ? AND a.approved_amount = ?");
$query->bind_param("dd",$zero,$zero);
$query->execute(); 
$query->store_result();
$not_approved_accounts_num_rows = $query->num_rows; 
$not_approved_accounts = fetch($query);
?>

        <form method="post" action="" class="form-inline">		
		    <div class="container">
			   <div class="row alignCenter marginTop25">
					<div class="col-md-12">
						<a href="projects.php"><img src="images/davidnahmias_logo.png" width="170px" height="170px" /></a>
					</div>
			    </div>
			    
				<div class="row alignCenter marginTop25 fontSize22 colorBlue dir-rtl">
					<div class="col-md-12">
						<a href="global_settings.php">רשימת החשבונות הלא מאושרים</a>
					</div>
			    </div>
			
			    <div class="row alignCenter dir-rtl">
					<div class="col-md-12">
						<input type="button" value="חזור להגדרות כלליות" class="btn btn-primary marginTop20 alignCenter mb-2" onclick="location.href='global_settings.php'" />
					</div>
			    </div>
				
				<?php if($not_approved_accounts_num_rows > 0) { ?>
					<div class="row fontSize14 dir-rtl">
						<div class="col-md-12 mx-2">
							<table align="center" id="not_approved_accounts_list" class="marginTop15" border="1">
								<tr class="bgColorSilver height50">
									<th width="30px;" class="alignCenter">&#x2116;</th>
									<th width="100px;" class="alignCenter">שם פרוייקט</th>
									<th width="170px;" class="alignCenter">שם ספק</th>
									<th width="110px;" class="alignCenter">חשבון שהוגש</th>
									<th width="80px" class="alignCenter">&nbsp;</th>
								</tr>
								
								<?php
								$count = 0;
								foreach($not_approved_accounts as $item) {
									$count++;
									
									$submitted_account = '';
									if(@$item->submitted_account != 0.00)
									   $submitted_account = number_format(@$item->submitted_account,2,'.',',').'&nbsp;&#8362;';
									?>
									<tr class="height30">
										<td class="alignCenter"><?=@$count?></td>
										<td class="alignCenter"><?=@$item->p_nickame?></td>			
										<td class="alignRight paddingRight5"><?=@$item->s_name_he?></td>							
										<td class="alignRight paddingRight5"><?php if(@$item->pdf_submission != '') { ?><a href="uploads/<?=@$item->pdf_submission?>" title="View PDF" target="_blank"><?=@$submitted_account?></a><?php } else { echo @$submitted_account; }?></td>	
									    <td class="alignCenter"><a href="add_account.php?id=<?=@$item->account_id?>&from=not_app_acts">ראה חשבון</a></td>
									</tr>
									<?php
								}
								?>
							</table>		
						</div>
					</div>
					<div class="row alignCenter marginTop20 dir-rtl">
						<div class="col-md-12">
							<a href="not_approved_accounts_report.php" target="_blank">
								<input type="button" value="יצירת PDF" class="btn btn-primary mb-2" />
							</a>
						</div>
					</div>				
				<?php } ?>
			</div>
		</form> 
	</body>
</html>

<style>
.alignCenter {
	text-align: center;
}

.alignRight {
	text-align: right;
}

.dir-rtl {
	direction: rtl;
}

.marginTop15 {
	margin-top: 15px;
}

.marginTop20 {
	margin-top: 20px;
}

.marginTop25 {
	margin-top: 25px;
}

.marginTop25 {
	margin-top: 20px;
}

.paddingRight5 {
	padding-right: 5px;
}

.colorBlue {
	color:#0d6efd;
}

.bgColorSilver {
	background-color: silver;
}

.height30 {
	height: 30px;
}

.height50 {
	height: 50px;
}

.fontSize14 {
	font-size: 14px;
}

.fontSize22 {
	font-size: 22px;
}
</style>