<?php
// api/settings.php - Gestione delle impostazioni del sito

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Includi il file di autenticazione e configurazione
require_once 'auth.php';
require_once '../config.php';

// Se è una richiesta OPTIONS, termina qui
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Inizializza la connessione al database
try {
    $conn = getDBConnection();
} catch (PDOException $e) {
    logAuthMessage("Errore di connessione al database: " . $e->getMessage());
    jsonResponse(false, 'Errore di connessione al database');
}

// Cartella delle immagini dei loghi
$logoDirectory = '../images/logos/';

// Assicurati che la cartella dei loghi esista e sia scrivibile
if (!file_exists($logoDirectory)) {
    mkdir($logoDirectory, 0755, true);
}

// Funzione di risposta
function sendResponse($success, $message = '', $data = null) {
    // Usa jsonResponse dalla libreria auth.php
    jsonResponse($success, $message, $data);
}

// Verifica se l'utente è autenticato per tutte le operazioni di scrittura
function requireAuth() {
    if (!isAuthenticated()) {
        logAuthMessage("Tentativo di accesso alle impostazioni senza autenticazione");
        jsonResponse(false, 'Utente non autenticato');
    }
}

// Ottieni il metodo di richiesta
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Recupera le impostazioni
        getSettings();
        break;
    case 'POST':
        // Salva le impostazioni
        requireAuth();
        saveSettings();
        break;
    default:
        sendResponse(false, 'Metodo non supportato');
}

// Funzione per recuperare le impostazioni
function getSettings() {
    global $conn;
    
    try {
        // Inizializza impostazioni predefinite
        $formattedSettings = array(
            'general' => array(
                'siteName' => '',
                'siteTagline' => '',
                'logoUrl' => '',
                'primaryColor' => '#1A3C40',
                'accentColor' => '#E76F51'
            ),
            'contact' => array(
                'address' => '',
                'phone' => '',
                'email' => '',
                'weekdayHours' => '',
                'weekendHours' => ''
            )
        );
        
        // Controlla se esiste la tabella delle impostazioni
        $stmt = $conn->query("SHOW TABLES LIKE 'settings'");
        if ($stmt->rowCount() == 0) {
            // La tabella non esiste, crea la tabella
            logAuthMessage("Tabella settings non esistente, la creo");
            $sql = "CREATE TABLE settings (
                setting_id INT AUTO_INCREMENT PRIMARY KEY,
                setting_key VARCHAR(255) NOT NULL UNIQUE,
                setting_value TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )";
            $conn->exec($sql);
            
            // Restituisci le impostazioni predefinite
            jsonResponse(true, 'Impostazioni predefinite', $formattedSettings);
            return;
        }
        
        // Leggi le impostazioni generali
        $stmt = $conn->prepare("SELECT setting_key, setting_value FROM settings");
        $stmt->execute();
        
        $settings = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        
        // Aggiorna le impostazioni con i valori dal database
        if (isset($settings['site_name'])) $formattedSettings['general']['siteName'] = $settings['site_name'];
        if (isset($settings['site_tagline'])) $formattedSettings['general']['siteTagline'] = $settings['site_tagline'];
        if (isset($settings['logo_url'])) $formattedSettings['general']['logoUrl'] = $settings['logo_url'];
        if (isset($settings['primary_color'])) $formattedSettings['general']['primaryColor'] = $settings['primary_color'];
        if (isset($settings['accent_color'])) $formattedSettings['general']['accentColor'] = $settings['accent_color'];
        
        if (isset($settings['address'])) $formattedSettings['contact']['address'] = $settings['address'];
        if (isset($settings['phone'])) $formattedSettings['contact']['phone'] = $settings['phone'];
        if (isset($settings['email'])) $formattedSettings['contact']['email'] = $settings['email'];
        if (isset($settings['weekday_hours'])) $formattedSettings['contact']['weekdayHours'] = $settings['weekday_hours'];
        if (isset($settings['weekend_hours'])) $formattedSettings['contact']['weekendHours'] = $settings['weekend_hours'];
        
        jsonResponse(true, 'Impostazioni recuperate con successo', $formattedSettings);
    } catch (PDOException $e) {
        logAuthMessage("Errore nel recupero delle impostazioni: " . $e->getMessage());
        jsonResponse(false, 'Errore nel recupero delle impostazioni: ' . $e->getMessage());
    }
}

