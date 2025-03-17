<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $serverName = "MSI-POINTER\\SQLEXPRESS"; // Aggiorna con il tuo server name
    $connectionInfo = array(
        "Database" => "menu_db",
        "CharacterSet" => "UTF-8"
    );
    
    echo "Provo a connettermi con sqlsrv_connect...<br>";
    $conn = sqlsrv_connect($serverName, $connectionInfo);
    
    if($conn) {
        echo "Connessione con sqlsrv_connect stabilita.<br>";
        sqlsrv_close($conn);
    } else {
        echo "Connessione con sqlsrv_connect fallita.<br>";
        print_r(sqlsrv_errors(), true);
    }
    
    echo "<hr>";
    
    echo "Provo a connettermi con PDO...<br>";
    $dsn = "sqlsrv:Server=$serverName;Database=menu_db";
    $pdo = new PDO($dsn, "", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connessione con PDO stabilita.<br>";
    
    // Prova a eseguire una query di test
    $stmt = $pdo->query("SELECT @@VERSION");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Versione SQL Server: " . $row[''] . "<br>";
    
    $pdo = null; // Chiudi la connessione
    
} catch (Exception $e) {
    echo "Errore PDO: " . $e->getMessage() . "<br>";
}
?>