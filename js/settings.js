/**
 * settings.js - Gestione delle impostazioni del sito
 */

// Carica le impostazioni dal server
function loadSettings() {
    console.log("Tentativo di caricamento delle impostazioni...");
    const token = localStorage.getItem('auth_token');
    
    fetch('/menu_digitale/api/settings.php', {
        headers: {
            'Authorization': 'Bearer ' + token
        }
    })
    .then(response => {
        console.log("Risposta ricevuta:", response.status, response.statusText);
        // Controlla se la risposta è OK
        if (!response.ok) {
            throw new Error(`Risposta HTTP non valida: ${response.status} ${response.statusText}`);
        }
        return response.text();
    })
    .then(text => {
        console.log("Testo risposta:", text);
        // Prova a convertire in JSON
        try {
            const data = JSON.parse(text);
            if (data.success) {
                console.log("Dati impostazioni:", data.data);
                populateSettingsForm(data.data);
                showNotification('Impostazioni caricate con successo', 'success');
            } else {
                showNotification('Errore nel caricamento delle impostazioni: ' + data.message, 'error');
            }
        } catch (e) {
            console.error("Errore nel parsing JSON:", e);
            showNotification('Errore nella risposta del server: non è un formato JSON valido', 'error');
        }
    })
    .catch(error => {
        console.error('Errore nel caricamento delle impostazioni:', error);
        showNotification('Errore nella comunicazione con il server', 'error');
    });
}

// Popola i form con i dati delle impostazioni
function populateSettingsForm(settings) {
    // Impostazioni generali
    if (settings.general) {
        document.getElementById('siteName').value = settings.general.siteName || '';
        document.getElementById('siteTagline').value = settings.general.siteTagline || '';
        
        const primaryColor = settings.general.primaryColor || '#1A3C40';
        const accentColor = settings.general.accentColor || '#E76F51';
        
        document.getElementById('primaryColor').value = primaryColor;
        document.getElementById('primaryColorHex').value = primaryColor;
        document.getElementById('accentColor').value = accentColor;
        document.getElementById('accentColorHex').value = accentColor;
        
        // Logo preview se disponibile
        if (settings.general.logoUrl) {
            const logoPreview = document.getElementById('logoPreview');
            logoPreview.src = settings.general.logoUrl;
            logoPreview.style.display = 'block';
        }
    }
    
    // Informazioni di contatto
    if (settings.contact) {
        document.getElementById('address').value = settings.contact.address || '';
        document.getElementById('phone').value = settings.contact.phone || '';
        document.getElementById('email').value = settings.contact.email || '';
        document.getElementById('weekdayHours').value = settings.contact.weekdayHours || '';
        document.getElementById('weekendHours').value = settings.contact.weekendHours || '';
    }
}

// Salva le impostazioni generali
function saveGeneralInfo(e) {
    e.preventDefault();
    console.log("Tentativo di salvataggio impostazioni generali...");
    
    const siteName = document.getElementById('siteName').value;
    const siteTagline = document.getElementById('siteTagline').value;
    const primaryColor = document.getElementById('primaryColor').value;
    const accentColor = document.getElementById('accentColor').value;
    
    // Logo handling
    const logoFileInput = document.getElementById('siteLogo');
    const logoFile = logoFileInput.files[0];
    
    // Create FormData for file upload
    const formData = new FormData();
    formData.append('siteName', siteName);
    formData.append('siteTagline', siteTagline);
    formData.append('primaryColor', primaryColor);
    formData.append('accentColor', accentColor);
    
    if (logoFile) {
        formData.append('logo', logoFile);
        console.log("Logo aggiunto:", logoFile.name, logoFile.size, "bytes");
    }
    
    // Get auth token
    const token = localStorage.getItem('auth_token');
    console.log("Token di autenticazione:", token);
    
    // Invia i dati al server
    fetch('/menu_digitale/api/settings.php', {
        method: 'POST',
        headers: {
            'Authorization': 'Bearer ' + token
        },
        body: formData
    })
    .then(response => {
        console.log("Risposta ricevuta:", response.status, response.statusText);
        if (!response.ok) {
            throw new Error(`Risposta HTTP non valida: ${response.status} ${response.statusText}`);
        }
        return response.text();
    })
    .then(text => {
        console.log("Testo risposta:", text);
        try {
            const data = JSON.parse(text);
            if (data.success) {
                showNotification('Informazioni generali salvate con successo!', 'success');
                
                // Aggiorna i colori CSS
                updateCustomColors(primaryColor, accentColor);
            } else {
                showNotification('Errore: ' + data.message, 'error');
            }
        } catch (e) {
            console.error("Errore nel parsing JSON:", e);
            showNotification('Errore nella risposta del server: non è un formato JSON valido', 'error');
        }
    })
    .catch(error => {
        console.error('Errore:', error);
        showNotification('Errore nella comunicazione con il server', 'error');
    });
}

