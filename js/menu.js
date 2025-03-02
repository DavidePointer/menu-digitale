class Menu {
    constructor() {
        this.items = [];
        this.categories = [];
        this.currentCategory = 'all';
    }

    init() {
        this.loadMenuItems();
        this.setupEventListeners();
        this.renderMenu();
    }

    async loadMenuItems() {
        try {
            const menuData = await this.fetchMenuData();
            this.items = menuData.items;
            this.categories = this.extractCategories(menuData.items);
            this.renderCategories();
        } catch (error) {
            console.error('Errore nel caricamento del menu:', error);
        }
    }

    extractCategories(items) {
        return [...new Set(items.map(item => item.category))];
    }

    setupEventListeners() {
        document.addEventListener('DOMContentLoaded', () => {
            this.renderMenu();
            this.setupCategoryFilters();
        });
    }

    setupCategoryFilters() {
        const categoriesContainer = document.querySelector('.categories');
        if (categoriesContainer) {
            categoriesContainer.addEventListener('click', (e) => {
                if (e.target.classList.contains('category-btn')) {
                    this.filterByCategory(e.target.dataset.category);
                }
            });
        }
    }

    filterByCategory(category) {
        this.currentCategory = category;
        this.renderMenu();
    }

    renderMenu() {
        const menuContainer = document.querySelector('.menu-items');
        if (!menuContainer) return;

        const filteredItems = this.currentCategory === 'all' 
            ? this.items 
            : this.items.filter(item => item.category === this.currentCategory);

        menuContainer.innerHTML = filteredItems.map(item => this.createMenuItemHTML(item)).join('');
    }

    createMenuItemHTML(item) {
        return `
            <div class="menu-item" data-id="${item.id}">
                <img src="${item.image}" alt="${item.name}">
                <h3>${item.name}</h3>
                <p>${item.description}</p>
                <div class="price">€${item.price.toFixed(2)}</div>
                <button onclick="cart.addItem(${item.id})">Aggiungi al carrello</button>
            </div>
        `;
    }

    renderCategories() {
        const categoriesContainer = document.querySelector('.categories');
        if (!categoriesContainer) return;

        const categoriesHTML = this.categories.map(category => `
            <button class="category-btn" data-category="${category}">
                ${category}
            </button>
        `).join('');

        categoriesContainer.innerHTML = `
            <button class="category-btn active" data-category="all">Tutti</button>
            ${categoriesHTML}
        `;
    }
}

class MenuAPI {
    constructor() {
        this.baseUrl = '/menu_digitale/api';
    }

    async getCategories() {
        try {
            const response = await fetch(`${this.baseUrl}/menu.php`);
            if (!response.ok) throw new Error('Network response was not ok');
            return await response.json();
        } catch (error) {
            console.error('Error fetching categories:', error);
            throw error;
        }
    }

    async getMenuItems(category) {
        try {
            const response = await fetch(`${this.baseUrl}/menu.php?category=${encodeURIComponent(category)}`);
            if (!response.ok) throw new Error('Network response was not ok');
            return await response.json();
        } catch (error) {
            console.error('Error fetching menu items:', error);
            throw error;
        }
    }
}

const menu = new Menu();
menu.init(); 