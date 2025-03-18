# Documentazione API - Menu Digitale

## Panoramica
Questa documentazione descrive le API disponibili nel sistema Menu Digitale. Le API sono implementate come endpoint RESTful che restituiscono dati in formato JSON.

## Base URL
```
http://{server}/menu_digitale/api/
```

## Autenticazione
Attualmente le API sono pubbliche per le operazioni di lettura. Le operazioni di scrittura/modifica saranno protette in futuro con autenticazione.

## Formati di Risposta
Tutte le risposte sono in formato JSON.

### Formato Successo
```json
[
  {
    "property1": "value1",
    "property2": "value2",
    ...
  },
  ...
]
```

### Formato Errore
```json
{
  "error": "Tipo errore",
  "message": "Messaggio dettagliato sull'errore",
  "trace": "Stack trace dell'errore (solo in ambiente di sviluppo)"
}
```

## Endpoint Disponibili

### Menu e Categorie
Endpoint principale per ottenere categorie e articoli.

#### GET /menu.php
Ottiene l'elenco di tutte le categorie disponibili.

**Parametri**
Nessuno

**Risposta**
```json
[
  {
    "name": "Nome Categoria",
    "category_id": 1,
    "image_url": "percorso/immagine.jpg",
    "url_name": "nome_categoria"
  },
  ...
]
```

#### GET /menu.php?category={url_name}
Ottiene l'elenco degli articoli per una categoria specifica.

**Parametri**
- `category` (string, obbligatorio): l'identificativo URL della categoria (url_name)

**Risposta**
```json
[
  {
    "name": "Nome Articolo",
    "description": "Descrizione dell'articolo",
    "price": "10.50",
    "image_url": "percorso/immagine.jpg",
    "category_name": "Nome Categoria",
    "category_url_name": "nome_categoria"
  },
  ...
]
```

### Categorie
Endpoint per la gestione delle categorie.

#### GET /categories.php
Ottiene l'elenco di tutte le categorie disponibili.

**Parametri**
Nessuno

**Risposta**
```json
[
  {
    "category_id": 1,
    "name": "Nome Categoria",
    "image_url": "percorso/immagine.jpg"
  },
  ...
]
```

#### POST /add_category.php
Aggiunge una nuova categoria.

**Parametri**
- `name` (string, obbligatorio): nome della categoria
- `image` (file, opzionale): immagine della categoria

**Risposta Successo**
```json
{
  "success": true,
  "message": "Categoria aggiunta con successo",
  "category_id": 1
}
```

**Risposta Errore**
```json
{
  "success": false,
  "message": "Errore durante l'aggiunta della categoria",
  "error": "Dettaglio errore"
}
```

### Articoli
Endpoint per la gestione degli articoli.

#### GET /articles.php
Ottiene l'elenco di tutti gli articoli disponibili.

**Parametri**
- `category_id` (integer, opzionale): ID categoria per filtrare gli articoli

**Risposta**
```json
[
  {
    "article_id": 1,
    "name": "Nome Articolo",
    "description": "Descrizione dell'articolo",
    "price": "10.50",
    "image_url": "percorso/immagine.jpg",
    "category_id": 1,
    "category_name": "Nome Categoria"
  },
  ...
]
```

#### POST /add_article.php
Aggiunge un nuovo articolo.

**Parametri**
- `name` (string, obbligatorio): nome dell'articolo
- `description` (string, opzionale): descrizione dell'articolo
- `price` (decimal, obbligatorio): prezzo dell'articolo
- `category_id` (integer, obbligatorio): ID della categoria
- `image` (file, opzionale): immagine dell'articolo

**Risposta Successo**
```json
{
  "success": true,
  "message": "Articolo aggiunto con successo",
  "article_id": 1
}
```

**Risposta Errore**
```json
{
  "success": false,
  "message": "Errore durante l'aggiunta dell'articolo",
  "error": "Dettaglio errore"
}
```

## Codici di Stato HTTP

- `200 OK`: Richiesta completata con successo
- `400 Bad Request`: Richiesta malformata o parametri mancanti
- `404 Not Found`: Risorsa non trovata
- `500 Internal Server Error`: Errore interno del server

## Limiti e Ottimizzazioni

- Timeout richieste: 5 secondi
- Cache: le risposte GET hanno un cache di 5 minuti (300 secondi)
- CORS: le API sono configurate per permettere richieste cross-origin

## Esempi di Utilizzo

### JavaScript Fetch
```javascript
// Ottenere tutte le categorie
fetch('/menu_digitale/api/menu.php')
  .then(response => response.json())
  .then(data => console.log(data))
  .catch(error => console.error('Error:', error));

// Ottenere articoli per categoria
fetch('/menu_digitale/api/menu.php?category=nome_categoria')
  .then(response => response.json())
  .then(data => console.log(data))
  .catch(error => console.error('Error:', error));
```

### PHP cURL
```php
// Ottenere tutte le categorie
$ch = curl_init('http://localhost/menu_digitale/api/menu.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$data = json_decode($response, true);
curl_close($ch);
print_r($data);
```

## Roadmap API Future

- Implementazione autenticazione JWT
- Endpoint per modifica e cancellazione di articoli e categorie
- Versioning delle API (v1, v2, ecc.)
- API per gestione varianti articoli
- Rate limiting per prevenire abusi
- Endpoint per statistiche e reporting 