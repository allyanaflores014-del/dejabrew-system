// navbar-mobile.js
// Opens and closes the hamburger menu of the navbar on phones.
// Loaded by includes/header.php, so it works on every page that has the main navbar.

(function () {
    "use strict";

    var toggle = document.getElementById("nav-toggle");
    var nav = document.getElementById("main-nav");

    if (!toggle || !nav) {
        return;
    }

    // Opens or closes the menu and updates the button for screen readers.
    function setOpen(open) {
        nav.classList.toggle("is-open", open);
        toggle.setAttribute("aria-expanded", open ? "true" : "false");
        toggle.setAttribute("aria-label", open ? "Close menu" : "Open menu");
    }

    function isOpen() {
        return nav.classList.contains("is-open");
    }

    // Tapping the button opens or closes the menu.
    toggle.addEventListener("click", function () {
        setOpen(!isOpen());
    });

    // Tapping a link or anywhere outside the menu closes it.
    document.addEventListener("click", function (event) {
        if (!isOpen() || toggle.contains(event.target)) {
            return;
        }
        if (!nav.contains(event.target) || event.target.closest("a")) {
            setOpen(false);
        }
    });

    // Escape closes the menu and gives the focus back to the button.
    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape" && isOpen()) {
            setOpen(false);
            toggle.focus();
        }
    });

    // If the window becomes wide (rotate the phone, resize), close the menu.
    var wide = window.matchMedia("(min-width: 601px)");
    wide.addEventListener("change", function (event) {
        if (event.matches) {
            setOpen(false);
        }
    });

    // Show a small dot on the button while the cart has items.
    // cart.js shows or hides the number badge, so we watch it.
    var badge = document.getElementById("cart-count");

    function updateDot() {
        var hasItems = badge && !badge.hidden && Number(badge.textContent) > 0;
        toggle.classList.toggle("has-items", Boolean(hasItems));
    }

    if (badge) {
        updateDot();
        new MutationObserver(updateDot).observe(badge, {
            attributes: true,
            childList: true,
            characterData: true,
            subtree: true
        });
    }
})();