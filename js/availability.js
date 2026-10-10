/* =====================================================
   availability.js - reads what staff saved in Menu Management
   (vanilla JavaScript)
 
   Staff page (js/staff-menu.js) saves this in localStorage:
       key:   "dejabrew_availability"
       value: {"1": true, "2": false}      (product id -> available?)
   A product that is not in the object yet counts as Available.
 
   ===================================================== */
 
const AVAILABILITY_KEY = 'dejabrew_availability';
 
function getAvailability() {
    try {
        return JSON.parse(localStorage.getItem(AVAILABILITY_KEY)) || {};
    } catch (error) {
        return {};   // storage empty or broken: everything is available
    }
}
 
// productId can be a number or text ("3"). Used by the home page AND by cart.js.
function isProductAvailable(productId) {
    return getAvailability()[String(productId)] !== false;
}
 
 
/* ---------- Home page only: update the cards and the banner ---------- */
 
function setCardAvailability(card, available) {
    const addButton = card.querySelector('.add-btn');
    const name = card.dataset.productName;
 
    card.classList.toggle('is-sold-out', !available);
    addButton.disabled = !available;
    addButton.setAttribute('aria-label', available ? 'Add ' + name + ' to cart' : name + ' is sold out');
 
    let badge = card.querySelector('.sold-out-badge');
 
    if (!available) {
        card.classList.remove('is-open');          // a sold-out card never expands
 
        if (!badge) {
            badge = document.createElement('span');
            badge.className = 'sold-out-badge';
            badge.textContent = 'Sold out';
            card.querySelector('.card-image').appendChild(badge);
        }
    } else if (badge) {
        badge.remove();
    }
}
 
function applyAvailability() {
    const data = getAvailability();
 
    // Product cards
    document.querySelectorAll('.product-card').forEach(function (card) {
        setCardAvailability(card, data[card.dataset.productId] !== false);
    });
 
    // New Series banner button
    document.querySelectorAll('.series-btn').forEach(function (button) {
        const available = data[button.dataset.productId] !== false;
 
        if (!button.dataset.label) button.dataset.label = button.textContent;   // remember "Add to Cart"
        button.textContent = available ? button.dataset.label : 'Sold Out';
        button.disabled = !available;
    });
}
 
if (document.querySelector('.product-card') || document.querySelector('.series-btn')) {
    applyAvailability();
 
    // Staff changed something in another tab of this browser: update right away.
    window.addEventListener('storage', function (event) {
        if (event.key === AVAILABILITY_KEY) applyAvailability();
    });
 
    // Coming back with the browser's Back button
    window.addEventListener('pageshow', applyAvailability);
}
 