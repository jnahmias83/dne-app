<style id="maquette-sc">
/* ===== MAQUETTE - tous les boutons Enregistrer / Annuler / Fermer (pages ET popups) : meme style partout =====
   enregistrer = fond bleu du site (#218FD6, bgColorBlue), annuler/fermer = fond noir (bgColorBlack), texte blanc, forme arrondie
   seule l'apparence change : memes boutons, memes id, memes actions */
/* titre des popups de tache (sujet | lieu) : la barre rouge au MILIEU de la largeur du popup,
   sujet colle a la barre d'un cote, lieu colle de l'autre (3 colonnes egales : 1fr | auto | 1fr) */
.subtitle .mq-sub-line { display:grid; grid-template-columns:1fr auto 1fr; align-items:center; column-gap:10px; width:100%; }
.subtitle .mq-sub-line > .mq-sub-a { text-align:end; }
.subtitle .mq-sub-line > .mq-sub-b { text-align:start; }
.subtitle .mq-sub-line > span.fontSize20.colorRed { line-height:1; }
/* couleurs = celles du tableau de bord (projects.php) : chapitre vert gras (inchange), sujet bleu #1A5276 gras, lieu bleu normal ; la barre | reste ROUGE (couleur d'origine du popup) */
.subtitle .mq-sub-line > .mq-sub-a { color:#1A5276 !important; font-weight:700 !important; }
.subtitle .mq-sub-line > span.fontSize20.colorRed { font-weight:700 !important; }
.subtitle .mq-sub-line > .mq-sub-b { color:#1A5276 !important; font-weight:400 !important; }
/* trait sous le titre (sujet | lieu) : un peu d'air au-dessus */
.subtitle + hr.hr-popup { margin-top:14px !important; }
/* popup מעקב : le trait est dans la ligne suivante (row > col-12 > hr) */
.row:has(.subtitle) + .row > .col-12 > hr,
#modalTaskTracking form .row:has(.subtitle) + .row > .col-12 > hr { margin-top:14px !important; }   /* plus fort que "#modalTaskTracking form hr {3px}" du site */
html body .mq-sc.mq-sc.mq-sc.mq-sc.mq-sc { background:#218FD6 !important; color:#fff !important; border:1.5px solid #218FD6 !important; border-radius:999px !important;
  padding:6px 18px !important; font-weight:700 !important; font-size:14px !important; box-shadow:none !important; height:auto !important;
  width:auto !important; min-width:0 !important; line-height:1.4 !important; }
html body .mq-sc.mq-sc.mq-sc.mq-sc.mq-sc:hover:not(:disabled) { background:#1a7bbd !important; border-color:#1a7bbd !important; }
html body .mq-sc.mq-sc.mq-sc.mq-sc.mq-sc i { color:#fff !important; }
html body .mq-sc.mq-sc.mq-sc.mq-sc.mq-sc.mq-sc-cancel { background:#000 !important; border-color:#000 !important; color:#fff !important; }
html body .mq-sc.mq-sc.mq-sc.mq-sc.mq-sc.mq-sc-cancel:hover:not(:disabled) { background:#333 !important; border-color:#333 !important; }
html body .mq-sc.mq-sc.mq-sc.mq-sc.mq-sc:disabled { opacity:.4; }
/* bouton "שמור ושתף" : son icone de partage est une IMAGE DE FOND (.whatsapp-btn du site) -> on la remet par-dessus le bleu,
   avec la place a gauche pour l'icone (sinon "background" du bouton bleu l'efface) */
html body .mq-sc.mq-sc.mq-sc.mq-sc.mq-sc.whatsapp-btn, html body .mq-sc.mq-sc.mq-sc.mq-sc.mq-sc.whatsapp-btn:hover:not(:disabled) {
  background-image:url('images/share-icon.svg') !important; background-repeat:no-repeat !important; background-position:left 12px center !important;
  background-size:26px 19px !important; padding-left:44px !important; }
</style>
<script>
(function(){
  var SAVE = /^(\+\s*)?(שמור|שמירה|עדכן|save|update|ok|אישור|עבור לצוות ניהול|המשך|הבא)/i, CANCEL = /^(ביטול|סגור|בטל|cancel|close)/i;
  function mark(){
    document.querySelectorAll('input[type="button"], input[type="submit"], button, a.btn').forEach(function(b){
      if(b.classList.contains('mq-sc') || b.closest('.topbar') || b.classList.contains('close') || b.classList.contains('btn-close')) return;
      if(b.querySelector('img')) return;   // boutons-icones (grille d'actions des popups) : on n'y touche pas
      var txt = ((b.value || b.textContent) || '').replace(/\s+/g, ' ').trim();
      if(!txt) return;
      if(CANCEL.test(txt)){ b.classList.add('mq-sc', 'mq-sc-cancel'); }
      else if(SAVE.test(txt)){ b.classList.add('mq-sc'); }
    });
  }
  mark();
  window.addEventListener('load', function(){ mark(); setTimeout(mark, 800); });
  document.addEventListener('shown.bs.modal', mark);   // popups : quand ils s'ouvrent
  if(window.jQuery) jQuery(document).on('shown.bs.modal', mark);
})();
</script>
