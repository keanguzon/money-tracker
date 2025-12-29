<?php
/**
 * Get Categories API
 * Money Tracker Application
 */

header('Content-Type: application/json');

require_once dirname(__DIR__) . '/includes/auth.php';

// Check if user is authenticated
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Only accept GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$user = getCurrentUser();
$type = $_GET['type'] ?? null;

try {
    $db = Database::getInstance()->getConnection();
    
    $sql = "SELECT * FROM categories WHERE (user_id = ? OR user_id IS NULL)";
    $params = [$user['id']];
    
    if ($type && in_array($type, ['income', 'expense'])) {
        $sql .= " AND type = ?";
        $params[] = $type;
    }
    
    $sql .= " ORDER BY name ASC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'categories' => $categories
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
