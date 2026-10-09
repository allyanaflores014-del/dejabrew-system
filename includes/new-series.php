<?php
/*
 * new-series.php
 * --------------
 * The "New Series" banner after the scroll story.
 * Change the values in $series to feature another drink.
 * The drink name comes from products.php (matched by product_id).
 */
$series = [
    'product_id' => 9,   // 'id' of Chocolate Frappe in includes/products.php
    'kicker' => 'New',
    'title' => 'Series',
    'description' => 'A rich and creamy blended coffee with delicious chocolate flavor.',
    'image' => 'assets/new-series-chocolate-frappe.png',   // your separate photo
];

$seriesName = '';
foreach ($products as $item) {
    if ((int) $item['id'] === $series['product_id']) {
        $seriesName = $item['prefix'] . ' ' . $item['name'];
    }
}
?>
<section class="new-series" aria-labelledby="new-series-heading">
    <div class="series-card">
        <div class="series-text">
            <h2 class="series-heading" id="new-series-heading">
                <span class="series-kicker"><?= e($series['kicker']) ?></span>
                <span class="series-title"><?= e($series['title']) ?></span>
            </h2>
            <h3 class="series-name"><?= e($seriesName) ?></h3>
            <p class="series-desc"><?= e($series['description']) ?></p>

            <!-- Scrolls up to this drink's card in the menu (see script.js) -->
            <button type="button" class="series-btn" data-product-id="<?= (int) $series['product_id'] ?>">Add to
                Cart</button>
        </div>

        <img class="series-cup" src="<?= e($series['image']) ?>" alt="<?= e($seriesName) ?>" loading="lazy">
    </div>
</section>