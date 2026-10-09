<?php
/*
 * product-card.php
 * ----------------
 * One product card. index.php includes this file inside a foreach loop,
 * so the variable $product is the current product.
 *
 * Price rule: $product['price'] is the SMALL price.
 *             Medium = +15, Large = +30 (see $sizes in index.php, the "extra" values).
 *
 * Structure:
 *   .product-slot   reserves space in the grid (so the grid never jumps)
 *     .product-card   the thing that grows on hover / flips on click
 *       .card-inner     rotates in 3D
 *         .card-front   image, info button, size + sugar + quantity, add button
 *         .card-back    description (click anywhere on it to flip back)
 */
$fullName = $product['prefix'] . ' ' . $product['name'];
$id = (int) $product['id'];
?>
<div class="product-slot" id="product-<?= $id ?>" data-category="<?= e($product['category']) ?>"
    data-name="<?= e($fullName) ?>">

    <article class="product-card" data-product-id="<?= $id ?>" data-product-name="<?= e($fullName) ?>"
        data-price="<?= (int) $product['price'] ?>" data-image="<?= e($product['image']) ?>">
        <div class="card-inner">

            <!-- ================= FRONT ================= -->
            <div class="card-face card-front">
                <h3 class="card-title">
                    <span class="title-script"><?= e($product['prefix']) ?></span>
                    <span class="title-main"><?= e($product['name']) ?></span>
                </h3>

                <div class="card-image">
                    <img src="<?= e($product['image']) ?>" alt="<?= e($fullName) ?>" loading="lazy">
                    <button type="button" class="icon-btn info-btn" aria-label="Show details for <?= e($fullName) ?>">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.6" aria-hidden="true">
                            <circle cx="12" cy="12" r="9" />
                            <path d="M12 11v5M12 7.8v.2" stroke-linecap="round" />
                        </svg>
                    </button>
                </div>

                <!-- Size, sugar, quantity, total: hidden until the card is "open" (hovered / tapped) -->
                <div class="card-options">
                    <div class="card-options-inner">

                        <div class="option-row">
                            <div class="field">
                                <label for="size-<?= $id ?>">Size</label>
                                <select id="size-<?= $id ?>" class="size-select">
                                    <?php foreach ($sizes as $value => $size): ?>
                                        <option value="<?= e($value) ?>" data-extra="<?= (int) $size['extra'] ?>"
                                            <?= $value === 'Small' ? 'selected' : '' ?>>
                                            <?= e($size['label']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="field">
                                <label for="sugar-<?= $id ?>">Sugar</label>
                                <select id="sugar-<?= $id ?>" class="sugar-select">
                                    <?php foreach ($sugarLevels as $level): ?>
                                        <option value="<?= e($level) ?>" <?= $level === '50%' ? 'selected' : '' ?>>
                                            <?= e($level) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="qty-row">
                            <div class="card-qty" role="group" aria-label="Quantity">
                                <button type="button" class="card-qty-btn card-qty-minus"
                                    aria-label="Decrease quantity">&minus;</button>
                                <span class="card-qty-value" aria-live="polite">1</span>
                                <button type="button" class="card-qty-btn card-qty-plus"
                                    aria-label="Increase quantity">+</button>
                            </div>
                            <p class="card-total">
                                <span class="card-total-label">Total Amount:</span>
                                <strong
                                    class="card-total-value">₱<?= number_format((int) $product['price'], 2) ?></strong>
                            </p>
                        </div>

                    </div>
                </div>

                <div class="card-footer">
                    <button type="button" class="add-btn" aria-label="Add <?= e($fullName) ?> to cart">
                        <span class="add-btn-plus" aria-hidden="true">+</span>
                        <span class="add-btn-label">Add to Cart</span>
                    </button>
                </div>
            </div>

            <!-- ================= BACK ================= -->
            <!-- Clicking anywhere on the back flips it to the front (see script.js) -->
            <div class="card-face card-back">
                <h3 class="card-title">
                    <span class="title-script"><?= e($product['prefix']) ?></span>
                    <span class="title-main"><?= e($product['name']) ?></span>
                </h3>

                <img class="back-image" src="<?= e($product['image']) ?>" alt="" loading="lazy">

                <p class="back-description"><?= e($product['description']) ?></p>

                <div class="back-footer">
                    <!-- Still a real button so keyboard users can flip back too -->
                    <button type="button" class="icon-btn back-btn" aria-label="Back to <?= e($fullName) ?>">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.6" aria-hidden="true">
                            <circle cx="12" cy="12" r="9" />
                            <path d="M13.6 8.6 10 12l3.6 3.4z" fill="currentColor" stroke="none" />
                        </svg>
                    </button>
                </div>
            </div>

        </div>
    </article>
</div>