# Menu Digitale - Pointer

## Panoramica del Progetto
Sistema di visualizzazione menu digitale per ristoranti/locali, personalizzato per Pointer. L'applicazione permette di visualizzare categorie e articoli di menu in un'interfaccia responsiva e user-friendly, con un pannello amministrativo protetto da autenticazione e un sistema di gestione delle impostazioni completamente personalizzabile.

## Architettura del Sistema

### Frontend
- **HTML/CSS/JavaScript**: Applicazione web responsive
- **Design moderno**: Animazioni CSS e interfaccia intuitiva
- **Ricerca in tempo reale**: Funzionalità di ricerca istantanea per gli articoli
- **Pannello Admin**: Interfaccia sicura per la gestione dei contenuti
- **Gestione immagini flessibile**: Supporto per immagini opzionali in categorie e articoli
- **Impostazioni personalizzabili**: Gestione completa di logo, colori, informazioni di contatto

### Backend
- **PHP**: API RESTful per la gestione dei dati
- **MySQL**: Database per categorie, articoli e impostazioni
- **Architettura modulare**: Separazione tra logica di business e presentazione
- **Autenticazione sicura**: Sistema di login basato su sessioni PHP
- **Auto-manutenzione DB**: Creazione automatica delle strutture necessarie se mancanti

## Struttura del Repository

```
menu_digitale/
├── api/                      # API per l'interazione con il database
│   ├── add_article.php       # Aggiunta di articoli
│   ├── add_category.php      # Aggiunta di categorie
│   ├── update_article.php    # Aggiornamento articoli
│   ├── update_category.php   # Aggiornamento categorie 
│   ├── settings.php          # Gestione impostazioni
│   ├── auth_check.php        # Verifica autenticazione
│   ├── check_auth.php        # Helper autenticazione
│   ├── login.php             # Login amministratore
│   ├── logout.php            # Logout amministratore
│   ├── change_password.php   # Cambio password
│   ├── menu.php              # Endpoint principale per menu e categorie
│   └── articles.php          # Gestione articoli 
├── css/                      # Stili dell'applicazione
│   ├── style.css             # Stile principale
│   └── admin.css             # Stili per pannello admin
├── js/                       # Script JavaScript
│   ├── ui.js                 # Gestione interfaccia utente
│   ├── api.js                # Interazione con le API
│   └── utils.js              # Funzioni di utilità
├── images/                   # Immagini e risorse
│   ├── categories/           # Immagini delle categorie
│   ├── articles/             # Immagini degli articoli
│   └── logos/                # Logo del ristorante
├── config.php                # Configurazione database
├── index.html                # Pagina principale (visualizzazione menu)
├── login.html                # Pagina di login amministratore
└── admin.html                # Pannello amministrativo
```

## Funzionalità Principali

### Menu Digitale (Pubblico)
- Visualizzazione categorie con immagini opzionali
- Elenco articoli per categoria con immagini opzionali
- Ricerca articoli in tempo reale
- Layout responsivo per tutti i dispositivi
- Ottimizzazione performance (animazioni ottimizzate)
- Personalizzazione completa dell'aspetto (colori, logo)
- Informazioni di contatto nel footer
- Link discreto all'area amministrativa

### Pannello Amministrativo
- Autenticazione sicura con username e password
- Gestione completa categorie e articoli (CRUD)
- Caricamento immagini opzionale per categorie e articoli
- Modifica prezzi e descrizioni
- Gestione impostazioni del sito:
  * Logo del ristorante
  * Colori personalizzati
  * Nome del ristorante
  * Informazioni di contatto
  * Orari di apertura
- Cambio password dell'amministratore
- Sistema auto-resiliente con auto-riparazione della struttura del database

## Requisiti Tecnici

### Ambiente di Sviluppo
- PHP 7.4+
- MySQL 5.7+
- Webserver (Apache/Nginx)
- XAMPP per sviluppo locale

### Database
- Tabella `categories`: Gestione categorie menu
- Tabella `articles`: Gestione articoli con relazioni alle categorie
- Tabella `users`: Gestione utenti amministratori
- Tabella `settings`: Gestione impostazioni personalizzate

## Setup e Installazione

1. Clonare il repository nella cartella root del webserver
2. Importare `database.sql` nel database MySQL (opzionale, la struttura si crea automaticamente)
3. Configurare il file `config.php` con i parametri corretti di connessione
4. Accedere all'applicazione via browser: `http://localhost/menu_digitale/`
5. Per accedere all'area admin: `http://localhost/menu_digitale/login.html`
   - Username predefinito: `admin`
   - Password predefinita: `admin123`
   - **IMPORTANTE**: Si raccomanda di cambiare la password predefinita dopo il primo accesso

## Funzionalità di Auto-manutenzione

Il sistema è progettato per essere auto-resiliente:
- Verifica automatica della struttura del database
- Creazione automatica di tabelle e colonne mancanti
- Gestione errori con messaggi informativi
- Logging delle operazioni critiche

## Roadmap di Sviluppo

### In Corso
- Ottimizzazione performance
- Miglioramento UX/UI
- Sistema di backup automatico

### Completato
- Pannello amministrativo sicuro
- Sistema di autenticazione
- CRUD completo per categorie e articoli
- Auto-riparazione struttura database
- Gestione immagini opzionale per categorie e articoli
- Sistema completo di personalizzazione (colori, logo, informazioni)
- Miglioramento della resilienza del sistema

### Futuro
- Sistema multi-tenant per più clienti
- Integrazione con sistema di vendita POS (Flutter)
- Integrazione con stampanti fiscali (Epson FP81)
- Sistema di analytics per monitorare le visualizzazioni
- Supporto multi-lingua
- Modalità dark/light theme

## Contatti e Supporto
Pointer - Soluzioni evolute per il punto cassa
Via Trieste 42, Udine
Tel: 0432 111111
Email: info@pointer.it 