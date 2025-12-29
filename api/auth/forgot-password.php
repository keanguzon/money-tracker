<?php
require_once dirname(dirname(__DIR__)) . '/config/app.php';
require_once dirname(dirname(__DIR__)) . '/config/database.php';
require_once dirname(dirname(__DIR__)) . '/config/mail.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$email = filter_var($data['email'] ?? '', FILTER_SANITIZE_EMAIL);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

$db = getDB();

// Check if user exists
$stmt = $db->prepare("SELECT id, full_name, username FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
    // For security, don't reveal if email exists or not, but for UX we might want to say "If an account exists..."
    // But for this app, let's be helpful
    echo json_encode(['success' => false, 'message' => 'Email not found']);
    exit;
}

// Generate OTP
$otp = generateOTP();
$expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));

// Store OTP
try {
    // Delete old OTPs for this email
    $stmt = $db->prepare("DELETE FROM password_resets WHERE email = ?");
    $stmt->execute([$email]);

    // Insert new OTP
    $stmt = $db->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$email, $otp, $expiresAt]);

    // Send Email
    $subject = "Password Reset OTP - " . APP_NAME;
    $htmlContent = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <h2>Password Reset Request</h2>
            <p>Hello " . htmlspecialchars($user['full_name'] ?? $user['username']) . ",</p>
            <p>You requested to reset your password. Use the code below to proceed:</p>
            <div style='background: #f4f4f4; padding: 15px; text-align: center; font-size: 24px; font-weight: bold; letter-spacing: 5px; margin: 20px 0;'>
                $otp
            </div>
            <p>This code will expire in 15 minutes.</p>
            <p>If you didn't request this, please ignore this email.</p>
        </div>
    ";

    if (sendEmail($email, $user['full_name'] ?? $user['username'], $subject, $htmlContent)) {
        echo json_encode(['success' => true, 'message' => 'OTP sent to your email']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to send email. Please try again later.']);
    }

} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}
?>
