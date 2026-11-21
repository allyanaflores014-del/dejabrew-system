<?php
// Tells the customer pages which products are available: {"1": true, "2": false}
require_once __DIR__ . '/includes/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$list = [];
$result = getDB()->query('SELECT product_id, is_available FROM products');

while ($row = $result->fetch_assoc()) {
    $list[$row['product_id']] = ((int) $row['is_available'] === 1);
}

echo json_encode((object) $list);