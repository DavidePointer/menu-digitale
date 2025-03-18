<?php
require_once 'config.php';

try {
    $conn = getDBConnection();
    $stmt = $conn->query('SHOW TABLES');
    
    echo "Tabelle nel database:\n";
    $tableFound = false;
    while($row = $stmt->fetch(PDO::FETCH_NUM)) {
        echo "- " . $row[0] . "\n";
        $tableFound = true;
    }
    
    if (!$tableFound) {
        echo "Nessuna tabella trovata nel database.\n";
        echo "Creazione della tabella 'settings'...\n";
        
        // Crea la tabella settings
        $conn->exec("
            CREATE TABLE IF NOT EXISTS `settings` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `setting_key` VARCHAR(100) NOT NULL UNIQUE,
                `setting_value` TEXT,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        echo "Tabella 'settings' creata con successo.\n";
        
        // Inserisci le impostazioni di default
        $defaultSettings = [
            ['site_name', 'Pointer - Menu Digitale'],
            ['site_tagline', 'Soluzioni evolute per il punto cassa'],
            ['logo_url', '/menu_digitale/images/Logo-Pointer.jpg'],
            ['primary_color', '#1A3C40'],
            ['accent_color', '#E76F51'],
            ['address', 'Via Trieste 42, Udine'],
            ['phone', '0432 111111'],
            ['email', 'info@pointer.it'],
            ['weekday_hours', '8:30-12:30, 14:30-18:30'],
            ['weekend_hours', 'Chiuso']
        ];
        
        $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
        foreach ($defaultSettings as $setting) {
            $stmt->execute($setting);
        }
        
        echo "Impostazioni predefinite inserite.\n";
    }
    
    // Verifica se esistono le tabelle necessarie per il menu
    $stmt = $conn->query("SHOW TABLES LIKE 'categories'");
    if ($stmt->rowCount() == 0) {
        echo "Tabella 'categories' non trovata. Creazione in corso...\n";
        
        $conn->exec("
            CREATE TABLE IF NOT EXISTS `categories` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(100) NOT NULL,
                `image_url` VARCHAR(255),
                `display_order` INT DEFAULT 0,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        echo "Tabella 'categories' creata con successo.\n";
    }
    
    $stmt = $conn->query("SHOW TABLES LIKE 'menu_items'");
    if ($stmt->rowCount() == 0) {
        echo "Tabella 'menu_items' non trovata. Creazione in corso...\n";
        
        $conn->exec("
            CREATE TABLE IF NOT EXISTS `menu_items` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `category_id` INT NOT NULL,
                `name` VARCHAR(100) NOT NULL,
                `description` TEXT,
                `price` DECIMAL(10,2) NOT NULL,
                `image_url` VARCHAR(255),
                `is_available` TINYINT(1) DEFAULT 1,
                `display_order` INT DEFAULT 0,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        echo "Tabella 'menu_items' creata con successo.\n";
    }
    
} catch (PDOException $e) {
    echo "Errore: " . $e->getMessage();
}
?> 