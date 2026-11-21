CREATE DATABASE IF NOT EXISTS dejabrew CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE dejabrew;

-- ========== USERS (for the login branch) ==========
CREATE TABLE users (
    user_id    INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(20)  NOT NULL UNIQUE,
    email      VARCHAR(100) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    role       ENUM('customer','staff') NOT NULL DEFAULT 'customer',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE login_attempts (
    attempt_id    INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    email         VARCHAR(100) NOT NULL,
    ip_address    VARCHAR(45)  NOT NULL,
    is_successful TINYINT(1)   NOT NULL DEFAULT 0,
    attempted_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email_time (email, attempted_at)
) ENGINE=InnoDB;

-- ========== MENU (my module) ==========
CREATE TABLE categories (
    category_id   INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(30) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE products (
    product_id   INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    category_id  INT UNSIGNED NOT NULL,
    product_name VARCHAR(60)  NOT NULL,
    description  TEXT         NOT NULL,
    image        VARCHAR(255) NOT NULL,
    base_price   DECIMAL(10,2) NOT NULL,
    is_available TINYINT(1)   NOT NULL DEFAULT 1,
    created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_products_category (category_id),
    CONSTRAINT fk_products_category FOREIGN KEY (category_id)
        REFERENCES categories (category_id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE product_sizes (
    size_id          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    size_name        VARCHAR(20) NOT NULL UNIQUE,
    additional_price DECIMAL(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB;

-- ========== CART ==========
CREATE TABLE cart (
    cart_id    INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_cart_user (user_id),              -- only ONE cart per customer
    CONSTRAINT fk_cart_user FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE cart_items (
    cart_item_id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    cart_id      INT UNSIGNED NOT NULL,
    product_id   INT UNSIGNED NOT NULL,
    size_id      INT UNSIGNED NOT NULL,
    quantity     INT UNSIGNED NOT NULL DEFAULT 1,
    sugar_level  VARCHAR(10)  NOT NULL DEFAULT '50%',
    unit_price   DECIMAL(10,2) NOT NULL,
    UNIQUE KEY uq_cart_line (cart_id, product_id, size_id, sugar_level),
    CONSTRAINT fk_ci_cart    FOREIGN KEY (cart_id)    REFERENCES cart (cart_id)               ON DELETE CASCADE,
    CONSTRAINT fk_ci_product FOREIGN KEY (product_id) REFERENCES products (product_id)        ON DELETE CASCADE,
    CONSTRAINT fk_ci_size    FOREIGN KEY (size_id)    REFERENCES product_sizes (size_id)      ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ========== ORDERS ==========
CREATE TABLE vouchers (
    voucher_id    INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    voucher_code  VARCHAR(20) NOT NULL UNIQUE,
    discount_rate DECIMAL(4,2) NOT NULL,            -- 0.05 means 5%
    minimum_order DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    is_active     TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE orders (
    order_id       INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id        INT UNSIGNED NOT NULL,
    voucher_id     INT UNSIGNED NULL,
    order_date     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    pickup_time    DATETIME NOT NULL,
    payment_method VARCHAR(30) NOT NULL DEFAULT 'Pay upon pickup',
    order_note     VARCHAR(255) NULL,
    subtotal       DECIMAL(10,2) NOT NULL,
    discount       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total_amount   DECIMAL(10,2) NOT NULL,
    order_status   ENUM('pending','preparing','completed','cancelled') NOT NULL DEFAULT 'pending',
    INDEX idx_orders_user (user_id),
    INDEX idx_orders_status (order_status),
    CONSTRAINT fk_orders_user    FOREIGN KEY (user_id)    REFERENCES users (user_id)       ON DELETE RESTRICT,
    CONSTRAINT fk_orders_voucher FOREIGN KEY (voucher_id) REFERENCES vouchers (voucher_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- product_id is NULL-able so a product can be deleted without losing order history
CREATE TABLE order_items (
    order_item_id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    order_id      INT UNSIGNED NOT NULL,
    product_id    INT UNSIGNED NULL,
    product_name  VARCHAR(60)  NOT NULL,
    product_image VARCHAR(255) NULL,
    size          VARCHAR(20)  NOT NULL,
    unit_price    DECIMAL(10,2) NOT NULL,
    quantity      INT UNSIGNED NOT NULL,
    sugar_level   VARCHAR(10)  NOT NULL,
    subtotal      DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_oi_order   FOREIGN KEY (order_id)   REFERENCES orders (order_id)     ON DELETE CASCADE,
    CONSTRAINT fk_oi_product FOREIGN KEY (product_id) REFERENCES products (product_id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE order_status_history (
    history_id      INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    order_id        INT UNSIGNED NOT NULL,
    staff_id        INT UNSIGNED NULL,
    previous_status VARCHAR(20) NULL,
    new_status      VARCHAR(20) NOT NULL,
    changed_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_osh_order FOREIGN KEY (order_id) REFERENCES orders (order_id) ON DELETE CASCADE,
    CONSTRAINT fk_osh_staff FOREIGN KEY (staff_id) REFERENCES users (user_id)   ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE notifications (
    notification_id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED NOT NULL,
    order_id        INT UNSIGNED NULL,
    type            VARCHAR(30)  NOT NULL,
    message         VARCHAR(255) NOT NULL,
    is_read         TINYINT(1)   NOT NULL DEFAULT 0,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    read_at         DATETIME NULL,
    INDEX idx_notif_user (user_id, is_read),
    CONSTRAINT fk_notif_user  FOREIGN KEY (user_id)  REFERENCES users (user_id)   ON DELETE CASCADE,
    CONSTRAINT fk_notif_order FOREIGN KEY (order_id) REFERENCES orders (order_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ========== SEED DATA ==========
INSERT INTO product_sizes (size_id, size_name, additional_price) VALUES
    (1, 'Small', 0.00), (2, 'Medium', 15.00), (3, 'Large', 30.00);

INSERT INTO categories (category_id, category_name) VALUES
    (1, 'Iced Coffee'), (2, 'Hot Coffee'), (3, 'Frappe');

-- Same ids (1-10) as the old products.php, so hero cups and New Series keep working
INSERT INTO products (product_id, category_id, product_name, description, image, base_price) VALUES
(1, 1, 'Classic Iced Coffee',  'Smooth, refreshing coffee served over ice for a simple everyday brew.', 'assets/Classic.png', 89),
(2, 1, 'Caramel Macchiato',    'Espresso poured over cold milk and finished with a ribbon of buttery caramel.', 'assets/CaramelMacchiato.png', 109),
(3, 1, 'Spanish Latte',        'Espresso and milk sweetened with condensed milk for a rich, creamy sip.', 'assets/SpanishLatte.png', 109),
(4, 1, 'Caramel Latte',        'Smooth espresso and milk sweetened with a drizzle of golden caramel.', 'assets/CaramelLatte.png', 99),
(5, 1, 'Vanilla Latte',        'Creamy espresso and milk with a soft, sweet vanilla finish.', 'assets/VanillaLatte.png', 99),
(6, 2, 'Classic Americano',    'Bold espresso topped with hot water for a clean, straightforward cup.', 'assets/Americano.png', 79),
(7, 2, 'Cafe Latte',           'Espresso with steamed milk and a light layer of foam. Simple and smooth.', 'assets/CafeLatte.png', 89),
(8, 2, 'Mocha Latte',          'Espresso, steamed milk, and chocolate for a cozy, comforting cup.', 'assets/MochaLatte.png', 99),
(9, 3, 'Chocolate Frappe',     'Blended coffee and chocolate with ice, topped with whipped cream.', 'assets/ChocolateFrappe.png', 119),
(10, 3, 'Java Chip Frappe',    'Icy blended coffee with chocolate chips and a swirl of whipped cream.', 'assets/JavaChipFrappe.png', 129);