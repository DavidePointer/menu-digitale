<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

echo "<h1>Test Dati Database</h1>";

try {
    $pdo = getDBConnection();
    
    echo "<h2>Categorie</h2>";
    $stmt = $pdo->query("SELECT * FROM categories");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($categories) > 0) {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Nome</th><th>Immagine</th></tr>";
        foreach ($categories as $category) {
            echo "<tr>";
            echo "<td>" . $category['category_id'] . "</td>";
            echo "<td>" . $category['name'] . "</td>";
            echo "<td>" . $category['image_url'] . " <img src='" . $category['image_url'] . "' height='50'></td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Nessuna categoria trovata.</p>";
    }
    
    echo "<h2>Articoli</h2>";
    $stmt = $pdo->query("SELECT a.*, c.name as category_name FROM articles a JOIN categories c ON a.category_id = c.category_id");
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($articles) > 0) {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Categoria</th><th>Nome</th><th>Descrizione</th><th>Prezzo</th><th>Immagine</th></tr>";
        foreach ($articles as $article) {
            echo "<tr>";
            echo "<td>" . $article['article_id'] . "</td>";
            echo "<td>" . $article['category_name'] . " (ID: " . $article['category_id'] . ")</td>";
            echo "<td>" . $article['name'] . "</td>";
            echo "<td>" . $article['description'] . "</td>";
            echo "<td>€" . number_format($article['price'], 2) . "</td>";
            echo "<td>" . $article['image_url'] . " <img src='" . $article['image_url'] . "' height='50'></td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Nessun articolo trovato.</p>";
    }
    
} catch (PDOException $e) {
    echo "<h2>Errore durante l'accesso ai dati:</h2>";
    echo $e->getMessage();
}
?> 