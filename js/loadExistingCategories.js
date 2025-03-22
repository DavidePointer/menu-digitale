/**
 * loadExistingCategories.js - Carica e visualizza le categorie esistenti
 */

document.addEventListener('DOMContentLoaded', function() {
    // Creo il modal per modifica categorie
    createCategoryEditModal();
    
    // Carico le categorie esistenti
    loadExistingCategories();
});

/**
 * Carica le categorie esistenti dal server
 */
function loadExistingCategories() {
    console.log("Caricamento categorie esistenti...");
    const token = localStorage.getItem('auth_token');
    const container = document.getElementById('existingCategories');
    
    if (!container) {
        console.error("Container 'existingCategories' non trovato!");
        return;
    }
    
    // Mostro il loader
    container.innerHTML = `
        <div class="loader-container">
            <div class="spinner"></div>
            <p>Caricamento categorie in corso...</p>
        </div>
    `;
    
    fetch('/menu_digitale/api/get_categories.php', {
        method: 'GET',
        headers: {
            'Authorization': 'Bearer ' + token
        }
    })
    .then(response => {
        console.log("Risposta API categorie:", response.status);
        if (!response.ok) {
            throw new Error('Errore durante il caricamento delle categorie');
        }
        return response.json();
    })
    .then(data => {
        console.log("Dati categorie ricevuti:", data);
        
        if (!Array.isArray(data)) {
            console.error("I dati ricevuti non sono un array:", data);
            throw new Error('Formato dati non valido');
        }
        
        if (data.length === 0) {
            container.innerHTML = '<div class="no-items">Nessuna categoria trovata</div>';
            return;
        }
        
        // Genero l'HTML per ogni categoria
        const categoriesHTML = data.map(category => {
            console.log("Elaborazione categoria:", category);
            return generateCategoryHTML(category);
        }).join('');
        
        console.log("HTML generato:", categoriesHTML);
        container.innerHTML = categoriesHTML;
        
        // Aggiungo gli event listener ai pulsanti
        setupCategoryButtons();
    })
    .catch(error => {
        console.error('Error:', error);
        container.innerHTML = `
            <div class="error-message">
                Errore durante il caricamento delle categorie: ${error.message}
            </div>
        `;
    });
}

function generateCategoryHTML(category) {
    if (!category || typeof category !== 'object') {
        console.error("Categoria non valida:", category);
        return '';
    }

    const {
        category_id,
        name = 'Categoria senza nome',
        image_url = '/menu_digitale/images/placeholder.jpg',
        article_count = 0
    } = category;

    if (!category_id) {
        console.error("ID categoria mancante:", category);
        return '';
    }

    console.log(`Generazione HTML per categoria: ID=${category_id}, Nome=${name}`);

    return `
        <div class="category-item" data-id="${category_id}">
            <div class="category-info">
                <div class="category-image">
                    <img src="${image_url}" alt="${name}" onerror="this.src='/menu_digitale/images/placeholder.jpg'">
                </div>
                <div class="category-details">
                    <h3>${name}</h3>
                    <p>Articoli associati: ${article_count}</p>
                </div>
            </div>
            <div class="category-actions">
                <button class="edit-btn" onclick="editCategory(${category_id})">
                    <i class="fas fa-edit"></i> Modifica
                </button>
                <button class="delete-btn" onclick="deleteCategory(${category_id})">
                    <i class="fas fa-trash"></i> Elimina
                </button>
            </div>
        </div>
    `;
}

/**
 * Imposta gli event listeners per i pulsanti di modifica e cancellazione
 */
function setupCategoryButtons() {
    // Gestisco i pulsanti di modifica
    const editButtons = document.querySelectorAll('.category-card .edit-btn');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const categoryId = this.getAttribute('data-id');
            editCategory(categoryId);
        });
    });
    
    // Gestisco i pulsanti di eliminazione
    const deleteButtons = document.querySelectorAll('.category-card .delete-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const categoryId = this.getAttribute('data-id');
            if (confirm('Sei sicuro di voler eliminare questa categoria? Tutti gli articoli associati saranno eliminati.')) {
                deleteCategory(categoryId);
            }
        });
    });
}

/**
 * Crea il modal per la modifica delle categorie
 */
