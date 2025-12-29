<?php
/**
 * Categories Page
 * Money Tracker Application
 */

$pageTitle = 'Categories';
$pageStyles = ['dashboard', 'transactions'];
$currentPage = 'categories';

require_once dirname(dirname(__DIR__)) . '/includes/auth.php';
requireAuth();

$user = getCurrentUser();
$db = getDB();

// Get user's custom categories and default categories
$stmt = $db->prepare("
    SELECT c.*, 
           COUNT(t.id) as transaction_count,
           COALESCE(SUM(t.amount), 0) as total_amount
    FROM categories c
    LEFT JOIN transactions t ON c.id = t.category_id AND t.user_id = ?
    WHERE c.user_id = ? OR c.is_default = TRUE
    GROUP BY c.id
    ORDER BY c.is_default ASC, c.type, c.name
");
$stmt->execute([$user['id'], $user['id']]);
$categories = $stmt->fetchAll() ?: [];

$incomeCategories = array_filter($categories, fn($c) => $c['type'] === 'income');
$expenseCategories = array_filter($categories, fn($c) => $c['type'] === 'expense');

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
                <h1 class="page-title">Categories</h1>
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
            <h2 style="margin-bottom: 1.5rem; color: var(--text-primary);">Income Categories</h2>
            <div class="category-grid" style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); margin-bottom: 3rem;">
                <?php foreach ($incomeCategories as $category): ?>
                    <div class="category-card" style="background: var(--card-bg); border-radius: 0.75rem; padding: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 1rem;">
                        <div style="width: 48px; height: 48px; border-radius: 12px; background: <?= htmlspecialchars($category['color']) ?>20; color: <?= htmlspecialchars($category['color']) ?>; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0;">
                            💰
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <h3 style="margin: 0 0 0.25rem; font-size: 1rem; color: var(--text-primary); display: flex; align-items: center; gap: 0.5rem;">
                                <?= htmlspecialchars($category['name']) ?>
                                <?php if ($category['is_default']): ?>
                                    <span style="font-size: 0.75rem; background: var(--bg-secondary); color: var(--text-muted); padding: 0.125rem 0.5rem; border-radius: 999px;">Default</span>
                                <?php endif; ?>
                            </h3>
                            <p style="margin: 0; font-size: 0.875rem; color: var(--text-muted);">
                                <?= number_format($category['transaction_count']) ?> transactions • <?= formatCurrency($category['total_amount'], $user['currency']) ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <h2 style="margin-bottom: 1.5rem; color: var(--text-primary);">Expense Categories</h2>
            <div class="category-grid" style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));">
                <?php foreach ($expenseCategories as $category): ?>
                    <div class="category-card" style="background: var(--card-bg); border-radius: 0.75rem; padding: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 1rem;">
                        <div style="width: 48px; height: 48px; border-radius: 12px; background: <?= htmlspecialchars($category['color']) ?>20; color: <?= htmlspecialchars($category['color']) ?>; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0;">
                            💸
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <h3 style="margin: 0 0 0.25rem; font-size: 1rem; color: var(--text-primary); display: flex; align-items: center; gap: 0.5rem;">
                                <?= htmlspecialchars($category['name']) ?>
                                <?php if ($category['is_default']): ?>
                                    <span style="font-size: 0.75rem; background: var(--bg-secondary); color: var(--text-muted); padding: 0.125rem 0.5rem; border-radius: 999px;">Default</span>
                                <?php endif; ?>
                            </h3>
                            <p style="margin: 0; font-size: 0.875rem; color: var(--text-muted);">
                                <?= number_format($category['transaction_count']) ?> transactions • <?= formatCurrency($category['total_amount'], $user['currency']) ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
</div>

<script src="<?= APP_URL ?>/assets/js/darkmode.js"></script>
<script src="<?= APP_URL ?>/assets/js/main.js"></script>

<?php require_once dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
