<?php
/**
 * Forgot Password Page
 * Money Tracker Application
 */

$pageTitle = 'Forgot Password';
$pageStyles = ['login'];

require_once dirname(dirname(__DIR__)) . '/includes/auth.php';
requireGuest();

require_once dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="login-page">
    <!-- Theme Toggle -->
    <div class="theme-toggle-login">
        <button class="theme-toggle-btn" data-theme-toggle>
            <svg class="theme-icon-sun" xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="5"></circle>
                <line x1="12" y1="1" x2="12" y2="3"></line>
                <line x1="12" y1="21" x2="12" y2="23"></line>
                <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                <line x1="1" y1="12" x2="3" y2="12"></line>
                <line x1="21" y1="12" x2="23" y2="12"></line>
                <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
            </svg>
            <svg class="theme-icon-moon" style="display: none;" xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
            </svg>
        </button>
    </div>

    <div class="login-container">
        <!-- Banner Section -->
        <div class="login-banner">
            <div class="login-banner-content">
                <div class="login-banner-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                </div>
                <h1>Reset Password</h1>
                <p>Don't worry, it happens to the best of us. Follow the steps to recover your account.</p>
            </div>
        </div>

        <!-- Form Section -->
        <div class="login-form-container">
            <div class="login-header">
                <h2>Forgot Password</h2>
                <p id="step-description">Enter your email address to receive a verification code.</p>
            </div>

            <div id="alert-message" class="error-message" style="display: none;"></div>
            <div id="success-message" class="success-message" style="display: none;"></div>

            <!-- Step 1: Email -->
            <form id="email-form" class="login-form">
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <div class="input-icon-wrapper">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                        <input type="email" id="email" class="form-input" placeholder="Enter your email" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary login-btn">Send Code</button>
            </form>

            <!-- Step 2: OTP -->
            <form id="otp-form" class="login-form" style="display: none;">
                <div class="form-group">
                    <label class="form-label">Verification Code</label>
                    <div class="input-icon-wrapper">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        </svg>
                        <input type="text" id="otp" class="form-input" placeholder="Enter 6-digit code" maxlength="6" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary login-btn">Verify Code</button>
                <div class="form-options" style="justify-content: center; margin-top: 1rem;">
                    <a href="#" id="resend-code">Resend Code</a>
                </div>
            </form>

            <!-- Step 3: New Password -->
            <form id="password-form" class="login-form" style="display: none;">
                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <div class="input-icon-wrapper">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                        <input type="password" id="new-password" class="form-input" placeholder="Enter new password" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm Password</label>
                    <div class="input-icon-wrapper">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                        <input type="password" id="confirm-password" class="form-input" placeholder="Confirm new password" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary login-btn">Reset Password</button>
            </form>

            <div class="login-footer">
                Remember your password? <a href="<?= APP_URL ?>/pages/login/">Sign In</a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const emailForm = document.getElementById('email-form');
    const otpForm = document.getElementById('otp-form');
    const passwordForm = document.getElementById('password-form');
    const alertMsg = document.getElementById('alert-message');
    const successMsg = document.getElementById('success-message');
    const stepDesc = document.getElementById('step-description');
    
    let userEmail = '';
    let userOtp = '';

    function showMessage(type, message) {
        if (type === 'error') {
            alertMsg.textContent = message;
            alertMsg.style.display = 'flex';
            successMsg.style.display = 'none';
        } else {
            successMsg.textContent = message;
            successMsg.style.display = 'flex';
            alertMsg.style.display = 'none';
        }
    }

    function hideMessages() {
        alertMsg.style.display = 'none';
        successMsg.style.display = 'none';
    }

    // Step 1: Send Email
    emailForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        hideMessages();
        
        const email = document.getElementById('email').value;
        const btn = emailForm.querySelector('button');
        const originalText = btn.textContent;
        
        btn.disabled = true;
        btn.textContent = 'Sending...';

        try {
            const response = await fetch('<?= APP_URL ?>/api/auth/forgot-password.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email: email })
            });
            
            const data = await response.json();
            
            if (data.success) {
                userEmail = email;
                showMessage('success', data.message);
                emailForm.style.display = 'none';
                otpForm.style.display = 'block';
                stepDesc.textContent = 'Enter the 6-digit code sent to ' + email;
            } else {
                showMessage('error', data.message);
            }
        } catch (err) {
            showMessage('error', 'An error occurred. Please try again.');
        } finally {
            btn.disabled = false;
            btn.textContent = originalText;
        }
    });

    // Step 2: Verify OTP
    otpForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        hideMessages();
        
        const otp = document.getElementById('otp').value;
        const btn = otpForm.querySelector('button');
        const originalText = btn.textContent;
        
        btn.disabled = true;
        btn.textContent = 'Verifying...';

        try {
            const response = await fetch('<?= APP_URL ?>/api/auth/verify-otp.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email: userEmail, otp: otp })
            });
            
            const data = await response.json();
            
            if (data.success) {
                userOtp = otp;
                showMessage('success', 'Code verified!');
                otpForm.style.display = 'none';
                passwordForm.style.display = 'block';
                stepDesc.textContent = 'Create a new password for your account.';
            } else {
                showMessage('error', data.message);
            }
        } catch (err) {
            showMessage('error', 'An error occurred. Please try again.');
        } finally {
            btn.disabled = false;
            btn.textContent = originalText;
        }
    });

    // Step 3: Reset Password
    passwordForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        hideMessages();
        
        const newPass = document.getElementById('new-password').value;
        const confirmPass = document.getElementById('confirm-password').value;
        
        if (newPass !== confirmPass) {
            showMessage('error', 'Passwords do not match');
            return;
        }

        const btn = passwordForm.querySelector('button');
        const originalText = btn.textContent;
        
        btn.disabled = true;
        btn.textContent = 'Resetting...';

        try {
            const response = await fetch('<?= APP_URL ?>/api/auth/reset-password.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email: userEmail, otp: userOtp, password: newPass })
            });
            
            const data = await response.json();
            
            if (data.success) {
                showMessage('success', 'Password reset successfully! Redirecting to login...');
                setTimeout(() => {
                    window.location.href = '<?= APP_URL ?>/pages/login/';
                }, 2000);
            } else {
                showMessage('error', data.message);
            }
        } catch (err) {
            showMessage('error', 'An error occurred. Please try again.');
        } finally {
            btn.disabled = false;
            btn.textContent = originalText;
        }
    });

    // Resend Code
    document.getElementById('resend-code').addEventListener('click', function(e) {
        e.preventDefault();
        emailForm.dispatchEvent(new Event('submit'));
    });
});
</script>

<?php require_once dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
