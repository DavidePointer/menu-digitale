<?php
// Richiedi autenticazione
require_once 'auth_check.php';

// Headers CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Se è una richiesta OPTIONS, termina qui
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Configurazione dei log
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/debug.log');
if (!file_exists(__DIR__ . '/../logs')) {
    mkdir(__DIR__ . '/../logs', 0777, true);
}

error_log("=== Inizio elaborazione add_category.php ===");

require_once '../config.php';

// Abilita la visualizzazione degli errori
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Debug del percorso corrente
error_log("Directory corrente: " . __DIR__);
error_log("Document root: " . $_SERVER['DOCUMENT_ROOT']);

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Metodo non consentito');
    }

    // Debug dei dati ricevuti
    error_log("POST data: " . print_r($_POST, true));
    error_log("FILES data: " . print_r($_FILES, true));

    if (!isset($_POST['name']) || empty($_POST['name'])) {
        throw new Exception('Nome categoria richiesto');
    }

    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        error_log("Errore nel file: " . print_r($_FILES['image'], true));
        throw new Exception('Immagine richiesta. Errore: ' . (isset($_FILES['image']) ? $_FILES['image']['error'] : 'File non inviato'));
    }

    // Gestione upload immagine
    $uploadDir = __DIR__ . '/../images/categories/';
    error_log("Directory upload: " . $uploadDir);
    
    if (!file_exists($uploadDir)) {
        error_log("Tentativo di creare la directory: " . $uploadDir);
        if (!mkdir($uploadDir, 0777, true)) {
            error_log("Errore nella creazione della directory");
            throw new Exception('Impossibile creare la directory per le immagini');
        }
    }

    // Verifica permessi directory
    if (!is_writable($uploadDir)) {
        error_log("Directory non scrivibile: " . $uploadDir);
        throw new Exception('Directory non scrivibile: ' . $uploadDir);
    }

    $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
    $uploadFile = $uploadDir . $fileName;

    error_log("Tentativo di upload del file in: " . $uploadFile);

    if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
        $uploadError = error_get_last();
        error_log("Errore upload: " . print_r($uploadError, true));
        throw new Exception('Errore nel caricamento dell\'immagine. Errore PHP: ' . ($uploadError ? $uploadError['message'] : 'Sconosciuto'));
    }

    error_log("File caricato con successo in: " . $uploadFile);

    // Inserimento nel database
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare('INSERT INTO categories (name, image_url) VALUES (?, ?)');
        $imageUrl = 'images/categories/' . $fileName;
        
        if (!$stmt->execute([$_POST['name'], $imageUrl])) {
            $dbError = $stmt->errorInfo();
            error_log("Errore database: " . print_r($dbError, true));
            unlink($uploadFile);
            throw new Exception('Errore nell\'inserimento della categoria: ' . $dbError[2]);
        }

        error_log("Categoria inserita con successo nel database");
        echo json_encode(['success' => true, 'message' => 'Categoria aggiunta con successo']);

    } catch (PDOException $e) {
        error_log("Errore PDO: " . $e->getMessage());
        if (file_exists($uploadFile)) {
            unlink($uploadFile);
        }
        throw new Exception('Errore database: ' . $e->getMessage());
    }

} catch (Exception $e) {
    error_log("Errore in add_category.php: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

error_log("=== Fine elaborazione add_category.php ==="); 