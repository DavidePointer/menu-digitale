<?php
// test_db.php - Verifica connessione database

require_once 'config.php';

header('Content-Type: text/plain');

echo "Test di connessione al database MySQL\n\n";

try {
    $pdo = getDBConnection();
    echo "✅ Connessione al database riuscita!\n";
    
    // Verifica esistenza tabelle
    $tables = [
        'users',
        'settings',
        'categories',
        'articles'
    ];
    
    echo "\nTabelle presenti nel database:\n";
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '" . $table . "'");
        $exists = $stmt->rowCount() > 0;
        echo ($exists ? "✅" : "❌") . " " . $table . "\n";
    }
    
    // Verifica database
    echo "\nInformazioni del database:\n";
    $stmt = $pdo->query("SELECT DATABASE() as db_name");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Database in uso: " . $row['db_name'] . "\n";
    
    $stmt = $pdo->query("SHOW VARIABLES LIKE 'character_set_database'");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Character set: " . $row['Value'] . "\n";
    
    // Verifica versione MySQL
    $stmt = $pdo->query("SELECT VERSION() as version");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Versione MySQL: " . $row['version'] . "\n";
    
} catch (PDOException $e) {
    echo "❌ Errore di connessione al database: " . $e->getMessage() . "\n";
    
    echo "\nVerifica dei parametri di connessione:\n";
    echo "Server: " . DB_SERVER . "\n";
    echo "Database: " . DB_NAME . "\n";
    echo "Utente: " . DB_USER . "\n";
    echo "Password: " . (empty(DB_PASS) ? "(vuota)" : "(impostata)") . "\n";
}
?> 