<?php
require_once 'config.php';

// REPLACE 'uploads/' with 'assets/images/' in cars table
$sql_cars = "UPDATE cars SET image_path = REPLACE(image_path, 'uploads/', 'assets/images/') WHERE image_path LIKE 'uploads/%'";
$stmt = $pdo->prepare($sql_cars);
$stmt->execute();
echo "Updated cars table paths: " . $stmt->rowCount() . " rows affected.<br>";

// REPLACE 'uploads/' with 'assets/images/' in car_images table (gallery)
$sql_gallery = "UPDATE car_images SET image_path = REPLACE(image_path, 'uploads/', 'assets/images/') WHERE image_path LIKE 'uploads/%'";
$stmt_g = $pdo->prepare($sql_gallery);
$stmt_g->execute();
echo "Updated car_images table paths: " . $stmt_g->rowCount() . " rows affected.<br>";

echo "Database Paths Update Complete!";
?>
