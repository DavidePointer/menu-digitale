class MenuUI {
    constructor(api) {
        this.api = api;
        this.categories = [];
        this.allMenuItems = [];
        this.utils = MenuUtils;
        this.currentCategory = '';
        this.currentCategoryName = '';
        this.initializeEventListeners();
    }

    initializeEventListeners() {
        document.addEventListener('DOMContentLoaded', () => this.initialize());
    }

    async initialize() {
        try {
            await this.loadCategories();
            await this.loadAllMenuItems();
            this.initializeSearch();
            await this.loadSettings();
        } catch (error) {
            console.error('Initialization error:', error);
        }
    }

    async loadCategories() {
        const container = this.utils.getElement('#categoriesView');
        try {
            const categories = await this.api.fetchCategories();
            this.categories = categories;
            
            const categoriesHTML = categories
                .map((category, index) => this.createCategoryCard(category, index))
                .join('');

            container.innerHTML = categoriesHTML;
        } catch (error) {
            container.innerHTML = this.utils.showError(error.message);
        }
    }

    createCategoryCard(category, index) {
        return `
            <div class="category-card" 
                 style="--card-index: ${index}"
                 onclick="ui.showCategory('${category.url_name}', '${category.name}')"
                 data-category-id="${category.category_id}">
                <img src="/menu_digitale/${category.image_url}" 
                     alt="${category.name}" 
                     class="category-image"
                     onerror="this.src='/menu_digitale/images/placeholder.jpg'">
                <div class="category-content">
                    <h2>${category.name}</h2>
                </div>
            </div>
        `;
    }

    async showCategory(urlName, categoryName, isFromSearch = false) {
        const categoriesView = this.utils.getElement('#categoriesView');
        const menuView = this.utils.getElement('#menuView');
        const categoryTitle = this.utils.getElement('#categoryTitle');

        // Salva la categoria corrente
        this.currentCategory = urlName;
        this.currentCategoryName = categoryName;

        // Aggiungi la navigazione rapida se non esiste
        if (!document.querySelector('.quick-categories')) {
            const quickNav = document.createElement('div');
            quickNav.className = 'quick-categories';
            
            // Aggiungi l'event listener per lo scroll
            window.addEventListener('scroll', () => {
                const scrolled = window.scrollY > 10;
                quickNav.classList.toggle('scrolled', scrolled);
            });

            this.categories.forEach(category => {
                const input = document.createElement('input');
                input.type = 'radio';
                input.id = `cat-${category.url_name}`;
                input.name = 'category';
                input.className = 'category-radio';
                input.checked = category.url_name === urlName;
                
                const label = document.createElement('label');
                label.htmlFor = `cat-${category.url_name}`;
                label.className = 'category-label';
                label.textContent = category.name;
                
                input.addEventListener('change', () => {
                    if (input.checked) {
                        this.showCategory(category.url_name, category.name);
                    }
                });
                
                quickNav.appendChild(input);
                quickNav.appendChild(label);
            });
            
            menuView.insertBefore(quickNav, menuView.firstChild);
        } else {
            // Aggiorna solo lo stato checked del radio button
            document.querySelectorAll('.category-radio').forEach(radio => {
                radio.checked = radio.id === `cat-${urlName}`;
            });
        }

        categoriesView.style.display = 'none';
        menuView.style.display = 'block';
        categoryTitle.textContent = categoryName;

        await this.loadMenuItems(urlName, isFromSearch);
    }

    showCategories() {
        const categoriesView = this.utils.getElement('#categoriesView');
        const menuView = this.utils.getElement('#menuView');
        const quickNav = document.querySelector('.quick-categories');
        
        if (quickNav) {
            quickNav.remove(); // Rimuovi la navigazione rapida quando torni alla vista categorie
        }
        
        categoriesView.style.display = 'grid';
        menuView.style.display = 'none';
    }

    async loadMenuItems(category, isFromSearch = false) {
        const menuItems = this.utils.getElement('#menuItems');
        menuItems.innerHTML = '<div class="loading">Caricamento menu...</div>';

        try {
            const items = await this.api.fetchMenuItems(category);
            
            // Assicurati che ci sia sempre un'immagine per ogni articolo
            const processedItems = items.map(item => {
                // Se l'URL dell'immagine è vuoto o undefined, usa un placeholder
                if (!item.image_url) {
                    item.image_url = '/menu_digitale/images/placeholder.jpg';
                    console.log('Immagine mancante per:', item.name);
                } else {
                    // In caso di errore nell'immagine, verrà gestito dall'attributo onerror nell'HTML
                    console.log('Immagine per:', item.name, '=', item.image_url);
                }
                return item;
            });
            
            // Verifica che la funzione encodeForHTML esista
            const encodeForHTML = typeof this.utils.encodeForHTML === 'function' 
                ? this.utils.encodeForHTML 
                : (str => str ? String(str) : '');
            
            menuItems.innerHTML = processedItems
                .map((item, index) => {
                    // Converti caratteri problematici in una forma sicura per gli attributi HTML
                    const safeItemName = encodeForHTML(item.name);
                    const safeItemDesc = item.description ? encodeForHTML(item.description) : '';
                    const safeItemImageUrl = encodeForHTML(item.image_url);
                    
                    // Aggiunta classe animation solo se non stiamo caricando da una ricerca
                    const animationClass = isFromSearch ? '' : 'with-animation';
                    
                    return `
                    <div class="menu-item ${animationClass}" style="--item-index: ${index}" 
                         data-name="${safeItemName}" 
                         data-description="${safeItemDesc}" 
                         data-price="${item.price}" 
                         data-image="${safeItemImageUrl}"
                         onclick="ui.showItemDetailFromAttributes(this)">
                        <div class="item-content">
                            <div class="item-name">${item.name}</div>
                            ${item.description ? 
                                `<div class="item-description">${item.description}</div>` : 
                                ''}
                            <div class="item-price">${this.utils.formatPrice(item.price)}</div>
                        </div>
                        <div class="item-image-container">
                            <img src="${item.image_url}" 
                                 alt="${safeItemName}" 
                                 class="item-image" 
                                 onerror="this.onerror=null; this.src='/menu_digitale/images/placeholder.jpg'; console.error('Errore caricamento immagine:', this.src);">
                        </div>
                    </div>
                    `;
                }).join('');
        } catch (error) {
            menuItems.innerHTML = this.utils.showError(error.message);
        }
    }

    async loadAllMenuItems() {
        try {
            // Carica gli articoli di tutte le categorie
            const promises = this.categories.map(category => 
                this.api.fetchMenuItems(category.url_name)
                    .then(items => items.map(item => ({
                        ...item,
                        category_name: category.name,
                        category_url: category.url_name
                    })))
            );
            const results = await Promise.all(promises);
            this.allMenuItems = results.flat();
        } catch (error) {
            console.error('Error loading all menu items:', error);
        }
    }

    initializeSearch() {
        const searchInput = this.utils.getElement('#searchInput');
        const searchResults = this.utils.getElement('#searchResults');
        const searchClear = this.utils.getElement('#searchClear');
        
        // Gestione visibilità del pulsante clear
        const toggleClearButton = () => {
            searchClear.classList.toggle('visible', searchInput.value.length > 0);
        };

        // Click sul pulsante clear
        searchClear.addEventListener('click', () => {
            searchInput.value = '';
            searchResults.classList.remove('active');
            searchResults.innerHTML = '';
            toggleClearButton();
            searchInput.focus();
        });

        // Mostra/nascondi il pulsante clear durante l'input
        searchInput.addEventListener('input', (e) => {
            toggleClearButton();
            this._searchTimeout = debouncedSearch(e.target.value.trim());
        });

        const showResults = (results) => {
            if (results.length === 0) {
                searchResults.classList.remove('active');
                return;
            }

            searchResults.innerHTML = results
                .map(item => `
                    <div class="search-result-item" 
                         onclick="(function(e) {
                             e.stopPropagation();
                             document.getElementById('searchInput').value = '';
                             document.getElementById('searchResults').classList.remove('active');
                             ui.showCategoryAndScrollToItem('${item.category_url}', '${item.category_name}', '${item.name}');
                         })(event)">
                        <div class="search-result-category">${item.category_name}</div>
                        <div class="item-name">${item.name}</div>
                        <div class="item-price">${this.utils.formatPrice(item.price)}</div>
                    </div>
                `).join('');
            searchResults.classList.add('active');
        };

        // Usa debounce per migliorare le performance
        const debouncedSearch = this.utils.debounce((searchTerm) => {
            if (!searchTerm) {
                searchResults.classList.remove('active');
                return;
            }

            const results = this.allMenuItems.filter(item => {
                const searchLower = searchTerm.toLowerCase();
                return item.name.toLowerCase().includes(searchLower) ||
                       (item.description && item.description.toLowerCase().includes(searchLower));
            }).slice(0, 5);

            showResults(results);
        }, 300);

        // Chiudi i risultati quando si clicca fuori
        document.addEventListener('click', (e) => {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                this.clearSearch(searchInput, searchResults);
            }
        });
    }

    async showCategoryAndScrollToItem(categoryUrl, categoryName, itemName) {
        // Prima mostra la categoria, indicando che viene dalla ricerca
        await this.showCategory(categoryUrl, categoryName, true);
        
        // Nessun ritardo per la ricerca, cerca subito l'elemento
        const menuItems = document.querySelectorAll('.menu-item');
        let found = false;
        
        for (let item of menuItems) {
            const itemNameElement = item.querySelector('.item-name');
            if (itemNameElement && itemNameElement.textContent === itemName) {
                // Aggiunge la classe per l'evidenziazione
                item.classList.add('highlighted-item');
                
                // Scroll all'elemento immediatamente
                item.scrollIntoView({ behavior: 'auto', block: 'center' });
                
                // Rimuove la classe dopo 2 secondi
                setTimeout(() => {
                    item.classList.remove('highlighted-item');
                }, 2000);
                
                // Apri direttamente la visualizzazione dettagliata
                if (item.getAttribute('data-name')) {
                    this.showItemDetailFromAttributes(item);
                }
                
                found = true;
                break;
            }
        }
        
        // Se l'articolo non è stato trovato subito, riprova dopo un breve ritardo
        if (!found) {
            setTimeout(() => {
                const menuItems = document.querySelectorAll('.menu-item');
                for (let item of menuItems) {
                    const itemNameElement = item.querySelector('.item-name');
                    if (itemNameElement && itemNameElement.textContent === itemName) {
                        item.classList.add('highlighted-item');
                        item.scrollIntoView({ behavior: 'auto', block: 'center' });
                        
                        setTimeout(() => {
                            item.classList.remove('highlighted-item');
                        }, 2000);
                        break;
                    }
                }
            }, 50); // Ridotto ulteriormente a 50ms
        }
    }

    // Nuovo metodo per gestire sia la pulizia che la navigazione
    async clearSearchAndShowItem(categoryUrl, categoryName, itemName) {
        const searchInput = this.utils.getElement('#searchInput');
        const searchResults = this.utils.getElement('#searchResults');
        
        // Pulisci l'input e i risultati
        searchInput.value = '';
        searchResults.classList.remove('active');
        searchResults.innerHTML = '';
        
        // Cancella il timeout del debounce
        if (this._searchTimeout) {
            clearTimeout(this._searchTimeout);
        }
        
        // Procedi con la navigazione
        await this.showCategoryAndScrollToItem(categoryUrl, categoryName, itemName);
    }

    showItemDetailFromAttributes(element) {
        const name = element.getAttribute('data-name');
        const description = element.getAttribute('data-description');
        const price = parseFloat(element.getAttribute('data-price'));
        const imageUrl = element.getAttribute('data-image');
        
        this.showItemDetail(name, description, price, imageUrl);
    }

    showItemDetail(name, description, price, imageUrl) {
        // Crea un overlay modale
        const modal = document.createElement('div');
        modal.className = 'item-detail-modal';
        
        // Crea contenuto modale
        modal.innerHTML = `
            <div class="item-detail-content">
                <button class="close-modal" onclick="this.parentNode.parentNode.remove()">&times;</button>
                <div class="item-detail-image-container">
                    <img src="${imageUrl}" 
                         alt="${name}" 
                         class="item-detail-image" 
                         onerror="this.onerror=null; this.src='/menu_digitale/images/placeholder.jpg'; console.error('Errore caricamento immagine:', this.src);">
                </div>
                <div class="item-detail-info">
                    <h2>${name}</h2>
                    ${description ? `<p class="item-detail-description">${description}</p>` : ''}
                    <p class="item-detail-price">${this.utils.formatPrice(price)}</p>
                </div>
            </div>
        `;
        
        // Aggiungi al DOM
        document.body.appendChild(modal);
        
        // Aggiungi event listener per chiudere il modale quando si clicca fuori
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.remove();
            }
        });
    }

    // Carica le impostazioni dal server
    async loadSettings() {
        console.log("Caricamento impostazioni...");
        
        try {
            const response = await fetch('/menu_digitale/api/settings.php');
            const data = await response.json();
            if (data.success) {
                await this.applySettings(data.data);
            } else {
                console.error('Errore nel caricamento delle impostazioni:', data.message);
            }
        } catch (error) {
            console.error('Errore nel caricamento delle impostazioni:', error);
        }
    }

    // Applica le impostazioni alla pagina
    async applySettings(settings) {
        if (settings.general) {
            // Applica il nome del sito
            const siteNameElement = document.querySelector('.site-name');
            if (siteNameElement && settings.general.siteName) {
                siteNameElement.textContent = settings.general.siteName;
                document.title = settings.general.siteName;
            }

            // Applica il tagline
            const taglineElement = document.querySelector('.site-tagline');
            if (taglineElement && settings.general.siteTagline) {
                taglineElement.textContent = settings.general.siteTagline;
            }

            // Applica il logo
            const logoElement = document.querySelector('.site-logo');
            if (logoElement && settings.general.logoUrl) {
                logoElement.src = settings.general.logoUrl;
                logoElement.style.display = 'block';
            }

            // Applica i colori personalizzati
            if (settings.general.primaryColor) {
                document.documentElement.style.setProperty('--primary-color', settings.general.primaryColor);
            }
            if (settings.general.accentColor) {
                document.documentElement.style.setProperty('--accent-color', settings.general.accentColor);
            }
        }

        if (settings.contact) {
            // Applica le informazioni di contatto
            const contactElements = {
                address: document.querySelector('.contact-address'),
                phone: document.querySelector('.contact-phone'),
                email: document.querySelector('.contact-email'),
                weekdayHours: document.querySelector('.weekday-hours'),
                weekendHours: document.querySelector('.weekend-hours')
            };

            for (const [key, element] of Object.entries(contactElements)) {
                if (element && settings.contact[key]) {
                    element.textContent = settings.contact[key];
                    element.style.display = 'block';
                }
            }
        }
    }
}

// Esponi le funzioni necessarie globalmente
window.ui = null;
document.addEventListener('DOMContentLoaded', () => {
    window.ui = new MenuUI(new MenuAPI());
}); 