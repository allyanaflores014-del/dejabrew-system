// ========== 1. SET UP ==========
// Put the sample orders in localStorage the first time the page opens
seedSampleOrders();


// ========== 2. HELPERS ==========
// Makes text safe to put inside HTML
function escapeHtml(text) {
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

// 265 -> "₱265"
function formatPeso(amount) {
    return '₱' + amount.toLocaleString();
}

// "2026-10-09T12:30:00" -> "12:30 PM"
function formatTime(dateText) {
    return new Date(dateText).toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit'
    });
}

// "2026-10-09T12:30:00" -> "Oct 9, 2026"
function formatDate(dateText) {
    return new Date(dateText).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    });
}

// "13:00" -> "1:00 PM"
function formatPickup(timeText) {
    const parts = timeText.split(':').map(Number);
    const ampm = parts[0] >= 12 ? 'PM' : 'AM';
    const hour12 = parts[0] % 12 || 12;
    return hour12 + ':' + String(parts[1]).padStart(2, '0') + ' ' + ampm;
}

// The words shown on the badges
const STATUS_LABELS = {
    pending: 'Pending',
    preparing: 'Preparing',
    completed: 'Completed',
    cancelled: 'Cancelled'
};

// Total number of drinks in an order (2 x Latte + 1 x Frappe = 3)
function countItems(order) {
    let total = 0;
    order.items.forEach(function (item) {
        total += item.quantity;
    });
    return total;
}


// ========== 3. WHICH ORDER IS SELECTED ==========
// null means no order is selected, so the panel stays closed
let selectedOrderId = null;

const main = document.querySelector('.staff-main');
const detailPanel = document.getElementById('order-detail');


// ========== 4. FILTER AND SEARCH (NEW) ==========
let activeStatus = 'all';   // all | pending | preparing | completed
let searchText = '';        // whatever the staff typed in the search box

const searchInput = document.getElementById('order-search');
const filterPills = document.querySelectorAll('.filter-pill');

// Does this order pass the current filter AND the current search?
function matchesFilters(order) {

    // 1. Status filter
    if (activeStatus !== 'all' && order.status !== activeStatus) {
        return false;
    }

    // 2. Search box (empty search = show everything)
    const text = searchText.trim().toLowerCase();
    if (text === '') {
        return true;
    }

    const idQuery = text.replace('#', '');   // "#1008" and "1008" both work
    const matchesId = idQuery !== '' && String(order.id).includes(idQuery);
    const matchesCustomer = order.customerName.toLowerCase().includes(text);
    const matchesItem = order.items.some(function (item) {
        return item.name.toLowerCase().includes(text);
    });

    return matchesId || matchesCustomer || matchesItem;
}


// ========== 5. SHOW THE ORDER LIST ==========
const ordersList = document.getElementById('orders-list');
const noOrders = document.getElementById('no-orders');

function renderOrders() {
    // NEW: keep only the orders that pass the filter and the search
    const orders = getOrders().filter(matchesFilters);

    // Newest order on top
    orders.sort(function (a, b) {
        return new Date(b.createdAt) - new Date(a.createdAt);
    });

    ordersList.innerHTML = '';

    orders.forEach(function (order) {
        const count = countItems(order);
        const selectedClass = (order.id === selectedOrderId) ? ' is-selected' : '';

        ordersList.innerHTML += `
            <button type="button" class="order-row${selectedClass}" data-id="${order.id}">
                <span class="row-id">#${order.id}</span>
                <span>${escapeHtml(order.customerName)}</span>
                <span>${count} ${count === 1 ? 'item' : 'items'}</span>
                <span>${formatPeso(order.total)}</span>
                <span>${formatTime(order.createdAt)}</span>
                <span class="row-end">
                    <span class="status-badge status-${order.status}">${STATUS_LABELS[order.status]}</span>
                    <span class="row-chevron">›</span>
                </span>
            </button>
        `;
    });

    // Show the "No orders found" message only when the list is empty
    noOrders.hidden = orders.length > 0;
}


