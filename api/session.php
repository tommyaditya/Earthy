<?php
/**
 * API to check user session status
 * GET /api/session.php
 */

session_start();

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');

$response = [
    'logged_in' => false,
    'user' => null
];

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
    $response['logged_in'] = true;
    $response['user'] = [
        'id' => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? '',
        'email' => $_SESSION['email'] ?? '',
        'full_name' => $_SESSION['full_name'] ?? '',
        'role' => $_SESSION['role'] ?? 'user',
        'profile_picture' => $_SESSION['profile_picture'] ?? null
    ];
}

echo json_encode($response);
?>
