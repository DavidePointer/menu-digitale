# Changelog

Tutte le modifiche significative al progetto saranno documentate in questo file.

Il formato è basato su [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
e questo progetto aderisce al [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] - 2024-03-19

### Aggiunto
- Sistema completo di gestione delle impostazioni del sito
- Personalizzazione del logo del ristorante
- Personalizzazione dei colori principali e di accento
- Gestione dinamica delle informazioni di contatto nel footer
- Gestione degli orari di apertura
- Nuova tabella `settings` nel database per le impostazioni
- Nuova API `settings.php` per la gestione delle impostazioni
- Directory `logos/` per il salvataggio dei loghi dei ristoranti

### Modificato
- Migliorata la struttura del footer con informazioni dinamiche
- Ottimizzato il caricamento delle impostazioni all'avvio
- Aggiornata l'interfaccia amministrativa con sezione impostazioni
- Migliorata la gestione delle immagini opzionali
- Aggiornata la documentazione con le nuove funzionalità

### Corretto
- Risolto il problema della duplicazione delle informazioni di contatto
- Migliorata la gestione degli errori nel caricamento delle immagini
- Ottimizzata la visualizzazione dei contenuti su dispositivi mobili

## [1.0.0] - 2024-03-01

### Aggiunto
- Prima release pubblica
- Sistema di autenticazione per amministratori
- CRUD completo per categorie e articoli
- Gestione immagini per categorie e articoli
- Ricerca in tempo reale degli articoli
- Interfaccia responsiva
- Sistema di auto-riparazione del database
- Logging delle operazioni critiche

### Sicurezza
- Implementata protezione contro SQL injection
- Implementata protezione contro XSS
- Implementata validazione input lato server
- Implementata gestione sicura delle sessioni 