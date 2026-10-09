// ========== 1. SAVING & LOADING THE CART ==========
const CART_KEY = 'dejabrewCart';

function getCart() {
    const saved = localStorage.getItem(CART_KEY);
    return saved ? JSON.parse(saved) : [];
}

function saveCart(cart) {
    localStorage.setItem(CART_KEY, JSON.stringify(cart));
}

// Makes text safe to put inside HTML
function escapeHtml(text) {
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}


// ========== 2. ADD TO CART ==========
// product = { id, name, price, image, size, sugar }
// "id" already includes the size and sugar, so different options = different item.
function addToCart(product) {
    const cart = getCart();
    const existingItem = cart.find(item => String(item.id) === String(product.id));

    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({ ...product, quantity: 1 });
    }

    saveCart(cart);
    renderCart();
}


// ========== 3. CHANGE QUANTITY ==========
function changeQuantity(id, change) {
    const cart = getCart();
    const item = cart.find(cartItem => String(cartItem.id) === String(id));
    if (!item) return;

    if (item.quantity + change >= 1) {
        item.quantity += change;
    }

    saveCart(cart);
    renderCart();
}


// ========== 4. REMOVE ITEM ==========
function removeItem(id) {
    const cart = getCart();
    const updatedCart = cart.filter(item => String(item.id) !== String(id));

    saveCart(updatedCart);
    renderCart();
}


// ========== 5. VOUCHERS ==========
const VOUCHER_KEY = 'dejabrewVoucher';

// rate: 0.05 means 5% off | minOrder: minimum subtotal in pesos
const VOUCHERS = {
    'DEJA5':  { rate: 0.05, minOrder: 200, label: '5% off (orders above ₱200)' },
    'DEJA10': { rate: 0.10, minOrder: 800, label: '10% off (orders ₱800 and above)' }
};

function applyVoucher() {
    const input = document.getElementById('voucher-code');
    const message = document.getElementById('voucher-message');
    if (!input) return;

    const code = input.value.trim().toUpperCase();

    // Empty box = remove the voucher
    if (code === '') {
        localStorage.removeItem(VOUCHER_KEY);
        updateSummary();
        return;
    }

    // Code not in our list
    if (!VOUCHERS[code]) {
        localStorage.removeItem(VOUCHER_KEY);
        updateSummary();
        if (message) {
            message.textContent = 'Invalid voucher code.';
            message.className = 'voucher-message error';
        }
        return;
    }

    localStorage.setItem(VOUCHER_KEY, code);
    updateSummary();
}


// ========== 6. CALCULATE TOTALS ==========
function formatPeso(amount) {
    return '₱' + amount.toLocaleString();
}

// Safely write text into an element (does nothing if the element is missing)
function setText(id, text) {
    const element = document.getElementById(id);
    if (element) {
        element.textContent = text;
    } else {
        console.warn('Missing id in cart.php: ' + id);
    }
}

// NEW: does the math in ONE place, so the page and the order always agree
function calculateTotals() {
    const cart = getCart();

    let subtotal = 0;
    let itemCount = 0;

    cart.forEach(function (item) {
        subtotal += item.price * item.quantity;
        itemCount += item.quantity;
    });

    const code = localStorage.getItem(VOUCHER_KEY);
    const voucher = code ? VOUCHERS[code] : null;

    // The discount only applies if the subtotal reaches the voucher's minimum
    let discount = 0;
    if (voucher && subtotal >= voucher.minOrder) {
        discount = Math.round(subtotal * voucher.rate);
    }

    return {
        subtotal: subtotal,
        itemCount: itemCount,
        discount: discount,
        total: subtotal - discount,
        code: code,
        voucher: voucher
    };
}

function updateSummary() {
    const totals = calculateTotals();
    const message = document.getElementById('voucher-message');

    // The message under the voucher box
    if (message) {
        if (totals.voucher && totals.subtotal >= totals.voucher.minOrder) {
            message.textContent = '✔ Voucher applied: ' + totals.voucher.label;
            message.className = 'voucher-message success';
        } else if (totals.voucher) {
            message.textContent = 'Add ' + formatPeso(totals.voucher.minOrder - totals.subtotal) + ' more to use ' + totals.code + '.';
            message.className = 'voucher-message warning';
        } else {
            message.textContent = '';
            message.className = 'voucher-message';
        }
    }

    setText('item-count', '(' + totals.itemCount + (totals.itemCount === 1 ? ' item)' : ' items)'));
    setText('subtotal', formatPeso(totals.subtotal));
    setText('discount', '-' + formatPeso(totals.discount));
    setText('total', formatPeso(totals.total));
}


