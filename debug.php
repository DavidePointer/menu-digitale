<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/html; charset=utf-8');

echo "<h1>Debug Applicazione Menu</h1>";

// 1. Test Config
echo "<h2>1. Configurazione Database</h2>";
require_once 'config.php';
echo "<pre>";
print_r([
    'DB_SERVER' => DB_SERVER,
    'DB_NAME' => DB_NAME
]);
echo "</pre>";

// 2. Test Connessione
echo "<h2>2. Test Connessione Database</h2>";
try {
    $pdo = getDBConnection();
    echo "✅ Connessione riuscita<br>";
} catch (Exception $e) {
    echo "❌ Errore connessione: " . $e->getMessage() . "<br>";
}

// 3. Test Query Categorie
echo "<h2>3. Test Query Categorie</h2>";
try {
    $sql = "SELECT * FROM dbo.categories ORDER BY display_order";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($categories);
    echo "</pre>";
} catch (Exception $e) {
    echo "❌ Errore query: " . $e->getMessage() . "<br>";
}

// 4. Test API Endpoint
echo "<h2>4. Test API Endpoint</h2>";
$apiUrl = "http://" . $_SERVER['HTTP_HOST'] . "/menu_digitale/api/menu.php";
echo "URL API: " . $apiUrl . "<br><br>";

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status: " . $httpCode . "<br>";
echo "Risposta API:<br><pre>";
echo htmlspecialchars($response);
echo "</pre>";

// 5. Test File System
echo "<h2>5. Test File System</h2>";
$paths = [
    'config.php' => file_exists('config.php'),
    'api/menu.php' => file_exists('api/menu.php'),
    'js/menu.js' => file_exists('js/menu.js'),
    'index.html' => file_exists('index.html')
];
echo "<pre>";
print_r($paths);
echo "</pre>";

// 6. Log PHP
echo "<h2>6. PHP Error Log</h2>";
$logPath = ini_get('error_log');
echo "Log Path: " . $logPath . "<br>";
if (file_exists($logPath)) {
    echo "<pre>";
    echo htmlspecialchars(file_get_contents($logPath));
    echo "</pre>";
} else {
    echo "❌ File di log non trovato<br>";
}
?> 