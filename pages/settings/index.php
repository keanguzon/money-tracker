<?php
/**
 * Settings Page
 * Money Tracker Application
 */

$pageTitle = 'Settings';
$pageStyles = ['dashboard', 'settings'];
$currentPage = 'settings';

require_once dirname(dirname(__DIR__)) . '/includes/auth.php';
requireAuth();

$user = getCurrentUser();

// Handle form submissions
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_profile') {
        $result = updateProfile($user['id'], [
            'full_name' => $_POST['full_name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'currency' => $_POST['currency'] ?? 'PHP'
        ]);
        
        if ($result['success']) {
            $success = 'Profile updated successfully!';
            $user = getCurrentUser(); // Refresh user data
        } else {
            $error = $result['message'];
        }
    }
    
    if ($action === 'change_password') {
        $result = updatePassword($user['id'], $_POST['current_password'], $_POST['new_password']);
        
        if ($result['success']) {
            $success = 'Password changed successfully!';
        } else {
            $error = $result['message'];
        }
    }
}

require_once dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="dashboard-layout">
    <?php require_once dirname(dirname(__DIR__)) . '/includes/sidebar.php'; ?>

    <main class="main-content">
        <!-- Top Header -->
        <header class="top-header">
            <div class="header-left">
                <button class="menu-toggle">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="3" y1="12" x2="21" y2="12"></line>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <line x1="3" y1="18" x2="21" y2="18"></line>
                    </svg>
                </button>
                <h1 class="page-title">Settings</h1>
            </div>
            <div class="header-right">
                <button class="header-btn" data-theme-toggle>
                    <svg class="theme-icon-sun" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
                    <svg class="theme-icon-moon" style="display: none;" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                    </svg>
                </button>
            </div>
        </header>

        <!-- Page Content -->
        <div class="dashboard-content settings-page">
            <div class="settings-header">
                <h1>Settings</h1>
                <p>Manage your account settings and preferences</p>
            </div>

            <?php if ($success): ?>
                <div class="success-message" style="margin-bottom: 1.5rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="error-message" style="margin-bottom: 1.5rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                    </svg>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="settings-layout">
                <!-- Settings Navigation -->
                <nav class="settings-nav">
                    <a href="#profile" class="settings-nav-item active">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        Profile
                    </a>
                    <a href="#appearance" class="settings-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="3"></circle>
                            <path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"></path>
                        </svg>
                        Appearance
                    </a>
                    <a href="#preferences" class="settings-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="4" y1="21" x2="4" y2="14"></line>
                            <line x1="4" y1="10" x2="4" y2="3"></line>
                            <line x1="12" y1="21" x2="12" y2="12"></line>
                            <line x1="12" y1="8" x2="12" y2="3"></line>
                            <line x1="20" y1="21" x2="20" y2="16"></line>
                            <line x1="20" y1="12" x2="20" y2="3"></line>
                            <line x1="1" y1="14" x2="7" y2="14"></line>
                            <line x1="9" y1="8" x2="15" y2="8"></line>
                            <line x1="17" y1="16" x2="23" y2="16"></line>
                        </svg>
                        Preferences
                    </a>
                    <a href="#security" class="settings-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        </svg>
                        Security
                    </a>
                </nav>

                <!-- Settings Content -->
                <div class="settings-content">
                    <!-- Profile Section -->
                    <section id="profile" class="settings-section">
                        <div class="settings-section-header">
                            <h2>Profile Information</h2>
                            <p>Update your personal information and email address</p>
                        </div>
                        <div class="settings-section-body">
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="update_profile">
                                
                                <div class="profile-header">
                                    <div class="profile-avatar-large">
                                        <?= strtoupper(substr($user['full_name'] ?? $user['username'], 0, 1)) ?>
                                        <label class="avatar-upload">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                                                <circle cx="12" cy="13" r="4"></circle>
                                            </svg>
                                            <input type="file" accept="image/*">
                                        </label>
                                    </div>
                                    <div class="profile-info">
                                        <h3><?= htmlspecialchars($user['full_name'] ?? $user['username']) ?></h3>
                                        <p><?= htmlspecialchars($user['email']) ?></p>
                                    </div>
                                </div>

                                <div class="settings-form-grid">
                                    <div class="form-group">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" name="full_name" class="form-input" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" placeholder="Enter your full name">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Username</label>
                                        <input type="text" class="form-input" value="<?= htmlspecialchars($user['username']) ?>" disabled style="opacity: 0.6;">
                                    </div>
                                    <div class="form-group full-width">
                                        <label class="form-label">Email Address</label>
                                        <input type="email" name="email" class="form-input" value="<?= htmlspecialchars($user['email']) ?>" placeholder="Enter your email">
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary" style="margin-top: 1rem;">
                                    Save Changes
                                </button>
                            </form>
                        </div>
                    </section>

                    <!-- Appearance Section -->
                    <section id="appearance" class="settings-section">
                        <div class="settings-section-header">
                            <h2>Appearance</h2>
                            <p>Customize how the application looks</p>
                        </div>
                        <div class="settings-section-body">
                            <h4 style="font-size: 0.9375rem; font-weight: 600; color: var(--text-primary); margin-bottom: 0.5rem;">Theme</h4>
                            <p style="font-size: 0.8125rem; color: var(--text-muted); margin-bottom: 1rem;">Select your preferred theme</p>
                            
                            <div class="theme-options">
                                <label class="theme-option" data-theme="light">
                                    <div class="theme-preview light">
                                        <div class="preview-sidebar"></div>
                                        <div class="preview-content"></div>
                                    </div>
                                    <span>Light</span>
                                </label>
                                <label class="theme-option" data-theme="dark">
                                    <div class="theme-preview dark">
                                        <div class="preview-sidebar"></div>
                                        <div class="preview-content"></div>
                                    </div>
                                    <span>Dark</span>
                                </label>
                                <label class="theme-option" data-theme="system">
                                    <div class="theme-preview system"></div>
                                    <span>System</span>
                                </label>
                            </div>
                        </div>
                    </section>

                    <!-- Preferences Section -->
                    <section id="preferences" class="settings-section">
                        <div class="settings-section-header">
                            <h2>Preferences</h2>
                            <p>Configure your application preferences</p>
                        </div>
                        <div class="settings-section-body">
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="update_profile">
                                
                                <h4 style="font-size: 0.9375rem; font-weight: 600; color: var(--text-primary); margin-bottom: 0.5rem;">Currency</h4>
                                <p style="font-size: 0.8125rem; color: var(--text-muted); margin-bottom: 1rem;">Select your preferred currency for displaying amounts</p>
                                
                                <div class="currency-options">
                                    <label class="currency-option <?= $user['currency'] === 'PHP' ? 'active' : '' ?>">
                                        <input type="radio" name="currency" value="PHP" <?= $user['currency'] === 'PHP' ? 'checked' : '' ?> style="display: none;">
                                        <div class="currency-symbol">₱</div>
                                        <div class="currency-code">PHP</div>
                                    </label>
                                    <label class="currency-option <?= $user['currency'] === 'USD' ? 'active' : '' ?>">
                                        <input type="radio" name="currency" value="USD" <?= $user['currency'] === 'USD' ? 'checked' : '' ?> style="display: none;">
                                        <div class="currency-symbol">$</div>
                                        <div class="currency-code">USD</div>
                                    </label>
                                    <label class="currency-option <?= $user['currency'] === 'EUR' ? 'active' : '' ?>">
                                        <input type="radio" name="currency" value="EUR" <?= $user['currency'] === 'EUR' ? 'checked' : '' ?> style="display: none;">
                                        <div class="currency-symbol">€</div>
                                        <div class="currency-code">EUR</div>
                                    </label>
                                    <label class="currency-option <?= $user['currency'] === 'GBP' ? 'active' : '' ?>">
                                        <input type="radio" name="currency" value="GBP" <?= $user['currency'] === 'GBP' ? 'checked' : '' ?> style="display: none;">
                                        <div class="currency-symbol">£</div>
                                        <div class="currency-code">GBP</div>
                                    </label>
                                </div>

                                <button type="submit" class="btn btn-primary" style="margin-top: 1.5rem;">
                                    Save Preferences
                                </button>
                            </form>

                            <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border-color);">
                                <div class="settings-toggle-item">
                                    <div class="toggle-info">
                                        <h4>Email Notifications</h4>
                                        <p>Receive weekly summary of your finances</p>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" checked>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>

                                <div class="settings-toggle-item">
                                    <div class="toggle-info">
                                        <h4>Budget Alerts</h4>
                                        <p>Get notified when approaching budget limits</p>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" checked>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Security Section -->
                    <section id="security" class="settings-section">
                        <div class="settings-section-header">
                            <h2>Security</h2>
                            <p>Manage your password and account security</p>
                        </div>
                        <div class="settings-section-body">
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="change_password">
                                
                                <div class="form-group">
                                    <label class="form-label">Current Password</label>
                                    <input type="password" name="current_password" class="form-input" placeholder="Enter current password" required>
                                </div>
                                <div class="settings-form-grid">
                                    <div class="form-group">
                                        <label class="form-label">New Password</label>
                                        <input type="password" name="new_password" class="form-input" placeholder="Enter new password" required minlength="8">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Confirm New Password</label>
                                        <input type="password" name="confirm_password" class="form-input" placeholder="Confirm new password" required>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary" style="margin-top: 1rem;">
                                    Change Password
                                </button>
                            </form>
                        </div>
                    </section>

                    <!-- Danger Zone -->
                    <section class="settings-section danger-zone">
                        <div class="settings-section-header">
                            <h2>Danger Zone</h2>
                            <p>Irreversible and destructive actions</p>
                        </div>
                        <div class="settings-section-body">
                            <div class="danger-item">
                                <div class="danger-info">
                                    <h4>Export Data</h4>
                                    <p>Download all your transactions and account data</p>
                                </div>
                                <button class="btn btn-secondary">Export</button>
                            </div>
                            <div class="danger-item">
                                <div class="danger-info">
                                    <h4>Delete All Transactions</h4>
                                    <p>Permanently delete all your transaction history</p>
                                </div>
                                <button class="btn btn-danger" onclick="Utils.confirm('Are you sure? This action cannot be undone.', () => {})">Delete All</button>
                            </div>
                            <div class="danger-item">
                                <div class="danger-info">
                                    <h4>Delete Account</h4>
                                    <p>Permanently delete your account and all associated data</p>
                                </div>
                                <button class="btn btn-danger" onclick="Utils.confirm('Are you sure you want to delete your account? This cannot be undone.', () => {})">Delete Account</button>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
