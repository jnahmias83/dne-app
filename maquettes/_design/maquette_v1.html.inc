<style id="maquette-v1">
/* ===== MAQUETTE v1 - tableau des taches (aucun fichier du site modifie) ===== */

/* 7. Fond blanc casse, le tableau ressort */
html, body { background:#f6f5f1 !important; }

/* 1. Tableau allege : plus de quadrillage, juste des separateurs fins */
#meetings_table { background:#fff; border:1px solid #e4e7eb !important; border-radius:14px; box-shadow:0 2px 12px rgba(26,82,118,.06); overflow:hidden; }
#meetings_table td, #meetings_table th { border:none !important; border-bottom:1px solid #eef0f3 !important; }
#meetings_table tr.bgColor-f2f6f9 th { background:#f5f7fa !important; color:#1a5276 !important; font-weight:600; }

/* 2. Couleur sur l'element seulement (pastille), la case reste blanche */
#meetings_table [id^="td_task_"], #meetings_table [id^="td_responsible_"],
#meetings_table [id^="td_pass_on_"], #meetings_table [id^="td_progress_status_"] { background-color:#fff !important; text-align:center !important; vertical-align:middle; }
#meetings_table [id^="td_task_"] select, #meetings_table [id^="td_responsible_"] select,
#meetings_table [id^="td_pass_on_"] select, #meetings_table [id^="td_progress_status_"] select {
  display:inline-block; width:auto !important; max-width:96%; height:auto !important;
  padding:3px 12px !important; border-radius:999px !important; border:none !important;
  line-height:1.4; font-size:12px !important; text-align-last:center; }

/* 3 + 4 + 5. En-tete de chapitre : titre aligne au texte, "+" au-dessus des numeros, triangle par chapitre */
#meetings_table tr.bgColor-cbddec > td { background:#eef3f8 !important; padding-right:92px !important; position:relative; height:40px; }
#meetings_table tr.bgColor-cbddec .chapter-name-link { text-decoration:none !important; color:#1a5276 !important; font-size:14px; }
#meetings_table tr.bgColor-cbddec .chapter-add-task-btn {
  position:absolute; right:51px; top:50%; transform:translateY(-50%);
  width:24px; height:24px; border-radius:50%; background:#1a5276; display:flex; align-items:center; justify-content:center;
  box-shadow:0 1px 3px rgba(0,0,0,.2); cursor:pointer; }
