<style id="maquette-np-fournisseurs">
/* ===== MAQUETTE - add_sup_to_proj.php (nouveau projet, etape 4) - aucun fichier du site modifie - meme design que les autres maquettes ===== */
html, body { background:#f6f5f1 !important; }

/* cartes : bordure, coins 15px, en-tete bleu = libelle de la liste */
.mq-card { width:fit-content; max-width:100%; margin:10px auto 0; border:1px solid #C5B8AA; border-radius:15px; background:#f2f6f9; overflow:hidden; padding:0 !important; }
.mq-page-title { display:flex; align-items:center; justify-content:center; gap:10px; background:#1a5276; color:#fff; font-weight:700; font-size:20px; padding:8px 28px; direction:rtl; }
.mq-card-body { padding:14px 12px; overflow-x:auto; }
.mq-hidden { display:none !important; }

/* tableaux : sans bordure, en-tete #dedede texte bleu, une ligne sur deux en commencant par la foncee */
#designers_list, #suppliers_list { border-collapse:separate !important; border-spacing:0 !important; border:none !important; background:#fff; margin:0 auto; }
#designers_list th, #designers_list td, #suppliers_list th, #suppliers_list td { border:none !important; border-bottom:1px solid #eef0f3 !important; }
#designers_list tr.bgColorSilver th, #suppliers_list tr.bgColorSilver th { background:#dedede !important; color:#1a5276 !important; font-weight:700; height:44px; padding:4px 10px !important; }
#designers_list tr.height35 td, #suppliers_list tr.height35 td { height:44px; padding:0 10px !important; color:#2d3a45; }
#designers_list tr.height35:nth-child(even) td, #suppliers_list tr.height35:nth-child(even) td { background:#a9c6e0 !important; }
#designers_list tr.height35:nth-child(odd) td, #suppliers_list tr.height35:nth-child(odd) td { background:#d4e4f2 !important; }
#designers_list td a, #suppliers_list td a { color:#1a5276 !important; font-weight:600; }
#designers_list td img, #suppliers_list td img { width:18px; height:18px; padding:6px; box-sizing:content-box; border-radius:50%; opacity:.75; transition:background .15s, opacity .15s; }
#designers_list td img:hover, #suppliers_list td img:hover { opacity:1; background:#fde6e6; }

/* boutons "הוסף מתכנן חדש" / "הוסף ספק חדש" : sans fond, texte et bord bleus, survol bleu clair ; listes arrondies */
#save_des_btn, #save_sup_btn { background:transparent !important; color:#1a5276 !important; border:1.5px solid #1a5276 !important; border-radius:999px !important;
  padding:7px 18px !important; font-weight:700; font-size:15px; box-shadow:none !important; height:auto !important; }
#save_des_btn:hover, #save_sup_btn:hover { background:#e3eef6 !important; }
/* langue : meme liste arrondie avec un globe que dans la maquette "נתוני הפרוייקט" */
.mq-lang-box { position:relative; display:inline-flex; align-items:center; direction:rtl; }
.mq-lang-box i { position:absolute; right:10px; color:#1a5276; font-size:14px; pointer-events:none; }
.mq-lang-box select#lang { -webkit-appearance:none; appearance:none; width:auto !important; margin:0 !important; height:34px !important;
  padding:0 32px 0 30px !important; min-width:120px; border:1.5px solid #c9d7e3 !important; border-radius:999px; background-color:#fff; color:#1a5276; font-weight:700; cursor:pointer;
  background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%231a5276'/%3E%3C/svg%3E");
  background-repeat:no-repeat; background-position:left 12px center; }
.mq-lang-box select#lang:hover { border-color:#1a5276 !important; }
.mq-lang-box select#lang:focus { outline:none; border-color:#1a5276 !important; box-shadow:0 0 0 3px rgba(26,82,118,.15); }
#designers_domain, #suppliers_domain { border:1.5px solid #c9d7e3 !important; border-radius:999px !important; background:#fff; color:#1a5276; height:38px !important; padding:0 14px !important; }
</style>
<script>
(function(){
  // MAQUETTE SEULEMENT : rien n'est enregistre (ajout / retrait d'un fournisseur) ; pose au chargement complet
  // langue : la meme liste #lang, mise dans une pilule avec un globe (memes choix עברית / English, meme fonctionnement)
  var sel = document.getElementById('lang');
  if(sel){
    var box = document.createElement('span'); box.className = 'mq-lang-box';
    box.innerHTML = '<i class="fa-solid fa-globe"></i>';
    sel.parentNode.insertBefore(box, sel); box.appendChild(sel);
  }

  window.addEventListener('load', function(){
    window.removeSupplierFromProject = function(){ return false; };
    if(window.jQuery){
      var realAjax = jQuery.ajax;
      jQuery.ajax = function(opts){
        if(/sup_from_proj_delete\.php|sup_to_proj_insert\.php/.test((opts && opts.url) || '')) return jQuery.Deferred().promise();
        return realAjax.apply(this, arguments);
      };
    }
    // cartes (apres le remplissage des libelles par la page)
    [['designers_list', 'designers_list_label', 'fa-user-tie'], ['suppliers_list', 'suppliers_list_label', 'fa-truck']].forEach(function(c){
      var table = document.getElementById(c[0]); if(!table) return;
      var label = document.getElementById(c[1]);
      var card = document.createElement('div'); card.className = 'mq-card';
      var ttl = document.createElement('div'); ttl.className = 'mq-page-title';
      ttl.innerHTML = '<i class="fa-solid ' + c[2] + '"></i><span></span>';
      // le libelle est rempli par la page (hebreu / anglais) : l'en-tete le recopie et suit ses changements
      var span = ttl.querySelector('span');
      var copy = function(){ span.textContent = label ? label.textContent.trim() : ''; };
      copy();
      if(label) new MutationObserver(copy).observe(label, {childList:true, characterData:true, subtree:true});
      var body = document.createElement('div'); body.className = 'mq-card-body';
      var holder = table.closest('.table-responsive') || table;
      holder.parentNode.insertBefore(card, holder);
      card.appendChild(ttl); card.appendChild(body); body.appendChild(holder);
      if(label && label.parentElement) label.parentElement.classList.add('mq-hidden');   // libelle deplace dans l'en-tete
    });
  });
})();
</script>
