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

// Enable error logging
error_log("=== UPDATE API CALLED ===");
error_log("ID: " . ($_GET['id'] ?? 'not set'));

try {
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        throw new Exception('Destination ID is required');
    }
    
    // Get PUT data
    $data = json_decode(file_get_contents("php://input"));
    error_log("Received data: " . json_encode($data));
    
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
        'ticket_price', 'facilities', 'accessibility', 'featured'
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
    
    // Handle images field separately
    if (isset($data->images) && is_array($data->images)) {
        $fields[] = "images = :images";
        $params[":images"] = json_encode($data->images);
    }
    
    if (empty($fields)) {
        throw new Exception('No fields to update');
    }
    
    $query = "UPDATE destinations SET " . implode(', ', $fields) . " WHERE id = :id";
    error_log("SQL Query: " . $query);
    error_log("Params: " . json_encode($params));
    
    $stmt = $db->prepare($query);
    
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->execute();
    error_log("Rows affected: " . $stmt->rowCount());
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Destination updated successfully',
        'rows_affected' => $stmt->rowCount()
    ]);
    
} catch (Exception $e) {
    error_log("ERROR: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update destination: ' . $e->getMessage()
    ]);
}
