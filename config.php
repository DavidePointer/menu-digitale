<?php
define('DB_SERVER', 'localhost');
define('DB_NAME', 'menu_db');
define('DB_USER', 'root');  // Utente predefinito di MySQL in XAMPP
define('DB_PASS', '');  // Password predefinita vuota in XAMPP

/**
 * Restituisce una connessione al database, creando il database se necessario
 * 
 * @return PDO La connessione PDO al database
 */
function getDBConnection() {
    try {
        // Prima prova a connettersi al server MySQL (senza specificare il database)
        $pdoServer = new PDO('mysql:host=' . DB_SERVER . ';charset=utf8mb4', DB_USER, DB_PASS);
        $pdoServer->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Verifica se il database esiste e lo crea se necessario
        $stmt = $pdoServer->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . DB_NAME . "'");
        if ($stmt->rowCount() == 0) {
            // Il database non esiste, crealo
            $pdoServer->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            error_log("Database " . DB_NAME . " creato con successo");
        }
        
        // Ora connettiti al database specifico
        $dsn = "mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        return $pdo;
    } catch (PDOException $e) {
        error_log("Errore connessione database: " . $e->getMessage());
        throw $e;
    }
}
?>