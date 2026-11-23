<style id="maquette-types">
/* couleurs (צבע רקע / צבע גופן) dans les tableaux : pastille ronde (meme regle que maquette_design.inc) */
table input[type="color"] { -webkit-appearance:none; appearance:none; width:30px !important; height:30px !important; min-width:30px; padding:0 !important; margin:2px auto !important; display:block;
  border:1.5px solid #c9d7e3 !important; border-radius:50% !important; background:none !important; cursor:pointer; overflow:hidden; box-sizing:border-box; }
table input[type="color"]::-webkit-color-swatch-wrapper { padding:0; }
table input[type="color"]::-webkit-color-swatch { border:none; border-radius:50%; }
table input[type="color"]::-moz-color-swatch { border:none; border-radius:50%; }
/* ===== MAQUETTE - tasks.php (סוגי משימות) - aucun fichier du site modifie - meme logique que la maquette responsables ===== */
html, body { background:#f6f5f1 !important; }

/* bouton "הוסף סוג משימה" : sans fond, texte et bord bleus, survol bleu clair ; au-dessus de la carte */
.mq-btns { display:flex; justify-content:center; gap:12px; flex-wrap:wrap; margin-top:24px; }
.mq-btn { display:inline-flex; align-items:center; gap:8px; padding:7px 18px; border-radius:999px; font-weight:700; font-size:15px;
  cursor:pointer; transition:background .15s; text-decoration:none !important; flex-direction:row-reverse; }
.mq-btn.add { background:transparent; color:#1a5276 !important; border:1.5px solid #1a5276; }
.mq-btn.add:hover, .mq-btn.small:hover { background:#e3eef6; }
.mq-btn i { font-size:14px; }
/* bouton du bas "Default" : meme style, plus petit (comme "ברירת מחדל גופנים ורקעים" des responsables) */
.mq-btns.bottom { margin-top:18px; margin-bottom:24px; }
.mq-btn.small { background:transparent; color:#1a5276 !important; border:1.5px solid #1a5276; padding:4px 10px; font-weight:600; font-size:13px; }

/* bouton "תקציב" du menu : moins large */
.btn-budget { padding:5px 10px !important; }
.btn-budget i { margin-left:6px !important; }
.btn-budget .budget-btn-arrow { margin-right:4px !important; margin-left:0 !important; }
/* "רשימת המשימות" dans le menu : icone blanche comme les autres */
.mq-menu-icon { color:#fff; font-size:17px; width:20px; text-align:center; }

/* carte comme dans add_meeting.php ; en-tete = titre avec l'icone du menu */
.mq-card { width:fit-content; max-width:100%; margin:16px auto 0; border:1px solid #C5B8AA; border-radius:15px; background:#f2f6f9; overflow:hidden; }
.mq-page-title { display:flex; align-items:center; justify-content:center; gap:10px; background:#1a5276; color:#fff; font-weight:700; font-size:20px;
  padding:8px 28px; direction:rtl; }
.mq-card-body { padding:14px 12px; overflow-x:auto; }

/* tableau dans la carte : sans bordure, en-tete #dedede texte bleu */
#tasks_list { border-collapse:separate !important; border-spacing:0; border:none !important; background:#fff; margin:0 auto; }
#tasks_list th, #tasks_list td { border:none !important; border-bottom:1px solid #eef0f3 !important; }
#tasks_list tr:last-child td { border-bottom:none !important; }
#tasks_list tr.bgColorSilver, #tasks_list tr.bgColorSilver th { background:#dedede !important; }
#tasks_list th { color:#1a5276 !important; font-weight:700; font-size:14px; height:44px; padding:4px 8px !important; }
#tasks_list tr.height35 td { height:44px; font-size:14.5px; color:#2d3a45; padding:0 8px !important; }
/* une ligne sur deux, en commencant par la ligne foncee (l'en-tete est hors du tbody) */
#tasks_tbody tr.height35:nth-child(odd) td { background:#a9c6e0 !important; }
#tasks_tbody tr.height35:nth-child(even) td { background:#d4e4f2 !important; }
#tasks_list tr.height35 td:nth-child(3), #tasks_list tr.height35 td:nth-child(4) { color:#1a5276 !important; font-weight:600; }
/* poignee + case a cocher : colonnes etroites */
#tasks_list tr.height35 > td:nth-child(1), #tasks_list tr.height35 > td:nth-child(2) { width:30px !important; padding:0 4px !important; }
#tasks_list .task-drag-handle { color:#1a5276; }
/* pastilles de couleur : petits ronds au lieu des grands rectangles */
#tasks_list input[type="color"] { width:26px !important; height:26px; padding:0; border:1px solid #999; border-radius:50%; background:none; cursor:default; }
#tasks_list input[type="color"]::-webkit-color-swatch-wrapper { padding:0; }
#tasks_list input[type="color"]::-webkit-color-swatch { border:none; border-radius:50%; }
#tasks_list tr.height35 td:has(input[type="color"]) { text-align:center !important; }
/* icones modifier / supprimer */
#tasks_list td img { width:18px; height:18px; padding:6px; box-sizing:content-box; border-radius:50%; opacity:.75; transition:background .15s, opacity .15s; }
#tasks_list td img:hover { opacity:1; background:#e7eef5; }
#tasks_list td img[src*="delete"]:hover { background:#fde6e6; }

/* type decoche (n'apparait pas dans la liste) : le fond garde son bleu, le contenu est estompe ; la case reste nette */
#tasks_list tr.height35:has(input[type="checkbox"]:not(:checked)) td:nth-child(n+3) { color:#8a96a3 !important; font-weight:normal !important; }
#tasks_list tr.height35:has(input[type="checkbox"]:not(:checked)) td:nth-child(n+3) > * { opacity:.4; }
</style>
<script>
(function(){
  // MAQUETTE SEULEMENT : rien n'est enregistre (case, ordre, Default, suppression) ; la case ne change que l'affichage
  // (pose au chargement complet : les vraies fonctions sont declarees plus bas dans la page)
  window.addEventListener('load', function(){
    window.removeTask = function(){ return false; };
    window.FillGlobalTasks = function(){};
    window.setIsAppearsTasksList = function(){};
    if(window.jQuery){
      var realAjax = jQuery.ajax;
      jQuery.ajax = function(opts){
        if(/reorder_tasks\.php|fill_global_tasks\.php|set_is_appears_tasks_list\.php|task_delete\.php/.test((opts && opts.url) || '')) return jQuery.Deferred().promise();
        return realAjax.apply(this, arguments);
      };
    }
  });

  var back = document.getElementById('a_project_title');
  var tasksUrl = back ? back.getAttribute('href') : 'meetings.php';

  // ancien titre "רשימת סוגי משימות" sous le nom du projet : il passe dans l'en-tete de la carte
  var oldTtl = back ? back.parentElement.querySelector(':scope > div.fontSize26') : null;
  if(oldTtl) oldTtl.style.display = 'none';

  // anciens boutons : "חזור לרשימת המשימות" (retire, passe dans le menu), "+ סוג משימה", "Default"
  var add = document.querySelector('a.btn[onclick*="add_task.php?id=0"]');
  if(!add) return;
  var pid = ((add.getAttribute('onclick') || '').match(/project_id=(\d+)/) || [])[1] || '';
  var row = add.closest('.row');
  row.style.display = 'none';
  var br = row.nextElementSibling; if(br && br.tagName === 'BR') br.style.display = 'none';

  // bouton "הוסף סוג משימה" au-dessus de la carte
  var wrap = document.createElement('div'); wrap.className = 'mq-btns';
  var b1 = document.createElement('a'); b1.className = 'mq-btn add';
  b1.innerHTML = '<i class="fa fa-plus"></i><span>הוסף סוג משימה</span>';
  b1.onclick = function(){ location.href = 'add_task.php?id=0&project_id=' + pid; };   // meme action que le bouton actuel
  wrap.appendChild(b1);

  // bouton "Default" en bas, sous la carte (meme action : FillGlobalTasks)
  var wrapB = document.createElement('div'); wrapB.className = 'mq-btns bottom';
  var b2 = document.createElement('a'); b2.className = 'mq-btn small'; b2.textContent = 'Default';
  b2.onclick = function(){ if(window.FillGlobalTasks) window.FillGlobalTasks(); };
  wrapB.appendChild(b2);

  // carte : en-tete "סוגי משימות" + icone du menu, tableau dans le corps
  var table = document.getElementById('tasks_list');
  // les types systeme (ביצוע, ניהול...) n'ont pas de case "supprimer" : on complete par une case vide pour que le bleu aille jusqu'au bout
  if(table){
    var rows = table.querySelectorAll('#tasks_tbody tr.height35'), max = 0;
    rows.forEach(function(r){ max = Math.max(max, r.children.length); });
    rows.forEach(function(r){ while(r.children.length < max) r.appendChild(document.createElement('td')); });
  }
  if(table){
    var card = document.createElement('div'); card.className = 'mq-card';
    var ttl = document.createElement('div'); ttl.className = 'mq-page-title';
    ttl.innerHTML = '<img src="images/tasks_icon.png" width="20" height="20" class="menu-item-icon" alt=""><span>סוגי משימות</span>';
    var body = document.createElement('div'); body.className = 'mq-card-body';
    table.parentNode.insertBefore(card, table);
    card.appendChild(ttl); card.appendChild(body); body.appendChild(table);
    card.parentNode.insertBefore(wrap, card);
    card.parentNode.insertBefore(wrapB, card.nextSibling);
  } else {
    row.parentNode.insertBefore(wrap, row);
    row.parentNode.insertBefore(wrapB, row.nextSibling);
  }

  // "רשימת המשימות" dans le menu, avant le 1er element, avec le "|", en gras
  ['tasks_menu_list', 'tasks_menu_list_preview'].forEach(function(id){
    var ul = document.getElementById(id); if(!ul || !ul.firstElementChild) return;
    var li = document.createElement('li');
    li.innerHTML = '<a class="font-weight-bold" href="' + tasksUrl.replace(/"/g, '&quot;') + '"><i class="fa-solid fa-table-list menu-item-icon mq-menu-icon"></i><span>רשימת המשימות</span></a>';
    var sep = document.createElement('li'); sep.className = 'separator'; sep.textContent = '|';
    ul.insertBefore(sep, ul.firstElementChild); ul.insertBefore(li, sep);
  });
})();
</script>
