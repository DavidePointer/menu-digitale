<?php
// api/bulk_edit_articles.php - Gestisce la modifica in massa degli articoli

// Includi i file necessari
require_once '../config.php';
require_once 'auth.php';

// Headers per CORS e JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Gestisci le richieste OPTIONS per il preflight CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Verifica autenticazione
if (!isAuthenticated()) {
    logAuthMessage("Tentativo di modifica in massa senza autenticazione");
    jsonResponse(false, 'Utente non autenticato');
}

// Verifica che il metodo sia POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    logAuthMessage("Metodo non consentito per modifica in massa: " . $_SERVER['REQUEST_METHOD']);
    jsonResponse(false, 'Metodo non consentito');
}

try {
    // Ottieni i dati JSON inviati
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        logAuthMessage("Dati JSON non validi per modifica in massa");
        jsonResponse(false, 'Dati non validi');
    }
    
    // Verifica che siano presenti tutti i campi necessari
    if (!isset($data['action']) || empty($data['action'])) {
        logAuthMessage("Azione mancante per modifica in massa");
        jsonResponse(false, 'Azione richiesta');
    }
    
    if (!isset($data['articles']) || !is_array($data['articles']) || empty($data['articles'])) {
        logAuthMessage("Nessun articolo specificato per modifica in massa");
        jsonResponse(false, 'Nessun articolo selezionato');
    }
    
    // Connessione al database
    $pdo = getDBConnection();
    $articleIds = $data['articles'];
    $action = $data['action'];
    
    // Preparazione query in base all'azione
    switch ($action) {
        case 'delete':
            // Eliminazione degli articoli selezionati
            $stmt = $pdo->prepare("DELETE FROM articles WHERE article_id IN (" . implode(',', array_fill(0, count($articleIds), '?')) . ")");
            $stmt->execute($articleIds);
            break;
            
        case 'change_category':
            if (!isset($data['target_category_id']) || empty($data['target_category_id'])) {
                jsonResponse(false, 'Categoria destinazione richiesta');
            }
            
            $categoryId = intval($data['target_category_id']);
            
            // Verifica che la categoria esista
            $checkStmt = $pdo->prepare("SELECT category_id FROM categories WHERE category_id = ?");
            $checkStmt->execute([$categoryId]);
            if ($checkStmt->rowCount() === 0) {
                jsonResponse(false, 'Categoria non valida');
            }
            
            $stmt = $pdo->prepare("UPDATE articles SET category_id = ? WHERE article_id IN (" . implode(',', array_fill(0, count($articleIds), '?')) . ")");
            
            $params = array_merge([$categoryId], $articleIds);
            $stmt->execute($params);
            break;
            
        default:
            jsonResponse(false, 'Azione non valida');
    }
    
    $count = $stmt->rowCount();
    logAuthMessage("Modifica in massa completata: $count articoli " . ($action === 'delete' ? 'eliminati' : 'aggiornati'));
    
    if ($action === 'delete') {
        jsonResponse(true, "Eliminati $count articoli con successo");
    } else {
        jsonResponse(true, "Aggiornati $count articoli con successo");
    }
    
} catch (Exception $e) {
    logAuthMessage("Errore in bulk_edit_articles.php: " . $e->getMessage());
    jsonResponse(false, 'Errore del server: ' . $e->getMessage());
} 