<?php
/**
 * API Endpoint: Create New Destination
 * POST /api/create.php
 */

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

require_once '../config/database.php';

try {
    // Get POST data
    $data = json_decode(file_get_contents("php://input"));
    
    // Validate required fields
    $required = ['name', 'latitude', 'longitude', 'category'];
    foreach ($required as $field) {
        if (!isset($data->$field) || empty($data->$field)) {
            throw new Exception("Field '$field' is required");
        }
    }
    
    $database = new Database();
    $db = $database->getConnection();
    
    // Start transaction
    $db->beginTransaction();
    
    // Insert destination
    $query = "INSERT INTO destinations 
              (name, description, long_description, address, location, latitude, longitude, 
               category, rating, opening_hours, ticket_price, contact, website, facilities, 
               accessibility, featured) 
              VALUES 
              (:name, :description, :long_description, :address, :location, :latitude, :longitude,
               :category, :rating, :opening_hours, :ticket_price, :contact, :website, :facilities,
               :accessibility, :featured)";
    
    $stmt = $db->prepare($query);
    
    // Bind values
    $stmt->bindParam(':name', $data->name);
    $stmt->bindParam(':description', $data->description);
    $stmt->bindParam(':long_description', $data->long_description);
    $stmt->bindParam(':address', $data->address);
    $stmt->bindParam(':location', $data->location);
    $stmt->bindParam(':latitude', $data->latitude);
    $stmt->bindParam(':longitude', $data->longitude);
    $stmt->bindParam(':category', $data->category);
    
    $rating = isset($data->rating) ? $data->rating : 0;
    $stmt->bindParam(':rating', $rating);
    
    $opening_hours = isset($data->opening_hours) ? $data->opening_hours : null;
    $stmt->bindParam(':opening_hours', $opening_hours);
    
    $ticket_price = isset($data->ticket_price) ? $data->ticket_price : null;
    $stmt->bindParam(':ticket_price', $ticket_price);
    
    $contact = isset($data->contact) ? $data->contact : null;
    $stmt->bindParam(':contact', $contact);
    
    $website = isset($data->website) ? $data->website : null;
    $stmt->bindParam(':website', $website);
    
    $facilities = isset($data->facilities) ? json_encode($data->facilities) : null;
    $stmt->bindParam(':facilities', $facilities);
    
    $accessibility = isset($data->accessibility) ? json_encode($data->accessibility) : null;
    $stmt->bindParam(':accessibility', $accessibility);
    
    $featured = isset($data->featured) ? $data->featured : 0;
    $stmt->bindParam(':featured', $featured);
    
    $stmt->execute();
    
    $destinationId = $db->lastInsertId();
    
    // Insert images if provided
    if (isset($data->images) && is_array($data->images)) {
        $imageQuery = "INSERT INTO destination_images 
                       (destination_id, image_url, caption, is_primary, display_order) 
                       VALUES (:destination_id, :image_url, :caption, :is_primary, :display_order)";
        $imageStmt = $db->prepare($imageQuery);
        
        foreach ($data->images as $index => $image) {
            $imageStmt->bindParam(':destination_id', $destinationId);
            $imageStmt->bindParam(':image_url', $image->url);
            $caption = isset($image->caption) ? $image->caption : null;
            $imageStmt->bindParam(':caption', $caption);
            $is_primary = ($index === 0) ? 1 : 0;
            $imageStmt->bindParam(':is_primary', $is_primary);
            $imageStmt->bindParam(':display_order', $index);
            $imageStmt->execute();
        }
    }
    
    // Insert tags if provided
    if (isset($data->tags) && is_array($data->tags)) {
        $tagQuery = "INSERT INTO destination_tags (destination_id, tag_id) 
                     SELECT :destination_id, id FROM tags WHERE slug IN (" . 
                     implode(',', array_fill(0, count($data->tags), '?')) . ")";
        $tagStmt = $db->prepare($tagQuery);
        $tagStmt->bindParam(':destination_id', $destinationId);
        foreach ($data->tags as $i => $tag) {
            $tagStmt->bindValue($i + 1, $tag);
        }
        $tagStmt->execute();
    }
    
    // Commit transaction
    $db->commit();
    
    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Destination created successfully',
        'data' => [
            'id' => $destinationId
        ]
    ]);
    
} catch (Exception $e) {
    if (isset($db)) {
        $db->rollBack();
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to create destination: ' . $e->getMessage()
    ]);
}
