<?php
/**
 * API Endpoint: Delete Destination
 * DELETE /api/delete.php?id=1
 */

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: DELETE');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

require_once '../config/database.php';

try {
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        throw new Exception('Destination ID is required');
    }
    
    $database = new Database();
    $db = $database->getConnection();
    
    // Check if destination exists
    $checkQuery = "SELECT id FROM destinations WHERE id = :id";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bindParam(':id', $_GET['id']);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Destination not found'
        ]);
        exit;
    }
    
    // Delete destination (cascades will handle related records)
    $query = "DELETE FROM destinations WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $_GET['id']);
    $stmt->execute();
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Destination deleted successfully'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to delete destination: ' . $e->getMessage()
    ]);
}
