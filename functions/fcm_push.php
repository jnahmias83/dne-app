<?php
// Envoi de notifications via Firebase Cloud Messaging (appli Android DNEMobile).
// Fichier autonome, sans dependance externe.

function fcmBase64UrlEncode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

// Echange la cle de service Firebase contre un jeton d'acces temporaire (valable 1h),
// via le flux standard Google "JWT Bearer" (OAuth2 service account).
function fcmGetAccessToken() {
    static $cachedToken = null;
    static $cachedExpiry = 0;

    if ($cachedToken !== null && time() < $cachedExpiry - 60) {
        return $cachedToken;
    }

    $serviceAccountPath = dirname(__DIR__) . '/firebase_service_account.json';
    if (!file_exists($serviceAccountPath)) {
        throw new Exception('Fichier firebase_service_account.json introuvable');
    }
    $serviceAccount = json_decode(file_get_contents($serviceAccountPath), true);

    $now = time();
    $header = fcmBase64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
    $claims = fcmBase64UrlEncode(json_encode([
        'iss' => $serviceAccount['client_email'],
        'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
        'aud' => $serviceAccount['token_uri'],
        'iat' => $now,
        'exp' => $now + 3600,
    ]));
    $signingInput = $header . '.' . $claims;

    $privateKey = openssl_pkey_get_private($serviceAccount['private_key']);
    if ($privateKey === false) {
        throw new Exception('Cle privee du compte de service invalide: ' . openssl_error_string());
    }

    $signature = '';
    $ok = openssl_sign($signingInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);
    if (!$ok) {
        throw new Exception('Echec signature JWT: ' . openssl_error_string());
    }

    $jwt = $signingInput . '.' . fcmBase64UrlEncode($signature);

    $ch = curl_init($serviceAccount['token_uri']);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode($response, true);
    if ($httpCode !== 200 || empty($data['access_token'])) {
        throw new Exception('Echec obtention jeton acces FCM: ' . $response);
    }

    $cachedToken = $data['access_token'];
    $cachedExpiry = $now + (int)$data['expires_in'];

    return $cachedToken;
}

function fcmGetProjectId() {
    $serviceAccountPath = dirname(__DIR__) . '/firebase_service_account.json';
    $serviceAccount = json_decode(file_get_contents($serviceAccountPath), true);
    return $serviceAccount['project_id'];
}

// Envoie une notification a un seul appareil (identifie par son token FCM).
// Retourne true si accepte par Firebase, false sinon (voir $error par reference).
// Si le token n'est plus valide (appli desinstallee, etc.), $isInvalidToken passe a true
// pour que l'appelant puisse nettoyer la table dne_push_subscriptions.
function sendFcmNotification($fcmToken, $title, $body, &$error = null, &$isInvalidToken = false) {
    try {
        $accessToken = fcmGetAccessToken();
        $projectId = fcmGetProjectId();

        $message = [
            'message' => [
                'token' => $fcmToken,
                'data' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'android' => [
                    'priority' => 'high',
                ],
            ],
        ];

        $ch = curl_init("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send");
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($message),
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json',
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            return true;
        }

        $error = $response;
        if ($httpCode === 404 || strpos($response, 'UNREGISTERED') !== false) {
            $isInvalidToken = true;
        }
        return false;
    } catch (Exception $e) {
        $error = $e->getMessage();
        return false;
    }
}

// Envoie a tous les appareils abonnes, et nettoie automatiquement les abonnements devenus invalides.
function sendFcmNotificationToAllSubscribers($title, $body, $exclude_id_user = 0) {
    global $mysqli;

    if($exclude_id_user > 0){
        $query = $mysqli->prepare("SELECT id, endpoint FROM dne_push_subscriptions WHERE id_user <> ?");
        $query->bind_param('i', $exclude_id_user);
    }
    else {
        $query = $mysqli->prepare("SELECT id, endpoint FROM dne_push_subscriptions");
    }
    $query->execute();
    $query->store_result();
    $subscriptions = fetch($query);

    foreach ($subscriptions as $sub) {
        $error = null;
        $isInvalidToken = false;
        $success = sendFcmNotification($sub->endpoint, $title, $body, $error, $isInvalidToken);

        if (!$success && $isInvalidToken) {
            $deleteQuery = $mysqli->prepare("DELETE FROM dne_push_subscriptions WHERE id = ?");
            $deleteQuery->bind_param('i', $sub->id);
            $deleteQuery->execute();
        }
    }
}
