<?php
header('Content-Type: application/json');
require_once '../config.php';

try {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT * FROM categories");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Aggiungi il campo url_name
    foreach ($categories as &$category) {
        $category['url_name'] = strtolower(str_replace(' ', '_', $category['name']));
    }
    
    echo json_encode($categories);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?> 