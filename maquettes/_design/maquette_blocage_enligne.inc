<style id="maquette-blocage-enligne">
/* MAQUETTE : aucun CSS ; bloque les enregistrements UNIQUEMENT hors de dne.local (en ligne) -> en local, comportement inchange */
</style>
<script>
(function(){
  if(/^(dne.local|localhost|127.0.0.1)$/.test(location.hostname)) return;   // en local : rien ne change
  // ---------- MAQUETTE SEULEMENT : aucun enregistrement possible ----------
  // tout appel qui ecrit (insert / delete / update / set_ / reorder / upload / send...) est bloque ; les lectures restent
  var WRITE = /(insert|delete|update|_set|set_|reorder|remove|save|upload|send|fill_global|duplicate|archive|cleanup|unset|_add|add_to)/i;
  var oOpen = XMLHttpRequest.prototype.open, oSend = XMLHttpRequest.prototype.send;
  // + les fichiers "setXxx.php" sans souligne (ex. setEtrpData.php, setNewTaskData.php)
  // + tout appel en arriere-plan vers "add_xxx.php" (ex. add_meeting_to_do_today.php, appele automatiquement par le tableau de bord)
  var isWrite = function(u){ u = String(u || ''); return WRITE.test(u) || /(^|\/)set[A-Z]/.test(u) || /(^|\/)add_/.test(u); };
  XMLHttpRequest.prototype.open = function(m, u){ this._mqBlock = isWrite(u); return oOpen.apply(this, arguments); };
  XMLHttpRequest.prototype.send = function(){ if(this._mqBlock) return; return oSend.apply(this, arguments); };
  if(window.fetch){ var oFetch = window.fetch; window.fetch = function(u){ if(isWrite(u && u.url || u)) return new Promise(function(){}); return oFetch.apply(this, arguments); }; }
  document.addEventListener('submit', function(e){ e.preventDefault(); e.stopPropagation(); }, true);   // aucun formulaire envoye
})();
</script>