// Salva le informazioni di contatto
function saveContactInfo(e) {
    e.preventDefault();
    console.log("Tentativo di salvataggio informazioni di contatto...");
    
    const address = document.getElementById('address').value;
    const phone = document.getElementById('phone').value;
    const email = document.getElementById('email').value;
    const weekdayHours = document.getElementById('weekdayHours').value;
    const weekendHours = document.getElementById('weekendHours').value;
    
    // Prepara i dati da inviare
    const formData = new FormData();
    formData.append('address', address);
    formData.append('phone', phone);
    formData.append('email', email);
    formData.append('weekdayHours', weekdayHours);
    formData.append('weekendHours', weekendHours);
    
    console.log("Dati di contatto da inviare:", Object.fromEntries(formData));
    
    // Get auth token
    const token = localStorage.getItem('auth_token');
    
    // Invia i dati al server
    fetch('/menu_digitale/api/settings.php', {
        method: 'POST',
        headers: {
            'Authorization': 'Bearer ' + token
        },
        body: formData
    })
    .then(response => {
        console.log("Risposta ricevuta:", response.status, response.statusText);
        if (!response.ok) {
            throw new Error(`Risposta HTTP non valida: ${response.status} ${response.statusText}`);
        }
        return response.text();
    })
    .then(text => {
        console.log("Testo risposta:", text);
        try {
            const data = JSON.parse(text);
            if (data.success) {
                showNotification('Informazioni di contatto salvate con successo!', 'success');
                // Ricarica le impostazioni per aggiornare l'interfaccia
                loadSettings();
            } else {
                showNotification('Errore: ' + data.message, 'error');
            }
        } catch (e) {
            console.error("Errore nel parsing JSON:", e);
            showNotification('Errore nella risposta del server: non è un formato JSON valido', 'error');
        }
    })
    .catch(error => {
        console.error('Errore:', error);
        showNotification('Errore nella comunicazione con il server', 'error');
    });
}

// Aggiorna i colori custom nel CSS
function updateCustomColors(primaryColor, accentColor) {
    document.documentElement.style.setProperty('--primary-color', primaryColor);
    document.documentElement.style.setProperty('--accent-color', accentColor);
}

// Inizializza i color picker
function initializeColorPickers() {
    const colorInputs = document.querySelectorAll('input[type="color"]');
    colorInputs.forEach(colorInput => {
        const hexInput = colorInput.parentElement.querySelector('.color-hex-input');
        
        // Update hex input when color changes
        colorInput.addEventListener('input', function() {
            hexInput.value = this.value;
        });
        
        // Update color input when hex changes
        hexInput.addEventListener('input', function() {
            // Ensure valid hex format
            if (/^#[0-9A-F]{6}$/i.test(this.value)) {
                colorInput.value = this.value;
            }
        });
    });
}

// Inizializza la preview del logo
function initializeLogoPreview() {
    const logoInput = document.getElementById('siteLogo');
    const logoPreview = document.getElementById('logoPreview');
    
    if (logoInput && logoPreview) {
        logoInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    logoPreview.src = e.target.result;
                    logoPreview.style.display = 'block';
                };
                
                reader.readAsDataURL(this.files[0]);
            }
        });
    }
}

// Mostra una notifica all'utente
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.add('show');
        
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 3000);
    }, 10);
}

// Inizializzazione
document.addEventListener('DOMContentLoaded', function() {
    // Setup degli event listener dei form
    const generalInfoForm = document.getElementById('generalInfoForm');
    if (generalInfoForm) {
        generalInfoForm.addEventListener('submit', saveGeneralInfo);
    }
    
    const contactInfoForm = document.getElementById('contactInfoForm');
    if (contactInfoForm) {
        contactInfoForm.addEventListener('submit', saveContactInfo);
    }
    
    // Inizializza i color picker
    initializeColorPickers();
    
    // Inizializza la preview del logo
    initializeLogoPreview();
    
    // Carica le impostazioni dal server
    loadSettings();
}); 