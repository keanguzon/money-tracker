<?php
/**
 * OAuth Configuration
 * Money Tracker Application
 * 
 * Set these environment variables in Render (or .env locally):
 * - GOOGLE_CLIENT_ID
 * - GOOGLE_CLIENT_SECRET
 * - GITHUB_CLIENT_ID
 * - GITHUB_CLIENT_SECRET
 */

// Google OAuth
define('GOOGLE_CLIENT_ID', getenv('GOOGLE_CLIENT_ID') ?: '');
define('GOOGLE_CLIENT_SECRET', getenv('GOOGLE_CLIENT_SECRET') ?: '');
define('GOOGLE_REDIRECT_URI', rtrim(APP_URL, '/') . '/api/oauth/google-callback.php');

// GitHub OAuth
define('GITHUB_CLIENT_ID', getenv('GITHUB_CLIENT_ID') ?: '');
define('GITHUB_CLIENT_SECRET', getenv('GITHUB_CLIENT_SECRET') ?: '');
define('GITHUB_REDIRECT_URI', rtrim(APP_URL, '/') . '/api/oauth/github-callback.php');

/**
 * Generate Google OAuth URL
 */
function getGoogleAuthUrl() {
    if (empty(GOOGLE_CLIENT_ID)) {
        return '#';
    }
    
    $state = bin2hex(random_bytes(16));
    $_SESSION['oauth_state'] = $state;
    
    $params = [
        'client_id' => GOOGLE_CLIENT_ID,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'response_type' => 'code',
        'scope' => 'email profile',
        'state' => $state,
        'access_type' => 'online',
        'prompt' => 'select_account'
    ];
    
    return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
}

/**
 * Generate GitHub OAuth URL
 */
function getGitHubAuthUrl() {
    if (empty(GITHUB_CLIENT_ID)) {
        return '#';
    }
    
    $state = bin2hex(random_bytes(16));
    $_SESSION['oauth_state'] = $state;
    
    $params = [
        'client_id' => GITHUB_CLIENT_ID,
        'redirect_uri' => GITHUB_REDIRECT_URI,
        'scope' => 'user:email',
        'state' => $state
    ];
    
    return 'https://github.com/login/oauth/authorize?' . http_build_query($params);
}

/**
 * Exchange Google auth code for tokens and get user info
 */
