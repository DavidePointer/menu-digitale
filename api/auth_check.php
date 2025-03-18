<?php
/**
 * Questo file deve essere incluso all'inizio di ogni API che richiede autenticazione.
 * Verifica che l'utente sia autenticato prima di consentire l'accesso.
 * Esempio di utilizzo: 
 * require_once 'auth_check.php';
 */

// Avvia la sessione se non è già attiva
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Configura il log degli errori
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/auth.log');

// Verifica se l'utente è autenticato
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    error_log("Tentativo di accesso API protetta senza autenticazione. URL: " . $_SERVER['REQUEST_URI']);
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Autenticazione richiesta']);
    exit;
}

// Verifica se la sessione è scaduta (dopo 2 ore di inattività)
$sessionTimeout = 7200; // 2 ore in secondi
if (time() - $_SESSION['login_time'] > $sessionTimeout) {
    error_log("Sessione scaduta per utente: " . ($_SESSION['username'] ?? 'unknown'));
    session_unset();
    session_destroy();
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Sessione scaduta, effettua di nuovo il login']);
    exit;
}

// Aggiorna il timestamp della sessione
$_SESSION['login_time'] = time();

// Registra l'accesso all'API
error_log("Accesso API autenticato: " . $_SESSION['username'] . ", URL: " . $_SERVER['REQUEST_URI']); 