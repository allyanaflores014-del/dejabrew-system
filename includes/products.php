<?php
/*
 * products.php - loads products and categories from the database.
 * Gives the pages two variables:
 *   $products   -> same shape as before (id, prefix, name, category, image, description, price)
 *                  plus: full_name, category_name, available
 *   $categories -> list of [id, name]
 * 'category' is the category ID (as text). The filter buttons use the same value.
 */
require_once __DIR__ . '/db.php';

// "Caramel Macchiato" -> ["Caramel", "Macchiato"]. One word -> ["", "Americano"].
function splitProductName(string $fullName): array
{
    $fullName = trim($fullName);
    $space = strpos($fullName, ' ');

    if ($space === false) {
        return ['', $fullName];
    }
    return [substr($fullName, 0, $space), substr($fullName, $space + 1)];
}

$db = getDB();

$products = [];
$result = $db->query(
    'SELECT p.product_id, p.product_name, p.description, p.image, p.base_price,
            p.is_available, p.category_id, c.category_name
     FROM products p
     JOIN categories c ON c.category_id = p.category_id
     ORDER BY p.product_id'
);

while ($row = $result->fetch_assoc()) {
    [$prefix, $name] = splitProductName($row['product_name']);

    $products[] = [
        'id' => (int) $row['product_id'],
        'prefix' => $prefix,
        'name' => $name,
        'full_name' => $row['product_name'],
        'category' => (string) $row['category_id'],
        'category_name' => $row['category_name'],
        'image' => $row['image'],
        'description' => $row['description'],
        'price' => (int) $row['base_price'],
        'available' => (int) $row['is_available'] === 1,
    ];
}

$categories = [];
$result = $db->query('SELECT category_id, category_name FROM categories ORDER BY category_name');

while ($row = $result->fetch_assoc()) {
    $categories[] = [
        'id' => (int) $row['category_id'],
        'name' => $row['category_name'],
    ];
}