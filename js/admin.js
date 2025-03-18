document.addEventListener('DOMContentLoaded', function() {
    // Verifica autenticazione
    if (!isAuthenticated()) {
        window.location.href = 'login.html';
        return;
    }

    // Setup tab navigation
    setupTabs();
    
    // Setup forms
    setupCategoryForm();
    setupArticleForm();
    
    // Setup image previews
    setupImagePreviews();
    
    // Setup logout
    document.getElementById('logoutButton').addEventListener('click', function() {
        localStorage.removeItem('auth_token');
        window.location.href = 'login.html';
    });
});

function setupTabs() {
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Remove active class from all tabs
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Add active class to clicked tab
            button.classList.add('active');
            const tabId = button.getAttribute('data-tab');
            document.getElementById(tabId).classList.add('active');
        });
    });
}

function setupImagePreviews() {
    // Category image preview
    const categoryImage = document.getElementById('categoryImage');
    const categoryPreview = document.getElementById('categoryImagePreview');
    
    categoryImage.addEventListener('change', () => {
        displayImagePreview(categoryImage, categoryPreview);
    });
    
    // Article image preview
    const articleImage = document.getElementById('articleImage');
    const articlePreview = document.getElementById('articleImagePreview');
    
    articleImage.addEventListener('change', () => {
        displayImagePreview(articleImage, articlePreview);
    });
}

function displayImagePreview(input, previewElement) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            previewElement.src = e.target.result;
            previewElement.style.display = 'block';
        };
        
        reader.readAsDataURL(input.files[0]);
    }
}

function setupCategoryForm() {
    const form = document.getElementById('addCategoryForm');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const nameInput = document.getElementById('categoryName');
        const imageInput = document.getElementById('categoryImage');
        
        if (!nameInput.value.trim()) {
            showNotification('Inserisci un nome per la categoria', 'error');
            return;
        }
        
        if (!imageInput.files || !imageInput.files[0]) {
            showNotification('Seleziona un\'immagine per la categoria', 'error');
            return;
        }
        
        const formData = new FormData(form);
        const token = localStorage.getItem('auth_token');
        
        // Mostra spinner di caricamento
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.textContent;
        submitBtn.innerHTML = '<div class="spinner" style="width:20px;height:20px;margin:0 auto;"></div>';
        submitBtn.disabled = true;
        
        fetch('/menu_digitale/api/add_category.php', {
            method: 'POST',
            body: formData,
            headers: {
                'Authorization': 'Bearer ' + token
            }
        })
        .then(response => {
            return response.json().then(data => {
                if (!response.ok) {
                    throw new Error(data.message || 'Errore durante l\'aggiunta della categoria');
                }
                return data;
            });
        })
        .then(data => {
            showNotification('Categoria aggiunta con successo!', 'success');
            form.reset();
            document.getElementById('categoryImagePreview').style.display = 'none';
            // Ricarica le categorie esistenti
            if (typeof loadExistingCategories === 'function') {
                loadExistingCategories();
            }
            // Ricarica le categorie per il select degli articoli
            loadCategoriesForSelect();
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification(error.message || 'Errore durante l\'aggiunta della categoria', 'error');
        })
        .finally(() => {
            // Ripristina il testo del pulsante
            submitBtn.innerHTML = originalBtnText;
            submitBtn.disabled = false;
        });
    });
}

function setupArticleForm() {
    const form = document.getElementById('addArticleForm');
    
    // Carica le categorie per il select
    loadCategoriesForSelect();
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const nameInput = document.getElementById('articleName');
        const categoryInput = document.getElementById('articleCategory');
        const priceInput = document.getElementById('articlePrice');
        const imageInput = document.getElementById('articleImage');
        
        if (!nameInput.value.trim()) {
            showNotification('Inserisci un nome per l\'articolo', 'error');
            return;
        }
        
        if (!categoryInput.value) {
            showNotification('Seleziona una categoria per l\'articolo', 'error');
            return;
        }
        
        if (!priceInput.value || isNaN(priceInput.value) || parseFloat(priceInput.value) < 0) {
            showNotification('Inserisci un prezzo valido per l\'articolo', 'error');
            return;
        }
        
        if (!imageInput.files || !imageInput.files[0]) {
            showNotification('Seleziona un\'immagine per l\'articolo', 'error');
            return;
        }
        
        const formData = new FormData(form);
        const token = localStorage.getItem('auth_token');
        
        // Mostra spinner di caricamento
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.textContent;
        submitBtn.innerHTML = '<div class="spinner" style="width:20px;height:20px;margin:0 auto;"></div>';
        submitBtn.disabled = true;
        
        fetch('/menu_digitale/api/add_article.php', {
            method: 'POST',
            body: formData,
            headers: {
                'Authorization': 'Bearer ' + token
            }
        })
        .then(response => {
            return response.json().then(data => {
                if (!response.ok) {
                    throw new Error(data.message || 'Errore durante l\'aggiunta dell\'articolo');
                }
                return data;
            });
        })
        .then(data => {
            showNotification('Articolo aggiunto con successo!', 'success');
            form.reset();
            document.getElementById('articleImagePreview').style.display = 'none';
            // Ricarica gli articoli esistenti
            if (typeof loadExistingArticles === 'function') {
                loadExistingArticles();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification(error.message || 'Errore durante l\'aggiunta dell\'articolo', 'error');
        })
        .finally(() => {
            // Ripristina il testo del pulsante
            submitBtn.innerHTML = originalBtnText;
            submitBtn.disabled = false;
        });
    });
}

function loadCategoriesForSelect() {
    const categorySelect = document.getElementById('articleCategory');
    const categoryFilter = document.getElementById('categoryFilter');
    
    if (!categorySelect) {
        console.error('Elemento select categoria non trovato');
        return;
    }
    
    // Mostra spinner di caricamento nel select
    categorySelect.innerHTML = '<option value="">Caricamento categorie...</option>';
    
    const token = localStorage.getItem('auth_token');
    fetch('/menu_digitale/api/get_categories.php', {
        method: 'GET',
        headers: {
            'Authorization': 'Bearer ' + token
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Errore durante il caricamento delle categorie');
        }
        return response.json();
    })
    .then(data => {
        // Svuota il select
        categorySelect.innerHTML = '<option value="">Seleziona una categoria</option>';
        
        if (categoryFilter) {
            categoryFilter.innerHTML = '<option value="all">Tutti gli articoli</option>';
        }
        
        // Aggiungi le opzioni
        data.forEach(category => {
            const option = document.createElement('option');
            option.value = category.category_id;
            option.textContent = category.name;
            categorySelect.appendChild(option);
            
            // Aggiungi anche al filtro categorie negli articoli
            if (categoryFilter) {
                const filterOption = document.createElement('option');
                filterOption.value = category.category_id;
                filterOption.textContent = category.name;
                categoryFilter.appendChild(filterOption);
            }
        });
    })
    .catch(error => {
        console.error('Error:', error);
        categorySelect.innerHTML = '<option value="">Errore di caricamento</option>';
        showNotification('Errore durante il caricamento delle categorie', 'error');
    });
}

// Funzione per mostrare notifiche all'utente
function showNotification(message, type = 'info') {
    // Rimuovi notifiche precedenti
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notification => {
        notification.remove();
    });
    
    // Crea l'elemento di notifica
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    // Aggiungi l'elemento al body
    document.body.appendChild(notification);
    
    // Mostra la notifica con animazione
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);
    
    // Rimuovi la notifica dopo 5 secondi
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 5000);
} 