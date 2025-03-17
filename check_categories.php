<?php
require_once 'config.php';

try {
    $pdo = getDBConnection();
    
    // Stampa la configurazione del database
    echo "Configurazione Database:<br>";
    echo "Server: " . DB_SERVER . "<br>";
    echo "Database: " . DB_NAME . "<br><br>";
    
    // Verifica se la tabella esiste
    $tables = $pdo->query("SHOW TABLES LIKE 'categories'")->fetchAll();
    echo "Tabella categories esiste: " . (count($tables) > 0 ? 'Sì' : 'No') . "<br><br>";
    
    if (count($tables) > 0) {
        // Mostra la struttura della tabella
        echo "Struttura della tabella categories:<br>";
        $columns = $pdo->query("SHOW COLUMNS FROM categories")->fetchAll(PDO::FETCH_ASSOC);
        echo "<pre>" . print_r($columns, true) . "</pre><br>";
        
        // Mostra i dati nella tabella
        echo "Contenuto della tabella categories:<br>";
        $categories = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
        echo "<pre>" . print_r($categories, true) . "</pre>";
    }
    
} catch (Exception $e) {
    echo "Errore: " . $e->getMessage();
} 