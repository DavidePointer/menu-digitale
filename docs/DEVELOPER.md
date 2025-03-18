# Documentazione per Sviluppatori - Menu Digitale Pointer

## Panoramica Tecnica

Questo documento fornisce dettagli tecnici sull'architettura, i componenti e le convenzioni di codice utilizzate nel progetto Menu Digitale. È pensato per sviluppatori che devono comprendere, mantenere o estendere il codice esistente.

## Stack Tecnologico

### Frontend
- **HTML5/CSS3**: Struttura e stile dell'interfaccia
- **JavaScript (ES6+)**: Logica dell'applicazione client-side
- **Architettura OOP**: Classi con responsabilità specifiche
- **Pattern MVC**: Separazione dati, logica e visualizzazione

### Backend
- **PHP 7.4+**: API RESTful
- **MySQL**: Database relazionale
- **PDO**: Accesso al database
- **JSON**: Formato di scambio dati

## Struttura del Codice

### Organizzazione dei File

```
menu_digitale/
├── api/                            # Backend API
│   ├── menu.php                    # Gestione menu e categorie
│   ├── categories.php              # CRUD categorie
│   ├── articles.php                # CRUD articoli
│   └── add_*.php                   # Endpoint per aggiunta elementi
├── css/                            # Stili
│   ├── style.css                   # Stile principale
│   └── responsive.css              # Media queries
├── js/                             # Client JavaScript
│   ├── ui.js                       # Gestione interfaccia utente
│   ├── api.js                      # Client API
│   └── utils.js                    # Funzioni di utilità
├── images/                         # Asset grafici
├── docs/                           # Documentazione
├── config.php                      # Configurazione database
└── index.html                      # Entry point applicazione
```

## Componenti Principali

### MenuUI (js/ui.js)
Gestisce l'interfaccia utente, gli eventi e la visualizzazione dei dati.

```javascript
class MenuUI {
    // Inizializzazione UI e listener
    constructor(api) { ... }
    
    // Caricamento dati e inizializzazione componenti
    async initialize() { ... }
    
    // Caricamento e visualizzazione categorie
    async loadCategories() { ... }
    
    // Visualizzazione articoli per categoria
    async showCategory(categoryUrl, categoryName) { ... }
    
    // Sistema di ricerca
    initializeSearch() { ... }
    performSearch(query) { ... }
}
```

### MenuAPI (js/api.js)
Gestisce le comunicazioni con il backend tramite fetch API.

```javascript
class MenuAPI {
    // Inizializzazione configurazione API
    constructor() { ... }
    
    // Wrapper per fetch con timeout
    async fetchWithTimeout(url) { ... }
    
    // Recupero categorie dal server
    async fetchCategories() { ... }
    
    // Recupero articoli per categoria
    async fetchMenuItems(category) { ... }
}
```

### API Backend (api/menu.php)
Endpoint principale per dati menu, categorie e articoli.

```php
// Gestione richieste GET per categorie e articoli
// Parametri: category (opzionale)
// Ritorna: JSON con dati richiesti
```

## Flusso dei Dati

1. **Inizializzazione**:
   - `index.html` carica i file JS necessari e istanzia `MenuUI` e `MenuAPI`
   - `MenuUI.initialize()` carica categorie e inizializza la ricerca

2. **Visualizzazione Categorie**:
   - `MenuUI.loadCategories()` chiama `MenuAPI.fetchCategories()`
   - `MenuAPI` effettua una richiesta a `api/menu.php`
   - `menu.php` interroga il database e restituisce JSON
   - `MenuUI` renderizza le categorie nella griglia

3. **Visualizzazione Articoli**:
   - L'utente clicca su una categoria
   - `MenuUI.showCategory()` chiama `MenuAPI.fetchMenuItems()`
   - `MenuAPI` effettua una richiesta a `api/menu.php?category=X`
   - `menu.php` interroga il database filtrando per categoria
   - `MenuUI` renderizza gli articoli nella vista menu

4. **Ricerca**:
   - L'utente digita nella barra di ricerca
   - `MenuUI.performSearch()` filtra tra tutti gli articoli
   - I risultati vengono visualizzati in tempo reale

## Database

### Schema
```sql
CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    image_url VARCHAR(255) NOT NULL
);

CREATE TABLE articles (
    article_id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    FOREIGN KEY (category_id) REFERENCES categories(category_id)
);
```

### Relazioni
- **One-to-Many**: Una categoria può contenere molti articoli

## Convenzioni di Codice

### JavaScript
- **Classi**: PascalCase (MenuUI, MenuAPI)
- **Metodi/Variabili**: camelCase (loadCategories, fetchMenuItems)
- **Costanti**: UPPER_SNAKE_CASE
- **Async/Await**: Utilizzato per codice asincrono
- **Event Delegation**: Per gestione eventi

### PHP
- **File**: snake_case.php
- **Funzioni**: camelCase()
- **Costanti**: UPPER_SNAKE_CASE
- **PDO**: Prepared statements per sicurezza

### CSS
- **Classi**: kebab-case (menu-item, search-bar)
- **BEM**: Block__Element--Modifier dove possibile
- **Variabili CSS**: Per colori e dimensioni principali
- **Media Queries**: Mobile-first approach

## Gestione Errori

### Frontend
- Try/catch per gestire errori API
- Messaggi di errore user-friendly
- Fallback per immagini mancanti

### Backend
- Log errori in `/logs/debug.log`
- Risposte HTTP appropriate (200, 404, 500)
- Validazione input e sanitizzazione

## Performance

### Ottimizzazioni
- Timeout per chiamate API
- Caricamento asincrono dei dati
- Animazioni ottimizzate per dispositivi mobile
- Cache HTTP configurata per risorse statiche

## Estensione del Codice

### Aggiungere Nuove Funzionalità
1. **Nuovi Endpoint API**:
   - Creare file in `/api/` con adeguata gestione CORS e validazione
   - Registrare nel README

2. **Nuove Funzionalità UI**:
   - Estendere la classe MenuUI con nuovi metodi
   - Documentare parametri e comportamento atteso

3. **Nuovi Stili**:
   - Seguire le convenzioni BEM
   - Utilizzare variabili CSS esistenti

## Testing

### Test Manuali
- Verificare visualizzazione su dispositivi diversi
- Testare funzionalità di ricerca
- Verificare caricamento immagini e fallback

### Plan di Test Automatizzati (Futuro)
- Jest per unit testing JS
- PHPUnit per API backend
- Cypress per E2E testing

## Roadmap Tecnica

### Miglioramenti Previsti
- Implementazione autenticazione JWT per admin
- Ottimizzazione caricamento immagini (WebP, lazy loading)
- Caching lato client per migliorare performance
- Implementazione service worker per funzionalità offline 