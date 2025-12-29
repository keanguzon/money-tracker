<?php
/**
 * Delete Transaction API
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

// Accept POST or DELETE requests
if (!in_array($_SERVER['REQUEST_METHOD'], ['POST', 'DELETE'])) {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$user = getCurrentUser();

// Get transaction ID from query string or JSON body
$transactionId = $_GET['id'] ?? null;

if (!$transactionId) {
    $input = json_decode(file_get_contents('php://input'), true);
    $transactionId = $input['id'] ?? null;
}

if (!$transactionId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Transaction ID is required']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Verify transaction exists and belongs to user
    $stmt = $db->prepare("SELECT id FROM transactions WHERE id = ? AND user_id = ?");
    $stmt->execute([$transactionId, $user['id']]);
    
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Transaction not found']);
        exit;
    }
    
    // Delete transaction
    $stmt = $db->prepare("DELETE FROM transactions WHERE id = ? AND user_id = ?");
    $stmt->execute([$transactionId, $user['id']]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Transaction deleted successfully'
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
