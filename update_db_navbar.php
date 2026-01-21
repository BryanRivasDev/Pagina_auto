<?php
require_once 'config.php';

try {
    // Check if column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM navbar_links LIKE 'is_visible'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE navbar_links ADD COLUMN is_visible TINYINT(1) DEFAULT 1");
        echo "Column 'is_visible' added successfully.";
    } else {
        echo "Column 'is_visible' already exists.";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