// ========== 7. ORDER DETAILS: PICKUP, PAYMENT, NOTE ==========
const DETAILS_KEY = 'dejabrewOrderDetails';

function saveOrderDetails() {
    const pickup = document.getElementById('pickup-time');
    const payment = document.getElementById('payment-method');
    const note = document.getElementById('barista-note');
    if (!pickup || !payment || !note) return;

    const details = {
        pickupTime: pickup.value,
        paymentMethod: payment.value,
        note: note.value
    };
    localStorage.setItem(DETAILS_KEY, JSON.stringify(details));
}

function loadOrderDetails() {
    const pickup = document.getElementById('pickup-time');
    const payment = document.getElementById('payment-method');
    const note = document.getElementById('barista-note');
    const voucherInput = document.getElementById('voucher-code');

    const saved = localStorage.getItem(DETAILS_KEY);
    if (saved && pickup && payment && note) {
        const details = JSON.parse(saved);
        pickup.value = details.pickupTime;
        payment.value = 'cash';   // cash is the only payment method
        note.value = details.note;
    }

    const savedCode = localStorage.getItem(VOUCHER_KEY);
    if (savedCode && voucherInput) {
        voucherInput.value = savedCode;
    }
}

document.addEventListener('input', function (event) {
    if (['pickup-time', 'payment-method', 'barista-note'].includes(event.target.id)) {
        saveOrderDetails();
    }
});

document.addEventListener('change', function (event) {
    if (['pickup-time', 'payment-method'].includes(event.target.id)) {
        saveOrderDetails();
    }
});


// ========== 8. PICKUP TIME RULES ==========
const STORE_OPEN = '11:00';    // 11:00 AM
const STORE_CLOSE = '23:00';   // 11:00 PM
const PREP_MINUTES = 15;       // the barista needs this long to prepare the order

// "14:30" -> 870 (minutes since midnight), so we can compare times
function timeToMinutes(timeText) {
    const parts = timeText.split(':');
    return Number(parts[0]) * 60 + Number(parts[1]);
}

// "14:30" -> "2:30 PM" (for friendly messages)
function formatTime(timeText) {
    const parts = timeText.split(':').map(Number);
    const ampm = parts[0] >= 12 ? 'PM' : 'AM';
    const hour12 = parts[0] % 12 || 12;
    return hour12 + ':' + String(parts[1]).padStart(2, '0') + ' ' + ampm;
}


// ========== 9. PLACE THE ORDER (NEW) ==========

// Until there is a login page, every order belongs to this sample customer.
// When your team adds login, replace these two values with the logged-in user.
const CURRENT_CUSTOMER = {
    name: 'Jane Doe',
    email: 'JaneDoe@gmail.com'
};

// Turns the cart into an order, saves it, and empties the cart.
// Returns the new order (or null if the cart is empty).
function placeOrder(pickupTime) {
    const cart = getCart();
    if (cart.length === 0) return null;

    const totals = calculateTotals();
    const noteField = document.getElementById('barista-note');

    const order = {
        id: getNextOrderId(),                       // from orders-data.js
        customerName: CURRENT_CUSTOMER.name,
        customerEmail: CURRENT_CUSTOMER.email,
        createdAt: new Date().toISOString(),
        pickupTime: pickupTime,
        status: 'pending',
        paymentMethod: 'Pay upon pickup',
        note: noteField ? noteField.value.trim() : '',
        voucherCode: totals.discount > 0 ? totals.code : '',
        items: cart.map(function (item) {
            return {
                name: item.name,
                size: item.size,
                sugar: item.sugar,
                price: item.price,
                quantity: item.quantity,
                image: item.image
            };
        }),
        subtotal: totals.subtotal,
        discount: totals.discount,
        total: totals.total
    };

    // Save the order where the staff page can see it
    const orders = getOrders();
    orders.push(order);
    saveOrders(orders);

    // Empty the cart and forget the voucher and the form
    saveCart([]);
    localStorage.removeItem(VOUCHER_KEY);
    localStorage.removeItem(DETAILS_KEY);

    ['pickup-time', 'barista-note', 'voucher-code'].forEach(function (id) {
        const field = document.getElementById(id);
        if (field) field.value = '';
    });

    return order;
}


