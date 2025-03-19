/**
 * bulkEditArticles.js - Gestione delle operazioni di modifica in massa degli articoli
 */

document.addEventListener('DOMContentLoaded', function() {
    // Inizializza i controlli di selezione e modifica in massa
    initBulkControls();
    
    // Inizializza i modali per conferma operazioni
    initModals();
});

/**
 * Inizializza i controlli per la selezione e modifica in massa
 */
function initBulkControls() {
    // Riferimenti agli elementi dell'interfaccia
    const selectAllCheckbox = document.getElementById('selectAllArticles');
    const bulkActionsButtons = document.querySelector('.bulk-actions-buttons');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const bulkChangeCategoryBtn = document.getElementById('bulkChangeCategoryBtn');
    
    if (!selectAllCheckbox) {
        console.log("Elemento 'selectAllArticles' non trovato.");
        return;
    }
    
    // Popola il dropdown delle categorie nel modale di cambio categoria
    populateCategoryDropdown();
    
    // Gestisci l'evento di selezione "Seleziona tutti"
    selectAllCheckbox.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.article-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
            
            // Aggiorna anche la classe visiva degli elementi
            const item = checkbox.closest('.article-item');
            if (item) {
                if (this.checked) {
                    item.classList.add('selected');
                } else {
                    item.classList.remove('selected');
                }
            }
        });
        
        // Mostra/nascondi i pulsanti di operazioni in massa
        updateBulkOperationsVisibility();
    });
    
    // Delegazione eventi per gestire i click sulle checkbox
    document.addEventListener('change', function(e) {
        if (e.target && e.target.classList.contains('article-checkbox')) {
            const item = e.target.closest('.article-item');
            if (item) {
                if (e.target.checked) {
                    item.classList.add('selected');
                } else {
                    item.classList.remove('selected');
                    
                    // Deseleziona "seleziona tutti" se una checkbox viene deselezionata
                    const selectAll = document.getElementById('selectAllArticles');
                    if (selectAll) selectAll.checked = false;
                }
            }
            
            // Aggiorna visibilità pulsanti bulk
            updateBulkOperationsVisibility();
        }
    });
    
    // Pulsante eliminazione
    if (bulkDeleteBtn) {
        bulkDeleteBtn.addEventListener('click', function() {
            const selectedArticles = getSelectedArticles();
            
            if (selectedArticles.length === 0) {
                showNotification('Seleziona almeno un articolo da eliminare', 'warning');
                return;
            }
            
            // Mostra modale di conferma con l'elenco degli articoli
            showDeleteConfirmation(selectedArticles);
        });
    } else {
        console.log("Elemento 'bulkDeleteBtn' non trovato.");
    }
    
    // Pulsante cambio categoria
    if (bulkChangeCategoryBtn) {
        bulkChangeCategoryBtn.addEventListener('click', function() {
            const selectedArticles = getSelectedArticles();
            
            if (selectedArticles.length === 0) {
                showNotification('Seleziona almeno un articolo per cambiare categoria', 'warning');
                return;
            }
            
            // Mostra modale per selezionare la nuova categoria
            showChangeCategoryModal();
        });
    } else {
        console.log("Elemento 'bulkChangeCategoryBtn' non trovato.");
    }
}

/**
 * Aggiorna la visibilità dei pulsanti di modifica in massa
 */
function updateBulkOperationsVisibility() {
    const bulkOperationsButtons = document.querySelector('.bulk-actions-buttons');
    const selectedCheckboxes = document.querySelectorAll('.article-checkbox:checked');
    
    if (bulkOperationsButtons) {
        bulkOperationsButtons.style.display = selectedCheckboxes.length > 0 ? 'flex' : 'none';
    }
}

/**
 * Ottiene gli articoli selezionati
 */
function getSelectedArticles() {
    const selectedCheckboxes = document.querySelectorAll('.article-checkbox:checked');
    const articles = [];
    
    selectedCheckboxes.forEach(checkbox => {
        const id = checkbox.getAttribute('data-id');
        const item = checkbox.closest('.article-item');
        
        if (item) {
            // Ottieni i dati dall'elemento, adattato alla nuova struttura DOM
            const nameElement = item.querySelector('h3');
            const name = nameElement ? nameElement.textContent : 'Articolo';
            
            articles.push({
                id: id,
                name: name
            });
        }
    });
    
    return articles;
}

/**
 * Inizializza i modali di conferma operazioni
 */
