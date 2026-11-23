<style id="maquette-rapports">
/* ===== MAQUETTE - custom_reports.php (aucun fichier du site modifie) ===== */
/* Proposition (pas demandee) : meme fond blanc casse que la maquette du tableau des taches, pour faire ressortir le tableau */
html, body { background:#f6f5f1 !important; }

/* (1) titre dans une carte, comme l'en-tete des cartes de add_meeting.php (bgColor-1a5276, blanc, gras, coins 15px), icone du menu devant */
.mq-page-title { display:flex; align-items:center; justify-content:center; gap:10px; width:fit-content; max-width:100%; margin:12px auto 0;   /* taille d'un bouton, centre */
  background:#1a5276; color:#fff; font-weight:700; font-size:20px; padding:8px 28px; border-radius:15px; box-sizing:border-box; direction:rtl; }   /* icone avant le texte (a droite en hebreu) */

/* (3) bouton "יצירת דו״ח חדש" : sans fond, texte et bord dans le bleu de l'ancien fond, survol bleu clair */
.mq-btns { display:flex; justify-content:center; gap:12px; flex-wrap:wrap; margin-top:-10px; }
/* carte (classes du site : border-frame #C5B8AA, bg-f2f6f9, borderRadius15) ; le titre devient son en-tete sur toute la largeur */
.mq-card { width:320px; max-width:100%; margin:16px auto 0; border:1px solid #C5B8AA; border-radius:15px; background:#f2f6f9; overflow:hidden; }
.mq-card .mq-page-title { width:100%; margin:0 !important; border-radius:0; }
.mq-card-body { padding:14px 12px; }
.mq-card-body #responsibles_list { width:100%; margin:0 auto; border:none !important; border-radius:0 !important; box-shadow:none; }   /* tableau sans bordure dans la carte */
.mq-btn { display:inline-flex; align-items:center; gap:8px; padding:7px 18px; border-radius:999px; font-weight:700; font-size:15px;
  cursor:pointer; transition:background .15s; text-decoration:none !important; flex-direction:row-reverse; }
.mq-btn.add { background:transparent; color:#1a5276 !important; border:1.5px solid #1a5276; }
.mq-btn.add:hover { background:#e3eef6; }
.mq-btn i { font-size:14px; }

/* bouton "תקציב" du menu : moins large (moins d'espace interieur et entre les icones) */
.btn-budget { padding:5px 10px !important; }
.btn-budget i { margin-left:6px !important; }
.btn-budget .budget-btn-arrow { margin-right:4px !important; margin-left:0 !important; }

/* (2) nouvel element du menu : icone blanche comme les autres */
.mq-menu-icon { color:#fff; font-size:17px; width:20px; text-align:center; }

/* Tableau : bordure arrondie, plus de quadrillage, survol des lignes */
#responsibles_list { border-collapse:separate !important; border-spacing:0; border:1.5px solid #9fb6c9 !important; border-radius:16px !important; overflow:hidden;
  background:#fff; box-shadow:0 2px 12px rgba(26,82,118,.06); width:360px; max-width:100%; }
/* colonnes modifier / supprimer etroites, le nom prend le reste */
#responsibles_list td:not(:first-child) { width:40px; padding:0 4px !important; }
#responsibles_list th[colspan] { width:80px; }
#responsibles_list tr.height35 td:first-child, #responsibles_list th:first-child { width:100% !important; }
#responsibles_list td:not(:first-child) { width:36px !important; min-width:36px; white-space:nowrap; }
#responsibles_list th, #responsibles_list td { border:none !important; border-bottom:1px solid #eef0f3 !important; }
#responsibles_list tr:last-child td { border-bottom:none !important; }
/* (3) en-tete "שם" : fond #dedede, texte bleu */
#responsibles_list tr.bgColorSilver, #responsibles_list tr.bgColorSilver th { background:#dedede !important; }
#responsibles_list th { color:#1a5276 !important; font-weight:700; font-size:14px; height:44px; }
#responsibles_list tr.height35 td { height:44px; font-size:14.5px; color:#2d3a45; padding:0 14px !important; }
/* une ligne sur deux, en commencant par la ligne foncee (la 1re ligne de donnees est le 2e tr, apres l en-tete) */
#responsibles_list tr.height35:nth-child(odd) td { background:#d4e4f2 !important; }
#responsibles_list tr.height35:nth-child(even) td { background:#a9c6e0 !important; }
#responsibles_list tr.height35 td:first-child { color:#1a5276 !important; font-weight:600; }
/* icones modifier / supprimer : petites, avec un rond au survol */
#responsibles_list td img { width:18px; height:18px; padding:6px; box-sizing:content-box; border-radius:50%; opacity:.75; transition:background .15s, opacity .15s; }
#responsibles_list td img:hover { opacity:1; background:#e7eef5; }
#responsibles_list td img[src*="delete"]:hover { background:#fde6e6; }
</style>
<script>
(function(){
  var add = document.querySelector('input[type="button"][value*="יצירת"]');
  if(!add) return;
  var pid = (document.getElementById('project_id') || {}).value || '';
  var back = document.getElementById('a_project_title');
  var tasksUrl = back ? back.getAttribute('href') : ('meetings.php?project_id=' + pid);

  // (1) titre-carte avec l'icone du menu
  var ttl = document.createElement('div'); ttl.className = 'mq-page-title';
  ttl.innerHTML = '<img src="images/my_reports_icon.png" width="20" height="20" class="menu-item-icon" alt=""><span>הדוח\'\'ות שלי</span>';   // meme icone que le menu
  add.parentNode.insertBefore(ttl, add);

  // (3) bouton de creation seul (le bouton "חזרה" passe dans le menu)
  var wrap = document.createElement('div'); wrap.className = 'mq-btns';
  var b1 = document.createElement('a'); b1.className = 'mq-btn add';
  b1.innerHTML = '<i class="fa-solid fa-plus"></i><span>יצירת דו״ח חדש</span>';
  b1.onclick = function(){ location.href = 'add_custom_report.php?id=0&project_id=' + pid; };   // meme action que le bouton actuel
  wrap.appendChild(b1);
  add.style.display = 'none';

  // carte comme dans add_meeting.php : bordure, en-tete = titre, tableau dans le corps ; bouton au-dessus de la carte
  var table = document.getElementById('responsibles_list');
  if(table){
    var card = document.createElement('div'); card.className = 'mq-card';
    var body = document.createElement('div'); body.className = 'mq-card-body';
    table.parentNode.insertBefore(card, table);
    card.appendChild(ttl); card.appendChild(body); body.appendChild(table);
    card.parentNode.insertBefore(wrap, card);
  } else {
    ttl.parentNode.insertBefore(wrap, ttl);
  }

  // (2) "רשימת המשימות" dans le menu, avant "הדוח''ות שלי", avec le "|" ; en gras comme les elements actifs
  ['tasks_menu_list', 'tasks_menu_list_preview'].forEach(function(id){
    var ul = document.getElementById(id); if(!ul || !ul.firstElementChild) return;
    var li = document.createElement('li');
    li.innerHTML = '<a class="font-weight-bold" href="' + tasksUrl.replace(/"/g, '&quot;') + '"><i class="fa-solid fa-table-list menu-item-icon mq-menu-icon"></i><span>רשימת המשימות</span></a>';
    var sep = document.createElement('li'); sep.className = 'separator'; sep.textContent = '|';
    ul.insertBefore(sep, ul.firstElementChild); ul.insertBefore(li, sep);
  });
})();
</script>
