// =====================================================
// DejaBrew - staff-menu.js  (Menu Management)
// 1. Helpers (send requests to PHP)
// 2. Show status / availability switch
// 3. Expand a row
// 4. Search + category filter
// 5. Add / Edit product
// 6. Delete product
// 7. Categories (add, rename, delete)
// =====================================================

// ---------- 1. Helpers ----------
var csrfToken = document.querySelector('meta[name="csrf"]').content;

// Sends data to staff-menu-actions.php and gives back {ok, message}
function sendToServer(fields, formData) {
    var data = formData || new FormData();
    data.append('csrf', csrfToken);
    Object.keys(fields).forEach(function (key) {
        data.append(key, fields[key]);
    });

    return fetch('staff-menu-actions.php', { method: 'POST', body: data })
        .then(function (response) { return response.json(); })
        .catch(function () {
            return { ok: false, message: 'Could not reach the server. Please try again.' };
        });
}

// Shows a "Are you sure?" box. Gives back a Promise: true = confirmed.
var confirmDialog = document.getElementById('confirm-dialog');

function askConfirm(title, message, buttonText) {
    return new Promise(function (resolve) {
        document.getElementById('confirm-title').textContent = title;
        document.getElementById('confirm-message').textContent = message;
        document.getElementById('confirm-ok').textContent = buttonText;
        confirmDialog.returnValue = '';

        confirmDialog.addEventListener('close', function () {
            resolve(confirmDialog.returnValue === 'ok');
        }, { once: true });

        confirmDialog.showModal();
    });
}

function showFormError(element, message) {
    element.textContent = message;
    element.hidden = false;
}


// ---------- 2. Show status / availability switch ----------
var menuList = document.getElementById('menu-list');
var items = document.querySelectorAll('.menu-item');

function showStatus(item, isAvailable) {
    var text = isAvailable ? 'Available' : 'Not Available';

    item.dataset.available = isAvailable ? 'true' : 'false';   // CSS uses this for the dot color
    item.querySelector('.status-text').textContent = text;
    item.querySelector('.switch-label').textContent = text;
    item.querySelector('.availability-toggle').checked = isAvailable;
}

menuList.addEventListener('change', function (event) {
    if (!event.target.matches('.availability-toggle')) return;

    var item = event.target.closest('.menu-item');
    var nowAvailable = event.target.checked;

    showStatus(item, nowAvailable);

    sendToServer({
        action: 'toggle_availability',
        product_id: item.dataset.id,
        available: nowAvailable ? '1' : '0'
    }).then(function (result) {
        if (!result.ok) {
            showStatus(item, !nowAvailable);   // saving failed: put it back
            alert(result.message || 'Could not save the change.');
        }
    });
});


// ---------- 3. Expand a row ----------
menuList.addEventListener('click', function (event) {
    var row = event.target.closest('.menu-row');
    if (!row) return;

    var item = row.closest('.menu-item');
    var detail = item.querySelector('.menu-detail');
    var willOpen = detail.hidden;

    // Close every row first, so only one is open at a time
    items.forEach(function (other) {
        other.classList.remove('is-open');
        other.querySelector('.menu-detail').hidden = true;
        other.querySelector('.menu-row').setAttribute('aria-expanded', 'false');
    });

    if (willOpen) {
        item.classList.add('is-open');
        detail.hidden = false;
        row.setAttribute('aria-expanded', 'true');
    }
});


// ---------- 4. Search + category filter ----------
var searchInput = document.getElementById('menu-search');
var emptyMessage = document.getElementById('menu-empty');
var categoryList = document.getElementById('category-list');
var activeCategory = 'all';   // 'all' or a category id

function applyFilters() {
    var term = searchInput.value.trim().toLowerCase();
    var shown = 0;

    items.forEach(function (item) {
        var matchesCategory = activeCategory === 'all' || item.dataset.category === activeCategory;
        var matchesSearch = item.dataset.name.indexOf(term) !== -1;
        var visible = matchesCategory && matchesSearch;

        item.hidden = !visible;
        if (visible) shown++;
    });

    emptyMessage.hidden = shown !== 0;
}

searchInput.addEventListener('input', applyFilters);

// Click a category to filter. Click it again to show everything.
categoryList.addEventListener('click', function (event) {
    var nameButton = event.target.closest('.cat-name');
    if (!nameButton) return;

    var catItem = nameButton.closest('.cat-item');
    var wasActive = catItem.classList.contains('is-active');

    categoryList.querySelectorAll('.cat-item').forEach(function (c) {
        c.classList.remove('is-active');
    });

    if (wasActive) {
        activeCategory = 'all';
    } else {
        catItem.classList.add('is-active');
        activeCategory = catItem.dataset.id;
    }
    applyFilters();
});


// ---------- 5. Add / Edit product ----------
var productDialog = document.getElementById('product-dialog');
var productForm = document.getElementById('product-form');
var productError = document.getElementById('product-error');
var productSave = document.getElementById('product-save');
var imageInput = document.getElementById('product-image');
var previewImage = document.getElementById('product-preview');
var previewEmpty = document.getElementById('preview-empty');

