/**
 * loadExistingArticles.js - Carica e visualizza gli articoli esistenti
 */

document.addEventListener('DOMContentLoaded', function() {
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
    const container = document.getElementById('existingArticles');
    if (!container) return;
    
    if (!articles || articles.length === 0) {
        container.innerHTML = '<p class="no-items">Nessun articolo trovato. Aggiungi il tuo primo articolo!</p>';
        return;
    }
    
    let html = '';
    
    articles.forEach(article => {
        html += `
            <div class="item-card">
                <div class="item-info">
                    <img src="${article.image_url || '/menu_digitale/images/placeholder.jpg'}" alt="${article.name}" class="item-image">
                    <div class="item-details">
                        <div class="item-name">${article.name}</div>
                        <div class="item-description">${article.description}</div>
                        <div class="item-price">${formatPrice(article.price)}</div>
                        <div class="item-category">${article.category_name}</div>
                    </div>
                </div>
                <div class="item-actions">
                    <button class="edit-btn" data-id="${article.article_id}">Modifica</button>
                    <button class="delete-btn" data-id="${article.article_id}">Elimina</button>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
    
    // Aggiungi event listeners ai pulsanti
    setupArticleButtons();
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
    // Creo l'elemento del modal
    const modal = document.createElement('div');
    modal.id = 'editArticleModal';
    modal.className = 'modal';
    
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h3>Modifica Articolo</h3>
                <span class="close-modal">&times;</span>
            </div>
            <form id="editArticleForm">
                <input type="hidden" id="editArticleId" name="article_id">
                <div class="form-group">
                    <label for="editArticleName">Nome Articolo:</label>
                    <input type="text" id="editArticleName" name="name" required>
                </div>
                <div class="form-group">
                    <label for="editArticleCategory">Categoria:</label>
                    <select id="editArticleCategory" name="category_id" required>
                        <option value="">Seleziona una categoria</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="editArticleDescription">Descrizione:</label>
                    <textarea id="editArticleDescription" name="description" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="editArticlePrice">Prezzo (€):</label>
                    <input type="number" id="editArticlePrice" name="price" step="0.01" min="0" required>
                </div>
                <div class="form-group">
                    <label for="editArticleImage">Immagine Articolo:</label>
                    <input type="file" id="editArticleImage" name="image" accept="image/*">
                    <p class="form-hint">Lascia vuoto per mantenere l'immagine attuale</p>
                    <div id="currentArticleImage"></div>
                </div>
                <button type="submit" class="submit-btn">Aggiorna Articolo</button>
            </form>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Gestisco la chiusura del modal
    const closeModal = modal.querySelector('.close-modal');
    closeModal.addEventListener('click', function() {
        modal.style.display = 'none';
    });
    
    // Chiudo il modal quando si clicca fuori dal contenuto
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
    
    // Gestisco il submit del form di modifica
    const editForm = document.getElementById('editArticleForm');
    editForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const articleId = document.getElementById('editArticleId').value;
        const formData = new FormData(editForm);
        
        // Mostro spinner di caricamento
        const submitBtn = editForm.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.textContent;
        submitBtn.innerHTML = '<div class="spinner" style="width:20px;height:20px;margin:0 auto;"></div>';
        submitBtn.disabled = true;
        
        fetch('/menu_digitale/api/update_article.php', {
            method: 'POST',
            body: formData,
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('auth_token')
            }
        })
        .then(response => {
            return response.json().then(data => {
                if (!response.ok) {
                    throw new Error(data.message || 'Errore durante l\'aggiornamento dell\'articolo');
                }
                return data;
            });
        })
        .then(data => {
            showNotification('Articolo aggiornato con successo!', 'success');
            modal.style.display = 'none';
            loadExistingArticles(document.getElementById('categoryFilter').value);
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification(error.message || 'Errore durante l\'aggiornamento dell\'articolo', 'error');
        })
        .finally(() => {
            // Ripristino il testo del pulsante
            submitBtn.innerHTML = originalBtnText;
            submitBtn.disabled = false;
        });
    });
    
    // Gestisco anteprima immagine
    const imageInput = document.getElementById('editArticleImage');
    imageInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const imgContainer = document.getElementById('currentArticleImage');
                imgContainer.innerHTML = `<img src="${e.target.result}" alt="Anteprima" style="max-width: 200px; margin-top: 10px;">`;
            };
            reader.readAsDataURL(this.files[0]);
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
        
        const imagePreview = document.getElementById('currentArticleImage');
        if (article.image_url) {
            imagePreview.innerHTML = `<img src="${article.image_url}" alt="${article.name}" style="max-width: 200px; margin-top: 10px; border-radius: 4px;">`;
        } else {
            imagePreview.innerHTML = '<p>Nessuna immagine disponibile</p>';
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
    if (typeof window.showNotification === 'function') {
        window.showNotification(message, type);
        return;
    }
    
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.add('show');
        
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 5000);
    }, 10);
} 