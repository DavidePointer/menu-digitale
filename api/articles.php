<?php
header('Content-Type: application/json');
require_once '../config.php';

try {
    $category_url_name = isset($_GET['category']) ? $_GET['category'] : null;
    
    if (!$category_url_name) {
        throw new Exception('Parametro category richiesto');
    }
    
    $pdo = getDBConnection();
    
    // Prima trova la categoria in base all'url_name
    $categoryName = str_replace('_', ' ', $category_url_name);
    $stmt = $pdo->prepare("SELECT category_id FROM categories WHERE LOWER(name) = LOWER(?)");
    $stmt->execute([$categoryName]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$category) {
        throw new Exception('Categoria non trovata');
    }
    
    // Ottieni gli articoli della categoria
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE category_id = ?");
    $stmt->execute([$category['category_id']]);
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Aggiungi informazioni sulla categoria
    foreach ($articles as &$article) {
        $article['category_name'] = $categoryName;
        $article['category_url_name'] = $category_url_name;
    }
    
    echo json_encode($articles);
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?> 