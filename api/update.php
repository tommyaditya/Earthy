<?php
/**
 * API Endpoint: Update Destination
 * PUT /api/update.php?id=1
 */

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: PUT, PATCH');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

require_once '../config/database.php';

try {
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        throw new Exception('Destination ID is required');
    }
    
    // Get PUT data
    $data = json_decode(file_get_contents("php://input"));
    
    $database = new Database();
    $db = $database->getConnection();
    
    // Check if destination exists
    $checkQuery = "SELECT id FROM destinations WHERE id = :id";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bindParam(':id', $_GET['id']);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() === 0) {
        throw new Exception('Destination not found');
    }
    
    // Build update query dynamically based on provided fields
    $fields = [];
    $params = [':id' => $_GET['id']];
    
    $allowedFields = [
        'name', 'description', 'long_description', 'address', 'location',
        'latitude', 'longitude', 'category', 'rating', 'opening_hours',
        'ticket_price', 'contact', 'website', 'facilities', 'accessibility', 'featured'
    ];
    
    foreach ($allowedFields as $field) {
        if (isset($data->$field)) {
            $fields[] = "$field = :$field";
            
            // Handle JSON fields
            if ($field === 'facilities' || $field === 'accessibility') {
                $params[":$field"] = json_encode($data->$field);
            } else {
                $params[":$field"] = $data->$field;
            }
        }
    }
    
    if (empty($fields)) {
        throw new Exception('No fields to update');
    }
    
    $query = "UPDATE destinations SET " . implode(', ', $fields) . " WHERE id = :id";
    $stmt = $db->prepare($query);
    
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->execute();
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Destination updated successfully'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update destination: ' . $e->getMessage()
    ]);
}
