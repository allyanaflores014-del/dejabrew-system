// ========== ORDERS DATA ==========
// Orders are saved in localStorage, just like the cart.
// This file is used by the staff page (to read orders)
// and by the cart page (to save a new order at checkout).

const ORDERS_KEY = 'dejabrewOrders';

function getOrders() {
    const saved = localStorage.getItem(ORDERS_KEY);
    return saved ? JSON.parse(saved) : [];
}

function saveOrders(orders) {
    localStorage.setItem(ORDERS_KEY, JSON.stringify(orders));
}
// The next order number: highest existing number + 1 (starts at 1001)
function getNextOrderId() {
    const orders = getOrders();
    let highest = 1000;

    orders.forEach(function (order) {
        if (order.id > highest) {
            highest = order.id;
        }
    });

    return highest + 1;
}
// Only the orders of ONE customer (matched by email, ignoring capital letters).
function getOrdersForCustomer(email) {
    const wanted = String(email).toLowerCase();
    return getOrders().filter(function (order) {
        return String(order.customerEmail).toLowerCase() === wanted;
    });
}


// ========== SAMPLE ORDERS ==========
// Fake orders so the staff page has something to show.
// Prices and image names match includes/products.php.
const SAMPLE_ORDERS = [
    {
        id: 1008,
        customerName: 'Jane Doe',
        customerEmail: 'JaneDoe@gmail.com',
        createdAt: '2026-10-09T12:30:00',
        pickupTime: '13:00',
        status: 'pending',
        paymentMethod: 'Pay upon pickup',
        note: '',
        items: [
            { name: 'Caramel Macchiato',   size: 'Medium 16oz', sugar: '50%', price: 145, quantity: 1, image: 'assets/CaramelMacchiato.png' },
            { name: 'Classic Iced Coffee', size: 'Medium 16oz', sugar: '50%', price: 120, quantity: 1, image: 'assets/Classic.png' }
        ],
        subtotal: 265,
        discount: 0,
        total: 265
    },
    {
        id: 1007,
        customerName: 'John Cruz',
        customerEmail: 'JohnCruz@gmail.com',
        createdAt: '2026-10-09T12:10:00',
        pickupTime: '12:45',
        status: 'preparing',
        paymentMethod: 'Pay upon pickup',
        note: 'Less ice please',
        items: [
            { name: 'Spanish Latte', size: 'Large 20oz', sugar: '75%', price: 140, quantity: 1, image: 'assets/SpanishLatte.png' }
        ],
        subtotal: 140,
        discount: 0,
        total: 140
    },
    {
        id: 1006,
        customerName: 'Ana Reyes',
        customerEmail: 'AnaReyes@gmail.com',
        createdAt: '2026-10-09T11:45:00',
        pickupTime: '12:15',
        status: 'completed',
        paymentMethod: 'Pay upon pickup',
        note: '',
        items: [
            { name: 'Cafe Latte',       size: 'Small 12oz',  sugar: '50%',  price: 125, quantity: 2, image: 'assets/CafeLatte.png' },
            { name: 'Chocolate Frappe', size: 'Medium 16oz', sugar: '100%', price: 150, quantity: 1, image: 'assets/ChocolateFrappe.png' }
        ],
        subtotal: 400,
        discount: 20,       // DEJA5 = 5% off
        total: 380
    },
    {
        id: 1005,
        customerName: 'Mark Santos',
        customerEmail: 'MarkSantos@gmail.com',
        createdAt: '2026-10-09T11:20:00',
        pickupTime: '11:40',
        status: 'completed',
        paymentMethod: 'Pay upon pickup',
        note: 'No sugar',
        items: [
            { name: 'Classic Americano', size: 'Medium 16oz', sugar: '0%', price: 100, quantity: 1, image: 'assets/Americano.png' }
        ],
        subtotal: 100,
        discount: 0,
        total: 100
    },
    {
        id: 1004,
        customerName: 'Lia Garcia',
        customerEmail: 'LiaGarcia@gmail.com',
        createdAt: '2026-10-09T11:05:00',
        pickupTime: '11:30',
        status: 'pending',
        paymentMethod: 'Pay upon pickup',
        note: 'No whipped cream',
        items: [
            { name: 'Java Chip Frappe', size: 'Large 20oz',  sugar: '100%', price: 155, quantity: 1, image: 'assets/JavaChipFrappe.png' },
            { name: 'Vanilla Latte',    size: 'Medium 16oz', sugar: '50%',  price: 135, quantity: 1, image: 'assets/VanillaLatte.png' }
        ],
        subtotal: 290,
        discount: 15,       // DEJA5 = 5% of 290, rounded
        total: 275
    },
    {
        id: 1003,
        customerName: 'Paolo Lim',
        customerEmail: 'PaoloLim@gmail.com',
        createdAt: '2026-10-09T10:50:00',
        pickupTime: '11:15',
        status: 'cancelled',
        paymentMethod: 'Pay upon pickup',
        note: '',
        items: [
            { name: 'Mocha Latte', size: 'Medium 16oz', sugar: '50%', price: 140, quantity: 1, image: 'assets/MochaLatte.png' }
        ],
        subtotal: 140,
        discount: 0,
        total: 140
    }
];

// Save the sample orders ONLY if there are no orders yet.
function seedSampleOrders() {
    if (localStorage.getItem(ORDERS_KEY) === null) {
        saveOrders(SAMPLE_ORDERS);
    }
}

// For testing: put the sample orders back (call it from the Console)
function resetSampleOrders() {
    saveOrders(SAMPLE_ORDERS);
    location.reload();
}