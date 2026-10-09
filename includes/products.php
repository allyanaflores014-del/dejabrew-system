<?php
/*
 * products.php - all product data lives here.
 * Image paths are relative to index.php. File names are case-sensitive
 * on most web hosts, so keep them exactly as they appear in assets/.
 *
 * prefix   -> small script word above the name ("Classic")
 * name     -> big bold name ("Iced Coffee")
 * category -> iced-coffee | hot-coffee | frappe  (must match the filter buttons)
 */
$products = [
    [
        'id' => 1,
        'prefix' => 'Classic',
        'name' => 'Iced Coffee',
        'category' => 'iced-coffee',
        'image' => 'assets/Classic.png',
        'description' => 'Smooth, refreshing coffee served over ice for a simple everyday brew.',
        'price' => 89,
    ],
    [
        'id' => 2,
        'prefix' => 'Caramel',
        'name' => 'Macchiato',
        'category' => 'iced-coffee',
        'image' => 'assets/CaramelMacchiato.png',
        'description' => 'Espresso poured over cold milk and finished with a ribbon of buttery caramel.',
        'price' => 109,
    ],
    [
        'id' => 3,
        'prefix' => 'Spanish',
        'name' => 'Latte',
        'category' => 'iced-coffee',
        'image' => 'assets/SpanishLatte.png',
        'description' => 'Espresso and milk sweetened with condensed milk for a rich, creamy sip.',
        'price' => 109,
    ],
    [
        'id' => 4,
        'prefix' => 'Caramel',
        'name' => 'Latte',
        'category' => 'iced-coffee',
        'image' => 'assets/CaramelLatte.png',
        'description' => 'Smooth espresso and milk sweetened with a drizzle of golden caramel.',
        'price' => 99,
    ],
    [
        'id' => 5,
        'prefix' => 'Vanilla',
        'name' => 'Latte',
        'category' => 'iced-coffee',
        'image' => 'assets/VanillaLatte.png',
        'description' => 'Creamy espresso and milk with a soft, sweet vanilla finish.',
        'price' => 99,
    ],
    [
        'id' => 6,
        'prefix' => 'Classic',
        'name' => 'Americano',
        'category' => 'hot-coffee',
        'image' => 'assets/Americano.png',
        'description' => 'Bold espresso topped with hot water for a clean, straightforward cup.',
        'price' => 79,
    ],
    [
        'id' => 7,
        'prefix' => 'Cafe',
        'name' => 'Latte',
        'category' => 'hot-coffee',
        'image' => 'assets/CafeLatte.png',
        'description' => 'Espresso with steamed milk and a light layer of foam. Simple and smooth.',
        'price' => 89,
    ],
    [
        'id' => 8,
        'prefix' => 'Mocha',
        'name' => 'Latte',
        'category' => 'hot-coffee',
        'image' => 'assets/MochaLatte.png',
        'description' => 'Espresso, steamed milk, and chocolate for a cozy, comforting cup.',
        'price' => 99,
    ],
    [
        'id' => 9,
        'prefix' => 'Chocolate',
        'name' => 'Frappe',
        'category' => 'frappe',
        'image' => 'assets/ChocolateFrappe.png',
        'description' => 'Blended coffee and chocolate with ice, topped with whipped cream.',
        'price' => 119,
    ],
    [
        'id' => 10,
        'prefix' => 'Java Chip',
        'name' => 'Frappe',
        'category' => 'frappe',
        'image' => 'assets/JavaChipFrappe.png',
        'description' => 'Icy blended coffee with chocolate chips and a swirl of whipped cream.',
        'price' => 129,
    ],
];

