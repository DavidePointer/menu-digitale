<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test Connessione MySQL</h1>";

try {
    $host = 'localhost';
    $dbname = 'menu_db';
    $username = 'root';
    $password = '';
    
    echo "Provo a connettermi a MySQL con PDO...<br>";
    $dsn = "mysql:host=$host;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connessione a MySQL stabilita.<br>";
    
    // Verifichiamo se il database esiste
    $stmt = $pdo->query("SHOW DATABASES LIKE '$dbname'");
    if ($stmt->rowCount() > 0) {
        echo "Il database '$dbname' esiste.<br>";
    } else {
        echo "Il database '$dbname' non esiste. Lo creerò...<br>";
        $pdo->exec("CREATE DATABASE `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "Database '$dbname' creato con successo.<br>";
    }
    
    // Connessione al database specifico
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connessione al database '$dbname' stabilita.<br>";
    
    // Verifica versione MySQL
    $stmt = $pdo->query("SELECT VERSION() as version");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Versione MySQL: " . $row['version'] . "<br>";
    
    $pdo = null; // Chiudi la connessione
    
} catch (PDOException $e) {
    echo "Errore PDO: " . $e->getMessage() . "<br>";
}
?> 