/**
 * importCsv.js - Gestione dell'importazione unificata di articoli e categorie da CSV
 */

document.addEventListener('DOMContentLoaded', function() {
    // Inizializza il form di importazione
    initImportForm();
    
    // Gestisce il download del template
    setupTemplateDownload();
});

/**
 * Inizializza il form di importazione CSV
 */
function initImportForm() {
    const importForm = document.getElementById('importCsvForm');
    if (importForm) {
        importForm.addEventListener('submit', function(e) {
            e.preventDefault();
            importCsv();
        });
    }
}

/**
 * Importa dati da file CSV
 */
function importCsv() {
    const fileInput = document.getElementById('csvFile');
    
    if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
        showNotification('Seleziona un file CSV da importare', 'warning');
        return;
    }
    
    const file = fileInput.files[0];
    const formData = new FormData();
    formData.append('csv_file', file);
    
    // Ottieni token di autenticazione
    const token = localStorage.getItem('auth_token');
    
    // Mostra indicatore di caricamento
    showNotification('Importazione in corso...', 'info');
    
    fetch('/menu_digitale/api/import_csv.php', {
        method: 'POST',
        body: formData,
        headers: {
            'Authorization': 'Bearer ' + token
        }
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showNotification(`Importazione completata: ${result.data.categories} categorie e ${result.data.articles} articoli importati`, 'success');
            
            // Resetta il form
            document.getElementById('importCsvForm').reset();
            
            // Ricarica le categorie e gli articoli
            if (typeof loadExistingCategories === 'function') {
                loadExistingCategories();
            }
            
            if (typeof loadExistingArticles === 'function') {
                loadExistingArticles();
            }
        } else {
            showNotification('Errore: ' + result.message, 'error');
        }
    })
    .catch(error => {
        console.error('Errore durante l\'importazione:', error);
        showNotification('Errore nella comunicazione con il server', 'error');
    });
}

/**
 * Gestisce il download del template CSV
 */
function setupTemplateDownload() {
    const templateLink = document.getElementById('downloadCsvTemplate');
    if (templateLink) {
        templateLink.addEventListener('click', function(e) {
            e.preventDefault();
            downloadCsvTemplate();
        });
    }
}

/**
 * Download template CSV per importazione unificata
 */
function downloadCsvTemplate() {
    // Header e contenuto di esempio
    const csvContent = [
        'Nome Categoria,Nome Articolo,Descrizione Articolo,Prezzo Articolo',
        'Pizze,Margherita,Pomodoro e mozzarella,7.50',
        'Pizze,Diavola,Pomodoro mozzarella e salame piccante,9.00',
        'Pizze,Capricciosa,Pomodoro mozzarella funghi e carciofi,8.50',
        'Primi,Spaghetti,Pasta con sugo di pomodoro,8.00',
        'Primi,Risotto,Risotto alla milanese con zafferano,10.00',
        'Bevande,Acqua,Acqua naturale o frizzante,2.00',
        'Bevande,Coca Cola,Bevanda gassata,3.00'
    ].join('\n');
    
    // Crea il download
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    
    link.setAttribute('href', url);
    link.setAttribute('download', 'template_importazione.csv');
    link.style.visibility = 'hidden';
    
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
} 