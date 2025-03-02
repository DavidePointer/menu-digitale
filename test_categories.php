<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

try {
    $pdo = getDBConnection();
    echo "<h2>Test Query Categorie</h2>";
    
    // Test query diretta
    $sql = "SELECT * FROM dbo.categories ORDER BY display_order";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<pre>";
    echo "Numero categorie trovate: " . count($categories) . "\n\n";
    echo "Dettaglio categorie:\n";
    print_r($categories);
    echo "</pre>";
    
    // Test query formattata come nel menu.php
    echo "<h3>Test Query Menu:</h3>";
    $sql = "SELECT 
            name,
            LOWER(REPLACE(name, ' ', '')) as url_name,
            CONCAT('images/', LOWER(REPLACE(name, ' ', '')), '.jpg') as image_url 
            FROM dbo.categories 
            ORDER BY display_order";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $menuCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<pre>";
    print_r($menuCategories);
    echo "</pre>";
    
} catch (PDOException $e) {
    echo "<h2>❌ Errore:</h2>";
    echo $e->getMessage();
}
?> 