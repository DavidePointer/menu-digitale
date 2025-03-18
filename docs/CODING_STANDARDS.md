# Convenzioni di Codice - Menu Digitale

## Panoramica
Questo documento definisce le convenzioni di codice da seguire nello sviluppo del Menu Digitale. L'aderenza a queste convenzioni è fondamentale per mantenere il codice consistente, leggibile e facile da mantenere, soprattutto in vista della collaborazione futura.

## Generali

### Indentazione e Formattazione
- Utilizzare **spazi** invece di tab
- 4 spazi per ogni livello di indentazione
- Lunghezza massima linea: 100 caratteri
- Codificare i file in UTF-8
- Terminare ogni file con una nuova linea

### Commenti
- Utilizzare commenti per spiegare il "perché", non il "cosa" (il codice stesso deve essere autoesplicativo)
- Tutti i metodi pubblici devono avere un commento che ne descrive lo scopo
- Usare JSDoc per JavaScript, PHPDoc per PHP

### Gestione Errori
- Utilizzare try/catch per gestire eccezioni
- Registrare gli errori in modo appropriato
- Presentare messaggi di errore user-friendly all'utente finale

## HTML

### Struttura
- Utilizzare HTML5 semantico
- Includere attributi `alt` per tutte le immagini
- Utilizzo appropriato di titoli (`h1`, `h2`, etc.) in base alla gerarchia
- Mantenere la struttura nidificata leggibile

```html
<!-- Buono -->
<section class="menu-section">
    <h2>Titolo Sezione</h2>
    <div class="menu-item">
        <h3>Nome Piatto</h3>
        <p class="description">Descrizione del piatto</p>
    </div>
</section>

<!-- Da evitare -->
<div>
    <div>Titolo Sezione</div>
    <div>
        <div>Nome Piatto</div>
        <div>Descrizione del piatto</div>
    </div>
</div>
```

### Attributi
- Utilizzare doppi apici `"` per i valori degli attributi
- Per attributi booleani, utilizzare forma contratta (es. `required` anziché `required="required"`)

## CSS

### Nomenclatura
- Utilizzare kebab-case per i nomi delle classi (es. `menu-item`, `search-bar`)
- Preferire nomi di classi che descrivono il contenuto piuttosto che l'aspetto
- Utilizzare la metodologia BEM (Block, Element, Modifier) dove appropriato

```css
/* Approccio BEM */
.menu-item {}                  /* Block */
.menu-item__title {}           /* Element */
.menu-item--featured {}        /* Modifier */
```

### Organizzazione
- Raggruppare le proprietà CSS logicamente (box model, tipografia, colori, etc.)
- Evitare selettori troppo specifici
- Preferire classi a selettori ID
- Utilizzare variabili CSS per colori, dimensioni e altri valori riutilizzabili

```css
:root {
    --primary-color: #3498db;
    --secondary-color: #2ecc71;
    --font-main: 'Rubik', sans-serif;
    --spacing-unit: 8px;
}

.menu-item {
    /* Box model */
    margin: calc(var(--spacing-unit) * 2);
    padding: var(--spacing-unit);
    
    /* Tipografia */
    font-family: var(--font-main);
    font-size: 1.1rem;
    
    /* Colori e visualizzazione */
    color: var(--primary-color);
    background-color: white;
}
```

### Media Queries
- Utilizzare approccio mobile-first
- Punti di interruzione standard:
  - `576px` per dispositivi mobili in landscape
  - `768px` per tablet
  - `992px` per desktop
  - `1200px` per schermi grandi

## JavaScript

### Sintassi
- Utilizzare ECMAScript 6+ 
- Utilizzare `const` per valori che non cambiano, `let` per variabili
- Evitare l'uso di `var`
- Utilizzare arrow functions per preservare il contesto `this`
- Preferire metodi array moderni (map, filter, reduce) invece di cicli for

```javascript
// Buono
const items = ['a', 'b', 'c'];
const transformed = items.map(item => `Item: ${item}`);

// Da evitare
var items = ['a', 'b', 'c'];
var transformed = [];
for (var i = 0; i < items.length; i++) {
    transformed.push('Item: ' + items[i]);
}
```

