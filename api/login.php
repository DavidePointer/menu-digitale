<?php
// Headers CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Configurazione dei log
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/auth.log');
if (!file_exists(__DIR__ . '/../logs')) {
    mkdir(__DIR__ . '/../logs', 0777, true);
}
error_log("=== Inizio richiesta login.php === " . date('Y-m-d H:i:s'));
error_log("Metodo: " . $_SERVER['REQUEST_METHOD']);

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
    
    error_log("Dati di login ricevuti per username: " . ($data['username'] ?? 'non fornito'));
    
    // Verifica che il JSON sia valido
    if ($data === null) {
        error_log("JSON non valido ricevuto");
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Dati di richiesta non validi']);
        exit;
    }
    
    // Verifica che username e password siano presenti
    if (empty($data['username']) || empty($data['password'])) {
        error_log("Username o password mancanti");
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Username e password sono obbligatori']);
        exit;
    }
    
    // Carica il file di configurazione
    require_once '../config.php';
    
    try {
        // Connessione al database
        $pdo = getDBConnection();
        
        // Verifica esistenza della tabella users
        $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
        if ($stmt->rowCount() == 0) {
            // Crea tabella users e inserisci admin predefinito
            createUsersTable($pdo);
        }
        
        // Verifica credenziali
        $stmt = $pdo->prepare("SELECT user_id, username, password_hash, role FROM users WHERE username = ?");
        $stmt->execute([$data['username']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user || !password_verify($data['password'], $user['password_hash'])) {
            error_log("Tentativo di login fallito per: " . $data['username']);
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Credenziali non valide']);
            exit;
        }
        
        // Login riuscito, genera un token di sessione
        session_start();
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
        
        error_log("Login riuscito per: " . $user['username']);
        echo json_encode([
            'success' => true, 
            'message' => 'Login eseguito con successo',
            'user' => [
                'id' => $user['user_id'],
                'username' => $user['username'],
                'role' => $user['role']
            ]
        ]);
        
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

/**
 * Crea la tabella users con l'admin predefinito
 */
function createUsersTable($pdo) {
    error_log("Creazione tabella users...");
    
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
    $adminPasswordHash = password_hash($adminPassword, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, 'admin')");
    $stmt->execute([$adminUsername, $adminPasswordHash]);
    
    error_log("Creato utente admin predefinito con username: admin");
} 