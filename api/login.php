<?php
// api/login.php - API per il login
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Includi il file di autenticazione
require_once 'auth.php';

// Directory per i log
$logDir = __DIR__ . '/../logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

// File di log per l'autenticazione
$logFile = $logDir . '/auth.log';
$logMessage = date('Y-m-d H:i:s') . " - Inizio richiesta login\n";
file_put_contents($logFile, $logMessage, FILE_APPEND);

// Se è una richiesta OPTIONS, termina qui
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    $logMessage = date('Y-m-d H:i:s') . " - Richiesta OPTIONS ricevuta, invio headers CORS\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
    exit(0);
}

// Verifica che il metodo sia POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $logMessage = date('Y-m-d H:i:s') . " - Metodo non consentito: " . $_SERVER['REQUEST_METHOD'] . "\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
    jsonResponse(false, 'Metodo non consentito');
}

// Verifica che i dati siano in formato JSON
$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
if (strpos($contentType, 'application/json') !== false) {
    // Ottieni il corpo della richiesta
    $json = file_get_contents('php://input');
    
    // Decodifica il JSON
    $data = json_decode($json, true);
    
    // Verifica che il JSON sia valido
    if (json_last_error() !== JSON_ERROR_NONE) {
        $logMessage = date('Y-m-d H:i:s') . " - JSON non valido: " . json_last_error_msg() . "\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);
        jsonResponse(false, 'Dati di richiesta non validi');
    }
} else {
    $logMessage = date('Y-m-d H:i:s') . " - Formato richiesta non valido. Atteso JSON.\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
    jsonResponse(false, 'Formato richiesta non valido. Atteso JSON.');
}

// Verifica che username e password siano presenti
if (empty($data['username']) || empty($data['password'])) {
    $logMessage = date('Y-m-d H:i:s') . " - Username o password mancanti\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
    jsonResponse(false, 'Username e password sono obbligatori');
}

try {
    // Carica il file di configurazione
    require_once '../config.php';
    
    // Connessione al database
    $pdo = getDBConnection();
    
    // Sanitizza i dati
    $username = htmlspecialchars(strip_tags($data['username']));
    $password = $data['password']; // Non sanitizziamo la password
    
    // Verifica esistenza della tabella users
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() == 0) {
        // Crea tabella users e inserisci admin predefinito
        createUsersTable($pdo);
    }
    
    // Query per cercare l'utente
    $stmt = $pdo->prepare("SELECT user_id, username, password_hash, email, role FROM users WHERE username = ?");
    $stmt->execute([$username]);
    
    // Verifica se l'utente esiste
    if ($stmt->rowCount() == 0) {
        $logMessage = date('Y-m-d H:i:s') . " - Utente non trovato: $username\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);
        jsonResponse(false, 'Credenziali non valide');
    }
    
    // Ottieni i dati dell'utente
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Controlla se le credenziali sono valide
    if (verifyPassword($password, $user['password_hash'])) {
        // Inizializza la sessione se non è già attiva
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Credenziali valide, imposta le variabili di sessione
        $_SESSION['logged_in'] = true;
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['login_time'] = time();
        
        // Genera un token di autenticazione
        $token = generateToken();
        
        // Aggiorna la data di ultimo accesso nel database
        $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
        $stmt->execute([$user['user_id']]);
        
        // Registra il login
        $logMessage = date('Y-m-d H:i:s') . " - Login riuscito per utente: " . $user['username'] . "\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);
        
        // Restituisci successo con i dati dell'utente
        jsonResponse(true, 'Login effettuato con successo', [
            'user_id' => $user['user_id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role' => $user['role']
        ]);
    }
    
    // Credenziali non valide
    $logMessage = date('Y-m-d H:i:s') . " - Tentativo di login fallito per username: $username\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
    jsonResponse(false, 'Credenziali non valide');
    
} catch (PDOException $e) {
    $logMessage = date('Y-m-d H:i:s') . " - Errore di connessione al database: " . $e->getMessage() . "\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
    jsonResponse(false, 'Errore durante l\'accesso. Riprova più tardi.');
}

/**
 * Crea la tabella users con l'admin predefinito
 */
function createUsersTable($pdo) {
    global $logFile;
    $logMessage = date('Y-m-d H:i:s') . " - Creazione tabella users...\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
    
    // Crea tabella users
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        user_id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        email VARCHAR(100),
        role VARCHAR(20) NOT NULL DEFAULT 'editor',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_login TIMESTAMP NULL
    )");
    
    // Inserisci admin predefinito (username: admin, password: admin123)
    $adminUsername = 'admin';
    $adminPassword = 'admin123';
    $adminPasswordHash = hashPassword($adminPassword);
    
    $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, 'admin')");
    $stmt->execute([$adminUsername, $adminPasswordHash]);
    
    $logMessage = date('Y-m-d H:i:s') . " - Creato utente admin predefinito con username: admin\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
} 