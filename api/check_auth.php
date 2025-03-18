<?php
// Headers CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Includi il file di autenticazione
require_once 'auth.php';

// Se è una richiesta OPTIONS, termina qui
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    logAuthMessage("Richiesta OPTIONS ricevuta, invio headers CORS");
    exit(0);
}

// Log della richiesta
logAuthMessage("=== Verifica autenticazione ===");

// Verifica se l'utente è autenticato
if (!isAuthenticated()) {
    logAuthMessage("Sessione non autenticata");
    
    // Restituisci risposta di errore
    jsonResponse(false, 'Utente non autenticato', ['authenticated' => false]);
}

// Verifica se la sessione è scaduta (dopo 2 ore di inattività)
$sessionTimeout = 7200; // 2 ore in secondi
if (time() - $_SESSION['login_time'] > $sessionTimeout) {
    logAuthMessage("Sessione scaduta per utente: " . ($_SESSION['username'] ?? 'unknown'));
    
    // Rimuovi la sessione
    session_unset();
    session_destroy();
    
    // Restituisci risposta di errore
    jsonResponse(false, 'Sessione scaduta, effettua di nuovo il login', ['authenticated' => false]);
}

// Aggiorna il timestamp della sessione
$_SESSION['login_time'] = time();

// Restituisci informazioni sull'utente autenticato
logAuthMessage("Utente autenticato: " . $_SESSION['username']);

// Restituisci risposta con i dati dell'utente
jsonResponse(true, 'Utente autenticato', [
    'authenticated' => true,
    'user' => [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'role' => $_SESSION['role']
    ]
]); 