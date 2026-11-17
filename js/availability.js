/* =====================================================
   availability.js - asks the server which drinks are available.
   The data comes from availability.php (the database), which staff
   changes in Menu Management.
   ===================================================== */

// null = not loaded yet (everything counts as available until it loads)
var availabilityData = null;

// productId can be a number or text ("3"). Used by the home page AND by cart.js.
// A product that is missing from the list was deleted, so it is not available.
function isProductAvailable(productId) {
    if (availabilityData === null) return true;
    return availabilityData[String(productId)] === true;
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
    document.querySelectorAll('.product-card').forEach(function (card) {
        setCardAvailability(card, isProductAvailable(card.dataset.productId));
    });

    document.querySelectorAll('.series-btn').forEach(function (button) {
        const available = isProductAvailable(button.dataset.productId);

        if (!button.dataset.label) button.dataset.label = button.textContent;   // remember "Add to Cart"
        button.textContent = available ? button.dataset.label : 'Sold Out';
        button.disabled = !available;
    });
}

function loadAvailability() {
    fetch('availability.php', { cache: 'no-store' })
        .then(function (response) { return response.json(); })
        .then(function (data) {
            availabilityData = data;

            if (document.querySelector('.product-card') || document.querySelector('.series-btn')) {
                applyAvailability();
            }
            // cart.js: redraw the cart so removed / sold-out items are marked
            if (typeof renderCart === 'function') renderCart();
        })
        .catch(function () { /* server not reachable: keep what the page already shows */ });
}

loadAvailability();

// Coming back with the Back button, or switching back to this tab
window.addEventListener('pageshow', function (event) { if (event.persisted) loadAvailability(); });
document.addEventListener('visibilitychange', function () {
    if (document.visibilityState === 'visible') loadAvailability();
});