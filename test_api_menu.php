<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test API Menu</h1>";

// Verifica API categorie
echo "<h2>Test API Categorie</h2>";
$categoriesUrl = "http://localhost/menu_digitale/api/menu.php";
echo "URL: " . $categoriesUrl . "<br>";

try {
    $response = file_get_contents($categoriesUrl);
    if ($response === false) {
        echo "Errore nella richiesta. ";
        if (isset($http_response_header)) {
            print_r($http_response_header);
        }
    } else {
        $data = json_decode($response, true);
        
        echo "<pre>";
        print_r($data);
        echo "</pre>";
        
        if (is_array($data) && count($data) > 0) {
            echo "<p>Trovate " . count($data) . " categorie.</p>";
            
            // Test API per ogni categoria
            $firstCategory = isset($data[0]['url_name']) ? $data[0]['url_name'] : "";
            if (!empty($firstCategory)) {
                echo "<h2>Test API Articoli per la categoria: " . $firstCategory . "</h2>";
                $articlesUrl = "http://localhost/menu_digitale/api/menu.php?category=" . urlencode($firstCategory);
                echo "URL: " . $articlesUrl . "<br>";
                
                $articleResponse = file_get_contents($articlesUrl);
                if ($articleResponse === false) {
                    echo "Errore nella richiesta degli articoli.";
                } else {
                    $articleData = json_decode($articleResponse, true);
                    
                    echo "<pre>";
                    print_r($articleData);
                    echo "</pre>";
                    
                    if (is_array($articleData)) {
                        echo "<p>Trovati " . count($articleData) . " articoli.</p>";
                    } else {
                        echo "<p>Nessun articolo trovato o errore nel formato.</p>";
                    }
                }
            }
        } else {
            echo "<p>Nessuna categoria trovata o errore nel formato della risposta.</p>";
        }
    }
} catch (Exception $e) {
    echo "Errore: " . $e->getMessage();
}
?> 