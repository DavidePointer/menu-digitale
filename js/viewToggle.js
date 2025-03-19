/**
 * viewToggle.js - Sistema di visualizzazione articoli (card/list)
 * Versione 3.0 - Completamente rivista e semplificata
 */

console.log('ViewToggle.js v3.0 caricato!');

// Salva la modalità di visualizzazione in localStorage
function saveViewMode(viewMode) {
    if (viewMode === 'card' || viewMode === 'list') {
        localStorage.setItem('articleViewMode', viewMode);
        console.log('Modalità di visualizzazione salvata:', viewMode);
        return viewMode;
    }
    return null;
}

// Legge la modalità di visualizzazione da localStorage
function getViewMode() {
    const viewMode = localStorage.getItem('articleViewMode');
    return (viewMode === 'card' || viewMode === 'list') ? viewMode : 'card';
}

// Applica la modalità di visualizzazione
function applyViewMode(viewMode) {
    // Assicuriamoci di avere un valore valido
    viewMode = (viewMode === 'list' || viewMode === 'card') ? viewMode : 'card';
    
    console.log('Applicazione modalità:', viewMode);
    
    // Applica le classi al body
    document.body.classList.remove('view-card', 'view-list');
    document.body.classList.add('view-' + viewMode);
    
    // Forza l'aggiornamento del contenitore articoli se presente
    const menuItems = document.getElementById('menuItems');
    if (menuItems) {
        menuItems.classList.remove('view-card', 'view-list');
        menuItems.classList.add('view-' + viewMode);
        console.log('Classe view-' + viewMode + ' applicata a #menuItems');
    }
    
    return viewMode;
}

// Evento DOMContentLoaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('ViewToggle - DOMContentLoaded');
    
    // Applica la modalità salvata
    const savedMode = getViewMode();
    applyViewMode(savedMode);
    
    // Ascolta per l'evento settingsLoaded
    window.addEventListener('settingsLoaded', function(e) {
        console.log('ViewToggle - Evento settingsLoaded ricevuto');
        const settings = e.detail;
        
        if (settings && settings.general && settings.general.articleView) {
            const newMode = settings.general.articleView;
            console.log('Nuova modalità dalle impostazioni:', newMode);
            saveViewMode(newMode);
            applyViewMode(newMode);
        }
    });
    
    // Monitora i cambiamenti nel DOM
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                // Se viene aggiunto #menuItems, applicagli la classe corretta
                const menuItems = document.getElementById('menuItems');
                if (menuItems && !menuItems.classList.contains('view-card') && !menuItems.classList.contains('view-list')) {
                    const viewMode = getViewMode();
                    menuItems.classList.add('view-' + viewMode);
                    console.log('MutationObserver: Classe aggiunta a #menuItems');
                }
            }
        });
    });
    
    // Osserva tutto il documento
    observer.observe(document.body, { childList: true, subtree: true });
});

// Esporta le funzioni per l'uso in altri contesti
window.viewToggle = {
    save: saveViewMode,
    get: getViewMode,
    apply: applyViewMode
}; 