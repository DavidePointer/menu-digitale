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

// Verifica che l'ID articolo sia stato fornito
if (!isset($data['article_id']) || empty($data['article_id'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'ID articolo richiesto']);
    exit;
}

$articleId = $data['article_id'];

// Connessione al database
try {
    $db = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Ottieni le informazioni sull'articolo prima dell'eliminazione
    $stmt = $db->prepare("SELECT a.*, c.name AS category_name FROM articles a JOIN categories c ON a.category_id = c.category_id WHERE a.article_id = :article_id");
    $stmt->bindParam(':article_id', $articleId, PDO::PARAM_INT);
    $stmt->execute();
    $article = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$article) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Articolo non trovato']);
        exit;
    }
    
    $articleName = $article['name'];
    $articleImage = $article['image'];
    $categoryName = $article['category_name'];
    
    // Elimina l'articolo
    $stmt = $db->prepare("DELETE FROM articles WHERE article_id = :article_id");
    $stmt->bindParam(':article_id', $articleId, PDO::PARAM_INT);
    $stmt->execute();
    
    // Elimina l'immagine dell'articolo
    $articlesDir = '../images/articles/';
    if (isset($articleImage) && file_exists($articlesDir . $articleImage)) {
        unlink($articlesDir . $articleImage);
    }
    
    // Log dell'azione
    $username = $_SESSION['user']['username'] ?? 'unknown';
    logAction("Utente $username ha eliminato l'articolo '$articleName' (ID: $articleId) dalla categoria '$categoryName'");
    
    // Restituisci la risposta
    echo json_encode([
        'success' => true, 
        'message' => 'Articolo eliminato con successo',
        'deleted_item' => [
            'article' => $articleName,
            'category' => $categoryName
        ]
    ]);
    
} catch(PDOException $e) {
    // Errore di database
    error_log("Database error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Errore del server']);
} 