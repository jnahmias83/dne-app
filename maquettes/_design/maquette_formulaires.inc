<style id="maquette-formulaires">
/* ===== MAQUETTE - add_chapter / add_task / add_responsible : cadres moins larges - aucun fichier du site modifie ===== */
/* colonnes vides de cote masquees, carte centree a une largeur fixe selon le formulaire */
.mq-form-row > .col-1, .mq-form-row > .col-3 { display:none !important; }
.mq-form-row > .card { flex:0 0 auto !important; max-width:100% !important; margin:0 auto !important; }
.mq-form-row.mq-w-chapitre > .card { width:420px !important; }
.mq-form-row.mq-w-type > .card { width:560px !important; }
.mq-form-row.mq-w-resp > .card { width:520px !important; }
/* formulaire en 2 colonnes (add_task) : les blocs prennent toute la largeur de la carte au lieu de la moitie */
.mq-form-row .card-body > .flex.width50Percents { width:100% !important; padding:0 10px; box-sizing:border-box; gap:16px; }
.mq-form-row.mq-w-type .card-body input[type="text"] { width:100% !important; box-sizing:border-box; }
.mq-form-row.mq-w-type .card-body input[type="color"] { width:100% !important; max-width:200px; }
.mq-form-row.mq-w-type .card-body .width80Percents.border-black { width:100% !important; box-sizing:border-box; }
/* type de tache : titre EN FACE de son champ (colonne centree), champs couleur centres a 150px ; smartphone = une colonne (comme statut_ajout) */
.mq-form-row.mq-w-type .card-body > .flex.width50Percents > div { text-align:center; flex:1 1 0; min-width:0; }
.mq-form-row.mq-w-type .card-body > .flex.width50Percents > div > input[type="color"] { display:block; width:150px !important; max-width:100%; margin-left:auto !important; margin-right:auto !important; }
.mq-form-row.mq-w-type .card-body > .flex.width50Percents > div > .border-black { text-align:right; }
/* tous les titres a la meme taille ("שם" a la classe text-size du site qui le rapetisse selon l'ecran) ; champs texte 220px maxi, centres */
.mq-form-row.mq-w-type .card-body > .flex.width50Percents > div > strong { font-size:16px !important; }
.mq-form-row.mq-w-type .card-body > .flex.width50Percents > div > input[type="text"] { display:block; width:150px !important; max-width:100% !important; margin-left:auto !important; margin-right:auto !important; }
@media (max-width:560px){
  .mq-form-row.mq-w-type .card-body > .flex.width50Percents { flex-direction:column; gap:14px; }
  .mq-form-row.mq-w-type .card-body > .flex.width50Percents > div { flex:0 0 auto; width:100% !important; }
}
/* "הבא : הגדרות" (ajout de projet, צוות הפרוייקט) : il etait pose par-dessus a gauche ; dans la carte plus etroite il chevauchait
   "ספק נוסף" -> meme ligne, toujours colle a gauche comme avant, les 2 autres boutons a droite (plus de chevauchement) */
.mq-form-row div:has(> #next_step_btn_2) { display:flex; justify-content:space-between; align-items:center; gap:10px; padding:0 10px; }
.mq-form-row #next_step_btn_2 { position:static !important; transform:none !important; }
</style>
<script>
(function(){
  // MAQUETTE SEULEMENT : les boutons d'enregistrement n'enregistrent rien (pose au chargement complet, apres les scripts de la page)
  window.addEventListener('load', function(){
    if(window.jQuery){
      var realAjax = jQuery.ajax;
      jQuery.ajax = function(opts){
        if(/chapter_insert\.php|task_insert\.php|responsible_insert\.php/.test((opts && opts.url) || '')) return jQuery.Deferred().promise();
        return realAjax.apply(this, arguments);
      };
    }
  });
  // repere la rangee de la carte et la page
  var card = document.querySelector('.card.border-frame');
  if(!card || !card.parentElement) return;
  var row = card.parentElement; row.classList.add('mq-form-row');
  if(document.getElementById('add_responsible_form_row')) row.classList.add('mq-w-resp');
  else if(card.querySelector('input[type="color"]')) row.classList.add('mq-w-type');
  else row.classList.add('mq-w-chapitre');
})();
</script>
