class MenuAPI {
    constructor() {
        this.baseUrl = '/menu_digitale/api/menu.php';
        this.timeout = 5000; // 5 secondi timeout
    }

    async fetchWithTimeout(url) {
        const controller = new AbortController();
        const id = setTimeout(() => controller.abort(), this.timeout);
        try {
            const response = await fetch(url, { signal: controller.signal });
            clearTimeout(id);
            return response;
        } catch (error) {
            clearTimeout(id);
            throw error;
        }
    }

    async fetchCategories() {
        try {
            const response = await this.fetchWithTimeout(this.baseUrl);
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            return await response.json();
        } catch (error) {
            console.error('API Error:', error);
            throw new Error('Errore nel caricamento delle categorie');
        }
    }

    async fetchMenuItems(category) {
        try {
            const url = `${this.baseUrl}?category=${encodeURIComponent(category)}`;
            const response = await fetch(url);
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            return await response.json();
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }
} 