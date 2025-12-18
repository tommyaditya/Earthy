<?php
/**
 * API Endpoint: Get Single Destination
 * GET /api/destination.php?id=1
 */

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: GET');

require_once '../config/database.php';

try {
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        throw new Exception('Destination ID is required');
    }
    
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT 
                d.*,
                GROUP_CONCAT(DISTINCT t.name) as tags,
                GROUP_CONCAT(DISTINCT t.slug) as tag_slugs
              FROM destinations d
              LEFT JOIN destination_tags dt ON d.id = dt.destination_id
              LEFT JOIN tags t ON dt.tag_id = t.id
              WHERE d.id = :id
              GROUP BY d.id";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $_GET['id'], PDO::PARAM_INT);
    $stmt->execute();
    
    $destination = $stmt->fetch();
    
    if (!$destination) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Destination not found'
        ]);
        exit;
    }
    
    // Get images
    $imageQuery = "SELECT image_url, caption, is_primary, display_order 
                   FROM destination_images 
                   WHERE destination_id = :id 
                   ORDER BY display_order";
    $imageStmt = $db->prepare($imageQuery);
    $imageStmt->bindParam(':id', $_GET['id'], PDO::PARAM_INT);
    $imageStmt->execute();
    $destination['images'] = $imageStmt->fetchAll();
    
    // Get reviews
    $reviewQuery = "SELECT id, user_name, rating, comment, created_at 
                    FROM reviews 
                    WHERE destination_id = :id 
                    ORDER BY created_at DESC";
    $reviewStmt = $db->prepare($reviewQuery);
    $reviewStmt->bindParam(':id', $_GET['id'], PDO::PARAM_INT);
    $reviewStmt->execute();
    $destination['reviews'] = $reviewStmt->fetchAll();
    
    // Calculate review statistics
    $destination['review_count'] = count($destination['reviews']);
    $destination['average_rating'] = $destination['review_count'] > 0 
        ? array_sum(array_column($destination['reviews'], 'rating')) / $destination['review_count']
        : 0;
    
    // Parse JSON fields
    $destination['facilities'] = json_decode($destination['facilities']);
    $destination['accessibility'] = json_decode($destination['accessibility']);
    
    // Convert tags to array
    $destination['tags'] = $destination['tags'] ? explode(',', $destination['tags']) : [];
    $destination['tag_slugs'] = $destination['tag_slugs'] ? explode(',', $destination['tag_slugs']) : [];
    
    // Convert numeric types
    $destination['latitude'] = floatval($destination['latitude']);
    $destination['longitude'] = floatval($destination['longitude']);
    $destination['rating'] = floatval($destination['rating']);
    $destination['featured'] = (bool)$destination['featured'];
    
    // Create coords array
    $destination['coords'] = [
        $destination['latitude'],
        $destination['longitude']
    ];
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => $destination
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
