<?php
require_once 'config.php';

// Check Categories
$cats = $pdo->query("SELECT count(*) FROM categories")->fetchColumn();
echo "Categories count: $cats\n";

if ($cats == 0) {
    $pdo->exec("INSERT INTO categories (name) VALUES ('SUV'), ('Sedan'), ('Pickup'), ('Camioneta')");
    echo "Seeded Categories.\n";
}

// Check Brands
try {
    $brands = $pdo->query("SELECT count(*) FROM brands")->fetchColumn();
    echo "Brands count: $brands\n";

    if ($brands == 0) {
        $pdo->exec("INSERT INTO brands (name) VALUES ('Toyota'), ('Honda'), ('Nissan'), ('Ford'), ('Hyundai')");
        echo "Seeded Brands.\n";
    }
} catch (Exception $e) {
    echo "Error querying brands: " . $e->getMessage() . "\n";
}
?>
