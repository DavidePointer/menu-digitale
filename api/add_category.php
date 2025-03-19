<?php
// api/add_category.php - Aggiunge una nuova categoria

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
    logAuthMessage("Tentativo di aggiungere una categoria senza autenticazione");
    jsonResponse(false, 'Utente non autenticato');
}

// Verifica che il metodo sia POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    logAuthMessage("Metodo non consentito per aggiunta categoria: " . $_SERVER['REQUEST_METHOD']);
    jsonResponse(false, 'Metodo non consentito');
}

try {
    // Log per debug
    logAuthMessage("Richiesta add_category.php ricevuta");

    // Validazione input
    if (!isset($_POST['name']) || empty($_POST['name'])) {
        logAuthMessage("Nome categoria mancante per aggiunta categoria");
        jsonResponse(false, 'Nome categoria richiesto');
    }

    $imageUrl = '';  // Inizializziamo con stringa vuota invece di null

    // Gestione upload immagine se presente
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        // Gestione upload immagine
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/menu_digitale/images/categories/';
        
        if (!file_exists($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                logAuthMessage("Errore nella creazione della directory: " . $uploadDir);
                jsonResponse(false, 'Impossibile creare la directory per le immagini');
            }
        }

        // Verifica permessi directory
        if (!is_writable($uploadDir)) {
            logAuthMessage("Directory non scrivibile: " . $uploadDir);
            jsonResponse(false, 'Directory non scrivibile');
        }

        $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
        $uploadFile = $uploadDir . $fileName;

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
            logAuthMessage("Errore nel caricamento dell'immagine: " . print_r(error_get_last(), true));
            jsonResponse(false, 'Errore nel caricamento dell\'immagine');
        }

        $imageUrl = 'images/categories/' . $fileName;
    }

    // Inserimento nel database
    $pdo = getDBConnection();
    $stmt = $pdo->prepare('INSERT INTO categories (name, image_url) VALUES (?, ?)');
    
    if (!$stmt->execute([$_POST['name'], $imageUrl])) {
        // Rimuovi il file se l'inserimento fallisce e se esiste un'immagine
        if ($imageUrl) {
            @unlink($uploadFile);
        }
        $dbError = $stmt->errorInfo();
        logAuthMessage("Errore nell'inserimento della categoria: " . $dbError[2]);
        jsonResponse(false, 'Errore nell\'inserimento della categoria');
    }

    $categoryId = $pdo->lastInsertId();
    logAuthMessage("Categoria ID $categoryId aggiunta con successo");
    jsonResponse(true, 'Categoria aggiunta con successo', ['category_id' => $categoryId]);

} catch (Exception $e) {
    logAuthMessage("Errore in add_category.php: " . $e->getMessage());
    jsonResponse(false, 'Errore del server: ' . $e->getMessage());
} 