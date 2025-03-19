/**
 * main.js - Gestione delle visualizzazioni articoli (soluzione radicale)
 * Versione 1.1 - Soluzione ULTRA emergenziale
 */

console.log('Main.js - Soluzione ULTRA emergenziale caricato!');

// Funzione per implementare la visualizzazione card/list in modo semplice e diretto
function impostaModalitaArticoli() {
    // 1. Ottieni la modalità di visualizzazione da localStorage
    const modalita = localStorage.getItem('articleViewMode') || 'card';
    console.log(`%c[MAIN.JS] Impostiamo la modalità: ${modalita}`, 'background: #ff0000; color: white; padding: 3px 5px; font-weight: bold;');
    
    // 2. Applica la classe al body
    document.body.classList.remove('view-card', 'view-list');
    document.body.classList.add(`view-${modalita}`);
    console.log(`%c[MAIN.JS] Classe body aggiornata: ${document.body.className}`, 'background: #ff0000; color: white;');
    
    // 3. FORZA gli stili direttamente nel documento
    const existingStyle = document.getElementById('force-view-styles');
    if (existingStyle) {
        document.head.removeChild(existingStyle);
    }
    
    // Crea un nuovo tag style
    const style = document.createElement('style');
    style.id = 'force-view-styles';
    
    if (modalita === 'card') {
        // Stili per la modalità card
        style.textContent = `
            /* FORCE MODE: CARD */
            .items-grid { 
                display: grid !important; 
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)) !important;
                gap: 20px !important;
                visibility: visible !important;
                opacity: 1 !important;
            }
            .items-list, 
            .restaurant-menu { 
                display: none !important; 
                visibility: hidden !important;
                height: 0 !important;
                overflow: hidden !important;
            }
            .item-image, 
            .item-image-container { 
                display: block !important; 
                height: 200px !important; 
                visibility: visible !important;
                opacity: 1 !important;
            }
            /* Rimuovi forzatamente qualsiasi stile che potrebbe interferire */
            #menuItems {
                display: block !important;
            }
            #menuItems .items-grid {
                display: grid !important;
            }
        `;
    } else {
        // Stili per la modalità lista
        style.textContent = `
            /* FORCE MODE: LIST */
            .items-grid { 
                display: none !important; 
                visibility: hidden !important;
                height: 0 !important;
                overflow: hidden !important;
            }
            .items-list, 
            .restaurant-menu { 
                display: block !important; 
                width: 100% !important; 
                max-width: 800px !important; 
                margin: 0 auto !important;
                visibility: visible !important;
                opacity: 1 !important;
            }
            .item-image, 
            .item-image-container { 
                display: none !important;
                visibility: hidden !important;
                height: 0 !important;
                overflow: hidden !important;
            }
            /* Specifica esattamente gli stili per la vista lista */
            .list-item-header {
                display: flex !important;
                justify-content: space-between !important;
                align-items: baseline !important;
                margin-bottom: 5px !important;
            }
            .restaurant-item {
                padding: 12px 0 !important;
                background-color: transparent !important;
                margin-bottom: 0 !important;
            }
            .menu-divider {
                height: 1px !important;
                background-color: #eaeaea !important;
                margin: 5px 0 !important;
            }
            /* Rimuovi forzatamente qualsiasi stile che potrebbe interferire */
            #menuItems {
                display: block !important;
            }
        `;
    }
    
    // Aggiungi gli stili al documento
    document.head.appendChild(style);
    console.log(`%c[MAIN.JS] Stili forzati applicati per modalità: ${modalita}`, 'background: #ff0000; color: white;');
    
    // 4. Applica DIRETTAMENTE gli stili agli elementi esistenti
    processExistingElements();
    
    // 5. FORZA l'aggiornamento continuo per intercettare nuovi elementi
    if (!window.viewUpdateInterval) {
        window.viewUpdateInterval = setInterval(processExistingElements, 100);
        console.log(`%c[MAIN.JS] Avviato intervallo di aggiornamento continuo`, 'background: #ff0000; color: white;');
    }
}

