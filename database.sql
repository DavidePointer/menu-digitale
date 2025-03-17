CREATE DATABASE IF NOT EXISTS menu_db;
USE menu_db;

CREATE TABLE IF NOT EXISTS categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    image_url VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS articles (
    article_id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    FOREIGN KEY (category_id) REFERENCES categories(category_id)
); 