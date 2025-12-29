<?php
/**
 * Upload Profile Picture API
 * BukoJuice Application
 */

require_once dirname(__DIR__) . '/includes/auth.php';
requireAuth();

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    if (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('No file uploaded or upload error');
    }

    $file = $_FILES['profile_picture'];
    $user = getCurrentUser();
    
    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        throw new Exception('Invalid file type. Only JPG, PNG, GIF, and WebP are allowed.');
    }
    
    // Validate file size (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        throw new Exception('File size must be less than 5MB');
    }
    
    // Create uploads directory if it doesn't exist
    $uploadDir = dirname(__DIR__) . '/uploads/profiles/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'profile_' . $user['id'] . '_' . time() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    // Delete old profile picture if exists
    if (!empty($user['profile_picture'])) {
        $oldFile = dirname(__DIR__) . '/uploads/profiles/' . basename($user['profile_picture']);
        if (file_exists($oldFile)) {
            unlink($oldFile);
        }
    }
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception('Failed to save uploaded file');
    }
    
    // Update database
    $db = getDB();
    $pictureUrl = APP_URL . '/uploads/profiles/' . $filename;
    $stmt = $db->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
    $stmt->execute([$pictureUrl, $user['id']]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Profile picture updated successfully',
        'picture_url' => $pictureUrl
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
