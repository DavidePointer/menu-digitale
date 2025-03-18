<?php
// Carica la configurazione
require_once '../config.php';

// Configura CORS
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Abilita la visualizzazione di errori per il debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

// Struttura di risposta
$response = [
    'success' => false,
    'message' => '',
    'data' => null,
    'debug' => [
        'db_server' => DB_SERVER,
        'db_name' => DB_NAME,
        'db_user' => DB_USER,
        'db_pass' => '******', // nascosto per sicurezza
        'php_version' => PHP_VERSION,
        'tables_check' => []
    ]
];

// Connessione al database
try {
    // Test connessione database
    try {
        $db = getDBConnection();
        $response['debug']['connection'] = 'success';
    } catch (PDOException $e) {
        $response['debug']['connection'] = 'failed';
        $response['debug']['connection_error'] = $e->getMessage();
        throw new Exception('Connessione al database fallita: ' . $e->getMessage());
    }
    
    // Verifica se le tabelle esistono
    $tables = ['users', 'categories', 'articles'];
    $allTablesExist = true;
    
    foreach ($tables as $table) {
        $checkTableQuery = "SHOW TABLES LIKE '$table'";
        $stmt = $db->prepare($checkTableQuery);
        $stmt->execute();
        $tableExists = $stmt->rowCount() > 0;
        $response['debug']['tables_check'][$table] = $tableExists ? 'exists' : 'missing';
        
        if (!$tableExists) {
            $allTablesExist = false;
        }
    }
    
    if (!$allTablesExist) {
        $response['message'] = 'Alcune tabelle necessarie non esistono nel database. Apri /menu_digitale/api/create_tables.php per crearle.';
        http_response_code(500);
        echo json_encode($response);
        exit;
    }
    
    // Conta gli articoli per il debug
    $stmt = $db->prepare("SELECT COUNT(*) FROM articles");
    $stmt->execute();
    $articlesCount = $stmt->fetchColumn();
    $response['debug']['articles_count'] = $articlesCount;
    
    // Conta le categorie per il debug
    $stmt = $db->prepare("SELECT COUNT(*) FROM categories");
    $stmt->execute();
    $categoriesCount = $stmt->fetchColumn();
    $response['debug']['categories_count'] = $categoriesCount;
    
    // Query semplificata per evitare problemi di JOIN
    $query = "SELECT a.* FROM articles a";
    if ($categoryFilter) {
        $query .= " $categoryFilter";
    }
    $query .= " ORDER BY a.category_id, a.name";
    
    $stmt = $db->prepare($query);
    
    // Bind dei parametri se necessario
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->execute();
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Ottieni le informazioni delle categorie
    $categoryInfo = [];
    $stmt = $db->prepare("SELECT category_id, name FROM categories");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($categories as $category) {
        $categoryInfo[$category['category_id']] = $category['name'];
    }
    
    // Aggiungi il nome della categoria a ciascun articolo
    foreach ($articles as &$article) {
        $categoryId = $article['category_id'];
        $article['category_name'] = isset($categoryInfo[$categoryId]) ? $categoryInfo[$categoryId] : 'Categoria Sconosciuta';
    }
    
    // Successo - restituisci gli articoli
    $response['success'] = true;
    $response['message'] = 'Articoli caricati con successo';
    $response['data'] = $articles;
    
    echo json_encode($response, JSON_NUMERIC_CHECK);
    
} catch(Exception $e) {
    $response['message'] = 'Errore: ' . $e->getMessage();
    error_log("Error in get_articles.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode($response);
} 