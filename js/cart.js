class Cart {
    constructor() {
        this.items = [];
        this.init();
    }

    init() {
        this.loadFromLocalStorage();
        this.render();
    }

    loadFromLocalStorage() {
        const savedCart = localStorage.getItem('cart');
        if (savedCart) {
            this.items = JSON.parse(savedCart);
        }
    }

    saveToLocalStorage() {
        localStorage.setItem('cart', JSON.stringify(this.items));
    }

    addItem(itemId) {
        const menuItem = menu.items.find(item => item.id === itemId);
        if (!menuItem) return;

        const existingItem = this.items.find(item => item.id === itemId);
        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            this.items.push({
                id: itemId,
                name: menuItem.name,
                price: menuItem.price,
                quantity: 1
            });
        }

        this.saveToLocalStorage();
        this.render();
    }

    removeItem(itemId) {
        const index = this.items.findIndex(item => item.id === itemId);
        if (index !== -1) {
            this.items.splice(index, 1);
            this.saveToLocalStorage();
            this.render();
        }
    }

    updateQuantity(itemId, quantity) {
        const item = this.items.find(item => item.id === itemId);
        if (item) {
            item.quantity = Math.max(0, quantity);
            if (item.quantity === 0) {
                this.removeItem(itemId);
            } else {
                this.saveToLocalStorage();
                this.render();
            }
        }
    }

    getTotal() {
        return this.items.reduce((total, item) => total + (item.price * item.quantity), 0);
    }

    render() {
        const cartContainer = document.querySelector('.cart');
        if (!cartContainer) return;

        if (this.items.length === 0) {
            cartContainer.innerHTML = '<p>Il carrello è vuoto</p>';
            return;
        }

        cartContainer.innerHTML = `
            <h3>Carrello</h3>
            <div class="cart-items">
                ${this.items.map(item => `
                    <div class="cart-item" data-id="${item.id}">
                        <span>${item.name}</span>
                        <div class="quantity-controls">
                            <button onclick="cart.updateQuantity(${item.id}, ${item.quantity - 1})">-</button>
                            <span>${item.quantity}</span>
                            <button onclick="cart.updateQuantity(${item.id}, ${item.quantity + 1})">+</button>
                        </div>
                        <span>€${(item.price * item.quantity).toFixed(2)}</span>
                        <button onclick="cart.removeItem(${item.id})">×</button>
                    </div>
                `).join('')}
            </div>
            <div class="cart-total">
                <strong>Totale: €${this.getTotal().toFixed(2)}</strong>
            </div>
            <button onclick="cart.checkout()" class="checkout-btn">Procedi all'ordine</button>
        `;
    }

    checkout() {
        // Implementa la logica per il checkout
        alert('Funzionalità di checkout da implementare');
    }
}

const cart = new Cart(); 