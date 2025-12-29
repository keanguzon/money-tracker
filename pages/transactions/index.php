<?php
/**
 * Transactions Page
 * Money Tracker Application
 */

$pageTitle = 'Transactions';
$pageStyles = ['dashboard', 'transactions'];
$currentPage = 'transactions';

require_once dirname(dirname(__DIR__)) . '/includes/auth.php';
requireAuth();

$user = getCurrentUser();
$db = getDB();

// Get filter parameters
$type = $_GET['type'] ?? 'all';
$category = $_GET['category'] ?? '';
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-t');
$search = $_GET['search'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 15;
$offset = ($page - 1) * $perPage;

// Build query
$whereClause = "WHERE t.user_id = ?";
$params = [$user['id']];

if ($type !== 'all') {
    $whereClause .= " AND t.type = ?";
    $params[] = $type;
}

if ($category) {
    $whereClause .= " AND t.category_id = ?";
    $params[] = $category;
}

$whereClause .= " AND t.transaction_date BETWEEN ? AND ?";
$params[] = $startDate;
$params[] = $endDate;

if ($search) {
    $whereClause .= " AND (t.description LIKE ? OR c.name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Get total count
$countSql = "SELECT COUNT(*) as total FROM transactions t 
             LEFT JOIN categories c ON t.category_id = c.id 
             $whereClause";
$stmt = $db->prepare($countSql);
$stmt->execute($params);
$result = $stmt->fetch();
$totalRecords = $result ? $result['total'] : 0;
$totalPages = ceil($totalRecords / $perPage);

// Get transactions
$sql = "SELECT t.*, c.name as category_name, c.icon as category_icon, c.color as category_color
        FROM transactions t
        LEFT JOIN categories c ON t.category_id = c.id
        $whereClause
        ORDER BY t.transaction_date DESC, t.created_at DESC
        LIMIT $perPage OFFSET $offset";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$transactions = $stmt->fetchAll() ?: [];

// Get categories for filter
$stmt = $db->prepare("
    SELECT * FROM categories 
    WHERE user_id = ? OR is_default = TRUE 
    ORDER BY type, name
");
$stmt->execute([$user['id']]);
$categories = $stmt->fetchAll() ?: [];

// Calculate totals for filtered results
$totalParams = $params;
$totalSql = "SELECT 
    COALESCE(SUM(CASE WHEN t.type = 'income' THEN t.amount ELSE 0 END), 0) as total_income,
    COALESCE(SUM(CASE WHEN t.type = 'expense' THEN t.amount ELSE 0 END), 0) as total_expense
    FROM transactions t
    LEFT JOIN categories c ON t.category_id = c.id
    $whereClause";
$stmt = $db->prepare($totalSql);
$stmt->execute($totalParams);
$result = $stmt->fetch();
$totals = $result ?: ['total_income' => 0, 'total_expense' => 0];

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
                <h1 class="page-title">Transactions</h1>
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
        <div class="dashboard-content transactions-page">
            <!-- Page Header -->
            <div class="page-header">
                <div>
                    <p style="color: var(--text-muted); font-size: 0.875rem;">
                        Showing <?= number_format($totalRecords) ?> transactions
                        • Income: <span style="color: var(--success);"><?= formatCurrency($totals['total_income'], $user['currency']) ?></span>
                        • Expenses: <span style="color: var(--danger);"><?= formatCurrency($totals['total_expense'], $user['currency']) ?></span>
                    </p>
                </div>
                <div class="header-actions">
                    <button class="btn btn-primary" onclick="openModal('addTransactionModal')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        Add Transaction
                    </button>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters-card">
                <form class="filters-row" method="GET" action="">
                    <div class="filter-tabs">
                        <button type="submit" name="type" value="all" class="filter-tab <?= $type === 'all' ? 'active' : '' ?>">All</button>
                        <button type="submit" name="type" value="income" class="filter-tab <?= $type === 'income' ? 'active' : '' ?>">Income</button>
                        <button type="submit" name="type" value="expense" class="filter-tab <?= $type === 'expense' ? 'active' : '' ?>">Expense</button>
                    </div>
                    
                    <div class="filter-group">
                        <label>Category</label>
                        <select name="category" class="filter-select" onchange="this.form.submit()">
                            <option value="">All Categories</option>
                            <optgroup label="Income">
                                <?php foreach ($categories as $cat): ?>
                                    <?php if ($cat['type'] === 'income'): ?>
                                        <option value="<?= $cat['id'] ?>" <?= $category == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </optgroup>
                            <optgroup label="Expense">
                                <?php foreach ($categories as $cat): ?>
                                    <?php if ($cat['type'] === 'expense'): ?>
                                        <option value="<?= $cat['id'] ?>" <?= $category == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </optgroup>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label>From</label>
                        <input type="date" name="start_date" class="filter-input" value="<?= $startDate ?>" onchange="this.form.submit()">
                    </div>
                    
                    <div class="filter-group">
                        <label>To</label>
                        <input type="date" name="end_date" class="filter-input" value="<?= $endDate ?>" onchange="this.form.submit()">
                    </div>
                </form>
            </div>

            <!-- Transactions Table -->
            <div class="transactions-table-card">
                <?php if (empty($transactions)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                                <polyline points="17 6 23 6 23 12"></polyline>
                            </svg>
                        </div>
                        <h3>No transactions found</h3>
                        <p>Start tracking your money by adding your first transaction</p>
                        <button class="btn btn-primary" onclick="openModal('addTransactionModal')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            Add Transaction
                        </button>
                    </div>
                <?php else: ?>
                    <table class="transactions-table">
                        <thead>
                            <tr>
                                <th>Transaction</th>
                                <th>Category</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transactions as $transaction): ?>
                                <tr>
                                    <td>
                                        <div class="table-transaction">
                                            <div class="table-transaction-icon" style="background: <?= $transaction['category_color'] ?>20; color: <?= $transaction['category_color'] ?>;">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <?php if ($transaction['type'] === 'income'): ?>
                                                        <line x1="12" y1="19" x2="12" y2="5"></line>
                                                        <polyline points="5 12 12 5 19 12"></polyline>
                                                    <?php else: ?>
                                                        <line x1="12" y1="5" x2="12" y2="19"></line>
                                                        <polyline points="19 12 12 19 5 12"></polyline>
                                                    <?php endif; ?>
                                                </svg>
                                            </div>
                                            <div class="table-transaction-details">
                                                <h4><?= htmlspecialchars($transaction['description'] ?: $transaction['category_name']) ?></h4>
                                                <p><?= ucfirst($transaction['type']) ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="table-category">
                                            <span class="table-category-dot" style="background: <?= $transaction['category_color'] ?>;"></span>
                                            <?= htmlspecialchars($transaction['category_name']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="table-date"><?= date('M d, Y', strtotime($transaction['transaction_date'])) ?></span>
                                    </td>
                                    <td>
                                        <span class="table-amount <?= $transaction['type'] ?>">
                                            <?= $transaction['type'] === 'income' ? '+' : '-' ?><?= formatCurrency($transaction['amount'], $user['currency']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="table-actions">
                                            <button class="table-action-btn" onclick="editTransaction(<?= $transaction['id'] ?>)" title="Edit">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                                </svg>
                                            </button>
                                            <button class="table-action-btn delete" onclick="deleteTransaction(<?= $transaction['id'] ?>)" title="Delete">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <polyline points="3 6 5 6 21 6"></polyline>
                                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <?php if ($totalPages > 1): ?>
                        <div class="pagination">
                            <div class="pagination-info">
                                Showing <?= $offset + 1 ?> - <?= min($offset + $perPage, $totalRecords) ?> of <?= $totalRecords ?>
                            </div>
                            <div class="pagination-controls">
                                <?php if ($page > 1): ?>
                                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="pagination-btn">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="15 18 9 12 15 6"></polyline>
                                        </svg>
                                    </a>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" class="pagination-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                                <?php endfor; ?>
                                
                                <?php if ($page < $totalPages): ?>
                                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="pagination-btn">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="9 18 15 12 9 6"></polyline>
                                        </svg>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<!-- Add Transaction Modal -->
<div class="modal-overlay" id="addTransactionModal">
    <div class="modal">
        <div class="modal-header">
            <h2>Add Transaction</h2>
            <button class="modal-close" onclick="closeModal('addTransactionModal')">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <form id="addTransactionForm" method="POST" action="<?= APP_URL ?>/api/add_transaction.php">
            <div class="modal-body">
                <!-- Transaction Type -->
                <div class="type-selector">
                    <label class="type-option income active" onclick="selectType('income')">
                        <input type="radio" name="type" value="income" checked style="display: none;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="19" x2="12" y2="5"></line>
                            <polyline points="5 12 12 5 19 12"></polyline>
                        </svg>
                        <span>Income</span>
                    </label>
                    <label class="type-option expense" onclick="selectType('expense')">
                        <input type="radio" name="type" value="expense" style="display: none;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <polyline points="19 12 12 19 5 12"></polyline>
                        </svg>
                        <span>Expense</span>
                    </label>
                </div>

                <!-- Amount -->
                <div class="form-group">
                    <label class="form-label">Amount</label>
                    <div class="amount-input-wrapper">
                        <span class="currency-symbol"><?= $user['currency'] === 'PHP' ? '₱' : '$' ?></span>
                        <input type="number" name="amount" class="form-input" placeholder="0.00" step="0.01" min="0" required>
                    </div>
                </div>

                <!-- Category -->
                <div class="form-group">
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-input" id="categorySelect" required>
                        <option value="">Select category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" data-type="<?= $cat['type'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Description -->
                <div class="form-group">
                    <label class="form-label">Description (Optional)</label>
                    <input type="text" name="description" class="form-input" placeholder="Enter description">
                </div>

                <!-- Date -->
                <div class="form-group">
                    <label class="form-label">Date</label>
                    <input type="date" name="transaction_date" class="form-input" value="<?= date('Y-m-d') ?>" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addTransactionModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Transaction</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) {
    document.getElementById(id).classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal(id) {
    document.getElementById(id).classList.remove('active');
    document.body.style.overflow = '';
}

function selectType(type) {
    document.querySelectorAll('.type-option').forEach(opt => opt.classList.remove('active'));
    document.querySelector(`.type-option.${type}`).classList.add('active');
    document.querySelector(`input[name="type"][value="${type}"]`).checked = true;
    
    // Filter categories
    const categorySelect = document.getElementById('categorySelect');
    Array.from(categorySelect.options).forEach(option => {
        if (option.value === '') return;
        option.style.display = option.dataset.type === type ? '' : 'none';
    });
    categorySelect.value = '';
}

function deleteTransaction(id) {
    Utils.confirm('Are you sure you want to delete this transaction?', () => {
        fetch('<?= APP_URL ?>/api/delete_transaction.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Utils.showToast('Transaction deleted successfully', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                Utils.showToast(data.message || 'Failed to delete transaction', 'error');
            }
        });
    });
}

function editTransaction(id) {
    // For simplicity, redirect to edit page or open edit modal
    Utils.showToast('Edit functionality coming soon', 'info');
}

// Handle form submission
document.getElementById('addTransactionForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch(this.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Utils.showToast('Transaction added successfully', 'success');
            closeModal('addTransactionModal');
            setTimeout(() => location.reload(), 1000);
        } else {
            Utils.showToast(data.message || 'Failed to add transaction', 'error');
        }
    })
    .catch(error => {
        Utils.showToast('An error occurred', 'error');
    });
});

// Initialize category filter
selectType('income');

// Check URL params for auto-opening modal
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.get('action') === 'add') {
    openModal('addTransactionModal');
    const type = urlParams.get('type');
    if (type === 'income' || type === 'expense') {
        selectType(type);
    }
}
</script>

<?php require_once dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
