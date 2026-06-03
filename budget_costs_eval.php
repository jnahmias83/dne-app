<?php
include 'include/header.php';
include 'functions/functions.php';

$project_id = @$_GET['project_id'];
$lang_screen = @$_GET['lang_screen'];

$query = $mysqli->prepare("SELECT * FROM dne_projects WHERE id = ?");
$query->bind_param("i",$project_id );
$query->execute();
$query->store_result();
$project = fetch_unique($query);

$query = $mysqli->prepare("SELECT bce.id AS id,bce.name AS name,bce.description AS description,
                          bce.pdf_evaluation AS pdf_evaluation,bce.evaluation_cost AS evaluation_cost,
                          bce.evaluation_date AS evaluation_date,sfow.name AS sfow_name
                          FROM dne_budget_costs_eval bce
						  LEFT JOIN dne_sup_field_of_work sfow ON bce.id_field_of_work = sfow.id
						  WHERE id_project = ?
						  ORDER BY evaluation_date");
$query->bind_param("i",$project_id);
$query->execute(); 
$query->store_result();
$budget_costs_eval_num_rows = $query->num_rows;
$budget_costs_eval = fetch($query);

include 'menu_budget_reports.php';
?>

        <form method="post" action="" class="form-inline">	
			<div class="container">
			    <div class="row alignCenter marginTop25">
					<div class="col-md-12">
						<a href="projects.php"><img src="images/davidnahmias_logo.png" width="170px" height="170px" /></a>
					</div>
			    </div>
			    <div class="row marginTop15 title">	
					<div class="col-md-12">
						<a id="a_project_title" class="text-decoration-none color-1A5276" href="budget.php?project_id=<?=@$project_id?>&lang_screen=<?=@$lang_screen?>">
						    <strong class="fontSize33 line-height-1em"><?=@$project->nickname?></strong>
							<div class="fontSize26 font-weight-bold font-family-david cursor-pointer">פרוייקט <?=@$project->name_he?></div>
					    </a>
                        <div class='fontSize26 font-weight-bold font-family-david cursor-pointer'>דו''ח אומדן תקציבי</div>					
					</div>				
			    </div>			
				<div class="margin-top-10-x-auto d-flex justify-content-center dir-rtl">
					<div id="div_btns_eval" class="bgColor-cbddec padding10 borderRadius10 d-flex flex-wrap justify-content-center gap-3">
						<a class="border-black borderRadius10 padding-4x-4y d-flex flex-column align-items-center justify-content-center text-decoration-none" onclick="location.href='add_cost_eval.php?id=0&project_id=<?=@$project_id?>&lang_screen=<?=@$lang_screen?>'">
						  <i class='fa fa-plus'></i>
						  <div>אומדן תקציבי</div>
						</a>
						<a class="text-decoration-none d-flex flex-column align-items-center" href="budget_costs_eval_report.php?project_id=<?=@$project_id?>" target="_blank">
						  <img src="images/file-pdf-solid.svg" width="70" height="40" /><strong class="font-family-david">דו''ח</strong>
						</a>
					</div>
				</div>

			    <?php if($budget_costs_eval_num_rows > 0) { ?>		
				<div class="row fontSize14 marginTop15 alignCenter">
					<div align="center" class="col-md-12 mx-2">
						<table border="1">						
							<tr class="bgColorSilver height50">
								<th class="alignCenter" width="30px;">&#x2116;</th>
								<th class="alignCenter" width="180px;">Name</th>
								<th class="alignCenter" width="120px">Domain</th>
								<th class="alignCenter" width="250px;">Description</th>
								<th class="alignCenter" width="120px;">Evaluation Cost</th>
								<th class="alignCenter" width="90px;">Evaluation <br/> Date</th>
								<th class="alignCenter" width="40px;">&nbsp;</th>
								<th class="alignCenter" width="40px;">&nbsp;</th>
							</tr>
		
							<?php
							$count = 0;
							foreach($budget_costs_eval as $item) {
								$count++;
								
								$evaluation_date = '';
								if(@$item->evaluation_date != '0000-00-00')
									$evaluation_date = substr(@$item->evaluation_date,8,2).'/'.substr(@$item->evaluation_date,5,2).'/'.substr(@$item->evaluation_date,2,2);
								
								$evaluation_cost_display = '';
								if(@$item->evaluation_cost > 0) {
								    $evaluation_cost_display = isset($item->evaluation_cost)
									? ((floor($item->evaluation_cost) == $item->evaluation_cost)
									?number_format($item->evaluation_cost,0,'.',',').'&#8362;'
									:number_format($item->evaluation_cost,2,'.',',').'&#8362;')
									:'';
								}
								?>
								<tr height="60px;">
									<td class="alignCenter"><a href="add_cost_eval.php?id=<?=@$item->id?>&project_id=<?=@$project_id?>&lang_screen=<?=@$lang_screen?>"><?=@$count?></a></td>
									<td class="alignLeft paddingLeft5 paddingLeft5"><?=@$item->name?></td>
									<td class="alignLeft paddingLeft5"><?=@$item->sfow_name?></td>
									<td class="alignLeft paddingLeft5"><?=@$item->description?></td>											
									<td class="alignLeft paddingLeft5"><?php if(@$item->pdf_evaluation != '') { ?><a href="uploads/<?=@$item->pdf_evaluation?>" title="View PDF" target="_blank"><?=@$evaluation_cost_display?></a><?php } else echo @$evaluation_cost_display?></td>
									<td class="alignLeft paddingLeft5"><?=@$evaluation_date?></td>
									<td class="alignCenter"><img src="images/edit-button.svg" class="padding10 cursor-pointer" title="Edite" onclick="location.href='add_cost_eval.php?id=<?=@$item->id?>&project_id=<?=@$project_id?>&lang_screen=<?=@$lang_screen?>'" /></td>									
									<td class="alignCenter"><img src="images/delete.svg" class="cursor-pointer" title="מחק" onclick="return removeCostEval(<?=@$item->id?>);" /></td>
								</tr>
								<?php
							}
							?>
						</table>		
					</div>
				</div>
			<?php } ?>
			</div>
		</form> 
	</body>
</html>

<script>
function removeCostEval(id) {
	if(confirm("Are you sure to remove this cost eval?")) {
        var form_data = new FormData();	
		form_data.append('id',id);			
		$.ajax({
			type: 'POST',
			url: 'cost_eval_delete.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,			
			success: function(data){
				location.reload(true);			
			},
		});		
    }
    return false;
}
</script>

<style>
.btn {
   color: white;
   background-color: #218FD6;
}

.btn:hover {
   color: white;
   background-color: #3370d6;
}

#a_project_title:hover {
	color: grey;
}

#div_btns_eval {
	max-width: 280px;
    width: 100%;
}

.gap-3 {
	gap: 1rem; 
}
</style>