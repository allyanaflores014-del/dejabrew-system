<?php
// Shared staff sidebar (used by staff-orders.php and staff-menu.php)
require_once __DIR__ . '/config.php';   // makes sure the session is started

$currentPage = basename($_SERVER['SCRIPT_NAME']);
$staffName = $_SESSION['user']['username'] ?? 'Staff';
$staffEmail = $_SESSION['user']['email'] ?? '';
?>
<!-- SIDEBAR (dark) -->
<aside class="staff-sidebar">

    <nav class="staff-nav" aria-label="Staff navigation">
        <a href="staff-orders.php" class="staff-nav-link<?= $currentPage === 'staff-orders.php' ? ' is-active' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path>
                <polyline points="14 3 14 9 20 9"></polyline>
                <line x1="8" y1="13" x2="16" y2="13"></line>
                <line x1="8" y1="17" x2="14" y2="17"></line>
            </svg>
            <span>Orders</span>
        </a>

        <a href="staff-menu.php" class="staff-nav-link<?= $currentPage === 'staff-menu.php' ? ' is-active' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
            </svg>
            <span>Menu</span>
        </a>
    </nav>

    <!-- Staff account (bottom). Click it to show the Logout button. -->
    <div class="staff-account" id="staff-account">
        <div class="staff-logout-pop" id="staff-logout-pop" hidden>
            <a href="logout.php" class="staff-logout-btn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16 17 21 12 16 7"></polyline>
                    <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
                Logout
            </a>
        </div>

        <button type="button" class="staff-account-btn" id="staff-account-btn" aria-expanded="false"
            aria-controls="staff-logout-pop">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="10"></circle>
                <circle cx="12" cy="10" r="3"></circle>
                <path d="M6.2 18.5a7 7 0 0 1 11.6 0"></path>
            </svg>
            <span class="staff-account-text">
                <span class="staff-account-name"><?= htmlspecialchars($staffName, ENT_QUOTES, 'UTF-8') ?></span>
                <span class="staff-account-email"><?= htmlspecialchars($staffEmail, ENT_QUOTES, 'UTF-8') ?></span>
            </span>
        </button>
    </div>

</aside>

<style>
    .staff-account {
        position: relative;
        display: block;
    }

    .staff-account-btn {
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
        padding: 8px;
        border: none;
        border-radius: 10px;
        background: none;
        color: inherit;
        font: inherit;
        text-align: left;
        cursor: pointer;
    }

    .staff-account-btn:hover,
    .staff-account-btn[aria-expanded="true"] {
        background: rgba(255, 255, 255, 0.1);
    }

    .staff-account-text {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .staff-account-name {
        font-size: 0.8rem;
        font-weight: 500;
    }

    .staff-account-email {
        font-size: 0.7rem;
        opacity: 0.7;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .staff-logout-pop {
        position: absolute;
        left: 0;
        right: 0;
        bottom: calc(100% + 8px);
        z-index: 20;
    }

    .staff-logout-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px;
        border-radius: 10px;
        background: var(--caramel);
        color: var(--white);
        font-size: 0.85rem;
        font-weight: 500;
    }

    .staff-logout-btn:hover {
        background: var(--caramel-dark);
    }
</style>

<script>
    (function () {
        var button = document.getElementById('staff-account-btn');
        var popup = document.getElementById('staff-logout-pop');

        function setOpen(open) {
            popup.hidden = !open;
            button.setAttribute('aria-expanded', open ? 'true' : 'false');
        }

        button.addEventListener('click', function (event) {
            event.stopPropagation();
            setOpen(popup.hidden);
        });
        document.addEventListener('click', function (event) {
            if (!popup.hidden && !popup.contains(event.target)) setOpen(false);
        });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') setOpen(false);
        });
    })();
</script>