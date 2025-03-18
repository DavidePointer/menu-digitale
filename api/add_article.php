<?php
// api/add_article.php - Aggiunge un nuovo articolo

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
    logAuthMessage("Tentativo di aggiungere un articolo senza autenticazione");
    jsonResponse(false, 'Utente non autenticato');
}

// Verifica che il metodo sia POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    logAuthMessage("Metodo non consentito per aggiunta articolo: " . $_SERVER['REQUEST_METHOD']);
    jsonResponse(false, 'Metodo non consentito');
}

try {
    // Log per debug
    logAuthMessage("Richiesta add_article.php ricevuta");

    // Validazione input
    $requiredFields = ['category_id', 'name', 'description', 'price'];
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            logAuthMessage("Campo $field mancante per aggiunta articolo");
            jsonResponse(false, "Campo $field richiesto");
        }
    }

    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        logAuthMessage("Immagine mancante per aggiunta articolo. Errore: " . (isset($_FILES['image']) ? $_FILES['image']['error'] : 'File non inviato'));
        jsonResponse(false, 'Immagine richiesta');
    }

    // Gestione upload immagine
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/menu_digitale/images/articles/';
    
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

    // Inserimento nel database
    $pdo = getDBConnection();
    $stmt = $pdo->prepare('INSERT INTO articles (category_id, name, description, price, image_url) VALUES (?, ?, ?, ?, ?)');
    $imageUrl = 'images/articles/' . $fileName;
    
    if (!$stmt->execute([
        $_POST['category_id'],
        $_POST['name'],
        $_POST['description'],
        $_POST['price'],
        $imageUrl
    ])) {
        // Rimuovi il file se l'inserimento fallisce
        unlink($uploadFile);
        $dbError = $stmt->errorInfo();
        logAuthMessage("Errore nell'inserimento dell'articolo: " . $dbError[2]);
        jsonResponse(false, 'Errore nell\'inserimento dell\'articolo');
    }

    $articleId = $pdo->lastInsertId();
    logAuthMessage("Articolo ID $articleId aggiunto con successo");
    jsonResponse(true, 'Articolo aggiunto con successo');

} catch (Exception $e) {
    logAuthMessage("Errore in add_article.php: " . $e->getMessage());
    jsonResponse(false, 'Errore del server: ' . $e->getMessage());
} 