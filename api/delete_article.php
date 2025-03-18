<?php
// api/delete_article.php - Elimina un articolo

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
    logAuthMessage("Tentativo di eliminare un articolo senza autenticazione");
    jsonResponse(false, 'Utente non autenticato');
}

// Verifica che il metodo sia POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    logAuthMessage("Metodo non consentito per eliminazione articolo: " . $_SERVER['REQUEST_METHOD']);
    jsonResponse(false, 'Metodo non consentito');
}

try {
    // Recupera i dati della richiesta
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Verifica che l'ID articolo sia stato fornito
    if (!isset($input['article_id']) || empty($input['article_id'])) {
        logAuthMessage("ID articolo mancante per eliminazione");
        jsonResponse(false, 'ID articolo richiesto');
    }
    
    $articleId = (int)$input['article_id'];
    
    // Connessione al database
    $pdo = getDBConnection();
    
    // Prima ottieni i dettagli dell'articolo per poter eliminare l'immagine
    $stmt = $pdo->prepare("SELECT image_url FROM articles WHERE article_id = ?");
    $stmt->execute([$articleId]);
    $article = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$article) {
        logAuthMessage("Tentativo di eliminare un articolo inesistente: " . $articleId);
        jsonResponse(false, 'Articolo non trovato');
    }
    
    // Elimina l'articolo
    $stmt = $pdo->prepare("DELETE FROM articles WHERE article_id = ?");
    $stmt->execute([$articleId]);
    $deleted = $stmt->rowCount();
    
    if ($deleted === 0) {
        logAuthMessage("Errore nell'eliminazione dell'articolo: " . $articleId);
        jsonResponse(false, 'Impossibile eliminare l\'articolo');
    }
    
    // Rimuovi il file dell'immagine se esiste
    if ($article['image_url'] && file_exists($_SERVER['DOCUMENT_ROOT'] . '/menu_digitale/' . $article['image_url'])) {
        unlink($_SERVER['DOCUMENT_ROOT'] . '/menu_digitale/' . $article['image_url']);
    }
    
    logAuthMessage("Articolo eliminato con successo: " . $articleId);
    jsonResponse(true, 'Articolo eliminato con successo');
    
} catch (Exception $e) {
    logAuthMessage("Errore nell'eliminazione dell'articolo: " . $e->getMessage());
    jsonResponse(false, 'Errore del server: ' . $e->getMessage());
} 