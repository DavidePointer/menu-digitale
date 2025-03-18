<?php
/**
 * Questo file deve essere incluso all'inizio di ogni API che richiede autenticazione.
 * Verifica che l'utente sia autenticato prima di consentire l'accesso.
 * Esempio di utilizzo: 
 * require_once 'auth_check.php';
 */

// Includi il file di autenticazione
require_once 'auth.php';

// File di log
$logFile = __DIR__ . '/../logs/auth.log';

// Verifica se l'utente è autenticato
if (!isAuthenticated()) {
    $logMessage = date('Y-m-d H:i:s') . " - Tentativo di accesso API protetta senza autenticazione. URL: " . $_SERVER['REQUEST_URI'] . "\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
    
    header('Content-Type: application/json');
    http_response_code(401);
    jsonResponse(false, 'Autenticazione richiesta');
}

// Verifica del token
$token = getTokenFromRequest();
if (!$token || !verifyToken($token)) {
    $logMessage = date('Y-m-d H:i:s') . " - Token non valido o mancante. URL: " . $_SERVER['REQUEST_URI'] . "\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
    
    header('Content-Type: application/json');
    http_response_code(401);
    jsonResponse(false, 'Token non valido o mancante');
}

// Verifica se la sessione è scaduta (dopo 2 ore di inattività)
$sessionTimeout = 7200; // 2 ore in secondi
if (time() - $_SESSION['login_time'] > $sessionTimeout) {
    $logMessage = date('Y-m-d H:i:s') . " - Sessione scaduta per utente: " . ($_SESSION['username'] ?? 'unknown') . "\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
    
    // Rimuovi la sessione
    session_unset();
    session_destroy();
    
    header('Content-Type: application/json');
    http_response_code(401);
    jsonResponse(false, 'Sessione scaduta, effettua di nuovo il login');
}

// Aggiorna il timestamp della sessione
$_SESSION['login_time'] = time();

// Registra l'accesso all'API
$logMessage = date('Y-m-d H:i:s') . " - Accesso API autenticato: " . $_SESSION['username'] . ", URL: " . $_SERVER['REQUEST_URI'] . "\n";
file_put_contents($logFile, $logMessage, FILE_APPEND); 