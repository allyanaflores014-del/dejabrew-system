// =====================================================
// DejaBrew - staff-menu.js  (Menu Management)
// 1. Saving availability (localStorage)
// 2. Showing the status of each product
// 3. Expand / collapse a row
// 4. Search + category filter
// 5. Sidebar fix
// =====================================================

// ---------- 1. Saving availability ----------
// Saved like this: {"1": true, "2": false}. A product that is not
// in the object yet counts as Available.
var STORAGE_KEY = 'dejabrew_availability';

function loadAvailability() {
    try {
        return JSON.parse(localStorage.getItem(STORAGE_KEY)) || {};
    } catch (error) {
        return {};   // storage empty or broken: start fresh
    }
}

function saveAvailability(data) {
    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
    } catch (error) {
        alert('Could not save the change in this browser.');
    }
}

var availability = loadAvailability();


// ---------- 2. Showing the status of each product ----------
var items = document.querySelectorAll('.menu-item');

// Updates one row (text, dot color, and the toggle) to match its status.
function showStatus(item, isAvailable) {
    var text = isAvailable ? 'Available' : 'Not Available';

    item.dataset.available = isAvailable ? 'true' : 'false';   // CSS uses this for the dot color
    item.querySelector('.status-text').textContent = text;
    item.querySelector('.switch-label').textContent = text;
    item.querySelector('.availability-toggle').checked = isAvailable;
}

items.forEach(function (item) {
    var id = item.dataset.id;
    var isAvailable = availability[id] !== false;   // default: Available
    showStatus(item, isAvailable);

    // When staff flips the switch: update the row and save it
    item.querySelector('.availability-toggle').addEventListener('change', function (event) {
        var nowAvailable = event.target.checked;
        availability[id] = nowAvailable;
        saveAvailability(availability);
        showStatus(item, nowAvailable);
    });
});


// ---------- 3. Expand / collapse a row ----------
items.forEach(function (item) {
    var row = item.querySelector('.menu-row');
    var detail = item.querySelector('.menu-detail');

    row.addEventListener('click', function () {
        var willOpen = detail.hidden;   // currently closed -> open it

        // Close every row first, so only one is open at a time
        items.forEach(function (other) {
            other.classList.remove('is-open');
            other.querySelector('.menu-detail').hidden = true;
            other.querySelector('.menu-row').setAttribute('aria-expanded', 'false');
        });

        if (willOpen) {
            item.classList.add('is-open');
            detail.hidden = false;
            row.setAttribute('aria-expanded', 'true');
        }
    });
});


// ---------- 4. Search + category filter ----------
var searchInput = document.getElementById('menu-search');
var pills = document.querySelectorAll('.menu-pill');
var emptyMessage = document.getElementById('menu-empty');
var activeCategory = 'all';

function applyFilters() {
    var term = searchInput.value.trim().toLowerCase();
    var shown = 0;

    items.forEach(function (item) {
        var matchesCategory = activeCategory === 'all' || item.dataset.category === activeCategory;
        var matchesSearch = item.dataset.name.indexOf(term) !== -1;
        var visible = matchesCategory && matchesSearch;

        item.hidden = !visible;
        if (visible) {
            shown++;
        }
    });

    emptyMessage.hidden = shown !== 0;
}

searchInput.addEventListener('input', applyFilters);

pills.forEach(function (pill) {
    pill.addEventListener('click', function () {
        pills.forEach(function (p) { p.classList.remove('is-active'); });
        pill.classList.add('is-active');
        activeCategory = pill.dataset.category;
        applyFilters();
    });
});


// ---------- 5. Sidebar fix ----------
// staff-sidebar.php always marks "Orders" as active. On this page,
// highlight "Menu" instead (without editing the shared sidebar file).
document.querySelectorAll('.staff-nav-link').forEach(function (link) {
    link.classList.toggle('is-active', link.getAttribute('href') === 'staff-menu.php');
});