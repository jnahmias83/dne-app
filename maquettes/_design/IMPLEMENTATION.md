# Aide-mémoire : passer les maquettes sur le vrai site

Le but est de reproduire la maquette à l'identique, que toutes les fonctions marchent, et que le CSS du design ne soit écrasé par rien.

## Les 5 règles
1. **On ne touche à aucune logique.**
   - Rien ne change dans le PHP, les requêtes, le JS métier, les noms de champs ou les id.
   - On n'ajoute que du CSS, plus, au besoin, une classe dans le HTML.
2. **Un seul fichier CSS de design : `css/dne-design.css`.**
   - C'est une copie exacte des blocs CSS de `maquette_design.inc`, `maquette_champs.inc` et `maquette_sc.inc` (la partie `<style>` uniquement).
   - Il est chargé **en dernier** dans chaque page, après Bootstrap, `jquery.dataTables.css`, `jquery.dataTables_themeroller.css` et le CSS du site. Ainsi, rien ne l'écrase.
3. **Le JS des maquettes ne passe pas sur le site, sauf le JS d'apparence** (voir le tableau plus bas).
4. **On avance une page à la fois.** Pour chaque page :
   - mesurer la maquette et la vraie page avec la même photo chiffrée (voir « Filet anti-régression ») ;
   - tester les fonctions de la page sur dne.local ;
   - faire valider par le client ;
   - seulement ensuite, passer à la page suivante.
5. **Rien ne part sur test ni sur prod sans confirmation.**
   - Lancer `deploy-to-test.ps1`, puis `deploy-to-prod.ps1`.
   - Avant de lancer un script, vérifier que l'arbre de travail est propre : les scripts envoient l'état du disque, pas l'état de git.

## ⚠ Tableau de bord (projects.php) : AUCUN changement d'apparence
Décision de l'utilisateur, le 2026-11-23 : le tableau de bord garde son CSS d'origine (croix de fermeture, boutons des surnoms de projet, popups, tout).
- **Ne pas charger `dne-design.css` sur projects.php**, ou bien limiter ses règles aux autres pages. Sinon, les règles générales (tableaux, `.btn`, `.subtitle`…) changeraient le tableau de bord.
- La maquette `tableau_de_bord.html` est remise à l'origine. Elle ne garde que le blocage des enregistrements (`maquette_blocage.inc`). La version précédente est sauvegardée dans `_design/backup_tableau_de_bord_avant_origine.html`.

## Changements du site des 25-26/11 (repris dans les maquettes le 26/11)
Les maquettes tableau, tache, maakav (copies de meetings.php, projet 20) et tableau_de_bord (copie de projects.php) ont été recopiées depuis dne.local le 26/11. Les blocs de design `<style id="maquette-…">` sont repris à l'identique (du premier bloc jusqu'au premier `</body>`). La galerie suit seule (vignettes en direct) et la liste `index.html` ne change pas (70 maquettes). La photo anti-régression a été refaite le 26/11.

Ce qui a changé sur le site, et que `dne-design.css` ne doit pas écraser :
- **En-tête du tableau des tâches (meetings.php)** : bleu marine `#0E3C5D`, texte blanc, séparateurs `#D6D2D2`. Le cadre beige autour du tableau a été retiré (le `<div class="meetings-table-frame">` reste, sans style).
  - Décision de l'utilisateur (26/11) : la maquette prend aussi le bleu marine. `maquette-v1` (tableau.html) met l'en-tête en `#0E3C5D` et le texte en blanc, au lieu de `#f5f7fa`. tache.html et maakav.html le montrent aussi (CSS du site). Au passage sur le site, `dne-design.css` ne doit pas remettre le `#dedede` général sur cet en-tête.
- **Lignes de journal updates et tracking** (tableau, popups, task_details, dashboard) :
  - plus de tirets ;
  - 10 px avant la remarque (`margin-inline-start`) et 6 px avant le statut ;
  - badges ronds de 22 × 22 px, en gras.
