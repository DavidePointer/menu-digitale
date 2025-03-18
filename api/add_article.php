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

// Carica configurazione
require_once '../config.php';

// Configurazione dei log
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/debug.log');
if (!file_exists(__DIR__ . '/../logs')) {
    mkdir(__DIR__ . '/../logs', 0777, true);
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Metodo non consentito');
    }

    // Log per debug
    error_log("Richiesta add_article.php ricevuta");
    error_log("POST data: " . print_r($_POST, true));
    error_log("FILES data: " . print_r($_FILES, true));

    // Validazione input
    $requiredFields = ['category_id', 'name', 'description', 'price'];
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("Campo $field richiesto");
        }
    }

    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Immagine richiesta. Errore: ' . (isset($_FILES['image']) ? $_FILES['image']['error'] : 'File non inviato'));
    }

    // Gestione upload immagine
    $uploadDir = __DIR__ . '/../images/articles/';
    error_log("Directory upload: " . $uploadDir);
    
    if (!file_exists($uploadDir)) {
        error_log("Creazione directory: " . $uploadDir);
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
        throw new Exception('Errore nel caricamento dell\'immagine');
    }

    error_log("File caricato con successo in: " . $uploadFile);

    // Inserimento nel database
    $pdo = getDBConnection();
    $stmt = $pdo->prepare('INSERT INTO articles (category_id, name, description, price, image_url) VALUES (?, ?, ?, ?, ?)');
    $imageUrl = 'images/articles/' . $fileName;
    
    error_log("Inserimento nel database: categoria=" . $_POST['category_id'] . ", nome=" . $_POST['name'] . ", immagine=" . $imageUrl);
    
    if (!$stmt->execute([
        $_POST['category_id'],
        $_POST['name'],
        $_POST['description'],
        $_POST['price'],
        $imageUrl
    ])) {
        unlink($uploadFile); // Rimuovi il file se l'inserimento fallisce
        $dbError = $stmt->errorInfo();
        throw new Exception('Errore nell\'inserimento dell\'articolo: ' . $dbError[2]);
    }

    error_log("Articolo inserito con successo nel database");
    echo json_encode(['success' => true, 'message' => 'Articolo aggiunto con successo']);

} catch (Exception $e) {
    error_log("Errore in add_article.php: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 