<?php
// Carica la configurazione e le utilities
require_once '../config.php';
require_once 'auth_check.php';

// Configura CORS
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Per le richieste OPTIONS, termina qui
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Verifica l'autenticazione (funzione importata da auth_check.php)
if (!isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non autorizzato']);
    exit;
}

// Verifica che sia una richiesta POST
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Metodo non consentito']);
    exit;
}

// Verifica che siano stati forniti i parametri necessari
if (!isset($_POST['category_id']) || empty($_POST['category_id']) || !isset($_POST['name']) || empty($_POST['name'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Parametri mancanti']);
    exit;
}

$categoryId = $_POST['category_id'];
$name = $_POST['name'];
$imageUpdated = false;
$newImageName = '';

// Connessione al database
try {
    $db = getDBConnection();
    
    // Ottieni l'immagine attuale
    $stmt = $db->prepare("SELECT image FROM categories WHERE category_id = :category_id");
    $stmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
    $stmt->execute();
    
    $currentCategory = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$currentCategory) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Categoria non trovata']);
        exit;
    }
    
    $currentImage = $currentCategory['image'];
    
    // Gestisci l'upload dell'immagine se presente
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../images/categories/';
        
        // Crea la directory se non esiste
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // Genera un nome univoco per l'immagine
        $imageExtension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $newImageName = uniqid() . '.' . $imageExtension;
        $uploadFile = $uploadDir . $newImageName;
        
        // Sposta il file caricato
        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
            $imageUpdated = true;
            
            // Elimina la vecchia immagine se esiste
            if ($currentImage && file_exists($uploadDir . $currentImage)) {
                unlink($uploadDir . $currentImage);
            }
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Errore nel caricamento dell\'immagine']);
            exit;
        }
    }
    
    // Aggiorna i dati della categoria nel database
    if ($imageUpdated) {
        $stmt = $db->prepare("UPDATE categories SET name = :name, image = :image WHERE category_id = :category_id");
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':image', $newImageName, PDO::PARAM_STR);
        $stmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
    } else {
        $stmt = $db->prepare("UPDATE categories SET name = :name WHERE category_id = :category_id");
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
    }
    
    $stmt->execute();
    
    // Restituisci la risposta
    echo json_encode(['success' => true, 'message' => 'Categoria aggiornata con successo']);
    
} catch(PDOException $e) {
    // Errore di database
    error_log("Database error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Errore del server', 'debug' => $e->getMessage()]);
} 