// Elabora gli elementi esistenti applicando gli stili direttamente
function processExistingElements() {
    const modalita = localStorage.getItem('articleViewMode') || 'card';
    
    // Intervieni sul contenitore degli articoli
    const menuItems = document.getElementById('menuItems');
    if (menuItems) {
        menuItems.classList.remove('view-card', 'view-list');
        menuItems.classList.add(`view-${modalita}`);
        menuItems.setAttribute('data-view-mode', modalita);
        
        // Applica gli stili direttamente
        if (modalita === 'list') {
            // Nascondi la griglia
            const itemsGrid = menuItems.querySelector('.items-grid');
            if (itemsGrid) {
                itemsGrid.style.display = 'none';
                itemsGrid.style.visibility = 'hidden';
                itemsGrid.style.height = '0';
                itemsGrid.style.overflow = 'hidden';
            }
            
            // Mostra la lista
            const itemsList = menuItems.querySelector('.items-list, .restaurant-menu');
            if (itemsList) {
                itemsList.style.display = 'block';
                itemsList.style.visibility = 'visible';
                itemsList.style.width = '100%';
                itemsList.style.maxWidth = '800px';
                itemsList.style.margin = '0 auto';
            } else {
                // Se non esiste, forse è il primo caricamento - dobbiamo attendere
                console.log("%c[MAIN.JS] Lista non trovata, riprovando...", 'background: #ff0000; color: white;');
            }
            
            // Nascondi tutte le immagini
            const images = menuItems.querySelectorAll('.item-image, .item-image-container');
            if (images.length > 0) {
                images.forEach(img => {
                    img.style.display = 'none';
                    img.style.visibility = 'hidden';
                    img.style.height = '0';
                    img.style.overflow = 'hidden';
                });
                console.log(`%c[MAIN.JS] Nascoste ${images.length} immagini`, 'background: #ff0000; color: white;');
            }
        } else {
            // Mostra la griglia
            const itemsGrid = menuItems.querySelector('.items-grid');
            if (itemsGrid) {
                itemsGrid.style.display = 'grid';
                itemsGrid.style.visibility = 'visible';
                itemsGrid.style.gridTemplateColumns = 'repeat(auto-fill, minmax(280px, 1fr))';
                itemsGrid.style.gap = '20px';
            }
            
            // Nascondi la lista
            const itemsList = menuItems.querySelector('.items-list, .restaurant-menu');
            if (itemsList) {
                itemsList.style.display = 'none';
                itemsList.style.visibility = 'hidden';
                itemsList.style.height = '0';
                itemsList.style.overflow = 'hidden';
            }
            
            // Mostra tutte le immagini
            const images = menuItems.querySelectorAll('.item-image, .item-image-container');
            if (images.length > 0) {
                images.forEach(img => {
                    img.style.display = 'block';
                    img.style.visibility = 'visible';
                    img.style.height = '200px';
                });
                console.log(`%c[MAIN.JS] Mostrate ${images.length} immagini`, 'background: #ff0000; color: white;');
            }
        }
    } else {
        console.log("%c[MAIN.JS] menuItems non trovato, riprovando...", 'background: #ff0000; color: white;');
    }
}

// Monitora il DOM per intercettare nuovi elementi dinamici
function setupMutationObserver() {
    console.log('%c[MAIN.JS] Configurazione observer per modifiche DOM', 'background: #ff0000; color: white;');
    
    // Configura un observer per rilevare cambiamenti nel DOM
    const observer = new MutationObserver(function(mutations) {
        let needsUpdate = false;
        
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                // Controlla se è stato aggiunto il container menuItems o elementi al suo interno
                mutation.addedNodes.forEach(function(node) {
                    if (node.id === 'menuItems' || 
                        (node.nodeType === 1 && (node.classList.contains('items-grid') || 
                                               node.classList.contains('items-list') || 
                                               node.classList.contains('restaurant-menu')))) {
                        needsUpdate = true;
                    }
                });
            }
        });
        
        if (needsUpdate) {
            console.log('%c[MAIN.JS] Rilevate modifiche DOM significative, aggiorno stili...', 'background: #ff0000; color: white;');
            processExistingElements();
        }
    });
    
    // Inizia a osservare il documento
    observer.observe(document.body, { childList: true, subtree: true });
    console.log('%c[MAIN.JS] Observer DOM attivo', 'background: #ff0000; color: white;');
}

// Funzione di inizializzazione completa
function init() {
    console.log('%c[MAIN.JS] Inizializzazione completa...', 'background: #ff0000; color: white;');
    
    // Prima applicazione degli stili
    impostaModalitaArticoli();
    
    // Setup observer
    setupMutationObserver();
    
    // Log contenuto localStorage
    console.log('%c[MAIN.JS] CONTENUTO LOCALSTORAGE:', 'background: #ff0000; color: white;');
    Object.keys(localStorage).forEach(key => {
        console.log(`- ${key}: ${localStorage.getItem(key)}`);
    });
}

// Esegui subito all'avvio
window.addEventListener('DOMContentLoaded', init);

// Esegui quando cambia la modalità
window.addEventListener('viewModeChanged', impostaModalitaArticoli);
window.addEventListener('settingsLoaded', impostaModalitaArticoli);

// Esporta globalmente
window.impostaModalitaArticoli = impostaModalitaArticoli;

// Esegui ora per sicurezza
init(); 