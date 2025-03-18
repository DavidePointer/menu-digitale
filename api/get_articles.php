<?php
// Carica la configurazione
require_once '../config.php';

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

// Filtra per categoria se richiesto
$categoryFilter = '';
$params = [];

if (isset($_GET['category_id']) && !empty($_GET['category_id'])) {
    $categoryFilter = 'WHERE a.category_id = :category_id';
    $params[':category_id'] = $_GET['category_id'];
}

// Connessione al database
try {
    // Configurazione per vedere tutti gli errori
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    $db = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Verifica se le tabelle esistono prima di eseguire la query
    $checkTableQuery = "SHOW TABLES LIKE 'articles'";
    $stmt = $db->prepare($checkTableQuery);
    $stmt->execute();
    $articlesTableExists = $stmt->rowCount() > 0;
    
    $checkTableQuery = "SHOW TABLES LIKE 'categories'";
    $stmt = $db->prepare($checkTableQuery);
    $stmt->execute();
    $categoriesTableExists = $stmt->rowCount() > 0;
    
    if (!$articlesTableExists || !$categoriesTableExists) {
        throw new Exception('Le tabelle necessarie non esistono nel database.');
    }
    
    // Prepara e esegui la query con join alla tabella categorie per ottenere il nome della categoria
    // Gestione più robusta con LEFT JOIN per evitare errori se una categoria è stata eliminata
    $query = "SELECT a.*, c.name as category_name 
              FROM articles a 
              LEFT JOIN categories c ON a.category_id = c.category_id 
              " . ($categoryFilter ? $categoryFilter : "") . " 
              ORDER BY a.category_id, a.name";
    
    $stmt = $db->prepare($query);
    
    // Bind dei parametri se necessario
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->execute();
    
    // Ottieni tutti gli articoli
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Restituisci gli articoli come JSON
    echo json_encode($articles, JSON_NUMERIC_CHECK);
    
} catch(PDOException $e) {
    // Errore di database
    error_log("Database error in get_articles.php: " . $e->getMessage());
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'error' => 'Database error', 
        'message' => $e->getMessage(),
        'query' => isset($query) ? $query : 'No query created yet'
    ]);
} catch(Exception $e) {
    // Altri errori
    error_log("Error in get_articles.php: " . $e->getMessage());
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Error', 'message' => $e->getMessage()]);
} 