function createCategoryEditModal() {
    // Creo l'elemento del modal
    const modal = document.createElement('div');
    modal.id = 'editCategoryModal';
    modal.className = 'modal';
    
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h3>Modifica Categoria</h3>
                <span class="close-modal">&times;</span>
            </div>
            <form id="editCategoryForm">
                <input type="hidden" id="editCategoryId" name="category_id">
                <div class="form-group">
                    <label for="editCategoryName">Nome Categoria:</label>
                    <input type="text" id="editCategoryName" name="name" required>
                </div>
                <div class="form-group">
                    <label for="editCategoryImage">Immagine Categoria:</label>
                    <input type="file" id="editCategoryImage" name="image" accept="image/*">
                    <p class="form-hint">Lascia vuoto per mantenere l'immagine attuale</p>
                    <div id="currentCategoryImage"></div>
                </div>
                <button type="submit" class="submit-btn">Aggiorna Categoria</button>
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
    const editForm = document.getElementById('editCategoryForm');
    editForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const categoryId = document.getElementById('editCategoryId').value;
        const formData = new FormData(editForm);
        
        // Mostro spinner di caricamento
        const submitBtn = editForm.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.textContent;
        submitBtn.innerHTML = '<div class="spinner" style="width:20px;height:20px;margin:0 auto;"></div>';
        submitBtn.disabled = true;
        
        fetch('/menu_digitale/api/update_category.php', {
            method: 'POST',
            body: formData,
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('auth_token')
            }
        })
        .then(response => {
            return response.json().then(data => {
                if (!response.ok) {
                    throw new Error(data.message || 'Errore durante l\'aggiornamento della categoria');
                }
                return data;
            });
        })
        .then(data => {
            showNotification('Categoria aggiornata con successo!', 'success');
            modal.style.display = 'none';
            loadExistingCategories();
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification(error.message || 'Errore durante l\'aggiornamento della categoria', 'error');
        })
        .finally(() => {
            // Ripristino il testo del pulsante
            submitBtn.innerHTML = originalBtnText;
            submitBtn.disabled = false;
        });
    });
    
    // Gestisco anteprima immagine
    const imageInput = document.getElementById('editCategoryImage');
    imageInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const imgContainer = document.getElementById('currentCategoryImage');
                imgContainer.innerHTML = `<img src="${e.target.result}" alt="Anteprima" style="max-width: 200px; margin-top: 10px;">`;
            };
            reader.readAsDataURL(this.files[0]);
        }
    });
}

/**
 * Modifica una categoria
 */
function editCategory(categoryId) {
    console.log("Modifica categoria ID:", categoryId);
    const token = localStorage.getItem('auth_token');
    
    // Mostro il modal
    const modal = document.getElementById('editCategoryModal');
    const form = document.getElementById('editCategoryForm');
    const imgContainer = document.getElementById('currentCategoryImage');
    
    // Pulisco il form
    form.reset();
    imgContainer.innerHTML = '<div class="spinner" style="margin: 10px auto;"></div>';
    
    // Imposto l'ID della categoria
    document.getElementById('editCategoryId').value = categoryId;
    
    // Carico i dati della categoria
    const apiUrl = `/menu_digitale/api/get_category.php?category_id=${categoryId}`;
    console.log("Chiamata API:", apiUrl);
    
    fetch(apiUrl, {
        method: 'GET',
        headers: {
            'Authorization': 'Bearer ' + token
        }
    })
    .then(response => {
        console.log("Status risposta:", response.status);
        if (!response.ok) {
            throw new Error('Errore durante il caricamento dei dati della categoria');
        }
        return response.json();
    })
    .then(category => {
        console.log("Dati categoria ricevuti:", category);
        
        // Compilo il form con i dati della categoria
        document.getElementById('editCategoryId').value = category.category_id;
        document.getElementById('editCategoryName').value = category.name || '';
        
        // Mostro l'immagine attuale
        if (category.image_url) {
            imgContainer.innerHTML = `<img src="${category.image_url}" alt="${category.name}" style="max-width: 200px; margin-top: 10px; border-radius: 4px;">`;
        } else {
            imgContainer.innerHTML = `<p>Nessuna immagine disponibile</p>`;
        }
        
        // Mostro il modal
        modal.style.display = 'block';
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification(error.message || 'Errore durante il caricamento dei dati', 'error');
    });
}

/**
 * Elimina una categoria
 */
function deleteCategory(categoryId) {
    const token = localStorage.getItem('auth_token');
    
    fetch('/menu_digitale/api/delete_category.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + token
        },
        body: JSON.stringify({ category_id: categoryId })
    })
    .then(response => {
        return response.json().then(data => {
            if (!response.ok) {
                throw new Error(data.message || 'Errore durante l\'eliminazione della categoria');
            }
            return data;
        });
    })
    .then(data => {
        showNotification('Categoria eliminata con successo!', 'success');
        loadExistingCategories();
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification(error.message || 'Errore durante l\'eliminazione della categoria', 'error');
    });
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