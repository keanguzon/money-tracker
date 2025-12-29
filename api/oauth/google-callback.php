<?php
/**
 * Google OAuth Callback Handler
 */

require_once dirname(dirname(__DIR__)) . '/config/app.php';
require_once dirname(dirname(__DIR__)) . '/config/oauth.php';

function sendPopupResponse($type, $message = '') {
    $dashboardUrl = APP_URL . '/pages/dashboard/';
    $loginUrl = APP_URL . '/pages/login/';
    
    echo <<<HTML
<!DOCTYPE html>
<html>
<head><title>Authenticating...</title></head>
<body>
<script>
    if (window.opener) {
        window.opener.postMessage({ type: '$type', message: '$message' }, '*');
        window.close();
    } else {
        // Fallback if not in a popup
        window.location.href = '$type' === 'oauth-login-success' ? '$dashboardUrl' : '$loginUrl';
    }
</script>
</body>
</html>
HTML;
    exit;
}

// Verify state to prevent CSRF
if (empty($_GET['state']) || empty($_SESSION['oauth_state']) || $_GET['state'] !== $_SESSION['oauth_state']) {
    setFlashMessage('error', 'Invalid OAuth state. Please try again.');
    sendPopupResponse('oauth-login-error');
}

unset($_SESSION['oauth_state']);

// Check for error from Google
if (!empty($_GET['error'])) {
    setFlashMessage('error', 'Google sign-in was cancelled.');
    sendPopupResponse('oauth-login-error');
}

// Get authorization code
$code = $_GET['code'] ?? '';
if (empty($code)) {
    setFlashMessage('error', 'No authorization code received.');
    sendPopupResponse('oauth-login-error');
}

// Get user info from Google
$oauthUser = getGoogleUser($code);
if (!$oauthUser) {
    setFlashMessage('error', 'Failed to get user information from Google.');
    sendPopupResponse('oauth-login-error');
}

// Find or create user
$result = findOrCreateOAuthUser($oauthUser);
if (!$result['success']) {
    setFlashMessage('error', $result['message'] ?? 'Failed to sign in with Google.');
    sendPopupResponse('oauth-login-error');
}

// Log the user in
$_SESSION['user_id'] = $result['user']['id'];

if ($result['is_new']) {
    setFlashMessage('success', 'Welcome to ' . APP_NAME . '! Your account has been created.');
} else {
    setFlashMessage('success', 'Welcome back!');
}

sendPopupResponse('oauth-login-success');
?>
