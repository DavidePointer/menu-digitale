<?php
require_once '../config.php';

header('Content-Type: application/json');

try {
    $pdo = getDBConnection();
    $stmt = $pdo->query('SELECT category_id, name FROM categories ORDER BY name');
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($categories);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} 