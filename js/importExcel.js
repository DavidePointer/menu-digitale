/**
 * importExcel.js - Gestione dell'importazione di articoli e categorie da Excel
 */

document.addEventListener('DOMContentLoaded', function() {
    // Inizializza i form di importazione
    initImportForms();
    
    // Gestisci i download dei template
    setupTemplateDownloads();
});

/**
 * Inizializza i form di importazione
 */
function initImportForms() {
    // Form importazione categorie
    const categoriesForm = document.getElementById('importCategoriesForm');
    if (categoriesForm) {
        categoriesForm.addEventListener('submit', function(e) {
            e.preventDefault();
            importCategories();
        });
    }
    
    // Form importazione articoli
    const articlesForm = document.getElementById('importArticlesForm');
    if (articlesForm) {
        articlesForm.addEventListener('submit', function(e) {
            e.preventDefault();
            importArticles();
        });
    }
}

/**
 * Importa categorie da file Excel
 */
function importCategories() {
    const fileInput = document.getElementById('categoriesExcelFile');
    
    if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
        showNotification('Seleziona un file Excel da importare', 'warning');
        return;
    }
    
    const file = fileInput.files[0];
    const formData = new FormData();
    formData.append('excel_file', file);
    
    // Ottieni token di autenticazione
    const token = localStorage.getItem('auth_token');
    
    // Mostra indicatore di caricamento
    showNotification('Importazione in corso...', 'info');
    
    fetch('/menu_digitale/api/import_categories.php', {
        method: 'POST',
        body: formData,
        headers: {
            'Authorization': 'Bearer ' + token
        }
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showNotification(`Importazione completata: ${result.data.imported} categorie importate`, 'success');
            
            // Resetta il form
            document.getElementById('importCategoriesForm').reset();
            
            // Ricarica le categorie
            if (typeof loadExistingCategories === 'function') {
                loadExistingCategories();
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
 * Importa articoli da file Excel
 */
function importArticles() {
    const fileInput = document.getElementById('articlesExcelFile');
    
    if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
        showNotification('Seleziona un file Excel da importare', 'warning');
        return;
    }
    
    const file = fileInput.files[0];
    const formData = new FormData();
    formData.append('excel_file', file);
    
    // Ottieni token di autenticazione
    const token = localStorage.getItem('auth_token');
    
    // Mostra indicatore di caricamento
    showNotification('Importazione in corso...', 'info');
    
    fetch('/menu_digitale/api/import_articles.php', {
        method: 'POST',
        body: formData,
        headers: {
            'Authorization': 'Bearer ' + token
        }
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showNotification(`Importazione completata: ${result.data.imported} articoli importati`, 'success');
            
            // Resetta il form
            document.getElementById('importArticlesForm').reset();
            
            // Ricarica gli articoli
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
 * Gestisce i download dei template Excel
 */
function setupTemplateDownloads() {
    // Template categorie
    const categoryTemplateLink = document.getElementById('downloadCategoryTemplate');
    if (categoryTemplateLink) {
        categoryTemplateLink.addEventListener('click', function(e) {
            e.preventDefault();
            downloadCategoryTemplate();
        });
    }
    
    // Template articoli
    const articleTemplateLink = document.getElementById('downloadArticleTemplate');
    if (articleTemplateLink) {
        articleTemplateLink.addEventListener('click', function(e) {
            e.preventDefault();
            downloadArticleTemplate();
        });
    }
}

/**
 * Download template Excel per categorie
 */
function downloadCategoryTemplate() {
    // Genera un semplice CSV per categorie
    const csv = 'Nome,Immagine\nCategoria 1,\nCategoria 2,\nCategoria 3,';
    
    // Crea il download
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    
    link.setAttribute('href', url);
    link.setAttribute('download', 'template_categorie.csv');
    link.style.visibility = 'hidden';
    
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

/**
 * Download template Excel per articoli
 */
function downloadArticleTemplate() {
    // Genera un semplice CSV per articoli
    const csv = 'Nome,Categoria,Prezzo,Descrizione,Immagine\nArticolo 1,Categoria 1,9.99,Descrizione articolo 1,\nArticolo 2,Categoria 2,12.50,Descrizione articolo 2,\nArticolo 3,Categoria 3,8.75,Descrizione articolo 3,';
    
    // Crea il download
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    
    link.setAttribute('href', url);
    link.setAttribute('download', 'template_articoli.csv');
    link.style.visibility = 'hidden';
    
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
} 