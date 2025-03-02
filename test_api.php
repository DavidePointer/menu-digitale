<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/html; charset=utf-8');

echo "<h1>Debug Applicazione Menu</h1>";

// Link diretto all'applicazione
echo "<div style='margin-bottom: 20px; padding: 10px; background: #f0f0f0; border-radius: 4px;'>";
echo "<strong>Link diretto all'applicazione:</strong><br>";
echo "<a href='http://localhost/menu_digitale/index.html' target='_blank'>http://localhost/menu_digitale/index.html</a>";
echo "</div>";

// 1. Test Config
echo "<h2>1. Configurazione Database</h2>";
require_once 'config.php';
echo "<pre>";
print_r([
    'DB_SERVER' => DB_SERVER,
    'DB_NAME' => DB_NAME
]);
echo "</pre>";

try {
    $pdo = getDBConnection();
    
    // 1. Test connessione
    echo "<h2>1. Test Connessione</h2>";
    echo "✅ Connessione OK<br><br>";
    
    // 2. Test chiamata API categorie
    echo "<h2>2. Test API Categorie</h2>";
    $url = "http://" . $_SERVER['HTTP_HOST'] . "/menu_digitale/api/menu.php";
    echo "URL API: " . $url . "<br><br>";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "HTTP Status: " . $httpCode . "<br>";
    echo "Risposta API:<br><pre>";
    print_r(json_decode($response, true));
    echo "</pre>";
    
    // 3. Test query diretta
    echo "<h2>3. Test Query Diretta</h2>";
    $sql = "SELECT name FROM dbo.categories ORDER BY display_order";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Risultati query diretta:<br><pre>";
    print_r($categories);
    echo "</pre>";
    
    // 4. Test formattazione
    echo "<h2>4. Test Formattazione</h2>";
    $result = array();
    foreach ($categories as $category) {
        $urlName = strtolower(str_replace(' ', '', $category['name']));
        $result[] = array(
            'name' => $category['name'],
            'url_name' => $urlName,
            'image_url' => "images/{$urlName}.jpg"
        );
    }
    
    echo "Risultati formattati:<br><pre>";
    print_r($result);
    echo "</pre>";

} catch (Exception $e) {
    echo "<h2>❌ Errore:</h2>";
    echo $e->getMessage();
}
?> 