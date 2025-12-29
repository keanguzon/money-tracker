<?php
/**
 * Dashboard Page
 * Money Tracker Application
 */

$pageTitle = 'Dashboard';
$pageStyles = ['dashboard'];
$pageScripts = ['charts'];
$currentPage = 'dashboard';

require_once dirname(dirname(__DIR__)) . '/includes/auth.php';
requireAuth();

$user = getCurrentUser();
$db = getDB();

// Get date range for current month
$startOfMonth = date('Y-m-01');
$endOfMonth = date('Y-m-t');
$currentMonth = date('F Y');

// Calculate NET WORTH (total from all accounts)
$stmt = $db->prepare("
    SELECT COALESCE(SUM(balance), 0) as net_worth
    FROM accounts 
    WHERE user_id = ? AND is_active = TRUE
");
$stmt->execute([$user['id']]);
$result = $stmt->fetch();
$netWorth = $result ? $result['net_worth'] : 0;

// Get all accounts
$stmt = $db->prepare("
    SELECT * FROM accounts 
    WHERE user_id = ? AND is_active = TRUE
    ORDER BY type, name
");
$stmt->execute([$user['id']]);
$accounts = $stmt->fetchAll() ?: [];

// Get total income this month
$stmt = $db->prepare("
    SELECT COALESCE(SUM(amount), 0) as total 
    FROM transactions 
    WHERE user_id = ? AND type = 'income' 
    AND transaction_date BETWEEN ? AND ?
");
$stmt->execute([$user['id'], $startOfMonth, $endOfMonth]);
$result = $stmt->fetch();
$totalIncome = $result ? $result['total'] : 0;

// Get total expenses this month
$stmt = $db->prepare("
    SELECT COALESCE(SUM(amount), 0) as total 
    FROM transactions 
    WHERE user_id = ? AND type = 'expense' 
    AND transaction_date BETWEEN ? AND ?
");
$stmt->execute([$user['id'], $startOfMonth, $endOfMonth]);
$result = $stmt->fetch();
$totalExpenses = $result ? $result['total'] : 0;

// Calculate balance
$balance = $totalIncome - $totalExpenses;

// Get last month's data for comparison
$lastMonthStart = date('Y-m-01', strtotime('-1 month'));
$lastMonthEnd = date('Y-m-t', strtotime('-1 month'));

$stmt = $db->prepare("
    SELECT COALESCE(SUM(amount), 0) as total 
    FROM transactions 
    WHERE user_id = ? AND type = 'income' 
    AND transaction_date BETWEEN ? AND ?
");
$stmt->execute([$user['id'], $lastMonthStart, $lastMonthEnd]);
$result = $stmt->fetch();
$lastMonthIncome = $result ? $result['total'] : 0;

$stmt = $db->prepare("
    SELECT COALESCE(SUM(amount), 0) as total 
    FROM transactions 
    WHERE user_id = ? AND type = 'expense' 
    AND transaction_date BETWEEN ? AND ?
");
$stmt->execute([$user['id'], $lastMonthStart, $lastMonthEnd]);
$result = $stmt->fetch();
$lastMonthExpenses = $result ? $result['total'] : 0;

// Calculate trends
$incomeTrend = $lastMonthIncome > 0 ? (($totalIncome - $lastMonthIncome) / $lastMonthIncome) * 100 : 0;
$expenseTrend = $lastMonthExpenses > 0 ? (($totalExpenses - $lastMonthExpenses) / $lastMonthExpenses) * 100 : 0;

// Get recent transactions with account info
$stmt = $db->prepare("
    SELECT t.*, c.name as category_name, c.icon as category_icon, c.color as category_color,
           a.name as account_name, a.icon as account_icon, a.color as account_color
    FROM transactions t
    LEFT JOIN categories c ON t.category_id = c.id
    LEFT JOIN accounts a ON t.account_id = a.id
    WHERE t.user_id = ?
    ORDER BY t.transaction_date DESC, t.created_at DESC
    LIMIT 10
");
$stmt->execute([$user['id']]);
$recentTransactions = $stmt->fetchAll() ?: [];

// Get expense by category for chart
$stmt = $db->prepare("
    SELECT c.name, c.color, COALESCE(SUM(t.amount), 0) as total
    FROM categories c
    LEFT JOIN transactions t ON c.id = t.category_id 
        AND t.user_id = ? 
        AND t.type = 'expense'
        AND t.transaction_date BETWEEN ? AND ?
    WHERE c.type = 'expense' AND (c.user_id = ? OR c.is_default = TRUE)
    GROUP BY c.id, c.name, c.color
    HAVING COALESCE(SUM(t.amount), 0) > 0
    ORDER BY total DESC
    LIMIT 6
");
$stmt->execute([$user['id'], $startOfMonth, $endOfMonth, $user['id']]);
$expensesByCategory = $stmt->fetchAll() ?: [];

// Get monthly data for chart (last 6 months)
$monthlyData = [];
for ($i = 5; $i >= 0; $i--) {
    $monthStart = date('Y-m-01', strtotime("-$i months"));
    $monthEnd = date('Y-m-t', strtotime("-$i months"));
    $monthLabel = date('M', strtotime("-$i months"));
    
    $stmt = $db->prepare("
        SELECT COALESCE(SUM(amount), 0) as total 
        FROM transactions 
        WHERE user_id = ? AND type = 'income' 
        AND transaction_date BETWEEN ? AND ?
    ");
    $stmt->execute([$user['id'], $monthStart, $monthEnd]);
    $result = $stmt->fetch();
    $monthIncome = $result ? $result['total'] : 0;
    
    $stmt = $db->prepare("
        SELECT COALESCE(SUM(amount), 0) as total 
        FROM transactions 
        WHERE user_id = ? AND type = 'expense' 
        AND transaction_date BETWEEN ? AND ?
    ");
    $stmt->execute([$user['id'], $monthStart, $monthEnd]);
    $result = $stmt->fetch();
    $monthExpense = $result ? $result['total'] : 0;
    
    $monthlyData[] = [
        'label' => $monthLabel,
        'income' => (float)$monthIncome,
        'expense' => (float)$monthExpense
    ];
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
                <h1 class="page-title">Dashboard</h1>
            </div>
            <div class="header-right">
                <div class="search-box">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" placeholder="Search transactions...">
                </div>
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
                <button class="header-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                    </svg>
                    <span class="notification-dot"></span>
                </button>
                <a href="<?= APP_URL ?>/pages/logout/" class="header-btn" title="Logout">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                </a>
            </div>
        </header>

        <!-- Dashboard Content -->
        <div class="dashboard-content">
            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card primary">
                    <div class="stat-header">
                        <div class="stat-icon primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="1" x2="12" y2="23"></line>
                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                            </svg>
                        </div>
                        <div class="stat-label">Net Worth</div>
                    </div>
                    <div class="stat-value" style="font-size: 2rem; font-weight: 800;"><?= formatCurrency($netWorth, $user['currency']) ?></div>
                    <div class="stat-sublabel" style="margin-top: 0.5rem; color: var(--text-muted);">Total across all accounts</div>
                </div>

                <div class="stat-card income">
                    <div class="stat-header">
                        <div class="stat-icon income">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="19" x2="12" y2="5"></line>
                                <polyline points="5 12 12 5 19 12"></polyline>
                            </svg>
                        </div>
                        <div class="stat-trend <?= $incomeTrend >= 0 ? 'up' : 'down' ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <?php if ($incomeTrend >= 0): ?>
                                    <polyline points="18 15 12 9 6 15"></polyline>
                                <?php else: ?>
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                <?php endif; ?>
                            </svg>
                            <?= abs(round($incomeTrend, 1)) ?>%
                        </div>
                    </div>
                    <div class="stat-value"><?= formatCurrency($totalIncome, $user['currency']) ?></div>
                    <div class="stat-label">Income (<?= $currentMonth ?>)</div>
                </div>

                <div class="stat-card expense">
                    <div class="stat-header">
                        <div class="stat-icon expense">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <polyline points="19 12 12 19 5 12"></polyline>
                            </svg>
                        </div>
                        <div class="stat-trend <?= $expenseTrend <= 0 ? 'up' : 'down' ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <?php if ($expenseTrend <= 0): ?>
                                    <polyline points="18 15 12 9 6 15"></polyline>
                                <?php else: ?>
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                <?php endif; ?>
                            </svg>
                            <?= abs(round($expenseTrend, 1)) ?>%
                        </div>
                    </div>
                    <div class="stat-value"><?= formatCurrency($totalExpenses, $user['currency']) ?></div>
                    <div class="stat-label">Expenses (<?= $currentMonth ?>)</div>
                </div>

                <div class="stat-card balance">
                    <div class="stat-header">
                        <div class="stat-icon balance">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="1" x2="12" y2="23"></line>
                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="stat-value"><?= formatCurrency($balance, $user['currency']) ?></div>
                    <div class="stat-label">Balance (<?= $currentMonth ?>)</div>
                </div>
            </div>

            <!-- Accounts Overview -->
            <div class="card" style="margin-bottom: 2rem;">
                <div class="card-header">
                    <h3>Accounts Overview</h3>
                    <a href="<?= APP_URL ?>/pages/accounts/" class="btn btn-sm">Manage</a>
                </div>
                <div class="card-body">
                    <div class="accounts-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem;">
                        <?php foreach ($accounts as $account): ?>
                            <div class="account-item" style="padding: 1rem; background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 8px;">
                                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                                    <?php 
                                    $logoPath = APP_URL . '/assets/images/logos/' . ($account['icon'] ?: 'wallet.png');
                                    $logoExists = file_exists(dirname(dirname(__DIR__)) . '/assets/images/logos/' . ($account['icon'] ?: 'wallet.png'));
                                    ?>
                                    <div style="width: 32px; height: 32px; border-radius: 8px; background: white; display: flex; align-items: center; justify-content: center; padding: 4px; border: 1px solid #e5e7eb;">
                                        <?php if ($logoExists && $account['icon']): ?>
                                            <img src="<?= $logoPath ?>" alt="<?= htmlspecialchars($account['name']) ?>" style="width: 100%; height: 100%; object-fit: contain;">
                                        <?php else: ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="<?= $account['color'] ?>" stroke-width="2">
                                                <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                                                <line x1="1" y1="10" x2="23" y2="10"></line>
                                            </svg>
                                        <?php endif; ?>
                                    </div>
                                    <div style="flex: 1;">
                                        <div style="font-weight: 600; font-size: 0.875rem;"><?= htmlspecialchars($account['name']) ?></div>
                                        <?php if ($account['is_savings']): ?>
                                            <div style="font-size: 0.75rem; color: var(--success);"><?= number_format($account['interest_rate'], 2) ?>% interest</div>
                                        <?php else: ?>
                                            <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: capitalize;"><?= htmlspecialchars($account['type']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div style="font-size: 1.125rem; font-weight: 700; color: <?= $account['balance'] >= 0 ? 'var(--success)' : 'var(--danger)' ?>;">
                                    <?= formatCurrency($account['balance'], $user['currency']) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($accounts)): ?>
                            <div style="grid-column: 1 / -1; text-align: center; padding: 2rem; color: var(--text-muted);">
                                No accounts yet. <a href="<?= APP_URL ?>/pages/accounts/">Add your first account</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="dashboard-row">
                <!-- Charts Section -->
                                <path d="M14 8h.01"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="stat-value"><?= formatCurrency($balance, $user['currency']) ?></div>
                    <div class="stat-label">Current Balance</div>
                </div>

                <div class="stat-card savings">
                    <div class="stat-header">
                        <div class="stat-icon savings">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <circle cx="12" cy="12" r="6"></circle>
                                <circle cx="12" cy="12" r="2"></circle>
                            </svg>
                        </div>
                    </div>
                    <div class="stat-value"><?= $totalIncome > 0 ? round((($totalIncome - $totalExpenses) / $totalIncome) * 100) : 0 ?>%</div>
                    <div class="stat-label">Savings Rate</div>
                </div>
            </div>

            <!-- Dashboard Grid -->
            <div class="dashboard-grid">
                <!-- Chart Section -->
                <div class="chart-card">
                    <div class="chart-header">
                        <h2 class="chart-title">Income vs Expenses</h2>
                        <div class="chart-tabs">
                            <button class="chart-tab active" data-period="6months">6 Months</button>
                            <button class="chart-tab" data-period="year">This Year</button>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="incomeExpenseChart"></canvas>
                    </div>
                </div>

                <!-- Recent Transactions & Quick Actions -->
                <div>
                    <div class="transactions-card">
                        <div class="transactions-header">
                            <h2 class="transactions-title">Recent Transactions</h2>
                            <a href="<?= APP_URL ?>/pages/transactions/" class="view-all-btn">View All</a>
                        </div>
                        <div class="transactions-list">
                            <?php if (empty($recentTransactions)): ?>
                                <div class="empty-state" style="padding: 2rem;">
                                    <p style="color: var(--text-muted);">No transactions yet</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($recentTransactions as $transaction): ?>
                                    <div class="transaction-item">
                                        <div class="transaction-icon" style="background: <?= $transaction['category_color'] ?>20; color: <?= $transaction['category_color'] ?>;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <?php if ($transaction['type'] === 'income'): ?>
                                                    <line x1="12" y1="19" x2="12" y2="5"></line>
                                                    <polyline points="5 12 12 5 19 12"></polyline>
                                                <?php elseif ($transaction['type'] === 'transfer'): ?>
                                                    <polyline points="17 11 21 7 17 3"></polyline>
                                                    <line x1="21" y1="7" x2="9" y2="7"></line>
                                                    <polyline points="7 21 3 17 7 13"></polyline>
                                                    <line x1="15" y1="17" x2="3" y2="17"></line>
                                                <?php else: ?>
                                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                                    <polyline points="19 12 12 19 5 12"></polyline>
                                                <?php endif; ?>
                                            </svg>
                                        </div>
                                        <div class="transaction-details">
                                            <div class="transaction-name"><?= htmlspecialchars($transaction['description'] ?: $transaction['category_name']) ?></div>
                                            <div class="transaction-category">
                                                <?= htmlspecialchars($transaction['category_name']) ?>
                                                <?php if ($transaction['account_name']): ?>
                                                    • <?= htmlspecialchars($transaction['account_name']) ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="transaction-amount <?= $transaction['type'] ?>">
                                                <?= $transaction['type'] === 'income' ? '+' : ($transaction['type'] === 'transfer' ? '↔' : '-') ?><?= formatCurrency($transaction['amount'], $user['currency']) ?>
                                            </div>
                                            <div class="transaction-date"><?= date('M d', strtotime($transaction['transaction_date'])) ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="quick-actions">
                        <a href="<?= APP_URL ?>/pages/transactions/?action=add&type=income" class="quick-action-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            <span>Add Income</span>
                        </a>
                        <a href="<?= APP_URL ?>/pages/transactions/?action=add&type=expense" class="quick-action-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            <span>Add Expense</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Expense Breakdown -->
            <?php if (!empty($expensesByCategory)): ?>
            <div class="chart-card" style="margin-top: 1.5rem;">
                <div class="chart-header">
                    <h2 class="chart-title">Expense Breakdown - <?= $currentMonth ?></h2>
                </div>
                <div style="display: grid; grid-template-columns: 300px 1fr; gap: 2rem; padding: 1rem 0;">
                    <div style="height: 250px;">
                        <canvas id="expenseBreakdownChart"></canvas>
                    </div>
                    <div class="category-list">
                        <?php 
                        $totalCategoryExpense = array_sum(array_column($expensesByCategory, 'total'));
                        foreach ($expensesByCategory as $category): 
                            $percentage = $totalCategoryExpense > 0 ? ($category['total'] / $totalCategoryExpense) * 100 : 0;
                        ?>
                            <div class="category-item">
                                <div class="category-color" style="background: <?= $category['color'] ?>;"></div>
                                <div class="category-info">
                                    <div class="category-name"><?= htmlspecialchars($category['name']) ?></div>
                                    <div class="category-bar">
                                        <div class="category-bar-fill" style="width: <?= $percentage ?>%; background: <?= $category['color'] ?>;"></div>
                                    </div>
                                </div>
                                <div class="category-amount"><?= formatCurrency($category['total'], $user['currency']) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Monthly chart data
    const monthlyData = <?= json_encode($monthlyData) ?>;
    
    MoneyCharts.createIncomeExpenseChart('incomeExpenseChart', {
        labels: monthlyData.map(d => d.label),
        income: monthlyData.map(d => d.income),
        expenses: monthlyData.map(d => d.expense)
    });

    <?php if (!empty($expensesByCategory)): ?>
    // Expense breakdown chart
    const categoryData = <?= json_encode($expensesByCategory) ?>;
    
    MoneyCharts.createExpenseBreakdownChart('expenseBreakdownChart', {
        labels: categoryData.map(c => c.name),
        values: categoryData.map(c => parseFloat(c.total)),
        colors: categoryData.map(c => c.color)
    });
    <?php endif; ?>
});
</script>

<?php require_once dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