// Funzione per salvare le impostazioni
function saveSettings() {
    global $conn;
    
    try {
        // Controlla se esiste la tabella settings
        $stmt = $conn->query("SHOW TABLES LIKE 'settings'");
        if ($stmt->rowCount() == 0) {
            // La tabella non esiste, crea la tabella
            logAuthMessage("Tabella settings non esistente, la creo");
            $sql = "CREATE TABLE settings (
                setting_id INT AUTO_INCREMENT PRIMARY KEY,
                setting_key VARCHAR(255) NOT NULL UNIQUE,
                setting_value TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )";
            $conn->exec($sql);
        }
    
        // Verifica se è una richiesta JSON o form data
        $contentType = isset($_SERVER["CONTENT_TYPE"]) ? $_SERVER["CONTENT_TYPE"] : "";
        $isJson = strpos($contentType, "application/json") !== false;
        
        if ($isJson) {
            // Recupera i dati JSON
            $inputData = file_get_contents('php://input');
            logAuthMessage("Dati ricevuti (JSON): " . $inputData);
            $data = json_decode($inputData, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                logAuthMessage("Errore JSON: " . json_last_error_msg());
                jsonResponse(false, 'Dati JSON non validi: ' . json_last_error_msg());
            }
        } else {
            // Form data
            $data = $_POST;
            logAuthMessage("Dati ricevuti (FORM): " . print_r($data, true));
        }
        
        $conn->beginTransaction();
        
        // Gestione dell'upload del logo se presente
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
            $logo = $_FILES['logo'];
            $extension = pathinfo($logo['name'], PATHINFO_EXTENSION);
            $filename = 'logo_' . time() . '.' . $extension;
            $logoPath = $GLOBALS['logoDirectory'] . $filename;
            
            if (move_uploaded_file($logo['tmp_name'], $logoPath)) {
                // Aggiorna il percorso del logo nelle impostazioni
                updateSetting($conn, 'logo_url', '/menu_digitale/images/logos/' . $filename);
            } else {
                logAuthMessage("Errore nel caricamento del logo");
                jsonResponse(false, 'Errore nel caricamento del logo');
            }
        }
        
        // Aggiorna le impostazioni
        foreach ($data as $key => $value) {
            switch ($key) {
                case 'siteName':
                    updateSetting($conn, 'site_name', $value);
                    break;
                case 'siteTagline':
                    updateSetting($conn, 'site_tagline', $value);
                    break;
                case 'primaryColor':
                    updateSetting($conn, 'primary_color', $value);
                    break;
                case 'accentColor':
                    updateSetting($conn, 'accent_color', $value);
                    break;
                case 'address':
                    updateSetting($conn, 'address', $value);
                    break;
                case 'phone':
                    updateSetting($conn, 'phone', $value);
                    break;
                case 'email':
                    updateSetting($conn, 'email', $value);
                    break;
                case 'weekdayHours':
                    updateSetting($conn, 'weekday_hours', $value);
                    break;
                case 'weekendHours':
                    updateSetting($conn, 'weekend_hours', $value);
                    break;
            }
        }
        
        $conn->commit();
        logAuthMessage("Impostazioni salvate con successo");
        jsonResponse(true, 'Impostazioni salvate con successo');
    } catch (PDOException $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        logAuthMessage("Errore nel salvataggio delle impostazioni: " . $e->getMessage());
        jsonResponse(false, 'Errore nel salvataggio delle impostazioni: ' . $e->getMessage());
    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        logAuthMessage("Errore generico: " . $e->getMessage());
        jsonResponse(false, 'Errore: ' . $e->getMessage());
    }
}

// Funzione di supporto per aggiornare una singola impostazione
function updateSetting($conn, $key, $value) {
    // Verifica se l'impostazione esiste già
    $stmt = $conn->prepare("SELECT COUNT(*) FROM settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $exists = (int)$stmt->fetchColumn() > 0;

    if ($exists) {
        // Aggiorna il valore esistente
        $stmt = $conn->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
        $stmt->execute([$value, $key]);
    } else {
        // Inserisci una nuova impostazione
        $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
        $stmt->execute([$key, $value]);
    }
    
    logAuthMessage("Impostazione $key aggiornata con successo");
    return true;
} 