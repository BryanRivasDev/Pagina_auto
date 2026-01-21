<?php
require_once 'config.php';

try {
    // 1. Add image_path to categories if it doesn't exist
    $stmt = $pdo->query("SHOW COLUMNS FROM categories LIKE 'image_path'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE categories ADD COLUMN image_path VARCHAR(255) DEFAULT NULL");
        echo "Added 'image_path' column to 'categories' table.<br>";
    } else {
        echo "'image_path' column already exists in 'categories'.<br>";
    }

    // 2. Create brands table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS brands (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        logo_path VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "Table 'brands' checked/created.<br>";

    echo "Database update completed successfully.";

} catch (PDOException $e) {
    echo "Error updating database: " . $e->getMessage();
}
?>
