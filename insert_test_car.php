<?php
require_once 'config.php';

// Data for test car
$make = 'Auto de Prueba';
$model = 'Tal Cual'; // "que se llame tal cual"
$year = 2024;
$price = 15000.00;
$mileage = 100;
$description = 'Este es un auto de prueba agregado automáticamente.';
$image_path = ''; // No image as requested
$show_price = 1;
$category = 'Sedan'; // Default category
$status_label = 'Nuevo';
$traction = '4x2';
$engine_displacement = '2.0';
$cylinders = 4;
$fuel_type = 'Gasolina';
$color_exterior = 'Blanco';
$color_interior = 'Negro';
$passengers = 5;
$doors = 4;
$steering = 'Hidráulica';
$feature_electric_windows = 1;
$feature_ac = 1;

// Ensure category exists
$stmt_check = $pdo->prepare("SELECT id FROM categories WHERE name = :name");
$stmt_check->bindParam(':name', $category);
$stmt_check->execute();
if ($stmt_check->rowCount() == 0) {
    $stmt_new = $pdo->prepare("INSERT INTO categories (name) VALUES (:name)");
    $stmt_new->bindParam(':name', $category);
    $stmt_new->execute();
}

$sql = "INSERT INTO cars (make, model, year, price, mileage, description, image_path, show_price, category, status_label, 
        traction, engine_displacement, cylinders, fuel_type, color_exterior, color_interior, passengers, doors, steering,
        feature_electric_windows, feature_ac) 
        VALUES (:make, :model, :year, :price, :mileage, :description, :image_path, :show_price, :category, :status_label,
        :traction, :engine_displacement, :cylinders, :fuel_type, :color_exterior, :color_interior, :passengers, :doors, :steering,
        :feature_electric_windows, :feature_ac)";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':make' => $make,
        ':model' => $model,
        ':year' => $year,
        ':price' => $price,
        ':mileage' => $mileage,
        ':description' => $description,
        ':image_path' => $image_path,
        ':show_price' => $show_price,
        ':category' => $category,
        ':status_label' => $status_label,
        ':traction' => $traction,
        ':engine_displacement' => $engine_displacement,
        ':cylinders' => $cylinders,
        ':fuel_type' => $fuel_type,
        ':color_exterior' => $color_exterior,
        ':color_interior' => $color_interior,
        ':passengers' => $passengers,
        ':doors' => $doors,
        ':steering' => $steering,
        ':feature_electric_windows' => $feature_electric_windows,
        ':feature_ac' => $feature_ac
    ]);
    echo "Test car added successfully.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit(1);
}
?>
