<?php
/**
 * API Endpoint: Upload Profile Picture
 * POST /api/upload_profile.php
 */

session_start();

header('Content-Type: application/json; charset=UTF-8');

require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized. Please login.'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
}

try {
    // Check if file was uploaded
    if (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('No file uploaded or upload error');
    }
    
    $file = $_FILES['profile_picture'];
    $userId = $_SESSION['user_id'];
    
    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $fileType = $file['type'];
    
    if (!in_array($fileType, $allowedTypes)) {
        throw new Exception('File type not allowed. Only JPG, PNG, GIF, and WEBP are allowed.');
    }
    
    // Validate file size (max 5MB)
    $maxSize = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $maxSize) {
        throw new Exception('File size too large. Maximum 5MB.');
    }
    
    // Create upload directory if not exists
    $uploadDir = '../uploads/profiles/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'profile_' . $userId . '_' . time() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    // Get database connection
    $database = new Database();
    $db = $database->getConnection();
    
    // Get old profile picture to delete
    $query = "SELECT profile_picture FROM users WHERE id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();
    $oldData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception('Failed to move uploaded file');
    }
    
    // Update database
    $dbPath = 'uploads/profiles/' . $filename;
    $query = "UPDATE users SET profile_picture = :profile_picture WHERE id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':profile_picture', $dbPath);
    $stmt->bindParam(':user_id', $userId);
    
    if ($stmt->execute()) {
        // Delete old profile picture if exists
        if ($oldData && $oldData['profile_picture']) {
            $oldFile = '../' . $oldData['profile_picture'];
            if (file_exists($oldFile)) {
                unlink($oldFile);
            }
        }
        
        // Update session
        $_SESSION['profile_picture'] = $dbPath;
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Profile picture updated successfully',
            'profile_picture' => $dbPath
        ]);
    } else {
        // Delete uploaded file if database update fails
        if (file_exists($filepath)) {
            unlink($filepath);
        }
        throw new Exception('Failed to update database');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