// ========== 10. CHECKOUT BUTTON ==========
function handleCheckout() {
    const error = document.getElementById('checkout-error');
    const pickupField = document.getElementById('pickup-time');
    if (!pickupField) return;

    function showError(text) {
        if (error) {
            error.textContent = text;
        } else {
            alert(text);
        }
    }

    // Make sure orders-data.js was loaded in cart.php
    if (typeof saveOrders !== 'function' || typeof getNextOrderId !== 'function') {
        showError('Orders are not set up. Add js/orders-data.js before js/cart.js in cart.php.');
        return;
    }

    if (getCart().length === 0) {
        showError('Your cart is empty.');
        return;
    }

    const pickup = pickupField.value;

    // 0. Is the shop still accepting orders today?
    const rightNow = new Date();
    const rightNowMinutes = rightNow.getHours() * 60 + rightNow.getMinutes();
    if (rightNowMinutes + PREP_MINUTES > timeToMinutes(STORE_CLOSE)) {
        showError('Sorry, we are no longer accepting orders for today.');
        return;
    }

    // 1. A time must be chosen
    if (pickup === '') {
        showError('Please choose a pickup time.');
        return;
    }

    const chosen = timeToMinutes(pickup);

    // 2. It must be within store hours
    if (chosen < timeToMinutes(STORE_OPEN) || chosen > timeToMinutes(STORE_CLOSE)) {
        showError('Pickup time must be between ' + formatTime(STORE_OPEN) + ' and ' + formatTime(STORE_CLOSE) + '.');
        return;
    }

    // 3. It must be at least PREP_MINUTES from now
    if (chosen < rightNowMinutes + PREP_MINUTES) {
        showError('Please choose a time at least ' + PREP_MINUTES + ' minutes from now.');
        return;
    }

    if (error) error.textContent = '';

    // All good: create the order (NEW)
    const order = placeOrder(pickup);
    renderCart();   // the cart is empty now, so the Empty Cart design appears

    if (order) {
        alert('Order #' + order.id + ' placed! Pay in cash when you pick it up at ' + formatTime(pickup) + '.');
    }
}


// ========== 11. CART ICON BADGE ==========
// Shows the total number of drinks on the cart icon in the navbar
function updateCartCount() {
    const badge = document.getElementById('cart-count');
    if (!badge) return;

    let total = 0;
    getCart().forEach(function (item) {
        total += item.quantity;
    });

    badge.textContent = total;
    badge.hidden = (total === 0);
}


// ========== 12. DISPLAY THE CART ITEMS ==========
function renderCart() {
    updateCartCount();   // works on every page that has the navbar

    const container = document.getElementById('cart-items');
    if (!container) return;   // not the cart page, so stop here

    const cart = getCart();

    const emptySection = document.getElementById('empty-cart');
    const itemsSection = document.getElementById('cart-with-items');

    if (emptySection && itemsSection) {
        if (cart.length === 0) {
            emptySection.style.display = 'flex';
            itemsSection.style.display = 'none';
        } else {
            emptySection.style.display = 'none';
            itemsSection.style.display = 'grid';
        }
    }

    container.innerHTML = '';

    cart.forEach(function (item) {
        // Show size and sugar only if the item has them
        const options = item.size
            ? `<p class="item-options">${escapeHtml(item.size)} · ${escapeHtml(item.sugar)} sugar</p>`
            : '';

        container.innerHTML += `
            <div class="cart-item">
                <img src="${escapeHtml(item.image)}" alt="${escapeHtml(item.name)}">
                <div class="item-info">
                    <p class="item-name">${escapeHtml(item.name)}</p>
                    ${options}
                    <p class="item-price">₱${item.price}</p>
                </div>
                <div class="item-actions">
                    <div class="quantity-controls">
                        <button class="qty-btn" data-id="${escapeHtml(item.id)}" data-change="-1">−</button>
                        <span>${item.quantity}</span>
                        <button class="qty-btn" data-id="${escapeHtml(item.id)}" data-change="1">+</button>
                    </div>
                    <button class="remove-btn" data-id="${escapeHtml(item.id)}">Remove</button>
                </div>
            </div>
        `;
    });

    updateSummary();
}


// ========== 13. BUTTON CLICKS ==========
document.addEventListener('click', function (event) {

    // + and − buttons
    const qtyButton = event.target.closest('.qty-btn');
    if (qtyButton) {
        changeQuantity(qtyButton.dataset.id, Number(qtyButton.dataset.change));
        return;
    }

    // Remove buttons
    const removeButton = event.target.closest('.remove-btn');
    if (removeButton) {
        removeItem(removeButton.dataset.id);
        return;
    }

    // Apply voucher button
    if (event.target.closest('#apply-voucher-btn')) {
        applyVoucher();
        return;
    }

    // Proceed to Checkout button
    if (event.target.closest('#checkout-btn')) {
        handleCheckout();
    }
});


// ========== 14. WHEN THE PAGE LOADS ==========
loadOrderDetails();
renderCart();