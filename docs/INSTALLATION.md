# Guida all'Installazione - Menu Digitale

Questa guida fornisce istruzioni dettagliate per l'installazione, la configurazione e l'utilizzo del Menu Digitale in diversi ambienti.

## Requisiti di Sistema

### Software Richiesto
- PHP 7.4 o superiore
- MySQL 5.7 o superiore
- Apache/Nginx Web Server
- Browser moderno (Chrome, Firefox, Safari, Edge)

### Estensioni PHP Richieste
- PDO e PDO_MySQL
- GD (per manipolazione immagini)
- JSON
- mbstring

## Installazione in Ambiente Locale

### Utilizzo di XAMPP (Raccomandato per lo sviluppo)

1. **Installare XAMPP**
   - Scaricare da [https://www.apachefriends.org/](https://www.apachefriends.org/)
   - Installare con le opzioni predefinite

2. **Clonare il Repository**
   ```
   cd c:\xampp\htdocs\
   git clone [url-repository] menu_digitale
   ```
   oppure scaricare e decomprimere l'archivio nella cartella `htdocs`

3. **Configurare il Database**
   - Aprire phpMyAdmin (http://localhost/phpmyadmin)
   - Creare un nuovo database chiamato `menu_db`
   - Importare il file `database.sql` presente nella cartella principale

4. **Configurare il File di Connessione**
   - Aprire `config.php` e verificare/modificare i parametri di connessione:
   ```php
   define('DB_SERVER', 'localhost');
   define('DB_NAME', 'menu_db');
   define('DB_USER', 'root');  
   define('DB_PASS', '');  
   ```

5. **Verificare l'Installazione**
   - Aprire il browser e navigare a: http://localhost/menu_digitale/
   - Verifica che la pagina principale si carichi correttamente
   - Verifica che vengano mostrate le categorie e gli articoli
   - Verifica che la ricerca funzioni correttamente

6. **Risolvere Problemi Comuni**
   - Se appare un errore di connessione al database, verifica i parametri in `config.php`
   - Se le immagini non si caricano, verifica le permissioni della cartella `images`

## Installazione in Ambiente di Produzione

### Web Hosting Condiviso

1. **Caricare i File**
   - Utilizzare FTP/SFTP per caricare tutti i file nella directory principale del sito
   - Verificare che le permissioni dei file siano corrette (644 per i file, 755 per le cartelle)

2. **Creare il Database**
   - Utilizzare cPanel, phpMyAdmin o strumenti simili per creare un database
   - Importare il file `database.sql`

3. **Configurare la Connessione**
   - Modificare il file `config.php` con i parametri di connessione corretti

4. **Verificare l'Installazione**
   - Accedere al sito web dalla URL principale
   - Verificare che tutte le funzionalità siano operative

### VPS/Server Dedicato

1. **Installare il Web Server**
   ```bash
   # Per Ubuntu/Debian
   apt update
   apt install apache2 mysql-server php php-mysql php-gd php-json php-mbstring
   
   # Per CentOS/RHEL
   yum update
   yum install httpd mysql-server php php-mysql php-gd php-json php-mbstring
   ```

2. **Configurare MySQL**
   ```bash
   mysql_secure_installation
   mysql -u root -p
   CREATE DATABASE menu_db;
   CREATE USER 'menuuser'@'localhost' IDENTIFIED BY 'password';
   GRANT ALL PRIVILEGES ON menu_db.* TO 'menuuser'@'localhost';
   FLUSH PRIVILEGES;
   EXIT;
   ```

3. **Clonare il Repository**
   ```bash
   cd /var/www/html/
   git clone [url-repository] menu_digitale
   # Oppure copiare manualmente i file
   ```

4. **Importare il Database**
   ```bash
   mysql -u menuuser -p menu_db < /var/www/html/menu_digitale/database.sql
   ```

5. **Configurare la Connessione**
   - Modificare il file `config.php` con i parametri corretti
   ```php
   define('DB_SERVER', 'localhost');
   define('DB_NAME', 'menu_db');
   define('DB_USER', 'menuuser');  
   define('DB_PASS', 'password');  
   ```

6. **Configurare il Virtual Host**
   ```apache
   # /etc/apache2/sites-available/menu.conf
   <VirtualHost *:80>
       ServerName menu.example.com
       DocumentRoot /var/www/html/menu_digitale
       
       <Directory /var/www/html/menu_digitale>
           Options -Indexes +FollowSymLinks
           AllowOverride All
           Require all granted
       </Directory>
       
       ErrorLog ${APACHE_LOG_DIR}/menu_error.log
       CustomLog ${APACHE_LOG_DIR}/menu_access.log combined
   </VirtualHost>
   ```

7. **Abilitare il Sito e Riavviare Apache**
   ```bash
   # Ubuntu/Debian
   a2ensite menu.conf
   systemctl restart apache2
   
   # CentOS/RHEL
   systemctl restart httpd
   ```

## Configurazione

### Personalizzazione Aspetto

1. **Modificare i Colori**
   - Aprire `css/style.css` e modificare le variabili CSS
   ```css
   :root {
       --primary-color: #3498db;  /* Colore principale */
       --secondary-color: #2ecc71;  /* Colore secondario */
       --accent-color: #f1c40f;  /* Colore di accento */
       --text-color: #333;  /* Colore testo */
       --background-color: #f9f9f9;  /* Colore sfondo */
   }
   ```

2. **Modificare Font**
   - Sostituire l'importazione Google Font in `index.html`
   - Aggiornare la famiglia di font in `css/style.css`

3. **Personalizzare Logo e Favicon**
   - Sostituire i file in `images/logo.png` e `images/favicon.ico`
   - Assicurarsi che il logo abbia dimensioni appropriate

## Sviluppo

### Ambiente di Sviluppo

1. **Editor/IDE Consigliati**
   - Visual Studio Code con estensioni per PHP, JavaScript e HTML/CSS
   - PHPStorm per uno sviluppo PHP più avanzato

2. **Estensioni Browser Utili**
   - Chrome/Firefox DevTools per debug
   - React Developer Tools se si utilizza React
   - Redux DevTools se si utilizza Redux

### Workflow di Sviluppo

1. **Clonare il Repository**
   ```bash
   git clone [url-repository]
   cd menu_digitale
   ```

2. **Creazione Branch per Nuove Funzionalità**
   ```bash
   git checkout -b feature/nome-funzionalità
   ```

3. **Test Locale**
   - Implementare la funzionalità
   - Testare in ambiente locale
   - Assicurarsi che non ci siano regressioni

4. **Commit e Push**
   ```bash
   git add .
   git commit -m "Aggiungi: descrizione della funzionalità"
   git push origin feature/nome-funzionalità
   ```

5. **Pull Request**
   - Creare PR nel repository principale
   - Attendere review e approvazione
   - Dopo l'approvazione, merge nel branch develop/main

## Risoluzione Problemi

### Problemi Comuni e Soluzioni

#### Errore di Connessione al Database
**Problema**: 
```
Errore connessione database: SQLSTATE[HY000] [1045] Access denied for user...
```

**Soluzioni**:
1. Verificare le credenziali in `config.php`
2. Verificare che l'utente MySQL abbia i permessi corretti
3. Controllare che il servizio MySQL sia in esecuzione

#### Problemi di Caricamento Immagini
**Problema**: Immagini non visualizzate o errori 404

**Soluzioni**:
1. Verificare che le cartelle `images` abbiano permessi di lettura (755)
2. Controllare che i percorsi relativi siano corretti
3. Verificare che i file immagine esistano nelle posizioni specificate

#### Errori PHP
**Problema**: "White screen of death" o errori 500

**Soluzioni**:
1. Abilitare error reporting in `php.ini` o aggiungere all'inizio degli script PHP:
   ```php
   ini_set('display_errors', 1);
   error_reporting(E_ALL);
   ```
2. Controllare i log degli errori:
   - XAMPP: `C:\xampp\php\logs\php_error_log`
   - Linux: `/var/log/apache2/error.log` o `/var/log/httpd/error_log`

## Manutenzione

### Backup

1. **Database**
   ```bash
   # Esportare il database
   mysqldump -u root -p menu_db > backup_$(date +%Y%m%d).sql
   
   # Importare il database (se necessario)
   mysql -u root -p menu_db < backup_20230101.sql
   ```

2. **File**
   ```bash
   # Creare archivio dei file
   tar -czvf menu_digitale_$(date +%Y%m%d).tar.gz /path/to/menu_digitale
   ```

### Aggiornamenti

1. **Backup Preventivo**
   - Eseguire backup di database e file prima di aggiornare

2. **Aggiornamento da Repository**
   ```bash
   cd /path/to/menu_digitale
   git pull origin main
   ```

3. **Aggiornamenti Manuali**
   - Sovrascrivere i file con l'ultima versione
   - Eseguire eventuali script di migrazione database

## Supporto

Per domande o problemi:
- Email: info@pointer.it
- Telefono: 0432 111111 