function initModals() {
    // Modal di eliminazione
    const deleteModal = document.getElementById('confirmDeleteModal');
    const closeDeleteBtns = deleteModal?.querySelectorAll('.close-modal, #cancelDelete');
    const confirmDeleteBtn = document.getElementById('confirmDelete');
    
    if (deleteModal) {
        // Chiudi modale
        closeDeleteBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                deleteModal.style.display = 'none';
            });
        });
        
        // Conferma eliminazione
        if (confirmDeleteBtn) {
            confirmDeleteBtn.addEventListener('click', function() {
                const selectedArticles = getSelectedArticles();
                deleteArticles(selectedArticles);
                deleteModal.style.display = 'none';
            });
        }
    }
    
    // Modal cambio categoria
    const categoryModal = document.getElementById('changeCategoryModal');
    const closeCategoryBtns = categoryModal?.querySelectorAll('.close-modal, #cancelCategoryChange');
    const confirmCategoryBtn = document.getElementById('confirmCategoryChange');
    
    if (categoryModal) {
        // Chiudi modale
        closeCategoryBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                categoryModal.style.display = 'none';
            });
        });
        
        // Conferma cambio categoria
        if (confirmCategoryBtn) {
            confirmCategoryBtn.addEventListener('click', function() {
                const selectedArticles = getSelectedArticles();
                const targetCategoryId = document.getElementById('targetCategorySelect').value;
                
                if (!targetCategoryId) {
                    showNotification('Seleziona una categoria di destinazione', 'error');
                    return;
                }
                
                changeArticlesCategory(selectedArticles, targetCategoryId);
                categoryModal.style.display = 'none';
            });
        }
    }
    
    // Chiudi il modale se si fa clic all'esterno
    window.addEventListener('click', function(e) {
        if (e.target === deleteModal) deleteModal.style.display = 'none';
        if (e.target === categoryModal) categoryModal.style.display = 'none';
    });
}

/**
 * Popola il dropdown delle categorie nel modale
 */
function populateCategoryDropdown() {
    const targetCategory = document.getElementById('targetCategorySelect');
    
    if (!targetCategory) return;
    
    // Fetch delle categorie esistenti
    const token = localStorage.getItem('auth_token');
    
    fetch('/menu_digitale/api/get_categories.php', {
        method: 'GET',
        headers: {
            'Authorization': 'Bearer ' + token
        }
    })
    .then(response => response.json())
    .then(categories => {
        if (!categories || categories.length === 0) return;
        
        // Genera le opzioni per il dropdown
        let options = '<option value="">Seleziona categoria</option>';
        categories.forEach(category => {
            options += `<option value="${category.category_id}">${category.name}</option>`;
        });
        
        // Aggiorna il dropdown
        targetCategory.innerHTML = options;
    })
    .catch(error => {
        console.error('Errore nel caricamento delle categorie:', error);
    });
}

/**
 * Mostra il modale di conferma eliminazione
 */
function showDeleteConfirmation(articles) {
    const modal = document.getElementById('confirmDeleteModal');
    const articlesList = document.getElementById('deleteArticlesList');
    
    if (!modal || !articlesList) return;
    
    // Genera l'elenco degli articoli da eliminare
    let html = '<ul class="delete-list">';
    articles.forEach(article => {
        html += `<li>${article.name}</li>`;
    });
    html += '</ul>';
    
    articlesList.innerHTML = html;
    modal.style.display = 'block';
}

/**
 * Mostra il modale per cambiare categoria
 */
function showChangeCategoryModal() {
    const modal = document.getElementById('changeCategoryModal');
    
    if (!modal) return;
    
    // Reset selezione
    const targetSelect = document.getElementById('targetCategorySelect');
    if (targetSelect) targetSelect.value = '';
    
    modal.style.display = 'block';
}

/**
 * Elimina gli articoli selezionati
 */
function deleteArticles(articles) {
    // Dati da inviare al server
    const data = {
        action: 'delete',
        articles: articles.map(article => article.id)
    };
    
    // Invia la richiesta al server
    const token = localStorage.getItem('auth_token');
    
    fetch('/menu_digitale/api/bulk_edit_articles.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + token
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showNotification(`Eliminati ${articles.length} articoli con successo!`, 'success');
            
            // Ricarica gli articoli
            loadExistingArticles();
        } else {
            showNotification('Errore durante l\'eliminazione: ' + result.message, 'error');
        }
    })
    .catch(error => {
        console.error('Errore:', error);
        showNotification('Errore nella comunicazione con il server', 'error');
    });
}

/**
 * Cambia la categoria degli articoli selezionati
 */
function changeArticlesCategory(articles, targetCategoryId) {
    // Dati da inviare al server
    const data = {
        action: 'change_category',
        target_category_id: targetCategoryId,
        articles: articles.map(article => article.id)
    };
    
    // Invia la richiesta al server
    const token = localStorage.getItem('auth_token');
    
    fetch('/menu_digitale/api/bulk_edit_articles.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + token
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showNotification(`Categoria modificata per ${articles.length} articoli!`, 'success');
            
            // Ricarica gli articoli
            loadExistingArticles();
        } else {
            showNotification('Errore durante la modifica: ' + result.message, 'error');
        }
    })
    .catch(error => {
        console.error('Errore:', error);
        showNotification('Errore nella comunicazione con il server', 'error');
    });
} 