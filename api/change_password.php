<?php
// Richiedi file di autenticazione
require_once 'auth.php';

// Headers CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Se è una richiesta OPTIONS, termina qui
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    logAuthMessage("Richiesta OPTIONS ricevuta, invio headers CORS");
    exit(0);
}

// Verifica autenticazione utente
if (!isAuthenticated()) {
    logAuthMessage("Tentativo di cambio password senza autenticazione");
    jsonResponse(false, 'Utente non autenticato');
}

// Verifica che il metodo sia POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    logAuthMessage("Metodo non consentito: " . $_SERVER['REQUEST_METHOD']);
    jsonResponse(false, 'Metodo non consentito');
}

try {
    // Ottieni il corpo della richiesta
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    // Verifica che il JSON sia valido
    if ($data === null) {
        logAuthMessage("JSON non valido ricevuto");
        jsonResponse(false, 'Dati di richiesta non validi');
    }
    
    // Verifica che le password siano presenti
    if (empty($data['currentPassword']) || empty($data['newPassword'])) {
        logAuthMessage("Password mancanti");
        jsonResponse(false, 'Password attuale e nuova sono obbligatorie');
    }
    
    // Verifica che la nuova password sia abbastanza forte
    if (strlen($data['newPassword']) < 8) {
        logAuthMessage("Password troppo corta");
        jsonResponse(false, 'La nuova password deve essere di almeno 8 caratteri');
    }
    
    // Carica il file di configurazione
    require_once '../config.php';
    
    try {
        // Connessione al database
        $pdo = getDBConnection();
        
        // Ottieni l'utente corrente dal database
        $stmt = $pdo->prepare("SELECT user_id, username, password_hash FROM users WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            logAuthMessage("Utente non trovato: " . $_SESSION['user_id']);
            jsonResponse(false, 'Errore interno del server');
        }
        
        // Verifica la password attuale
        if (!password_verify($data['currentPassword'], $user['password_hash'])) {
            logAuthMessage("Password attuale non valida per l'utente: " . $user['username']);
            jsonResponse(false, 'Password attuale non valida');
        }
        
        // Genera hash della nuova password
        $newPasswordHash = password_hash($data['newPassword'], PASSWORD_DEFAULT);
        
        // Aggiorna la password nel database
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
        $result = $stmt->execute([$newPasswordHash, $_SESSION['user_id']]);
        
        if ($result) {
            logAuthMessage("Password aggiornata con successo per l'utente: " . $user['username']);
            jsonResponse(true, 'Password aggiornata con successo');
        } else {
            logAuthMessage("Errore nell'aggiornamento della password per l'utente: " . $user['username']);
            jsonResponse(false, 'Errore nell\'aggiornamento della password');
        }
        
    } catch (PDOException $e) {
        logAuthMessage("Errore database: " . $e->getMessage());
        jsonResponse(false, 'Errore interno del server');
    }
    
} catch (Exception $e) {
    logAuthMessage("Errore generale: " . $e->getMessage());
    jsonResponse(false, 'Errore interno del server');
} 