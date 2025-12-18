<?php
/**
 * API Endpoint: Manage Reviews
 * GET /api/reviews.php?destination_id=1 - Get reviews
 * POST /api/reviews.php - Create new review
 */

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

require_once '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($method === 'GET') {
        // Get reviews for a destination
        if (!isset($_GET['destination_id'])) {
            throw new Exception('Destination ID is required');
        }
        
        $query = "SELECT id, user_name, rating, comment, created_at 
                  FROM reviews 
                  WHERE destination_id = :destination_id 
                  ORDER BY created_at DESC";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':destination_id', $_GET['destination_id']);
        $stmt->execute();
        
        $reviews = $stmt->fetchAll();
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $reviews,
            'count' => count($reviews)
        ]);
        
    } elseif ($method === 'POST') {
        // Create new review
        $data = json_decode(file_get_contents("php://input"));
        
        // Validate
        if (!isset($data->destination_id) || !isset($data->user_name) || !isset($data->rating)) {
            throw new Exception('Missing required fields');
        }
        
        if ($data->rating < 1 || $data->rating > 5) {
            throw new Exception('Rating must be between 1 and 5');
        }
        
        $query = "INSERT INTO reviews (destination_id, user_name, user_email, rating, comment) 
                  VALUES (:destination_id, :user_name, :user_email, :rating, :comment)";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':destination_id', $data->destination_id);
        $stmt->bindParam(':user_name', $data->user_name);
        
        $email = isset($data->user_email) ? $data->user_email : null;
        $stmt->bindParam(':user_email', $email);
        $stmt->bindParam(':rating', $data->rating);
        
        $comment = isset($data->comment) ? $data->comment : null;
        $stmt->bindParam(':comment', $comment);
        
        $stmt->execute();
        
        // Update destination average rating
        $updateRatingQuery = "UPDATE destinations 
                              SET rating = (SELECT AVG(rating) FROM reviews WHERE destination_id = :destination_id)
                              WHERE id = :destination_id";
        $updateStmt = $db->prepare($updateRatingQuery);
        $updateStmt->bindParam(':destination_id', $data->destination_id);
        $updateStmt->execute();
        
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Review submitted successfully',
            'data' => ['id' => $db->lastInsertId()]
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
