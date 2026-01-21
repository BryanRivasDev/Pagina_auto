-- Create Database
CREATE DATABASE IF NOT EXISTS pagina_autos_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pagina_autos_db;

-- Users Table (for Admin)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Cars Table
CREATE TABLE IF NOT EXISTS cars (
    id INT AUTO_INCREMENT PRIMARY KEY,
    make VARCHAR(50) NOT NULL,
    model VARCHAR(50) NOT NULL,
    year INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    mileage INT,
    description TEXT,
    image_path VARCHAR(255),
    show_price TINYINT(1) DEFAULT 1, -- 1 for Show, 0 for Hide (Ask for Price)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert Default Admin User (Password: admin123)
-- bcrypt hash for 'admin123'
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Site Settings Table
CREATE TABLE IF NOT EXISTS site_settings (
    id INT PRIMARY KEY DEFAULT 1,
    site_name VARCHAR(100) DEFAULT 'AutoSales',
    navbar_title VARCHAR(100) DEFAULT 'AUTOSALES',
    hero_title VARCHAR(255) DEFAULT 'Encuentra tu Auto Ideal',
    hero_subtitle VARCHAR(255) DEFAULT 'Calidad, Confianza y los Mejores Precios del Mercado',
    logo_path VARCHAR(255),
    contact_phone VARCHAR(50) DEFAULT '+504 9999-9999',
    contact_email VARCHAR(100) DEFAULT 'info@autosales.com',
    contact_address VARCHAR(255) DEFAULT 'Av. Circunvalación, San Pedro Sula',
    whatsapp_number VARCHAR(50) DEFAULT '50499999999'
);

INSERT INTO site_settings (id) VALUES (1);
