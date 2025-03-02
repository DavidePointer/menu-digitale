<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $dsn = "sqlsrv:Server=MSI-POINTER\\SQLEXPRESS;Database=menu_db";
    $pdo = new PDO($dsn, "", "");
    echo "Connessione riuscita!";
    
    // Test query semplice
    $stmt = $pdo->prepare("SELECT @@version");
    $stmt->execute();
    $result = $stmt->fetch();
    echo "<pre>Version: " . print_r($result, true) . "</pre>";
    
} catch (PDOException $e) {
    die("Errore connessione: " . $e->getMessage());
}
?> 