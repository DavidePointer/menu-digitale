<?php
// api/get_categories.php - Ottiene tutte le categorie dal database

require_once '../config.php';

// Headers per CORS e JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Gestisci le richieste OPTIONS per il preflight CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    $pdo = getDBConnection();
    
    // Ottieni tutte le informazioni per ogni categoria
    $stmt = $pdo->query('SELECT * FROM categories ORDER BY name');
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Aggiungi per ogni categoria il numero di articoli
    foreach ($categories as &$category) {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM articles WHERE category_id = ?');
        $stmt->execute([$category['category_id']]);
        $category['article_count'] = $stmt->fetchColumn();
    }
    
    echo json_encode($categories);
} catch (Exception $e) {
    // Log dell'errore
    error_log("Errore in get_categories.php: " . $e->getMessage());
    
    // Restituisci errore come JSON
    http_response_code(500);
    echo json_encode(['error' => 'Errore nel caricamento delle categorie: ' . $e->getMessage()]);
} 