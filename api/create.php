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
    // Support both JSON body and multipart/form-data
    $isForm = !empty($_FILES) || !empty($_POST);
    if ($isForm) {
        // Prefer $_POST values when using form-data
        $data = new stdClass();
        $data->name = $_POST['name'] ?? null;
        $data->latitude = $_POST['latitude'] ?? null;
        $data->longitude = $_POST['longitude'] ?? null;
        $data->category = $_POST['category'] ?? null;
        $data->description = $_POST['description'] ?? null;
        $data->long_description = $_POST['long_description'] ?? null;
        $data->address = $_POST['address'] ?? null;
        $data->location = $_POST['location'] ?? null;
        $data->rating = $_POST['rating'] ?? 0;
        $data->opening_hours = $_POST['opening_hours'] ?? null;
        $data->ticket_price = $_POST['ticket_price'] ?? null;
        $data->facilities = isset($_POST['facilities']) ? json_decode($_POST['facilities'], true) : null;
        $data->accessibility = isset($_POST['accessibility']) ? json_decode($_POST['accessibility'], true) : null;
        $data->featured = $_POST['featured'] ?? 0;
        $data->tags = isset($_POST['tags']) ? json_decode($_POST['tags'], true) : null;
        // note: images will be handled via $_FILES below
    } else {
        $data = json_decode(file_get_contents("php://input"));
    }

    // Validate required fields
    $required = ['name', 'latitude', 'longitude', 'category'];
    foreach ($required as $field) {
        if (!isset($data->$field) || $data->$field === '' || $data->$field === null) {
            throw new Exception("Field '$field' is required");
        }
    }

    $database = new Database();
    $db = $database->getConnection();

    // Start transaction
    $db->beginTransaction();

    // Handle file uploads (if any). Save only filenames.
    $uploadedFilenames = [];

    if (!empty($_FILES['images'])) {
        // Support multiple files: input name="images[]" expected
        $files = $_FILES['images'];
        $count = is_array($files['name']) ? count($files['name']) : 0;
        $uploadDir = __DIR__ . '/../uploads/destinations/';
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
                throw new Exception('Unable to create uploads directory');
            }
        }

        for ($i = 0; $i < $count; $i++) {
            // Skip empty uploads
            if ($files['error'][$i] === UPLOAD_ERR_NO_FILE) continue;

            if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                throw new Exception("Upload error for file {$files['name'][$i]} (code {$files['error'][$i]})");
            }

            $originalName = $files['name'][$i];
            $tmpPath = $files['tmp_name'][$i];

            // Sanitize filename and ensure uniqueness
            $ext = pathinfo($originalName, PATHINFO_EXTENSION);
            $base = pathinfo($originalName, PATHINFO_FILENAME);
            $safeBase = preg_replace('/[^A-Za-z0-9_\-]/', '_', $base);
            $uniqueSuffix = time() . '_' . bin2hex(random_bytes(4));
            $filename = $safeBase . '_' . $uniqueSuffix . ($ext ? '.' . $ext : '');

            $targetPath = $uploadDir . $filename;

            if (!move_uploaded_file($tmpPath, $targetPath)) {
                // If moving fails, rollback and return error (do not save DB record)
                $db->rollBack();
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => "Failed to move uploaded file: {$originalName}"
                ]);
                exit;
            }

            $uploadedFilenames[] = $filename; // store only filename
        }
    } elseif (isset($data->images) && is_array($data->images)) {
        // If client provided image filenames in JSON body, keep them as-is
        $uploadedFilenames = array_values($data->images);
    }

    // Insert destination
    $query = "INSERT INTO destinations 
              (name, description, long_description, address, location, latitude, longitude, 
               category, rating, opening_hours, ticket_price, facilities, 
               accessibility, featured, images) 
              VALUES 
              (:name, :description, :long_description, :address, :location, :latitude, :longitude,
               :category, :rating, :opening_hours, :ticket_price, :facilities,
               :accessibility, :featured, :images)";

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

    $facilities = isset($data->facilities) ? json_encode($data->facilities) : null;
    $stmt->bindParam(':facilities', $facilities);

    $accessibility = isset($data->accessibility) ? json_encode($data->accessibility) : null;
    $stmt->bindParam(':accessibility', $accessibility);

    $featured = isset($data->featured) ? $data->featured : 0;
    $stmt->bindParam(':featured', $featured);

    // Ensure database saves only a JSON array of filenames or NULL
    $images = !empty($uploadedFilenames) ? json_encode(array_values($uploadedFilenames)) : null;
    $stmt->bindParam(':images', $images);

    $stmt->execute();

    $destinationId = $db->lastInsertId();

    // Insert tags if provided
    if (isset($data->tags) && is_array($data->tags) && count($data->tags) > 0) {
        $placeholders = implode(',', array_fill(0, count($data->tags), '?'));
        $tagQuery = "INSERT INTO destination_tags (destination_id, tag_id) 
                     SELECT ?, id FROM tags WHERE slug IN ($placeholders)";
        $tagStmt = $db->prepare($tagQuery);
        // first param is destination_id
        $tagStmt->bindValue(1, $destinationId, PDO::PARAM_INT);
        foreach ($data->tags as $i => $tag) {
            $tagStmt->bindValue($i + 2, $tag);
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
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to create destination: ' . $e->getMessage()
    ]);
}