- **Règle d'exception** à garder : `td[id^="td_description_"] span.badge-nickname-green, td[id^="td_description_"] span.badge-circle {display:inline-flex !important; white-space:nowrap !important; font-weight:bold !important;}`. Sinon, la règle générale `td[id^="td_description_"] span {display:inline !important}` déforme les badges.
- **Alignement de la remarque** : `align-items:first baseline` **seulement** dans le tableau de meetings.php (4 endroits). Les popups (fill_task_active_remarks.php, task_details.php) gardent `flex-start`, car `first baseline` y décalait le tracking de 17 px.
- **Dashboard (projects.php)** : badges de 22 px et `min-height:22px` sur les lignes de journal, pour que les badges ne se touchent pas.
- **Tableau des tâches dans les maquettes (26/11, demande de l'utilisateur)** : pas de séparateurs verticaux entre les colonnes, un trait horizontal très léger entre les tâches (`#meetings_table td { border:none !important; border-bottom:1px solid #e9eeef !important; }`, dans `maquette_v1.html.inc` et `maquette_tache_live.inc`). L'en-tête `th` garde ses séparateurs. Sur le site, la règle `td { border:1px solid #d6d2d2 !important }` de meetings.php est moins précise et sera donc battue par cette règle.
- **Règles de maquette retirées le 26/11** (le site fait maintenant mieux, et elles recouvraient son affichage) :
  - `maquette_v1.html.inc` : alignement des journaux du tableau (`display:flex; align-items:baseline; gap:4px`, écrit pour l'ancienne structure) ;
  - `maquette_maakav_live.inc` : badge de tracking forcé à 18 px / police 8, et `flex` + `gap:5px` des lignes. Il reste seulement « sans cadre » (`border:none; padding:2px 0`).
  - Mesuré après : badges de journal 22 × 22 px, ronds, en gras ; remarque alignée sur le statut (0 px) avec 10 px de marge, comme sur le site. Les badges de suivi de 18 px sous le numéro de tâche (`badge-circle-track`) ont la même taille sur le site.

## Mettre les maquettes en ligne (pour les montrer) : procédure
Les scripts de déploiement ignorent `maquettes/`. Une mise en ligne se fait donc toujours à la main, et seulement avec l'accord de l'utilisateur.

Protections en place :
- **Blocage des enregistrements dans les 70 maquettes.**
  - 64 maquettes l'ont toujours actif (`maquette_design.inc` et les autres).
  - Les 6 maquettes « vivantes » (maakav, tache, tableau, menu_tasks, rapports, responsables) l'ont **seulement hors de dne.local** (`maquette_blocage_enligne.inc`). En local, elles se comportent comme avant.
- **`maquettes/.htaccess`** :
  - **pas de mot de passe** (choix de l'utilisateur, 2026-11-24) : les maquettes sont ouvertes à qui a l'adresse, données réelles comprises ;
  - pas de liste des fichiers du dossier ;
  - `_design/` interdit sur les serveurs ;
  - fichiers `.secret` interdits partout ;
  - `noindex` pour les moteurs de recherche.

Étapes : lancer `Development\deploy-maquettes.ps1`, répondre `dev` ou `prod` (et `YES` pour prod), puis le mot de passe FTP. Le script fait ce qui suit :
1. Il envoie `maquettes/*.html` et `maquettes/.htaccess`, sans `_design/`.
   - Serveur dev : `/public_html/Development/maquettes/`. Le script remplace `<base href="/">` par `<base href="/Development/">` dans les copies, pour que les pages ne lisent pas le site prod.
   - Serveur prod : `/public_html/maquettes/`.
2. Il vérifie que `…/maquettes/` répond 200.

À vérifier ensuite : `_design/` doit répondre 403, et un clic sur « שמור » ne doit rien enregistrer.

## Ce qui passe sur le site et ce qui reste dans les maquettes

| Élément de la maquette | Sur le site ? | Pourquoi |
|---|---|---|
| CSS : tableaux arrondis, bleus alternés, en-tête #dedede | OUI | design |
| CSS : bloc `DEBUT/FIN BLOC DATATABLES` | OUI, **tel quel** | CSS seul, qui tient après un tri, un filtre ou un changement de page |
| CSS : fiches (cartes ajustées au contenu, champs 300/150 px centrés, EN\|HE alignés, bandes 95 % centrées) | OUI | design |
| CSS : boutons (enregistrer bleu #218FD6, annuler/fermer noir) | OUI | design |
| JS « style() » qui pose les classes `mq-t`, `mq-dark`, `mq-light`, `mq-form-row`, `mq-full`, globe de langue | OUI, **ou** remplacé par des classes écrites dans le PHP | ne fait que de l'apparence |
| JS qui bloque les enregistrements (XHR / fetch / submit) | NON | sur le site, il faut enregistrer |
| JS qui lit `?lang_screen=` dans l'adresse | NON | PHP le fait déjà |
| Bouton Google dessiné (connexion.html) | NON | le vrai bouton Google existe déjà et s'affiche sur le domaine public |
| Clic « הוסף מודול » qui saute à la partie module (estimation) | NON | le vrai bouton enregistre puis recharge |
| Données d'exemple (tableau des comptes non approuvés, lignes de module d'estimation) | NON | sur le site, ce sont les vraies données |

## Retouches HTML à faire dans les PHP
- Popups de tâche (meetings.php, dashboard, task.php… : chaque `.subtitle.html(chapter+"<br/>"+subject+"&nbsp;<span class=\"fontSize20 colorRed\">|</span>&nbsp;"+area)`). Seul l'affichage change : il faut entourer le sujet et le lieu pour centrer la barre rouge sur la largeur du popup. On remplace ce code par :
  `chapter+"<div class=\"mq-sub-line\"><span class=\"mq-sub-a\">"+subject+"</span><span class=\"fontSize20 colorRed\">|</span><span class=\"mq-sub-b\">"+area+"</span></div>"`. Le CSS correspondant est dans `maquette_sc.inc`.
- Liste des utilisateurs (maquette `gestion_utilisateurs.html`, PHP à confirmer) : `marginTop10` devient `marginTop20` sur la ligne des boutons « נתוני מנהל / הוסף משתמש » ; `marginTop15` devient `marginTop5` sur la ligne du logo PDF.
- Page d'ajout d'un type de tâche (maquette `gestion_type_tache_ajout.html`, PHP à confirmer) : remplacer `marginTop20` par `marginTop10` sur la ligne du champ « שם », pour réduire l'espace sous le titre.
- `not_approved_accounts.php` : retirer `marginTop20` sur la ligne du bouton « יצירת PDF » (espace trop grand au-dessus du bouton).
- `accounts_payments.php` : les données des tableaux הזמנות, חשבונות/תשלומים et לא מאושרים sont centrées sous leur titre. Ce sont les règles `#orders_list`, `#accounts_payments_list` et `#not_approved_accounts_list` du bloc `mq-align-cols`, dans `comptes_paiements.html`. Les mettre dans `dne-design.css`.
- `accounts_payments.php` : le titre et le tableau des comptes non approuvés doivent passer chacun sur sa propre ligne (règles `div:has(> .row > #div_title_not_approved_accounts_list)`, dans le même bloc). Vérifier sur le site avec un fournisseur qui a des comptes non approuvés.

## Pièges connus : à vérifier sur chaque page
- **DataTables.** Garder la version actuelle, car le CSS vise ses classes (`dataTable`, `sorting`, `text_filter`). Vérifier que :
  - les flèches de tri sont visibles ;
  - les filtres sont dans leurs cases ;
  - les bleus alternés restent justes après un tri ;
  - le tableau ne déborde pas.
- **Cas qui n'apparaissent qu'avec certaines données.** Les tester sur le site avec de vraies données :
  - tableau des comptes non approuvés ;
  - lignes de module d'estimation (le tableau et le bouton « הוסף מודול » s'excluent) ;
  - colonne poubelle seulement s'il y a des lignes.
- **Rapports du projet** (maquettes `np_rapports` et `np_rapports_nouveau`, même page PHP). Ce sont deux états de la même page. Tester un projet sans rapports (5 boutons « יצירת דו"ח » actifs) et un projet qui en a (boutons grisés, opacity .4, sans effet au survol).
- **Données centrées sous leur titre de colonne** : `#payments_list` (payments.php), `#orders_list`, `#accounts_payments_list` et `#not_approved_accounts_list` (accounts_payments.php). Les lignes de total (`colspan`) gardent leur alignement.
- **Lignes actif / pas actif** (même rendu partout) :
  - le fond garde l'alternance des bleus ;
  - le contenu d'une ligne décochée passe en gris #8a96a3, estompé (opacity .4) ;
  - la case reste nette pour pouvoir la recocher.

  Pages concernées :

  | Page PHP | Maquette | Règles CSS |
  |---|---|---|
  | `responsibles.php` | responsables | `#responsibles_list`, dans `maquette_responsables.inc` |
  | `payments_wires.php` | paiements_virements | `#payments_wires_list`, case `cb_*` « apparaît dans le PDF », dans `maquette_design.inc` |

  Sur le site, cocher ou décocher enregistre puis recharge la page. Vérifier après le rechargement que la ligne garde le bon aspect, et que le PDF des virements n'inclut que les lignes cochées.
- **Langue.** Tester HE puis EN sur chaque page : les libellés changent, et EN|HE reste aligné sur la zone de texte.
- **Petits écrans.** Tester à 1300, 900 et 600 px. Les fournisseurs (9 colonnes) dépassent encore sous 700 px : décision en attente.
- **Ne jamais utiliser `background:` (forme courte) sur un élément du site qui a une image de fond.** Mettre seulement `background-color`. Sinon on efface l'image : les flèches de tri DataTables, ou l'icône de partage de « שמור ושתף » (`.whatsapp-btn`, `images/share-icon.svg`). Cas déjà corrigés dans `maquette_sc.inc` et dans le bloc DataTables.
- **CSS `:has()` imbriqué interdit.** Le navigateur ignore toute la règle s'il y a un `:has()` dans un autre `:has()`. Écrire `:has(> table #id)`, et non `:has(> table:has(#id))`.
- **Tableau des réglages de rapport** (rapport_form, `table:has(#all_chapters)`). Sous 900 px, il défile dans une zone de 75 % de la hauteur de l'écran, avec la ligne des titres fixée en haut. Tester sur tablette et sur smartphone.
- **Captures d'écran sans fenêtre.** Elles inversent l'hébreu ; ne pas s'y fier pour le sens du texte.

## Fonctions : test obligatoire sur chaque page (dne.local, jamais en prod)
1. Tester chaque bouton et chaque action : enregistrer, annuler, supprimer, crayon, trier, filtrer, pagination, changer de langue, envoyer un PDF, ouvrir et fermer les popups.
2. Provoquer les messages : champ obligatoire vide, confirmation de suppression. Vérifier qu'ils sont **visibles** et non recouverts.
3. Comparer avec la même page sans `dne-design.css` : le comportement doit être le même.
4. Points de CSS à risque :
   - **colonnes `col-*:empty` masquées dans les fiches** : vérifier qu'aucune n'est remplie plus tard par le JS (messages d'alerte…) ;
   - **filtres DataTables en `position:absolute`** : on doit pouvoir cliquer et taper dedans ;
   - **globe autour de `#lang`** : le changement de langue doit toujours partir ;
   - **`overflow:hidden` des tableaux et cartes arrondis** : ne doit couper ni liste déroulante, ni info-bulle, ni calendrier.

## Filet anti-régression (pour les maquettes, puis pour le site)
- `bash regression.sh baseline` photographie toutes les maquettes à 1300 et 600 px. Pour chaque tableau, champ, bouton et carte, la photo note la position, la taille, les couleurs et l'alignement.
- `bash regression.sh check` reprend la photo et liste **tout** ce qui a changé ailleurs.
- **Après chaque correction :**
  1. lancer `check` ;
  2. tout changement qui n'est pas sur la page corrigée est une régression : la réparer avant de continuer ;
  3. une fois que tout est conforme, relancer `baseline`.
- Ce dossier `_design` contient les sources du design (`maquette_*.inc`) et l'outil de reconstruction des maquettes (`node rebuild_mq.js <page.html> <style-id> <inc>`).

## Pages : maquette → PHP (cocher une fois faite et validée)
Liste à compléter au fur et à mesure. Chaque maquette porte le nom de la page PHP dont elle est la copie.
- [ ] comptes_paiements.html → accounts_payments.php
- [ ] comptes_non_approuves.html → not_approved_accounts.php
- [ ] compte_form.html → add_account.php
- [ ] paiement_form.html → add_payment.php
- [ ] commande_form.html → add_order.php
- [ ] estimation_form.html / estimation_ajout.html → add_cost_eval.php
- [ ] gestion_fournisseurs_S.html / _D.html → (liste des fournisseurs, à confirmer)
- [ ] gestion_domaines.html → domains.php
- [ ] connexion.html → login.php
- [ ] budget.html → budget.php
- [ ] rapports.html → custom_reports.php (liste des rapports du projet)
- [ ] rapport_form.html → add_custom_report.php. **Une seule page pour la création (`id=0`, vide) et la mise à jour (`id=268`…, remplie)** ; la maquette montre la mise à jour. Tester les deux cas.
- [ ] rapport_reglages.html → report_settings.php (réglages PDF, nom du fichier sur une ligne)
- [ ] np_rapports.html / np_rapports_nouveau.html → add_default_reports.php. **Une seule page, deux états** : boutons grisés (rapports déjà créés) ou actifs (nouveau projet).
- [ ] … (autres maquettes du dossier)
