<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

echo "<h1>Configurazione Database MySQL</h1>";

try {
    // Connessione al server MySQL senza specificare il database
    $pdo = new PDO("mysql:host=" . DB_SERVER . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Verifica se il database esiste, altrimenti lo crea
    $stmt = $pdo->query("SHOW DATABASES LIKE '" . DB_NAME . "'");
    if ($stmt->rowCount() == 0) {
        echo "Creazione database " . DB_NAME . "...<br>";
        $pdo->exec("CREATE DATABASE `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "Database creato con successo!<br>";
    } else {
        echo "Il database " . DB_NAME . " esiste già.<br>";
    }
    
    // Connessione al database specifico
    $pdo = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Creazione tabelle
    echo "Creazione delle tabelle...<br>";
    
    // Tabella categorie
    $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
        category_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        image_url VARCHAR(255) NOT NULL
    )");
    echo "Tabella 'categories' creata.<br>";
    
    // Tabella articoli
    $pdo->exec("CREATE TABLE IF NOT EXISTS articles (
        article_id INT AUTO_INCREMENT PRIMARY KEY,
        category_id INT NOT NULL,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        image_url VARCHAR(255) NOT NULL,
        FOREIGN KEY (category_id) REFERENCES categories(category_id)
    )");
    echo "Tabella 'articles' creata.<br>";
    
    echo "<h2>Configurazione del database completata con successo!</h2>";
    
} catch (PDOException $e) {
    echo "<h2>Errore durante la configurazione del database:</h2>";
    echo $e->getMessage();
}
?> 