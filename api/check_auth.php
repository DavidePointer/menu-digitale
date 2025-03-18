<?php
// Headers CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Configurazione dei log
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/auth.log');
error_log("=== Verifica autenticazione === " . date('Y-m-d H:i:s'));

// Se è una richiesta OPTIONS, termina qui
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    error_log("Richiesta OPTIONS ricevuta, invio headers CORS");
    exit(0);
}

// Avvia la sessione
session_start();

// Verifica se l'utente è autenticato
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    error_log("Sessione non autenticata");
    http_response_code(401);
    echo json_encode(['authenticated' => false, 'message' => 'Utente non autenticato']);
    exit;
}

// Verifica se la sessione è scaduta (dopo 2 ore di inattività)
$sessionTimeout = 7200; // 2 ore in secondi
if (time() - $_SESSION['login_time'] > $sessionTimeout) {
    error_log("Sessione scaduta per utente: " . ($_SESSION['username'] ?? 'unknown'));
    session_unset();
    session_destroy();
    http_response_code(401);
    echo json_encode(['authenticated' => false, 'message' => 'Sessione scaduta, effettua di nuovo il login']);
    exit;
}

// Aggiorna il timestamp della sessione
$_SESSION['login_time'] = time();

// Restituisci informazioni sull'utente autenticato
error_log("Utente autenticato: " . $_SESSION['username']);
echo json_encode([
    'authenticated' => true,
    'user' => [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'role' => $_SESSION['role']
    ]
]); 