// Theme options
document.querySelectorAll('.theme-option').forEach(option => {
    option.addEventListener('click', function() {
        document.querySelectorAll('.theme-option').forEach(o => o.classList.remove('active'));
        this.classList.add('active');
        
        const theme = this.dataset.theme;
        if (theme === 'dark') {
            document.documentElement.classList.add('dark');
            localStorage.setItem('moneytrack_darkmode', 'true');
        } else if (theme === 'light') {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('moneytrack_darkmode', 'false');
        } else {
            localStorage.removeItem('moneytrack_darkmode');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            document.documentElement.classList.toggle('dark', prefersDark);
        }
    });
});

// Currency options
document.querySelectorAll('.currency-option').forEach(option => {
    option.addEventListener('click', function() {
        document.querySelectorAll('.currency-option').forEach(o => o.classList.remove('active'));
        this.classList.add('active');
    });
});

// Set initial theme option
const savedTheme = localStorage.getItem('moneytrack_darkmode');
if (savedTheme === 'true') {
    document.querySelector('[data-theme="dark"]').classList.add('active');
} else if (savedTheme === 'false') {
    document.querySelector('[data-theme="light"]').classList.add('active');
} else {
    document.querySelector('[data-theme="system"]').classList.add('active');
}

// Smooth scroll for nav items
document.querySelectorAll('.settings-nav-item').forEach(item => {
    item.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
        document.querySelectorAll('.settings-nav-item').forEach(i => i.classList.remove('active'));
        this.classList.add('active');
    });
});
</script>

<?php require_once dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
