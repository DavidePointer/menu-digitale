# Schema Database - Menu Digitale

## Panoramica
Questo documento descrive lo schema del database utilizzato dal Menu Digitale. Il sistema utilizza MySQL come database relazionale per archiviare informazioni su categorie e articoli del menu.

## Schema ER
```
┌────────────────┐         ┌────────────────┐
│   categories   │         │    articles    │
├────────────────┤         ├────────────────┤
│ PK category_id │◄────────┤ FK category_id │
│    name        │         │ PK article_id  │
│    image_url   │         │    name        │
└────────────────┘         │    description │
                           │    price       │
                           │    image_url   │
                           └────────────────┘
```

## Tabelle

### categories
Memorizza le categorie di menu disponibili.

| Campo        | Tipo          | Descrizione                            | Vincoli            |
|--------------|---------------|----------------------------------------|--------------------|
| category_id  | INT           | Identificatore unico della categoria   | PK, AUTO_INCREMENT |
| name         | VARCHAR(100)  | Nome della categoria                   | NOT NULL           |
| image_url    | VARCHAR(255)  | Percorso dell'immagine della categoria | NOT NULL           |

**Indici**:
- PRIMARY KEY (category_id)

**Relazioni**:
- One-to-Many con la tabella `articles`

**Script SQL**:
```sql
CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    image_url VARCHAR(255) NOT NULL
);
```

### articles
Memorizza gli articoli del menu appartenenti a diverse categorie.

| Campo        | Tipo          | Descrizione                             | Vincoli                 |
|--------------|---------------|------------------------------------------|-------------------------|
| article_id   | INT           | Identificatore unico dell'articolo       | PK, AUTO_INCREMENT     |
| category_id  | INT           | Riferimento alla categoria dell'articolo | FK, NOT NULL           |
| name         | VARCHAR(100)  | Nome dell'articolo                       | NOT NULL               |
| description  | TEXT          | Descrizione dell'articolo                | NULL                   |
| price        | DECIMAL(10,2) | Prezzo dell'articolo                     | NOT NULL               |
| image_url    | VARCHAR(255)  | Percorso dell'immagine dell'articolo     | NOT NULL               |

**Indici**:
- PRIMARY KEY (article_id)
- FOREIGN KEY (category_id) REFERENCES categories(category_id)

**Relazioni**:
- Many-to-One con la tabella `categories`

**Script SQL**:
```sql
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

## Relazioni

### categories ↔ articles (One-to-Many)
Una categoria può contenere molti articoli, ma un articolo appartiene a una sola categoria.

**Vincoli**:
- Chiave esterna `category_id` in `articles` che riferisce `category_id` in `categories`
- La cancellazione di una categoria non è consentita se esistono articoli associati (integrità referenziale)

## Evoluzione Futura dello Schema

### Versione 2.0 (Pianificata)
In futuro, lo schema del database sarà esteso per supportare:

#### 1. Supporto Multi-Tenant
Per gestire menu di più clienti/ristoranti.

```sql
CREATE TABLE tenants (
    tenant_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    subdomain VARCHAR(50) NOT NULL UNIQUE,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Aggiunta colonna tenant_id alle tabelle esistenti
ALTER TABLE categories ADD COLUMN tenant_id INT NOT NULL;
ALTER TABLE articles ADD COLUMN tenant_id INT NOT NULL;

-- Aggiunta foreign keys
ALTER TABLE categories ADD FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id);
ALTER TABLE articles ADD FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id);
```

#### 2. Varianti degli Articoli
Per gestire opzioni e modifiche per gli articoli.

```sql
CREATE TABLE variants (
    variant_id INT AUTO_INCREMENT PRIMARY KEY,
    article_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    price_delta DECIMAL(10,2) DEFAULT 0.00,
    FOREIGN KEY (article_id) REFERENCES articles(article_id)
);
```

#### 3. Allergeni e Informazioni Nutrizionali
Per memorizzare informazioni dettagliate sugli allergeni e valori nutrizionali.

```sql
CREATE TABLE allergens (
    allergen_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    icon VARCHAR(100) NOT NULL
);

CREATE TABLE article_allergens (
    article_id INT NOT NULL,
    allergen_id INT NOT NULL,
    PRIMARY KEY (article_id, allergen_id),
    FOREIGN KEY (article_id) REFERENCES articles(article_id),
    FOREIGN KEY (allergen_id) REFERENCES allergens(allergen_id)
);
```

## Note di Implementazione

### Convenzioni di Naming
- Nomi tabelle: plurale, lowercase
- Nomi colonne: singolare, lowercase, underscore per separare parole
- Chiavi primarie: `nome_tabella_singolare_id`
- Chiavi esterne: `nome_tabella_riferimento_singolare_id`

### Ottimizzazioni
- Indici creati su colonne frequentemente utilizzate per ricerche
- Tipi di dati scelti per bilanciare spazio e performance
- Vincoli di integrità referenziale per garantire consistenza dei dati

### Backup
Si consiglia di configurare backup giornalieri del database per prevenire perdita di dati.

### Migrazione Dati
Script di migrazione saranno forniti per aggiornamenti futuri dello schema.

## Risoluzione Problemi

### Problemi Comuni e Soluzioni

#### Errore di Connessione
```
Errore connessione database: SQLSTATE[HY000] [1045] Access denied for user...
```

**Soluzione**: Verificare le credenziali in `config.php` e assicurarsi che l'utente abbia i permessi corretti.

#### Errore di Tabella Mancante
```
Table 'menu_db.categories' doesn't exist
```

**Soluzione**: Eseguire lo script `database.sql` per creare le tabelle necessarie. 