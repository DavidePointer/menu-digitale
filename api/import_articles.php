<?php
// api/import_articles.php - Importa articoli da file Excel/CSV

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
    logAuthMessage("Tentativo di importare articoli senza autenticazione");
    jsonResponse(false, 'Utente non autenticato');
}

// Verifica che il metodo sia POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    logAuthMessage("Metodo non consentito per importazione articoli: " . $_SERVER['REQUEST_METHOD']);
    jsonResponse(false, 'Metodo non consentito');
}

try {
    // Verifica se il file è stato caricato
    if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
        logAuthMessage("File non caricato per importazione articoli. Errore: " . (isset($_FILES['excel_file']) ? $_FILES['excel_file']['error'] : 'File non inviato'));
        jsonResponse(false, 'File non valido o non caricato');
    }

    // Ottieni il percorso temporaneo del file
    $filePath = $_FILES['excel_file']['tmp_name'];
    $fileName = $_FILES['excel_file']['name'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    // Verifica il formato del file
    if (!in_array($fileExt, ['csv', 'xls', 'xlsx'])) {
        logAuthMessage("Formato file non supportato per importazione articoli: " . $fileExt);
        jsonResponse(false, 'Formato file non supportato. Utilizzare CSV, XLS o XLSX');
    }

    // Connessione al database
    $pdo = getDBConnection();

    // Ottieni le categorie esistenti
    $categories = [];
    $stmt = $pdo->query("SELECT category_id, name FROM categories");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $categories[strtolower($row['name'])] = $row['category_id'];
    }

    // Gestisci diversi formati di file
    if ($fileExt === 'csv') {
        // Elabora file CSV
        $articlesData = processCsvArticles($filePath, $categories);
    } else {
        // Per XLS/XLSX servirebbe PHPExcel o simili, per ora utilizziamo un messaggio
        jsonResponse(false, 'Al momento solo il formato CSV è supportato completamente. XLS e XLSX saranno implementati in futuro.');
    }

    // Importa gli articoli
    $importedCount = importArticles($pdo, $articlesData);

    logAuthMessage("Importazione articoli completata: $importedCount articoli importati");
    jsonResponse(true, "Importazione articoli completata", ['imported' => $importedCount]);

} catch (Exception $e) {
    logAuthMessage("Errore in import_articles.php: " . $e->getMessage());
    jsonResponse(false, 'Errore del server: ' . $e->getMessage());
}

/**
 * Elabora il file CSV per estrarre i dati degli articoli
 */
function processCsvArticles($filePath, $existingCategories) {
    $articles = [];
    
    // Apri il file CSV
    if (($handle = fopen($filePath, "r")) !== FALSE) {
        // Leggi la prima riga come intestazioni
        $headers = fgetcsv($handle, 1000, ",");
        
        // Converti intestazioni in minuscolo per confronto case-insensitive
        $lowerHeaders = array_map('strtolower', $headers);
        
        // Trova gli indici delle colonne necessarie
        $nameIndex = array_search('nome', $lowerHeaders);
        $categoryIndex = array_search('categoria', $lowerHeaders);
        $priceIndex = array_search('prezzo', $lowerHeaders);
        $descriptionIndex = array_search('descrizione', $lowerHeaders);
        $imageIndex = array_search('immagine', $lowerHeaders);
        
        // Verifica che le colonne essenziali siano presenti
        if ($nameIndex === false || $categoryIndex === false || $priceIndex === false) {
            fclose($handle);
            throw new Exception("Le colonne essenziali (Nome, Categoria, Prezzo) non sono tutte presenti nel file CSV");
        }
        
        // Leggi le righe di dati
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            // Verifica che ci siano i dati essenziali
            if (!isset($data[$nameIndex]) || empty(trim($data[$nameIndex])) ||
                !isset($data[$categoryIndex]) || empty(trim($data[$categoryIndex])) ||
                !isset($data[$priceIndex]) || !is_numeric(str_replace(',', '.', trim($data[$priceIndex])))) {
                continue; // Salta righe con dati essenziali mancanti o non validi
            }
            
            // Converti il nome della categoria in ID
            $categoryName = strtolower(trim($data[$categoryIndex]));
            $categoryId = isset($existingCategories[$categoryName]) ? $existingCategories[$categoryName] : null;
            
            // Salta articoli con categoria non trovata
            if ($categoryId === null) {
                continue;
            }
            
            // Formatta prezzo in modo corretto
            $price = str_replace(',', '.', trim($data[$priceIndex]));
            
            $article = [
                'name' => trim($data[$nameIndex]),
                'category_id' => $categoryId,
                'price' => floatval($price),
                'description' => ($descriptionIndex !== false && isset($data[$descriptionIndex])) ? trim($data[$descriptionIndex]) : '',
                'image_url' => ($imageIndex !== false && isset($data[$imageIndex])) ? trim($data[$imageIndex]) : null
            ];
            
            $articles[] = $article;
        }
        
        fclose($handle);
    } else {
        throw new Exception("Impossibile aprire il file CSV");
    }
    
    return $articles;
}

/**
 * Importa gli articoli nel database
 */
function importArticles($pdo, $articles) {
    if (empty($articles)) {
        return 0;
    }
    
    $importCount = 0;
    
    // Prepara la query di inserimento
    $insertStmt = $pdo->prepare('INSERT INTO articles (category_id, name, description, price, image_url) VALUES (?, ?, ?, ?, ?)');
    
    // Esegui le inserzioni
    foreach ($articles as $article) {
        try {
            $insertStmt->execute([
                $article['category_id'],
                $article['name'],
                $article['description'],
                $article['price'],
                $article['image_url']
            ]);
            $importCount++;
        } catch (PDOException $e) {
            // Logga l'errore ma continua con gli altri articoli
            logAuthMessage("Errore inserimento articolo: " . $e->getMessage());
        }
    }
    
    return $importCount;
}

/**
 * Invia una risposta JSON
 */
function jsonResponse($success, $message, $data = null) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
} 