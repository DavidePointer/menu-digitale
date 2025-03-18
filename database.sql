CREATE DATABASE IF NOT EXISTS menu_db;
USE menu_db;

CREATE TABLE IF NOT EXISTS categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    image VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS articles (
    article_id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    FOREIGN KEY (category_id) REFERENCES categories(category_id)
);

CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Inserimento amministratore predefinito con password 'admin123'
INSERT INTO users (username, password) 
VALUES ('admin', '$2y$10$i9KjspSMuP5GvhJoSQ.Bfu0DoXbHbLShYHY.VQq6KCgFIjEKCTD0m');

-- Nota: Gli indici e le chiavi esterne verranno create automaticamente
-- Le tabelle possono essere estese in futuro per il supporto multi-tenant 