<?php
/**
 * API Endpoint: Get All Destinations
 * GET /api/destinations.php
 * 
 * Query Parameters:
 * - category: Filter by category (alam, budaya, kuliner, sejarah)
 * - rating: Filter by minimum rating
 * - search: Search by name
 * - featured: Get only featured destinations (1 or 0)
 * - limit: Limit results
 * - offset: Offset for pagination
 */

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Max-Age: 3600');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

require_once '../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Build base query
    $query = "SELECT 
                d.id,
                d.name,
                d.description,
                d.long_description,
                d.address,
                d.location,
                d.latitude,
                d.longitude,
                d.category,
                d.rating,
                d.opening_hours,
                d.ticket_price,
                d.contact,
                d.website,
                d.facilities,
                d.accessibility,
                d.featured,
                GROUP_CONCAT(DISTINCT t.name) as tags,
                GROUP_CONCAT(DISTINCT t.slug) as tag_slugs,
                (SELECT image_url FROM destination_images WHERE destination_id = d.id AND is_primary = 1 LIMIT 1) as primary_image,
                (SELECT GROUP_CONCAT(image_url) FROM destination_images WHERE destination_id = d.id ORDER BY display_order) as images
              FROM destinations d
              LEFT JOIN destination_tags dt ON d.id = dt.destination_id
              LEFT JOIN tags t ON dt.tag_id = t.id
              WHERE 1=1";
    
    $params = [];
    
    // Filter by category
    if (isset($_GET['category']) && !empty($_GET['category']) && $_GET['category'] !== 'all') {
        $query .= " AND d.category = :category";
        $params[':category'] = $_GET['category'];
    }
    
    // Filter by minimum rating
    if (isset($_GET['rating']) && is_numeric($_GET['rating'])) {
        $query .= " AND d.rating >= :rating";
        $params[':rating'] = floatval($_GET['rating']);
    }
    
    // Search by name
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $query .= " AND d.name LIKE :search";
        $params[':search'] = '%' . $_GET['search'] . '%';
    }
    
    // Filter by featured
    if (isset($_GET['featured'])) {
        $query .= " AND d.featured = :featured";
        $params[':featured'] = intval($_GET['featured']);
    }
    
    // Filter by price range
    if (isset($_GET['min_price']) && is_numeric($_GET['min_price'])) {
        $query .= " AND CAST(REGEXP_REPLACE(d.ticket_price, '[^0-9]', '') AS UNSIGNED) >= :min_price";
        $params[':min_price'] = intval($_GET['min_price']);
    }
    
    if (isset($_GET['max_price']) && is_numeric($_GET['max_price'])) {
        $query .= " AND CAST(REGEXP_REPLACE(d.ticket_price, '[^0-9]', '') AS UNSIGNED) <= :max_price";
        $params[':max_price'] = intval($_GET['max_price']);
    }
    
    $query .= " GROUP BY d.id ORDER BY d.rating DESC, d.name ASC";
    
    // Apply limit and offset for pagination
    if (isset($_GET['limit']) && is_numeric($_GET['limit'])) {
        $query .= " LIMIT :limit";
        if (isset($_GET['offset']) && is_numeric($_GET['offset'])) {
            $query .= " OFFSET :offset";
        }
    }
    
    $stmt = $db->prepare($query);
    
    // Bind parameters
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    // Bind limit and offset separately (must be integers)
    if (isset($_GET['limit']) && is_numeric($_GET['limit'])) {
        $stmt->bindValue(':limit', intval($_GET['limit']), PDO::PARAM_INT);
        if (isset($_GET['offset']) && is_numeric($_GET['offset'])) {
            $stmt->bindValue(':offset', intval($_GET['offset']), PDO::PARAM_INT);
        }
    }
    
    $stmt->execute();
    $destinations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format data
    foreach ($destinations as &$destination) {
        // Parse JSON fields
        $destination['facilities'] = json_decode($destination['facilities']);
        $destination['accessibility'] = json_decode($destination['accessibility']);
        
        // Convert tags to array
        $destination['tags'] = $destination['tags'] ? explode(',', $destination['tags']) : [];
        $destination['tag_slugs'] = $destination['tag_slugs'] ? explode(',', $destination['tag_slugs']) : [];
        
        // Convert images to array
        $destination['images'] = $destination['images'] ? explode(',', $destination['images']) : [];
        
        // Convert numeric strings to proper types
        $destination['latitude'] = floatval($destination['latitude']);
        $destination['longitude'] = floatval($destination['longitude']);
        $destination['rating'] = floatval($destination['rating']);
        $destination['featured'] = (bool)$destination['featured'];
        
        // Create coords array for Leaflet
        $destination['coords'] = [
            $destination['latitude'],
            $destination['longitude']
        ];
    }
    
    // Get total count (without limit)
    $countQuery = "SELECT COUNT(DISTINCT d.id) as total FROM destinations d WHERE 1=1";
    if (isset($_GET['category']) && !empty($_GET['category']) && $_GET['category'] !== 'all') {
        $countQuery .= " AND d.category = :category";
    }
    if (isset($_GET['rating']) && is_numeric($_GET['rating'])) {
        $countQuery .= " AND d.rating >= :rating";
    }
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $countQuery .= " AND d.name LIKE :search";
    }
    
    $countStmt = $db->prepare($countQuery);
    foreach ($params as $key => $value) {
        if (strpos($key, 'limit') === false && strpos($key, 'offset') === false) {
            $countStmt->bindValue($key, $value);
        }
    }
    $countStmt->execute();
    $totalCount = $countStmt->fetch()['total'];
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => $destinations,
        'total' => intval($totalCount),
        'count' => count($destinations)
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
