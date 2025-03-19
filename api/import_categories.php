<?php
// api/import_categories.php - Importa categorie da file Excel/CSV

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
    logAuthMessage("Tentativo di importare categorie senza autenticazione");
    jsonResponse(false, 'Utente non autenticato');
}

// Verifica che il metodo sia POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    logAuthMessage("Metodo non consentito per importazione categorie: " . $_SERVER['REQUEST_METHOD']);
    jsonResponse(false, 'Metodo non consentito');
}

try {
    // Verifica se il file è stato caricato
    if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
        logAuthMessage("File non caricato per importazione categorie. Errore: " . (isset($_FILES['excel_file']) ? $_FILES['excel_file']['error'] : 'File non inviato'));
        jsonResponse(false, 'File non valido o non caricato');
    }

    // Ottieni il percorso temporaneo del file
    $filePath = $_FILES['excel_file']['tmp_name'];
    $fileName = $_FILES['excel_file']['name'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    // Verifica il formato del file
    if (!in_array($fileExt, ['csv', 'xls', 'xlsx'])) {
        logAuthMessage("Formato file non supportato per importazione categorie: " . $fileExt);
        jsonResponse(false, 'Formato file non supportato. Utilizzare CSV, XLS o XLSX');
    }

    // Connessione al database
    $pdo = getDBConnection();

    // Gestisci diversi formati di file
    if ($fileExt === 'csv') {
        // Elabora file CSV
        $categoriesData = processCsvCategories($filePath);
    } else {
        // Per XLS/XLSX servirebbe PHPExcel o simili, per ora utilizziamo un messaggio
        jsonResponse(false, 'Al momento solo il formato CSV è supportato completamente. XLS e XLSX saranno implementati in futuro.');
    }

    // Importa le categorie
    $importedCount = importCategories($pdo, $categoriesData);

    logAuthMessage("Importazione categorie completata: $importedCount categorie importate");
    jsonResponse(true, "Importazione categorie completata", ['imported' => $importedCount]);

} catch (Exception $e) {
    logAuthMessage("Errore in import_categories.php: " . $e->getMessage());
    jsonResponse(false, 'Errore del server: ' . $e->getMessage());
}

/**
 * Elabora il file CSV per estrarre i dati delle categorie
 */
function processCsvCategories($filePath) {
    $categories = [];
    
    // Apri il file CSV
    if (($handle = fopen($filePath, "r")) !== FALSE) {
        // Leggi la prima riga come intestazioni
        $headers = fgetcsv($handle, 1000, ",");
        
        // Converti intestazioni in minuscolo per confronto case-insensitive
        $lowerHeaders = array_map('strtolower', $headers);
        
        // Trova gli indici delle colonne necessarie
        $nameIndex = array_search('nome', $lowerHeaders);
        $imageIndex = array_search('immagine', $lowerHeaders);
        
        // Verifica che la colonna nome sia presente
        if ($nameIndex === false) {
            fclose($handle);
            throw new Exception("La colonna 'Nome' non è presente nel file CSV");
        }
        
        // Leggi le righe di dati
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            // Verifica che ci sia almeno un nome di categoria
            if (!isset($data[$nameIndex]) || empty(trim($data[$nameIndex]))) {
                continue; // Salta righe senza nome
            }
            
            $category = [
                'name' => trim($data[$nameIndex]),
                'image_url' => ($imageIndex !== false && isset($data[$imageIndex])) ? trim($data[$imageIndex]) : null
            ];
            
            $categories[] = $category;
        }
        
        fclose($handle);
    } else {
        throw new Exception("Impossibile aprire il file CSV");
    }
    
    return $categories;
}

/**
 * Importa le categorie nel database
 */
function importCategories($pdo, $categories) {
    if (empty($categories)) {
        return 0;
    }
    
    $importCount = 0;
    
    // Verifica se esiste la colonna image_url
    $stmt = $pdo->query("SHOW COLUMNS FROM categories LIKE 'image_url'");
    $hasImageUrl = $stmt->rowCount() > 0;
    
    // Prepara query in base alla struttura della tabella
    if (!$hasImageUrl) {
        $stmt = $pdo->query("SHOW COLUMNS FROM categories LIKE 'image'");
        if ($stmt->rowCount() > 0) {
            $insertStmt = $pdo->prepare('INSERT INTO categories (name, image) VALUES (?, ?)');
        } else {
            // Aggiungi image_url se non esiste
            $pdo->exec("ALTER TABLE categories ADD COLUMN image_url VARCHAR(255)");
            $insertStmt = $pdo->prepare('INSERT INTO categories (name, image_url) VALUES (?, ?)');
        }
    } else {
        $insertStmt = $pdo->prepare('INSERT INTO categories (name, image_url) VALUES (?, ?)');
    }
    
    // Esegui le inserzioni
    foreach ($categories as $category) {
        try {
            $insertStmt->execute([
                $category['name'],
                $category['image_url']
            ]);
            $importCount++;
        } catch (PDOException $e) {
            // Ignora errori di duplicazione (se la categoria esiste già)
            if ($e->getCode() !== '23000') {
                throw $e;
            }
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