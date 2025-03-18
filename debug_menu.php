<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Debug Menu Digitale</h1>";

require_once 'config.php';

try {
    echo "<h2>Connessione al Database</h2>";
    $pdo = getDBConnection();
    echo "<p style='color:green'>✅ Connessione al database MySQL riuscita</p>";
    
    // Informazioni di connessione
    echo "<h3>Informazioni di connessione:</h3>";
    echo "<ul>";
    echo "<li>Server: " . DB_SERVER . "</li>";
    echo "<li>Database: " . DB_NAME . "</li>";
    echo "<li>Utente: " . DB_USER . "</li>";
    echo "</ul>";
    
    // Verifica tabelle
    echo "<h2>Struttura del Database</h2>";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h3>Tabelle presenti:</h3>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>" . $table . "</li>";
    }
    echo "</ul>";
    
    // Verifica categorie
    echo "<h2>Categorie</h2>";
    $stmt = $pdo->query("SELECT * FROM categories");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($categories) > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Nome</th><th>Immagine</th><th>URL_Name (generato)</th></tr>";
        foreach ($categories as $category) {
            $url_name = strtolower(str_replace(' ', '_', $category['name']));
            echo "<tr>";
            echo "<td>" . $category['category_id'] . "</td>";
            echo "<td>" . $category['name'] . "</td>";
            echo "<td>" . $category['image_url'] . " <img src='" . $category['image_url'] . "' height='50'></td>";
            echo "<td>" . $url_name . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Verifica articoli per ogni categoria
        foreach ($categories as $category) {
            $category_id = $category['category_id'];
            $category_name = $category['name'];
            $url_name = strtolower(str_replace(' ', '_', $category_name));
            
            echo "<h3>Articoli per categoria: " . $category_name . " (ID: " . $category_id . ")</h3>";
            
            $stmt = $pdo->prepare("SELECT * FROM articles WHERE category_id = ?");
            $stmt->execute([$category_id]);
            $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($articles) > 0) {
                echo "<table border='1' cellpadding='5'>";
                echo "<tr><th>ID</th><th>Nome</th><th>Descrizione</th><th>Prezzo</th><th>Immagine</th></tr>";
                foreach ($articles as $article) {
                    echo "<tr>";
                    echo "<td>" . $article['article_id'] . "</td>";
                    echo "<td>" . $article['name'] . "</td>";
                    echo "<td>" . $article['description'] . "</td>";
                    echo "<td>€" . number_format($article['price'], 2) . "</td>";
                    echo "<td>" . $article['image_url'] . " <img src='" . $article['image_url'] . "' height='50'></td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>Nessun articolo trovato per questa categoria.</p>";
            }
            
            // Test API
            echo "<h4>Test API per categoria: " . $url_name . "</h4>";
            echo "<p>URL API: /api/menu.php?category=" . $url_name . "</p>";
            
            try {
                $apiUrl = "http://localhost/menu_digitale/api/menu.php?category=" . urlencode($url_name);
                $response = @file_get_contents($apiUrl);
                if ($response === false) {
                    echo "<p style='color:red'>❌ Errore nel chiamare l'API</p>";
                    if (isset($http_response_header)) {
                        echo "<pre>";
                        print_r($http_response_header);
                        echo "</pre>";
                    }
                } else {
                    $data = json_decode($response, true);
                    echo "<p>Risposta API:</p>";
                    echo "<pre>";
                    print_r($data);
                    echo "</pre>";
                }
            } catch (Exception $e) {
                echo "<p style='color:red'>❌ Eccezione: " . $e->getMessage() . "</p>";
            }
        }
    } else {
        echo "<p>Nessuna categoria trovata.</p>";
    }
    
    // Verifica file JavaScript
    echo "<h2>File JavaScript</h2>";
    
    $jsFiles = ['api.js', 'ui.js', 'utils.js'];
    foreach ($jsFiles as $file) {
        $filePath = 'js/' . $file;
        echo "<h3>" . $filePath . "</h3>";
        
        if (file_exists($filePath)) {
            echo "<p style='color:green'>✅ File trovato</p>";
            echo "<p>Dimensione: " . filesize($filePath) . " bytes, Ultima modifica: " . date("Y-m-d H:i:s", filemtime($filePath)) . "</p>";
        } else {
            echo "<p style='color:red'>❌ File non trovato!</p>";
        }
    }
    
    // Verifica file CSS
    echo "<h2>File CSS</h2>";
    
    $cssFile = 'css/style.css';
    echo "<h3>" . $cssFile . "</h3>";
    
    if (file_exists($cssFile)) {
        echo "<p style='color:green'>✅ File trovato</p>";
        echo "<p>Dimensione: " . filesize($cssFile) . " bytes, Ultima modifica: " . date("Y-m-d H:i:s", filemtime($cssFile)) . "</p>";
    } else {
        echo "<p style='color:red'>❌ File non trovato!</p>";
    }
    
    // Verifica directory delle immagini
    echo "<h2>Directory delle Immagini</h2>";
    
    $imageDirectories = ['images', 'images/categories', 'images/articles'];
    foreach ($imageDirectories as $dir) {
        echo "<h3>" . $dir . "</h3>";
        
        if (is_dir($dir)) {
            echo "<p style='color:green'>✅ Directory trovata</p>";
            
            // Elenca i file nella directory
            $files = scandir($dir);
            echo "<p>File nella directory (" . (count($files) - 2) . "):</p>";
            echo "<ul>";
            foreach ($files as $file) {
                if ($file != '.' && $file != '..') {
                    echo "<li>" . $file . " <small>(" . filesize($dir . '/' . $file) . " bytes)</small></li>";
                }
            }
            echo "</ul>";
        } else {
            echo "<p style='color:red'>❌ Directory non trovata!</p>";
        }
    }
    
    // Verifica index.html
    echo "<h2>File HTML Principale</h2>";
    
    $indexFile = 'index.html';
    echo "<h3>" . $indexFile . "</h3>";
    
    if (file_exists($indexFile)) {
        echo "<p style='color:green'>✅ File trovato</p>";
        echo "<p>Dimensione: " . filesize($indexFile) . " bytes, Ultima modifica: " . date("Y-m-d H:i:s", filemtime($indexFile)) . "</p>";
        
        // Analizza il file per trovare riferimenti ai file JavaScript
        $content = file_get_contents($indexFile);
        
        preg_match_all('/<script src="([^"]+)"/', $content, $matches);
        
        echo "<p>File JavaScript referenziati:</p>";
        echo "<ul>";
        foreach ($matches[1] as $jsFile) {
            echo "<li>" . $jsFile;
            
            $fullPath = str_replace('/menu_digitale/', '', $jsFile);
            if (file_exists($fullPath)) {
                echo " <span style='color:green'>✅ Trovato</span>";
            } else {
                echo " <span style='color:red'>❌ Non trovato</span>";
            }
            
            echo "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color:red'>❌ File non trovato!</p>";
    }
    
} catch (Exception $e) {
    echo "<h2 style='color:red'>ERRORE:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?> 