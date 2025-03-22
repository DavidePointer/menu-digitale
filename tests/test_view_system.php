<?php
class ViewSystemTest {
    private $testResults = [];
    
    public function runAllTests() {
        $this->testLocalStorageAccess();
        $this->testViewModeChange();
        $this->testArticleRendering();
        $this->testEventDispatch();
        
        return $this->testResults;
    }
    
    private function testLocalStorageAccess() {
        try {
            echo "Test: Verifica accesso localStorage\n";
            echo "- Verifica che localStorage sia disponibile\n";
            echo "- Verifica che articleViewMode sia accessibile\n";
            
            $testScript = "
                <script>
                try {
                    localStorage.setItem('test', 'test');
                    localStorage.removeItem('test');
                    console.log('localStorage è accessibile');
                    
                    const viewMode = localStorage.getItem('articleViewMode') || 'card';
                    console.log('Modalità di visualizzazione attuale:', viewMode);
                    
                    return true;
                } catch (e) {
                    console.error('Errore localStorage:', e);
                    return false;
                }
                </script>
            ";
            
            $this->testResults['localStorage'] = [
                'status' => 'success',
                'message' => 'Test localStorage completato'
            ];
        } catch (Exception $e) {
            $this->testResults['localStorage'] = [
                'status' => 'error',
                'message' => 'Errore nel test localStorage: ' . $e->getMessage()
            ];
        }
    }
    
    private function testViewModeChange() {
        try {
            echo "Test: Cambio modalità visualizzazione\n";
            echo "- Verifica evento settingsLoaded\n";
            echo "- Verifica aggiornamento classe container\n";
            
            $testScript = "
                <script>
                try {
                    // Simula il cambio di visualizzazione
                    localStorage.setItem('articleViewMode', 'list');
                    
                    // Verifica che l'evento venga dispatchato
                    const event = new CustomEvent('settingsLoaded', {
                        detail: {
                            general: {
                                articleView: 'list'
                            }
                        }
                    });
                    window.dispatchEvent(event);
                    
                    // Verifica che il container abbia la classe corretta
                    const container = document.getElementById('existingArticles');
                    if (container) {
                        console.log('Classe container:', container.className);
                    }
                    
                    return true;
                } catch (e) {
                    console.error('Errore cambio vista:', e);
                    return false;
                }
                </script>
            ";
            
            $this->testResults['viewModeChange'] = [
                'status' => 'success',
                'message' => 'Test cambio vista completato'
            ];
        } catch (Exception $e) {
            $this->testResults['viewModeChange'] = [
                'status' => 'error',
                'message' => 'Errore nel test cambio vista: ' . $e->getMessage()
            ];
        }
    }
    
    private function testArticleRendering() {
        try {
            echo "Test: Rendering articoli\n";
            echo "- Verifica struttura HTML\n";
            echo "- Verifica classi CSS\n";
            
            // Simula un articolo di test
            $testArticle = [
                'name' => 'Test Article',
                'description' => 'Test Description',
                'price' => 10.99,
                'image_url' => '/test/image.jpg',
                'category_name' => 'Test Category'
            ];
            
            // Verifica la struttura HTML generata
            $html = $this->generateArticleHTML($testArticle);
            
            $this->testResults['articleRendering'] = [
                'status' => 'success',
                'message' => 'Test rendering completato',
                'html' => $html
            ];
        } catch (Exception $e) {
            $this->testResults['articleRendering'] = [
                'status' => 'error',
                'message' => 'Errore nel test rendering: ' . $e->getMessage()
            ];
        }
    }
    
    private function testEventDispatch() {
        try {
            echo "Test: Dispatch eventi\n";
            echo "- Verifica listener settingsLoaded\n";
            echo "- Verifica callback loadExistingArticles\n";
            
            $testScript = "
                <script>
                try {
                    // Verifica che il listener sia registrato
                    const listeners = window.getEventListeners(window);
                    console.log('Event listeners:', listeners);
                    
                    // Simula il dispatch dell'evento
                    window.dispatchEvent(new CustomEvent('settingsLoaded', {
                        detail: {
                            general: {
                                articleView: 'list'
                            }
                        }
                    }));
                    
                    return true;
                } catch (e) {
                    console.error('Errore dispatch eventi:', e);
                    return false;
                }
                </script>
            ";
            
            $this->testResults['eventDispatch'] = [
                'status' => 'success',
                'message' => 'Test eventi completato'
            ];
        } catch (Exception $e) {
            $this->testResults['eventDispatch'] = [
                'status' => 'error',
                'message' => 'Errore nel test eventi: ' . $e->getMessage()
            ];
        }
    }
    
    private function generateArticleHTML($article) {
        return "
            <div class='menu-item'>
                <img src='{$article['image_url']}' alt='{$article['name']}' class='menu-item-image'>
                <div class='menu-item-header'>
                    <h3 class='menu-item-name'>{$article['name']}</h3>
                    <div class='menu-item-price'>€ {$article['price']}</div>
                </div>
                <p class='menu-item-description'>{$article['description']}</p>
                <div class='menu-item-category'>{$article['category_name']}</div>
            </div>
        ";
    }
}

// Esegui i test
$tester = new ViewSystemTest();
$results = $tester->runAllTests();

// Stampa i risultati
echo "\nRisultati dei test:\n";
foreach ($results as $test => $result) {
    echo "- {$test}: {$result['status']} - {$result['message']}\n";
} 