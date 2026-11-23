<style id="maquette-chapitres">
/* ===== MAQUETTE - chapters.php (aucun fichier du site modifie) - meme logique que les maquettes rapports / responsables ===== */
html, body { background:#f6f5f1 !important; }

/* bouton "הוסף פרק" : sans fond, texte et bord bleus, survol bleu clair ; au-dessus de la carte */
.mq-btns { display:flex; justify-content:center; gap:12px; flex-wrap:wrap; margin-top:24px; }
.mq-btn { display:inline-flex; align-items:center; gap:8px; padding:7px 18px; border-radius:999px; font-weight:700; font-size:15px;
  cursor:pointer; transition:background .15s; text-decoration:none !important; flex-direction:row-reverse; }
.mq-btn.add { background:transparent; color:#1a5276 !important; border:1.5px solid #1a5276; }
.mq-btn.add:hover { background:#e3eef6; }
.mq-btn i { font-size:14px; }

/* bouton "תקציב" du menu : moins large (moins d'espace interieur et entre les icones) */
.btn-budget { padding:5px 10px !important; }
.btn-budget i { margin-left:6px !important; }
.btn-budget .budget-btn-arrow { margin-right:4px !important; margin-left:0 !important; }

/* "רשימת המשימות" dans le menu : icone blanche comme les autres */
.mq-menu-icon { color:#fff; font-size:17px; width:20px; text-align:center; }

/* carte comme dans add_meeting.php (border-frame #C5B8AA, bg-f2f6f9, coins 15px) ; en-tete = titre avec l'icone du menu */
.mq-card { width:fit-content; min-width:320px; max-width:100%; margin:16px auto 0; border:1px solid #C5B8AA; border-radius:15px; background:#f2f6f9; overflow:hidden; }
.mq-page-title { display:flex; align-items:center; justify-content:center; gap:10px; background:#1a5276; color:#fff; font-weight:700; font-size:20px;
  padding:8px 28px; direction:rtl; }
.mq-card-body { padding:14px 12px; overflow-x:auto; }

/* tableau dans la carte : sans bordure, en-tete #dedede texte bleu */
#chapters_list { border-collapse:separate !important; border-spacing:0; border:none !important; background:#fff; margin:0 auto; width:100%; }
#chapters_list th, #chapters_list td { border:none !important; border-bottom:1px solid #eef0f3 !important; }
#chapters_list tr:last-child td { border-bottom:none !important; }
#chapters_list tr.bgColorSilver, #chapters_list tr.bgColorSilver th { background:#dedede !important; }
#chapters_list th { color:#1a5276 !important; font-weight:700; font-size:14px; height:44px; padding:4px 8px !important; }
#chapters_list tr.height35 td { height:44px; font-size:14.5px; color:#2d3a45; padding:0 10px !important; }
/* une ligne sur deux, en commencant par la ligne foncee (l'en-tete est hors du tbody) */
#chapters_tbody tr.height35:nth-child(odd) td { background:#a9c6e0 !important; }
#chapters_tbody tr.height35:nth-child(even) td { background:#d4e4f2 !important; }
#chapters_list tr.height35 td:nth-child(2) { color:#1a5276 !important; font-weight:600; min-width:200px; }
/* colonne de la poignee de deplacement : etroite */
#chapters_list tr > :first-child { width:34px !important; padding:0 4px !important; }
#chapters_list .chapter-drag-handle { color:#1a5276; }
/* icones modifier / supprimer : petites, avec un rond au survol */
#chapters_list tr > :nth-child(3), #chapters_list tr > :nth-child(4) { width:40px !important; padding:0 4px !important; }
#chapters_list td img { width:18px; height:18px; padding:6px; box-sizing:content-box; border-radius:50%; opacity:.75; transition:background .15s, opacity .15s; }
#chapters_list td img:hover { opacity:1; background:#e7eef5; }
#chapters_list td img[src*="delete"]:hover { background:#fde6e6; }
</style>
<script>
(function(){
  // MAQUETTE SEULEMENT : rien n'est enregistre (ni ordre des chapitres, ni suppression) ; le glisser-deposer reste visuel
  // (pose au chargement complet : les vraies fonctions sont declarees plus bas dans la page)
  window.addEventListener('load', function(){
    window.removeChapter = function(){ return false; };
    if(window.jQuery){
      var realAjax = jQuery.ajax;
      jQuery.ajax = function(opts){
        var url = (opts && opts.url) || '';
        if(/reorder_chapters\.php|chapter_delete\.php/.test(url)) return jQuery.Deferred().resolve('').promise();
        return realAjax.apply(this, arguments);
      };
    }
  });

  var pid = (document.getElementById('project_id') || document.getElementById('id_project') || {}).value || '';
  var back = document.getElementById('a_project_title');
  var tasksUrl = back ? back.getAttribute('href') : ('meetings.php?project_id=' + pid);

  // ancien titre "רשימת פרקים" sous le nom du projet : il passe dans l'en-tete de la carte
  var oldTtl = back ? back.parentElement.querySelector(':scope > div.fontSize26') : null;
  if(oldTtl) oldTtl.style.display = 'none';

  // anciens boutons "חזור לרשימת המשימות" (passe dans le menu) et "+ פרק"
  var add = document.querySelector('a.btn[onclick*="add_chapter.php?id=0"]');
  if(!add) return;
  var row = add.closest('.row');
  row.style.display = 'none';
  var br = row.nextElementSibling; if(br && br.tagName === 'BR') br.style.display = 'none';
  var m = (add.getAttribute('onclick') || '').match(/project_id=(\d+)/); if(m) pid = m[1];

  // bouton "הוסף פרק"
  var wrap = document.createElement('div'); wrap.className = 'mq-btns';
  var b1 = document.createElement('a'); b1.className = 'mq-btn add';
  b1.innerHTML = '<i class="fa fa-plus"></i><span>הוסף פרק</span>';
  b1.onclick = function(){ location.href = 'add_chapter.php?id=0&project_id=' + pid; };   // meme action que le bouton actuel
  wrap.appendChild(b1);

  // carte : en-tete "פרקים" + icone du menu, tableau dans le corps ; bouton au-dessus de la carte
  var table = document.getElementById('chapters_list');
  if(table){
    var card = document.createElement('div'); card.className = 'mq-card';
    var ttl = document.createElement('div'); ttl.className = 'mq-page-title';
    ttl.innerHTML = '<img src="images/chapters_icon.png" width="20" height="20" class="menu-item-icon" alt=""><span>פרקים</span>';
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
