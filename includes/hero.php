<?php
/*
 * The three hero cups.
 *   image -> transparent PNG of the cup (change to your own path)
 *   name  -> shown as the big script title while that cup is hovered
 *   product_id -> the 'id' of this drink in includes/products.php (clicking the cup scrolls to it)
 *   class -> hero-cup--large makes the right cup bigger
 * The cup with 'default' => true is the title shown when nothing is hovered.
 */
$heroCups = [
    ['image' => 'assets/Classic.png', 'name' => 'Classic Iced Coffee', 'product_id' => 1, 'class' => '', 'default' => false],
    ['image' => 'assets/SpanishLatte.png', 'name' => 'Spanish Latte', 'product_id' => 3, 'class' => 'hero-cup--large', 'default' => true],
    ['image' => 'assets/CaramelMacchiato.png', 'name' => 'Caramel Macchiato', 'product_id' => 2, 'class' => '', 'default' => false],
];

// Find the default title (falls back to the first cup).
$defaultTitle = $heroCups[0]['name'];
foreach ($heroCups as $cup) {
    if ($cup['default']) {
        $defaultTitle = $cup['name'];
    }
}
?>
<section class="hero" aria-labelledby="hero-heading">
    <div class="hero-text">
        <h1 class="hero-kicker" id="hero-heading">The Usuals</h1>
        <p class="hero-sub">The brews our customers keep coming back for.</p>
        <!-- JavaScript swaps this text when a cup is hovered. data-default = text to go back to. -->
        <p class="hero-title" id="hero-title" data-default="<?= e($defaultTitle) ?>" aria-hidden="true">
            <?= e($defaultTitle) ?>
        </p>
    </div>

    <div class="hero-cups">
        <?php foreach ($heroCups as $cup): ?>
            <button type="button" class="hero-cup <?= e($cup['class']) ?>" data-title="<?= e($cup['name']) ?>"
                data-product-id="<?= (int) $cup['product_id'] ?>" aria-label="<?= e($cup['name']) ?> - view in the menu">
                <img src="<?= e($cup['image']) ?>" alt="">
            </button>
        <?php endforeach; ?>
    </div>
</section>