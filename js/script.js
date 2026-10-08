/* =====================================================
   DejaBrew - script.js (vanilla JavaScript, no libraries)
 
   1. Helpers
   2. Hero cups (tap support for touch screens)
   3. Product card state: open (hover/tap) and flipped
   4. Cart
   5. Search + category filter
   ===================================================== */
 
 
/* ---------- 1. Helpers ---------- */
 
// True on phones/tablets where there is no mouse hover.
function isTouchDevice() {
    return window.matchMedia('(hover: none)').matches;
}
 
 
/* ---------- 2. Hero cups ---------- */
/*
 * The spotlight (bright cup, dim background) is pure CSS.
 * JavaScript only changes the big title to match the cup you point at.
 * Touch screens have no hover, so a tap lights the cup (class "is-lit").
 */
const heroCups  = document.querySelectorAll('.hero-cup');
const heroTitle = document.getElementById('hero-title');
 
// Fade the old title out, swap the text, fade the new one in.
function setHeroTitle(text) {
    if (heroTitle.textContent === text) return;
 
    heroTitle.classList.add('is-changing');          // CSS fades it out
    setTimeout(function () {
        heroTitle.textContent = text;
        heroTitle.classList.remove('is-changing');   // CSS fades it back in
    }, 180);
}
 
function resetHeroTitle() {
    setHeroTitle(heroTitle.dataset.default);
}
 
// Scroll to the product card that matches a hero cup, then flash it.
function goToProduct(productId) {
    const slot = document.getElementById('product-' + productId);
    if (!slot) return;
 
    // If a search/category filter is hiding the card, clear the filters first.
    if (slot.hidden) resetFilters();
 
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    slot.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'center' });
 
    // Restart the highlight animation (remove, force a reflow, add again).
    const card = slot.querySelector('.product-card');
    card.classList.remove('is-highlighted');
    void card.offsetWidth;
    card.classList.add('is-highlighted');
    setTimeout(function () { card.classList.remove('is-highlighted'); }, 2000);
}
 
heroCups.forEach(function (cup) {
    // Mouse
    cup.addEventListener('pointerenter', function (event) {
        if (event.pointerType === 'mouse') setHeroTitle(cup.dataset.title);
    });
    cup.addEventListener('pointerleave', function (event) {
        if (event.pointerType === 'mouse') resetHeroTitle();
    });
 
    // Keyboard
    cup.addEventListener('focus', function () {
        if (cup.matches(':focus-visible')) setHeroTitle(cup.dataset.title);
    });
    cup.addEventListener('blur', resetHeroTitle);
 
    // Click = go to this drink in the menu.
    // Touch screens: first tap lights the cup (no hover on touch), second tap goes to the menu.
    cup.addEventListener('click', function () {
        if (isTouchDevice() && !cup.classList.contains('is-lit')) {
            heroCups.forEach(function (c) { c.classList.remove('is-lit'); });
            cup.classList.add('is-lit');
            setHeroTitle(cup.dataset.title);
            return;
        }
 
        goToProduct(cup.dataset.productId);
    });
});
 
 
/* ---------- 3. Product card state ---------- */
/*
 * Every card has its own state, stored as CSS classes on that card only:
 *   .is-open     -> "hovered" look (Add to Cart + size + sugar)
 *   .is-flipped  -> showing the back
 * Because each class lives on ONE card element, cards never affect each other.
 */
const cards = document.querySelectorAll('.product-card');
 
function openCard(card) {
    // Flip wins over hover: a flipped card never shows front controls.
    if (card.classList.contains('is-flipped')) return;
    card.classList.add('is-open');
}
 
function closeCard(card) {
    card.classList.remove('is-open');
}
 
function closeAllCards(exceptCard) {
    cards.forEach(function (card) {
        if (card !== exceptCard) closeCard(card);
    });
}
 
function flipToBack(card) {
    card.classList.remove('is-open');   // clear the hover look first
    card.classList.add('is-flipped');
    card.querySelector('.back-btn').focus({ preventScroll: true });
}
 
function flipToFront(card) {
    card.classList.remove('is-flipped');
    if (card.matches(':hover')) openCard(card);  // mouse is still on it
    card.querySelector('.info-btn').focus({ preventScroll: true });
}
 
// Mouse + keyboard "hover": each card listens for its own events.
cards.forEach(function (card) {
 
    // pointerType === 'mouse' ignores touch, which is handled by the click code below
    card.addEventListener('pointerenter', function (event) {
        if (event.pointerType === 'mouse') openCard(card);
    });
 
    card.addEventListener('pointerleave', function (event) {
        if (event.pointerType === 'mouse') closeCard(card);
    });
 
    // Keyboard users: tabbing into the card opens it too.
    card.addEventListener('focusin', function (event) {
        if (event.target.matches(':focus-visible')) openCard(card);
    });
 
    card.addEventListener('focusout', function (event) {
        const stillInside = card.contains(event.relatedTarget);
        if (!stillInside && !card.matches(':hover')) closeCard(card);
    });
});
 
/*
 * One click listener on the grid handles every button (this is called
 * "event delegation"). event.target.closest(...) tells us what was clicked.
 * Each branch ends with "return", so clicking the info button can never
 * fall through to the add-to-cart code.
 */
