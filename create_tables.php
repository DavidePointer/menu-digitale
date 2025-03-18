<?php
// create_tables.php - Script per creare le tabelle necessarie nel database

require_once 'config.php';

header('Content-Type: text/html; charset=utf-8');
echo "<h1>Creazione Tabelle Database</h1>";

try {
    $pdo = getDBConnection();
    echo "<p>✅ Connessione al database riuscita</p>";
    
    // Crea tabella categorie
    $sqlCategories = "CREATE TABLE IF NOT EXISTS categories (
        category_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        image_url VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($sqlCategories);
    echo "<p>✅ Tabella 'categories' creata o già esistente</p>";
    
    // Verifica se esistono categorie
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM categories");
    $categoriesCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($categoriesCount === 0) {
        // Inserisci alcune categorie di esempio
        $sampleCategories = [
            ['Antipasti', 'images/categories/antipasti.jpg'],
            ['Primi', 'images/categories/primi.jpg'],
            ['Secondi', 'images/categories/secondi.jpg'],
            ['Contorni', 'images/categories/contorni.jpg'],
            ['Dessert', 'images/categories/dessert.jpg'],
            ['Bevande', 'images/categories/bevande.jpg']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO categories (name, image_url) VALUES (?, ?)");
        
        foreach ($sampleCategories as $category) {
            $stmt->execute($category);
        }
        
        echo "<p>✅ Categorie di esempio inserite</p>";
    } else {
        echo "<p>ℹ️ Categorie già presenti nel database: " . $categoriesCount . "</p>";
    }
    
    // Crea tabella articoli
    $sqlArticles = "CREATE TABLE IF NOT EXISTS articles (
        article_id INT AUTO_INCREMENT PRIMARY KEY,
        category_id INT NOT NULL,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        price DECIMAL(10, 2) NOT NULL,
        image_url VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE CASCADE
    )";
    
    $pdo->exec($sqlArticles);
    echo "<p>✅ Tabella 'articles' creata o già esistente</p>";
    
    // Verifica se esistono articoli
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM articles");
    $articlesCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($articlesCount === 0) {
        // Ottieni gli id delle categorie
        $stmt = $pdo->query("SELECT category_id, name FROM categories");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $categoryIds = [];
        
        foreach ($categories as $category) {
            $categoryIds[$category['name']] = $category['category_id'];
        }
        
        // Inserisci alcuni articoli di esempio
        $sampleArticles = [];
        
        if (isset($categoryIds['Antipasti'])) {
            $sampleArticles[] = [$categoryIds['Antipasti'], 'Bruschetta', 'Pane tostato con pomodoro, aglio e basilico', 5.50, 'images/articles/bruschetta.jpg'];
            $sampleArticles[] = [$categoryIds['Antipasti'], 'Caprese', 'Mozzarella, pomodoro e basilico', 7.50, 'images/articles/caprese.jpg'];
        }
        
        if (isset($categoryIds['Primi'])) {
            $sampleArticles[] = [$categoryIds['Primi'], 'Spaghetti alla Carbonara', 'Spaghetti con uova, guanciale, pecorino e pepe', 12.00, 'images/articles/carbonara.jpg'];
            $sampleArticles[] = [$categoryIds['Primi'], 'Risotto ai Funghi', 'Riso carnaroli con funghi porcini', 13.50, 'images/articles/risotto.jpg'];
        }
        
        if (isset($categoryIds['Secondi'])) {
            $sampleArticles[] = [$categoryIds['Secondi'], 'Bistecca alla Fiorentina', 'Bistecca di manzo alla griglia', 22.00, 'images/articles/bistecca.jpg'];
            $sampleArticles[] = [$categoryIds['Secondi'], 'Pollo arrosto', 'Pollo arrosto con patate e rosmarino', 15.00, 'images/articles/pollo.jpg'];
        }
        
        $stmt = $pdo->prepare("INSERT INTO articles (category_id, name, description, price, image_url) VALUES (?, ?, ?, ?, ?)");
        
        foreach ($sampleArticles as $article) {
            $stmt->execute($article);
        }
        
        echo "<p>✅ Articoli di esempio inseriti</p>";
    } else {
        echo "<p>ℹ️ Articoli già presenti nel database: " . $articlesCount . "</p>";
    }
    
    echo "<h2>Creazione tabelle completata con successo!</h2>";
    echo "<p><a href='admin.html'>Vai al pannello di amministrazione</a></p>";
    echo "<p><a href='index.html'>Vai alla pagina principale</a></p>";
    
} catch (PDOException $e) {
    echo "<h2>❌ Errore:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
} 