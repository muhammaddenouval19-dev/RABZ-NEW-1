CREATE DATABASE IF NOT EXISTS rabz_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE rabz_db;

CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    username VARCHAR(80) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'customer',
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    city VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE drivers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    vehicle_name VARCHAR(150) DEFAULT NULL,
    license_number VARCHAR(50) DEFAULT NULL,
    status ENUM('ready', 'busy', 'offline') DEFAULT 'offline',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('income', 'expense') NOT NULL,
    category VARCHAR(100) NOT NULL,
    description TEXT DEFAULT NULL,
    amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    driver_id INT DEFAULT NULL,
    order_code VARCHAR(50) NOT NULL UNIQUE,
    pickup_location VARCHAR(255) NOT NULL,
    destination_location VARCHAR(255) NOT NULL,
    total_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    status ENUM('pending', 'accepted', 'on_the_way', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE SET NULL
);

CREATE TABLE approvals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    approval_type VARCHAR(80) NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    note TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE fare_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    base_fare DECIMAL(15,2) NOT NULL DEFAULT 5000,
    rate_per_km DECIMAL(15,2) NOT NULL DEFAULT 2500,
    operational_fee DECIMAL(15,2) NOT NULL DEFAULT 2000,
    driver_split_pct INT NOT NULL DEFAULT 80,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE app_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS owner_route_configs (
    user_id INT NOT NULL PRIMARY KEY,
    vehicle_type VARCHAR(30) NOT NULL DEFAULT 'motor',
    vehicle_speed DECIMAL(10,2) NOT NULL DEFAULT 45,
    pickup_label VARCHAR(255) DEFAULT NULL,
    pickup_lat DECIMAL(11,8) DEFAULT NULL,
    pickup_lng DECIMAL(11,8) DEFAULT NULL,
    destination_label VARCHAR(255) DEFAULT NULL,
    destination_lat DECIMAL(11,8) DEFAULT NULL,
    destination_lng DECIMAL(11,8) DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

INSERT INTO roles (name) VALUES
('owner'),
('admin'),
('driver'),
('customer');

INSERT INTO fare_settings (base_fare, rate_per_km, operational_fee, driver_split_pct)
VALUES (5000, 2500, 2000, 80);

INSERT INTO app_settings (setting_key, setting_value) VALUES
('app_name', 'RABZ'),
('apk_name', 'RABZ.apk'),
('theme_mode', 'dark');

INSERT INTO users (full_name, email, username, password_hash, role, status) VALUES
('Owner RABZ', 'owner@rabz.com', 'owner', '$2y$10$fjAqmMoFrVTKvPNYSPQUc.lzvtR9KK.d8Pu6VS4Ch06f.tCM0e2z2', 'owner', 'active');
