const MenuUtils = {
    getElement(selector) {
        const element = document.querySelector(selector);
        if (!element) throw new Error(`Element not found: ${selector}`);
        return element;
    },

    showError(message) {
        console.error(message);
        return `<div class="error-message">${message}</div>`;
    },

    formatPrice(price) {
        return `€${parseFloat(price).toFixed(2)}`;
    },

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },

    sanitizeHTML(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
}; 