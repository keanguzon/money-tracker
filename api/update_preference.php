<?php
/**
 * Update User Preferences API
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

if (empty($input)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No data provided']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    
    $updates = [];
    $params = [];
    
    // Update currency preference
    if (isset($input['currency'])) {
        $allowedCurrencies = ['PHP', 'USD', 'EUR', 'GBP', 'JPY', 'CAD', 'AUD'];
        if (!in_array($input['currency'], $allowedCurrencies)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid currency']);
            exit;
        }
        $updates[] = 'currency = ?';
        $params[] = $input['currency'];
    }
    
    // Update full name
    if (isset($input['full_name'])) {
        $updates[] = 'full_name = ?';
        $params[] = trim($input['full_name']);
    }
    
    // Update email
    if (isset($input['email'])) {
        $email = filter_var($input['email'], FILTER_VALIDATE_EMAIL);
        if (!$email) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid email address']);
            exit;
        }
        
        // Check if email is already used by another user
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user['id']]);
        if ($stmt->fetch()) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Email already in use']);
            exit;
        }
        
        $updates[] = 'email = ?';
        $params[] = $email;
    }
    
    if (empty($updates)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No valid fields to update']);
        exit;
    }
    
    // Build and execute update query
    $params[] = $user['id'];
    $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    
    // Get updated user data
    $stmt = $db->prepare("SELECT id, username, email, full_name, currency FROM users WHERE id = ?");
    $stmt->execute([$user['id']]);
    $updatedUser = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'message' => 'Preferences updated successfully',
        'user' => $updatedUser
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
