<?php
// Staff Side - Menu Management (data comes from the database)
require_once __DIR__ . '/includes/functions.php';   // escape() + session
require_once __DIR__ . '/includes/products.php';    // $products and $categories

requireRole('staff');

// Token that every save/delete request must send back (blocks fake requests)
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf" content="<?= escape($_SESSION['csrf']) ?>">
    <title>DejaBrew | Menu Management</title>
    <link rel="icon" type="image/png" href="assets/db-logo.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Poppins:wght@400;500;600&display=swap"
        rel="stylesheet">

    <!-- Shared staff layout (sidebar, colors) is from staff-orders.css -->
    <link rel="stylesheet" href="css/staff-orders.css">
    <link rel="stylesheet" href="css/staff-menu.css">
</head>

<body class="staff-body">

    <div class="staff-shell">

        <?php include __DIR__ . '/includes/staff-sidebar.php'; ?>

        <main class="staff-main">

            <section class="menu-section">

                <div class="menu-heading">
                    <div>
                        <h1>Menu Management</h1>
                        <p class="menu-subtitle">View and manage menu availability</p>
                    </div>
                    <button type="button" class="btn-outline" id="add-product-btn">+ Add Menu</button>
                </div>

                <div class="menu-card">

                    <!-- LEFT: search + product list -->
                    <div class="menu-products">

                        <div class="menu-toolbar">
                            <div class="menu-search">
                                <label for="menu-search" class="visually-hidden">Search products</label>
                                <input type="search" id="menu-search" placeholder="Search Products">
                            </div>
                        </div>

                        <div class="menu-table-head">
                            <span>Product</span>
                            <span>Category</span>
                            <span>Price</span>
                            <span class="head-avail">Availability</span>
                        </div>

                        <div id="menu-list">
                            <?php foreach ($products as $product): ?>
                                <?php $id = (int) $product['id']; ?>
                                <div class="menu-item" data-id="<?= $id ?>"
                                    data-category="<?= escape($product['category']) ?>"
                                    data-name="<?= escape(strtolower($product['full_name'])) ?>"
                                    data-full-name="<?= escape($product['full_name']) ?>"
                                    data-description="<?= escape($product['description']) ?>"
                                    data-price="<?= (int) $product['price'] ?>"
                                    data-image="<?= escape($product['image']) ?>"
                                    data-available="<?= $product['available'] ? 'true' : 'false' ?>">

                                    <button type="button" class="menu-row" aria-expanded="false"
                                        aria-controls="detail-<?= $id ?>">
                                        <span class="cell-product">
                                            <img src="<?= escape($product['image']) ?>" alt="">
                                            <span class="product-name"><?= escape($product['full_name']) ?></span>
                                        </span>
                                        <span class="cell-category"><?= escape($product['category_name']) ?></span>
                                        <span class="cell-price">P<?= (int) $product['price'] ?></span>
                                        <span class="cell-avail">
                                            <span class="status-text"><?= $product['available'] ? 'Available' : 'Not Available' ?></span>
                                            <span class="status-dot"></span>
                                            <svg class="row-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                                stroke-linejoin="round" aria-hidden="true">
                                                <polyline points="6 9 12 15 18 9"></polyline>
                                            </svg>
                                        </span>
                                    </button>

                                    <!-- Expanded part -->
                                    <div class="menu-detail" id="detail-<?= $id ?>" hidden>
                                        <p class="detail-desc"><?= escape($product['description']) ?></p>

                                        <div class="detail-actions">
                                            <label class="switch">
                                                <input type="checkbox" class="availability-toggle"
                                                    <?= $product['available'] ? 'checked' : '' ?>>
                                                <span class="slider"></span>
                                                <span class="switch-label"><?= $product['available'] ? 'Available' : 'Not Available' ?></span>
                                            </label>
                                            <button type="button" class="btn-small btn-edit">Edit</button>
                                            <button type="button" class="btn-small btn-delete">Delete</button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <p class="menu-empty" id="menu-empty" <?= count($products) > 0 ? 'hidden' : '' ?>>No products found.</p>
                    </div>

                    <!-- RIGHT: categories -->
                    <aside class="menu-categories" aria-label="Categories">
                        <h2 class="cat-title">Categories</h2>

                        <div id="category-list">
                            <?php foreach ($categories as $category): ?>
                                <div class="cat-item" data-id="<?= (int) $category['id'] ?>"
                                    data-name="<?= escape($category['name']) ?>">
                                    <button type="button" class="cat-name"><?= escape($category['name']) ?></button>
                                    <button type="button" class="cat-action cat-rename"
                                        aria-label="Rename <?= escape($category['name']) ?>">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M12 20h9"></path>
                                            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"></path>
                                        </svg>
                                    </button>
                                    <button type="button" class="cat-action cat-delete"
                                        aria-label="Delete <?= escape($category['name']) ?>">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path>
                                            <path d="M10 11v6M14 11v6"></path>
                                            <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"></path>
                                        </svg>
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <button type="button" class="btn-add-category" id="add-category-btn">+ Add Categories</button>
                    </aside>

                </div>
            </section>

        </main>
    </div>

    <!-- ========== Add / Edit product ========== -->
    <dialog id="product-dialog" aria-labelledby="product-title">
        <form id="product-form" class="dialog-form" novalidate>
            <h2 id="product-title">Add Product</h2>
            <input type="hidden" name="product_id" id="product-id">

            <div class="field">
                <label for="product-image">Product image</label>
                <div class="image-field">
                    <div class="image-preview">
                        <img id="product-preview" alt="" hidden>
                        <span id="preview-empty">No image</span>
                    </div>
                    <input type="file" name="image" id="product-image" accept="image/jpeg,image/png,image/webp">
                </div>
                <small class="field-hint">JPG, PNG or WEBP, up to 2 MB.</small>
            </div>

            <div class="field">
                <label for="product-name">Product name</label>
                <input type="text" name="name" id="product-name" maxlength="60" required>
            </div>

            <div class="field">
                <label for="product-category">Category</label>
                <select name="category_id" id="product-category" required>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= (int) $category['id'] ?>"><?= escape($category['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field">
                <label for="product-description">Description</label>
                <textarea name="description" id="product-description" rows="3" maxlength="500" required></textarea>
            </div>

            <div class="field">
                <label for="product-price">Regular price (Small, in pesos)</label>
                <input type="number" name="price" id="product-price" min="1" max="9999" step="1" required>
                <small class="field-hint">Medium is +P15 and Large is +P30. These are set automatically.</small>
            </div>

            <p class="form-error" id="product-error" hidden></p>

            <div class="dialog-buttons">
                <button type="button" class="btn-secondary" id="product-cancel">Cancel</button>
                <button type="submit" class="btn-primary" id="product-save">Save</button>
            </div>
        </form>
    </dialog>

    <!-- ========== Add / Rename category ========== -->
    <dialog id="category-dialog" aria-labelledby="category-title">
        <form id="category-form" class="dialog-form" novalidate>
            <h2 id="category-title">Add Category</h2>
            <input type="hidden" id="category-id">

            <div class="field">
                <label for="category-name">Category name</label>
                <input type="text" id="category-name" maxlength="30" required>
            </div>

            <p class="form-error" id="category-error" hidden></p>

            <div class="dialog-buttons">
                <button type="button" class="btn-secondary" id="category-cancel">Cancel</button>
                <button type="submit" class="btn-primary" id="category-save">Save</button>
            </div>
        </form>
    </dialog>

    <!-- ========== Confirm ========== -->
    <dialog id="confirm-dialog" aria-labelledby="confirm-title">
        <form method="dialog" class="dialog-form">
            <h2 id="confirm-title">Are you sure?</h2>
            <p id="confirm-message" class="confirm-message"></p>
            <div class="dialog-buttons">
                <button type="submit" value="cancel" class="btn-secondary">Cancel</button>
                <button type="submit" value="ok" class="btn-danger" id="confirm-ok">Delete</button>
            </div>
        </form>
    </dialog>

    <script src="js/staff-menu.js"></script>
</body>

</html>