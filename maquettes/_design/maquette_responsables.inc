<style id="maquette-responsables">
/* ===== MAQUETTE - responsibles.php (aucun fichier du site modifie) - meme logique que la maquette custom_reports ===== */
html, body { background:#f6f5f1 !important; }

/* bouton "הוסף איש צוות" : sans fond, texte et bord bleus, survol bleu clair ; au-dessus de la carte */
.mq-btns { display:flex; justify-content:center; gap:12px; flex-wrap:wrap; margin-top:24px; }
.mq-btn { display:inline-flex; align-items:center; gap:8px; padding:7px 18px; border-radius:999px; font-weight:700; font-size:15px;
  cursor:pointer; transition:background .15s; text-decoration:none !important; flex-direction:row-reverse; }
.mq-btn.add { background:transparent; color:#1a5276 !important; border:1.5px solid #1a5276; }
.mq-btn.add:hover { background:#e3eef6; }
.mq-btn i { font-size:14px; }
/* bouton du bas "ברירת מחדל גופנים ורקעים" : meme style que "הוסף איש צוות" */
input.btn[value*="ברירת מחדל"] { background:transparent !important; color:#1a5276 !important; border:1.5px solid #1a5276 !important;
  border-radius:999px !important; padding:4px 10px !important; font-weight:600; font-size:13px; letter-spacing:-.2px; box-shadow:none !important; transition:background .15s;
  width:auto !important; min-width:0 !important; }
input.btn[value*="ברירת מחדל"]:hover { background:#e3eef6 !important; }

/* bouton "תקציב" du menu : moins large (moins d'espace interieur et entre les icones) */
.btn-budget { padding:5px 10px !important; }
.btn-budget i { margin-left:6px !important; }
.btn-budget .budget-btn-arrow { margin-right:4px !important; margin-left:0 !important; }

/* "רשימת המשימות" dans le menu : icone blanche comme les autres */
.mq-menu-icon { color:#fff; font-size:17px; width:20px; text-align:center; }

/* carte comme dans add_meeting.php (border-frame #C5B8AA, bg-f2f6f9, coins 15px) ; en-tete = titre avec l'icone du menu */
.mq-card { width:fit-content; max-width:100%; margin:16px auto 0; border:1px solid #C5B8AA; border-radius:15px; background:#f2f6f9; overflow:hidden; }
.mq-page-title { display:flex; align-items:center; justify-content:center; gap:10px; background:#1a5276; color:#fff; font-weight:700; font-size:20px;
  padding:8px 28px; direction:rtl; }
.mq-card-body { padding:14px 12px; overflow-x:auto; }

/* tableau dans la carte : sans bordure, en-tete #dedede texte bleu, une ligne sur deux grise */
#responsibles_list { border-collapse:separate !important; border-spacing:0; border:none !important; background:#fff; margin:0 auto; }
#responsibles_list th, #responsibles_list td { border:none !important; border-bottom:1px solid #eef0f3 !important; }
#responsibles_list tr:last-child td { border-bottom:none !important; }
#responsibles_list tr.bgColorSilver, #responsibles_list tr.bgColorSilver th { background:#dedede !important; }
#responsibles_list th, #responsibles_list th * { color:#1a5276 !important; }
#responsibles_list th { font-weight:700; font-size:14px; height:44px; padding:4px 8px !important; }
#responsibles_list tr.height35 td { height:44px; font-size:14.5px; color:#2d3a45; padding:0 10px !important; }
/* une ligne sur deux, en commencant par la ligne foncee (la 1re ligne de donnees est le 2e tr, apres l en-tete) */
#responsibles_list tr.height35:nth-child(odd) td { background:#d4e4f2 !important; }
#responsibles_list tr.height35:nth-child(even) td { background:#a9c6e0 !important; }
/* contact decoche (inactif) : le fond de la rangee ne change pas (l'alternance reste), le contenu devient gris et estompe ;
   la case reste nette pour pouvoir la recocher */
#responsibles_list tr.height35:has(input[type="checkbox"]:not(:checked)) td:not(:first-child),
#responsibles_list tr.height35:has(input[type="checkbox"]:not(:checked)) td:nth-child(2) { color:#8a96a3 !important; font-weight:normal !important; }
#responsibles_list tr.height35:has(input[type="checkbox"]:not(:checked)) td:not(:first-child) > * { opacity:.4; }
/* colonne des cases a cocher : juste la largeur de la case */
#responsibles_list tr > :first-child { width:22px !important; min-width:0; padding:0 3px !important; white-space:nowrap; }
/* colonne "שם" : largeur selon les noms (au lieu de 130px fixes) */
#responsibles_list tr > :nth-child(2) { width:auto !important; }
#responsibles_list tr.height35 td:nth-child(2) { color:#1a5276 !important; font-weight:600; }
#responsibles_list td img { width:18px; height:18px; padding:6px; box-sizing:content-box; border-radius:50%; opacity:.75; transition:background .15s, opacity .15s; }
#responsibles_list td img:hover { opacity:1; background:#e7eef5; }
#responsibles_list td img[src*="delete"]:hover { background:#fde6e6; }
</style>
<script>
(function(){
  // MAQUETTE SEULEMENT : la case actif/inactif ne fait que changer l'affichage
  // (sur le vrai site elle enregistre en base puis recharge ; ici la photo figee la remettrait cochee)
  // (pose au chargement complet : la vraie fonction est declaree plus bas dans la page et ecraserait une pose immediate)
  window.addEventListener('load', function(){ window.setResponsibleActive = function(){}; });

  var pid = (document.getElementById('project_id') || {}).value || '';
  var back = document.getElementById('a_project_title');
  var tasksUrl = back ? back.getAttribute('href') : ('meetings.php?project_id=' + pid);

  // ancien titre "צוות הפרוייקט" sous le nom du projet : il passe dans l'en-tete de la carte
  var oldTtl = back ? back.parentElement.querySelector(':scope > div.fontSize26') : null;
  if(oldTtl) oldTtl.style.display = 'none';

  var add = document.querySelector('a.btn[onclick*="add_responsible.php?id=0"]');
  if(!add) return;
  var row = add.closest('.row');
  row.style.display = 'none';
  var br = row.nextElementSibling; if(br && br.tagName === 'BR') br.style.display = 'none';

  // bouton "הוסף איש צוות" (icone contact existante)
  var wrap = document.createElement('div'); wrap.className = 'mq-btns';
  var b1 = document.createElement('a'); b1.className = 'mq-btn add';
  b1.innerHTML = '<i class="fa fa-user"></i><span>הוסף איש צוות</span>';
  b1.onclick = function(){ location.href = 'add_responsible.php?id=0&project_id=' + pid; };   // meme action que le bouton actuel
  wrap.appendChild(b1);

  // carte : en-tete "צוות הפרוייקט" + icone du menu, tableau dans le corps ; bouton au-dessus de la carte
  var table = document.getElementById('responsibles_list');
  if(table){
    var card = document.createElement('div'); card.className = 'mq-card';
    var ttl = document.createElement('div'); ttl.className = 'mq-page-title';
    ttl.innerHTML = '<img src="images/responsibles_icon.png" width="20" height="20" class="menu-item-icon" alt=""><span>צוות הפרוייקט</span>';
    var body = document.createElement('div'); body.className = 'mq-card-body';
    table.parentNode.insertBefore(card, table);
    card.appendChild(ttl); card.appendChild(body); body.appendChild(table);
    card.parentNode.insertBefore(wrap, card);
  } else {
    row.parentNode.insertBefore(wrap, row);
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
