// Receipt page (My Orders): builds the order cards + receipts and handles
// selecting an order and downloading its receipt.
//
// The orders are read with getOrders() from js/orders-data.js, the same place
// checkout (js/cart.js) saves them, so a new order appears here automatically.
// This file only READS the orders; it never changes them.

(function () {
  "use strict";

  var page = document.getElementById("mo-page");
  var layout = document.getElementById("mo-layout");
  var emptyBox = document.getElementById("mo-none");
  var list = document.getElementById("mo-list");
  var receiptBox = document.getElementById("mo-receipts");

  if (!page || !layout || !emptyBox || !list || !receiptBox) {
    return;
  }

  var MONTHS = [
    "Jan.",
    "Feb.",
    "Mar.",
    "Apr.",
    "May",
    "Jun.",
    "Jul.",
    "Aug.",
    "Sep.",
    "Oct.",
    "Nov.",
    "Dec.",
  ];

  var orders = []; // the orders currently shown (newest first)
  var selectedId = ""; // id of the order whose receipt is visible

  // ---------- small helpers ----------

  // Makes text safe to put inside HTML.
  function escapeHtml(text) {
    return String(text)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#39;");
  }

  // "₱ 120" or "₱ 118.5"
  function formatPeso(amount) {
    return (
      "₱ " +
      Number(amount || 0).toLocaleString("en-US", { maximumFractionDigits: 2 })
    );
  }

  // "Oct. 5, 2026"
  function formatDate(isoText) {
    var date = new Date(isoText);
    if (isNaN(date.getTime())) {
      return "";
    }
    return (
      MONTHS[date.getMonth()] + " " + date.getDate() + ", " + date.getFullYear()
    );
  }

  // "12:12 PM"
  function formatClock(isoText) {
    var date = new Date(isoText);
    if (isNaN(date.getTime())) {
      return "";
    }
    return date.toLocaleTimeString("en-US", {
      hour: "numeric",
      minute: "2-digit",
    });
  }

  // "14:30" -> "2:30 PM"
  function formatPickup(timeText) {
    if (!timeText || String(timeText).indexOf(":") === -1) {
      return "";
    }
    var parts = String(timeText).split(":").map(Number);
    var suffix = parts[0] >= 12 ? "PM" : "AM";
    var hour12 = parts[0] % 12 || 12;
    return hour12 + ":" + String(parts[1]).padStart(2, "0") + " " + suffix;
  }

  function getQty(item) {
    return Number(item.quantity != null ? item.quantity : item.qty) || 0;
  }

  function countItems(order) {
    var total = 0;
    (order.items || []).forEach(function (item) {
      total += getQty(item);
    });
    return total;
  }

  function itemLabel(count) {
    return count + (count === 1 ? " Item" : " Items");
  }

  function getSubtotal(order) {
    if (order.subtotal != null) {
      return Number(order.subtotal);
    }
    var sum = 0;
    (order.items || []).forEach(function (item) {
      sum += Number(item.price) * getQty(item);
    });
    return sum;
  }

  function getDiscount(order) {
    return Number(order.discount || 0);
  }

  function getTotal(order) {
    return order.total != null
      ? Number(order.total)
      : getSubtotal(order) - getDiscount(order);
  }

  // Turns the order status into the badge label + color.
  // New orders start as "pending"; the staff page changes it later.
  function getStatusInfo(status) {
    var value = String(status || "").toLowerCase();

    if (
      [
        "completed",
        "complete",
        "done",
        "claimed",
        "picked up",
        "picked-up",
      ].indexOf(value) !== -1
    ) {
      return { label: "Completed", cls: "completed" };
    }
    if (["cancelled", "canceled", "rejected"].indexOf(value) !== -1) {
      return { label: "Cancelled", cls: "cancelled" };
    }
    if (
      ["preparing", "in progress", "in-progress", "processing"].indexOf(
        value,
      ) !== -1
    ) {
      return { label: "Preparing", cls: "preparing" };
    }
    // "pending", empty, or anything unknown
    return { label: "Pending", cls: "pending" };
  }

  // "Medium • 50% sugar • Qty 1"
  function getItemMeta(item) {
    var parts = [];
    if (item.size) {
      parts.push(item.size);
    }
    if (item.sugar) {
      parts.push(item.sugar + " sugar");
    }
    parts.push("Qty " + getQty(item));
    return parts.join(" \u2022 ");
  }

  function getImage(item) {
    return item && item.image ? item.image : "assets/dblogo.png";
  }

  // "Pickup • 2:30 PM"
  function getTypeText(order) {
    var pickup = formatPickup(order.pickupTime);
    return pickup ? "Pickup \u2022 " + pickup : "Pickup";
  }

  // ---------- reading the orders ----------

  // Reads the orders saved by checkout, newest first.
  function loadOrders() {
    // receipt.php?orders=none shows the empty page on purpose
    if (page.dataset.forceEmpty === "1") {
      return [];
    }
        // Only the orders of the logged-in customer (LOGGED_IN_USER is printed by receipt.php)
    var user = typeof LOGGED_IN_USER !== "undefined" ? LOGGED_IN_USER : null;
    if (!user || typeof getOrdersForCustomer !== "function") {
      return [];
    }

    var saved = getOrdersForCustomer(user.email);
    if (!Array.isArray(saved)) {
      return [];
    }

    return saved.slice().sort(function (a, b) {
      return new Date(b.createdAt) - new Date(a.createdAt);
    });
  }

  function findOrder(orderId) {
    for (var i = 0; i < orders.length; i++) {
      if (String(orders[i].id) === String(orderId)) {
        return orders[i];
      }
    }
    return null;
  }

  // ---------- building the HTML ----------

  function buildCard(order) {
    var status = getStatusInfo(order.status);
    var id = escapeHtml(order.id);
    var first = (order.items && order.items[0]) || null;
    var isSelected = String(order.id) === String(selectedId);

    return (
      "" +
      "<li>" +
      '<a class="mo-card' +
      (isSelected ? " is-selected" : "") +
      '"' +
      ' href="' +
      escapeHtml(page.dataset.pageUrl || "receipt.php") +
      "?order=" +
      encodeURIComponent(order.id) +
      '"' +
      ' data-order-id="' +
      id +
      '"' +
      ' aria-controls="receipt-' +
      id +
      '"' +
      (isSelected ? ' aria-current="true"' : "") +
      ">" +
      '<span class="mo-card__thumb"><img src="' +
      escapeHtml(getImage(first)) +
      '" alt=""></span>' +
      '<span class="mo-card__body">' +
      '<span class="mo-card__top">' +
      '<span class="mo-card__id">Order #' +
      id +
      "</span>" +
      '<span class="mo-badge mo-badge--' +
      status.cls +
      '">' +
      status.label +
      "</span>" +
      "</span>" +
      '<span class="mo-card__date">' +
      escapeHtml(formatDate(order.createdAt)) +
      " &bull; " +
      escapeHtml(formatClock(order.createdAt)) +
      "</span>" +
      '<span class="mo-card__count">' +
      itemLabel(countItems(order)) +
      "</span>" +
      "</span>" +
      "</a>" +
      "</li>"
    );
  }

  function buildReceipt(order) {
    var id = escapeHtml(order.id);
    var isSelected = String(order.id) === String(selectedId);

    var itemRows = (order.items || [])
      .map(function (item) {
        var lineTotal = Number(item.price) * getQty(item);
        return (
          "" +
          '<li class="mo-item">' +
          '<img class="mo-item__thumb" src="' +
          escapeHtml(getImage(item)) +
          '" alt="">' +
          '<span class="mo-item__info">' +
          '<span class="mo-item__name">' +
          escapeHtml(item.name) +
          "</span>" +
          '<span class="mo-item__meta">' +
          escapeHtml(getItemMeta(item)) +
          "</span>" +
          "</span>" +
          '<span class="mo-item__price">' +
          escapeHtml(formatPeso(lineTotal)) +
          "</span>" +
          "</li>"
        );
      })
      .join("");

    return (
      "" +
      '<article class="mo-receipt" id="receipt-' +
      id +
      '" data-order-id="' +
      id +
      '"' +
      (isSelected ? "" : " hidden") +
      ">" +
      '<header class="mo-receipt__head">' +
      '<img class="mo-receipt__logo" src="assets/dblogo.png" alt="DejaBrew logo">' +
      '<p class="mo-receipt__brand">DejaBrew</p>' +
      "</header>" +
      '<div class="mo-receipt__section mo-receipt__details">' +
      '<h2 class="mo-receipt__title">Order Details</h2>' +
      '<p class="mo-receipt__order">Order #' +
      id +
      "</p>" +
      '<p class="mo-receipt__when">' +
      escapeHtml(formatDate(order.createdAt)) +
      " &bull; " +
      escapeHtml(formatClock(order.createdAt)) +
      "</p>" +
      '<p class="mo-receipt__type">' +
      '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' +
      '<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg> ' +
      escapeHtml(getTypeText(order)) +
      "</p>" +
      "</div>" +
      '<div class="mo-receipt__section">' +
      '<h3 class="mo-receipt__heading">ITEMS</h3>' +
      '<ul class="mo-items">' +
      itemRows +
      "</ul>" +
      "</div>" +
      '<div class="mo-receipt__section">' +
      '<h3 class="mo-receipt__heading">PAYMENT SUMMARY</h3>' +
      '<dl class="mo-summary">' +
      '<div class="mo-summary__row"><dt>Subtotal:</dt><dd>' +
      escapeHtml(formatPeso(getSubtotal(order))) +
      "</dd></div>" +
      '<div class="mo-summary__row"><dt>Discount:</dt><dd>' +
      escapeHtml(formatPeso(getDiscount(order))) +
      "</dd></div>" +
      "</dl>" +
      '<div class="mo-total">' +
      '<span class="mo-total__label">Total:</span>' +
      '<span class="mo-total__amount">' +
      escapeHtml(formatPeso(getTotal(order))) +
      "</span>" +
      "</div>" +
      "</div>" +
      '<button class="mo-download" type="button" data-order-id="' +
      id +
      '">' +
      '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' +
      '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M7 10l5 5 5-5"/><path d="M12 15V3"/></svg>' +
      " Download Receipt" +
      "</button>" +
      "</article>"
    );
  }

  // Plain-text receipt for the Download button.
  function buildReceiptText(order) {
    var lines = [];
    lines.push("DEJABREW");
    lines.push("Order Receipt");
    lines.push("--------------------------------");
    lines.push("Order #" + order.id);
    lines.push(
      "Date: " +
        formatDate(order.createdAt) +
        " " +
        formatClock(order.createdAt),
    );
    lines.push("Type: " + getTypeText(order));
    lines.push("Status: " + getStatusInfo(order.status).label);
    if (order.customerName) {
      lines.push("Customer: " + order.customerName);
    }
    if (order.paymentMethod) {
      lines.push("Payment: " + order.paymentMethod);
    }
    lines.push("--------------------------------");

    (order.items || []).forEach(function (item) {
      var lineTotal = Number(item.price) * getQty(item);
      lines.push(
        item.name + " (" + getItemMeta(item) + ")  " + formatPeso(lineTotal),
      );
    });

    lines.push("--------------------------------");
    lines.push("Subtotal: " + formatPeso(getSubtotal(order)));
    lines.push("Discount: " + formatPeso(getDiscount(order)));
    lines.push("TOTAL: " + formatPeso(getTotal(order)));
    if (order.note) {
      lines.push("Note: " + order.note);
    }
    lines.push("--------------------------------");
    lines.push("Thank you for ordering at DejaBrew!");

    return lines.join("\r\n");
  }

  // ---------- showing everything ----------

  // Reads the orders again and redraws the page.
  function render() {
    orders = loadOrders();

    // no orders: show the "No orders yet" page
    if (orders.length === 0) {
      layout.hidden = true;
      emptyBox.hidden = false;
      page.classList.add("mo-page--empty");
      list.innerHTML = "";
      receiptBox.innerHTML = "";
      return;
    }

    layout.hidden = false;
    emptyBox.hidden = true;
    page.classList.remove("mo-page--empty");

    // keep the selected order if it still exists, otherwise use ?order=, otherwise the newest
    var wanted = selectedId || page.dataset.initialOrder || "";
    selectedId = findOrder(wanted)
      ? String(findOrder(wanted).id)
      : String(orders[0].id);

    list.innerHTML = orders.map(buildCard).join("");
    receiptBox.innerHTML = orders.map(buildReceipt).join("");
  }

  // Highlights one card and shows only that order's receipt.
  function selectOrder(orderId, shouldScroll) {
    selectedId = String(orderId);

    list.querySelectorAll(".mo-card").forEach(function (card) {
      var isActive = card.dataset.orderId === selectedId;
      card.classList.toggle("is-selected", isActive);
      if (isActive) {
        card.setAttribute("aria-current", "true");
      } else {
        card.removeAttribute("aria-current");
      }
    });

    receiptBox.querySelectorAll(".mo-receipt").forEach(function (receipt) {
      receipt.hidden = receipt.dataset.orderId !== selectedId;
    });

    // keep the address in sync so a refresh opens the same order
    try {
      history.replaceState(
        null,
        "",
        "?order=" + encodeURIComponent(selectedId),
      );
    } catch (error) {
      // some browsers block this on local files; the page still works
    }

    // on tablet / phone the receipt is under the list, so bring it into view
    if (shouldScroll && window.matchMedia("(max-width: 900px)").matches) {
      var receipt = receiptBox.querySelector(
        '.mo-receipt[data-order-id="' + selectedId + '"]',
      );
      var reduceMotion = window.matchMedia(
        "(prefers-reduced-motion: reduce)",
      ).matches;
      if (receipt) {
        receipt.scrollIntoView({
          behavior: reduceMotion ? "auto" : "smooth",
          block: "start",
        });
      }
    }
  }

  // Saves the receipt text as a .txt file.
  function downloadReceipt(orderId) {
    var order = findOrder(orderId);
    if (!order) {
      return;
    }

    var blob = new Blob([buildReceiptText(order)], {
      type: "text/plain;charset=utf-8",
    });
    var url = URL.createObjectURL(blob);

    var link = document.createElement("a");
    link.href = url;
    link.download = "DejaBrew-Receipt-" + order.id + ".txt";
    document.body.appendChild(link);
    link.click();
    link.remove();

    setTimeout(function () {
      URL.revokeObjectURL(url);
    }, 0);
  }

  // ---------- events ----------

  // Clicking a card selects it (the link still works as a normal page link without JavaScript).
  list.addEventListener("click", function (event) {
    var card = event.target.closest(".mo-card");
    if (!card) {
      return;
    }
    if (event.metaKey || event.ctrlKey || event.shiftKey) {
      return; // let "open in new tab" work normally
    }
    event.preventDefault();
    selectOrder(card.dataset.orderId, true);
  });

  // One listener for every Download Receipt button.
  receiptBox.addEventListener("click", function (event) {
    var button = event.target.closest(".mo-download");
    if (button) {
      downloadReceipt(button.dataset.orderId);
    }
  });

  // An order changed in another tab (for example the staff page): redraw.
  window.addEventListener("storage", render);

  // Coming back with the Back button: redraw with the latest orders.
  window.addEventListener("pageshow", function (event) {
    if (event.persisted) {
      render();
    }
  });

  render();
})();
