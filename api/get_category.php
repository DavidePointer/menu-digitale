<?php
// Carica la configurazione e le utilities
require_once '../config.php';
// Rimuovo il controllo dell'autenticazione perché anche la pagina principale deve accedere alle categorie
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

// Verifica che l'ID categoria sia stato fornito
if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'ID categoria richiesto']);
    exit;
}

$categoryId = $_GET['id'];

// Connessione al database
try {
    // Utilizza la funzione dal config.php invece di creare una nuova connessione
    $db = getDBConnection();
    
    // Prepara e esegui la query
    $stmt = $db->prepare("SELECT * FROM categories WHERE category_id = :category_id");
    $stmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
    $stmt->execute();
    
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($category) {
        // Categoria trovata, restituisci i dati
        echo json_encode(['success' => true, 'data' => $category]);
    } else {
        // Categoria non trovata
        http_response_code(404); // Not Found
        echo json_encode(['success' => false, 'message' => 'Categoria non trovata']);
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