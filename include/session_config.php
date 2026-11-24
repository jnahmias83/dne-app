<?php
// Connexion gardee 30 jours (site, appli, Google) - a charger AVANT chaque session_start().
// Le serveur GoDaddy impose 24 h (86400 s) : sans ce reglage, n'importe quelle page effacait les connexions de plus d'un jour.
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', '2592000');
}
