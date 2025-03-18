<?php
// api/delete_category.php - Elimina una categoria

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
    logAuthMessage("Tentativo di eliminare una categoria senza autenticazione");
    jsonResponse(false, 'Utente non autenticato');
}

// Verifica che il metodo sia POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    logAuthMessage("Metodo non consentito per eliminazione categoria: " . $_SERVER['REQUEST_METHOD']);
    jsonResponse(false, 'Metodo non consentito');
}

try {
    // Recupera i dati della richiesta
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Verifica che l'ID categoria sia stato fornito
    if (!isset($input['category_id']) || empty($input['category_id'])) {
        logAuthMessage("ID categoria mancante per eliminazione");
        jsonResponse(false, 'ID categoria richiesto');
    }
    
    $categoryId = (int)$input['category_id'];
    
    // Connessione al database
    $pdo = getDBConnection();
    
    // Inizia la transazione
    $pdo->beginTransaction();
    
    // Prima ottieni i dettagli della categoria per poter eliminare l'immagine
    $stmt = $pdo->prepare("SELECT image_url FROM categories WHERE category_id = ?");
    $stmt->execute([$categoryId]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$category) {
        logAuthMessage("Tentativo di eliminare una categoria inesistente: " . $categoryId);
        jsonResponse(false, 'Categoria non trovata');
    }
    
    // Elimina gli articoli associati alla categoria
    $stmt = $pdo->prepare("DELETE FROM articles WHERE category_id = ?");
    $stmt->execute([$categoryId]);
    $articlesDeleted = $stmt->rowCount();
    
    // Elimina la categoria
    $stmt = $pdo->prepare("DELETE FROM categories WHERE category_id = ?");
    $stmt->execute([$categoryId]);
    $categoryDeleted = $stmt->rowCount();
    
    if ($categoryDeleted === 0) {
        $pdo->rollBack();
        logAuthMessage("Errore nell'eliminazione della categoria: " . $categoryId);
        jsonResponse(false, 'Impossibile eliminare la categoria');
    }
    
    // Commit della transazione
    $pdo->commit();
    
    // Rimuovi il file dell'immagine se esiste
    if ($category['image_url'] && file_exists($_SERVER['DOCUMENT_ROOT'] . '/menu_digitale/' . $category['image_url'])) {
        unlink($_SERVER['DOCUMENT_ROOT'] . '/menu_digitale/' . $category['image_url']);
    }
    
    logAuthMessage("Categoria eliminata con successo: " . $categoryId . ", articoli eliminati: " . $articlesDeleted);
    jsonResponse(true, 'Categoria eliminata con successo');
    
} catch (Exception $e) {
    // In caso di errore, rollback della transazione
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    logAuthMessage("Errore nell'eliminazione della categoria: " . $e->getMessage());
    jsonResponse(false, 'Errore del server: ' . $e->getMessage());
} 