function showPreview(src) {
    previewImage.hidden = !src;
    previewEmpty.hidden = !!src;
    if (src) previewImage.src = src;
}

imageInput.addEventListener('change', function () {
    if (imageInput.files.length > 0) {
        showPreview(URL.createObjectURL(imageInput.files[0]));
    }
});

// item = the product row to edit, or nothing when adding a new product
function openProductDialog(item) {
    var editing = !!item;

    productForm.reset();
    productError.hidden = true;
    productSave.disabled = false;

    document.getElementById('product-title').textContent = editing ? 'Edit Product' : 'Add Product';
    document.getElementById('product-id').value = editing ? item.dataset.id : '';
    imageInput.required = !editing;   // image is only required for new products

    if (editing) {
        document.getElementById('product-name').value = item.dataset.fullName;
        document.getElementById('product-category').value = item.dataset.category;
        document.getElementById('product-description').value = item.dataset.description;
        document.getElementById('product-price').value = item.dataset.price;
        showPreview(item.dataset.image);
    } else {
        showPreview('');
    }

    productDialog.showModal();
}

document.getElementById('add-product-btn').addEventListener('click', function () {
    openProductDialog(null);
});

document.getElementById('product-cancel').addEventListener('click', function () {
    productDialog.close();
});

productForm.addEventListener('submit', function (event) {
    event.preventDefault();
    productError.hidden = true;

    // Quick checks here; PHP checks everything again
    var isNew = document.getElementById('product-id').value === '';
    if (isNew && imageInput.files.length === 0) {
        showFormError(productError, 'Please upload a product image.');
        return;
    }

    productSave.disabled = true;

    sendToServer({ action: 'save_product' }, new FormData(productForm)).then(function (result) {
        if (result.ok) {
            location.reload();
        } else {
            showFormError(productError, result.message);
            productSave.disabled = false;
        }
    });
});

menuList.addEventListener('click', function (event) {
    var editButton = event.target.closest('.btn-edit');
    if (editButton) {
        openProductDialog(editButton.closest('.menu-item'));
    }
});


// ---------- 6. Delete product ----------
menuList.addEventListener('click', function (event) {
    var deleteButton = event.target.closest('.btn-delete');
    if (!deleteButton) return;

    var item = deleteButton.closest('.menu-item');

    askConfirm(
        'Delete product?',
        'Delete "' + item.dataset.fullName + '"? This cannot be undone.',
        'Delete'
    ).then(function (confirmed) {
        if (!confirmed) return;

        sendToServer({ action: 'delete_product', product_id: item.dataset.id }).then(function (result) {
            if (result.ok) {
                location.reload();
            } else {
                alert(result.message);
            }
        });
    });
});


// ---------- 7. Categories ----------
var categoryDialog = document.getElementById('category-dialog');
var categoryForm = document.getElementById('category-form');
var categoryError = document.getElementById('category-error');
var categorySave = document.getElementById('category-save');
var categoryNameInput = document.getElementById('category-name');
var categoryIdInput = document.getElementById('category-id');

// catItem = the category to rename, or nothing when adding a new one
function openCategoryDialog(catItem) {
    var renaming = !!catItem;

    categoryError.hidden = true;
    categorySave.disabled = false;
    document.getElementById('category-title').textContent = renaming ? 'Rename Category' : 'Add Category';
    categoryIdInput.value = renaming ? catItem.dataset.id : '';
    categoryNameInput.value = renaming ? catItem.dataset.name : '';

    categoryDialog.showModal();
    categoryNameInput.focus();
}

document.getElementById('add-category-btn').addEventListener('click', function () {
    openCategoryDialog(null);
});

document.getElementById('category-cancel').addEventListener('click', function () {
    categoryDialog.close();
});

categoryForm.addEventListener('submit', function (event) {
    event.preventDefault();
    categoryError.hidden = true;
    categorySave.disabled = true;

    var renaming = categoryIdInput.value !== '';

    sendToServer({
        action: renaming ? 'rename_category' : 'add_category',
        category_id: categoryIdInput.value,
        name: categoryNameInput.value
    }).then(function (result) {
        if (result.ok) {
            location.reload();
        } else {
            showFormError(categoryError, result.message);
            categorySave.disabled = false;
        }
    });
});

categoryList.addEventListener('click', function (event) {
    var catItem = event.target.closest('.cat-item');
    if (!catItem) return;

    if (event.target.closest('.cat-rename')) {
        openCategoryDialog(catItem);
        return;
    }

    if (event.target.closest('.cat-delete')) {
        askConfirm(
            'Delete category?',
            'Delete the category "' + catItem.dataset.name + '"? A category that still has products cannot be deleted.',
            'Delete'
        ).then(function (confirmed) {
            if (!confirmed) return;

            sendToServer({ action: 'delete_category', category_id: catItem.dataset.id }).then(function (result) {
                if (result.ok) {
                    location.reload();
                } else {
                    alert(result.message);   // e.g. "still has 3 product(s)"
                }
            });
        });
    }
});