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

// Configura il logging
$logFile = '../logs/admin_actions.log';
if (!file_exists('../logs/')) {
    mkdir('../logs/', 0777, true);
}

function logAction($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] " . $message . PHP_EOL;
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

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

// Ottieni i dati JSON dalla richiesta
$data = json_decode(file_get_contents("php://input"), true);

// Verifica che l'ID categoria sia stato fornito
if (!isset($data['category_id']) || empty($data['category_id'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'ID categoria richiesto']);
    exit;
}

$categoryId = $data['category_id'];

// Connessione al database
try {
    $db = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Inizia una transazione per garantire l'integrità dei dati
    $db->beginTransaction();
    
    // Ottieni le informazioni sulla categoria e sugli articoli associati prima dell'eliminazione
    $stmt = $db->prepare("SELECT * FROM categories WHERE category_id = :category_id");
    $stmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
    $stmt->execute();
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$category) {
        $db->rollBack();
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Categoria non trovata']);
        exit;
    }
    
    $categoryName = $category['name'];
    $categoryImage = $category['image'];
    
    // Ottieni gli articoli associati alla categoria
    $stmt = $db->prepare("SELECT * FROM articles WHERE category_id = :category_id");
    $stmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
    $stmt->execute();
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Elimina gli articoli associati alla categoria
    $stmt = $db->prepare("DELETE FROM articles WHERE category_id = :category_id");
    $stmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
    $stmt->execute();
    
    // Elimina la categoria
    $stmt = $db->prepare("DELETE FROM categories WHERE category_id = :category_id");
    $stmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
    $stmt->execute();
    
    // Commit della transazione
    $db->commit();
    
    // Elimina le immagini degli articoli associati
    $articlesDir = '../images/articles/';
    foreach ($articles as $article) {
        if (isset($article['image']) && file_exists($articlesDir . $article['image'])) {
            unlink($articlesDir . $article['image']);
        }
    }
    
    // Elimina l'immagine della categoria
    $categoriesDir = '../images/categories/';
    if (isset($categoryImage) && file_exists($categoriesDir . $categoryImage)) {
        unlink($categoriesDir . $categoryImage);
    }
    
    // Log dell'azione
    $articlesCount = count($articles);
    $username = $_SESSION['user']['username'] ?? 'unknown';
    logAction("Utente $username ha eliminato la categoria '$categoryName' (ID: $categoryId) con $articlesCount articoli associati");
    
    // Restituisci la risposta
    echo json_encode([
        'success' => true, 
        'message' => 'Categoria eliminata con successo',
        'deleted_items' => [
            'category' => $categoryName,
            'articles_count' => $articlesCount
        ]
    ]);
    
} catch(PDOException $e) {
    // Errore di database, rollback della transazione
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    
    error_log("Database error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Errore del server']);
} 