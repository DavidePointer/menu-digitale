const menuData = {
    items: [
        {
            id: 1,
            name: "Margherita",
            category: "Pizza",
            description: "Pomodoro, mozzarella, basilico",
            price: 8.50,
            image: "img/dishes/margherita.jpg"
        },
        {
            id: 2,
            name: "Carbonara",
            category: "Pasta",
            description: "Spaghetti con uovo, guanciale, pecorino",
            price: 12.00,
            image: "img/dishes/carbonara.jpg"
        },
        {
            id: 3,
            name: "Insalata Mista",
            category: "Insalate",
            description: "Insalata mista di stagione",
            price: 6.50,
            image: "img/dishes/insalata.jpg"
        }
        // Aggiungi altri piatti qui
    ]
};

// Simula una chiamata API
function getMenuData() {
    return new Promise((resolve) => {
        setTimeout(() => {
            resolve(menuData);
        }, 500);
    });
} 