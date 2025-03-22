/**
 * test_view_system.js - Test suite per il sistema di visualizzazione articoli
 * Versione 1.0
 */

console.log('Inizializzazione test del sistema di visualizzazione...');

// Utility di test
const TestRunner = {
    passed: 0,
    failed: 0,
    
    assert(condition, message) {
        if (condition) {
            this.passed++;
            console.log('✅ Test passato:', message);
        } else {
            this.failed++;
            console.error('❌ Test fallito:', message);
        }
    },
    
    summary() {
        console.log(`\nRiepilogo Test:
        ✅ Passati: ${this.passed}
        ❌ Falliti: ${this.failed}
        Totale: ${this.passed + this.failed}`);
    }
};

// Test Suite
class ViewSystemTest {
    constructor() {
        this.setupTestEnvironment();
    }

    setupTestEnvironment() {
        // Pulisce localStorage per i test
        localStorage.removeItem('articleViewMode');
        
        // Crea un contenitore di test se non esiste
        if (!document.getElementById('menuItems')) {
            const container = document.createElement('div');
            container.id = 'menuItems';
            document.body.appendChild(container);
        }
    }

    async runAllTests() {
        console.log('Esecuzione test del sistema di visualizzazione...\n');
        
        // Test di base
        this.testDefaultView();
        this.testViewPersistence();
        this.testViewToggle();
        
        // Test UI
        this.testUIUpdate();
        
        // Test Edge Cases
        this.testInvalidValues();
        this.testLocalStorageFailure();
        
        // Mostra riepilogo
        TestRunner.summary();
    }

    testDefaultView() {
        // Test vista predefinita
        const defaultView = viewToggle.get();
        TestRunner.assert(
            defaultView === 'card',
            'La vista predefinita dovrebbe essere "card"'
        );
    }

    testViewPersistence() {
        // Test persistenza vista
        viewToggle.save('list');
        const savedView = viewToggle.get();
        TestRunner.assert(
            savedView === 'list',
            'La vista salvata dovrebbe persistere in localStorage'
        );
    }

    testViewToggle() {
        // Test cambio vista
        viewToggle.apply('card');
        const menuItems = document.getElementById('menuItems');
        TestRunner.assert(
            menuItems.classList.contains('view-card'),
            'Il contenitore dovrebbe avere la classe view-card'
        );
        
        viewToggle.apply('list');
        TestRunner.assert(
            menuItems.classList.contains('view-list'),
            'Il contenitore dovrebbe avere la classe view-list'
        );
    }

    testUIUpdate() {
        // Test aggiornamento UI
        const menuItems = document.getElementById('menuItems');
        viewToggle.apply('card');
        
        TestRunner.assert(
            document.body.classList.contains('view-card'),
            'Il body dovrebbe avere la classe view-card'
        );
        
        TestRunner.assert(
            menuItems.classList.contains('view-card'),
            'Il contenitore dovrebbe avere la classe view-card'
        );
    }

    testInvalidValues() {
        // Test valori non validi
        const result = viewToggle.save('invalid_view');
        TestRunner.assert(
            result === null,
            'I valori non validi dovrebbero essere rifiutati'
        );
        
        const currentView = viewToggle.get();
        TestRunner.assert(
            currentView === 'card' || currentView === 'list',
            'La vista dovrebbe sempre essere valida'
        );
    }

    testLocalStorageFailure() {
        // Simula fallimento localStorage
        const originalSetItem = localStorage.setItem;
        localStorage.setItem = () => { throw new Error('Storage error'); };
        
        try {
            viewToggle.save('card');
            TestRunner.assert(
                true,
                'Il sistema dovrebbe gestire gli errori di localStorage senza crash'
            );
        } catch (e) {
            TestRunner.assert(
                false,
                'Il sistema non dovrebbe crashare per errori di localStorage'
            );
        }
        
        // Ripristina localStorage
        localStorage.setItem = originalSetItem;
    }
}

// Esegui i test quando il DOM è caricato
document.addEventListener('DOMContentLoaded', () => {
    const tester = new ViewSystemTest();
    tester.runAllTests();
});