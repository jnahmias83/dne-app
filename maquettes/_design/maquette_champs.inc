<style id="maquette-champs">
/* ===== MAQUETTE - memes regles de champs que les fiches du budget (commande, compte, paiement...) =====
   listes deroulantes et zones de texte : 300px ; champs simples (texte court, montant, date) : 150px ; tous centres ;
   la mise en colonnes de chaque fiche (1, 2 ou 3 colonnes) ne change pas. Seule l'apparence change. */
.card.border-frame .card-body select:not([multiple]):not([id*="lang"]),
.card.border-frame .card-body .chosen-container,
.card.border-frame .card-body .select2-container,
.card.border-frame .card-body textarea {
  width:300px !important; max-width:100% !important; box-sizing:border-box; display:block; margin-left:auto !important; margin-right:auto !important; }
.card.border-frame .card-body input.mq-full { width:150px !important; max-width:100% !important; box-sizing:border-box; display:block; margin-left:auto !important; margin-right:auto !important; }
.card.border-frame .card-body .card.width95Percents { margin-left:auto !important; margin-right:auto !important; }
/* liens d'action sans adresse (EN / HE...) : main au survol */
.card.border-frame .card-body a[onclick] { cursor:pointer; }
</style>
<script>
(function(){
  function mark(){
    document.querySelectorAll('.card.border-frame .card-body input').forEach(function(e){
      if(e.classList.contains('mq-full') || e.classList.contains('mq-small')) return;
      if(!/^(text|number|date|email|tel|password)$/.test(e.type)) return;
      // champ de recherche interne aux listes deroulantes : on n'y touche pas
      if(e.closest('.chosen-container, .select2-container, .select2-dropdown')) return;
      // plusieurs champs sur la meme ligne (ex. nom du PDF en morceaux) : la ligne reste telle quelle
      if(e.parentElement.querySelectorAll(':scope > input[type=text], :scope > input[type=number]').length > 1){ e.classList.add('mq-small'); return; }
      e.classList.add(e.getBoundingClientRect().width >= 140 ? 'mq-full' : 'mq-small');
    });
  }
  mark();
  window.addEventListener('load', function(){ mark(); setTimeout(mark, 800); });
})();
</script>
