<style id="maquette-tache-live">
/* ===== MAQUETTE VIVANTE - popup de la tache (#modalTaskFollowupActions) - aucun fichier du site modifie =====
   Uniquement l'affichage DANS le popup (#div_content_task_details) : l'image partagee (#contentToScreenshot) n'est pas touchee. */
/* tableau des taches (derriere le popup) : comme tableau.html - pas de separateurs verticaux (l'en-tete th garde les siens),
   ligne tres legere entre les taches (26/11) */
#meetings_table td { border:none !important; border-bottom:1px solid #e9eeef !important; }

/* (2) titre centre, plus de ligne vide dessous (la ligne des fleches est supprimee, fleches deplacees) */
#modalTaskFollowupActions .modal-title { text-align:center; }
#modalTaskFollowupActions .mq-hide { display:none !important; }
/* (4) fleches blanches des 2 cotes, sur la ligne du nom du projet */
#modalTaskFollowupActions .mq-name-line { display:flex; align-items:center; justify-content:space-between; gap:8px; }
#modalTaskFollowupActions .mq-name-line .mq-name { flex:1; text-align:center; }
#modalTaskFollowupActions .mq-name-line a { position:static !important; cursor:pointer; padding:0 4px; }
#modalTaskFollowupActions .mq-name-line a i { font-size:22px !important; color:#fff !important; }

/* (3) "תודה על העדכון" : pastille sans trait de cadre, texte + triangle blancs, sur la ligne de BNY */
#modalTaskFollowupActions .modal-header { position:relative; }
#modalTaskFollowupActions .btn-set-to-read { position:absolute !important; left:12px; top:10px; z-index:2;
  background:rgba(255,255,255,.22) !important; border:none !important; border-radius:999px !important;
  color:#fff !important; font-size:12px !important; font-weight:700 !important; padding:3px 11px !important; box-shadow:none !important; margin:0 !important; white-space:nowrap; }
/* la ligne du nom du projet prend toute la largeur : fleches aux 2 extremites */
#modalTaskFollowupActions .modal-title { width:100%; }
#modalTaskFollowupActions .mq-name-line { width:100%; }
#modalTaskFollowupActions .btn-set-to-read:hover { background:rgba(255,255,255,.35) !important; }
#modalTaskFollowupActions .btn-set-to-read .fa-check { display:none !important; }
#modalTaskFollowupActions .btn-set-to-read .fa-caret-left { color:#fff !important; font-size:14px !important; }

/* (1)(5)(6) grille d'infos : 3 colonnes ; 2e ligne = dates (sous משימת + אחראי) et statut (sous להעביר ל), sans libelle */
#div_content_task_details .mq-grid { grid-template-columns:repeat(3, 1fr) !important; row-gap:6px; }
#div_content_task_details .mq-date { text-align:center; font-size:13px; align-self:center; white-space:nowrap; }
#div_content_task_details .mq-date span { margin:0 2px !important; }
/* sujet | zone : le trait au centre, sujet colle a droite du trait, zone a gauche */
#div_content_task_details .mq-pipe-line { display:grid; grid-template-columns:1fr auto 1fr; align-items:center; column-gap:8px; }
#div_content_task_details .mq-pipe-line .mq-subj { text-align:left; }
#div_content_task_details .mq-pipe-line .mq-area { text-align:right; }
#div_content_task_details .mq-desc-block { width:fit-content; max-width:100%; margin-left:auto; margin-right:auto; text-align:right; }
#div_content_task_details tr.alignRight.bgColor-ebf4f5 > td { padding-left:10px; }
/* labels משימת / אחראי / להעביר ל : pas en gras */
#div_content_task_details .mq-grid span.colorGrey.font-weight-bold { font-weight:normal !important; }
/* description | journal : meme trait fin sur toute la largeur que celui au-dessus du bloc מעקב */
#div_content_task_details tr.alignRight.bgColor-ebf4f5 > td hr { border:none !important; border-top:1px solid #ccc !important; margin:8px -10px !important; }
/* zone du journal : icone de repli au coin gauche, centree verticalement */
/* le </p> orphelin cree un <p> vide (marge de 16px) sous le journal : c'est lui qui decalait le centre de l'icone */
#div_content_task_details tr.alignRight.bgColor-ebf4f5 > td > p:empty { display:none; }
#div_content_task_details .mq-log-zone { position:relative; min-height:26px; background:#fff; margin:0 -10px -1px !important; padding:4px 10px 4px 32px !important; }
#div_content_task_details tr.alignRight.bgColor-ebf4f5 > td hr { margin-bottom:0 !important; }
#div_content_task_details .mq-log-toggle { position:absolute; left:8px; top:50%; transform:translateY(-50%); cursor:pointer; z-index:2; line-height:0; }
#div_content_task_details .mq-log-closed > div[style*="max-height:90px"] { display:none; }   /* marges egales des 2 cotes pour un vrai centre */
/* chapitre / sujet / zone : memes couleurs que le dashboard (projects.php) */
#div_content_task_details tr.bgColor-ebf4f5 td.paddingTop5 > strong { color:#19bf42; font-weight:bold; }
#div_content_task_details .mq-pipe-line, #div_content_task_details .mq-pipe-line .mq-subj { color:#1A5276; font-weight:bold; }
#div_content_task_details .mq-pipe-line > span.fontSize20.colorRed { color:red !important; font-size:inherit !important; font-weight:bold; }
#div_content_task_details .mq-pipe-line .mq-area { color:#1A5276; font-weight:normal; }
#div_content_task_details .mq-status { text-align:center; align-self:center; }

