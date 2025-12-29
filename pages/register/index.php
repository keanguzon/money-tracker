<?php
/**
 * Register Page
 * Money Tracker Application
 */

$pageTitle = 'Create Account';
$pageStyles = ['login'];

require_once dirname(dirname(__DIR__)) . '/includes/auth.php';
requireGuest();

$error = '';
$errors = [];

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($fullName)) {
        $errors['full_name'] = 'Full name is required';
    }
    
    if (empty($username)) {
        $errors['username'] = 'Username is required';
    } elseif (strlen($username) < 3) {
        $errors['username'] = 'Username must be at least 3 characters';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors['username'] = 'Username can only contain letters, numbers, and underscores';
    }
    
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address';
    }
    
    if (empty($password)) {
        $errors['password'] = 'Password is required';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters';
    }
    
    if ($password !== $confirmPassword) {
        $errors['confirm_password'] = 'Passwords do not match';
    }
    
    // If no validation errors, attempt registration
    if (empty($errors)) {
        $result = registerUser($username, $email, $password, $fullName);
        if ($result['success']) {
            // Send verification email
            $emailSent = sendVerificationEmail($email, $fullName);
            // Store registration data in session for modal
            $_SESSION['pending_verification'] = [
                'email' => $email,
                'full_name' => $fullName,
                'email_sent' => $emailSent
            ];
            // Redirect to same page to show modal
            redirect('/pages/register/?verify=1');
        } else {
            $error = $result['message'];
        }
    }
}

// Check if we should show verification modal
$showVerifyModal = isset($_GET['verify']) && isset($_SESSION['pending_verification']);
$verificationData = $_SESSION['pending_verification'] ?? null;

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
                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="8.5" cy="7" r="4"></circle>
                        <line x1="20" y1="8" x2="20" y2="14"></line>
                        <line x1="23" y1="11" x2="17" y2="11"></line>
                    </svg>
                </div>
                <h1>Join<br><?= APP_NAME ?></h1>
                <p>Start your journey to financial freedom. Create an account and take the first step towards managing your money smarter.</p>
                
                <div class="login-features">
                    <div class="login-feature">
                        <div class="login-feature-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                            </svg>
                        </div>
                        <span>Secure & Private</span>
                    </div>
                    <div class="login-feature">
                        <div class="login-feature-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                                <line x1="1" y1="10" x2="23" y2="10"></line>
                            </svg>
                        </div>
                        <span>Free Forever</span>
                    </div>
                    <div class="login-feature">
                        <div class="login-feature-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                        </div>
                        <span>Join 10k+ Users</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Register Form Section -->
        <div class="login-form-section">
            <div class="login-header">
                <h2>Create Account</h2>
                <p>Fill in your details to get started</p>
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

            <form class="login-form" method="POST" action="">
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <div class="input-icon-wrapper">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <input type="text" name="full_name" class="form-input <?= isset($errors['full_name']) ? 'error' : '' ?>" placeholder="Enter your full name" value="<?= isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : '' ?>">
                    </div>
                    <?php if (isset($errors['full_name'])): ?>
                        <span class="form-error"><?= $errors['full_name'] ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label">Username</label>
                    <div class="input-icon-wrapper">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <input type="text" name="username" class="form-input <?= isset($errors['username']) ? 'error' : '' ?>" placeholder="Choose a username" value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
                    </div>
                    <?php if (isset($errors['username'])): ?>
                        <span class="form-error"><?= $errors['username'] ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <div class="input-icon-wrapper">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                        <input type="email" name="email" class="form-input <?= isset($errors['email']) ? 'error' : '' ?>" placeholder="Enter your email" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                    </div>
                    <?php if (isset($errors['email'])): ?>
                        <span class="form-error"><?= $errors['email'] ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="input-icon-wrapper">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                        <input type="password" name="password" id="password" class="form-input <?= isset($errors['password']) ? 'error' : '' ?>" placeholder="Create a password">
                    </div>
                    <?php if (isset($errors['password'])): ?>
                        <span class="form-error"><?= $errors['password'] ?></span>
                    <?php endif; ?>
                    <div class="password-strength" id="passwordStrength" style="display: none;">
                        <div class="strength-bar">
                            <div class="strength-fill" id="strengthFill"></div>
                        </div>
                        <span class="strength-text" id="strengthText"></span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Confirm Password</label>
                    <div class="input-icon-wrapper">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                        <input type="password" name="confirm_password" class="form-input <?= isset($errors['confirm_password']) ? 'error' : '' ?>" placeholder="Confirm your password">
                    </div>
                    <?php if (isset($errors['confirm_password'])): ?>
                        <span class="form-error"><?= $errors['confirm_password'] ?></span>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-primary login-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="8.5" cy="7" r="4"></circle>
                        <line x1="20" y1="8" x2="20" y2="14"></line>
                        <line x1="23" y1="11" x2="17" y2="11"></line>
                    </svg>
                    Create Account
                </button>
            </form>

            <div class="login-footer">
                Already have an account? <a href="<?= APP_URL ?>/pages/login/">Sign in</a>
            </div>
        </div>
    </div>
