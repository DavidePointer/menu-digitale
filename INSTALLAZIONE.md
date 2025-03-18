# Guida all'Installazione e Manutenzione - Menu Digitale Pointer

## Requisiti di Sistema

### Software Necessario
- Web server: Apache 2.4+
- PHP 7.4+ con estensioni: 
  - PDO
  - PDO_MySQL
  - GD (per gestione immagini)
  - JSON
  - Session
- MySQL 5.7+ o MariaDB 10.3+
- XAMPP 7.4+ (raccomandata per sviluppo locale)

### Spazio su Disco
- Minimo: 50MB + spazio per le immagini
- Raccomandato: 500MB per consentire crescita del database e delle immagini

## Procedura di Installazione

### 1. Configurazione Ambiente
1. Installare XAMPP o configurare un ambiente web server con PHP e MySQL
2. Avviare i servizi Apache e MySQL
3. Verificare che il server web sia operativo

### 2. Installazione Applicazione
1. Clonare il repository:
   ```bash
   git clone https://github.com/DavidePointer/menu-digitale.git
   ```
2. Spostare i file nella cartella del server web:
   - Per XAMPP: `C:\xampp\htdocs\menu_digitale\`
   - Per altri server: nella cartella pubblica del web server

### 3. Configurazione Database
1. Accedere a phpMyAdmin (http://localhost/phpmyadmin)
2. Creare un nuovo database chiamato `menu_db`
3. Configurare il file `config.php` con i parametri corretti:
   ```php
   define('DB_SERVER', 'localhost');
   define('DB_NAME', 'menu_db');
   define('DB_USER', 'root');  // Modificare con utente personalizzato
   define('DB_PASS', '');      // Modificare con password sicura
   ```

**Nota**: Il sistema è progettato per creare automaticamente le tabelle necessarie al primo utilizzo, quindi non è richiesta l'importazione manuale di un file SQL.

### 4. Configurazione Permessi
1. Assicurarsi che le cartelle `images/categories` e `images/articles` abbiano permessi di scrittura:
   - Linux/Mac: `chmod 755 images/categories images/articles`
   - Windows: verificare che l'utente del web server abbia accesso in scrittura

### 5. Primo Accesso
1. Aprire il browser e navigare a: http://localhost/menu_digitale/
2. Per accedere all'area amministrativa: http://localhost/menu_digitale/login.html
   - Credenziali predefinite:
     - Username: `admin`
     - Password: `admin123`
   - **IMPORTANTE**: Cambiare immediatamente la password predefinita!

## Funzionalità di Auto-manutenzione

Il sistema è dotato di capacità di auto-diagnostica e auto-riparazione:

### Auto-creazione Strutture Database
- Al primo utilizzo, il sistema crea automaticamente le tabelle necessarie
- Quando vengono aggiunte funzionalità, il sistema controlla e aggiunge le colonne mancanti
- Non è necessario eseguire manualmente script di aggiornamento database

### Gestione Errori
- Il sistema registra gli errori in file di log
- Messaggi di errore user-friendly mostrati all'utente
- Meccanismi di recovery automatici per problemi comuni

### Sicurezza
- Il sistema implementa protezione contro SQL injection
- Autenticazione sicura con password hashate
- Protezione contro CSRF e XSS
- Controllo sessioni per prevenire session hijacking

## Manutenzione Periodica

### Backup
Si raccomanda di eseguire regolarmente:
1. Backup del database:
   ```bash
   mysqldump -u root -p menu_db > menu_db_backup.sql
   ```
2. Backup dei file dell'applicazione, specialmente le immagini

### Aggiornamenti
Per aggiornare l'applicazione:
1. Effettuare backup di database e file
2. Eseguire `git pull` nella directory del progetto
3. Verificare il funzionamento dopo l'aggiornamento

### Pulizia Periodica
- Rimuovere immagini non utilizzate
- Ottimizzare le tabelle del database

## Risoluzione Problemi

### Problemi Database
- **Errore di connessione**: Verificare parametri in `config.php`
- **Tabelle mancanti**: Riavviare l'applicazione, la creazione è automatica
- **Colonne mancanti**: Il sistema dovrebbe aggiungerle automaticamente

### Problemi Upload Immagini
- Verificare permessi delle cartelle `images/`
- Controllare limiti di upload in `php.ini` (post_max_size, upload_max_filesize)

### Problemi Autenticazione
- Se impossibile accedere: resettare password via database
  ```sql
  UPDATE users SET password = '$2y$10$i9KjspSMuP5GvhJoSQ.Bfu0DoXbHbLShYHY.VQq6KCgFIjEKCTD0m' WHERE username = 'admin';
  ```
  (Questo resetta la password ad 'admin123' - da cambiare immediatamente!)

## Contatti Supporto
In caso di problemi persistenti, contattare:
- Supporto tecnico: tech@pointer.it
- Telefono: 0432 111111
- Orari: Lun-Ven 8:30-12:30, 14:30-18:30 