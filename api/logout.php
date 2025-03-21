<?php
// api/logout.php - API per il logout
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Includi il file di autenticazione
require_once 'auth.php';

// Se è una richiesta OPTIONS, termina qui
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    logAuthMessage("Richiesta OPTIONS ricevuta, invio headers CORS");
    exit(0);
}

// Registra l'inizio della richiesta di logout
logAuthMessage("=== Richiesta di logout ===");

// Inizializza la sessione se non è già attiva
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Registra l'informazione su chi ha effettuato il logout
if (isset($_SESSION['username'])) {
    logAuthMessage("Logout utente: " . $_SESSION['username']);
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

// Restituisci una risposta di successo utilizzando la funzione da auth.php
jsonResponse(true, 'Logout eseguito con successo');