function getGoogleUser($code) {
    // Exchange code for access token
    $tokenUrl = 'https://oauth2.googleapis.com/token';
    $tokenData = [
        'code' => $code,
        'client_id' => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'grant_type' => 'authorization_code'
    ];
    
    $ch = curl_init($tokenUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($tokenData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $tokens = json_decode($response, true);
    
    if (!isset($tokens['access_token'])) {
        return null;
    }
    
    // Get user info
    $userUrl = 'https://www.googleapis.com/oauth2/v2/userinfo';
    $ch = curl_init($userUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $tokens['access_token']]);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $user = json_decode($response, true);
    
    if (!isset($user['email'])) {
        return null;
    }
    
    return [
        'provider' => 'google',
        'provider_id' => $user['id'],
        'email' => $user['email'],
        'name' => $user['name'] ?? '',
        'avatar' => $user['picture'] ?? null
    ];
}

/**
 * Exchange GitHub auth code for tokens and get user info
 */
function getGitHubUser($code) {
    // Exchange code for access token
    $tokenUrl = 'https://github.com/login/oauth/access_token';
    $tokenData = [
        'code' => $code,
        'client_id' => GITHUB_CLIENT_ID,
        'client_secret' => GITHUB_CLIENT_SECRET,
        'redirect_uri' => GITHUB_REDIRECT_URI
    ];
    
    $ch = curl_init($tokenUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($tokenData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Content-Type: application/x-www-form-urlencoded'
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $tokens = json_decode($response, true);
    
    if (!isset($tokens['access_token'])) {
        return null;
    }
    
    // Get user info
    $userUrl = 'https://api.github.com/user';
    $ch = curl_init($userUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $tokens['access_token'],
        'User-Agent: MoneyTracker-App',
        'Accept: application/vnd.github.v3+json'
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $user = json_decode($response, true);
    
    if (!isset($user['id'])) {
        return null;
    }
    
    // Get primary email (may need separate request if email is private)
    $email = $user['email'];
    if (empty($email)) {
        $emailUrl = 'https://api.github.com/user/emails';
        $ch = curl_init($emailUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $tokens['access_token'],
            'User-Agent: MoneyTracker-App',
            'Accept: application/vnd.github.v3+json'
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        
        $emails = json_decode($response, true);
        if (is_array($emails)) {
            foreach ($emails as $e) {
                if (!empty($e['primary']) && !empty($e['verified'])) {
                    $email = $e['email'];
                    break;
                }
            }
        }
    }
    
    if (empty($email)) {
        return null;
    }
    
    return [
        'provider' => 'github',
        'provider_id' => (string) $user['id'],
        'email' => $email,
        'name' => $user['name'] ?? $user['login'] ?? '',
        'avatar' => $user['avatar_url'] ?? null
    ];
}

/**
 * Find or create user from OAuth data
 */
function findOrCreateOAuthUser($oauthData) {
    $db = getDB();
    
    // First, check if this OAuth account is already linked
    $stmt = $db->prepare("SELECT * FROM users WHERE oauth_provider = ? AND oauth_provider_id = ?");
    $stmt->execute([$oauthData['provider'], $oauthData['provider_id']]);
    $user = $stmt->fetch();
    
    if ($user) {
        return ['success' => true, 'user' => $user, 'is_new' => false];
    }
    
    // Check if email already exists (link OAuth to existing account)
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$oauthData['email']]);
    $user = $stmt->fetch();
    
    if ($user) {
        // Link OAuth to existing account
        $stmt = $db->prepare("UPDATE users SET oauth_provider = ?, oauth_provider_id = ?, avatar = COALESCE(avatar, ?) WHERE id = ?");
        $stmt->execute([$oauthData['provider'], $oauthData['provider_id'], $oauthData['avatar'], $user['id']]);
        
        // Refresh user data
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user['id']]);
        $user = $stmt->fetch();
        
        return ['success' => true, 'user' => $user, 'is_new' => false];
    }
    
    // Create new user
    $username = generateUniqueUsername($oauthData['email'], $oauthData['name']);
    $randomPassword = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
    
    try {
        $stmt = $db->prepare("
            INSERT INTO users (username, email, password, full_name, avatar, oauth_provider, oauth_provider_id)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $username,
            $oauthData['email'],
            $randomPassword,
            $oauthData['name'],
            $oauthData['avatar'],
            $oauthData['provider'],
            $oauthData['provider_id']
        ]);
        
        $userId = $db->lastInsertId();
        
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        return ['success' => true, 'user' => $user, 'is_new' => true];
    } catch (PDOException $e) {
        error_log('OAuth user creation failed: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to create account'];
    }
}

/**
 * Generate a unique username from email/name
 */
function generateUniqueUsername($email, $name) {
    $db = getDB();
    
    // Try name-based username first
    $base = '';
    if (!empty($name)) {
        $base = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($name));
    }
    if (empty($base) || strlen($base) < 3) {
        // Fall back to email prefix
        $base = preg_replace('/[^a-zA-Z0-9]/', '', strtolower(explode('@', $email)[0]));
    }
    if (strlen($base) < 3) {
        $base = 'user';
    }
    $base = substr($base, 0, 20);
    
    $username = $base;
    $counter = 1;
    
    while (true) {
        $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if (!$stmt->fetch()) {
            return $username;
        }
        $username = $base . $counter;
        $counter++;
        if ($counter > 9999) {
            $username = $base . bin2hex(random_bytes(4));
            break;
        }
    }
    
    return $username;
}
?>
