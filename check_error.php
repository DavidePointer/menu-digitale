<?php
// Simula il comportamento di index.php ma in modo più semplice per diagnosticare l'errore
require_once 'config.php';

// Inizializza la connessione al database
$conn = getDBConnection();

// Funzione per ottenere le impostazioni dal database
function getSettings() {
    global $conn;
    
    try {
        $stmt = $conn->prepare("SELECT setting_key, setting_value FROM settings");
        $stmt->execute();
        
        $settings = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        
        return $settings;
    } catch (PDOException $e) {
        error_log('Errore nel recupero delle impostazioni: ' . $e->getMessage());
        return array(); // Restituisce un array vuoto in caso di errore
    }
}

// Carica le impostazioni
$settings = getSettings();

// Stampa le impostazioni
echo "<pre>";
print_r($settings);
echo "</pre>";

echo "Se stai vedendo le impostazioni sopra, il problema è stato risolto!";
?> 