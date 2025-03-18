<?php
// Abilita la visualizzazione di tutti gli errori
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Carica la configurazione
require_once '../config.php';

// Valori di configurazione
echo "<h2>Configurazione Database</h2>";
echo "Host: " . DB_SERVER . "<br>";
echo "Database: " . DB_NAME . "<br>";
echo "Utente: " . DB_USER . "<br>";
echo "Password: " . (empty(DB_PASS) ? "Non impostata" : "Impostata (nascosta)") . "<br>";

// Tenta connessione al database
echo "<h2>Test Connessione Database</h2>";
try {
    $db = getDBConnection();
    echo "<span style='color:green'>✓ Connessione al database riuscita!</span><br>";
} catch(PDOException $e) {
    echo "<span style='color:red'>✗ Errore di connessione: " . $e->getMessage() . "</span><br>";
    die("</body></html>");
}

// Verifica tabelle
echo "<h2>Verifica Tabelle</h2>";
$tables = ['users', 'categories', 'articles'];
foreach ($tables as $table) {
    try {
        $query = "SHOW TABLES LIKE '$table'";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $tableExists = $stmt->rowCount() > 0;
        
        if ($tableExists) {
            echo "<span style='color:green'>✓ Tabella '$table' esiste</span><br>";
            
            // Conta righe
            $query = "SELECT COUNT(*) FROM $table";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $count = $stmt->fetchColumn();
            echo "&nbsp;&nbsp;&nbsp;- Contiene $count righe<br>";
            
            // Mostra struttura
            $query = "DESCRIBE $table";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $fields = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<details>";
            echo "<summary>Struttura tabella</summary>";
            echo "<table border='1' cellpadding='3'>";
            echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Chiave</th><th>Default</th><th>Extra</th></tr>";
            
            foreach ($fields as $field) {
                echo "<tr>";
                echo "<td>" . $field['Field'] . "</td>";
                echo "<td>" . $field['Type'] . "</td>";
                echo "<td>" . $field['Null'] . "</td>";
                echo "<td>" . $field['Key'] . "</td>";
                echo "<td>" . $field['Default'] . "</td>";
                echo "<td>" . $field['Extra'] . "</td>";
                echo "</tr>";
            }
            
            echo "</table>";
            echo "</details>";
        } else {
            echo "<span style='color:red'>✗ Tabella '$table' non esiste!</span><br>";
            echo "&nbsp;&nbsp;&nbsp;<a href='create_tables.php' target='_blank'>Creazione tabelle</a><br>";
        }
    } catch(PDOException $e) {
        echo "<span style='color:red'>✗ Errore nel controllo della tabella '$table': " . $e->getMessage() . "</span><br>";
    }
}

// Test query problematica
echo "<h2>Test Query Articoli</h2>";
try {
    $query = "SELECT a.* FROM articles a ORDER BY a.category_id, a.name";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<span style='color:green'>✓ Query articoli eseguita con successo.</span><br>";
    echo "Articoli trovati: " . count($articles) . "<br>";
    
    if (count($articles) > 0) {
        echo "<details>";
        echo "<summary>Mostra articoli</summary>";
        echo "<table border='1' cellpadding='3'>";
        echo "<tr>";
        foreach (array_keys($articles[0]) as $key) {
            echo "<th>$key</th>";
        }
        echo "</tr>";
        
        foreach ($articles as $article) {
            echo "<tr>";
            foreach ($article as $value) {
                echo "<td>" . htmlspecialchars($value) . "</td>";
            }
            echo "</tr>";
        }
        
        echo "</table>";
        echo "</details>";
    }
} catch(PDOException $e) {
    echo "<span style='color:red'>✗ Errore nell'esecuzione della query articoli: " . $e->getMessage() . "</span><br>";
}

// Test query problematica con JOIN
echo "<h2>Test Query Articoli con JOIN</h2>";
try {
    $query = "SELECT a.*, c.name as category_name 
              FROM articles a 
              JOIN categories c ON a.category_id = c.category_id 
              ORDER BY a.category_id, a.name";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<span style='color:green'>✓ Query articoli con JOIN eseguita con successo.</span><br>";
    echo "Articoli trovati: " . count($articles) . "<br>";
    
    if (count($articles) > 0) {
        echo "<details>";
        echo "<summary>Mostra articoli con JOIN</summary>";
        echo "<table border='1' cellpadding='3'>";
        echo "<tr>";
        foreach (array_keys($articles[0]) as $key) {
            echo "<th>$key</th>";
        }
        echo "</tr>";
        
        foreach ($articles as $article) {
            echo "<tr>";
            foreach ($article as $value) {
                echo "<td>" . htmlspecialchars($value) . "</td>";
            }
            echo "</tr>";
        }
        
        echo "</table>";
        echo "</details>";
    }
} catch(PDOException $e) {
    echo "<span style='color:red'>✗ Errore nell'esecuzione della query articoli con JOIN: " . $e->getMessage() . "</span><br>";
}

// Mostra informazioni PHP
echo "<h2>Informazioni PHP</h2>";
echo "Versione PHP: " . PHP_VERSION . "<br>";
echo "Estensioni PHP Caricate: <br>";
echo "<ul>";
$extensions = get_loaded_extensions();
sort($extensions);
foreach ($extensions as $extension) {
    echo "<li>$extension</li>";
}
echo "</ul>";

// Mostra ultimi log di errore
echo "<h2>Ultimi Errori PHP (se disponibili)</h2>";
$error_log = ini_get('error_log');
if (file_exists($error_log) && is_readable($error_log)) {
    echo "<pre>" . file_get_contents($error_log, false, null, -4096) . "</pre>";
} else {
    echo "File di log non disponibile o non leggibile: $error_log";
} 