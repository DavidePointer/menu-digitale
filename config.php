<?php
define('DB_SERVER', 'MSI-POINTER\\SQLEXPRESS');
define('DB_NAME', 'menu_db');
define('DB_CHARSET', 'UTF-8');

function getDBConnection() {
    try {
        $pdo = new PDO(
            "sqlsrv:Server=" . DB_SERVER . ";Database=" . DB_NAME,
            "",  // username
            "",  // password
            array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::SQLSRV_ATTR_ENCODING => PDO::SQLSRV_ENCODING_UTF8
            )
        );
        return $pdo;
    } catch (PDOException $e) {
        error_log("Errore connessione database: " . $e->getMessage());
        throw $e;
    }
}
?>