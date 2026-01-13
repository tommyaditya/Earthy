<?php
/**
 * API Endpoint: Get Single Destination
 * GET /api/destination.php?id=1
 */

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

require_once __DIR__ . '/../config/database.php';

try {
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid destination id']);
        exit;
    }

    $database = new Database();
    $db = $database->getConnection();

    // Fetch destination row
    $stmt = $db->prepare('SELECT * FROM destinations WHERE id = :id LIMIT 1');
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $destination = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$destination) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Destination not found']);
        exit;
    }

    // === Images from JSON column (safe handling) ===
    $imagesFromColumn = [];
    if (isset($destination['images']) && $destination['images'] !== null && trim($destination['images']) !== '') {
        $decoded = json_decode($destination['images'], true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $imagesFromColumn = array_values($decoded);
        } else {
            // fallback: try comma-separated list; if that yields nothing, keep empty array
            $parts = array_filter(array_map('trim', explode(',', $destination['images'])));
            if (!empty($parts)) {
                $imagesFromColumn = array_values($parts);
            } else {
                $imagesFromColumn = [];
            }
        }
    } else {
        $imagesFromColumn = [];
    }

    // === Images from destination_images table ===
    // destination_images uses column `image_url` in schema; select it as filename for compatibility
    $stmtImg = $db->prepare('SELECT image_url AS filename, is_primary FROM destination_images WHERE destination_id = :id ORDER BY is_primary DESC, id ASC');
    $stmtImg->bindParam(':id', $id, PDO::PARAM_INT);
    $stmtImg->execute();
    $relImages = $stmtImg->fetchAll(PDO::FETCH_ASSOC);

    $imagesFromRel = [];
    $primaryFromRel = null;
    if (is_array($relImages) && count($relImages) > 0) {
        foreach ($relImages as $r) {
            if (empty($r['filename']))
                continue;
            $imagesFromRel[] = $r['filename'];
            if (!is_null($r['is_primary']) && (int) $r['is_primary'] === 1 && $primaryFromRel === null) {
                $primaryFromRel = $r['filename'];
            }
        }
    } else {
        $imagesFromRel = [];
    }

    // Use destination_images table if it has images, otherwise use JSON column
    // This prevents duplicates from merging both sources
    if (!empty($imagesFromRel)) {
        $merged = $imagesFromRel;
    } else {
        $merged = $imagesFromColumn;
    }

    // Determine primary image: prefer primary from destination_images, else first from relational, else first from JSON column
    $primaryImage = null;
    if (!empty($primaryFromRel)) {
        $primaryImage = $primaryFromRel;
    } elseif (!empty($imagesFromRel)) {
        $primaryImage = $imagesFromRel[0];
    } elseif (!empty($imagesFromColumn)) {
        $primaryImage = $imagesFromColumn[0];
    } else {
        // no images at all -> explicit null (do not return broken path)
        $primaryImage = null;
    }

    // Build response (include destination fields as needed)
    $response = [
        'success' => true,
        'data' => [
            'destination' => $destination,
            'images' => $merged,            // always an array (not null)
            'primary_image' => $primaryImage // null if none
        ]
    ];

    echo json_encode($response);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error', 'error' => $e->getMessage()]);
    exit;
}
