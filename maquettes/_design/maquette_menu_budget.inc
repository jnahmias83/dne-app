<style id="maquette-menu-budget">
/* ===== MAQUETTE - menu budget avec la meme logique que le menu du projet (menu_tasks.php) =====
   icone au-dessus du libelle, page courante en surbrillance, hamburger en 3 paliers ; memes liens */
.mq-bt { height:auto !important; min-height:60px; display:flex; align-items:center; flex-wrap:wrap; padding:0 20px; direction:rtl; position:relative;
  width:100vw; max-width:100vw; box-sizing:border-box; }   /* la barre reste a la largeur de l'ecran meme si un tableau elargit la page */
.mq-bt .menu-list, .mq-bt .mq-preview { list-style:none; display:flex; margin:0; padding:0; align-items:center; flex-grow:1; justify-content:center; gap:10px; color:#fff; }
.mq-bt .mq-preview { display:none; }
.mq-bt .menu-list li a, .mq-bt .mq-preview li a { display:flex; flex-direction:column; align-items:center; gap:3px; color:#fff; text-decoration:none;
  font-size:14px; padding:4px 8px; border-radius:8px; white-space:nowrap; }
.mq-bt .menu-list li a i, .mq-bt .mq-preview li a i { font-size:17px; }
.mq-bt .menu-list li a:hover, .mq-bt .mq-preview li a:hover { background-color:rgba(255,255,255,0.18); }
.mq-bt .menu-item-current { background-color:rgba(255,255,255,0.18); font-weight:700; }
.mq-bt li.separator { color:#fff; user-select:none; }
.mq-bt .mq-actions { display:flex; align-items:center; gap:10px; flex-shrink:0; margin-right:auto; }   /* toujours colles a gauche, comme le menu du projet */
.mq-bt .mq-actions a { margin-left:0 !important; }
/* boutons ספקים / משימות : meme taille que ספקים / תקציב du menu du projet a toutes les largeurs
   (l'ancien menu budget les reduisait jusqu'a 7px sur petit ecran : illisibles) */
.mq-bt .mq-actions .btn-tasks, .mq-bt .mq-actions .btn-attach-suppliers { font-size:14px !important; padding:5px 15px !important; border:1px solid #fff; border-radius:10px; }
@media (max-width:600px){ .mq-bt .mq-actions .btn-tasks, .mq-bt .mq-actions .btn-attach-suppliers { padding:5px 5px !important; } }
.mq-ham { display:none; background:none; border:none; color:#fff; font-size:26px; cursor:pointer; padding:0 8px; }
/* palier 2 : 3 premiers + hamburger */
.mq-bt.mq-preview-on .mq-preview { display:flex; }
.mq-bt.mq-preview-on .menu-list:not(.open), .mq-bt.mq-collapsed .menu-list:not(.open) { display:none; }
.mq-bt.mq-preview-on .mq-ham, .mq-bt.mq-collapsed .mq-ham { display:block; }
/* menu ouvert (paliers 2 et 3) : memes regles que menu_tasks.php (.topbar-preview .menu-list.open) :
   petit panneau a la largeur des libelles, colle a droite sous la barre, coins bas arrondis */
.mq-bt .menu-list.open { display:flex; flex-direction:column; align-items:flex-end; position:absolute; top:100%; right:20px; left:auto; width:auto;
  background-color:inherit; z-index:1000; padding:10px 15px; border-radius:0 0 10px 10px; gap:8px; flex-wrap:nowrap; flex-grow:0; }
.mq-bt .menu-list.open li.separator { display:none; }
.mq-bt .menu-list.open li { width:100%; }
.mq-bt .menu-list.open li a { flex-direction:row; justify-content:flex-start; width:100%; box-sizing:border-box; gap:8px; padding:6px 10px; }
.mq-bt.measuring .menu-list { display:flex !important; }
</style>
<script>
(function(){
  var bar = Array.prototype.find.call(document.querySelectorAll('.topbar'), function(t){ return t.querySelector('.btn-tasks'); });
  if(!bar) return;
  var ul = bar.querySelector('ul.menu-list');
  var ICONS = { 'budget.php':'fa-coins', 'orders.php':'fa-file-signature', 'accounts.php':'fa-file-invoice', 'payments.php':'fa-money-bill-wave',
    'payments_wires.php':'fa-money-check', 'payments_missing_invoice.php':'fa-triangle-exclamation', 'budget_costs_eval.php':'fa-calculator' };
  // page courante (dans la maquette : d'apres le nom du fichier de maquette)
  var MAP = { 'budget':'budget.php', 'commandes':'orders.php', 'commande_form':'orders.php', 'comptes':'accounts.php', 'compte_form':'accounts.php',
    'comptes_paiements':'budget.php', 'paiements':'payments.php', 'paiement_form':'payments.php', 'paiements_virements':'payments_wires.php',
    'paiements_sans_facture':'payments_missing_invoice.php', 'budget_estimations':'budget_costs_eval.php', 'estimation_ajout':'budget_costs_eval.php', 'estimation_form':'budget_costs_eval.php', 'menu_budget':'budget.php' };
  var me = (location.pathname.split('/').pop() || '').replace('.html', '');
  var current = MAP[me] || (location.pathname.split('/').pop() || '');
  ul.querySelectorAll('li > a').forEach(function(a){
    var page = (a.getAttribute('href') || '').split('?')[0];
    var label = a.textContent.trim();
    a.innerHTML = '<i class="fa-solid ' + (ICONS[page] || 'fa-circle') + '"></i><span></span>';
    a.querySelector('span').textContent = label;
    if(page === current) a.classList.add('menu-item-current');
  });
  bar.classList.add('mq-bt');
  // boutons ספקים / משימות regroupes a gauche (memes liens)
  var act = document.createElement('div'); act.className = 'mq-actions';
  bar.querySelectorAll(':scope > a').forEach(function(a){ act.appendChild(a); });
  bar.appendChild(act);
  // hamburger + apercu des 3 premiers elements
  var ham = document.createElement('button'); ham.type = 'button'; ham.className = 'mq-ham'; ham.setAttribute('aria-label', 'menu'); ham.innerHTML = '&#9776;';
  bar.insertBefore(ham, ul);
  var prev = document.createElement('ul'); prev.className = 'mq-preview';
  var items = ul.querySelectorAll(':scope > li');
  for(var k = 0, n = 0; k < items.length && n < 3; k++){ var c = items[k].cloneNode(true); prev.appendChild(c); if(!items[k].classList.contains('separator')) n++; }
  if(prev.lastElementChild && prev.lastElementChild.classList.contains('separator')) prev.removeChild(prev.lastElementChild);
  bar.insertBefore(prev, ul);

  // meme logique que checkTasksMenuOverflow() de menu_tasks.php
  function oneLine(){
    var kids = Array.prototype.filter.call(bar.children, function(el){ return getComputedStyle(el).display !== 'none'; });
    if(!kids.length) return true;
    var c0 = kids[0].offsetTop + kids[0].offsetHeight / 2;
    return kids.every(function(el){ return Math.abs((el.offsetTop + el.offsetHeight / 2) - c0) < 5; });
  }
  function check(){
    var wasOpen = ul.classList.contains('open');
    bar.classList.remove('mq-collapsed', 'mq-preview-on'); ul.classList.remove('open');
    if(oneLine()) return;                                   // 1. tout tient
    // ecran tres etroit (telephone) : hamburger seul, aucun element visible (comme le menu du projet)
    if(window.innerWidth < 600){ bar.classList.add('mq-collapsed'); if(wasOpen) ul.classList.add('open'); return; }
    bar.classList.add('mq-preview-on');
    if(oneLine()){ if(wasOpen) ul.classList.add('open'); return; }   // 2. 3 premiers + hamburger
    bar.classList.remove('mq-preview-on'); bar.classList.add('mq-collapsed');   // 3. hamburger seul
    if(wasOpen) ul.classList.add('open');
  }
  window.addEventListener('load', check); window.addEventListener('resize', check); setInterval(check, 500); check();
  document.addEventListener('click', function(e){
    if(e.target === ham || ham.contains(e.target)) ul.classList.toggle('open');
    else if(!ul.contains(e.target)) ul.classList.remove('open');
  });
})();
</script>
