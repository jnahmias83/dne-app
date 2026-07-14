<div class="topbar bgColorBrown alignCenter" dir="rtl" id="tasks_topbar">
  <button type="button" id="tasks_hamburger_btn" class="hamburger-btn" aria-label="menu">&#9776;</button>
  <ul class="menu-list" id="tasks_menu_list">
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
    position: relative;
}

.hamburger-btn {
    display: none;
    background: none;
    border: none;
    color: white;
    font-size: 26px;
    line-height: 1;
    cursor: pointer;
    padding: 0 10px;
}

.topbar.measuring {
    flex-wrap: nowrap;
}

.menu-list.measuring {
    flex-wrap: nowrap;
}

.topbar-collapsed .menu-list {
    display: none;
}

.topbar-collapsed .hamburger-btn {
    display: inline-block;
}

.topbar-collapsed .menu-list.open {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    position: absolute;
    top: 60px;
    right: 20px;
    background-color: #4d7380;
    z-index: 1000;
    padding: 10px 15px;
    border-radius: 0 0 10px 10px;
    gap: 8px;
    flex-wrap: nowrap;
}

.topbar-collapsed .menu-list.open li.separator {
    display: none;
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

.menu-list a {
    font-size: 14px;
}

@media (max-width: 600px) {
    .menu-list a {
        font-size: 12px;
    }

	.btn-budget,
    .btn-attach-suppliers {
        padding: 5px 5px;
        margin-left: 5px;
		font-size: 10px;
    }
}
</style>

<script>
function checkTasksMenuOverflow(){
	let topbar = document.getElementById('tasks_topbar');
	let menuList = document.getElementById('tasks_menu_list');
	if(!topbar || !menuList) return;

	topbar.classList.remove('topbar-collapsed');
	let isWrapped = topbar.scrollHeight > 64;

	if(isWrapped){
		topbar.classList.add('topbar-collapsed');
	}
	else {
		menuList.classList.remove('open');
	}
}

window.addEventListener('load', checkTasksMenuOverflow);
window.addEventListener('resize', checkTasksMenuOverflow);
document.addEventListener('DOMContentLoaded', checkTasksMenuOverflow);
setInterval(checkTasksMenuOverflow, 500);

document.addEventListener('click', function(e){
	let hamburger = document.getElementById('tasks_hamburger_btn');
	let menuList = document.getElementById('tasks_menu_list');
	if(!hamburger || !menuList) return;

	if(e.target === hamburger || hamburger.contains(e.target)){
		menuList.classList.toggle('open');
	}
	else if(!menuList.contains(e.target)){
		menuList.classList.remove('open');
	}
});
</script>
