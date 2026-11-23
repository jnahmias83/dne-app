<style id="maquette-langue">
/* ===== MAQUETTE - liste de langue du tableau des taches : pilule arrondie avec globe (meme style que נתוני הפרוייקט) ===== */
/* seule l'apparence change : meme liste #select_lang, memes choix, meme fonctionnement */
.mq-lang-box { position:relative; display:inline-flex; align-items:center; direction:rtl; margin-bottom:10px; }
.mq-lang-box > i { position:absolute; right:10px; color:#1a5276; font-size:14px; pointer-events:none; }
.mq-lang-box select#select_lang { -webkit-appearance:none; appearance:none; margin:0 !important; height:32px !important; min-width:110px;
  padding:0 30px 0 28px !important; border:1.5px solid #c9d7e3 !important; border-radius:999px !important; background-color:#fff; color:#1a5276; font-weight:700; cursor:pointer;
  background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%231a5276'/%3E%3C/svg%3E");
  background-repeat:no-repeat; background-position:left 11px center; }
.mq-lang-box select#select_lang:hover { border-color:#1a5276 !important; }
.mq-lang-box select#select_lang:focus { outline:none; border-color:#1a5276 !important; box-shadow:0 0 0 3px rgba(26,82,118,.15); }
.mq-lang-old-globe { display:none !important; }
</style>
<script>
(function(){
  var sel = document.getElementById('select_lang');
  if(!sel) return;
  // l'ancien petit globe au-dessus de la liste : masque (il passe dans la pilule)
  var col = sel.parentElement, old = col ? col.querySelector(':scope > i.fa-globe') : null;
  if(old) old.classList.add('mq-lang-old-globe');
  var box = document.createElement('span'); box.className = 'mq-lang-box';
  box.innerHTML = '<i class="fa-solid fa-globe"></i>';
  sel.parentNode.insertBefore(box, sel); box.appendChild(sel);   // le meme element : ses evenements sont conserves
})();
</script>
