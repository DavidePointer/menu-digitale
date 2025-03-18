<?php
// Carica la configurazione
require_once '../config.php';

// Configura CORS
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Per le richieste OPTIONS, termina qui
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Abilita la visualizzazione di errori per il debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Connessione al database
try {
    // Connessione al database
    $db = getDBConnection();
    
    // Tabelle create
    $tablesCreated = [];
    
    // 1. Creazione tabella utenti (se non esiste)
    $query = "CREATE TABLE IF NOT EXISTS users (
        user_id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_login TIMESTAMP NULL
    )";
    $db->exec($query);
    $tablesCreated[] = 'users';
    
    // 2. Creazione tabella categorie (se non esiste)
    $query = "CREATE TABLE IF NOT EXISTS categories (
        category_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        image VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $db->exec($query);
    $tablesCreated[] = 'categories';
    
    // 3. Creazione tabella articoli (se non esiste)
    $query = "CREATE TABLE IF NOT EXISTS articles (
        article_id INT AUTO_INCREMENT PRIMARY KEY,
        category_id INT,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        image VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE CASCADE
    )";
    $db->exec($query);
    $tablesCreated[] = 'articles';
    
    // 4. Verifica se esiste già un utente admin
    $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE username = 'admin'");
    $stmt->execute();
    $adminExists = (int)$stmt->fetchColumn() > 0;
    
    // Se non esiste un utente admin, creane uno
    if (!$adminExists) {
        $defaultPassword = 'admin123';
        $hashedPassword = password_hash($defaultPassword, PASSWORD_DEFAULT);
        
        $stmt = $db->prepare("INSERT INTO users (username, password) VALUES ('admin', :password)");
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->execute();
        
        $tablesCreated[] = 'admin_user_created';
    }
    
    // 5. Verifica se esistono categorie e crea alcune categorie di esempio se non ce ne sono
    $stmt = $db->prepare("SELECT COUNT(*) FROM categories");
    $stmt->execute();
    $categoriesCount = (int)$stmt->fetchColumn();
    
    if ($categoriesCount === 0) {
        // Inserisci alcune categorie di esempio
        $categories = [
            ['name' => 'PIZZE', 'image' => 'pizza.jpg'],
            ['name' => 'PRIMI', 'image' => 'pasta.jpg'],
            ['name' => 'SECONDI', 'image' => 'meat.jpg'],
            ['name' => 'DESSERT', 'image' => 'dessert.jpg']
        ];
        
        $stmt = $db->prepare("INSERT INTO categories (name, image) VALUES (:name, :image)");
        
        foreach ($categories as $category) {
            $stmt->bindParam(':name', $category['name']);
            $stmt->bindParam(':image', $category['image']);
            $stmt->execute();
        }
        
        $tablesCreated[] = 'sample_categories_created';
    }
    
    // Successo
    http_response_code(200);
    echo json_encode([
        'success' => true, 
        'message' => 'Tabelle create con successo', 
        'tables_created' => $tablesCreated,
        'database_info' => [
            'server' => DB_SERVER,
            'name' => DB_NAME,
            'user' => DB_USER
        ]
    ]);
    
} catch(PDOException $e) {
    // Log dell'errore
    error_log('Database Error: ' . $e->getMessage());
    
    // Restituisci un errore
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Errore nella creazione delle tabelle',
        'error' => $e->getMessage(),
        'database_info' => [
            'server' => DB_SERVER,
            'name' => DB_NAME,
            'user' => DB_USER
        ]
    ]);
} 