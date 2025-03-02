class MenuUI {
    constructor(api) {
        this.api = api;
        this.categories = [];
        this.allMenuItems = [];
        this.utils = MenuUtils;
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

    async showCategory(urlName, categoryName) {
        const categoriesView = this.utils.getElement('#categoriesView');
        const menuView = this.utils.getElement('#menuView');
        const categoryTitle = this.utils.getElement('#categoryTitle');

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

        await this.loadMenuItems(urlName);
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

    async loadMenuItems(category) {
        const menuItems = this.utils.getElement('#menuItems');
        menuItems.innerHTML = '<div class="loading">Caricamento menu...</div>';

        try {
            const items = await this.api.fetchMenuItems(category);
            
            menuItems.innerHTML = items
                .map((item, index) => `
                    <div class="menu-item" style="--item-index: ${index}">
                        <div>
                            <div class="item-name">${item.name}</div>
                            ${item.description ? 
                                `<div class="item-description">${item.description}</div>` : 
                                ''}
                        </div>
                        <div class="item-price">${this.utils.formatPrice(item.price)}</div>
                    </div>
                `).join('');
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
        // Prima mostra la categoria
        await this.showCategory(categoryUrl, categoryName);
        
        // Poi trova e scrolla all'articolo
        setTimeout(() => {
            const menuItems = document.querySelectorAll('.menu-item');
            for (let item of menuItems) {
                const itemNameElement = item.querySelector('.item-name');
                if (itemNameElement && itemNameElement.textContent === itemName) {
                    // Aggiunge la classe per l'evidenziazione
                    item.classList.add('highlighted-item');
                    item.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    
                    // Rimuove la classe dopo 2 secondi
                    setTimeout(() => {
                        item.classList.remove('highlighted-item');
                    }, 2000);
                    
                    break;
                }
            }
        }, 100);
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
}

// Esponi le funzioni necessarie globalmente
window.ui = null;
document.addEventListener('DOMContentLoaded', () => {
    window.ui = new MenuUI(new MenuAPI());
}); 