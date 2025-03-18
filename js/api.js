class MenuAPI {
    constructor() {
        this.baseUrl = '/menu_digitale/api/menu.php';
        this.timeout = 5000; // 5 secondi timeout
        console.log('MenuAPI inizializzata con URL:', this.baseUrl);
    }

    async fetchWithTimeout(url) {
        console.log('Chiamata API a:', url);
        const controller = new AbortController();
        const id = setTimeout(() => controller.abort(), this.timeout);
        try {
            const response = await fetch(url, { signal: controller.signal });
            clearTimeout(id);
            console.log('Risposta ricevuta:', response.status, response.statusText);
            return response;
        } catch (error) {
            clearTimeout(id);
            console.error('Errore nella chiamata API:', error);
            throw error;
        }
    }

    async fetchCategories() {
        console.log('Caricamento categorie...');
        try {
            const response = await this.fetchWithTimeout(this.baseUrl);
            if (!response.ok) {
                console.error('Errore HTTP:', response.status, response.statusText);
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
            console.log('Categorie caricate:', data);
            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw new Error('Errore nel caricamento delle categorie');
        }
    }

    async fetchMenuItems(category) {
        console.log('Caricamento menu per categoria:', category);
        try {
            const url = `${this.baseUrl}?category=${encodeURIComponent(category)}`;
            console.log('URL richiesta articoli:', url);
            const response = await fetch(url);
            console.log('Risposta articoli:', response.status, response.statusText);
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            const data = await response.json();
            console.log('Articoli caricati:', data);
            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }
} 