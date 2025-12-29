<?php
/**
 * Google OAuth Callback Handler
 */

require_once dirname(dirname(__DIR__)) . '/config/app.php';
require_once dirname(dirname(__DIR__)) . '/config/oauth.php';

// Verify state to prevent CSRF
if (empty($_GET['state']) || empty($_SESSION['oauth_state']) || $_GET['state'] !== $_SESSION['oauth_state']) {
    setFlashMessage('error', 'Invalid OAuth state. Please try again.');
    redirect('/pages/login/');
}

unset($_SESSION['oauth_state']);

// Check for error from Google
if (!empty($_GET['error'])) {
    setFlashMessage('error', 'Google sign-in was cancelled.');
    redirect('/pages/login/');
}

// Get authorization code
$code = $_GET['code'] ?? '';
if (empty($code)) {
    setFlashMessage('error', 'No authorization code received.');
    redirect('/pages/login/');
}

// Get user info from Google
$oauthUser = getGoogleUser($code);
if (!$oauthUser) {
    setFlashMessage('error', 'Failed to get user information from Google.');
    redirect('/pages/login/');
}

// Find or create user
$result = findOrCreateOAuthUser($oauthUser);
if (!$result['success']) {
    setFlashMessage('error', $result['message'] ?? 'Failed to sign in with Google.');
    redirect('/pages/login/');
}

// Log the user in
$_SESSION['user_id'] = $result['user']['id'];

if ($result['is_new']) {
    setFlashMessage('success', 'Welcome to ' . APP_NAME . '! Your account has been created.');
} else {
    setFlashMessage('success', 'Welcome back!');
}

redirect('/pages/dashboard/');
?>
