<?php
// api/get_articles.php - Ottiene gli articoli dal database

// Includi i file necessari
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
    // Connessione al database
    $pdo = getDBConnection();
    
    // Filtra per categoria se richiesto
    $categoryFilter = '';
    $params = [];
    
    if (isset($_GET['category_id']) && !empty($_GET['category_id'])) {
        $categoryFilter = 'WHERE a.category_id = ?';
        $params[] = $_GET['category_id'];
    }
    
    // Query per ottenere gli articoli
    $query = "SELECT a.* FROM articles a";
    if ($categoryFilter) {
        $query .= " $categoryFilter";
    }
    $query .= " ORDER BY a.category_id, a.name";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Ottieni le informazioni delle categorie
    $categoryInfo = [];
    $stmt = $pdo->prepare("SELECT category_id, name FROM categories");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($categories as $category) {
        $categoryInfo[$category['category_id']] = $category['name'];
    }
    
    // Aggiungi il nome della categoria a ciascun articolo
    foreach ($articles as &$article) {
        $categoryId = $article['category_id'];
        $article['category_name'] = isset($categoryInfo[$categoryId]) ? $categoryInfo[$categoryId] : 'Categoria Sconosciuta';
    }
    
    // Restituisci gli articoli come JSON
    echo json_encode($articles);
    
} catch (Exception $e) {
    // Log dell'errore
    error_log("Errore in get_articles.php: " . $e->getMessage());
    
    // Restituisci errore come JSON
    http_response_code(500);
    echo json_encode(['error' => 'Errore nel caricamento degli articoli: ' . $e->getMessage()]);
} 