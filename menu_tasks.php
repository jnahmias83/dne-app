<div class="topbar bgColorBrown alignCenter" dir="rtl">
  <ul class="menu-list">   
    <li><a class="font-weight-bold" href="custom_reports.php?project_id=<?=@$_SESSION['id_project']?>&task_filter=<?=@$task_filter?>&progress_status_filter=<?=@$progress_status_filter?>&supplier_filter=<?=@$_GET['supplier_filter']?>&period_new_task_filter=<?=@$period_new_task_filter?>&period_late_filter=<?=@$period_late_filter?>">הדוח''ות שלי</a></li>
	<li class="separator">|</li>
	<li><a class="font-weight-bold" href="responsibles.php?project_id=<?=@$_SESSION['id_project']?>&task_filter=<?=@$task_filter?>&progress_status_filter=<?=@$progress_status_filter?>&supplier_filter=<?=@$_GET['supplier_filter']?>&period_new_task_filter=<?=@$period_new_task_filter?>&period_late_filter=<?=@$period_late_filter?>&is_specific_filter=<?=@$is_specific_filter?>">צוות הפרוייקט</a></li>
	<li class="separator">|</li>
	<li><a class="font-weight-bold" href="chapters.php?project_id=<?=@$_SESSION['id_project']?>&task_filter=<?=@$task_filter?>&progress_status_filter=<?=@$progress_status_filter?>&supplier_filter=<?=@$_GET['supplier_filter']?>&period_new_task_filter=<?=@$period_new_task_filter?>&period_late_filter=<?=@$period_late_filter?>&is_specific_filter=<?=@$is_specific_filter?>">פרקים</a></li>
	<li class="separator">|</li>
	<li><a href="add_project.php?id=<?=@$_SESSION['id_project']?>&task_filter=<?=@$task_filter?>&progress_status_filter=<?=@$progress_status_filter?>&supplier_filter=<?=@$_GET['supplier_filter']?>&period_new_task_filter=<?=@$period_new_task_filter?>&period_late_filter=<?=@$period_late_filter?>&is_specific_filter=<?=@$is_specific_filter?>&from=taskslist">נתוני הפרוייקט</a></li>
	<li class="separator">|</li>
	<li><a href="tasks.php?project_id=<?=@$_SESSION['id_project']?>&task_filter=<?=@$task_filter?>&progress_status_filter=<?=@$progress_status_filter?>&supplier_filter=<?=@$_GET['supplier_filter']?>&period_new_task_filter=<?=@$period_new_task_filter?>&period_late_filter=<?=@$period_late_filter?>&is_specific_filter=<?=@$is_specific_filter?>">סוגי משימות</a></li>
	<li class="separator">|</li>
	<li><a href="progress_status.php?project_id=<?=@$_SESSION['id_project']?>&task_filter=<?=@$task_filter?>&progress_status_filter=<?=@$progress_status_filter?>&supplier_filter=<?=@$_GET['supplier_filter']?>&period_new_task_filter=<?=@$period_new_task_filter?>&period_late_filter=<?=@$period_late_filter?>&is_specific_filter=<?=@$is_specific_filter?>">סטטוסים</a></li>
  </ul>
  <a href="add_sup_to_proj.php?id=<?=@$_SESSION['id_project']?>" class="btn-attach-suppliers">
	ספקים
  </a>
  <a href="budget.php?project_id=<?=@$_SESSION['id_project']?>&lang_screen=HE" class="btn-budget">
	<i class="fa-solid fa-dollar-sign"></i>תקציב
  </a>
</div>

<style>
.topbar {
    height: 60px;
    display: flex;
    align-items: center;
    padding: 0 20px;
    direction: rtl;
    background-color: #4d7380;
    color: white;
    flex-wrap: wrap;
}

.menu-list {
    list-style: none;
    display: flex;
    margin: 0;
    padding: 0;
    align-items: center;
    flex-grow: 1;
    justify-content: center;
    gap: 10px;
    color: white;
    flex-wrap: wrap;
}

.menu-list li a {
    color: white;
    text-decoration: none;
    white-space: nowrap;
}

.menu-list li.separator {
    color: white;
    user-select: none;
}

.btn-budget,
.btn-attach-suppliers {
    background-color: #6b804d;
    color: #fff;
    padding: 5px 15px;
    border-radius: 10px;
	border: 1px solid white; 
    display: inline-flex;
    align-items: center;
    white-space: nowrap;
    text-decoration: none;
    margin-left: 15px;
    flex-shrink: 0;
    cursor: pointer;
}

.btn-attach-suppliers {
	background-color: #706f6c;
}

.btn-budget i {
    margin-left: 10px;
}

@media (max-width: 1024px) {
    .menu-list a {
        font-size: 11px;
    }
}

@media (max-width: 850px) {
    .menu-list a {
        font-size: 10px;
    }
}

@media (max-width: 768px) {
    .menu-list a {
        font-size: 9px;
    }
	
	.btn-budget,
    .btn-attach-suppliers {
        padding: 5px 7px;
        margin-left: 5px;
		font-size: 10px;
    }
}

@media (max-width: 620px) {
	.menu-list a {
        font-size: 8px;
    }
	
	.btn-budget,
    .btn-attach-suppliers {
        padding: 5px 5px;
        margin-left: 5px;
		font-size: 7px;
    }
}
</style>
