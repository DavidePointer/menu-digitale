/**
 * loadExistingArticles.js - Carica e visualizza gli articoli esistenti
 */

document.addEventListener('DOMContentLoaded', function() {
    try {
        console.log('Inizializzazione sistema di visualizzazione...');
        
        // Creo il modal per modifica articoli
        createArticleEditModal();
        
        // Carico gli articoli esistenti
        loadExistingArticles();
        
        // Gestisco il filtro per categoria
        const filterEl = document.getElementById('categoryFilter');
        if (filterEl) {
            filterEl.addEventListener('change', function() {
                loadExistingArticles(this.value);
            });
        }

        // Gestisco il cambio di vista
        window.addEventListener('viewModeChanged', function(e) {
            try {
                console.log('Evento viewModeChanged ricevuto:', e.detail);
                
                if (e.detail && e.detail.mode) {
                    console.log('Nuova modalità di visualizzazione:', e.detail.mode);
                    
                    // Ricarica gli articoli con la nuova vista
                    loadExistingArticles(filterEl ? filterEl.value : 'all');
                }
            } catch (error) {
                console.error('Errore nella gestione dell\'evento viewModeChanged:', error);
                showNotification('Errore nel cambio visualizzazione: ' + error.message, 'error');
            }
        });
        
        console.log('Sistema di visualizzazione inizializzato con successo');
    } catch (error) {
        console.error('Errore durante l\'inizializzazione:', error);
        showNotification('Errore durante l\'inizializzazione: ' + error.message, 'error');
    }
});

/**
 * Carica gli articoli esistenti dal server
 * @param {string} categoryFilter - ID categoria per filtrare (opzionale)
 */
function loadExistingArticles(categoryFilter = 'all') {
    console.log("Caricamento articoli esistenti...", categoryFilter);
    const token = localStorage.getItem('auth_token');
    
    // Spinner di caricamento
    const articlesContainer = document.getElementById('existingArticles');
    if (articlesContainer) {
        articlesContainer.innerHTML = `
            <div class="loader-container">
                <div class="spinner"></div>
                <p>Caricamento articoli...</p>
            </div>
        `;
    }
    
    // Costruisci l'URL con il parametro di filtro categoria
    let url = '/menu_digitale/api/get_articles.php';
    if (categoryFilter && categoryFilter !== 'all') {
        url += `?category_id=${categoryFilter}`;
    }
    
    fetch(url, {
        headers: {
            'Authorization': 'Bearer ' + token
        }
    })
    .then(response => {
        console.log("Risposta articoli ricevuta:", response.status, response.statusText);
        if (!response.ok) {
            throw new Error(`Risposta non valida: ${response.status} ${response.statusText}`);
        }
        return response.text();
    })
    .then(text => {
        console.log("Risposta articoli:", text);
        try {
            const articles = JSON.parse(text);
            displayArticles(articles);
            
            // Aggiorna anche il select per il filtro categorie se necessario
            if (categoryFilter === 'all') {
                updateCategoryFilter(articles);
            }
        } catch (e) {
            console.error("Errore parsing JSON articoli:", e);
            showArticlesError("Errore nel parsing della risposta dal server");
        }
    })
    .catch(error => {
        console.error("Errore caricamento articoli:", error);
        showArticlesError("Errore nel caricamento degli articoli: " + error.message);
    });
}

/**
 * Visualizza gli articoli nella pagina
 */
