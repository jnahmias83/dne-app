<style id="maquette-np-rapports">
/* ===== MAQUETTE - add_default_reports.php (nouveau projet, derniere etape) - aucun fichier du site modifie - meme design que les autres maquettes ===== */
html, body { background:#f6f5f1 !important; }

/* carte : bordure, coins 15px, en-tete bleu "הדוח''ות שלי" avec l'icone du menu */
.mq-card { width:fit-content; max-width:100%; margin:14px auto 0; border:1px solid #C5B8AA; border-radius:15px; background:#f2f6f9; overflow:hidden; padding:0 !important; flex:0 0 auto; }
.mq-card-body .default-reports-buttons { padding:0 !important; }
.mq-page-title { display:flex; align-items:center; justify-content:center; gap:10px; background:#1a5276; color:#fff; font-weight:700; font-size:20px; padding:8px 28px; direction:rtl; }
.mq-card-body { padding:16px 18px 8px; }
.mq-card-body .default-reports-buttons { display:flex; flex-wrap:wrap; justify-content:center; gap:10px; max-width:560px; }

/* boutons de creation : sans fond, texte et bord bleus, survol bleu clair ; deja crees (desactives) : estompes */
.default-reports-buttons input.btn, input.btn.mq-finish { background:transparent !important; color:#1a5276 !important; border:1.5px solid #1a5276 !important;
  border-radius:999px !important; padding:7px 16px !important; font-weight:700; font-size:14px; box-shadow:none !important; margin:0 !important; }
.default-reports-buttons input.btn:hover:not(:disabled), input.btn.mq-finish:hover:not(:disabled) { background:#e3eef6 !important; }
.default-reports-buttons input.btn:disabled { opacity:.4; cursor:default; }
input.btn.mq-finish { padding:7px 28px !important; margin-top:6px !important; }
</style>
<script>
(function(){
  // MAQUETTE SEULEMENT : la creation des rapports n'enregistre rien (pose au chargement complet)
  window.addEventListener('load', function(){
    if(window.jQuery){
      var realAjax = jQuery.ajax;
      jQuery.ajax = function(opts){
        if(/custom_report_insert\.php/.test((opts && opts.url) || '')) return jQuery.Deferred().promise();
        return realAjax.apply(this, arguments);
      };
    }
  });
  var box = document.querySelector('.default-reports-buttons');
  if(box){
    var card = document.createElement('div'); card.className = 'mq-card';
    var ttl = document.createElement('div'); ttl.className = 'mq-page-title';
    ttl.innerHTML = '<img src="images/my_reports_icon.png" width="20" height="20" class="menu-item-icon" alt=""><span>הדוח\'\'ות שלי</span>';
    var body = document.createElement('div'); body.className = 'mq-card-body';
    box.parentNode.insertBefore(card, box);
    card.appendChild(ttl); card.appendChild(body); body.appendChild(box);
  }
  // bouton "סיים" : meme style (meme action)
  Array.prototype.forEach.call(document.querySelectorAll('input.btn'), function(b){ if(/סיים/.test(b.value)) b.classList.add('mq-finish'); });
})();
</script>
