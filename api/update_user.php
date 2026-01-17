<?php
/**
 * API Endpoint: Update User Profile
 * POST /api/update_user.php
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
    $database = new Database();
    $db = $database->getConnection();

    $userId = $_SESSION['user_id'];
    $username = trim($_POST['username'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');

    // Validation
    if (empty($username)) {
        throw new Exception('Username tidak boleh kosong');
    }

    if (preg_match('/\s/', $username)) {
        throw new Exception('Username tidak boleh mengandung spasi');
    }

    // Check if username exists (if changed)
    if ($username !== $_SESSION['username']) {
        $checkQuery = "SELECT id FROM users WHERE username = :username AND id != :id";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(':username', $username);
        $checkStmt->bindParam(':id', $userId);
        $checkStmt->execute();

        if ($checkStmt->rowCount() > 0) {
            throw new Exception('Username sudah digunakan oleh pengguna lain');
        }
    }

    // Update Database
    $query = "UPDATE users SET username = :username, full_name = :full_name WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':full_name', $fullName);
    $stmt->bindParam(':id', $userId);

    if ($stmt->execute()) {
        // Update Session
        $_SESSION['username'] = $username;
        $_SESSION['full_name'] = $fullName;

        // Also update admin session variables if applicable, for backward compatibility
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
            $_SESSION['admin_username'] = $username;
            $_SESSION['admin_name'] = $fullName;
        }

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Profil berhasil diperbarui',
            'data' => [
                'username' => $username,
                'full_name' => $fullName
            ]
        ]);
    } else {
        throw new Exception('Gagal memperbarui database');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>