<?php
/**
 * Add Transaction API
 * BukoJuice Application
 */

header('Content-Type: application/json');

require_once dirname(__DIR__) . '/includes/auth.php';

// Check if user is authenticated
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$user = getCurrentUser();

// Get input from either JSON or FormData
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (strpos($contentType, 'application/json') !== false) {
    $input = json_decode(file_get_contents('php://input'), true);
} else {
    $input = $_POST;
}

// Validate required fields
$required = ['type', 'category_id', 'amount', 'account_id'];
foreach ($required as $field) {
    if (!isset($input[$field]) || $input[$field] === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
        exit;
    }
}

// Validate type
if (!in_array($input['type'], ['income', 'expense'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid transaction type']);
    exit;
}

// Validate amount
$amount = floatval($input['amount']);
if ($amount <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Amount must be greater than 0']);
    exit;
}

// Get description (optional)
$description = $input['description'] ?? '';

// Get transaction date or use current date
$transactionDate = !empty($input['transaction_date']) ? $input['transaction_date'] : date('Y-m-d');

try {
    $db = Database::getInstance()->getConnection();
    
    // Start transaction
    $db->beginTransaction();
    
    // Verify category exists and belongs to user or is default
    $stmt = $db->prepare("SELECT id FROM categories WHERE id = ? AND (user_id = ? OR user_id IS NULL)");
    $stmt->execute([$input['category_id'], $user['id']]);
    
    if (!$stmt->fetch()) {
        $db->rollBack();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid category']);
        exit;
    }
    
    // Verify account exists and belongs to user
    $stmt = $db->prepare("SELECT id, balance FROM accounts WHERE id = ? AND user_id = ?");
    $stmt->execute([$input['account_id'], $user['id']]);
    $account = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$account) {
        $db->rollBack();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid account']);
        exit;
    }
    
    // Insert transaction
    $stmt = $db->prepare("
        INSERT INTO transactions (user_id, account_id, category_id, type, amount, description, transaction_date) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $user['id'],
        $input['account_id'],
        $input['category_id'],
        $input['type'],
        $amount,
        $description,
        $transactionDate
    ]);
    
    $transactionId = $db->lastInsertId();
    
    // Update account balance
    if ($input['type'] === 'income') {
        $newBalance = $account['balance'] + $amount;
    } else {
        $newBalance = $account['balance'] - $amount;
    }
    
    $stmt = $db->prepare("UPDATE accounts SET balance = ? WHERE id = ?");
    $stmt->execute([$newBalance, $input['account_id']]);
    
    
    // Commit transaction
    $db->commit();
    
    // Fetch the created transaction with category name and account name
    $stmt = $db->prepare("
        SELECT t.*, c.name as category_name, c.icon as category_icon, c.color as category_color,
               a.name as account_name
        FROM transactions t
        LEFT JOIN categories c ON t.category_id = c.id
        LEFT JOIN accounts a ON t.account_id = a.id
        WHERE t.id = ?
    ");
    $stmt->execute([$transactionId]);
    $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'message' => 'Transaction added successfully',
        'transaction' => $transaction
    ]);
    
} catch (PDOException $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
