<?php
// index.php - Visualizza il menu con le impostazioni personalizzate
require_once 'config.php';

// Inizializza la connessione al database
$conn = getDBConnection();

// Funzione per ottenere le impostazioni dal database
function getSettings() {
    global $conn;
    
    try {
        $stmt = $conn->prepare("SELECT setting_key, setting_value FROM settings");
        $stmt->execute();
        
        $settings = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        
        return $settings;
    } catch (PDOException $e) {
        error_log('Errore nel recupero delle impostazioni: ' . $e->getMessage());
        return array(); // Restituisce un array vuoto in caso di errore
    }
}

// Carica le impostazioni
$settings = getSettings();

// Imposta valori predefiniti se non presenti
$siteName = $settings['site_name'] ?? 'Pointer - Menu Digitale';
$siteTagline = $settings['site_tagline'] ?? 'Soluzioni evolute per il punto cassa';
$logoUrl = $settings['logo_url'] ?? '/menu_digitale/images/Logo-Pointer.jpg';
$primaryColor = $settings['primary_color'] ?? '#1A3C40';
$accentColor = $settings['accent_color'] ?? '#E76F51';
$address = $settings['address'] ?? 'Via Trieste 42, Udine';
$phone = $settings['phone'] ?? '0432 111111';
$email = $settings['email'] ?? 'info@pointer.it';
$weekdayHours = $settings['weekday_hours'] ?? '8:30-12:30, 14:30-18:30';
$weekendHours = $settings['weekend_hours'] ?? 'Chiuso';

