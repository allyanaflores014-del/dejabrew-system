<?php
// Staff Menu Management - handles every save/delete request (returns JSON).
require_once __DIR__ . '/includes/functions.php';   // session + getRole()
require_once __DIR__ . '/includes/db.php';

header('Content-Type: application/json; charset=utf-8');

function respond(bool $ok, string $message = '', array $extra = []): void
{
    echo json_encode(array_merge(['ok' => $ok, 'message' => $message], $extra));
    exit;
}

// ---------- Safety checks ----------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    respond(false, 'Invalid request.');
}
if (getRole() !== 'staff') {
    http_response_code(403);
    respond(false, 'Please log in as staff.');
}
$token = $_POST['csrf'] ?? '';
if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $token)) {
    http_response_code(403);
    respond(false, 'Session expired. Please refresh the page.');
}

// ---------- Image helpers ----------
const UPLOAD_FOLDER = 'assets/uploads/products/';

// Returns [path, errorMessage]
function saveImage(array $file): array
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [null, 'Image upload failed. The image must be 2 MB or smaller.'];
    }
    if ($file['size'] > 2 * 1024 * 1024) {
        return [null, 'Image must be 2 MB or smaller.'];
    }

    // Check the real file type, not just the file name
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($allowed[$mime])) {
        return [null, 'Only JPG, PNG or WEBP images are allowed.'];
    }

    $folder = __DIR__ . '/' . UPLOAD_FOLDER;
    if (!is_dir($folder) && !mkdir($folder, 0755, true)) {
        return [null, 'Could not create the upload folder.'];
    }

    $fileName = 'product_' . bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($file['tmp_name'], $folder . $fileName)) {
        return [null, 'Could not save the image.'];
    }
    return [UPLOAD_FOLDER . $fileName, null];
}

// Only deletes files inside the uploads folder (never assets/Classic.png etc.)
function deleteImageFile(string $path): void
{
    if (strpos($path, UPLOAD_FOLDER) === 0 && strpos($path, '..') === false) {
        $fullPath = __DIR__ . '/' . $path;
        if (is_file($fullPath)) {
            unlink($fullPath);
        }
    }
}

function categoryExists(mysqli $db, int $id): bool
{
    $stmt = $db->prepare('SELECT 1 FROM categories WHERE category_id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

// ---------- Handle the action ----------
$db = getDB();
$action = $_POST['action'] ?? '';

try {
    switch ($action) {

        // --- Availability switch ---
        case 'toggle_availability':
            $id = (int) ($_POST['product_id'] ?? 0);
            $available = ($_POST['available'] ?? '') === '1' ? 1 : 0;

            $stmt = $db->prepare('UPDATE products SET is_available = ? WHERE product_id = ?');
            $stmt->bind_param('ii', $available, $id);
            $stmt->execute();
            respond(true);
            break;

        // --- Add or edit a product ---
        case 'save_product':
            $id = (int) ($_POST['product_id'] ?? 0);   // 0 = new product
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $categoryId = (int) ($_POST['category_id'] ?? 0);
            $priceText = trim($_POST['price'] ?? '');

            if ($name === '' || mb_strlen($name) > 60) {
                respond(false, 'Product name is required (up to 60 characters).');
            }
            if (!categoryExists($db, $categoryId)) {
                respond(false, 'Please choose a category.');
            }
            if ($description === '' || mb_strlen($description) > 500) {
                respond(false, 'Description is required (up to 500 characters).');
            }
            if (!ctype_digit($priceText) || (int) $priceText < 1 || (int) $priceText > 9999) {
                respond(false, 'Price must be a whole number from 1 to 9999.');
            }
            $price = (int) $priceText;

            $hasFile = isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE;

            if ($id === 0) {
                // NEW product: image is required
                if (!$hasFile) {
                    respond(false, 'Please upload a product image.');
                }
                [$imagePath, $imageError] = saveImage($_FILES['image']);
                if ($imageError) {
                    respond(false, $imageError);
                }

                $stmt = $db->prepare(
                    'INSERT INTO products (category_id, product_name, description, image, base_price)
                     VALUES (?, ?, ?, ?, ?)'
                );
                $stmt->bind_param('isssi', $categoryId, $name, $description, $imagePath, $price);
                $stmt->execute();
                respond(true, 'Product added.');
            }

            // EDIT product
            $stmt = $db->prepare('SELECT image FROM products WHERE product_id = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $old = $stmt->get_result()->fetch_assoc();
            if (!$old) {
                respond(false, 'That product no longer exists.');
            }

            if ($hasFile) {
                [$imagePath, $imageError] = saveImage($_FILES['image']);
                if ($imageError) {
                    respond(false, $imageError);
                }
                $stmt = $db->prepare(
                    'UPDATE products SET category_id = ?, product_name = ?, description = ?, image = ?, base_price = ?
                     WHERE product_id = ?'
                );
                $stmt->bind_param('isssii', $categoryId, $name, $description, $imagePath, $price, $id);
                $stmt->execute();
                deleteImageFile($old['image']);   // remove the replaced picture
            } else {
                $stmt = $db->prepare(
                    'UPDATE products SET category_id = ?, product_name = ?, description = ?, base_price = ?
                     WHERE product_id = ?'
                );
                $stmt->bind_param('issii', $categoryId, $name, $description, $price, $id);
                $stmt->execute();
            }
            respond(true, 'Product updated.');
            break;

        // --- Delete a product ---
        case 'delete_product':
            $id = (int) ($_POST['product_id'] ?? 0);

            $stmt = $db->prepare('SELECT image FROM products WHERE product_id = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $old = $stmt->get_result()->fetch_assoc();
            if (!$old) {
                respond(false, 'That product no longer exists.');
            }

            $stmt = $db->prepare('DELETE FROM products WHERE product_id = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            deleteImageFile($old['image']);
            respond(true, 'Product deleted.');
            break;

        // --- Categories ---
        case 'add_category':
        case 'rename_category':
            $name = trim($_POST['name'] ?? '');
            $id = (int) ($_POST['category_id'] ?? 0);

            if ($name === '' || mb_strlen($name) > 30) {
                respond(false, 'Category name is required (up to 30 characters).');
            }

            // Same name already used by a different category?
            $stmt = $db->prepare('SELECT category_id FROM categories WHERE category_name = ? AND category_id <> ?');
            $stmt->bind_param('si', $name, $id);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                respond(false, 'That category already exists.');
            }

            if ($action === 'add_category') {
                $stmt = $db->prepare('INSERT INTO categories (category_name) VALUES (?)');
                $stmt->bind_param('s', $name);
            } else {
                if (!categoryExists($db, $id)) {
                    respond(false, 'That category no longer exists.');
                }
                $stmt = $db->prepare('UPDATE categories SET category_name = ? WHERE category_id = ?');
                $stmt->bind_param('si', $name, $id);
            }
            $stmt->execute();
            respond(true, $action === 'add_category' ? 'Category added.' : 'Category renamed.');
            break;

        case 'delete_category':
            $id = (int) ($_POST['category_id'] ?? 0);

            $stmt = $db->prepare('SELECT COUNT(*) AS total FROM products WHERE category_id = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $count = (int) $stmt->get_result()->fetch_assoc()['total'];

            if ($count > 0) {
                respond(false, 'This category still has ' . $count . ' product(s). Move them to another category first.');
            }

            $stmt = $db->prepare('DELETE FROM categories WHERE category_id = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            respond(true, 'Category deleted.');
            break;

        default:
            respond(false, 'Unknown action.');
    }
} catch (mysqli_sql_exception $error) {
    respond(false, 'Database error. Please try again.');
}