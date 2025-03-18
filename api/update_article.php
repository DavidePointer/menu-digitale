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
file_put_contents('../debug_update_article.log', json_encode($debugData, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);

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
if (!isset($_POST['article_id']) || empty($_POST['article_id']) || 
    !isset($_POST['category_id']) || empty($_POST['category_id']) || 
    !isset($_POST['name']) || empty($_POST['name']) || 
    !isset($_POST['description']) || empty($_POST['description']) || 
    !isset($_POST['price']) || $_POST['price'] === '') {
    
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Parametri mancanti']);
    exit;
}

$articleId = $_POST['article_id'];
$categoryId = $_POST['category_id'];
$name = $_POST['name'];
$description = $_POST['description'];
$price = $_POST['price'];
$imageUpdated = false;
$newImageName = '';

// Connessione al database
try {
    $db = getDBConnection();
    
    // Prima di tutto, verifichiamo se la colonna 'image' esiste e se non esiste la aggiungiamo
    $checkColumn = $db->prepare("SHOW COLUMNS FROM articles LIKE 'image'");
    $checkColumn->execute();
    if ($checkColumn->rowCount() == 0) {
        // La colonna non esiste, quindi la aggiungiamo
        $addColumn = $db->prepare("ALTER TABLE articles ADD COLUMN image VARCHAR(255)");
        $addColumn->execute();
    }
    
    // Verifichiamo se le colonne name, description e price esistono e le aggiungiamo se necessario
    $checkNameColumn = $db->prepare("SHOW COLUMNS FROM articles LIKE 'name'");
    $checkNameColumn->execute();
    if ($checkNameColumn->rowCount() == 0) {
        $addNameColumn = $db->prepare("ALTER TABLE articles ADD COLUMN name VARCHAR(255) NOT NULL");
        $addNameColumn->execute();
    }
    
    $checkDescColumn = $db->prepare("SHOW COLUMNS FROM articles LIKE 'description'");
    $checkDescColumn->execute();
    if ($checkDescColumn->rowCount() == 0) {
        $addDescColumn = $db->prepare("ALTER TABLE articles ADD COLUMN description TEXT");
        $addDescColumn->execute();
    }
    
    $checkPriceColumn = $db->prepare("SHOW COLUMNS FROM articles LIKE 'price'");
    $checkPriceColumn->execute();
    if ($checkPriceColumn->rowCount() == 0) {
        $addPriceColumn = $db->prepare("ALTER TABLE articles ADD COLUMN price DECIMAL(10,2) NOT NULL");
        $addPriceColumn->execute();
    }
    
    // Ottieni l'immagine attuale
    $stmt = $db->prepare("SELECT image FROM articles WHERE article_id = :article_id");
    $stmt->bindParam(':article_id', $articleId, PDO::PARAM_INT);
    $stmt->execute();
    
    $currentArticle = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$currentArticle) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Articolo non trovato']);
        exit;
    }
    
    $currentImage = $currentArticle['image'];
    
    // Gestisci l'upload dell'immagine se presente
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../images/articles/';
        
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
    
    // Aggiorna i dati dell'articolo nel database
    if ($imageUpdated) {
        // Verifica prima se la colonna image esiste nella tabella
        $checkColumn = $db->prepare("SHOW COLUMNS FROM articles LIKE 'image'");
        $checkColumn->execute();
        $columnExists = $checkColumn->rowCount() > 0;
        
        if ($columnExists) {
            $stmt = $db->prepare("
                UPDATE articles 
                SET category_id = :category_id, name = :name, description = :description, 
                    price = :price, image = :image 
                WHERE article_id = :article_id
            ");
            $stmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':description', $description, PDO::PARAM_STR);
            $stmt->bindParam(':price', $price, PDO::PARAM_STR);
            $stmt->bindParam(':image', $newImageName, PDO::PARAM_STR);
            $stmt->bindParam(':article_id', $articleId, PDO::PARAM_INT);
        } else {
            // Se la colonna image non esiste, aggiorna solo gli altri campi
            $stmt = $db->prepare("
                UPDATE articles 
                SET category_id = :category_id, name = :name, description = :description, 
                    price = :price
                WHERE article_id = :article_id
            ");
            $stmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':description', $description, PDO::PARAM_STR);
            $stmt->bindParam(':price', $price, PDO::PARAM_STR);
            $stmt->bindParam(':article_id', $articleId, PDO::PARAM_INT);
        }
    } else {
        $stmt = $db->prepare("
            UPDATE articles 
            SET category_id = :category_id, name = :name, description = :description, 
                price = :price 
            WHERE article_id = :article_id
        ");
        $stmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->bindParam(':price', $price, PDO::PARAM_STR);
        $stmt->bindParam(':article_id', $articleId, PDO::PARAM_INT);
    }
    
    $stmt->execute();
    
    // Restituisci la risposta
    echo json_encode(['success' => true, 'message' => 'Articolo aggiornato con successo']);
    
} catch(PDOException $e) {
    // Errore di database
    error_log("Database error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Errore del server: ' . $e->getMessage()]);
} 