</div>

<!-- Verify Email Modal -->
<?php if ($showVerifyModal && $verificationData): ?>
<div class="modal-overlay" id="verifyModal">
    <div class="modal-container">
        <div class="modal-header">
            <h3>Verify Your Email</h3>
        </div>
        <div class="modal-body">
            <div class="verify-email-content">
                <div class="verify-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                        <polyline points="22,6 12,13 2,6"></polyline>
                    </svg>
                </div>
                <p style="text-align: center; margin-bottom: 20px;">
                    We've sent a 6-digit verification code to<br>
                    <strong><?= htmlspecialchars($verificationData['email']) ?></strong>
                </p>
                
                <?php if (!$verificationData['email_sent']): ?>
                    <div class="error-message" style="margin-bottom: 15px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="15" y1="9" x2="9" y2="15"></line>
                            <line x1="9" y1="9" x2="15" y2="15"></line>
                        </svg>
                        Failed to send email. Please try resending.
                    </div>
                <?php endif; ?>
                
                <div id="verifyError" class="error-message" style="display: none; margin-bottom: 15px;">

// Verify Email Modal
<?php if ($showVerifyModal && $verificationData): ?>
const verifyModal = document.getElementById('verifyModal');
const verifyForm = document.getElementById('verifyForm');
const otpInput = document.getElementById('otpInput');
const verifyBtn = document.getElementById('verifyBtn');
const verifyError = document.getElementById('verifyError');
const verifyErrorText = document.getElementById('verifyErrorText');
const resendLink = document.getElementById('resendLink');

// Auto-focus OTP input
setTimeout(() => otpInput.focus(), 100);

// Handle form submission
verifyForm.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const otp = otpInput.value.trim();
    if (otp.length !== 6) {
        showError('Please enter a 6-digit code');
        return;
    }
    
    verifyBtn.disabled = true;
    verifyBtn.textContent = 'Verifying...';
    verifyError.style.display = 'none';
    
    try {
        const response = await fetch('<?= APP_URL ?>/api/auth/verify-otp.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                email: '<?= htmlspecialchars($verificationData['email'] ?? '') ?>',
                otp: otp
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            window.location.href = '<?= APP_URL ?>/pages/dashboard/';
        } else {
            showError(data.message || 'Invalid or expired code');
            verifyBtn.disabled = false;
            verifyBtn.textContent = 'Verify Account';
        }
    } catch (err) {
        showError('An error occurred. Please try again.');
        verifyBtn.disabled = false;
        verifyBtn.textContent = 'Verify Account';
    }
});

// Handle resend
resendLink.addEventListener('click', async function(e) {
    e.preventDefault();
    
    if (this.classList.contains('disabled')) return;
    
    const originalText = this.textContent;
    this.textContent = 'Sending...';
    this.classList.add('disabled');
    
    try {
        const response = await fetch('<?= APP_URL ?>/api/auth/send-verification-otp.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                email: '<?= htmlspecialchars($verificationData['email'] ?? '') ?>',
                full_name: '<?= htmlspecialchars($verificationData['full_name'] ?? '') ?>'
            })
        });