/* (8) remarques de suivi : sans cadre, 2e ligne alignee sur le texte (pas sous la date) */
#div_content_task_details #row_log_meeting_tracking > td > div,
#div_content_task_details #row_log_meeting_tracking > td > div > div:first-child { border:none !important; }
#div_content_task_details #row_log_meeting_tracking td[style*="width:91%"] { display:flex !important; align-items:flex-start; gap:4px; }
#div_content_task_details #row_log_meeting_tracking td[style*="width:91%"] > span:first-child { flex:0 0 auto; }
#div_content_task_details #row_log_meeting_tracking td[style*="width:91%"] > span:last-child { flex:1; min-width:0; }

/* (9) boutons d'action plus compacts */
#modalTaskFollowupActions form img { width:22px !important; height:22px !important; }
#modalTaskFollowupActions form strong.fontSize14 { font-size:11px !important; }
#modalTaskFollowupActions form .btn { padding:2px 4px !important; width:auto !important; height:auto !important; min-width:0 !important; }
/* icones resserrees (au lieu de 20% de la largeur chacune) et les 2 rangees rapprochees */
#modalTaskFollowupActions form .flex.justify-content-center { gap:4px; }
#modalTaskFollowupActions form .width20Percents { width:auto !important; flex:0 0 62px; }
#modalTaskFollowupActions form .row { margin-top:0 !important; margin-bottom:0 !important; }
#modalTaskFollowupActions form .row + .row { margin-top:8px !important; } /* un peu d'air entre les 2 rangees */
#modalTaskFollowupActions form.marginTop15 { margin-top:6px !important; }
</style>
<script>
(function(){
  var modal = document.getElementById('modalTaskFollowupActions');
  if(!modal) return;
  var header = modal.querySelector('.modal-header');
  var title = modal.querySelector('.modal-title');
  var next = document.getElementById('link_next_task'), prev = document.getElementById('link_prev_task');
  var navRow = next ? next.parentElement : null;

  // (2)(4) fleches -> ligne du nom du projet ; ligne des fleches supprimee
  function fixTitle(){
    if(!title || !next || !prev) return;
    var sub = title.querySelector('div');            // ligne du nom du projet (name_he)
    if(!sub || sub.querySelector('.mq-name')) return;
    var name = document.createElement('span'); name.className = 'mq-name'; name.innerHTML = sub.innerHTML;
    sub.innerHTML = ''; sub.classList.add('mq-name-line');
    next.querySelector('i').className = 'fa-solid fa-caret-right';
    prev.querySelector('i').className = 'fa-solid fa-caret-left';
    sub.appendChild(next); sub.appendChild(name); sub.appendChild(prev);   // les liens gardent leurs evenements
    if(navRow) navRow.classList.add('mq-hide');
  }
  if(title) new MutationObserver(fixTitle).observe(title, {childList:true, subtree:true});

  // (1)(5)(6) grille : dates + statut sur la 2e ligne
  var box = document.getElementById('div_content_task_details');
  function fixGrid(){
    if(!box) return;
    var grid = box.querySelector('div[style*="display:grid"]');
    if(!grid || grid.classList.contains('mq-grid')) return;
    grid.classList.add('mq-grid');
    var items = grid.children, status = items.length > 3 ? items[3] : null;
    if(status){
      var lab = status.querySelector('span.colorGrey'); if(lab){ var br = lab.nextElementSibling; lab.remove(); if(br && br.tagName === 'BR') br.remove(); }
      status.classList.add('mq-status');
    }
    // 2e ligne en face de la 1re : יצירה sous משימה, יעד sous אחראי, statut sous להעביר ל (case vide s'il n'y a pas de statut)
    var datesTd = Array.prototype.find.call(box.querySelectorAll('td'), function(td){ return /יצירה|Creation/.test(td.textContent) && /יעד|Target/.test(td.textContent); });
    if(datesTd){
      var sp = datesTd.querySelectorAll('span');
      var d1 = document.createElement('div'); d1.className = 'mq-date'; d1.innerHTML = 'יצירה ' + (sp[0] ? sp[0].outerHTML : '');
      var d2 = document.createElement('div'); d2.className = 'mq-date'; d2.innerHTML = 'יעד ' + (sp[1] ? sp[1].outerHTML : '');
      if(status){ grid.insertBefore(d1, status); grid.insertBefore(d2, status); }
      else { var empty = document.createElement('div'); grid.appendChild(d1); grid.appendChild(d2); grid.appendChild(empty); }
      datesTd.parentElement.classList.add('mq-hide');
    }
    // "|" entre sujet et zone : au centre horizontal du popup
    var pipe = box.querySelector('span.fontSize20.colorRed');
    var strong = pipe ? pipe.parentElement : null;
    if(strong && !strong.classList.contains('mq-pipe-line')){
      var parts = strong.innerHTML.split(pipe.outerHTML);
      if(parts.length === 2){
        strong.classList.add('mq-pipe-line');
        strong.innerHTML = '<span class="mq-subj">' + parts[0].replace(/&nbsp;\s*$/, '') + '</span>' + pipe.outerHTML + '<span class="mq-area">' + parts[1].replace(/^\s*&nbsp;/, '') + '</span>';
      }
    }
  }
  if(box) new MutationObserver(fixGrid).observe(box, {childList:true, subtree:true});

  // description = bloc centre sous "sujet | zone", aussi large que sa ligne la plus longue, texte aligne a droite dedans
  // (courte : centree ; plusieurs lignes courtes : centrees ensemble ; longue : toute la largeur, comme avant)
  function centerDesc(){
    if(!box) return;
    // le navigateur ferme le <p> avant les <div> : le texte est dans la case, a cote du <p> vide
    var td = box.querySelector('tr.alignRight.bgColor-ebf4f5 > td');
    if(!td || td.querySelector('.mq-desc-block')) return;
    var target = td.querySelector('div[style*="position:relative"]');   // avec journal : la description seule
    if(!target){                                                        // sans journal : on regroupe le texte
      target = document.createElement('div');
      Array.prototype.slice.call(td.childNodes).forEach(function(n){ if(!(n.tagName === 'P' && !n.textContent.trim())) target.appendChild(n); });
      td.appendChild(target);
    }
    target.classList.add('mq-desc-block');
  }

  // journal des remarques (log meeting updates) : l'icone de repli passe dans sa zone, au coin gauche, centree verticalement,
  // et replie CE journal (plus le bloc מעקב, qui reste toujours affiche)
  function fixLog(){
    if(!box) return;
    var td = box.querySelector('tr.alignRight.bgColor-ebf4f5 > td');
    if(!td || td.classList.contains('mq-log-done')) return;
    td.classList.add('mq-log-done');
    var old = td.querySelector('span.cursor-pointer[onclick*="row_log_meeting_tracking"]');
    if(old) old.remove();
    var tr = document.getElementById('row_log_meeting_tracking'); if(tr) tr.style.display = '';
    var list = td.querySelector('div[style*="max-height:90px"]');
    if(!list) return;
    var zone = list.parentElement; zone.classList.add('mq-log-zone');
    var t = document.createElement('span'); t.className = 'mq-log-toggle'; t.title = 'הצג/הסתר עדכונים';
    t.innerHTML = '<img src="images/tracking-toggle-icon.png" width="18" height="18" alt="">';
    t.onclick = function(e){ e.stopPropagation(); zone.classList.toggle('mq-log-closed'); };
    zone.appendChild(t);
  }
  if(box) new MutationObserver(function(){ fixLog(); centerDesc(); }).observe(box, {childList:true});

  // (le bouton apparait seulement pour les taches de מה חדש, comme sur le vrai site)
})();
</script>
