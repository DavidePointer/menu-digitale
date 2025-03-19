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
    if (!isset($_POST['name']) || empty($_POST['name'])) {
        logAuthMessage("Nome articolo mancante per aggiunta articolo");
        jsonResponse(false, 'Nome articolo richiesto');
    }

    if (!isset($_POST['category_id']) || empty($_POST['category_id'])) {
        logAuthMessage("Categoria mancante per aggiunta articolo");
        jsonResponse(false, 'Categoria richiesta');
    }

    if (!isset($_POST['price']) || !is_numeric($_POST['price']) || $_POST['price'] < 0) {
        logAuthMessage("Prezzo non valido per aggiunta articolo");
        jsonResponse(false, 'Prezzo non valido');
    }

    $imageUrl = '';  // Inizializziamo con stringa vuota invece di null

    // Gestione upload immagine se presente
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
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

        $imageUrl = 'images/articles/' . $fileName;
    }

    // Inserimento nel database
    $pdo = getDBConnection();
    $stmt = $pdo->prepare('INSERT INTO articles (name, category_id, description, price, image_url) VALUES (?, ?, ?, ?, ?)');
    
    if (!$stmt->execute([
        $_POST['name'],
        $_POST['category_id'],
        $_POST['description'] ?? '',
        $_POST['price'],
        $imageUrl
    ])) {
        // Rimuovi il file se l'inserimento fallisce e se esiste un'immagine
        if ($imageUrl) {
            @unlink($uploadFile);
        }
        $dbError = $stmt->errorInfo();
        logAuthMessage("Errore nell'inserimento dell'articolo: " . $dbError[2]);
        jsonResponse(false, 'Errore nell\'inserimento dell\'articolo');
    }

    $articleId = $pdo->lastInsertId();
    logAuthMessage("Articolo ID $articleId aggiunto con successo");
    jsonResponse(true, 'Articolo aggiunto con successo', ['article_id' => $articleId]);

} catch (Exception $e) {
    logAuthMessage("Errore in add_article.php: " . $e->getMessage());
    jsonResponse(false, 'Errore del server: ' . $e->getMessage());
} 