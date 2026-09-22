<?php if(!empty($project_id)) $_SESSION['id_project'] = $project_id; ?>
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
  <ul class="menu-list-preview" id="tasks_menu_list_preview">
    <li><a class="font-weight-bold" href="custom_reports.php?project_id=<?=@$_SESSION['id_project']?>&task_filter=<?=@$task_filter?>&progress_status_filter=<?=@$progress_status_filter?>&supplier_filter=<?=@$_GET['supplier_filter']?>&period_new_task_filter=<?=@$period_new_task_filter?>&period_late_filter=<?=@$period_late_filter?>">הדוח''ות שלי</a></li>
	<li class="separator">|</li>
	<li><a class="font-weight-bold" href="responsibles.php?project_id=<?=@$_SESSION['id_project']?>&task_filter=<?=@$task_filter?>&progress_status_filter=<?=@$progress_status_filter?>&supplier_filter=<?=@$_GET['supplier_filter']?>&period_new_task_filter=<?=@$period_new_task_filter?>&period_late_filter=<?=@$period_late_filter?>&is_specific_filter=<?=@$is_specific_filter?>">צוות הפרוייקט</a></li>
	<li class="separator">|</li>
	<li><a class="font-weight-bold" href="chapters.php?project_id=<?=@$_SESSION['id_project']?>&task_filter=<?=@$task_filter?>&progress_status_filter=<?=@$progress_status_filter?>&supplier_filter=<?=@$_GET['supplier_filter']?>&period_new_task_filter=<?=@$period_new_task_filter?>&period_late_filter=<?=@$period_late_filter?>&is_specific_filter=<?=@$is_specific_filter?>">פרקים</a></li>
  </ul>
  <div class="topbar-actions">
	  <a href="add_sup_to_proj.php?id=<?=@$_SESSION['id_project']?>" class="btn-attach-suppliers">
		ספקים
	  </a>
	  <a href="budget.php?project_id=<?=@$_SESSION['id_project']?>&lang_screen=HE" class="btn-budget">
		<i class="fa-solid fa-dollar-sign"></i>תקציב
		<i class="fa-solid fa-share budget-btn-arrow"></i>
	  </a>
  </div>
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
    margin-left: 8px;
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

.menu-list-preview {
    display: none;
    list-style: none;
    margin: 0;
    padding: 0;
    align-items: center;
    gap: 10px;
    color: white;
    flex-wrap: wrap;
}

.menu-list-preview li a {
    color: white;
    text-decoration: none;
    white-space: nowrap;
}

.menu-list-preview li.separator {
    color: white;
    user-select: none;
}

.menu-list-preview a {
    font-size: 14px;
}

.topbar-preview .menu-list-preview {
    display: flex;
}

.topbar-preview .menu-list:not(.open) {
    display: none;
}

.topbar-preview .hamburger-btn {
    display: inline-block;
}

.topbar-preview .menu-list.open {
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

.topbar-preview .menu-list.open li.separator {
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

.topbar-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    padding-left: 8px;
    margin-right: auto;
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
    margin-left: 0;
    font-size: 14px;
    flex-shrink: 0;
    cursor: pointer;
}

.btn-attach-suppliers {
	background-color: #706f6c;
}

.btn-budget i {
    margin-left: 10px;
}

.budget-btn-arrow {
    color: #fff;
    font-weight: bold;
    margin-right: 8px;
    transform: scaleX(-1);
    display: inline-block;
}

.btn-budget:hover,
.btn-attach-suppliers:hover {
    color: rgba(255,255,255,0.55);
}

.menu-list a {
    font-size: 14px;
}

@media (max-width: 600px) {
    .menu-list a,
    .menu-list-preview a {
        font-size: 12px;
    }

	.btn-budget,
    .btn-attach-suppliers {
        padding: 5px 5px;
        margin-left: 5px;
    }
}
</style>

<script>
function isTasksTopbarSingleLine(topbar){
	let children = Array.from(topbar.children).filter(function(el){
		return getComputedStyle(el).display !== 'none';
	});
	if(children.length === 0) return true;
	let firstTop = children[0].offsetTop;
	return children.every(function(el){
		return Math.abs(el.offsetTop - firstTop) < 5;
	});
}

function checkTasksMenuOverflow(){
	let topbar = document.getElementById('tasks_topbar');
	let menuList = document.getElementById('tasks_menu_list');
	let previewList = document.getElementById('tasks_menu_list_preview');
	if(!topbar || !menuList || !previewList) return;

	topbar.classList.remove('topbar-collapsed', 'topbar-preview');

	// Etape 1 : est-ce que les 6 items + les boutons a droite tiennent reellement sur une ligne ?
	let fullFits = isTasksTopbarSingleLine(topbar);
	if(fullFits){
		menuList.classList.remove('open');
		return;
	}

	// Etape 2 : est-ce que les 3 premiers items + hamburger + les boutons a droite
	// tiennent sur une ligne ?
	topbar.classList.add('topbar-preview');
	let previewFits = isTasksTopbarSingleLine(topbar);
	if(previewFits){
		return;
	}

	// Etape 3 : meme les 3 premiers ne rentrent pas -> hamburger seul
	topbar.classList.remove('topbar-preview');
	topbar.classList.add('topbar-collapsed');
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
