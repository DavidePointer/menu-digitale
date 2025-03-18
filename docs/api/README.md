# Documentazione API - Menu Digitale

## Panoramica
Questa documentazione descrive le API disponibili nel sistema Menu Digitale. Le API sono implementate come endpoint RESTful che restituiscono dati in formato JSON.

## Base URL
```
http://{server}/menu_digitale/api/
```

## Autenticazione
Le API di lettura sono pubbliche mentre le operazioni di scrittura/modifica/cancellazione sono protette con autenticazione. Per utilizzare le API protette, è necessario:

1. Effettuare l'accesso tramite il pannello di amministrazione
2. Ottenere un token di sessione valido
3. Includere il token nelle richieste HTTP attraverso i cookie (gestito automaticamente dal browser)

## Formati di Risposta
Tutte le risposte sono in formato JSON.

### Formato Successo
```json
{
  "success": true,
  "message": "Operazione completata con successo",
  "data": [
    {
      "property1": "value1",
      "property2": "value2",
      ...
    },
    ...
  ]
}
```

### Formato Errore
```json
{
  "success": false,
  "message": "Messaggio dettagliato sull'errore",
  "error": "Tipo errore",
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

#### GET /get_category.php
Ottiene i dettagli di una categoria specifica.

**Parametri**
- `category_id` (integer, obbligatorio): ID della categoria

**Risposta Successo**
```json
{
  "success": true,
  "data": {
    "category_id": 1,
    "name": "Nome Categoria",
    "image_url": "percorso/immagine.jpg",
    "article_count": 5
  }
}
```

**Risposta Errore**
```json
{
  "success": false,
  "message": "Categoria non trovata",
  "error": "Categoria con ID 1 non trovata"
}
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

#### POST /update_category.php
Aggiorna una categoria esistente.

**Parametri**
- `category_id` (integer, obbligatorio): ID della categoria da aggiornare
- `name` (string, obbligatorio): nuovo nome della categoria
- `image` (file, opzionale): nuova immagine della categoria

**Risposta Successo**
```json
{
  "success": true,
  "message": "Categoria aggiornata con successo",
  "category_id": 1
}
```

**Risposta Errore**
```json
{
  "success": false,
  "message": "Errore durante l'aggiornamento della categoria",
  "error": "Dettaglio errore"
}
```

#### POST /delete_category.php
Elimina una categoria esistente.

**Parametri**
- `category_id` (integer, obbligatorio): ID della categoria da eliminare

**Risposta Successo**
```json
{
  "success": true,
  "message": "Categoria eliminata con successo"
}
```

**Risposta Errore**
```json
{
  "success": false,
  "message": "Impossibile eliminare la categoria",
  "error": "La categoria contiene articoli"
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

#### GET /get_article.php
Ottiene i dettagli di un articolo specifico.

**Parametri**
- `article_id` (integer, obbligatorio): ID dell'articolo

**Risposta Successo**
```json
{
  "success": true,
  "data": {
    "article_id": 1,
    "name": "Nome Articolo",
    "description": "Descrizione dell'articolo",
    "price": "10.50",
    "image_url": "percorso/immagine.jpg",
    "category_id": 1,
    "category_name": "Nome Categoria"
  }
}
```

**Risposta Errore**
```json
{
  "success": false,
  "message": "Articolo non trovato",
  "error": "Articolo con ID 1 non trovato"
}
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

#### POST /update_article.php
Aggiorna un articolo esistente.

**Parametri**
- `article_id` (integer, obbligatorio): ID dell'articolo da aggiornare
- `name` (string, obbligatorio): nuovo nome dell'articolo
- `description` (string, opzionale): nuova descrizione dell'articolo
- `price` (decimal, obbligatorio): nuovo prezzo dell'articolo
- `category_id` (integer, obbligatorio): ID della categoria 
- `image` (file, opzionale): nuova immagine dell'articolo

**Risposta Successo**
```json
{
  "success": true,
  "message": "Articolo aggiornato con successo",
  "article_id": 1
}
```

**Risposta Errore**
```json
{
  "success": false,
  "message": "Errore durante l'aggiornamento dell'articolo",
  "error": "Dettaglio errore"
}
```

#### POST /delete_article.php
Elimina un articolo esistente.

**Parametri**
- `article_id` (integer, obbligatorio): ID dell'articolo da eliminare

**Risposta Successo**
```json
{
  "success": true,
  "message": "Articolo eliminato con successo"
}
```

**Risposta Errore**
```json
{
  "success": false,
  "message": "Impossibile eliminare l'articolo",
  "error": "Dettaglio errore"
}
```

## Codici di Stato HTTP

- `200 OK`: Richiesta completata con successo
- `400 Bad Request`: Richiesta malformata o parametri mancanti
- `401 Unauthorized`: Autenticazione richiesta
- `403 Forbidden`: Autenticazione insufficiente per accedere alla risorsa
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

// Aggiornare un articolo (richiede autenticazione)
const formData = new FormData();
formData.append('article_id', '1');
formData.append('name', 'Nuovo nome');
formData.append('price', '12.50');
formData.append('category_id', '1');
formData.append('description', 'Nuova descrizione');

fetch('/menu_digitale/api/update_article.php', {
  method: 'POST',
  body: formData,
  credentials: 'include' // Importante per inviare i cookie di autenticazione
})
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
- Versioning delle API (v1, v2, ecc.)
- API per gestione varianti articoli
- Rate limiting per prevenire abusi
- Endpoint per statistiche e reporting 