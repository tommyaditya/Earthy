<?php
require __DIR__ . '/../config/database.php';
try {
    $id = $argv[1] ?? 11;
    $db = (new Database())->getConnection();
    $stmt = $db->prepare('SELECT * FROM destinations WHERE id = :id LIMIT 1');
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $d = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "DESTINATION:\n";
    var_dump($d);
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
