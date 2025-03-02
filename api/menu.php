<?php
// Headers CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Cache-Control: public, max-age=300'); // Cache per 5 minuti

// Log iniziale con informazioni sulla richiesta
error_log("=== Inizio richiesta API menu.php ===");
error_log("Metodo: " . $_SERVER['REQUEST_METHOD']);
error_log("URL: " . $_SERVER['REQUEST_URI']);
error_log("Query string: " . $_SERVER['QUERY_STRING']);

// Se è una richiesta OPTIONS, termina qui
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    error_log("Richiesta OPTIONS ricevuta, invio headers CORS");
    exit(0);
}

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
                c.name as category_name,
                LOWER(REPLACE(c.name, ' ', '_')) as category_url_name
                FROM dbo.articles a 
                JOIN dbo.categories c ON a.category_id = c.category_id 
                WHERE LOWER(REPLACE(c.name, ' ', '_')) = ?
                ORDER BY a.name";
                
        error_log("Query SQL: " . $sql);
        error_log("Parametro categoria: " . $category);
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$category]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("Trovati " . count($result) . " articoli");
        error_log("Risultati query: " . print_r($result, true));
    } else {
        error_log("Esecuzione query tutte le categorie");
        $sql = "SELECT 
                c.name,
                c.description,
                c.category_id,
                c.display_order,
                c.image_path,
                LOWER(REPLACE(c.name, ' ', '_')) as url_name
                FROM dbo.categories c 
                ORDER BY c.display_order";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Processiamo i risultati per gestire i percorsi delle immagini
        foreach ($result as &$category) {
            // Generiamo un nome file sicuro per l'immagine
            $imageFileName = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '_', $category['name'])));
            $category['image_url'] = 'images/' . $imageFileName . '.jpg';
            
            // Verifichiamo se l'immagine esiste
            $imagePath = dirname(dirname(__FILE__)) . '/images/' . $imageFileName . '.jpg';
            if (!file_exists($imagePath)) {
                error_log("Immagine non trovata: " . $imagePath);
                $category['image_url'] = 'images/placeholder.jpg';
            }
            
            unset($category['image_path']);
        }
        
        error_log("Trovate " . count($result) . " categorie");
    }

    error_log("Dati recuperati dal database:");
    error_log(print_r($result, true));

    $json = json_encode($result, JSON_PRETTY_PRINT);
    if ($json === false) {
        error_log("❌ Errore nella codifica JSON: " . json_last_error_msg());
        throw new Exception("Errore nella codifica JSON");
    }
    
    error_log("✅ JSON generato con successo");
    error_log("=== Fine richiesta API menu.php ===");
    
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