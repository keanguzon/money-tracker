<?php
/**
 * Accounts API Endpoint
 * Handle CRUD operations for accounts (e-wallets, banks, cash, etc.)
 */

require_once dirname(__DIR__) . '/includes/auth.php';
requireAuth();

header('Content-Type: application/json');

$user = getCurrentUser();
$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];

// Handle PUT and DELETE through POST with _method parameter
if ($method === 'POST' && isset($_POST['_method'])) {
    $method = $_POST['_method'];
}

try {
    switch ($method) {
        case 'GET':
            // Get single account or list all
            if (isset($_GET['id'])) {
                $stmt = $db->prepare("SELECT * FROM accounts WHERE id = ? AND user_id = ?");
                $stmt->execute([$_GET['id'], $user['id']]);
                $account = $stmt->fetch();
                
                if ($account) {
                    echo json_encode(['success' => true, 'data' => $account]);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Account not found']);
                }
            } else {
                $stmt = $db->prepare("SELECT * FROM accounts WHERE user_id = ? ORDER BY type, name");
                $stmt->execute([$user['id']]);
                $accounts = $stmt->fetchAll();
                
                echo json_encode(['success' => true, 'data' => $accounts]);
            }
            break;

        case 'POST':
            // Create new account
            $name = $_POST['name'] ?? '';
            $type = $_POST['type'] ?? '';
            $balance = floatval($_POST['balance'] ?? 0);
            $color = $_POST['color'] ?? '#10b981';
            $icon = $_POST['icon'] ?? '';
            $isSavings = !empty($_POST['is_savings']) ? true : false;
            $interestRate = floatval($_POST['interest_rate'] ?? 0);
            $includeInNetworth = !empty($_POST['include_in_networth']) ? true : false;

            if (empty($name) || empty($type)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Name and type are required']);
                break;
            }

            $stmt = $db->prepare("
                INSERT INTO accounts (user_id, name, type, balance, color, icon, is_savings, interest_rate, include_in_networth, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                $user['id'],
                $name,
                $type,
                $balance,
                $color,
                $icon,
                $isSavings ? 'true' : 'false',
                $interestRate,
                $includeInNetworth ? 'true' : 'false',
                'true'
            ]);

            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Account created successfully', 'id' => $db->lastInsertId()]);
            } else {
                throw new Exception('Failed to create account');
            }
            break;

        case 'PUT':
            // Update account
            $id = $_POST['id'] ?? '';
            $name = $_POST['name'] ?? '';
            $type = $_POST['type'] ?? '';
            $balance = floatval($_POST['balance'] ?? 0);
            $color = $_POST['color'] ?? '#10b981';
            $isSavings = !empty($_POST['is_savings']) ? true : false;
            $interestRate = floatval($_POST['interest_rate'] ?? 0);
            $includeInNetworth = !empty($_POST['include_in_networth']) ? true : false;
            $isActive = !empty($_POST['is_active']) ? true : false;

            if (empty($id) || empty($name) || empty($type)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID, name, and type are required']);
                break;
            }

            $stmt = $db->prepare("
                UPDATE accounts 
                SET name = ?, type = ?, balance = ?, color = ?, is_savings = ?, interest_rate = ?, include_in_networth = ?, is_active = ?, updated_at = NOW()
                WHERE id = ? AND user_id = ?
            ");
            
            $result = $stmt->execute([
                $name,
                $type,
                $balance,
                $color,
                $isSavings ? 'true' : 'false',
                $interestRate,
                $includeInNetworth ? 'true' : 'false',
                $isActive ? 'true' : 'false',
                $id,
                $user['id']
            ]);

            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Account updated successfully']);
            } else {
                throw new Exception('Failed to update account');
            }
            break;

        case 'DELETE':
            // Delete account
            $id = $_POST['id'] ?? '';

            if (empty($id)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Account ID is required']);
                break;
            }

            // Check if account has transactions
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM transactions WHERE account_id = ?");
            $stmt->execute([$id]);
            $result = $stmt->fetch();

            if ($result['count'] > 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Cannot delete account with existing transactions. Archive it instead.']);
                break;
            }

            $stmt = $db->prepare("DELETE FROM accounts WHERE id = ? AND user_id = ?");
            $result = $stmt->execute([$id, $user['id']]);

            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Account deleted successfully']);
            } else {
                throw new Exception('Failed to delete account');
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
