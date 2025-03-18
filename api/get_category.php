<?php
// api/get_category.php - Ottiene una singola categoria dal database

require_once '../config.php';
require_once 'auth.php';

// Imposta gli header CORS e JSON
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Gestisci il preflight CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Verifica l'autenticazione
if (!isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non autorizzato']);
    exit;
}

// Ottieni l'ID della categoria dalla richiesta
$categoryId = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;

// Verifica che l'ID categoria sia stato fornito
if (!$categoryId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID categoria richiesto']);
    exit;
}

try {
    // Connessione al database
    $db = getDBConnection();
    
    // Ottieni i dettagli della categoria
    $stmt = $db->prepare('SELECT * FROM categories WHERE category_id = ?');
    $stmt->execute([$categoryId]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$category) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Categoria non trovata']);
        exit;
    }
    
    // Conta quanti articoli sono associati a questa categoria
    $stmt = $db->prepare('SELECT COUNT(*) FROM articles WHERE category_id = ?');
    $stmt->execute([$categoryId]);
    $articleCount = $stmt->fetchColumn();
    
    // Aggiungi il numero di articoli al risultato
    $category['article_count'] = $articleCount;
    
    // Assicurati che image_url abbia il path completo
    if (!empty($category['image_url']) && strpos($category['image_url'], 'http') !== 0) {
        // Se il percorso è relativo, aggiungi il path base
        $category['image_url'] = "/menu_digitale/" . ltrim($category['image_url'], '/');
    }
    
    // Restituisci i dati della categoria
    echo json_encode($category);
    
} catch (Exception $e) {
    error_log('Errore get_category.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Errore del server: ' . $e->getMessage()]);
} 