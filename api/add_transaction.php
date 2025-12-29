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

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$required = ['type', 'category_id', 'amount', 'description'];
foreach ($required as $field) {
    if (empty($input[$field])) {
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

// Get transaction date or use current date
$transactionDate = !empty($input['transaction_date']) ? $input['transaction_date'] : date('Y-m-d');

try {
    $db = Database::getInstance()->getConnection();
    
    // Verify category exists and belongs to user or is default
    $stmt = $db->prepare("SELECT id FROM categories WHERE id = ? AND (user_id = ? OR user_id IS NULL)");
    $stmt->execute([$input['category_id'], $user['id']]);
    
    if (!$stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid category']);
        exit;
    }
    
    // Insert transaction
    $stmt = $db->prepare("
        INSERT INTO transactions (user_id, category_id, type, amount, description, transaction_date) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $user['id'],
        $input['category_id'],
        $input['type'],
        $amount,
        $input['description'],
        $transactionDate
    ]);
    
    $transactionId = $db->lastInsertId();
    
    // Fetch the created transaction with category name
    $stmt = $db->prepare("
        SELECT t.*, c.name as category_name, c.icon as category_icon, c.color as category_color
        FROM transactions t
        LEFT JOIN categories c ON t.category_id = c.id
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
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
