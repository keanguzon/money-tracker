<?php
/**
 * Verify Email Page
 * Money Tracker Application
 */

$pageTitle = 'Verify Email';
$pageStyles = ['login'];

require_once dirname(dirname(__DIR__)) . '/includes/auth.php';
requireGuest();

$email = $_GET['email'] ?? '';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $otp = $_POST['otp'] ?? '';
    
    if (empty($email) || empty($otp)) {
        $error = 'Please enter the verification code.';
    } else {
        $db = getDB();
        
        // Verify OTP
        $stmt = $db->prepare("SELECT * FROM password_resets WHERE email = ? AND token = ? AND expires_at > NOW()");
        $stmt->execute([$email, $otp]);
        $reset = $stmt->fetch();
        
        if ($reset) {
            // Mark user as verified
            $stmt = $db->prepare("UPDATE users SET is_verified = TRUE WHERE email = ?");
            $stmt->execute([$email]);
            
            // Delete OTP
            $stmt = $db->prepare("DELETE FROM password_resets WHERE email = ?");
            $stmt->execute([$email]);
            
            // Log user in
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                setFlashMessage('success', 'Email verified successfully! Welcome to ' . APP_NAME);
                redirect('/pages/dashboard/');
            }
        } else {
            $error = 'Invalid or expired verification code.';
        }
    }
}

require_once dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="login-page">
    <div class="login-container">
        <div class="login-banner">
            <div class="login-banner-content">
                <div class="login-banner-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                </div>
                <h1>Verify Email</h1>
                <p>We've sent a verification code to your email address. Please enter it below to complete your registration.</p>
            </div>
        </div>

        <div class="login-form-section">
            <div class="login-header">
                <h2>Enter Code</h2>
                <p>Check your email for the 6-digit code.</p>
            </div>

            <?php if ($error): ?>
                <div class="error-message">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                    </svg>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="login-form">
                <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
                
                <div class="form-group">
                    <label class="form-label">Verification Code</label>
                    <div class="input-icon-wrapper">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        </svg>
                        <input type="text" name="otp" class="form-input" placeholder="Enter 6-digit code" maxlength="6" required autofocus>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary login-btn">Verify Account</button>
            </form>
            
            <div class="login-footer">
                Didn't receive the code? <a href="#" id="resend-link">Resend Code</a>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('resend-link').addEventListener('click', async function(e) {
    e.preventDefault();
    const link = this;
    const originalText = link.textContent;
    
    if (link.classList.contains('disabled')) return;
    
    link.textContent = 'Sending...';
    link.classList.add('disabled');
    
    try {
        const response = await fetch('<?= APP_URL ?>/api/auth/send-verification-otp.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                email: '<?= htmlspecialchars($email) ?>'
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Code resent successfully!');
        } else {
            alert(data.message || 'Failed to resend code');
        }
    } catch (err) {
        alert('An error occurred');
    } finally {
        link.textContent = 'Resent!';
        setTimeout(() => {
            link.textContent = originalText;
            link.classList.remove('disabled');
        }, 30000);
    }
});
</script>

<?php require_once dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
