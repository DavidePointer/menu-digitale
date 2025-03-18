<?php
// Richiedi autenticazione
require_once 'auth_check.php';

// Headers CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Configurazione dei log
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/auth.log');
error_log("=== Richiesta cambio password === " . date('Y-m-d H:i:s') . " - Utente: " . $_SESSION['username']);

// Se è una richiesta OPTIONS, termina qui
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    error_log("Richiesta OPTIONS ricevuta, invio headers CORS");
    exit(0);
}

// Verifica che il metodo sia POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("Metodo non consentito: " . $_SERVER['REQUEST_METHOD']);
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metodo non consentito']);
    exit;
}

try {
    // Ottieni il corpo della richiesta
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    // Verifica che il JSON sia valido
    if ($data === null) {
        error_log("JSON non valido ricevuto");
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Dati di richiesta non validi']);
        exit;
    }
    
    // Verifica che le password siano presenti
    if (empty($data['currentPassword']) || empty($data['newPassword'])) {
        error_log("Password mancanti");
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Password attuale e nuova sono obbligatorie']);
        exit;
    }
    
    // Verifica che la nuova password sia abbastanza forte
    if (strlen($data['newPassword']) < 8) {
        error_log("Password troppo corta");
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'La nuova password deve essere di almeno 8 caratteri']);
        exit;
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
            error_log("Utente non trovato: " . $_SESSION['user_id']);
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Errore interno del server']);
            exit;
        }
        
        // Verifica la password attuale
        if (!password_verify($data['currentPassword'], $user['password_hash'])) {
            error_log("Password attuale non valida per l'utente: " . $user['username']);
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Password attuale non valida']);
            exit;
        }
        
        // Genera hash della nuova password
        $newPasswordHash = password_hash($data['newPassword'], PASSWORD_DEFAULT);
        
        // Aggiorna la password nel database
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
        $result = $stmt->execute([$newPasswordHash, $_SESSION['user_id']]);
        
        if ($result) {
            error_log("Password aggiornata con successo per l'utente: " . $user['username']);
            echo json_encode(['success' => true, 'message' => 'Password aggiornata con successo']);
        } else {
            error_log("Errore nell'aggiornamento della password per l'utente: " . $user['username']);
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Errore nell\'aggiornamento della password']);
        }
        
    } catch (PDOException $e) {
        error_log("Errore database: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Errore interno del server']);
    }
    
} catch (Exception $e) {
    error_log("Errore generale: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Errore interno del server']);
} 