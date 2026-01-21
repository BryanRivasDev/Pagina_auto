<?php
require_once 'config.php';

// IMAGE PATHS
$logo_path = 'assets/images/brand_logo.png';
$images = [
    'Pickup' => 'assets/images/car_pickup.png',
    'Sedan' => 'assets/images/car_sedan.png',
    'SUV' => 'assets/images/car_suv.png',
    'Hatchback' => 'assets/images/car_hatchback.png',
    'Sport' => 'assets/images/car_sedan.png' // Fallback to Sedan or use SUV if closer visually, user failed generation for Sport
    // Actually, I can map Categories to these keys.
];

// 1. UPDATE BRANDS LOGO
$sql_brands = "UPDATE brands SET logo_path = :logo";
$stmt_brands = $pdo->prepare($sql_brands);
$stmt_brands->execute([':logo' => $logo_path]);
echo "Updated Brand Logos.<br>";

// 2. UPDATE CAR IMAGES BASED ON CATEGORY
// Categories in DB: Camioneta, Sedan, SUV, Hatchback, Deportivo
/*
Mapping:
Camioneta -> car_pickup.png
Sedan -> car_sedan.png
SUV -> car_suv.png
Hatchback -> car_hatchback.png
Deportivo -> car_sedan.png (Fallback)
*/

$updates = [
    'Camioneta' => 'assets/images/car_pickup.png',
    'Sedan' => 'assets/images/car_sedan.png',
    'SUV' => 'assets/images/car_suv.png',
    'Hatchback' => 'assets/images/car_hatchback.png',
    'Deportivo' => 'assets/images/car_sedan.png' 
];

$sql_cars = "UPDATE cars SET image_path = :img WHERE category = :cat";
$stmt_cars = $pdo->prepare($sql_cars);

foreach ($updates as $cat => $img) {
    // Verify file exists? We just copied them, so assuming yes.
    $stmt_cars->execute([':img' => $img, ':cat' => $cat]);
    echo "Updated cars in category '$cat' to use image '$img'.<br>";
}

echo "Images Update Complete!";
?>
