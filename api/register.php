<?php
/**
 * API Endpoint: User Registration
 * POST /api/register.php - Register new user
 */

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

require_once '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $data = json_decode(file_get_contents("php://input"));
    
    // Validate input
    if (!isset($data->username) || !isset($data->email) || !isset($data->password)) {
        throw new Exception('Username, email, dan password harus diisi');
    }
    
    // Validate username (alphanumeric and underscore only, 3-50 chars)
    if (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $data->username)) {
        throw new Exception('Username harus 3-50 karakter (huruf, angka, underscore)');
    }
    
    // Validate email
    if (!filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Format email tidak valid');
    }
    
    // Validate password length
    if (strlen($data->password) < 6) {
        throw new Exception('Password minimal 6 karakter');
    }
    
    // Check if username already exists
    $checkQuery = "SELECT id FROM users WHERE username = :username";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bindParam(':username', $data->username);
    $checkStmt->execute();
    
    if ($checkStmt->fetch()) {
        throw new Exception('Username sudah digunakan');
    }
    
    // Check if email already exists
    $checkQuery = "SELECT id FROM users WHERE email = :email";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bindParam(':email', $data->email);
    $checkStmt->execute();
    
    if ($checkStmt->fetch()) {
        throw new Exception('Email sudah terdaftar');
    }
    
    // Hash password
    $hashedPassword = password_hash($data->password, PASSWORD_DEFAULT);
    
    // Insert new user
    $query = "INSERT INTO users (username, email, password, full_name, role) 
              VALUES (:username, :email, :password, :full_name, 'user')";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $data->username);
    $stmt->bindParam(':email', $data->email);
    $stmt->bindParam(':password', $hashedPassword);
    
    $fullName = isset($data->full_name) ? $data->full_name : $data->username;
    $stmt->bindParam(':full_name', $fullName);
    
    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Registrasi berhasil! Silakan login.',
            'data' => [
                'username' => $data->username,
                'email' => $data->email
            ]
        ]);
    } else {
        throw new Exception('Gagal mendaftar. Silakan coba lagi.');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
