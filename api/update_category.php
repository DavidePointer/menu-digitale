<?php
// Carica la configurazione e le utilities
require_once '../config.php';
require_once 'auth_check.php';

// Debug: salva i dati ricevuti in un file di log
$debugData = [
    'method' => $_SERVER['REQUEST_METHOD'],
    'post' => $_POST,
    'files' => isset($_FILES) ? $_FILES : [],
    'time' => date('Y-m-d H:i:s')
];
file_put_contents('../debug_update_category.log', json_encode($debugData, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);

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

// La verifica dell'autenticazione è già inclusa in auth_check.php
// Non serve chiamare isAuthenticated() perché il controllo è già fatto quando si include auth_check.php

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
        // Verifica prima se la colonna image esiste nella tabella
        $checkColumn = $db->prepare("SHOW COLUMNS FROM categories LIKE 'image'");
        $checkColumn->execute();
        $columnExists = $checkColumn->rowCount() > 0;
        
        if ($columnExists) {
            $stmt = $db->prepare("UPDATE categories SET name = :name, image = :image WHERE category_id = :category_id");
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':image', $newImageName, PDO::PARAM_STR);
            $stmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
        } else {
            // Se la colonna image non esiste, aggiorna solo il nome
            $stmt = $db->prepare("UPDATE categories SET name = :name WHERE category_id = :category_id");
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
        }
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