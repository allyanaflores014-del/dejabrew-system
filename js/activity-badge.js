(function () {
  var SEEN_PREFIX = "dejabrew_seen_status_";

  function getUser() {
    return typeof LOGGED_IN_USER !== "undefined" ? LOGGED_IN_USER : null;
  }

  function getOrdersList() {
    try {
      var user = getUser();
      if (user && typeof getOrdersForCustomer === "function") {
        return getOrdersForCustomer(user.email) || [];
      }
    } catch (e) {}
    return [];
  }

  // Per-customer ang tala, para hindi maghalo kapag magkaibang account
  function seenKey() {
    var user = getUser();
    return SEEN_PREFIX + (user && user.email ? user.email : "guest");
  }

  function getSeen() {
    try {
      return JSON.parse(localStorage.getItem(seenKey())) || {};
    } catch (e) {
      return {};
    }
  }

  function saveSeen(seen) {
    localStorage.setItem(seenKey(), JSON.stringify(seen));
  }

  function normalize(status) {
    return String(status || "pending").toLowerCase();
  }

  function getUpdatedIds() {
    var seen = getSeen();
    var updated = [];
    var changed = false;

    getOrdersList().forEach(function (o) {
      var id = String(o.id);
      var status = normalize(o.status);

      if (!(id in seen)) {
        seen[id] = status; // bagong order: itala lang
        changed = true;
      } else if (seen[id] !== status) {
        updated.push(id);
      }
    });

    if (changed) saveSeen(seen);
    return updated;
  }

  function refresh() {
    var updated = getUpdatedIds();

    var badge = document.getElementById("activity-count");
    if (badge) {
      badge.textContent = updated.length > 9 ? "9+" : updated.length;
      badge.hidden = updated.length === 0;
    }

    document.querySelectorAll(".mo-card[data-order-id]").forEach(function (card) {
      card.classList.toggle("is-new", updated.indexOf(card.dataset.orderId) !== -1);
    });
  }

  window.markOrderSeen = function (id) {
    var order = getOrdersList().find(function (o) {
      return String(o.id) === String(id);
    });
    if (!order) return;
    var seen = getSeen();
    seen[String(id)] = normalize(order.status);
    saveSeen(seen);
    refresh();
  };

  window.refreshActivityBadge = refresh;

  document.addEventListener("DOMContentLoaded", refresh);
  window.addEventListener("storage", refresh);
  setInterval(refresh, 3000);
})();