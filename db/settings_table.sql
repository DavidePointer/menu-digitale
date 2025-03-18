-- Creazione della tabella settings per memorizzare le impostazioni del sito
CREATE TABLE IF NOT EXISTS settings (
    setting_id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inserimento delle impostazioni predefinite
INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
    ('site_name', 'Pointer - Menu Digitale'),
    ('site_tagline', 'Soluzioni evolute per il punto cassa'),
    ('primary_color', '#1A3C40'),
    ('accent_color', '#E76F51'),
    ('address', 'Via Trieste 42, Udine'),
    ('phone', '0432 111111'),
    ('email', 'info@pointer.it'),
    ('weekday_hours', '8:30-12:30, 14:30-18:30'),
    ('weekend_hours', 'Chiuso'); 