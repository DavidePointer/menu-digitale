<?php
require_once '../config.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Metodo non consentito');
    }

    // Validazione input
    $requiredFields = ['category_id', 'name', 'description', 'price'];
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("Campo $field richiesto");
        }
    }

    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Immagine richiesta');
    }

    // Gestione upload immagine
    $uploadDir = '../images/articles/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
    $uploadFile = $uploadDir . $fileName;

    if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
        throw new Exception('Errore nel caricamento dell\'immagine');
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
        unlink($uploadFile); // Rimuovi il file se l'inserimento fallisce
        throw new Exception('Errore nell\'inserimento dell\'articolo');
    }

    echo json_encode(['success' => true, 'message' => 'Articolo aggiunto con successo']);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 