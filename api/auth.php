<?php
// api/auth.php - Funzioni di autenticazione e gestione token

// Configurazione sessione
ini_set('session.cookie_path', '/');
ini_set('session.cookie_domain', '');
ini_set('session.cookie_secure', false);
ini_set('session.cookie_httponly', true);
ini_set('session.use_only_cookies', true);
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.gc_maxlifetime', 7200); // 2 ore

// Header per CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Gestisci le richieste OPTIONS per il preflight CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

// Inizializza la sessione
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Directory per i log
$logDir = __DIR__ . '/../logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

// File di log per l'autenticazione
$authLogFile = $logDir . '/auth.log';

/**
 * Verifica se la sessione è attiva e registra informazioni di debug
 * 
 * @return void
 */
function debugSessionStatus() {
    $status = session_status();
    $statusText = '';
    
    switch ($status) {
        case PHP_SESSION_DISABLED:
            $statusText = 'Le sessioni sono disabilitate';
            break;
        case PHP_SESSION_NONE:
            $statusText = 'Le sessioni sono abilitate, ma nessuna sessione è attiva';
            break;
        case PHP_SESSION_ACTIVE:
            $statusText = 'Le sessioni sono abilitate e una sessione è attiva';
            break;
    }
    
    logAuthMessage("Stato sessione: " . $statusText . " (" . $status . ")");
    
    if ($status === PHP_SESSION_ACTIVE) {
        logAuthMessage("ID Sessione: " . session_id());
        logAuthMessage("Dati sessione: " . print_r($_SESSION, true));
    }
}

/**
 * Verifica se l'utente è autenticato
 * 
 * @return bool True se l'utente è autenticato, False altrimenti
 */
function isAuthenticated() {
    // Debug della sessione
    debugSessionStatus();
    
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        logAuthMessage("Utente non autenticato: variabile logged_in non presente o non true");
        return false;
    }
    
    if (!isset($_SESSION['auth_token'])) {
        logAuthMessage("Utente non autenticato: token non presente in sessione");
        return false;
    }
    
    if (!isset($_SESSION['user_id'])) {
        logAuthMessage("Utente non autenticato: user_id non presente in sessione");
        return false;
    }
    
    logAuthMessage("Utente autenticato: " . $_SESSION['username']);
    return true;
}

/**
 * Recupera il token dalla richiesta
 * 
 * @return string|null Il token se presente, null altrimenti
 */
function getTokenFromRequest() {
    $headers = getallheaders();
    
    // Se il token è nell'header di autorizzazione
    if (isset($headers['Authorization'])) {
        // Formato dell'header: "Bearer TOKEN"
        $authHeader = $headers['Authorization'];
        
        // Estrai il token
        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $matches[1];
        }
    }
    
    // Se il token è nel corpo della richiesta
    $input = json_decode(file_get_contents('php://input'), true);
    if (isset($input['token'])) {
        return $input['token'];
    }
    
    // Se il token è nei parametri GET
    if (isset($_GET['token'])) {
        return $_GET['token'];
    }
    
    return null;
}

/**
 * Verifica la validità del token
 * 
 * @param string $token Il token da verificare
 * @return bool True se il token è valido, False altrimenti
 */
function verifyToken($token) {
    // Verifica che la sessione sia attiva e che l'utente sia loggato
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        logAuthMessage("verifyToken: utente non autenticato");
        return false;
    }
    
    // Verifica che ci sia un token in sessione
    if (!isset($_SESSION['auth_token'])) {
        logAuthMessage("verifyToken: token non presente in sessione");
        return false;
    }
    
    // Confronta i token
    $valid = $token === $_SESSION['auth_token'];
    
    if ($valid) {
        logAuthMessage("verifyToken: token valido");
    } else {
        logAuthMessage("verifyToken: token non valido. In sessione: " . substr($_SESSION['auth_token'], 0, 10) . "..., ricevuto: " . substr($token, 0, 10) . "...");
    }
    
    return $valid;
}

/**
 * Genera un nuovo token di autenticazione
 * 
 * @return string Il token generato
 */
function generateToken() {
    // Genera un token casuale di 64 caratteri
    $token = bin2hex(random_bytes(32));
    
    // Salva il token nella sessione
    $_SESSION['auth_token'] = $token;
    
    // Aggiungi log
    global $authLogFile;
    $logMessage = date('Y-m-d H:i:s') . " - Nuovo token generato per utente: " . 
                 (isset($_SESSION['username']) ? $_SESSION['username'] : 'sconosciuto') . "\n";
    file_put_contents($authLogFile, $logMessage, FILE_APPEND);
    
    return $token;
}

/**
 * Crea una risposta JSON
 * 
 * @param bool $success Indica se l'operazione è stata completata con successo
 * @param string $message Messaggio da includere nella risposta
 * @param array|null $data Dati aggiuntivi da includere nella risposta
 * @return void
 */
function jsonResponse($success, $message = '', $data = null) {
    $response = array(
        'success' => $success,
        'message' => $message
    );
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    // Se richiesta autenticata, includi il token nella risposta
    if ($success && isAuthenticated()) {
        $response['auth_token'] = $_SESSION['auth_token'];
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

/**
 * Registra un messaggio di log
 * 
 * @param string $message Il messaggio da registrare
 * @return void
 */
function logAuthMessage($message) {
    global $authLogFile;
    $logMessage = date('Y-m-d H:i:s') . " - " . $message . "\n";
    file_put_contents($authLogFile, $logMessage, FILE_APPEND);
}

// Funzioni ausiliarie per la sicurezza

/**
 * Genera un hash sicuro per le password
 * 
 * @param string $password La password da hashare
 * @return string L'hash della password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verifica se una password corrisponde al suo hash
 * 
 * @param string $password La password da verificare
 * @param string $hash L'hash a cui confrontare la password
 * @return bool True se la password corrisponde, False altrimenti
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Gestione della richiesta API se questo file viene chiamato direttamente
if (basename($_SERVER['SCRIPT_FILENAME']) == basename(__FILE__)) {
    // Inizializza la risposta
    $response = array(
        'success' => false,
        'message' => 'Operazione non supportata'
    );
    
    // Se è una richiesta GET, restituisci lo stato dell'autenticazione
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $response['success'] = isAuthenticated();
        $response['message'] = isAuthenticated() ? 'Utente autenticato' : 'Utente non autenticato';
        
        if (isAuthenticated()) {
            $response['data'] = array(
                'username' => $_SESSION['username'],
                'email' => isset($_SESSION['email']) ? $_SESSION['email'] : '',
                'role' => isset($_SESSION['role']) ? $_SESSION['role'] : 'user'
            );
        }
    }
    
    // Restituisci la risposta come JSON
    header('Content-Type: application/json');
    echo json_encode($response);
} 