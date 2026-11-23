<style id="maquette-boutons">
/* ===== MAQUETTE - boutons des etapes "nouveau projet" et des fiches : meme style que les autres maquettes ===== */
/* seule l'apparence change : memes boutons, memes id, memes actions */
html, body { background:#f6f5f1 !important; }
.card .card-body .btn, .card .card-body input.btn {
  background:transparent !important; color:#1a5276 !important; border:1.5px solid #1a5276 !important; border-radius:999px !important;
  padding:6px 16px !important; font-weight:700; font-size:14px !important; box-shadow:none !important; height:auto !important; }
.card .card-body .btn:hover:not(:disabled), .card .card-body input.btn:hover:not(:disabled) { background:#e3eef6 !important; }
.card .card-body .btn i { color:#1a5276 !important; }
/* boutons noirs du site (ביטול / סגור) : meme forme, en gris fonce */
.card .card-body .btn.bgColorBlack, .card .card-body input.btn.bgColorBlack { color:#4a4a4a !important; border-color:#8a8a8a !important; }
.card .card-body .btn.bgColorBlack:hover { background:#ececec !important; }
.card .card-body .btn:disabled, .card .card-body input.btn:disabled { opacity:.4; }
</style>
<script>
/* (aucun script : les boutons gardent leurs evenements d'origine) */
</script>
