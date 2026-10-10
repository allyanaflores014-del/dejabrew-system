<?php
/*
 * index.php
 */
require __DIR__ . '/includes/products.php';

// Short helper: makes text safe to print inside HTML (prevents broken markup / XSS).
function e(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

// Dropdown options. value = what gets saved in the cart, label = what the user reads.
$sizes = [
    'Small' => ['label' => 'Small 12oz', 'extra' => 0],
    'Medium' => ['label' => 'Medium 16oz', 'extra' => 15],
    'Large' => ['label' => 'Large 20oz', 'extra' => 30],
];
$sugarLevels = ['0%', '25%', '50%', '75%', '100%'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DejaBrew | Menu</title>
    <link rel="icon" type="image/png" href="assets/db-logo.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Allura&family=Playfair+Display:wght@600&family=Poppins:wght@400;500;600&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/scroll-story.css">
    <link rel="stylesheet" href="css/notifications.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="css/availability.css">

</head>

<body>

    <?php include __DIR__ . '/includes/header.php'; ?>

    <main>
        <?php include __DIR__ . '/includes/hero.php'; ?>

        <section class="menu" id="menu" aria-labelledby="menu-heading">
            <h2 class="menu-title" id="menu-heading">Menu</h2>

            <!-- Search + category filters -->
            <div class="menu-toolbar">
                <div class="search">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        aria-hidden="true">
                        <circle cx="11" cy="11" r="7" />
                        <path d="M20 20l-3.5-3.5" stroke-linecap="round" />
                    </svg>
                    <label for="search-input" class="visually-hidden">Search products</label>
                    <input type="search" id="search-input" placeholder="Search products">
                </div>

                <div class="filters" role="group" aria-label="Filter by category">
                    <button type="button" class="filter-btn is-active" data-category="all">All</button>
                    <button type="button" class="filter-btn" data-category="iced-coffee">Iced Coffee</button>
                    <button type="button" class="filter-btn" data-category="hot-coffee">Hot Coffee</button>
                    <button type="button" class="filter-btn" data-category="frappe">Frappe</button>
                </div>
            </div>

            <!-- Product grid: one card per product from products.php -->
            <div class="product-grid" id="product-grid">
                <?php foreach ($products as $product): ?>
                    <?php include __DIR__ . '/includes/product-card.php'; ?>
                <?php endforeach; ?>
            </div>

            <p class="no-results" id="no-results" hidden>No drinks match your search. Try another name or category.</p>
        </section>

        <section class="coffee-break" aria-labelledby="coffee-break-heading">
            <h2 class="coffee-break-title" id="coffee-break-heading">Made for Your Coffee Break</h2>
            <p class="coffee-break-text">Good coffee doesn't need a special occasion. Just a good moment.</p>
        </section>

        <!-- NEW SCROLL STORY SECTION -->
        <?php include __DIR__ . '/includes/scroll-story.php'; ?>
        <?php include __DIR__ . '/includes/new-series.php'; ?>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>

    <!-- Small message that appears when something is added to the cart -->
    <div class="toast" id="toast" role="status" aria-live="polite"></div>
    <?php include __DIR__ . '/includes/notifications.php'; ?>

    <script src="js/availability.js"></script>
    <script src="js/cart.js"></script>
    <script src="js/script.js"></script>
    <script src="js/scroll-story.js"></script>
    <script src="js/notifications.js"></script>
</body>

</html>