/* Verify Email Modal */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    padding: 20px;
}

.modal-container {
    background: var(--card-bg);
    border-radius: 12px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    max-width: 450px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    padding: 24px;
    border-bottom: 1px solid var(--border);
}

.modal-header h3 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--text-primary);
}

.modal-body {
    padding: 24px;
}

.verify-email-content {
    text-align: center;
}

.verify-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 80px;
    height: 80px;
    background: var(--primary-light, #dbeafe);
    border-radius: 50%;
    margin-bottom: 20px;
}

.verify-icon svg {
    color: var(--primary);
}

#resendLink.disabled {
    pointer-events: none;
    opacity: 0.5;
}
        
        const data = await response.json();
        
        if (data.success) {
            showError('Code resent successfully!', 'success');
        } else {
            showError(data.message || 'Failed to resend code');
        }
    } catch (err) {
        showError('An error occurred');
    } finally {
        this.textContent = 'Resent!';
        setTimeout(() => {
            this.textContent = originalText;
            this.classList.remove('disabled');
        }, 30000);
    }
});

function showError(message, type = 'error') {
    verifyErrorText.textContent = message;
    verifyError.style.display = 'flex';
    if (type === 'success') {
        verifyError.style.backgroundColor = 'var(--success-bg, #d1fae5)';
        verifyError.style.color = 'var(--success, #10b981)';
    } else {
        verifyError.style.backgroundColor = '';
        verifyError.style.color = '';
    }
}

// Prevent modal close
verifyModal.addEventListener('click', function(e) {
    if (e.target === verifyModal) {
        // Don't close on backdrop click
    }
});
<?php endif; ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                    </svg>
                    <span id="verifyErrorText"></span>
                </div>

                <form id="verifyForm">
                    <div class="form-group">
                        <label class="form-label">Verification Code</label>
                        <input type="text" id="otpInput" class="form-input" placeholder="Enter 6-digit code" maxlength="6" pattern="[0-9]{6}" required autofocus style="text-align: center; font-size: 1.5rem; letter-spacing: 0.5rem;">
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-bottom: 10px;" id="verifyBtn">
                        Verify Account
                    </button>
                </form>
                
                <p style="text-align: center; font-size: 0.875rem; color: var(--text-muted);">
                    Didn't receive the code? 
                    <a href="#" id="resendLink" style="color: var(--primary);">Resend Code</a>
                </p>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
// Password strength indicator
const passwordInput = document.getElementById('password');
const strengthContainer = document.getElementById('passwordStrength');
const strengthFill = document.getElementById('strengthFill');
const strengthText = document.getElementById('strengthText');

passwordInput.addEventListener('input', function() {
    const password = this.value;
    
    if (password.length === 0) {
        strengthContainer.style.display = 'none';
        return;
    }
    
    strengthContainer.style.display = 'block';
    
    let strength = 0;
    if (password.length >= 8) strength++;
    if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
    if (password.match(/\d/)) strength++;
    if (password.match(/[^a-zA-Z\d]/)) strength++;
    
    const colors = ['#ef4444', '#f97316', '#eab308', '#10b981'];
    const texts = ['Weak', 'Fair', 'Good', 'Strong'];
    const widths = ['25%', '50%', '75%', '100%'];
    
    strengthFill.style.width = widths[strength - 1] || '0%';
    strengthFill.style.backgroundColor = colors[strength - 1] || '#ef4444';
    strengthText.textContent = texts[strength - 1] || 'Very Weak';
    strengthText.style.color = colors[strength - 1] || '#ef4444';
});
</script>

<style>
.form-error {
    display: block;
    font-size: 0.75rem;
    color: var(--danger);
    margin-top: 0.25rem;
}
.form-input.error {
    border-color: var(--danger) !important;
}
</style>

<?php require_once dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
