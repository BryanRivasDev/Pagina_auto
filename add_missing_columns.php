<?php
require 'config.php';

try {
    // Add 'doors' column
    $pdo->exec("ALTER TABLE cars ADD COLUMN doors INT DEFAULT 4");
    echo "Added 'doors' column.<br>";
} catch (PDOException $e) {
    echo "Error adding 'doors': " . $e->getMessage() . "<br>";
}

try {
    // Add 'steering' column
    $pdo->exec("ALTER TABLE cars ADD COLUMN steering VARCHAR(50) DEFAULT 'Hidráulica'");
    echo "Added 'steering' column.<br>";
} catch (PDOException $e) {
    echo "Error adding 'steering': " . $e->getMessage() . "<br>";
}
?>
