<?php
/**
 * API Endpoint: Authentication
 * POST /api/auth.php - Login
 * GET /api/auth.php?action=logout - Logout
 * GET /api/auth.php?action=check - Check session
 */

session_start();

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

require_once '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($method === 'POST') {
        // Login
        $data = json_decode(file_get_contents("php://input"));
        
        if (!isset($data->username) || !isset($data->password)) {
            throw new Exception('Username dan password harus diisi');
        }
        
        $query = "SELECT id, username, email, password, full_name, role, is_active, profile_picture 
                  FROM users 
                  WHERE username = :username AND is_active = 1";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':username', $data->username);
        $stmt->execute();
        
        $user = $stmt->fetch();
        
        if (!$user) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Username atau password salah'
            ]);
            exit;
        }
        
        // Verify password
        if (!password_verify($data->password, $user['password'])) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Username atau password salah'
            ]);
            exit;
        }
        
        // Set session based on role
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['logged_in'] = true;
        $_SESSION['profile_picture'] = $user['profile_picture'];
        
        // For backward compatibility with existing admin pages
        if ($user['role'] === 'admin') {
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            $_SESSION['admin_email'] = $user['email'];
            $_SESSION['admin_name'] = $user['full_name'];
            $_SESSION['admin_role'] = $user['role'];
            $_SESSION['admin_logged_in'] = true;
        }
        
        // Determine redirect URL based on role (relative to login page location)
        $redirectUrl = $user['role'] === 'admin' ? 'index.php' : '../public/index.html';
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Login berhasil',
            'redirect' => $redirectUrl,
            'data' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'name' => $user['full_name'],
                'role' => $user['role']
            ]
        ]);
        
    } elseif ($method === 'GET') {
        $action = isset($_GET['action']) ? $_GET['action'] : 'check';
        
        if ($action === 'logout') {
            // Logout
            session_destroy();
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Logout berhasil'
            ]);
            
        } elseif ($action === 'check') {
            // Check session
            if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']) {
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'logged_in' => true,
                    'data' => [
                        'id' => $_SESSION['admin_id'],
                        'username' => $_SESSION['admin_username'],
                        'email' => $_SESSION['admin_email'],
                        'name' => $_SESSION['admin_name'],
                        'role' => $_SESSION['admin_role']
                    ]
                ]);
            } else {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'logged_in' => false,
                    'message' => 'Not logged in'
                ]);
            }
        }
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
