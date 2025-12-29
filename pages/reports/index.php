<?php
/**
 * Reports Page
 * BukoJuice Application
 */

$pageTitle = 'Reports';
$pageStyles = ['dashboard'];
$currentPage = 'reports';

require_once dirname(dirname(__DIR__)) . '/includes/auth.php';
requireAuth();

$user = getCurrentUser();
$db = getDB();

// Get date range (default: last 30 days)
$endDate = date('Y-m-d');
$startDate = date('Y-m-d', strtotime('-30 days'));

// Get total income and expenses
$stmt = $db->prepare("
    SELECT 
        COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) as total_income,
        COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) as total_expense,
        COUNT(*) as transaction_count
    FROM transactions
    WHERE user_id = ? AND transaction_date BETWEEN ? AND ?
");
$stmt->execute([$user['id'], $startDate, $endDate]);
$result = $stmt->fetch();
$summary = $result ?: ['total_income' => 0, 'total_expense' => 0, 'transaction_count' => 0];

$balance = $summary['total_income'] - $summary['total_expense'];

// Get top categories
$stmt = $db->prepare("
    SELECT c.name, c.color, t.type, COALESCE(SUM(t.amount), 0) as total, COUNT(t.id) as count
    FROM transactions t
    LEFT JOIN categories c ON t.category_id = c.id
    WHERE t.user_id = ? AND t.transaction_date BETWEEN ? AND ?
    GROUP BY c.id, c.name, c.color, t.type
    ORDER BY total DESC
    LIMIT 10
");
$stmt->execute([$user['id'], $startDate, $endDate]);
$topCategories = $stmt->fetchAll() ?: [];

require_once dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="dashboard-layout">
    <?php require_once dirname(dirname(__DIR__)) . '/includes/sidebar.php'; ?>

    <main class="main-content">
        <header class="top-header">
            <div class="header-left">
                <button class="menu-toggle">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="3" y1="12" x2="21" y2="12"></line>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <line x1="3" y1="18" x2="21" y2="18"></line>
                    </svg>
                </button>
                <h1 class="page-title">Reports</h1>
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

        <div class="dashboard-content">
            <h2 style="margin-bottom: 1.5rem; color: var(--text-primary);">Financial Summary</h2>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #10b98120; color: #10b981;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                        </svg>
                    </div>
                    <div class="stat-info">
                        <p class="stat-label">Total Income</p>
                        <h3 class="stat-value"><?= formatCurrency($summary['total_income'], $user['currency']) ?></h3>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: #ef444420; color: #ef4444;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                            <polyline points="17 6 23 6 23 12"></polyline>
                        </svg>
                    </div>
                    <div class="stat-info">
                        <p class="stat-label">Total Expenses</p>
                        <h3 class="stat-value"><?= formatCurrency($summary['total_expense'], $user['currency']) ?></h3>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: <?= $balance >= 0 ? '#10b98120' : '#f59e0b20' ?>; color: <?= $balance >= 0 ? '#10b981' : '#f59e0b' ?>;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="1" x2="12" y2="23"></line>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                        </svg>
                    </div>
                    <div class="stat-info">
                        <p class="stat-label">Net Balance</p>
                        <h3 class="stat-value" style="color: <?= $balance >= 0 ? '#10b981' : '#ef4444' ?>"><?= formatCurrency($balance, $user['currency']) ?></h3>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: #8b5cf620; color: #8b5cf6;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="8" y1="6" x2="21" y2="6"></line>
                            <line x1="8" y1="12" x2="21" y2="12"></line>
                            <line x1="8" y1="18" x2="21" y2="18"></line>
                            <line x1="3" y1="6" x2="3.01" y2="6"></line>
                            <line x1="3" y1="12" x2="3.01" y2="12"></line>
                            <line x1="3" y1="18" x2="3.01" y2="18"></line>
                        </svg>
                    </div>
                    <div class="stat-info">
                        <p class="stat-label">Transactions</p>
                        <h3 class="stat-value"><?= number_format($summary['transaction_count']) ?></h3>
                    </div>
                </div>
            </div>

            <h2 style="margin: 2rem 0 1.5rem; color: var(--text-primary);">Top Categories (Last 30 Days)</h2>
            
            <?php if (empty($topCategories)): ?>
                <div class="empty-state" style="text-align: center; padding: 3rem 1rem;">
                    <p style="color: var(--text-muted);">No transactions in this period</p>
                </div>
            <?php else: ?>
                <div class="card" style="overflow: hidden;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: var(--bg-secondary); text-align: left;">
                                <th style="padding: 1rem; font-weight: 600; color: var(--text-primary);">Category</th>
                                <th style="padding: 1rem; font-weight: 600; color: var(--text-primary);">Type</th>
                                <th style="padding: 1rem; font-weight: 600; color: var(--text-primary);">Transactions</th>
                                <th style="padding: 1rem; font-weight: 600; color: var(--text-primary); text-align: right;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topCategories as $cat): ?>
                                <tr style="border-bottom: 1px solid var(--border-color);">
                                    <td style="padding: 1rem;">
                                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                                            <div style="width: 10px; height: 10px; border-radius: 50%; background: <?= htmlspecialchars($cat['color']) ?>;"></div>
                                            <span style="color: var(--text-primary); font-weight: 500;"><?= htmlspecialchars($cat['name']) ?></span>
                                        </div>
                                    </td>
                                    <td style="padding: 1rem;">
                                        <span class="badge" style="background: <?= $cat['type'] === 'income' ? '#10b98120' : '#ef444420' ?>; color: <?= $cat['type'] === 'income' ? '#10b981' : '#ef4444' ?>; padding: 0.25rem 0.75rem; border-radius: 999px; font-size: 0.875rem; text-transform: capitalize;">
                                            <?= htmlspecialchars($cat['type']) ?>
                                        </span>
                                    </td>
                                    <td style="padding: 1rem; color: var(--text-muted);"><?= number_format($cat['count']) ?></td>
                                    <td style="padding: 1rem; text-align: right; font-weight: 600; color: var(--text-primary);"><?= formatCurrency($cat['total'], $user['currency']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<script src="<?= APP_URL ?>/assets/js/darkmode.js"></script>
<script src="<?= APP_URL ?>/assets/js/main.js"></script>

<?php require_once dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
