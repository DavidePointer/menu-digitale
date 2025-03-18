<?php
require_once 'config.php';

try {
    $conn = getDBConnection();
    
    // Verifica categorie
    $stmt = $conn->query("SELECT * FROM categories");
    echo "Dati nella tabella 'categories':\n";
    $categoryCount = 0;
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $categoryCount++;
        echo "ID: " . $row['category_id'] . " - Nome: " . $row['name'] . " - Immagine: " . $row['image_url'] . "\n";
    }
    
    if ($categoryCount == 0) {
        echo "Nessuna categoria trovata.\n";
    }
    
    echo "\n";
    
    // Verifica articoli
    $stmt = $conn->query("SELECT * FROM articles");
    echo "Dati nella tabella 'articles':\n";
    $articleCount = 0;
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $articleCount++;
        echo "ID: " . $row['article_id'] . " - Categoria: " . $row['category_id'] . " - Nome: " . $row['name'] . " - Prezzo: " . $row['price'] . "\n";
    }
    
    if ($articleCount == 0) {
        echo "Nessun articolo trovato.\n";
    }
} catch (PDOException $e) {
    echo "Errore: " . $e->getMessage();
}
?> 