<style id="maquette-statut-form">
/* ===== MAQUETTE - add_progress_status.php - aucun fichier du site modifie ===== */
/* cadre moins large : la carte ne prend plus 10/12 de l'ecran, elle est centree a 560px maxi (colonnes vides de cote masquees) */
.mq-form-row > .col-1 { display:none !important; }
.mq-form-row > .card { flex:0 0 auto !important; width:560px !important; max-width:100% !important; margin:0 auto !important; }
/* les blocs interieurs (2 colonnes) prennent toute la largeur de la carte au lieu de la moitie */
.mq-form-row .card-body > .flex.width50Percents { width:100% !important; padding:0 10px; box-sizing:border-box; gap:16px; }
/* champs et listes a cocher : la largeur de leur colonne */
.mq-form-row .card-body input[type="text"] { width:100% !important; box-sizing:border-box; }
.mq-form-row .card-body input[type="color"] { width:100% !important; max-width:200px; }
.mq-form-row .card-body .width80Percents.border-black { width:100% !important; box-sizing:border-box; }
/* titre EN FACE de son champ : chaque colonne est centree (titre + champ), les champs de couleur centres comme les champs texte */
.mq-form-row .card-body > .flex.width50Percents > div { text-align:center; flex:1 1 0; min-width:0; }
.mq-form-row .card-body > .flex.width50Percents > div > input[type="color"] { display:block; width:150px !important; max-width:100%; margin-left:auto !important; margin-right:auto !important; }
.mq-form-row .card-body > .flex.width50Percents > div > .border-black { text-align:right; }   /* listes a cocher : texte a droite (hebreu) */
/* tous les titres a la meme taille ; champs texte 220px maxi, centres */
.mq-form-row .card-body > .flex.width50Percents > div > strong { font-size:16px !important; }
.mq-form-row .card-body > .flex.width50Percents > div > input[type="text"] { display:block; max-width:220px !important; margin-left:auto !important; margin-right:auto !important; }
/* smartphone : les 2 colonnes l'une sous l'autre */
@media (max-width:560px){
  .mq-form-row .card-body > .flex.width50Percents { flex-direction:column; gap:14px; }
  .mq-form-row .card-body > .flex.width50Percents > div { flex:0 0 auto; width:100% !important; }
}
</style>
<script>
(function(){
  // MAQUETTE SEULEMENT : "שמור" n'enregistre rien (pose au chargement complet, apres les scripts de la page)
  window.addEventListener('load', function(){
    if(window.jQuery){
      var realAjax = jQuery.ajax;
      jQuery.ajax = function(opts){
        if(/progress_status_insert\.php/.test((opts && opts.url) || '')) return jQuery.Deferred().promise();
        return realAjax.apply(this, arguments);
      };
    }
  });
  // repere la rangee de la carte (col-1 / col-10 card / col-1)
  var card = document.querySelector('.card.border-frame');
  if(card && card.parentElement) card.parentElement.classList.add('mq-form-row');
})();
</script>
