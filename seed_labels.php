<?php
require_once 'config.php';

try {
    // Insert "Nuevo" Car
    $stmt = $pdo->prepare("INSERT INTO cars (make, model, year, price, mileage, description, status_label, category, show_price) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute(['TestMake', 'ModelNew', 2025, 25000, 0, 'This is a test car for NEW label verification.', 'Nuevo', 'Sedan', 1]);
    
    // Insert "Oferta" Car
    $stmt = $pdo->prepare("INSERT INTO cars (make, model, year, price, mileage, description, status_label, category, show_price) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute(['TestMake', 'ModelPromo', 2024, 18000, 5000, 'This is a test car for PROMO label verification.', 'Oferta', 'SUV', 1]);

    echo "Test cars seeded successfully.";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