function displayArticles(articles) {
    try {
        console.log('Visualizzazione articoli...');
        
        const container = document.getElementById('existingArticles');
        if (!container) {
            throw new Error('Container articoli non trovato');
        }
        
        if (!articles || articles.length === 0) {
            container.innerHTML = '<p class="no-items">Nessun articolo trovato. Aggiungi il tuo primo articolo!</p>';
            return;
        }
        
        // Ottieni la vista corrente
        const currentView = localStorage.getItem('articleViewMode') || 'card';
        console.log('Modalità di visualizzazione corrente:', currentView);
        
        // Imposta la classe del container
        container.className = `menu-items view-${currentView}`;
        console.log('Classe container aggiornata:', container.className);
        
        // Genera l'HTML per ogni articolo
        const html = articles
            .map((item, index) => generateItemHTML(item, index, currentView, false))
            .join('');
        
        container.innerHTML = html;
        console.log('HTML articoli generato e inserito');
        
        // Aggiungi event listeners ai pulsanti
        setupArticleButtons();
        
        console.log('Visualizzazione articoli completata');
    } catch (error) {
        console.error('Errore durante la visualizzazione degli articoli:', error);
        showNotification('Errore durante la visualizzazione degli articoli: ' + error.message, 'error');
    }
}

/**
 * Codifica il testo per l'output HTML
 */
