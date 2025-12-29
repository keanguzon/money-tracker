<?php
/**
 * Accounts Management Page
 * E-wallets, Banks, Cash, Credit Cards
 */

$pageTitle = 'Accounts';
$pageStyles = ['dashboard', 'accounts'];
$currentPage = 'accounts';

require_once dirname(dirname(__DIR__)) . '/includes/auth.php';
requireAuth();

$user = getCurrentUser();
$db = getDB();

// Get all accounts
$stmt = $db->prepare("
    SELECT * FROM accounts 
    WHERE user_id = ?
    ORDER BY is_active DESC, type, name
");
$stmt->execute([$user['id']]);
$accounts = $stmt->fetchAll() ?: [];

// Calculate total net worth (only accounts marked to include)
$stmt = $db->prepare("
    SELECT COALESCE(SUM(balance), 0) as net_worth
    FROM accounts 
    WHERE user_id = ? AND is_active = TRUE AND include_in_networth = TRUE
");
$stmt->execute([$user['id']]);
$result = $stmt->fetch();
$netWorth = $result ? $result['net_worth'] : 0;

// Group accounts by type
$groupedAccounts = [
    'ewallet' => [],
    'bank' => [],
    'cash' => [],
    'credit_card' => [],
    'investment' => []
];

foreach ($accounts as $account) {
    $groupedAccounts[$account['type']][] = $account;
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
                <h1 class="page-title">Accounts</h1>
            </div>
            <div class="header-right">
                <button class="header-btn" data-theme-toggle>
                    <svg class="theme-icon-sun" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="5"></circle>
                        <line x1="12" y1="1" x2="12" y2="3"></line>
                    </svg>
                    <svg class="theme-icon-moon" style="display: none;" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                    </svg>
                </button>
            </div>
        </header>

        <!-- Page Content -->
        <div class="dashboard-content">
            <!-- Net Worth Card -->
            <div class="card" style="margin-bottom: 2rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none;">
                <div class="card-body" style="padding: 2rem;">
                    <div style="font-size: 0.875rem; opacity: 0.9; margin-bottom: 0.5rem;">Total Net Worth</div>
                    <div style="font-size: 2.5rem; font-weight: 800; margin-bottom: 0.5rem;">
                        <?= formatCurrency($netWorth, $user['currency']) ?>
                    </div>
                    <div style="font-size: 0.875rem; opacity: 0.8;">Across <?= count(array_filter($accounts, fn($a) => $a['is_active'])) ?> active accounts</div>
                </div>
            </div>

            <!-- Add Account Button -->
            <div style="margin-bottom: 2rem;">
                <button class="btn btn-primary" onclick="openModal('addAccountModal')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    Add New Account
                </button>
            </div>

            <!-- E-Wallets Section -->
            <?php if (!empty($groupedAccounts['ewallet'])): ?>
            <div class="card" style="margin-bottom: 2rem;">
                <div class="card-header">
                    <h3>E-Wallets</h3>
                    <span class="badge"><?= count($groupedAccounts['ewallet']) ?></span>
                </div>
                <div class="card-body">
                    <div class="accounts-list">
                        <?php foreach ($groupedAccounts['ewallet'] as $account): ?>
                            <div class="account-card" style="display: flex; align-items: center; padding: 1.25rem; background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; margin-bottom: 1rem;">
                                <?php 
                                $logoPath = APP_URL . '/assets/images/logos/' . ($account['icon'] ?: 'wallet.png');
                                $logoExists = file_exists(dirname(dirname(__DIR__)) . '/assets/images/logos/' . ($account['icon'] ?: 'wallet.png'));
                                ?>
                                <div style="width: 48px; height: 48px; border-radius: 12px; background: white; display: flex; align-items: center; justify-content: center; margin-right: 1rem; padding: 4px; border: 1px solid #e5e7eb;">
                                    <?php if ($logoExists && !empty($account['icon'])): ?>
                                        <img src="<?= $logoPath ?>" alt="<?= htmlspecialchars($account['name']) ?>" style="width: 100%; height: 100%; object-fit: contain; border-radius: 8px;">
                                    <?php else: ?>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="<?= $account['color'] ?>" stroke-width="2">
                                            <rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect>
                                            <line x1="12" y1="18" x2="12.01" y2="18"></line>
                                        </svg>
                                    <?php endif; ?>
                                </div>
                                <div style="flex: 1;">
                                    <div style="font-weight: 600; font-size: 1rem; margin-bottom: 0.25rem;"><?= htmlspecialchars($account['name']) ?></div>
                                    <div style="font-size: 0.875rem; color: var(--text-muted);">
                                        <?php if ($account['is_savings']): ?>
                                            <span style="color: var(--success);">Savings • <?= number_format($account['interest_rate'], 2) ?>% interest</span>
                                        <?php else: ?>
                                            Wallet
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div style="text-align: right; margin-right: 1rem;">
                                    <div style="font-size: 1.5rem; font-weight: 700; color: <?= $account['balance'] >= 0 ? 'var(--success)' : 'var(--danger)' ?>;">
                                        <?= formatCurrency($account['balance'], $user['currency']) ?>
                                    </div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">
                                        <?= $account['is_active'] ? 'Active' : 'Inactive' ?>
                                    </div>
                                </div>
                                <button class="btn btn-sm btn-secondary" onclick="editAccount(<?= $account['id'] ?>)">Edit</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Banks Section -->
            <?php if (!empty($groupedAccounts['bank'])): ?>
            <div class="card" style="margin-bottom: 2rem;">
                <div class="card-header">
                    <h3>Banks</h3>
                    <span class="badge"><?= count($groupedAccounts['bank']) ?></span>
                </div>
                <div class="card-body">
                    <div class="accounts-list">
                        <?php foreach ($groupedAccounts['bank'] as $account): ?>
                            <div class="account-card" style="display: flex; align-items: center; padding: 1.25rem; background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; margin-bottom: 1rem;">
                                <?php 
                                $logoPath = APP_URL . '/assets/images/logos/' . ($account['icon'] ?: 'bank.png');
                                $logoExists = file_exists(dirname(dirname(__DIR__)) . '/assets/images/logos/' . ($account['icon'] ?: 'bank.png'));
                                ?>
                                <div style="width: 48px; height: 48px; border-radius: 12px; background: white; display: flex; align-items: center; justify-content: center; margin-right: 1rem; padding: 4px; border: 1px solid #e5e7eb;">
                                    <?php if ($logoExists && $account['icon']): ?>
                                        <img src="<?= $logoPath ?>" alt="<?= htmlspecialchars($account['name']) ?>" style="width: 100%; height: 100%; object-fit: contain; border-radius: 8px;">
                                    <?php else: ?>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="<?= $account['color'] ?>" stroke-width="2">
                                            <line x1="12" y1="1" x2="12" y2="23"></line>
                                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                        </svg>
                                    <?php endif; ?>
                                </div>
                                <div style="flex: 1;">
                                    <div style="font-weight: 600; font-size: 1rem; margin-bottom: 0.25rem;"><?= htmlspecialchars($account['name']) ?></div>
                                    <div style="font-size: 0.875rem; color: var(--text-muted);">
                                        <?php if ($account['interest_rate'] > 0): ?>
                                            <span style="color: var(--success);"><?= number_format($account['interest_rate'], 2) ?>% interest</span>
                                        <?php else: ?>
                                            Bank Account
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div style="text-align: right; margin-right: 1rem;">
                                    <div style="font-size: 1.5rem; font-weight: 700; color: <?= $account['balance'] >= 0 ? 'var(--success)' : 'var(--danger)' ?>;">
                                        <?= formatCurrency($account['balance'], $user['currency']) ?>
                                    </div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">
                                        <?= $account['is_active'] ? 'Active' : 'Inactive' ?>
                                    </div>
                                </div>
                                <button class="btn btn-sm btn-secondary" onclick="editAccount(<?= $account['id'] ?>)">Edit</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Cash Section -->
            <?php if (!empty($groupedAccounts['cash'])): ?>
            <div class="card" style="margin-bottom: 2rem;">
                <div class="card-header">
                    <h3>Cash</h3>
                    <span class="badge"><?= count($groupedAccounts['cash']) ?></span>
                </div>
                <div class="card-body">
                    <div class="accounts-list">
                        <?php foreach ($groupedAccounts['cash'] as $account): ?>
                            <div class="account-card" style="display: flex; align-items: center; padding: 1.25rem; background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; margin-bottom: 1rem;">
                                <?php 
                                $logoPath = APP_URL . '/assets/images/logos/' . ($account['icon'] ?: 'cash.png');
                                $logoExists = file_exists(dirname(dirname(__DIR__)) . '/assets/images/logos/' . ($account['icon'] ?: 'cash.png'));
                                ?>
                                <div style="width: 48px; height: 48px; border-radius: 12px; background: white; display: flex; align-items: center; justify-content: center; margin-right: 1rem; padding: 4px; border: 1px solid #e5e7eb;">
                                    <?php if ($logoExists && $account['icon']): ?>
                                        <img src="<?= $logoPath ?>" alt="<?= htmlspecialchars($account['name']) ?>" style="width: 100%; height: 100%; object-fit: contain; border-radius: 8px;">
                                    <?php else: ?>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="<?= $account['color'] ?>" stroke-width="2">
                                            <path d="M20 12V8H6a2 2 0 0 1-2-2c0-1.1.9-2 2-2h12v4"></path>
                                            <path d="M4 6v12c0 1.1.9 2 2 2h14v-4"></path>
                                            <path d="M18 12a2 2 0 0 0-2 2c0 1.1.9 2 2 2h4v-4h-4z"></path>
                                        </svg>
                                    <?php endif; ?>
                                </div>
                                <div style="flex: 1;">
                                    <div style="font-weight: 600; font-size: 1rem; margin-bottom: 0.25rem;"><?= htmlspecialchars($account['name']) ?></div>
                                    <div style="font-size: 0.875rem; color: var(--text-muted);">Physical Cash</div>
                                </div>
                                <div style="text-align: right; margin-right: 1rem;">
                                    <div style="font-size: 1.5rem; font-weight: 700; color: var(--success);">
                                        <?= formatCurrency($account['balance'], $user['currency']) ?>
                                    </div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">
                                        <?= $account['is_active'] ? 'Active' : 'Inactive' ?>
                                    </div>
                                </div>
                                <button class="btn btn-sm btn-secondary" onclick="editAccount(<?= $account['id'] ?>)">Edit</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (empty($accounts)): ?>
                <div class="empty-state" style="text-align: center; padding: 4rem 2rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin: 0 auto 1rem; opacity: 0.3;">
                        <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                        <line x1="1" y1="10" x2="23" y2="10"></line>
                    </svg>
                    <h3 style="margin-bottom: 0.5rem;">No Accounts Yet</h3>
                    <p style="color: var(--text-muted); margin-bottom: 1.5rem;">Start by adding your first account to track your money</p>
                    <button class="btn btn-primary" onclick="openModal('addAccountModal')">Add Account</button>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<!-- Add Account Modal -->
<div class="modal" id="addAccountModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add New Account</h3>
            <button class="modal-close" onclick="closeModal('addAccountModal')">&times;</button>
        </div>
        <form id="addAccountForm" onsubmit="handleAddAccount(event)">
            <input type="hidden" id="selectedAccountType" name="type">
            <input type="hidden" id="selectedAccountIcon" name="icon">
            <input type="hidden" id="selectedAccountName" name="name">
            <input type="hidden" id="selectedAccountColor" name="color">
            <input type="hidden" id="selectedIsSavings" name="is_savings" value="0">
            
            <div class="modal-body">
                <!-- Wallet Category -->
                <div class="account-category">
                    <h4 style="font-size: 0.875rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.75rem;">Wallet</h4>
                    <div class="account-options">
                        <div class="account-option" data-type="ewallet" data-icon="gcash.png" data-name="GCash" data-color="#007DFE" data-savings="0">
                            <div class="account-option-logo">
                                <img src="<?= APP_URL ?>/assets/images/logos/gcash.png" alt="GCash">
                            </div>
                            <span>GCash</span>
                        </div>
                        <div class="account-option" data-type="ewallet" data-icon="maya.png" data-name="Maya" data-color="#10b981" data-savings="0">
                            <div class="account-option-logo">
                                <img src="<?= APP_URL ?>/assets/images/logos/maya.png" alt="Maya">
                            </div>
                            <span>Maya</span>
                        </div>
                        <div class="account-option" data-type="cash" data-icon="" data-name="Cash on Hand" data-color="#86efac" data-savings="0">
                            <div class="account-option-logo" style="background: #86efac;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                                    <path d="M20 12V8H6a2 2 0 0 1-2-2c0-1.1.9-2 2-2h12v4"></path>
                                    <path d="M4 6v12c0 1.1.9 2 2 2h14v-4"></path>
                                    <path d="M18 12a2 2 0 0 0-2 2c0 1.1.9 2 2 2h4v-4h-4z"></path>
                                </svg>
                            </div>
                            <span>Cash on Hand</span>
                        </div>
                    </div>
                </div>

                <!-- Savings Category -->
                <div class="account-category" style="margin-top: 1.5rem;">
                    <h4 style="font-size: 0.875rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.75rem;">Savings</h4>
                    <div class="account-options">
                        <div class="account-option" data-type="ewallet" data-icon="gcash.png" data-name="GCash Savings" data-color="#007DFE" data-savings="1">
                            <div class="account-option-logo">
                                <img src="<?= APP_URL ?>/assets/images/logos/gcash.png" alt="GCash">
                            </div>
                            <span>GCash</span>
                        </div>
                        <div class="account-option" data-type="ewallet" data-icon="maya.png" data-name="Maya Savings" data-color="#10b981" data-savings="1">
                            <div class="account-option-logo">
                                <img src="<?= APP_URL ?>/assets/images/logos/maya.png" alt="Maya">
                            </div>
                            <span>Maya</span>
                        </div>
                        <div class="account-option" data-type="bank" data-icon="gotyme.png" data-name="GoTyme Savings" data-color="#06b6d4" data-savings="1">
                            <div class="account-option-logo">
                                <img src="<?= APP_URL ?>/assets/images/logos/gotyme.png" alt="GoTyme">
                            </div>
                            <span>GoTyme</span>
                        </div>
                        <div class="account-option" data-type="bank" data-icon="seabank.png" data-name="SeaBank Savings" data-color="#FF6B00" data-savings="1">
                            <div class="account-option-logo">
                                <img src="<?= APP_URL ?>/assets/images/logos/seabank.png" alt="SeaBank">
                            </div>
                            <span>SeaBank</span>
                        </div>
                    </div>
                </div>

                <!-- Include in Net Worth -->
                <div class="form-group" style="margin-top: 1.5rem; display: none;" id="includeNetworthSection">
                    <label style="display: flex; align-items: center; cursor: pointer;">
                        <input type="checkbox" id="includeNetworth" name="include_in_networth" value="1" checked style="margin-right: 0.5rem;">
                        <span>Include in Total Net Worth</span>
                    </label>
                    <small style="color: var(--text-muted); display: block; margin-top: 0.25rem; margin-left: 1.5rem;">Uncheck if you don't want this account counted in your total net worth</small>
                </div>

                <!-- Initial Balance -->
                <div class="form-group" style="display: none;" id="balanceSection">
                    <label for="accountBalance">Initial Balance</label>
                    <div style="position: relative;">
                        <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-weight: 600;">₱</span>
                        <input type="number" id="accountBalance" name="balance" class="form-control" style="padding-left: 2rem;" step="0.01" value="0.00" required>
                    </div>
                </div>

                <!-- Interest Rate for Savings -->
                <div class="form-group" id="interestRateGroup" style="display: none;">
                    <label for="interestRate">Interest Rate (% per year)</label>
                    <input type="number" id="interestRate" name="interest_rate" class="form-control" step="0.01" min="0" max="100" value="0.00">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addAccountModal')">Cancel</button>
                <button type="submit" class="btn btn-primary" id="submitAccountBtn" disabled>Add Account</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Account Modal -->
<div class="modal" id="editAccountModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit Account</h3>
            <button class="modal-close" onclick="closeModal('editAccountModal')">&times;</button>
        </div>
        <form id="editAccountForm" onsubmit="handleEditAccount(event)">
            <input type="hidden" id="editAccountId" name="id">
            <div class="modal-body">
                <div class="form-group">
                    <label for="editAccountName">Account Name *</label>
                    <input type="text" id="editAccountName" name="name" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="editAccountType">Account Type *</label>
                    <select id="editAccountType" name="type" class="form-control" required>
                        <option value="ewallet">E-Wallet</option>
                        <option value="bank">Bank</option>
                        <option value="cash">Cash</option>
                        <option value="credit_card">Credit Card</option>
                        <option value="investment">Investment</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="editAccountLogo">Logo Filename</label>
                    <select id="editAccountLogo" name="icon" class="form-control">
                        <option value="">None (use generic icon)</option>
                        <optgroup label="E-Wallets">
                            <option value="gcash.png">GCash</option>
                            <option value="maya.png">Maya</option>
                            <option value="gotyme.png">GoTyme</option>
                            <option value="seabank.png">SeaBank</option>
                            <option value="paymongo.png">PayMongo</option>
                            <option value="coins-ph.png">Coins.ph</option>
                            <option value="grabpay.png">GrabPay</option>
                            <option value="shopeepay.png">ShopeePay</option>
                        </optgroup>
                        <optgroup label="Banks">
                            <option value="bdo.png">BDO</option>
                            <option value="bpi.png">BPI</option>
                            <option value="metrobank.png">Metrobank</option>
                            <option value="unionbank.png">UnionBank</option>
                            <option value="landbank.png">Landbank</option>
                            <option value="security-bank.png">Security Bank</option>
                            <option value="rcbc.png">RCBC</option>
                        </optgroup>
                        <optgroup label="Generic">
                            <option value="cash.png">Cash</option>
                            <option value="credit-card.png">Credit Card</option>
                            <option value="bank.png">Bank</option>
                            <option value="wallet.png">Wallet</option>
                        </optgroup>
                    </select>
                    <small style="color: var(--text-muted); display: block; margin-top: 0.25rem;">Select logo from assets/images/logos/</small>
                </div>

                <div class="form-group">
                    <label for="editAccountBalance">Current Balance</label>
                    <input type="number" id="editAccountBalance" name="balance" class="form-control" step="0.01" required>
                </div>

                <div class="form-group">
                    <label for="editAccountColor">Color</label>
                    <select id="editAccountColor" name="color" class="form-control">
                        <option value="#6366f1">Indigo</option>
                        <option value="#007DFE">Blue</option>
                        <option value="#00D632">Green</option>
                        <option value="#FF6B00">Orange</option>
                        <option value="#00CFC8">Cyan</option>
                        <option value="#ef4444">Red</option>
                        <option value="#f59e0b">Amber</option>
                        <option value="#a855f7">Purple</option>
                        <option value="#10b981">Emerald</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" id="editIsSavings" name="is_savings" value="1">
                        This is a savings account with interest
                    </label>
                </div>

                <div class="form-group" id="editInterestRateGroup" style="display: none;">
                    <label for="editInterestRate">Interest Rate (% per year)</label>
                    <input type="number" id="editInterestRate" name="interest_rate" class="form-control" step="0.01" min="0" max="100">
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" id="editIncludeNetworth" name="include_in_networth" value="1">
                        Include in Total Net Worth
                    </label>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" id="editIsActive" name="is_active" value="1">
                        Account is active
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" onclick="handleDeleteAccount()">Delete</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('editAccountModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
// Existing accounts data for disabling already added accounts
const existingAccounts = <?= json_encode(array_map(function($acc) {
    return ['name' => $acc['name'], 'icon' => $acc['icon'], 'is_savings' => $acc['is_savings']];
}, $accounts)) ?>;

// Handle account option selection
document.querySelectorAll('.account-option').forEach(option => {
    // Check if this account already exists (same icon AND same savings status)
    const accountName = option.dataset.name;
    const accountIcon = option.dataset.icon;
    const isSavings = option.dataset.savings === '1';
    
    const exists = existingAccounts.some(acc => 
        accountIcon && acc.icon === accountIcon && acc.is_savings == isSavings
    );
    
    if (exists) {
        option.classList.add('disabled');
    }
    
    option.addEventListener('click', function() {
        if (this.classList.contains('disabled')) return;
        
        // Remove selection from all options
        document.querySelectorAll('.account-option').forEach(opt => opt.classList.remove('selected'));
        
        // Select this option
        this.classList.add('selected');
        
        // Get the color for this account
        const accountColor = this.dataset.color;
        
        // Apply dynamic border color
        this.style.borderColor = accountColor;
        this.style.boxShadow = `0 4px 12px ${accountColor}40`;
        
        // Fill hidden fields
        document.getElementById('selectedAccountType').value = this.dataset.type;
        document.getElementById('selectedAccountIcon').value = this.dataset.icon;
        document.getElementById('selectedAccountName').value = this.dataset.name;
        document.getElementById('selectedAccountColor').value = this.dataset.color;
        document.getElementById('selectedIsSavings').value = this.dataset.savings;
        
        // Show include networth and balance sections
        document.getElementById('includeNetworthSection').style.display = 'block';
        document.getElementById('balanceSection').style.display = 'block';
        
        // Show interest rate if savings
        if (this.dataset.savings === '1') {
            document.getElementById('interestRateGroup').style.display = 'block';
        } else {
            document.getElementById('interestRateGroup').style.display = 'none';
        }
        
        // Enable submit button
        document.getElementById('submitAccountBtn').disabled = false;
    });
});

// Auto-detect account type and savings based on logo selection
document.getElementById('accountLogo')?.addEventListener('change', function() {
    const logoValue = this.value.toLowerCase();
    const typeSelect = document.getElementById('accountType');
    const isSavingsCheckbox = document.getElementById('isSavings');
    
    // E-wallet logos
    const ewallets = ['gcash', 'maya', 'gotyme', 'seabank', 'paymongo', 'coins-ph', 'grabpay', 'shopeepay'];
    // Bank logos
    const banks = ['bdo', 'bpi', 'metrobank', 'unionbank', 'landbank', 'security-bank', 'rcbc'];
    
    // Auto-set type based on logo
    if (ewallets.some(ew => logoValue.includes(ew))) {
        typeSelect.value = 'ewallet';
    } else if (banks.some(bank => logoValue.includes(bank))) {
        typeSelect.value = 'bank';
    } else if (logoValue.includes('cash')) {
        typeSelect.value = 'cash';
    } else if (logoValue.includes('credit-card')) {
        typeSelect.value = 'credit_card';
    }
    
    // Auto-check savings if logo name contains "savings"
    if (logoValue.includes('savings') || logoValue.includes('save')) {
        isSavingsCheckbox.checked = true;
        document.getElementById('interestRateGroup').style.display = 'block';
    }
});

// Toggle interest rate field based on savings checkbox
document.getElementById('editIsSavings')?.addEventListener('change', function() {
    document.getElementById('editInterestRateGroup').style.display = this.checked ? 'block' : 'none';
});

// Handle add account form
async function handleAddAccount(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    
    try {
        const response = await fetch('<?= APP_URL ?>/api/accounts.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Account added successfully!');
            window.location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('Error adding account: ' + error.message);
    }
}

// Open edit modal with account data
async function editAccount(id) {
    try {
        const response = await fetch(`<?= APP_URL ?>/api/accounts.php?id=${id}`);
        const result = await response.json();
        
        if (result.success) {
            const account = result.data;
            document.getElementById('editAccountId').value = account.id;
            document.getElementById('editAccountName').value = account.name;
            document.getElementById('editAccountType').value = account.type;
            document.getElementById('editAccountLogo').value = account.icon || '';
            document.getElementById('editAccountBalance').value = account.balance;
            document.getElementById('editAccountColor').value = account.color;
            document.getElementById('editIsSavings').checked = account.is_savings;
            document.getElementById('editInterestRate').value = account.interest_rate;
            document.getElementById('editIncludeNetworth').checked = account.include_in_networth !== false;
            document.getElementById('editIsActive').checked = account.is_active;
            
            document.getElementById('editInterestRateGroup').style.display = account.is_savings ? 'block' : 'none';
            
            openModal('editAccountModal');
        }
    } catch (error) {
        alert('Error loading account: ' + error.message);
    }
}

// Handle edit account form
async function handleEditAccount(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    formData.append('_method', 'PUT');
    
    try {
        const response = await fetch('<?= APP_URL ?>/api/accounts.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Account updated successfully!');
            window.location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('Error updating account: ' + error.message);
    }
}

// Handle delete account
async function handleDeleteAccount() {
    if (!confirm('Are you sure you want to delete this account? This cannot be undone.')) {
        return;
    }
    
    const id = document.getElementById('editAccountId').value;
    const formData = new FormData();
    formData.append('id', id);
    formData.append('_method', 'DELETE');
    
    try {
        const response = await fetch('<?= APP_URL ?>/api/accounts.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Account deleted successfully!');
            window.location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('Error deleting account: ' + error.message);
    }
}
</script>

<?php require_once dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
