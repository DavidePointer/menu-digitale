<?php
// api/verify_token.php - Verifica il token di autenticazione

// Includi il file di autenticazione
require_once 'auth.php';

// Headers CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Gestisci le richieste OPTIONS per il preflight CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

// Log della richiesta
logAuthMessage("Verifica token richiesta - Metodo: " . $_SERVER['REQUEST_METHOD']);

// Debug - Registra tutti i dati della richiesta
$headers = getallheaders();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : 'Nessun header Authorization';
logAuthMessage("Header Authorization: " . $authHeader);

// Per le richieste POST, registra anche il corpo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawInput = file_get_contents('php://input');
    logAuthMessage("Body richiesta POST: " . $rawInput);
}

// Ottieni il token dalla richiesta
$token = getTokenFromRequest();

if (!$token) {
    logAuthMessage("Token non fornito");
    jsonResponse(false, 'Token non fornito');
}

logAuthMessage("Token ricevuto: " . $token);

// Verifica che la sessione sia attiva
if (session_status() !== PHP_SESSION_ACTIVE) {
    logAuthMessage("Sessione non attiva, tento di avviarla");
    session_start();
}

// Verifica che l'utente sia loggato
if (!isAuthenticated()) {
    logAuthMessage("Sessione non valida o utente non loggato. Stato sessione: " . print_r($_SESSION, true));
    jsonResponse(false, 'Sessione non valida');
}

// Verifica che il token corrisponda a quello nella sessione dell'utente autenticato
if (!verifyToken($token)) {
    logAuthMessage("Token non valido: " . $token . " vs " . ($_SESSION['auth_token'] ?? 'non impostato'));
    jsonResponse(false, 'Token non valido');
}

// Token valido, aggiorna il timestamp di ultima attività
$_SESSION['last_activity'] = time();

logAuthMessage("Token valido per utente: " . $_SESSION['username']);

// Token valido
jsonResponse(true, 'Token valido', array(
    'username' => $_SESSION['username'],
    'email' => isset($_SESSION['email']) ? $_SESSION['email'] : '',
    'role' => isset($_SESSION['role']) ? $_SESSION['role'] : 'user'
)); 