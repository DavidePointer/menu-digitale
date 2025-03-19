<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once 'config.php';
require_once 'auth.php';

// Funzione per inviare risposte JSON
function jsonResponse($success, $message, $data = null, $status = 200) {
    http_response_code($status);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Verifica autenticazione
requireAuth();

// Verifica che sia una richiesta POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Metodo non consentito', null, 405);
}

// Verifica che sia stato caricato un file
if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
    jsonResponse(false, 'Nessun file CSV caricato o errore di caricamento', null, 400);
}

// Ottieni il file caricato
$csvFile = $_FILES['csv_file']['tmp_name'];
$fileType = mime_content_type($csvFile);

// Verifica che sia un file CSV
if ($fileType !== 'text/plain' && $fileType !== 'text/csv' && $fileType !== 'application/csv') {
    jsonResponse(false, 'Il file deve essere in formato CSV', null, 400);
}

// Apri il file CSV
$handle = fopen($csvFile, 'r');
if (!$handle) {
    jsonResponse(false, 'Impossibile aprire il file CSV', null, 500);
}

// Variabili per il conteggio
$totalCategories = 0;
$totalArticles = 0;
$newCategories = 0;
$newArticles = 0;
$updatedArticles = 0;

// Mappa delle categorie esistenti (nome => id)
$existingCategories = [];

// Ottieni tutte le categorie esistenti
$stmt = $conn->prepare("SELECT category_id, name FROM categories");
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $existingCategories[strtolower($row['name'])] = $row['category_id'];
}

// Mappa degli articoli esistenti (nome => array con dati)
$existingArticles = [];

// Ottieni tutti gli articoli esistenti
$stmt = $conn->prepare("SELECT article_id, name, description, price, category_id FROM articles");
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $existingArticles[strtolower($row['name'])] = $row;
}

// Prepara le query per l'inserimento/aggiornamento
$insertCategoryStmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
$insertArticleStmt = $conn->prepare("INSERT INTO articles (name, description, price, category_id) VALUES (?, ?, ?, ?)");
$updateArticleStmt = $conn->prepare("UPDATE articles SET description = ?, price = ?, category_id = ? WHERE article_id = ?");

// Leggi la prima riga (intestazioni)
$headers = fgetcsv($handle);
if (!$headers) {
    fclose($handle);
    jsonResponse(false, 'Il file CSV è vuoto o malformato', null, 400);
}

// Verifica che ci siano almeno 4 colonne
if (count($headers) < 4) {
    fclose($handle);
    jsonResponse(false, 'Il file CSV deve contenere almeno 4 colonne: Nome Categoria, Nome Articolo, Descrizione Articolo, Prezzo Articolo', null, 400);
}

// Inizia una transazione
$conn->begin_transaction();

try {
    // Elabora ogni riga del CSV
    while (($row = fgetcsv($handle)) !== false) {
        // Assicurati che ci siano almeno 4 colonne
        if (count($row) < 4) {
            continue; // Salta righe malformate
        }
        
        // Estrai i dati
        $categoryName = trim($row[0]);
        $articleName = trim($row[1]);
        $articleDescription = trim($row[2]);
        $articlePrice = trim($row[3]);
        
        // Controllo dati obbligatori
        if (empty($categoryName) || empty($articleName) || !is_numeric($articlePrice)) {
            continue; // Salta righe con dati mancanti o non validi
        }
        
        // Convalida e formatta il prezzo
        $articlePrice = number_format((float)$articlePrice, 2, '.', '');
        
        // Incrementa il contatore totale
        $totalCategories++;
        $totalArticles++;
        
        // Verifica se la categoria esiste già
        $categoryId = null;
        $categoryKey = strtolower($categoryName);
        
        if (isset($existingCategories[$categoryKey])) {
            $categoryId = $existingCategories[$categoryKey];
        } else {
            // Crea nuova categoria
            $insertCategoryStmt->bind_param('s', $categoryName);
            $insertCategoryStmt->execute();
            $categoryId = $conn->insert_id;
            
            // Aggiorna la mappa delle categorie
            $existingCategories[$categoryKey] = $categoryId;
            $newCategories++;
        }
        
        // Verifica se l'articolo esiste già
        $articleKey = strtolower($articleName);
        
        if (isset($existingArticles[$articleKey])) {
            // Aggiorna l'articolo esistente
            $articleId = $existingArticles[$articleKey]['article_id'];
            $updateArticleStmt->bind_param('sdii', $articleDescription, $articlePrice, $categoryId, $articleId);
            $updateArticleStmt->execute();
            $updatedArticles++;
        } else {
            // Crea nuovo articolo
            $insertArticleStmt->bind_param('ssdi', $articleName, $articleDescription, $articlePrice, $categoryId);
            $insertArticleStmt->execute();
            $newArticles++;
            
            // Aggiorna la mappa degli articoli
            $articleId = $conn->insert_id;
            $existingArticles[$articleKey] = [
                'article_id' => $articleId,
                'name' => $articleName,
                'description' => $articleDescription,
                'price' => $articlePrice,
                'category_id' => $categoryId
            ];
        }
    }
    
    // Commit della transazione
    $conn->commit();
    
    // Chiudi il file
    fclose($handle);
    
    // Invia risposta di successo
    jsonResponse(true, 'Importazione completata con successo', [
        'categories' => $newCategories,
        'articles' => $newArticles + $updatedArticles,
        'new_articles' => $newArticles,
        'updated_articles' => $updatedArticles
    ]);
    
} catch (Exception $e) {
    // Rollback in caso di errore
    $conn->rollback();
    fclose($handle);
    jsonResponse(false, 'Errore durante l\'importazione: ' . $e->getMessage(), null, 500);
} 