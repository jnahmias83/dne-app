<?php
/**
 * Connexion via compte Google (Google Identity Services / "Sign in with Google").
 *
 * Recoit le jeton ID (POST 'credential'), le verifie aupres de Google,
 * relie l'email du compte Google a un utilisateur dne_users (colonne email),
 * puis ouvre la session PHP avec EXACTEMENT les memes cles $_SESSION que login.php.
 *
 * N'inclut PAS include/header.php (qui emet du HTML) : on reprend juste le bloc session,
 * aligne sur header.php (duree 30 jours).
 *
 * Reponses (text/plain) : ok | not_linked | invalid | no_token | not_configured
 */

include 'functions/functions.php';

header('Content-Type: text/plain; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    $session_cookie_secure = !empty($_SERVER['HTTPS'])
        || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    session_set_cookie_params([
        'lifetime'  => 2592000,
        'path'      => '/',
        'secure'    => $session_cookie_secure,
        'httponly'  => true,
        'samesite'  => 'Lax',
    ]);
    session_start();
}

/* --- 0. Configuration --- */
$allowed_aud = array_values(array_filter([
    defined('GOOGLE_CLIENT_ID') ? trim(GOOGLE_CLIENT_ID) : '',
    defined('GOOGLE_ANDROID_CLIENT_ID') ? trim(GOOGLE_ANDROID_CLIENT_ID) : '',
]));
if (empty($allowed_aud)) { echo 'not_configured'; exit; }

$credential = isset($_POST['credential']) ? trim($_POST['credential']) : '';
if ($credential === '') { echo 'no_token'; exit; }

/* --- 1. Verification du jeton aupres de Google --- */
$ch = curl_init('https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($credential));
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 10,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2,
]);
$resp = curl_exec($ch);
$http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http !== 200 || !$resp) { echo 'invalid'; exit; }

$claims = json_decode($resp, true);
if (!is_array($claims)) { echo 'invalid'; exit; }

$aud = isset($claims['aud']) ? $claims['aud'] : '';
$iss = isset($claims['iss']) ? $claims['iss'] : '';
$exp = isset($claims['exp']) ? (int) $claims['exp'] : 0;
$email_verified = isset($claims['email_verified']) ? $claims['email_verified'] : '';

if (!in_array($aud, $allowed_aud, true)) { echo 'invalid'; exit; }
if ($iss !== 'https://accounts.google.com' && $iss !== 'accounts.google.com') { echo 'invalid'; exit; }
if ($exp <= time()) { echo 'invalid'; exit; }
if ($email_verified !== 'true' && $email_verified !== true) { echo 'invalid'; exit; }

$email = strtolower(trim(isset($claims['email']) ? $claims['email'] : ''));
if ($email === '') { echo 'invalid'; exit; }

/* --- 2. Relier a un utilisateur DNE actif (par email) --- */
$one = 1;
$query = $mysqli->prepare("SELECT * FROM dne_users WHERE LOWER(email) = ? AND is_user_active = ?");
$query->bind_param('si', $email, $one);
$query->execute();
$query->store_result();

if ($query->num_rows === 0) { echo 'not_linked'; exit; }

$query    = fetch_unique($query);
$id_user  = (int) @$query->id;
$nickname = @$query->nickname;
$lang     = @$query->lang;

/* --- 3. projects_list : meme requete que login.php --- */
$is_project_active = 1;
$query = $mysqli->prepare("SELECT p.* FROM dne_projects p
                          WHERE p.is_project_active = ?
                          AND EXISTS (
                               SELECT 1
                               FROM dne_responsibles r
                               WHERE r.id_project = p.id
                               AND r.id_user = ?
                          )
                          ORDER BY p.nickname");
$query->bind_param('ii', $is_project_active, $id_user);
$query->execute();
$query->store_result();
$projects = fetch($query);

$projects_array = array();
foreach ($projects as $item) {
    array_push($projects_array, $item->id . '-' . $item->nickname);
}

/* --- 4. Ouverture de session : memes cles que login.php:37-49 --- */
$_SESSION['projects_list'] = implode(',', $projects_array);
$_SESSION['id_user']       = $id_user;
$_SESSION['user_nickname'] = $nickname;
$_SESSION['lang']          = $lang;

$query = $mysqli->prepare("SELECT * FROM dne_users WHERE id = ?");
$query->bind_param('i', $_SESSION['id_user']);
$query->execute();
$query->store_result();
$user = fetch_unique($query);
$_SESSION['user_role'] = $user->role;

session_write_close();
echo 'ok';
