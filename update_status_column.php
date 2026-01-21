<?php
require_once 'config.php';

try {
    // Add status_label column if it doesn't exist
    $sql = "ALTER TABLE cars ADD COLUMN status_label VARCHAR(50) DEFAULT NULL";
    $pdo->exec($sql);
    echo "Column 'status_label' added successfully to 'cars' table.";
} catch (PDOException $e) {
    // Ignore error if column already exists (common issue with simple scripts)
    if (strpos($e->getMessage(), "Duplicate column name") !== false) {
        echo "Column 'status_label' already exists.";
    } else {
        echo "Error: " . $e->getMessage();
    }
}
?>
