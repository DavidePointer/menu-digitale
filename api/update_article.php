<?php
// api/update_article.php - Aggiorna un articolo esistente

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
    logAuthMessage("Tentativo di aggiornare un articolo senza autenticazione");
    jsonResponse(false, 'Utente non autenticato');
}

// Verifica che il metodo sia POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    logAuthMessage("Metodo non consentito per aggiornamento articolo: " . $_SERVER['REQUEST_METHOD']);
    jsonResponse(false, 'Metodo non consentito');
}

try {
    // Log per debug
    logAuthMessage("Richiesta update_article.php ricevuta");

    // Validazione input
    if (!isset($_POST['article_id']) || empty($_POST['article_id'])) {
        logAuthMessage("ID articolo mancante per aggiornamento");
        jsonResponse(false, 'ID articolo richiesto');
    }

    if (!isset($_POST['name']) || empty($_POST['name'])) {
        logAuthMessage("Nome articolo mancante per aggiornamento");
        jsonResponse(false, 'Nome articolo richiesto');
    }

    if (!isset($_POST['category_id']) || empty($_POST['category_id'])) {
        logAuthMessage("Categoria mancante per aggiornamento articolo");
        jsonResponse(false, 'Categoria richiesta');
    }

    if (!isset($_POST['price']) || $_POST['price'] === '' || floatval($_POST['price']) <= 0) {
        logAuthMessage("Prezzo non valido per aggiornamento articolo");
        jsonResponse(false, 'Prezzo valido richiesto');
    }

    $articleId = (int)$_POST['article_id'];
    $categoryId = (int)$_POST['category_id'];
    $name = trim($_POST['name']);
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $price = floatval($_POST['price']);

    // Connessione al database
    $pdo = getDBConnection();

    // Verifica se l'articolo esiste
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE article_id = ?");
    $stmt->execute([$articleId]);
    $article = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$article) {
        logAuthMessage("Tentativo di aggiornare un articolo inesistente: " . $articleId);
        jsonResponse(false, 'Articolo non trovato');
    }

    // Prepara query di aggiornamento
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        // Se è stata caricata una nuova immagine
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

        // Elimina la vecchia immagine se esiste
        if ($article['image_url'] && file_exists($_SERVER['DOCUMENT_ROOT'] . '/menu_digitale/' . $article['image_url'])) {
            unlink($_SERVER['DOCUMENT_ROOT'] . '/menu_digitale/' . $article['image_url']);
        }

        $imageUrl = 'images/articles/' . $fileName;
        
        // Aggiorna tutti i campi inclusa l'immagine
        $stmt = $pdo->prepare("UPDATE articles SET category_id = ?, name = ?, description = ?, price = ?, image_url = ? WHERE article_id = ?");
        $stmt->execute([$categoryId, $name, $description, $price, $imageUrl, $articleId]);
    } else {
        // Aggiorna solo i campi di testo
        $stmt = $pdo->prepare("UPDATE articles SET category_id = ?, name = ?, description = ?, price = ? WHERE article_id = ?");
        $stmt->execute([$categoryId, $name, $description, $price, $articleId]);
    }

    if ($stmt->rowCount() === 0) {
        // Nessuna modifica effettuata (potrebbe essere perché i valori sono gli stessi)
        logAuthMessage("Nessuna modifica effettuata all'articolo: " . $articleId);
        jsonResponse(true, 'Nessuna modifica necessaria');
    }

    logAuthMessage("Articolo ID $articleId aggiornato con successo");
    jsonResponse(true, 'Articolo aggiornato con successo');

} catch (Exception $e) {
    logAuthMessage("Errore in update_article.php: " . $e->getMessage());
    jsonResponse(false, 'Errore del server: ' . $e->getMessage());
} 