<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

try {
    $pdo = getDBConnection();
    echo "✅ Connessione al database riuscita!<br>";
    
    // Test query semplice
    $sql = "SELECT COUNT(*) as count FROM dbo.articles";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Numero totale articoli: " . $row['count'] . "<br>";
    
    // Test categorie
    $sql = "SELECT COUNT(*) as count FROM dbo.categories";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Numero totale categorie: " . $row['count'] . "<br>";
    
} catch (PDOException $e) {
    echo "❌ Errore: " . $e->getMessage();
}
?> 