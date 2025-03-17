<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Connessione al server SQL Server
    $dsn = "sqlsrv:Server=MSI-POINTER\\SQLEXPRESS";
    $pdo = new PDO($dsn, "", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Crea il database se non esiste
    $pdo->exec("IF NOT EXISTS (SELECT * FROM sys.databases WHERE name = 'menu_db')
                BEGIN
                    CREATE DATABASE menu_db
                END");
    echo "Database menu_db creato o già esistente<br>";
    
    // Riconnettiti al database specifico
    $pdo = new PDO("sqlsrv:Server=MSI-POINTER\\SQLEXPRESS;Database=menu_db", "", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Crea la tabella categories se non esiste
    $sql = "IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='categories' and xtype='U')
            BEGIN
                CREATE TABLE categories (
                    category_id INT IDENTITY(1,1) PRIMARY KEY,
                    name NVARCHAR(100) NOT NULL,
                    image_url NVARCHAR(255) NOT NULL
                )
            END";
    
    $pdo->exec($sql);
    echo "Tabella categories creata o già esistente<br>";
    
    // Crea la tabella articles se non esiste
    $sql = "IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='articles' and xtype='U')
            BEGIN
                CREATE TABLE articles (
                    article_id INT IDENTITY(1,1) PRIMARY KEY,
                    category_id INT NOT NULL,
                    name NVARCHAR(100) NOT NULL,
                    description NTEXT,
                    price DECIMAL(10,2) NOT NULL,
                    image_url NVARCHAR(255) NOT NULL,
                    CONSTRAINT FK_Articles_Categories FOREIGN KEY (category_id) 
                    REFERENCES categories(category_id)
                )
            END";
    
    $pdo->exec($sql);
    echo "Tabella articles creata o già esistente<br>";
    
    echo "<br>Setup completato con successo!";
    
} catch (PDOException $e) {
    die("Errore: " . $e->getMessage());
} 