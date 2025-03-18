<?php
// Carica la configurazione e le utilities
require_once '../config.php';
// Rimuovo il controllo dell'autenticazione perché anche la pagina principale deve accedere agli articoli
// require_once 'auth_check.php';

// Configura CORS
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Per le richieste OPTIONS, termina qui
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Verifica che sia una richiesta GET
if ($_SERVER['REQUEST_METHOD'] != 'GET') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Metodo non consentito']);
    exit;
}

// Verifica che l'ID articolo sia stato fornito
if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'ID articolo richiesto']);
    exit;
}

$articleId = $_GET['id'];

// Connessione al database
try {
    // Utilizza la funzione dal config.php invece di creare una nuova connessione
    $db = getDBConnection();
    
    // Prepara e esegui la query
    $stmt = $db->prepare("
        SELECT a.*, c.name AS category_name 
        FROM articles a 
        JOIN categories c ON a.category_id = c.category_id 
        WHERE a.article_id = :article_id
    ");
    $stmt->bindParam(':article_id', $articleId, PDO::PARAM_INT);
    $stmt->execute();
    
    $article = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($article) {
        // Articolo trovato, restituisci i dati
        echo json_encode(['success' => true, 'data' => $article]);
    } else {
        // Articolo non trovato
        http_response_code(404); // Not Found
        echo json_encode(['success' => false, 'message' => 'Articolo non trovato']);
    }
} catch(PDOException $e) {
    // Errore di database
    error_log("Database error: " . $e->getMessage());
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'success' => false, 
        'message' => 'Errore del server', 
        'debug' => $e->getMessage()
    ]);
} 