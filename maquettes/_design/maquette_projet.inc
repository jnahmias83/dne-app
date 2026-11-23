<style id="maquette-projet">
/* ===== MAQUETTE - add_project.php (נתוני הפרוייקט, depuis le menu) - aucun fichier du site modifie ===== */
/* cadre moins large : la carte ne prend plus 10/12 de la largeur, elle est centree a 660px maxi (colonnes vides de cote masquees) */
.mq-proj-row > .col-1 { display:none !important; }
.mq-proj-row > .card { flex:0 0 auto !important; width:660px !important; max-width:100% !important; margin:0 auto !important; }

/* champs moins larges : chacun prend sa colonne (environ 190px) au lieu de 90% d'un tiers d'ecran */
.mq-proj-row input[type="text"] { width:100% !important; max-width:190px; box-sizing:border-box; }
.mq-proj-row .width-one-of-three { padding-left:6px !important; padding-right:6px !important; box-sizing:border-box; }

/* titres EN FACE de leur champ : les rangees "titres" et "champs" du site deviennent une seule grille de 3 colonnes
   (display:contents) -> chaque titre est exactement au-dessus de son champ. Aucun champ ni id ne change. */
.card-body:has(#project_name_he) { display:grid !important; grid-template-columns:repeat(3, minmax(0,1fr)); column-gap:14px; row-gap:6px; }
.card-body:has(#project_name_he) > .flex { display:contents !important; }
.card-body:has(#project_name_he) > .flex > div { width:auto !important; max-width:none !important; padding:0 !important; margin:0 !important; text-align:center !important; }
.card-body:has(#project_name_he) > .flex:nth-child(odd) > div { align-self:end; }          /* titres : alignes en bas (meme si "(up to 7 characters)") */
.card-body:has(#project_name_he) > .flex:nth-child(even) > div { align-self:start; }       /* champs */
.card-body:has(#project_name_he) > .flex:nth-child(3) > div { margin-top:16px !important; } /* 2e groupe (יזם) */
.card-body:has(#project_name_he) > .flex > div .row { margin:0 !important; }
.card-body:has(#project_name_he) > .flex > div .col-12 { padding:0 !important; }
.card-body:has(#project_name_he) > .flex > div input[type="text"] { display:block; width:100% !important; max-width:190px !important; margin:0 auto !important; box-sizing:border-box; }
.card-body:has(#project_name_he) > .row { grid-column:1 / -1; }
/* smartphone (portrait) : une seule colonne, chaque titre juste au-dessus de son champ */
@media (max-width:600px){
  .card-body:has(#project_name_he) { grid-template-columns:1fr; }
  .card-body:has(#project_name_he) > .flex:nth-child(1) > div:nth-child(1){order:1} .card-body:has(#project_name_he) > .flex:nth-child(2) > div:nth-child(1){order:2}
  .card-body:has(#project_name_he) > .flex:nth-child(1) > div:nth-child(2){order:3} .card-body:has(#project_name_he) > .flex:nth-child(2) > div:nth-child(2){order:4}
  .card-body:has(#project_name_he) > .flex:nth-child(1) > div:nth-child(3){order:5} .card-body:has(#project_name_he) > .flex:nth-child(2) > div:nth-child(3){order:6}
  .card-body:has(#project_name_he) > .flex:nth-child(3) > div:nth-child(1){order:7} .card-body:has(#project_name_he) > .flex:nth-child(4) > div:nth-child(1){order:8}
  .card-body:has(#project_name_he) > .flex:nth-child(3) > div:nth-child(2){order:9} .card-body:has(#project_name_he) > .flex:nth-child(4) > div:nth-child(2){order:10}
  .card-body:has(#project_name_he) > .flex:nth-child(3) > div:nth-child(3){order:11} .card-body:has(#project_name_he) > .flex:nth-child(4) > div:nth-child(3){order:12}
  .card-body:has(#project_name_he) > .row { order:20; }
  .card-body:has(#project_name_he) > .flex:nth-child(odd) > div { margin-top:12px !important; }
  .card-body:has(#project_name_he) > .flex:nth-child(1) > div:nth-child(1) { margin-top:0 !important; }
  .card-body:has(#project_name_he) > .flex > div input[type="text"] { max-width:260px !important; }
}
/* proposition : "שפת ברירת מחדל" avec un globe ; liste arrondie avec le globe dedans */
.mq-lang { display:inline-flex; align-items:center; gap:10px; direction:rtl; }
.mq-lang-label { display:inline-flex; align-items:center; gap:7px; font-weight:700; color:#2d3a45; }
.mq-lang-label i { color:#1a5276; font-size:17px; }
.mq-lang-box { position:relative; display:inline-flex; align-items:center; }
.mq-lang-box i { position:absolute; right:10px; color:#1a5276; font-size:14px; pointer-events:none; }
.mq-lang-box select#project_lang { -webkit-appearance:none; appearance:none; margin:0 !important; height:34px !important;
  padding:0 32px 0 28px; border:1.5px solid #c9d7e3; border-radius:999px; background:#fff; color:#1a5276; font-weight:700; cursor:pointer;
  background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%231a5276'/%3E%3C/svg%3E");
  background-repeat:no-repeat; background-position:left 12px center; }
.mq-lang-box select#project_lang:hover { border-color:#1a5276; }
.mq-lang-box select#project_lang:focus { outline:none; border-color:#1a5276; box-shadow:0 0 0 3px rgba(26,82,118,.15); }
</style>
<script>
(function(){
  // MAQUETTE SEULEMENT : "שמור" n'enregistre rien (pose au chargement complet, apres les scripts de la page)
  window.addEventListener('load', function(){
    if(window.jQuery){
      var realAjax = jQuery.ajax;
      jQuery.ajax = function(opts){
        if(/project_insert\.php/.test((opts && opts.url) || '')){ jQuery('#save_btn').prop('disabled', false); return jQuery.Deferred().promise(); }
        return realAjax.apply(this, arguments);
      };
    }
  });

  // repere la rangee de la carte (col-1 / col-10 card / col-1)
  var card = document.querySelector('.card.border-frame');
  if(card && card.parentElement) card.parentElement.classList.add('mq-proj-row');

  // proposition langue : globe devant le libelle + liste arrondie avec globe (la meme liste #project_lang, deplacee)
  var sel = document.getElementById('project_lang');
  if(sel){
    var cell = sel.parentElement, lab = cell.querySelector('strong');
    var wrap = document.createElement('span'); wrap.className = 'mq-lang';
    wrap.innerHTML = '<span class="mq-lang-label">' + (lab ? lab.textContent : 'שפת ברירת מחדל') + '</span>';
    var box = document.createElement('span'); box.className = 'mq-lang-box';
    box.innerHTML = '<i class="fa-solid fa-globe"></i>';
    box.appendChild(sel); wrap.appendChild(box);
    cell.innerHTML = ''; cell.appendChild(wrap);
  }
})();
</script>
