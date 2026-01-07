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
        $stmt->bindParam(':destination_id', $data->destination_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_name', $data->user_name, PDO::PARAM_STR);
        
        $email = isset($data->user_email) ? $data->user_email : null;
        $stmt->bindParam(':user_email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':rating', $data->rating, PDO::PARAM_INT);
        
        $comment = isset($data->comment) ? $data->comment : null;
        $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
        
        $stmt->execute();
        
        $reviewId = $db->lastInsertId();
        
        // Update destination average rating
        $updateRatingQuery = "UPDATE destinations 
                              SET rating = (SELECT AVG(rating) FROM reviews WHERE destination_id = :dest_id)
                              WHERE id = :dest_id2";
        $updateStmt = $db->prepare($updateRatingQuery);
        $updateStmt->bindValue(':dest_id', $data->destination_id, PDO::PARAM_INT);
        $updateStmt->bindValue(':dest_id2', $data->destination_id, PDO::PARAM_INT);
        $updateStmt->execute();
        
        // Get the updated average rating
        $avgQuery = "SELECT AVG(rating) as avg_rating FROM reviews WHERE destination_id = :destination_id";
        $avgStmt = $db->prepare($avgQuery);
        $avgStmt->bindParam(':destination_id', $data->destination_id, PDO::PARAM_INT);
        $avgStmt->execute();
        $avgResult = $avgStmt->fetch();
        
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Review submitted successfully',
            'data' => [
                'id' => $reviewId,
                'new_average_rating' => round($avgResult['avg_rating'], 1)
            ]
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
