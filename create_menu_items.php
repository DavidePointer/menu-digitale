<?php
require_once 'config.php';

try {
    $conn = getDBConnection();
    
    echo "Creazione della tabella 'menu_items'...\n";
    
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
            FOREIGN KEY (`category_id`) REFERENCES `categories`(`category_id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    
    echo "Tabella 'menu_items' creata con successo.\n";
    
} catch (PDOException $e) {
    echo "Errore: " . $e->getMessage();
}
?> 