#meetings_table tr.bgColor-cbddec .chapter-add-task-btn img { display:none; }
#meetings_table tr.bgColor-cbddec .chapter-add-task-btn::before { content:"+"; color:#fff; font-size:18px; font-weight:700; line-height:1; margin-top:-2px; }
.mq-chap-toggle { position:absolute; right:12px; top:50%; transform:translateY(-50%); width:22px; height:22px; border-radius:6px;
  display:flex; align-items:center; justify-content:center; color:#1a5276; font-size:14px; cursor:pointer; transition:transform .2s; }
.mq-chap-toggle:hover { background:#dde7f0; }
.mq-chap-toggle.is-collapsed { transform:translateY(-50%) rotate(90deg); }

/* 6 (proposition). Tache en cours : ligne blanche + barre verte + fond vert tres pale (au lieu de toute la ligne verte) */
#meetings_table tr.task-row-highlight td, #meetings_table tr.row-darken td { background-color:#f2faf4 !important; }
#meetings_table tr.task-row-highlight td:first-child, #meetings_table tr.row-darken td:first-child { box-shadow:inset -5px 0 0 #2e9e5b; }

/* Remarques dans le tableau : 2e ligne alignee sur le debut du texte (pas sous le badge / la date), comme le popup מעקב */
#meetings_table [id^="description_"] div.marginTop5.colorGreenDark,
#meetings_table [id^="description_"] div[id^="div-tracking-remarks-"] { display:flex !important; align-items:baseline; gap:4px; }
#meetings_table [id^="description_"] div.marginTop5.colorGreenDark > span { flex:0 0 auto; }
#meetings_table [id^="description_"] div.marginTop5.colorGreenDark > span:last-child { flex:1 1 auto; min-width:0; }
#meetings_table [id^="description_"] div[id^="div-tracking-remarks-"] > span { flex:0 0 auto; }
#meetings_table [id^="description_"] div[id^="div-tracking-remarks-"] > span.colorRed { flex:1 1 auto; min-width:0; }

/* 8. Barre de reglages au-dessus du tableau : plus discrete */
.d-flex.justify-content-between:has(#check_all) { zoom:.8; opacity:.9; padding:4px 10px; background:#fff; border:1px solid #eceff3; border-radius:12px; margin-bottom:6px; }

/* 9. Menus deroulants du haut : uniformes, couleurs My Project, ouverture qui se devoile */
.navbar .dropdown-menu { border:none !important; border-radius:12px !important; padding:6px !important; background:#fff !important;
  box-shadow:0 12px 32px rgba(30,45,60,.22) !important; animation:mqReveal .18s ease-out; transform-origin:top; }
@keyframes mqReveal { from { opacity:0; transform:translateY(-8px) scaleY(.96); } to { opacity:1; transform:none; } }
.navbar .dropdown-menu li a, .navbar .dropdown-menu .dropdown-item { display:block; border-radius:8px; padding:7px 16px !important; color:#3e4852 !important; font-size:14px; }
.navbar .dropdown-menu li a:hover, .navbar .dropdown-menu .dropdown-item:hover { background:#e7f0f3 !important; color:#1a5276 !important; }

/* Filtres - option A (PC) : zone compacte */
body.mq-A .meetings-toolbar { transform:scale(.88); transform-origin:top center; margin-bottom:-30px; }
body.mq-A .meetings-toolbar .report-container { height:auto !important; padding-bottom:8px; }
/* Filtres - option B (tablette) : zone repliee derriere un bouton */
body.mq-B .meetings-toolbar { display:none !important; }
body.mq-B.mq-B-open .meetings-toolbar { display:flex !important; }
.mq-filter-btn { display:none; margin:10px auto; padding:7px 18px; border-radius:999px; border:1px solid #c9d7e3; background:#fff; color:#1a5276;
  font-weight:600; font-size:14px; box-shadow:0 1px 4px rgba(0,0,0,.06); cursor:pointer; }
body.mq-B .mq-filter-btn { display:inline-flex; gap:8px; align-items:center; }
</style>
<script>
(function(){
  var h = location.hash || '';
  if(h.indexOf('A') > -1) document.body.classList.add('mq-A');
  if(h.indexOf('B') > -1) document.body.classList.add('mq-B');

  // Bouton "סינון ודו״חות" (option B)
  var tb = document.querySelector('.meetings-toolbar');
  if(tb){
    var wrap = document.createElement('div'); wrap.style.textAlign = 'center';
    var b = document.createElement('button'); b.className = 'mq-filter-btn'; b.type = 'button';
    b.innerHTML = '<span>⚙</span><span>סינון ודו״חות</span><span>▾</span>';
    b.onclick = function(){ document.body.classList.toggle('mq-B-open'); };
    wrap.appendChild(b); tb.parentNode.insertBefore(wrap, tb);
  }

  // Triangle de repli par chapitre (utilise le repli existant du chapitre)
  setTimeout(function(){
    document.querySelectorAll('#meetings_table tr.bgColor-cbddec > td').forEach(function(td){
      var t = document.createElement('span'); t.className = 'mq-chap-toggle'; t.textContent = '▾'; t.title = 'כווץ / הרחב פרק';
      t.onclick = function(e){ e.stopPropagation(); var l = td.querySelector('.chapter-name-link'); if(l) l.click(); t.classList.toggle('is-collapsed'); };
      td.appendChild(t);
    });
    // Demo : 2e tache = "tache en cours"
    var rows = document.querySelectorAll('#meetings_table tr[id^="row_"]');
    if(rows[1]) rows[1].classList.add('task-row-highlight');
    // Demo : menu "פרוייקטים" ouvert
    if(h.indexOf('open') > -1){ var m = document.querySelector('[aria-labelledby="projectsDropdown"]'); if(m) m.classList.add('show'); }
  }, 300);
})();
</script>