function encodeForHTML(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function generateItemHTML(item, index, viewMode, isFromSearch) {
    const safeItemName = encodeForHTML(item.name);
    const safeItemDesc = item.description ? encodeForHTML(item.description) : '';
    const safeItemImageUrl = item.image_url || '/menu_digitale/images/placeholder.jpg';
    const safeItemCategory = encodeForHTML(item.category_name);
    const animationClass = isFromSearch ? '' : 'with-animation';
    const itemId = item.article_id;
    
    if (viewMode === 'list') {
        // Vista Lista: struttura compatta senza immagine
        return `
            <div class="list-item ${animationClass}" data-id="${itemId}" style="--item-index: ${index}">
                <div class="list-item-content">
                    <div class="list-item-header">
                        <h3 class="list-item-name">${safeItemName}</h3>
                        <div class="list-item-price">${formatPrice(item.price)}</div>
                    </div>
                    ${safeItemDesc ? `<p class="list-item-description">${safeItemDesc}</p>` : ''}
                    <div class="list-item-footer">
                        <span class="list-item-category">${safeItemCategory}</span>
                        <div class="list-item-actions">
                            <button class="edit-btn" data-id="${itemId}">Modifica</button>
                            <button class="delete-btn" data-id="${itemId}">Elimina</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    } else {
        // Vista Card: struttura con immagine a destra
        return `
            <div class="card-item ${animationClass}" data-id="${itemId}" style="--item-index: ${index}">
                <div class="card-item-content">
                    <div class="card-item-category">${safeItemCategory}</div>
                    <h3 class="card-item-name">${safeItemName}</h3>
                    ${safeItemDesc ? `<p class="card-item-description">${safeItemDesc}</p>` : ''}
                    <div class="card-item-price">${formatPrice(item.price)}</div>
                    <div class="card-item-actions">
                        <button class="edit-btn" data-id="${itemId}">Modifica</button>
                        <button class="delete-btn" data-id="${itemId}">Elimina</button>
                    </div>
                </div>
                <div class="card-item-image">
                    <img src="${safeItemImageUrl}" 
                         alt="${safeItemName}" 
                         onerror="this.onerror=null; this.src='/menu_digitale/images/placeholder.jpg'">
                </div>
            </div>
        `;
    }
}

/**
 * Aggiorna il filtro categorie nel dropdown
 */
function updateCategoryFilter(articles) {
    const filterSelect = document.getElementById('categoryFilter');
    if (!filterSelect) return;
    
    // Mantieni l'opzione "Tutte le categorie"
    let options = '<option value="all">Tutte le categorie</option>';
    
    // Crea un set di categorie uniche
    const categories = new Map();
    
    articles.forEach(article => {
        if (article.category_id && article.category_name && !categories.has(article.category_id)) {
            categories.set(article.category_id, article.category_name);
        }
    });
    
    // Aggiungi le opzioni al select
    categories.forEach((name, id) => {
        options += `<option value="${id}">${name}</option>`;
    });
    
    filterSelect.innerHTML = options;
}

/**
 * Imposta gli event listeners per i pulsanti di modifica e cancellazione
 */
function setupArticleButtons() {
    // Pulsanti modifica
    document.querySelectorAll('#existingArticles .edit-btn').forEach(button => {
        button.addEventListener('click', function() {
            const articleId = this.getAttribute('data-id');
            editArticle(articleId);
        });
    });
    
    // Pulsanti elimina
    document.querySelectorAll('#existingArticles .delete-btn').forEach(button => {
        button.addEventListener('click', function() {
            const articleId = this.getAttribute('data-id');
            if (confirm('Sei sicuro di voler eliminare questo articolo? Questa azione non può essere annullata.')) {
                deleteArticle(articleId);
            }
        });
    });
}

/**
 * Crea il modal per la modifica degli articoli
 */
function createArticleEditModal() {
    const modalHTML = `
        <div id="editArticleModal" class="modal" style="display: none;">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">Modifica Articolo</h2>
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="editArticleForm" class="modal-form">
                        <input type="hidden" id="editArticleId">
                        <div class="form-group">
                            <label for="editArticleName">Nome Articolo:</label>
                            <input type="text" id="editArticleName" required>
                        </div>
                        <div class="form-group">
                            <label for="editArticleCategory">Categoria:</label>
                            <select id="editArticleCategory" required></select>
                        </div>
                        <div class="form-group">
                            <label for="editArticleDescription">Descrizione:</label>
                            <textarea id="editArticleDescription" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="editArticlePrice">Prezzo (€):</label>
                            <input type="number" id="editArticlePrice" step="0.01" min="0" required>
                        </div>
                        <div class="form-group">
                            <label for="editArticleImage">Immagine:</label>
                            <div class="image-preview-container" onclick="document.getElementById('editArticleImage').click()">
                                <img id="editImagePreview" class="image-preview" src="/menu_digitale/images/placeholder.jpg" alt="Anteprima">
                                <input type="file" id="editArticleImage" accept="image/*" style="display: none;">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="cancel-btn">Annulla</button>
                    <button class="save-btn">Salva Modifiche</button>
                </div>
            </div>
        </div>
    `;

    // Aggiungi il modal al DOM
    document.body.insertAdjacentHTML('beforeend', modalHTML);

    // Setup event listeners
    const modal = document.getElementById('editArticleModal');
    const closeBtn = modal.querySelector('.modal-close');
    const cancelBtn = modal.querySelector('.cancel-btn');
    const saveBtn = modal.querySelector('.save-btn');
    const imageInput = document.getElementById('editArticleImage');
    const imagePreview = document.getElementById('editImagePreview');

    // Chiusura modal
    [closeBtn, cancelBtn].forEach(btn => {
        btn.addEventListener('click', () => {
            modal.style.display = 'none';
        });
    });

    // Click fuori dal modal
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });

    // Preview immagine
    imageInput.addEventListener('change', function(e) {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
            };
            reader.readAsDataURL(this.files[0]);
        }
    });

    // Salvataggio modifiche
    saveBtn.addEventListener('click', async () => {
        const form = document.getElementById('editArticleForm');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const articleId = document.getElementById('editArticleId').value;
        const formData = new FormData();
        formData.append('article_id', articleId);
        formData.append('name', document.getElementById('editArticleName').value);
        formData.append('category_id', document.getElementById('editArticleCategory').value);
        formData.append('description', document.getElementById('editArticleDescription').value);
        formData.append('price', document.getElementById('editArticlePrice').value);

        const imageFile = document.getElementById('editArticleImage').files[0];
        if (imageFile) {
            formData.append('image', imageFile);
        }

        try {
            const token = localStorage.getItem('auth_token');
            const response = await fetch('/menu_digitale/api/update_article.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'Authorization': 'Bearer ' + token
                }
            });

            const data = await response.json();
            if (data.success) {
                showNotification('Articolo aggiornato con successo', 'success');
                modal.style.display = 'none';
                loadExistingArticles();
            } else {
                throw new Error(data.message || 'Errore durante l\'aggiornamento');
            }
        } catch (error) {
            console.error('Errore:', error);
            showNotification(error.message, 'error');
        }
    });
}

/**
 * Modifica un articolo
 */
function editArticle(articleId) {
    console.log("Modifica articolo ID:", articleId);
    const token = localStorage.getItem('auth_token');
    
    // Carica prima le categorie per popolare il select
    fetch('/menu_digitale/api/get_categories.php', {
        headers: {
            'Authorization': 'Bearer ' + token
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Risposta categorie non valida: ${response.status} ${response.statusText}`);
        }
        return response.json();
    })
    .then(categories => {
        // Popola il select delle categorie
        const categorySelect = document.getElementById('editArticleCategory');
        categorySelect.innerHTML = '';
        
        categories.forEach(category => {
            const option = document.createElement('option');
            option.value = category.category_id;
            option.textContent = category.name;
            categorySelect.appendChild(option);
        });
        
        // Ora carica i dati dell'articolo
        return fetch(`/menu_digitale/api/get_article.php?article_id=${articleId}`, {
            headers: {
                'Authorization': 'Bearer ' + token
            }
        });
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Risposta articolo non valida: ${response.status} ${response.statusText}`);
        }
        return response.json();
    })
    .then(article => {
        console.log("Dati articolo ricevuti:", article);
        
        // Popola il form con i dati dell'articolo
        document.getElementById('editArticleId').value = article.article_id;
        document.getElementById('editArticleName').value = article.name || '';
        document.getElementById('editArticleDescription').value = article.description || '';
        document.getElementById('editArticlePrice').value = article.price || 0;
        
        // Seleziona la categoria corretta
        const categorySelect = document.getElementById('editArticleCategory');
        for (let i = 0; i < categorySelect.options.length; i++) {
            if (categorySelect.options[i].value == article.category_id) {
                categorySelect.selectedIndex = i;
                break;
            }
        }
        
        const imagePreview = document.getElementById('editImagePreview');
        if (article.image_url) {
            imagePreview.src = article.image_url;
        } else {
            imagePreview.src = '/menu_digitale/images/placeholder.jpg';
        }
        
        // Mostra il modal
        document.getElementById('editArticleModal').style.display = 'block';
    })
    .catch(error => {
        console.error("Errore caricamento articolo:", error);
        showNotification('Errore nel caricamento dell\'articolo: ' + error.message, 'error');
    });
}

/**
 * Elimina un articolo
 */
function deleteArticle(articleId) {
    const token = localStorage.getItem('auth_token');
    
    fetch('/menu_digitale/api/delete_article.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + token
        },
        body: JSON.stringify({ article_id: articleId })
    })
    .then(response => {
        return response.json().then(data => {
            if (!response.ok) {
                throw new Error(data.message || 'Errore durante l\'eliminazione dell\'articolo');
            }
            return data;
        });
    })
    .then(data => {
        showNotification('Articolo eliminato con successo!', 'success');
        
        // Ricarica sia gli articoli che le categorie per aggiornare il conteggio
        setTimeout(() => {
            loadExistingArticles();
            
            // Aggiorna anche il conteggio nelle categorie
            if (typeof loadExistingCategories === 'function') {
                loadExistingCategories();
            }
        }, 500);
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification(error.message || 'Errore durante l\'eliminazione dell\'articolo', 'error');
    });
}

/**
 * Mostra un errore nella sezione articoli
 */
function showArticlesError(message) {
    const container = document.getElementById('existingArticles');
    if (container) {
        container.innerHTML = `<div class="error-message">${message}</div>`;
    }
}

/**
 * Formatta il prezzo con il simbolo dell'euro
 */
function formatPrice(price) {
    return '€ ' + parseFloat(price).toFixed(2);
}

// Funzione per mostrare notifiche (se non disponibile globalmente)
function showNotification(message, type = 'info') {
    // Verifica se esiste già una funzione globale showNotification
    if (typeof window.showNotification === 'function' && window.showNotification !== showNotification) {
        window.showNotification(message, type);
        return;
    }
    
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Aggiungi la classe show dopo un breve delay per attivare l'animazione
    setTimeout(() => {
        notification.classList.add('show');
        
        // Rimuovi la notifica dopo 5 secondi
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 5000);
    }, 10);
} 