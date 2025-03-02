<?php
header('Content-Type: application/json');
require_once 'config.php';

try {
    $query = "SELECT * FROM categories ORDER BY ordering";
    $stmt = $conn->query($query);
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($categories);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?> 