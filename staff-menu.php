<?php
// Staff Side - Menu Management
require_once __DIR__ . '/includes/functions.php';   // escape() + session
require_once __DIR__ . '/includes/products.php';    // $products array

requireRole('staff');

// Category slug (galing sa products.php) -> label na ipapakita
$categoryLabels = [
    'iced-coffee' => 'Iced Coffee',
    'hot-coffee' => 'Hot Coffee',
    'frappe' => 'Frappe',
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DejaBrew | Menu Management</title>
    <link rel="icon" type="image/png" href="assets/db-logo.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Poppins:wght@400;500;600&display=swap"
        rel="stylesheet">

    <!-- Shared staff layout (top bar, sidebar) is from staff-orders.css -->
    <link rel="stylesheet" href="css/staff-orders.css">
    <link rel="stylesheet" href="css/staff-menu.css">
</head>

<body class="staff-body">

    <div class="staff-shell">

        <?php include __DIR__ . '/includes/staff-sidebar.php'; ?>

        <main class="staff-main">

            <section class="menu-section">

                <div class="menu-heading">
                    <h1>Menu Management</h1>
                    <p class="menu-subtitle">View and manage menu availability</p>
                </div>

                <div class="menu-card">

                    <!-- Search + category filter -->
                    <div class="menu-toolbar">
                        <div class="menu-search">
                            <label for="menu-search" class="visually-hidden">Search products</label>
                            <input type="search" id="menu-search" placeholder="Search Products">
                        </div>

                        <div class="menu-filters" role="group" aria-label="Filter products by category">
                            <button type="button" class="menu-pill is-active" data-category="all">All</button>
                            <button type="button" class="menu-pill" data-category="iced-coffee">Iced Coffee</button>
                            <button type="button" class="menu-pill" data-category="hot-coffee">Hot Coffee</button>
                            <button type="button" class="menu-pill" data-category="frappe">Frappe</button>
                        </div>
                    </div>

                    <!-- Column titles -->
                    <div class="menu-table-head">
                        <span>Product</span>
                        <span>Category</span>
                        <span>Price</span>
                        <span class="head-avail">Availability</span>
                    </div>

                    <!-- Product rows (PHP builds one row per product) -->
                    <div id="menu-list">
                        <?php foreach ($products as $product): ?>
                            <?php
                            $id = (int) $product['id'];
                            $fullName = $product['prefix'] . ' ' . $product['name'];
                            $catLabel = $categoryLabels[$product['category']] ?? $product['category'];
                            ?>
                            <div class="menu-item" data-id="<?= $id ?>" data-category="<?= escape($product['category']) ?>"
                                data-name="<?= escape(strtolower($fullName)) ?>" data-available="true">

                                <button type="button" class="menu-row" aria-expanded="false"
                                    aria-controls="detail-<?= $id ?>">
                                    <span class="cell-product">
                                        <img src="<?= escape($product['image']) ?>" alt="">
                                        <span class="product-name">
                                            <?= escape($fullName) ?>
                                        </span>
                                    </span>
                                    <span class="cell-category">
                                        <?= escape($catLabel) ?>
                                    </span>
                                    <span class="cell-price">P
                                        <?= (int) $product['price'] ?>
                                    </span>
                                    <span class="cell-avail">
                                        <span class="status-text">Available</span>
                                        <span class="status-dot"></span>
                                        <svg class="row-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                            stroke-linejoin="round" aria-hidden="true">
                                            <polyline points="6 9 12 15 18 9"></polyline>
                                        </svg>
                                    </span>
                                </button>

                                <!-- Expanded part (hidden until the row is clicked) -->
                                <div class="menu-detail" id="detail-<?= $id ?>" hidden>
                                    <p class="detail-desc">
                                        <?= escape($product['description']) ?>
                                    </p>

                                    <label class="switch">
                                        <input type="checkbox" class="availability-toggle" checked>
                                        <span class="slider"></span>
                                        <span class="switch-label">Available</span>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <p class="menu-empty" id="menu-empty" hidden>No products found.</p>
                </div>
            </section>

        </main>
    </div>

    <script src="js/staff-menu.js"></script>
</body>

</html>