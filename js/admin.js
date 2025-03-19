document.addEventListener('DOMContentLoaded', function() {
    // Verifica autenticazione
    checkAuth();

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
    } else {
        previewElement.style.display = 'none';
    }
}

function setupCategoryForm() {
    const form = document.getElementById('addCategoryForm');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const nameInput = document.getElementById('categoryName');
        
        if (!nameInput.value.trim()) {
            showNotification('Inserisci un nome per la categoria', 'error');
            return;
        }
        
        const formData = new FormData(form);
        const token = localStorage.getItem('auth_token');
        
        // Mostra spinner di caricamento
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.textContent;
        submitBtn.innerHTML = '<div class="spinner" style="width:20px;height:20px;margin:0 auto;"></div>';
        submitBtn.disabled = true;
        
        // Usa XMLHttpRequest per maggiore controllo sulla richiesta
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '/menu_digitale/api/add_category.php');
        xhr.setRequestHeader('Authorization', 'Bearer ' + token);
        
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                // Ripristina il testo del pulsante
                submitBtn.innerHTML = originalBtnText;
                submitBtn.disabled = false;
                
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            showNotification('Categoria aggiunta con successo!', 'success');
                            form.reset();
                            document.getElementById('categoryImagePreview').style.display = 'none';
                            
                            // Ricarica le categorie esistenti con ritardo
                            setTimeout(() => {
                                if (typeof loadExistingCategories === 'function') {
                                    loadExistingCategories();
                                }
                                
                                // Ricarica le categorie per il select degli articoli
                                loadCategoriesForSelect(response.category_id);
                                
                                // Cambia tab agli articoli per maggiore comodità
                                if (response.category_id) {
                                    // Trova il pulsante della tab articoli e simuliamo un click
                                    const articlesTabBtn = document.querySelector('.tab-button[data-tab="articles"]');
                                    if (articlesTabBtn) {
                                        articlesTabBtn.click();
                                    }
                                }
                            }, 500);
                        } else {
                            showNotification(response.message || 'Errore durante l\'aggiunta della categoria', 'error');
                        }
                    } catch (e) {
                        console.error('Errore nel parsing JSON:', e);
                        console.error('Risposta ricevuta:', xhr.responseText);
                        showNotification('Errore nel parsing della risposta dal server', 'error');
                    }
                } else {
                    showNotification('Errore del server: ' + xhr.status, 'error');
                }
            }
        };
        
        xhr.onerror = function() {
            submitBtn.innerHTML = originalBtnText;
            submitBtn.disabled = false;
            showNotification('Errore di rete durante l\'aggiunta della categoria', 'error');
        };
        
        xhr.send(formData);
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
        
        const formData = new FormData(form);
        const token = localStorage.getItem('auth_token');
        
        // Mostra spinner di caricamento
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.textContent;
        submitBtn.innerHTML = '<div class="spinner" style="width:20px;height:20px;margin:0 auto;"></div>';
        submitBtn.disabled = true;
        
        // Usa XMLHttpRequest invece di fetch
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '/menu_digitale/api/add_article.php');
        xhr.setRequestHeader('Authorization', 'Bearer ' + token);
        
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                // Ripristina il testo del pulsante
                submitBtn.innerHTML = originalBtnText;
                submitBtn.disabled = false;
                
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            showNotification('Articolo aggiunto con successo!', 'success');
                            form.reset();
                            document.getElementById('articleImagePreview').style.display = 'none';
                            
                            // Ricarica gli articoli esistenti con un breve ritardo
                            setTimeout(() => {
                                if (typeof loadExistingArticles === 'function') {
                                    loadExistingArticles();
                                }
                                
                                // Ricarica anche le categorie per aggiornare il conteggio degli articoli
                                if (typeof loadExistingCategories === 'function') {
                                    loadExistingCategories();
                                }
                            }, 500);
                        } else {
                            showNotification(response.message || 'Errore durante l\'aggiunta dell\'articolo', 'error');
                        }
                    } catch (e) {
                        console.error('Errore nel parsing JSON:', e);
                        console.error('Risposta ricevuta:', xhr.responseText);
                        showNotification('Errore nel parsing della risposta dal server', 'error');
                    }
                } else {
                    showNotification('Errore del server: ' + xhr.status, 'error');
                }
            }
        };
        
        xhr.onerror = function() {
            submitBtn.innerHTML = originalBtnText;
            submitBtn.disabled = false;
            showNotification('Errore di rete durante l\'aggiunta dell\'articolo', 'error');
        };
        
        xhr.send(formData);
    });
}

function loadCategoriesForSelect(preSelectCategoryId = null) {
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
        
        // Preseleziona la categoria se specificata
        if (preSelectCategoryId && categorySelect) {
            categorySelect.value = preSelectCategoryId;
        }
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