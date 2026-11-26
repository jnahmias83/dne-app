<style id="maquette-maakav-live">
/* ===== MAQUETTE VIVANTE - popup מעקב (#modalTaskTracking) - aucun fichier du site modifie ===== */
/* (1) bandeau couleur מעקב + texte blanc + cadre du popup */
#modalTaskTracking .modal-content { border:2px solid #d93a3a !important; border-radius:18px !important; overflow:hidden; }
#modalTaskTracking .modal-header { background-color:#d93a3a !important; }
#modalTaskTracking .modal-header .modal-title, #modalTaskTracking .modal-header .modal-title * { color:#fff !important; }
/* icone de la cible en blanc (elle etait rouge sur le bandeau rouge -> invisible) */
#modalTaskTracking .modal-header .modal-title img { filter:brightness(0) invert(1); }
/* (6) titre de la tache sur fond blanc */
#modalTaskTracking .subtitle { background:#fff; }
/* (2) liste אחראי מעקב : teinte מעקב */
#modalTaskTracking #users { background:#fde6e6 !important; border:1px solid #f3b5b5 !important; border-radius:8px; padding:3px 12px; font-weight:700; }
/* (3) colonne "עדכון" (icone + mot) supprimee : l'icone passe a cote du titre MemoChat */
#modalTaskTracking .row:has(#task_active_remarks_tracking) > .col-2 { display:none !important; }
#modalTaskTracking .row:has(#task_active_remarks_tracking) > .col-10 { flex:0 0 100%; max-width:100%; }
#modalTaskTracking .mq-memo-title { display:flex; align-items:center; justify-content:center; gap:8px; color:#1a5276; font-weight:700; font-size:16px; direction:ltr; margin:4px 0 6px; }
/* (4) remarques sans cadre, 2e ligne alignee sur la 1re (pas sous la date) */
/* (alignement de la 2e ligne et badges 22px : faits par le site depuis le 26/11, plus de regle ici) */
#modalTaskTracking [id^="div_log_meeting_tracking_"] { border:none !important; padding:2px 0 !important; }
/* (7) champ sans cadre ni texte d'aide, ligne rouge en bas ; Clear / ע|EN au-dessus du champ (place d'origine) */
#modalTaskTracking .mq-input-box { border:none !important; background:#f4f5f7 !important; border-bottom:2px solid #d93a3a !important; border-radius:10px 10px 4px 4px; min-height:48px !important; padding:10px 12px !important; }
#modalTaskTracking .mq-input-box:focus-within { background:#fff !important; border-bottom-width:3px !important; }
/* zone editable : prend toute la place du cadre (sinon hauteur 0 une fois le texte d'aide retire) */
#modalTaskTracking #new_remark { display:block; width:100%; min-height:28px; outline:none; cursor:text; }
/* saisie en gris fonce (au lieu du rouge de la classe "red" du site), curseur assorti */
#modalTaskTracking #new_remark, #modalTaskTracking #new_remark * { color:#333 !important; caret-color:#333; }
#modalTaskTracking .mq-input-box { cursor:text !important; }
#modalTaskTracking .mq-under { margin-top:6px; margin-bottom:0 !important; }
</style>
<script>
(function(){
  var modal = document.getElementById('modalTaskTracking');
  if(!modal) return;
  var box = document.getElementById('new_remark');
  if(box){
    box.setAttribute('data-placeholder', '');                     // (7) plus de texte d'aide
    var frame = box.parentElement; frame.classList.add('mq-input-box');
    frame.addEventListener('click', function(e){ if(e.target === frame) box.focus(); }); // clic n'importe ou dans le cadre
    // (7) la ligne "ע | EN ... Clear" reste AU-DESSUS du champ (place d'origine) : plus deplacee dessous
  }
  // (8) "שמור" -> "מעקב" sous la cible rouge
  modal.querySelectorAll('button').forEach(function(b){
    if(b.innerHTML.indexOf('red-target-icon-transparent') > -1) b.innerHTML = b.innerHTML.replace('שמור','מעקב');
  });
  // (3) titre "עדכונים" -> icone crayon + "MemoChat" (le contenu est charge en AJAX a chaque ouverture)
  var list = document.getElementById('task_active_remarks_tracking');
  function retitle(){
    var t = list && list.firstElementChild;
    if(t && !t.classList.contains('mq-memo-title') && /עדכונים|Updates/.test(t.textContent)){
      t.className = 'mq-memo-title';
      t.innerHTML = '<img src="images/edit-button.svg" width="18" height="18" alt=""><span>MemoChat</span>';
    }
  }
  if(list) new MutationObserver(retitle).observe(list, {childList:true, subtree:true});
})();
</script>