// ========== 6. SHOW THE DETAIL PANEL ==========
function showDetail(order) {

    // Title, badge, and date
    document.getElementById('detail-title').textContent = 'Order #' + order.id;

    const badge = document.getElementById('detail-status');
    badge.textContent = STATUS_LABELS[order.status];
    badge.className = 'status-badge status-' + order.status;

    document.getElementById('detail-date').textContent =
        formatDate(order.createdAt) + ' · ' + formatTime(order.createdAt) +
        ' · Pickup ' + formatPickup(order.pickupTime);

    // Customer
    document.getElementById('detail-customer-name').textContent = order.customerName;
    document.getElementById('detail-customer-email').textContent = order.customerEmail;

    // Items: one block for every drink in the order
    const itemsBox = document.getElementById('detail-items');
    itemsBox.innerHTML = '';

    order.items.forEach(function (item) {
        itemsBox.innerHTML += `
            <div class="detail-item">
                <img src="${escapeHtml(item.image)}" alt="">
                <div>
                    <p class="item-title">${escapeHtml(item.name)}</p>
                    <p class="item-meta">${escapeHtml(item.size)} · ${escapeHtml(item.sugar)} sugar</p>
                </div>
                <div class="item-end">
                    ${formatPeso(item.price)}
                    <span class="item-qty">x${item.quantity}</span>
                </div>
            </div>
        `;
    });

    // Subtotal, discount, and total
    const count = countItems(order);
    document.getElementById('detail-subtotal-label').textContent =
        'Subtotal (' + count + (count === 1 ? ' item)' : ' items)');
    document.getElementById('detail-subtotal').textContent = formatPeso(order.subtotal);

    // The discount row only appears when there is a discount
    document.getElementById('detail-discount-row').hidden = !(order.discount > 0);
    document.getElementById('detail-discount').textContent = '-' + formatPeso(order.discount);

    document.getElementById('detail-total').textContent = formatPeso(order.total);

    // Payment and notes
    document.getElementById('detail-payment').textContent = order.paymentMethod;
    document.getElementById('detail-notes').textContent =
        order.note ? order.note : 'No special instructions.';

    // Buttons depend on the status:
    //   pending   -> "Start Preparing"      + Cancel
    //   preparing -> "Mark as Completed"    + Cancel
    //   completed / cancelled -> no buttons (nothing left to do)
    const statusBtn = document.getElementById('status-btn');
    const cancelBtn = document.getElementById('cancel-btn');

    if (order.status === 'pending') {
        statusBtn.textContent = 'Start Preparing';
        statusBtn.hidden = false;
        cancelBtn.hidden = false;
    } else if (order.status === 'preparing') {
        statusBtn.textContent = 'Mark as Completed';
        statusBtn.hidden = false;
        cancelBtn.hidden = false;
    } else {
        statusBtn.hidden = true;
        cancelBtn.hidden = true;
    }

    // Finally, show the panel and make room for it
    detailPanel.hidden = false;
    main.classList.add('has-detail');
}

function closeDetail() {
    selectedOrderId = null;
    detailPanel.hidden = true;
    main.classList.remove('has-detail');
    renderOrders();   // removes the highlight from the row
}


// ========== 7. CLICKING AN ORDER ROW ==========
ordersList.addEventListener('click', function (event) {
    const row = event.target.closest('.order-row');
    if (!row) return;

    const id = Number(row.dataset.id);

    // Clicking the row that is already open closes the panel
    if (id === selectedOrderId) {
        closeDetail();
        return;
    }

    const order = getOrders().find(function (o) {
        return o.id === id;
    });
    if (!order) return;

    selectedOrderId = id;
    renderOrders();     // redraw the list so the right row is highlighted
    showDetail(order);
});


// ========== 8. CHANGING THE STATUS ==========

// What comes next for each status. Completed and cancelled have no next step.
const NEXT_STATUS = {
    pending: 'preparing',
    preparing: 'completed'
};

// Save a new status, then refresh the list and the panel
function updateOrderStatus(id, newStatus) {
    const orders = getOrders();
    const order = orders.find(function (o) {
        return o.id === id;
    });
    if (!order) return;

    order.status = newStatus;
    saveOrders(orders);

    renderOrders();     // the badge in the list changes
    showDetail(order);  // the badge and buttons in the panel change
}

// "Start Preparing" / "Mark as Completed" button
document.getElementById('status-btn').addEventListener('click', function () {
    if (selectedOrderId === null) return;

    const order = getOrders().find(function (o) {
        return o.id === selectedOrderId;
    });
    if (!order) return;

    const next = NEXT_STATUS[order.status];
    if (next) {
        updateOrderStatus(order.id, next);
    }
});

// "Cancel Order" button: ask first, because this cannot be undone
document.getElementById('cancel-btn').addEventListener('click', function () {
    if (selectedOrderId === null) return;

    const sure = confirm('Cancel order #' + selectedOrderId + '? This cannot be undone.');
    if (!sure) return;

    updateOrderStatus(selectedOrderId, 'cancelled');
});


// ========== 9. FILTER BUTTONS AND SEARCH BOX (NEW) ==========

// Redraw the list. If the open order is no longer in the list, close its panel.
function refreshList() {
    renderOrders();

    if (selectedOrderId !== null) {
        const stillVisible = getOrders().some(function (order) {
            return order.id === selectedOrderId && matchesFilters(order);
        });

        if (!stillVisible) {
            closeDetail();
        }
    }
}

// Clicking All orders / Pending / Preparing / Completed
filterPills.forEach(function (pill) {
    pill.addEventListener('click', function () {
        activeStatus = pill.dataset.status;

        // Only the clicked button looks "active" (dark)
        filterPills.forEach(function (other) {
            other.classList.toggle('is-active', other === pill);
        });

        refreshList();
    });
});

// Typing in the search box
searchInput.addEventListener('input', function () {
    searchText = searchInput.value;
    refreshList();
});

// ========== "BACK TO ORDERS" BUTTON AND ESC KEY ==========
const closeButton = document.getElementById('detail-close');
if (closeButton) {
    closeButton.addEventListener('click', closeDetail);
}

// Pressing Esc also closes the panel
document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape' && selectedOrderId !== null) {
        closeDetail();
    }
});
// ========== 10. WHEN THE PAGE LOADS ==========
renderOrders();