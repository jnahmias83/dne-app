<?php
$lang = 'HE';

if(@$_SESSION['lang'] != null)
	$lang = @$_SESSION['lang'];
?>

<div class="topbar bgColorBrown alignCenter">
  <ul class="menu-list" dir="rtl">
    <li><a href="budget.php?project_id=<?=@$_SESSION['id_project']?>&lang_screen=<?=@$lang?>">תקציב</a></li>
    <li class="separator">|</li>
    <li><a href="orders.php?project_id=<?=@$_SESSION['id_project']?>&lang_screen=<?=@$lang?>">הזמנות</a></li>
    <li class="separator">|</li>
    <li><a href="accounts.php?project_id=<?=@$_SESSION['id_project']?>&lang_screen=<?=@$lang?>">חשבונות</a></li>
    <li class="separator">|</li>
    <li><a href="payments.php?project_id=<?=@$_SESSION['id_project']?>&lang_screen=<?=@$lang?>">תשלומים</a></li>
    <li class="separator">|</li>
    <li><a href="payments_wires.php?project_id=<?=@$_SESSION['id_project']?>&lang_screen=<?=@$lang?>">תשלומים לביצוע</a></li>
    <li class="separator">|</li>
    <li><a href="payments_missing_invoice.php?project_id=<?=@$_SESSION['id_project']?>&lang_screen=<?=@$lang?>">דו"ח חשבוניות חסרות</a></li>
    <li class="separator">|</li>
    <li><a href="budget_costs_eval.php?project_id=<?=@$_SESSION['id_project']?>&lang_screen=<?=@$lang?>">אומדן תקציבי</a></li>
  </ul>
  <a href="add_sup_to_proj.php?id=<?=@$_SESSION['id_project']?>" class="btn-attach-suppliers">
	ספקים
  </a>
  <a href="meetings.php?project_id=<?=@$_SESSION['id_project']?>" class="btn-tasks">
	<i class="fa-solid fa-list-check"></i>משימות
  </a>
</div>

<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

<style>
.topbar {
    height: 60px;
    display: flex;
    align-items: center;
    padding: 0 20px;
    direction: rtl;
}
  
.btn-tasks,
.btn-attach-suppliers {
    background-color: #4d7380;
    color: #fff;
    padding: 5px 15px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    white-space: nowrap;
    text-decoration: none;
    margin-left: 15px;
    flex-shrink: 0;
}

.btn-attach-suppliers {
	background-color: #706f6c;
}
  
.btn-tasks i {
    margin-left: 10px;
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
    color: #fff;
}
  
.menu-list li a {
    color: #fff;
    text-decoration: none;
}
  
.menu-list li.separator {
    color: #fff;
    user-select: none;
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
	
	.btn-tasks,
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
	
	.btn-tasks,
    .btn-attach-suppliers {
        padding: 5px 5px;
        margin-left: 5px;
		font-size: 7px;
    }
}
</style>