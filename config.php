<?php
define('DB_SERVER', 'MSI-POINTER\\SQLEXPRESS');
define('DB_NAME', 'menu_db');
define('DB_USER', '');  // Se usi Windows Authentication lascia vuoto
define('DB_PASS', '');  // Se usi Windows Authentication lascia vuoto

function getDBConnection() {
    try {
        $dsn = "sqlsrv:Server=" . DB_SERVER . ";Database=" . DB_NAME;
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Errore connessione database: " . $e->getMessage());
        throw $e;
    }
}
?>