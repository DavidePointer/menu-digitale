document.addEventListener('DOMContentLoaded', function() {
    loadCategories();
    setupImagePreviews();
    setupFormSubmissions();
});

function loadCategories() {
    fetch('api/get_categories.php')
        .then(response => response.json())
        .then(categories => {
            const select = document.getElementById('articleCategory');
            select.innerHTML = '';
            categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.category_id;
                option.textContent = category.name;
                select.appendChild(option);
            });
        })
        .catch(error => console.error('Errore nel caricamento delle categorie:', error));
}

function setupImagePreviews() {
    const setupPreview = (inputId, previewId) => {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        
        input.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    };

    setupPreview('categoryImage', 'categoryPreview');
    setupPreview('articleImage', 'articlePreview');
}

function setupFormSubmissions() {
    // Gestione form categoria
    document.getElementById('categoryForm').addEventListener('submit', function(e) {
        e.preventDefault();
        console.log('Invio form categoria...');
        
        const formData = new FormData();
        const nameInput = document.getElementById('categoryName');
        const imageInput = document.getElementById('categoryImage');
        
        console.log('Nome categoria:', nameInput.value);
        console.log('File selezionato:', imageInput.files[0]);
        
        formData.append('name', nameInput.value);
        formData.append('image', imageInput.files[0]);

        fetch('api/add_category.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Risposta ricevuta:', response);
            return response.text(); // Cambiato da json() a text() per vedere il contenuto esatto
        })
        .then(data => {
            console.log('Dati ricevuti:', data);
            try {
                const jsonData = JSON.parse(data);
                if (jsonData.success) {
                    alert('Categoria aggiunta con successo!');
                    loadCategories();
                    this.reset();
                    document.getElementById('categoryPreview').style.display = 'none';
                } else {
                    alert('Errore: ' + jsonData.message);
                }
            } catch (e) {
                console.error('Errore nel parsing JSON:', e);
                alert('Errore nella risposta del server. Controlla la console per i dettagli.');
            }
        })
        .catch(error => {
            console.error('Errore nella richiesta:', error);
            alert('Errore nella comunicazione con il server: ' + error.message);
        });
    });

    // Gestione form articolo
    document.getElementById('articleForm').addEventListener('submit', function(e) {
        e.preventDefault();
        console.log('Invio form articolo...');
        
        const formData = new FormData();
        formData.append('category_id', document.getElementById('articleCategory').value);
        formData.append('name', document.getElementById('articleName').value);
        formData.append('description', document.getElementById('articleDescription').value);
        formData.append('price', document.getElementById('articlePrice').value);
        formData.append('image', document.getElementById('articleImage').files[0]);

        fetch('api/add_article.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            console.log('Risposta articolo:', data);
            try {
                const jsonData = JSON.parse(data);
                if (jsonData.success) {
                    alert('Articolo aggiunto con successo!');
                    this.reset();
                    document.getElementById('articlePreview').style.display = 'none';
                } else {
                    alert('Errore: ' + jsonData.message);
                }
            } catch (e) {
                console.error('Errore nel parsing JSON:', e);
                alert('Errore nella risposta del server. Controlla la console per i dettagli.');
            }
        })
        .catch(error => {
            console.error('Errore:', error);
            alert('Errore nella comunicazione con il server: ' + error.message);
        });
    });
} 