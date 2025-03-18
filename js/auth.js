/**
 * Funzioni per la gestione dell'autenticazione
 */

// Verifica se l'utente è autenticato
function checkAuth() {
    console.log("Verifico autenticazione...");
    
    // Mostra loader di autenticazione
    const authLoader = document.getElementById('authLoader');
    const adminContent = document.getElementById('adminContent');
    
    if (authLoader) authLoader.style.display = 'flex';
    if (adminContent) adminContent.style.display = 'none';
    
    // Controlla se esiste un token
    const token = localStorage.getItem('auth_token');
    
    if (!token) {
        console.log('Nessun token trovato, reindirizzamento a login');
        window.location.href = 'login.html';
        return;
    }
    
    console.log("Token trovato, verifico validità...");
    
    // Verifica la validità del token
    fetch('/menu_digitale/api/verify_token.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + token
        },
        body: JSON.stringify({ token: token }) // Invio token anche nel body per compatibilità
    })
    .then(response => {
        console.log("Risposta ricevuta:", response.status);
        return response.json();
    })
    .then(data => {
        console.log("Dati risposta:", data);
        
        if (authLoader) authLoader.style.display = 'none';
        
        if (data.success) {
            // Token valido, mostra contenuto admin
            if (adminContent) {
                adminContent.style.display = 'block';
                
                // Aggiorna nome utente se disponibile
                if (data.data && data.data.username) {
                    const userNameEl = document.getElementById('userName');
                    if (userNameEl) userNameEl.textContent = data.data.username;
                }
            }
        } else {
            // Token non valido, reindirizza a login
            console.log('Token non valido:', data.message);
            localStorage.removeItem('auth_token');
            window.location.href = 'login.html';
        }
    })
    .catch(error => {
        console.error('Errore durante la verifica del token:', error);
        if (authLoader) authLoader.style.display = 'none';
        
        // In caso di errore, assume che il token non sia valido
        localStorage.removeItem('auth_token');
        window.location.href = 'login.html';
    });
}

// Esegue il logout
function logout() {
    // Recupera il token di autenticazione
    const token = localStorage.getItem('auth_token');
    
    // Elimina il token dal localStorage
    localStorage.removeItem('auth_token');
    
    // Chiamata API di logout (opzionale)
    if (token) {
        fetch('/menu_digitale/api/logout.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + token
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log('Logout completato:', data.message);
        })
        .catch(error => {
            console.error('Errore durante il logout:', error);
        })
        .finally(() => {
            // Reindirizza alla pagina di login a prescindere
            window.location.href = 'login.html';
        });
    } else {
        // Se non c'è token, reindirizza direttamente
        window.location.href = 'login.html';
    }
}

// Aggiunta dell'header di autenticazione alle richieste fetch
function fetchWithAuth(url, options = {}) {
    const token = localStorage.getItem('auth_token');
    
    if (!token) {
        return Promise.reject(new Error('Token di autenticazione non disponibile'));
    }
    
    // Aggiungi l'header di autorizzazione
    const headers = options.headers || {};
    headers['Authorization'] = 'Bearer ' + token;
    
    return fetch(url, {
        ...options,
        headers: headers
    });
}

// Cambio password per l'utente autenticato
function changePassword(currentPassword, newPassword) {
    return fetchWithAuth('/menu_digitale/api/change_password.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            currentPassword: currentPassword,
            newPassword: newPassword
        })
    })
    .then(response => response.json());
} 