<?php
require_once 'config.php';

try {
    $conn = getDBConnection();
    $stmt = $conn->query("DESCRIBE categories");
    
    echo "Struttura della tabella 'categories':\n";
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . " - " . $row['Type'] . " - " . $row['Key'] . " - " . $row['Default'] . "\n";
    }
} catch (PDOException $e) {
    echo "Errore: " . $e->getMessage();
}
?> 