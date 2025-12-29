<?php
/**
 * Authentication Helper
 * BukoJuice Application
 */

require_once dirname(__DIR__) . '/config/app.php';

/**
 * Require user to be logged in
 * Redirects to login page if not authenticated
 */
function requireAuth() {
    if (!isLoggedIn()) {
        setFlashMessage('error', 'Please log in to access this page.');
        redirect('/pages/login/');
    }
}

/**
 * Require user to be guest (not logged in)
 * Redirects to dashboard if already authenticated
 */
function requireGuest() {
    if (isLoggedIn()) {
        redirect('/pages/dashboard/');
    }
}

/**
 * Register a new user
 */
function registerUser($username, $email, $password, $fullName = '') {
    $db = getDB();
    
    // Check if username exists
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Username already exists'];
    }
    
    // Check if email exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Email already exists'];
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user
    try {
        // Set is_verified to TRUE by default (no email verification)
        $stmt = $db->prepare("INSERT INTO users (username, email, password, full_name, is_verified) VALUES (?, ?, ?, ?, TRUE)");
        $stmt->execute([$username, $email, $hashedPassword, $fullName]);
        
        $userId = $db->lastInsertId();
        
        // Auto-login after registration
        $_SESSION['user_id'] = $userId;
        
        return ['success' => true, 'user_id' => $userId];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Registration failed. Please try again.'];
    }
}

/**
 * Login user
 */
function loginUser($email, $password, $remember = false) {
    $db = getDB();
    
    // Find user by email or username
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
    $stmt->execute([$email, $email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        return ['success' => false, 'message' => 'Invalid email or password'];
    }
    
    // Verify password
    if (!password_verify($password, $user['password'])) {
        return ['success' => false, 'message' => 'Invalid email or password'];
    }
    
    // Set session
    $_SESSION['user_id'] = $user['id'];
    
    // Set remember me cookie
    if ($remember) {
        $token = bin2hex(random_bytes(32));
        setcookie('remember_token', $token, time() + (86400 * 30), '/'); // 30 days
        
        // Store token hash in database (would need to add this column)
        // For simplicity, we'll skip this for now
    }
    
    return ['success' => true, 'user' => $user];
}

/**
 * Logout user
 */
function logoutUser() {
    $_SESSION = [];
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
    
    // Clear remember me cookie
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 3600, '/');
    }
}

/**
 * Update user password
 */
function updatePassword($userId, $currentPassword, $newPassword) {
    $db = getDB();
    
    // Get current password
    $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user || !password_verify($currentPassword, $user['password'])) {
        return ['success' => false, 'message' => 'Current password is incorrect'];
    }
    
    // Update password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$hashedPassword, $userId]);
    
    return ['success' => true, 'message' => 'Password updated successfully'];
}

/**
 * Update user profile
 */
function updateProfile($userId, $data) {
    $db = getDB();
    
    $allowedFields = ['full_name', 'email', 'currency', 'dark_mode'];
    $updates = [];
    $values = [];
    
    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            $updates[] = "$field = ?";
            $values[] = $data[$field];
        }
    }
    
    if (empty($updates)) {
        return ['success' => false, 'message' => 'No data to update'];
    }
    
    $values[] = $userId;
    
    try {
        $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute($values);
        
        return ['success' => true, 'message' => 'Profile updated successfully'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Update failed. Please try again.'];
    }
}

/**
 * Send verification email
 */
function sendVerificationEmail($email, $name) {
    require_once dirname(__DIR__) . '/config/mail.php';
    
    $db = getDB();
    $otp = generateOTP();
    $expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));
    
    try {
        // Delete old OTPs
        $stmt = $db->prepare("DELETE FROM password_resets WHERE email = ?");
        $stmt->execute([$email]);
        
        // Insert new OTP
        $stmt = $db->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$email, $otp, $expiresAt]);
        
        // Send Email
        $subject = "Verify Your Email - " . APP_NAME;
        $htmlContent = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2>Welcome to " . APP_NAME . "!</h2>
                <p>Hello " . htmlspecialchars($name) . ",</p>
                <p>Please use the code below to verify your email address and complete your registration:</p>
                <div style='background: #f4f4f4; padding: 15px; text-align: center; font-size: 24px; font-weight: bold; letter-spacing: 5px; margin: 20px 0;'>
                    $otp
                </div>
                <p>This code will expire in 15 minutes.</p>
            </div>
        ";
        
        return sendEmail($email, $name, $subject, $htmlContent);
    } catch (Exception $e) {
        error_log("Verification Email Error: " . $e->getMessage());
        return false;
    }
}
?>
