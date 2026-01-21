<?php
// Database Configuration
if ($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == '127.0.0.1') {
    // Local XAMPP Credentials
    define('DB_SERVER', 'localhost');
    define('DB_USERNAME', 'root');
    define('DB_PASSWORD', '');
    define('DB_NAME', 'pagina_autos_db');
} else {
    // InfinityFree Production Credentials
    define('DB_SERVER', 'sql208.infinityfree.com');
    define('DB_USERNAME', 'if0_40835271');
    define('DB_PASSWORD', 'VB6nmtMnSxbxGn8');
    define('DB_NAME', 'if0_40835271_pagina_autos_db');
}

// Attempt to connect to MySQL database
try {
    $pdo = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME, DB_USERNAME, DB_PASSWORD);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("set names utf8mb4");

    // Auto-fix: Ensure site_settings exists (Key-Value Store Schema)
    $pdo->exec("CREATE TABLE IF NOT EXISTS site_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) UNIQUE NOT NULL,
        setting_value TEXT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    // Auto-fix: Add Category to cars if missing
    try {
        $pdo->query("SELECT category FROM cars LIMIT 1");
    } catch (Exception $e) {
        $pdo->exec("ALTER TABLE cars ADD COLUMN category VARCHAR(50) DEFAULT 'Sedan'");
    }

    // Auto-fix: Add Status Label to cars if missing
    try {
        $pdo->query("SELECT status_label FROM cars LIMIT 1");
    } catch (Exception $e) {
        $pdo->exec("ALTER TABLE cars ADD COLUMN status_label VARCHAR(50) DEFAULT NULL");
    }

    // Auto-fix: Create car_images table
    $pdo->exec("CREATE TABLE IF NOT EXISTS car_images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        car_id INT NOT NULL,
        image_path VARCHAR(255) NOT NULL,
        FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE
    ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    // Auto-fix: Create categories table
    $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE
    ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    // Seed default categories if empty
    $stmt = $pdo->query("SELECT COUNT(*) FROM categories");
    if ($stmt->fetchColumn() == 0) {
        $defaults = ['Sedan', 'SUV', 'Camioneta', 'Hatchback', 'Deportivo', 'Coupe', 'Minivan', 'Convertible'];
        $insert = $pdo->prepare("INSERT INTO categories (name) VALUES (:name)");
        foreach ($defaults as $cat) {
            $insert->execute([':name' => $cat]);
        }
    }

    // Auto-fix: Create carousel_slides table
    $pdo->exec("CREATE TABLE IF NOT EXISTS carousel_slides (
        id INT AUTO_INCREMENT PRIMARY KEY,
        image_path VARCHAR(255) NOT NULL,
        title VARCHAR(100) DEFAULT '',
        subtitle VARCHAR(255) DEFAULT ''
    ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    // Auto-fix: Create navbar_links table
    $pdo->exec("CREATE TABLE IF NOT EXISTS navbar_links (
        id INT AUTO_INCREMENT PRIMARY KEY,
        label VARCHAR(100) NOT NULL,
        url VARCHAR(255) NOT NULL,
        order_index INT DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1
    ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    // Seed default navbar links if empty
    $stmt = $pdo->query("SELECT COUNT(*) FROM navbar_links");
    if ($stmt->fetchColumn() == 0) {
        $defaults = [
            ['Inventario', 'index.php', 1],
            ['Nosotros', 'nosotros.php', 2],
            ['Contactenos', 'contact.php', 3]
        ];
        $insert = $pdo->prepare("INSERT INTO navbar_links (label, url, order_index) VALUES (?, ?, ?)");
        foreach ($defaults as $link) {
            $insert->execute($link);
        }
    }

    // Auto-fix: Add is_sold column to cars table
    try {
        $pdo->query("SELECT is_sold FROM cars LIMIT 1");
    } catch (PDOException $e) {
        $pdo->exec("ALTER TABLE cars ADD COLUMN is_sold TINYINT(1) DEFAULT 0");
    }

    // Check for is_visible in navbar_links
    $check = $pdo->query("SHOW COLUMNS FROM navbar_links LIKE 'is_visible'");
    if (!$check->fetch()) {
        $pdo->exec("ALTER TABLE navbar_links ADD COLUMN is_visible TINYINT(1) DEFAULT 1");
    }



    // Auto-create contact_messages table
    $pdo->exec("CREATE TABLE IF NOT EXISTS contact_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        phone VARCHAR(50),
        message TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");


} catch(PDOException $e) {
    die("ERROR: Could not connect. " . $e->getMessage());
}
?>
