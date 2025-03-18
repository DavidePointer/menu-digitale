<?php
// Headers CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Configurazione dei log
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/auth.log');
error_log("=== Richiesta di logout === " . date('Y-m-d H:i:s'));

// Se è una richiesta OPTIONS, termina qui
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    error_log("Richiesta OPTIONS ricevuta, invio headers CORS");
    exit(0);
}

// Avvia la sessione
session_start();

// Registra il logout
if (isset($_SESSION['username'])) {
    error_log("Logout utente: " . $_SESSION['username']);
}

// Distruggi la sessione
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

// Restituisci una risposta di successo
echo json_encode([
    'success' => true,
    'message' => 'Logout eseguito con successo'
]); 