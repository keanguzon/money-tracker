<?php
/**
 * Budgets Page
 * Money Tracker Application
 */

$pageTitle = 'Budgets';
$pageStyles = ['dashboard', 'transactions'];
$currentPage = 'budgets';

require_once dirname(dirname(__DIR__)) . '/includes/auth.php';
requireAuth();

$user = getCurrentUser();
$db = getDB();

// Get current month budgets
$stmt = $db->prepare("
    SELECT b.*, c.name as category_name, c.color as category_color,
           COALESCE(SUM(t.amount), 0) as spent
    FROM budgets b
    LEFT JOIN categories c ON b.category_id = c.id
    LEFT JOIN transactions t ON t.category_id = b.category_id 
        AND t.user_id = b.user_id 
        AND t.type = 'expense'
        AND t.transaction_date BETWEEN b.start_date AND b.end_date
    WHERE b.user_id = ? AND b.period = 'monthly'
    GROUP BY b.id, c.name, c.color
    ORDER BY b.created_at DESC
");
$stmt->execute([$user['id']]);
$budgets = $stmt->fetchAll() ?: [];

// Get categories for modal
$stmt = $db->prepare("
    SELECT * FROM categories 
    WHERE (user_id = ? OR is_default = TRUE) AND type = 'expense'
    ORDER BY name ASC
");
$stmt->execute([$user['id']]);
$categories = $stmt->fetchAll() ?: [];

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
                <h1 class="page-title">Budgets</h1>
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
                <button class="btn btn-primary" onclick="document.getElementById('addBudgetModal').style.display='flex'">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    New Budget
                </button>
            </div>
        </header>

        <div class="dashboard-content">
            <?php if (empty($budgets)): ?>
                <div class="empty-state" style="text-align: center; padding: 4rem 1rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--text-muted); margin-bottom: 1rem;">
                        <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                        <path d="M6 8h.01"></path>
                        <path d="M10 8h.01"></path>
                        <path d="M14 8h.01"></path>
                    </svg>
                    <h3 style="margin-bottom: 0.5rem; color: var(--text-primary);">No budgets yet</h3>
                    <p style="color: var(--text-muted); margin-bottom: 1.5rem;">Create your first budget to track spending limits</p>
                    <button class="btn btn-primary" onclick="document.getElementById('addBudgetModal').style.display='flex'">Create Budget</button>
                </div>
            <?php else: ?>
                <div class="budget-grid" style="display: grid; gap: 1.5rem; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));">
                    <?php foreach ($budgets as $budget): 
                        $percentage = $budget['amount'] > 0 ? min(100, ($budget['spent'] / $budget['amount']) * 100) : 0;
                        $status = $percentage >= 100 ? 'danger' : ($percentage >= 80 ? 'warning' : 'success');
                    ?>
                        <div class="budget-card" style="background: var(--card-bg); border-radius: 1rem; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                                <div style="width: 48px; height: 48px; border-radius: 12px; background: <?= htmlspecialchars($budget['category_color'] ?? '#6366f1') ?>20; color: <?= htmlspecialchars($budget['category_color'] ?? '#6366f1') ?>; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                                    💰
                                </div>
                                <div style="flex: 1;">
                                    <h3 style="margin: 0; font-size: 1.125rem; color: var(--text-primary);"><?= htmlspecialchars($budget['category_name'] ?? 'Overall') ?></h3>
                                    <p style="margin: 0; font-size: 0.875rem; color: var(--text-muted); text-transform: capitalize;"><?= htmlspecialchars($budget['period']) ?></p>
                                </div>
                            </div>
                            
                            <div style="margin-bottom: 1rem;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <span style="font-size: 0.875rem; color: var(--text-muted);">Spent</span>
                                    <span style="font-size: 0.875rem; font-weight: 600; color: var(--text-primary);"><?= formatCurrency($budget['spent'], $user['currency']) ?> / <?= formatCurrency($budget['amount'], $user['currency']) ?></span>
                                </div>
                                <div style="background: var(--bg-secondary); border-radius: 999px; height: 8px; overflow: hidden;">
                                    <div style="background: <?= $status === 'danger' ? '#ef4444' : ($status === 'warning' ? '#f59e0b' : '#10b981') ?>; height: 100%; width: <?= $percentage ?>%; transition: width 0.3s;"></div>
                                </div>
                                <div style="margin-top: 0.5rem; font-size: 0.875rem; color: <?= $status === 'danger' ? '#ef4444' : ($status === 'warning' ? '#f59e0b' : 'var(--text-muted)') ?>;">
                                    <?= number_format($percentage, 1) ?>% used
                                    <?php if ($percentage >= 100): ?>
                                        • Budget exceeded!
                                    <?php elseif ($percentage >= 80): ?>
                                        • Approaching limit
                                    <?php else: ?>
                                        • <?= formatCurrency($budget['amount'] - $budget['spent'], $user['currency']) ?> remaining
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<!-- Add Budget Modal -->
<div id="addBudgetModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Create Budget</h2>
            <button class="modal-close" onclick="document.getElementById('addBudgetModal').style.display='none'">&times;</button>
        </div>
        <form method="POST" action="">
            <div class="modal-body">
                <p style="color: var(--text-muted); margin-bottom: 1.5rem;">Budgets are coming soon. This feature will allow you to set spending limits for categories.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('addBudgetModal').style.display='none'">Close</button>
            </div>
        </form>
    </div>
</div>

<script src="<?= APP_URL ?>/assets/js/darkmode.js"></script>
<script src="<?= APP_URL ?>/assets/js/main.js"></script>

<?php require_once dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
