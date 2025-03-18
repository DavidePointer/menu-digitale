# Menu Digitale - Pointer

## Panoramica del Progetto
Sistema di visualizzazione menu digitale per ristoranti/locali, personalizzato per Pointer. L'applicazione permette di visualizzare categorie e articoli di menu in un'interfaccia responsiva e user-friendly.

## Architettura del Sistema

### Frontend
- **HTML/CSS/JavaScript**: Applicazione web responsive
- **Design moderno**: Animazioni CSS e interfaccia intuitiva
- **Ricerca in tempo reale**: Funzionalità di ricerca istantanea per gli articoli

### Backend
- **PHP**: API RESTful per la gestione dei dati
- **MySQL**: Database per categorie e articoli
- **Architettura modulare**: Separazione tra logica di business e presentazione

## Struttura del Repository

```
menu_digitale/
├── api/                  # API per l'interazione con il database
│   ├── menu.php          # Endpoint principale per menu e categorie
│   ├── categories.php    # Gestione categorie
│   └── articles.php      # Gestione articoli
├── css/                  # Stili dell'applicazione
│   ├── style.css         # Stile principale
│   └── responsive.css    # Stili per responsive design
├── js/                   # Script JavaScript
│   ├── ui.js             # Gestione interfaccia utente
│   ├── api.js            # Interazione con le API
│   └── utils.js          # Funzioni di utilità
├── images/               # Immagini e risorse
├── config.php            # Configurazione database
├── index.html            # Pagina principale (visualizzazione menu)
└── admin.html            # Pannello amministrativo (in sviluppo)
```

## Funzionalità Principali

### Menu Digitale (Pubblico)
- Visualizzazione categorie con immagini
- Elenco articoli per categoria
- Ricerca articoli in tempo reale
- Layout responsivo per tutti i dispositivi
- Ottimizzazione performance (animazioni ottimizzate)

### Pannello Amministrativo (In Sviluppo)
- Gestione categorie e articoli
- Caricamento immagini
- Modifica prezzi e descrizioni

## Requisiti Tecnici

### Ambiente di Sviluppo
- PHP 7.4+
- MySQL 5.7+
- Webserver (Apache/Nginx)
- XAMPP per sviluppo locale

### Database
- Tabella `categories`: Gestione categorie menu
- Tabella `articles`: Gestione articoli con relazioni alle categorie

## Setup e Installazione

1. Clonare il repository nella cartella root del webserver
2. Importare `database.sql` nel database MySQL
3. Configurare il file `config.php` con i parametri corretti di connessione
4. Accedere all'applicazione via browser: `http://localhost/menu_digitale/`

## Roadmap di Sviluppo

### In Corso
- Completamento pannello amministrativo
- Ottimizzazione performance
- Miglioramento UX/UI

### Futuro
- Sistema multi-tenant per più clienti
- Integrazione con sistema di vendita POS (Flutter)
- Integrazione con stampanti fiscali (Epson FP81)

## Contatti e Supporto
Pointer - Soluzioni evolute per il punto cassa
Via Trieste 42, Udine
Tel: 0432 111111
Email: info@pointer.it 