<?php
header('Content-Type: application/json');
require_once 'config.php';

try {
    $category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
    
    $query = "SELECT * FROM items WHERE category_id = ? ORDER BY name";
    $stmt = $conn->prepare($query);
    $stmt->execute([$category_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($items);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?> 