// Ora possiamo includere il template HTML con le variabili che saranno sostituite
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($siteName); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/menu_digitale/css/style.css">
    <link rel="icon" type="image/x-icon" href="/menu_digitale/images/favicon.ico">
    <style>
        :root {
            --primary-color: <?php echo $primaryColor; ?>;
            --accent-color: <?php echo $accentColor; ?>;
        }
        
        /* ... existing styles ... */
        .footer-admin-link {
            text-align: center;
            margin-top: 15px;
            font-size: 0.8rem;
        }
        
        .admin-link {
            color: #999;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .admin-link:hover {
            color: #666;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo-container">
                <img src="<?php echo htmlspecialchars($logoUrl); ?>" alt="<?php echo htmlspecialchars($siteName); ?>" class="logo">
            </div>
            <div class="header-content">
                <h1><?php echo htmlspecialchars($siteName); ?></h1>
                <div class="header-tagline"><?php echo htmlspecialchars($siteTagline); ?></div>
            </div>
        </div>
    </header>

    <div class="search-bar">
        <div class="search-input-container">
            <input type="text" id="searchInput" placeholder="Cerca nel menu...">
            <button class="search-clear" id="searchClear"></button>
        </div>
        <div id="searchResults" class="search-results"> </div>
    </div>

    <div id="categoriesView" class="categories-grid">
        <div class="loading">Caricamento categorie...</div>
    </div>

    <div class="menu-list" id="menuView" style="display: none;">
        <h2 id="categoryTitle"></h2>
        <div id="menuItems"></div>
    </div>

    <footer>
        <div class="footer-container">
            <div class="footer-info">
                <div class="footer-section">
                    <h3>Dove Siamo</h3>
                    <p>📍 <?php echo htmlspecialchars($address); ?></p>
                </div>
                <div class="footer-section">
                    <h3>Contatti</h3>
                    <p>📞 <?php echo htmlspecialchars($phone); ?></p>
                    <p>✉️ <?php echo htmlspecialchars($email); ?></p>
                </div>
                <div class="footer-section">
                    <h3>Orari</h3>
                    <p>Lun-Ven: <?php echo htmlspecialchars($weekdayHours); ?></p>
                    <p>Sab-Dom: <?php echo htmlspecialchars($weekendHours); ?></p>
                </div>
            </div>
            <div class="footer-admin-link">
                <a href="/menu_digitale/login.html" class="admin-link">Area Admin</a>
            </div>
        </div>
    </footer>

    <script src="/menu_digitale/js/utils.js"></script>
    <script src="/menu_digitale/js/api.js"></script>
    <script src="/menu_digitale/js/favorites.js"></script>
    <script src="/menu_digitale/js/ui.js"></script>
    <script>
        const api = new MenuAPI();
        const ui = new MenuUI(api);
        
        // Script per funzionalità preferiti direttamente incorporato nella pagina
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Script preferiti incorporato attivato!');
            
            // Semplice gestore preferiti
            const favoritesManager = {
                storageKey: 'menu_favorites',
                
                // Carica i preferiti da localStorage
                load: function() {
                    const stored = localStorage.getItem(this.storageKey);
                    return stored ? JSON.parse(stored) : [];
                },
                
                // Salva i preferiti in localStorage
                save: function(favorites) {
                    localStorage.setItem(this.storageKey, JSON.stringify(favorites));
                },
                
                // Aggiunge un elemento ai preferiti
                add: function(item) {
                    const favorites = this.load();
                    if (!favorites.some(f => f.id === item.id)) {
                        favorites.push(item);
                        this.save(favorites);
                        return true;
                    }
                    return false;
                },
                
                // Rimuove un elemento dai preferiti
                remove: function(itemId) {
                    const favorites = this.load();
                    const initialLength = favorites.length;
                    const newFavorites = favorites.filter(f => f.id !== itemId);
                    
                    if (initialLength !== newFavorites.length) {
                        this.save(newFavorites);
                        return true;
                    }
                    return false;
                },
                
                // Controlla se un elemento è nei preferiti
                isFavorite: function(itemId) {
                    return this.load().some(f => f.id === itemId);
                },
                
                // Ottiene tutti i preferiti
                getAll: function() {
                    return this.load();
                }
            };
            
            // Crea e aggiungi il pulsante dei preferiti
            const favButton = document.createElement('div');
            favButton.style.position = 'fixed';
            favButton.style.bottom = '20px';
            favButton.style.right = '20px';
            favButton.style.width = '60px';
            favButton.style.height = '60px';
            favButton.style.backgroundColor = 'var(--primary-color)';
            favButton.style.color = 'white';
            favButton.style.borderRadius = '50%';
            favButton.style.display = 'flex';
            favButton.style.justifyContent = 'center';
            favButton.style.alignItems = 'center';
            favButton.style.boxShadow = '0 4px 6px rgba(0,0,0,0.1)';
            favButton.style.cursor = 'pointer';
            favButton.style.zIndex = '1000';
            favButton.innerHTML = '❤️';
            favButton.style.fontSize = '24px';
            
            // Crea il contatore
            const counter = document.createElement('div');
            counter.style.position = 'absolute';
            counter.style.top = '-5px';
            counter.style.right = '-5px';
            counter.style.backgroundColor = 'var(--accent-color)';
            counter.style.color = 'white';
            counter.style.borderRadius = '50%';
            counter.style.width = '25px';
            counter.style.height = '25px';
            counter.style.display = 'flex';
            counter.style.justifyContent = 'center';
            counter.style.alignItems = 'center';
            counter.style.fontSize = '14px';
            counter.style.fontWeight = 'bold';
            
            // Aggiorna il contatore iniziale
            counter.textContent = favoritesManager.getAll().length.toString();
            counter.style.display = favoritesManager.getAll().length > 0 ? 'flex' : 'none';
            
            // Aggiungi il contatore al pulsante
            favButton.appendChild(counter);
            
            // Crea il pannello dei preferiti
            const favPanel = document.createElement('div');
            favPanel.style.position = 'fixed';
            favPanel.style.top = '0';
            favPanel.style.right = '-380px';
            favPanel.style.width = '380px';
            favPanel.style.height = '100%';
            favPanel.style.backgroundColor = 'white';
            favPanel.style.boxShadow = '0 0 15px rgba(0,0,0,0.2)';
            favPanel.style.zIndex = '1001';
            favPanel.style.transition = 'right 0.3s ease-in-out';
            favPanel.style.display = 'flex';
            favPanel.style.flexDirection = 'column';
            favPanel.style.padding = '0';
            
            // Intestazione pannello
            const favHeader = document.createElement('div');
            favHeader.style.backgroundColor = 'var(--primary-color)';
            favHeader.style.color = 'white';
            favHeader.style.padding = '15px';
            favHeader.style.display = 'flex';
            favHeader.style.justifyContent = 'space-between';
            favHeader.style.alignItems = 'center';
            
            const favTitle = document.createElement('h2');
            favTitle.style.margin = '0';
            favTitle.style.fontSize = '1.4rem';
            favTitle.textContent = 'I tuoi preferiti';
            
            const closeButton = document.createElement('button');
            closeButton.style.background = 'none';
            closeButton.style.border = 'none';
            closeButton.style.color = 'white';
            closeButton.style.fontSize = '24px';
            closeButton.style.cursor = 'pointer';
            closeButton.textContent = '×';
            
            favHeader.appendChild(favTitle);
            favHeader.appendChild(closeButton);
            
            // Contenuto del pannello
            const favContent = document.createElement('div');
            favContent.style.padding = '15px';
            favContent.style.overflowY = 'auto';
            favContent.style.flex = '1';
            
            // Aggiungi gli elementi al pannello
            favPanel.appendChild(favHeader);
            favPanel.appendChild(favContent);
            
            // Aggiungi il pannello alla pagina
            document.body.appendChild(favPanel);
            
            // Funzione per aggiornare la lista dei preferiti
            function updateFavoritesList() {
                const favorites = favoritesManager.getAll();
                
                if (favorites.length === 0) {
                    favContent.innerHTML = `
                        <div style="text-align: center; padding: 30px 0;">
                            <div style="font-size: 40px; margin-bottom: 10px;">❤️</div>
                            <p style="color: #777;">Non hai ancora aggiunto preferiti</p>
                        </div>
                    `;
                    return;
                }
                
                // Crea la lista
                favContent.innerHTML = '';
                favorites.forEach(item => {
                    const itemElement = document.createElement('div');
                    itemElement.style.display = 'flex';
                    itemElement.style.padding = '10px 0';
                    itemElement.style.borderBottom = '1px solid #eaeaea';
                    
                    // Immagine
                    const img = document.createElement('img');
                    img.src = item.imageUrl || '/menu_digitale/images/placeholder.jpg';
                    img.alt = item.name;
                    img.style.width = '60px';
                    img.style.height = '60px';
                    img.style.objectFit = 'cover';
                    img.style.borderRadius = '5px';
                    img.style.marginRight = '10px';
                    img.onerror = function() { this.src = '/menu_digitale/images/placeholder.jpg'; };
                    
                    // Dettagli
                    const details = document.createElement('div');
                    details.style.flex = '1';
                    
                    const name = document.createElement('div');
                    name.textContent = item.name;
                    name.style.fontWeight = 'bold';
                    name.style.marginBottom = '5px';
                    
                    const price = document.createElement('div');
                    price.textContent = `€${parseFloat(item.price).toFixed(2)}`;
                    price.style.color = 'var(--accent-color)';
                    price.style.fontWeight = 'bold';
                    
                    const removeBtn = document.createElement('button');
                    removeBtn.textContent = '×';
                    removeBtn.style.background = 'none';
                    removeBtn.style.border = 'none';
                    removeBtn.style.color = '#E74C3C';
                    removeBtn.style.fontSize = '20px';
                    removeBtn.style.cursor = 'pointer';
                    removeBtn.style.padding = '0 5px';
                    
                    // Aggiungi event listener per rimuovere
                    removeBtn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        favoritesManager.remove(item.id);
                        updateFavoritesList();
                        updateCounter();
                    });
                    
                    // Assembla l'elemento
                    details.appendChild(name);
                    details.appendChild(price);
                    
                    itemElement.appendChild(img);
                    itemElement.appendChild(details);
                    itemElement.appendChild(removeBtn);
                    
                    // Aggiungi all'elenco
                    favContent.appendChild(itemElement);
                });
            }
            
            // Funzione per aggiornare il contatore
            function updateCounter() {
                const count = favoritesManager.getAll().length;
                counter.textContent = count.toString();
                counter.style.display = count > 0 ? 'flex' : 'none';
            }
            
            // Event listener per aprire/chiudere il pannello
            favButton.addEventListener('click', function() {
                if (favPanel.style.right === '0px') {
                    favPanel.style.right = '-380px';
                } else {
                    favPanel.style.right = '0px';
                    updateFavoritesList();
                }
            });
            
            // Event listener per chiudere il pannello
            closeButton.addEventListener('click', function() {
                favPanel.style.right = '-380px';
            });
            
            // Estendere la classe UI per aggiungere funzionalità preferiti
            const originalShowItemDetail = ui.showItemDetail;
            ui.showItemDetail = function(name, description, price, imageUrl) {
                // Chiama il metodo originale
                originalShowItemDetail.call(this, name, description, price, imageUrl);
                
                // Genera un ID univoco per l'elemento
                const itemId = name.replace(/\s/g, '_').toLowerCase();
                
                // Crea e aggiungi il pulsante preferiti alla modale
                const modal = document.querySelector('.item-detail-modal');
                if (modal) {
                    const isFavorite = favoritesManager.isFavorite(itemId);
                    
                    const favBtn = document.createElement('button');
                    favBtn.className = 'fav-button';
                    favBtn.style.position = 'absolute';
                    favBtn.style.top = '20px';
                    favBtn.style.right = '60px';
                    favBtn.style.backgroundColor = 'white';
                    favBtn.style.border = 'none';
                    favBtn.style.width = '40px';
                    favBtn.style.height = '40px';
                    favBtn.style.borderRadius = '50%';
                    favBtn.style.display = 'flex';
                    favBtn.style.justifyContent = 'center';
                    favBtn.style.alignItems = 'center';
                    favBtn.style.boxShadow = '0 2px 5px rgba(0,0,0,0.1)';
                    favBtn.style.cursor = 'pointer';
                    favBtn.style.zIndex = '1002';
                    favBtn.innerHTML = isFavorite ? '❤️' : '🤍';
                    
                    favBtn.addEventListener('click', function() {
                        if (favoritesManager.isFavorite(itemId)) {
                            favoritesManager.remove(itemId);
                            favBtn.innerHTML = '🤍';
                            showNotification(`${name} rimosso dai preferiti`);
                        } else {
                            favoritesManager.add({
                                id: itemId,
                                name: name,
                                description: description || '',
                                price: price,
                                imageUrl: imageUrl
                            });
                            favBtn.innerHTML = '❤️';
                            showNotification(`${name} aggiunto ai preferiti`);
                        }
                        updateCounter();
                    });
                    
                    const modalContent = modal.querySelector('.item-detail-content');
                    if (modalContent) {
                        modalContent.appendChild(favBtn);
                    }
                }
            };
            
            // Funzione per mostrare notifiche
            function showNotification(message) {
                let notification = document.querySelector('.fav-notification');
                
                if (!notification) {
                    notification = document.createElement('div');
                    notification.className = 'fav-notification';
                    notification.style.position = 'fixed';
                    notification.style.bottom = '90px';
                    notification.style.right = '20px';
                    notification.style.backgroundColor = 'var(--primary-color)';
                    notification.style.color = 'white';
                    notification.style.padding = '12px 20px';
                    notification.style.borderRadius = '5px';
                    notification.style.boxShadow = '0 3px 12px rgba(0,0,0,0.2)';
                    notification.style.zIndex = '1002';
                    notification.style.transition = 'opacity 0.3s, transform 0.3s';
                    notification.style.opacity = '0';
                    notification.style.transform = 'translateY(20px)';
                    notification.style.fontWeight = '500';
                    notification.style.display = 'flex';
                    notification.style.alignItems = 'center';
                    notification.style.gap = '8px';
                    
                    document.body.appendChild(notification);
                }
                
                // Aggiungi icona alla notifica
                notification.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                    </svg>
                    ${message}
                `;
                
                notification.style.opacity = '1';
                notification.style.transform = 'translateY(0)';
                
                setTimeout(() => {
                    notification.style.opacity = '0';
                    notification.style.transform = 'translateY(20px)';
                }, 3000);
            }
            
            // Aggiungi alla pagina
            document.body.appendChild(favButton);
        });
    </script>
</body>
</html> 