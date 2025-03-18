<?php
// api/add_category_simple.php - Versione semplificata per aggiungere una categoria

// Disabilita tutti gli errori e avvisi
error_reporting(0);
ini_set('display_errors', 0);

// Buffer di output per catturare qualsiasi output indesiderato
ob_start();

try {
    // Controlla se è una richiesta POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Metodo HTTP non valido. Richiesto POST.');
    }
    
    // Verifica se è stata fornita un'immagine
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Immagine richiesta.');
    }
    
    // Verifica se è stato fornito un nome
    if (!isset($_POST['name']) || empty($_POST['name'])) {
        throw new Exception('Nome categoria richiesto.');
    }
    
    // Creazione della directory per le immagini se non esiste
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/menu_digitale/images/categories/';
    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true)) {
            throw new Exception('Impossibile creare la directory per le immagini.');
        }
    }
    
    // Genera un nome file univoco
    $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
    $uploadFile = $uploadDir . $fileName;
    
    // Carica l'immagine
    if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
        throw new Exception('Errore durante il caricamento dell\'immagine.');
    }
    
    // Inserisci nel database
    require_once '../config.php';
    $db = getDBConnection();
    $stmt = $db->prepare("INSERT INTO categories (name, image_url) VALUES (?, ?)");
    $imageUrl = 'images/categories/' . $fileName;
    
    if (!$stmt->execute([$_POST['name'], $imageUrl])) {
        // Se fallisce, elimina l'immagine caricata
        @unlink($uploadFile);
        throw new Exception('Errore durante l\'inserimento della categoria nel database.');
    }
    
    // Pulisci qualsiasi output precedente
    ob_end_clean();
    
    // Successo - ritorna una risposta JSON valida
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Categoria aggiunta con successo!',
        'category_id' => $db->lastInsertId()
    ]);
    
} catch (Exception $e) {
    // Pulisci qualsiasi output precedente
    ob_end_clean();
    
    // Ritorna un errore JSON
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 