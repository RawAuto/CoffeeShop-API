-- CoffeeShop API Database Schema
-- This file runs automatically when the MySQL container is first created

USE coffeeshop;

-- Drinks table: stores available drink types and their rules
CREATE TABLE IF NOT EXISTS drinks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    type ENUM('coffee', 'tea') NOT NULL,
    base_price DECIMAL(5,2) NOT NULL,
    has_milk BOOLEAN DEFAULT FALSE,
    allowed_sizes JSON NOT NULL COMMENT 'Array of allowed size values: small, medium, large',
    components JSON NOT NULL COMMENT 'Array of drink components/ingredients',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Orders table: stores customer orders
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(255) NOT NULL,
    status ENUM('pending', 'preparing', 'ready', 'completed', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Order items table: individual drinks within an order
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    drink_id INT NOT NULL,
    size ENUM('small', 'medium', 'large') NOT NULL,
    quantity INT DEFAULT 1,
    cup_text VARCHAR(255) COMMENT 'Custom text to write on the cup',
    price DECIMAL(5,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (drink_id) REFERENCES drinks(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed data: Available drinks
INSERT INTO drinks (name, slug, type, base_price, has_milk, allowed_sizes, components) VALUES
    ('Espresso', 'espresso', 'coffee', 2.50, FALSE, '["small"]', '["shot of coffee"]'),
    ('Latte', 'latte', 'coffee', 3.50, TRUE, '["small", "medium"]', '["shot of coffee", "steamed milk"]'),
    ('Americano', 'americano', 'coffee', 3.00, FALSE, '["small", "medium", "large"]', '["shot of coffee", "hot water"]'),
    ('English Tea', 'english-tea', 'tea', 2.00, TRUE, '["medium", "large"]', '["tea bag", "hot water", "milk"]');

-- Index for common queries
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_orders_created ON orders(created_at);
CREATE INDEX idx_order_items_order ON order_items(order_id);