### Organizzazione del Codice
- Utilizzare paradigma orientato agli oggetti (classi ES6)
- Separare la logica di presentazione (UI) dalla logica di business
- Modularizzare il codice in file separati con responsabilità singole
- Evitare funzioni troppo lunghe (max 30-50 righe)

```javascript
// Esempio di classe ben strutturata
class MenuAPI {
    constructor(baseUrl) {
        this.baseUrl = baseUrl;
    }
    
    async fetchCategories() {
        // Implementazione
    }
    
    async fetchMenuItems(categoryId) {
        // Implementazione
    }
}
```

### Denominazione
- camelCase per variabili, funzioni e metodi
- PascalCase per classi
- UPPER_SNAKE_CASE per costanti

### Gestione Asincrona
- Utilizzare Promises e async/await
- Evitare callback nidificate
- Gestire sempre sia successo che errori

```javascript
// Approccio consigliato
async function loadData() {
    try {
        const result = await api.fetchData();
        return processResult(result);
    } catch (error) {
        handleError(error);
    }
}
```

## PHP

### Denominazione
- camelCase per variabili e funzioni
- PascalCase per classi
- snake_case per nomi di file

### Struttura
- Utilizzare parentesi graffe su nuove righe per blocchi
- Includere sempre parentesi graffe per costrutti di controllo
- Utilizzare type hints quando possibile

```php
function getData($id) 
{
    if ($id <= 0) {
        return null;
    }
    
    $result = fetchFromDatabase($id);
    return $result;
}
```

### Database
- Utilizzare sempre prepared statements
- Non concatenare stringhe SQL
- Utilizzare PDO invece di mysqli

```php
// Approccio sicuro con prepared statements
$stmt = $pdo->prepare("SELECT * FROM articles WHERE category_id = ?");
$stmt->execute([$categoryId]);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
```

### Sicurezza
- Validare e sanitizzare tutti gli input utente
- Utilizzare htmlspecialchars per l'output
- Impostare header appropriate per prevenire attacchi XSS

## API

### Struttura
- Utilizzare RESTful design
- Utilizzare corretti codici di stato HTTP
- Ritornare JSON ben formattato

### Endpoint
- Utilizzare nomi di risorse, non azioni (es. `/categories` invece di `/getCategories`)
- Nomi risorse al plurale (`/articles` invece di `/article`)
- Utilizzare versioning quando necessario (`/api/v1/articles`)

## Controllo Versione (Git)

### Commit
- Scrivere messaggi di commit descrittivi e concisi
- Utilizzare il tempo presente ("Add feature" non "Added feature")
- Iniziare con un verbo ("Fix bug", "Update documentation")

### Branching
- Utilizzare feature branches per nuove funzionalità
- Utilizzare il pattern GitFlow
  - `main` per produzione
  - `develop` per sviluppo
  - `feature/nome-feature` per nuove funzionalità
  - `bugfix/nome-bug` per correzioni
  - `release/x.y.z` per release

## Test

### Frontend
- Testare su multipli browser e dispositivi
- Verificare accessibilità
- Testare funzionalità di ricerca e navigazione

### Backend
- Validare API con strumenti come Postman
- Verificare gestione errori e casi limite

## Documentazione

### Codice
- Ogni funzione/metodo pubblico deve essere documentato
- Documentare parametri e valori di ritorno
- Spiegare comportamenti non ovvi o edge cases

### API
- Documentare endpoints, parametri e risposte
- Fornire esempi di utilizzo
- Mantenere la documentazione aggiornata con il codice

## Evoluzione degli Standard
Queste convenzioni di codice possono evolversi nel tempo. Le modifiche significative devono essere discusse e approvate dal team di sviluppo.

## Strumenti Consigliati
- ESLint/PHP_CodeSniffer per verifica stile codice
- Prettier per formattazione automatica
- EditorConfig per consistenza tra editor diversi 