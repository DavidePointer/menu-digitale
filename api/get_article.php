<?php
// api/get_article.php - Ottiene un singolo articolo dal database

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

// Ottieni l'ID dell'articolo dalla richiesta
$articleId = isset($_GET['article_id']) ? intval($_GET['article_id']) : 0;

// Verifica che l'ID articolo sia stato fornito
if (!$articleId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID articolo richiesto']);
    exit;
}

try {
    // Connessione al database
    $db = getDBConnection();
    
    // Ottieni i dettagli dell'articolo con il nome della categoria
    $stmt = $db->prepare("
        SELECT a.*, c.name as category_name 
        FROM articles a
        JOIN categories c ON a.category_id = c.category_id
        WHERE a.article_id = ?
    ");
    $stmt->execute([$articleId]);
    $article = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$article) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Articolo non trovato']);
        exit;
    }
    
    // Assicurati che image_url abbia il path completo
    if (!empty($article['image_url']) && strpos($article['image_url'], 'http') !== 0) {
        // Se il percorso è relativo, aggiungi il path base
        $article['image_url'] = "/menu_digitale/" . ltrim($article['image_url'], '/');
    }
    
    // Restituisci i dati dell'articolo
    echo json_encode($article);
    
} catch (Exception $e) {
    error_log('Errore get_article.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Errore del server: ' . $e->getMessage()]);
} 