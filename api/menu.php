<?php
// Headers CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Cache-Control: public, max-age=300'); // Cache per 5 minuti

// Configurazione dei log
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/debug.log');
if (!file_exists(__DIR__ . '/../logs')) {
    mkdir(__DIR__ . '/../logs', 0777, true);
}
error_log("=== Inizio richiesta API menu.php === " . date('Y-m-d H:i:s'));
error_log("Metodo: " . $_SERVER['REQUEST_METHOD']);
error_log("URL: " . $_SERVER['REQUEST_URI']);
error_log("Query string: " . $_SERVER['QUERY_STRING']);

// Se è una richiesta OPTIONS, termina qui
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    error_log("Richiesta OPTIONS ricevuta, invio headers CORS");
    exit(0);
}

// Abilita visualizzazione errori
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    error_log("Caricamento config.php");
    require_once '../config.php';
    
    error_log("Tentativo connessione al database");
    $pdo = getDBConnection();
    error_log("✅ Connessione DB stabilita");
    
    $category = isset($_GET['category']) ? $_GET['category'] : null;
    error_log("Categoria richiesta: " . ($category ?? 'nessuna'));
    
    if ($category) {
        error_log("Esecuzione query articoli per categoria: " . $category);
        $sql = "SELECT 
                a.name,
                a.description, 
                a.price,
                a.image_url,
                c.name as category_name,
                LOWER(REPLACE(c.name, ' ', '_')) as category_url_name
                FROM articles a 
                JOIN categories c ON a.category_id = c.category_id 
                WHERE LOWER(REPLACE(c.name, ' ', '_')) = ?
                ORDER BY a.name";
                
        error_log("Query SQL: " . $sql);
        error_log("Parametro categoria: " . $category);
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$category]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("Trovati " . count($result) . " articoli");
        
        // Verifica se le immagini esistono e assegna placeholder se necessario
        foreach ($result as &$item) {
            $imagePath = dirname(dirname(__FILE__)) . '/' . $item['image_url'];
            if (!file_exists($imagePath) || empty($item['image_url'])) {
                // Usa l'immagine della categoria come fallback
                $item['image_url'] = 'images/placeholder.jpg';
                error_log("Immagine non trovata, usando placeholder: " . $imagePath);
            }
        }
        
        error_log("Risultati query: " . print_r($result, true));
    } else {
        error_log("Esecuzione query tutte le categorie");
        $sql = "SELECT 
                c.name,
                c.category_id,
                c.image_url,
                LOWER(REPLACE(c.name, ' ', '_')) as url_name
                FROM categories c 
                ORDER BY c.name";
        error_log("Query SQL: " . $sql);
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        error_log("Trovate " . count($result) . " categorie");
        error_log("Categorie: " . print_r($result, true));
    }

    error_log("Dati recuperati dal database");

    $json = json_encode($result, JSON_PRETTY_PRINT);
    if ($json === false) {
        error_log("❌ Errore nella codifica JSON: " . json_last_error_msg());
        throw new Exception("Errore nella codifica JSON");
    }
    
    error_log("✅ JSON generato con successo");
    error_log("JSON: " . $json);
    error_log("=== Fine richiesta API menu.php === " . date('Y-m-d H:i:s'));
    
    echo $json;

} catch (Exception $e) {
    error_log("❌ ERRORE: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        "error" => "Errore database",
        "message" => $e->getMessage(),
        "trace" => $e->getTraceAsString()
    ]);
}
?> 