document.addEventListener('click', function (event) {
    const card = event.target.closest('.product-card');
 
    // Tapped outside every card on a touch screen: close any open card.
    if (!card) {
        if (isTouchDevice()) closeAllCards(null);
        return;
    }
 
    // 1) Info button -> flip to the back
    if (event.target.closest('.info-btn')) {
        flipToBack(card);
        return;
    }
 
    // 2) Back button -> flip to the front
    if (event.target.closest('.back-btn')) {
        flipToFront(card);
        return;
    }
 
    // 3) Add button
    if (event.target.closest('.add-btn')) {
        // On touch, the first tap only reveals size + sugar; the next tap adds.
        if (isTouchDevice() && !card.classList.contains('is-open')) {
            closeAllCards(card);
            openCard(card);
            return;
        }
        // addToCart(card);
        addCardToCart(card);
        return;
    }
 
    // 4) Using the dropdowns should not toggle the card.
    if (event.target.closest('select, label')) return;
 
    // 5) Touch only: tapping the card toggles the customization controls.
    if (isTouchDevice() && !card.classList.contains('is-flipped')) {
        if (card.classList.contains('is-open')) {
            closeCard(card);
        } else {
            closeAllCards(card);
            openCard(card);
        }
    }
});
 
 
/* ---------- 4. Cart ---------- */
// A simple array. Each item looks like:
// { productId: 1, productName: "Classic Iced Coffee", size: "Medium", sugar: "50%" }
// const cart = [];
 
// const cartCount = document.getElementById('cart-count');
// const toast     = document.getElementById('toast');
// let toastTimer;
 
// function addToCart(card) {
//     // Read the values chosen on THIS card only.
//     const item = {
//         productId:   Number(card.dataset.productId),
//         productName: card.dataset.productName,
//         size:        card.querySelector('.size-select').value,
//         sugar:       card.querySelector('.sugar-select').value
//     };
 
//     cart.push(item);
//     console.log('Added to cart:', item);
//     console.log('Cart now:', cart);
 
//     updateCartBadge();
//     showToast(item.productName + ' added to cart');
// }
 
// function updateCartBadge() {
//     cartCount.textContent = cart.length;
//     cartCount.hidden = cart.length === 0;
// }
 
// function showToast(message) {
//     toast.textContent = message;
//     toast.classList.add('is-visible');
 
//     clearTimeout(toastTimer);
//     toastTimer = setTimeout(function () {
//         toast.classList.remove('is-visible');
//     }, 2000);
// }
/* ---------- 4. Cart ---------- */
// The cart itself lives in cart.js (saved in localStorage) so the cart page can read it.
// Here we only collect the choices from the card and call addToCart() from cart.js.
const toast = document.getElementById('toast');
let toastTimer;

function addCardToCart(card) {
    const sizeSelect  = card.querySelector('.size-select');
    const sugarSelect = card.querySelector('.sugar-select');

    const size  = sizeSelect.options[sizeSelect.selectedIndex].text.trim();   // e.g. "Medium 16oz"
    const sugar = sugarSelect.value;                                          // e.g. "50%"

    addToCart({   // this function comes from cart.js
        id:    card.dataset.productId + '|' + size + '|' + sugar,   // same drink + same options = same item
        name:  card.dataset.productName,
        price: Number(card.dataset.price),
        image: card.dataset.image,
        size:  size,
        sugar: sugar
    });

    showToast(card.dataset.productName + ' added to cart');
}

function showToast(message) {
    toast.textContent = message;
    toast.classList.add('is-visible');

    clearTimeout(toastTimer);
    toastTimer = setTimeout(function () {
        toast.classList.remove('is-visible');
    }, 2000);
}
 
 
/* ---------- 5. Search + category filter ---------- */
const searchInput   = document.getElementById('search-input');
const filterButtons = document.querySelectorAll('.filter-btn');
const slots         = document.querySelectorAll('.product-slot');
const noResults     = document.getElementById('no-results');
 
let activeCategory = 'all';
 
function applyFilters() {
    const searchText = searchInput.value.trim().toLowerCase();
    let visibleCount = 0;
 
    slots.forEach(function (slot) {
        const matchesCategory = activeCategory === 'all' || slot.dataset.category === activeCategory;
        const matchesSearch   = slot.dataset.name.toLowerCase().includes(searchText);
 
        slot.hidden = !(matchesCategory && matchesSearch);
        if (!slot.hidden) visibleCount++;
    });
 
    noResults.hidden = visibleCount > 0;
}
 
searchInput.addEventListener('input', applyFilters);
 
filterButtons.forEach(function (button) {
    button.addEventListener('click', function () {
        activeCategory = button.dataset.category;
 
        filterButtons.forEach(function (b) { b.classList.remove('is-active'); });
        button.classList.add('is-active');
 
        applyFilters();
    });
});
 
 
// Clears the search box and goes back to the "All" category.
function resetFilters() {
    searchInput.value = '';
    activeCategory = 'all';
 
    filterButtons.forEach(function (button) {
        button.classList.toggle('is-active', button.dataset.category === 'all');
    });
 
    applyFilters();
}
 