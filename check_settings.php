<?php
require_once 'config.php';

try {
    $conn = getDBConnection();
    
    // Verifica settings
    $stmt = $conn->query("SELECT * FROM settings");
    echo "Dati nella tabella 'settings':\n";
    $settingCount = 0;
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $settingCount++;
        echo "Chiave: " . $row['setting_key'] . " - Valore: " . $row['setting_value'] . "\n";
    }
    
    if ($settingCount == 0) {
        echo "Nessuna impostazione trovata.\n";
    }
} catch (PDOException $e) {
    echo "Errore: " . $e->getMessage();
}
?> 