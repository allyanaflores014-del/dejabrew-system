<?php
/*
 * notifications.php
 * -----------------
 * Two small popup messages for the home page. js/notifications.js shows them
 * one at a time. Needs $products (from products.php) to find the lowest price.
 */
$lowestPrice = min(array_column($products, 'price'));
?>
<div class="notif-stack" aria-live="polite">

    <!-- Promo notification -->
    <div class="notif" role="status" hidden>
        <span class="notif-icon" aria-hidden="true">
            <!-- coffee beans -->
            <svg width="26" height="26" viewBox="0 0 28 28" fill="currentColor">
                <g transform="rotate(-40 10 10)">
                    <ellipse cx="10" cy="10" rx="4.2" ry="6.2" />
                    <path d="M10 3.9C8 8 12 12 10 16.1" fill="none" stroke-width="1.4"
                        style="stroke: var(--notif-icon-bg)" />
                </g>
                <g transform="rotate(25 19 12)">
                    <ellipse cx="19" cy="12" rx="4" ry="5.8" />
                    <path d="M19 6.3C17 10 21 14 19 17.7" fill="none" stroke-width="1.4"
                        style="stroke: var(--notif-icon-bg)" />
                </g>
                <g transform="rotate(-70 12 21)">
                    <ellipse cx="12" cy="21" rx="4" ry="5.6" />
                    <path d="M12 15.6C10 19 14 23 12 26.4" fill="none" stroke-width="1.4"
                        style="stroke: var(--notif-icon-bg)" />
                </g>
            </svg>
        </span>
        <div class="notif-body">
            <p class="notif-title">A little déjà brew never hurt.</p>
            <p class="notif-text">Get <strong>5% OFF</strong> when you spend <strong>₱200</strong> or more.</p>
        </div>
        <button type="button" class="notif-close" aria-label="Dismiss notification">
            <svg width="9" height="9" viewBox="0 0 10 10" fill="none" stroke="currentColor" stroke-width="1.6"
                stroke-linecap="round" aria-hidden="true">
                <path d="M1 1l8 8M9 1L1 9" />
            </svg>
        </button>
    </div>

    <!-- Price notification -->
    <div class="notif" role="status" hidden>
        <span class="notif-icon" aria-hidden="true">
            <!-- coffee cup -->
            <svg width="26" height="26" viewBox="0 0 28 28" fill="none" stroke="currentColor" stroke-width="1.8"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M10 5c-1 1.2 1 2 0 3.2M14 4c-1 1.2 1 2 0 3.2M18 5c-1 1.2 1 2 0 3.2" />
                <path d="M6 12h14v4a6 6 0 0 1-6 6h-2a6 6 0 0 1-6-6z" />
                <path d="M20 13.5h1.5a2.5 2.5 0 0 1 0 5H19.5" />
                <path d="M7 24.5h12" />
            </svg>
        </span>
        <div class="notif-body">
            <p class="notif-title">Brewed to feel familiar.</p>
            <p class="notif-text">Price starts at <strong>₱
                    <?= number_format((int) $lowestPrice, 2) ?>
                </strong> only!</p>
        </div>
        <button type="button" class="notif-close" aria-label="Dismiss notification">
            <svg width="9" height="9" viewBox="0 0 10 10" fill="none" stroke="currentColor" stroke-width="1.6"
                stroke-linecap="round" aria-hidden="true">
                <path d="M1 1l8 8M9 1L1 9" />
            </svg>
        </button>
    </div>

</div>