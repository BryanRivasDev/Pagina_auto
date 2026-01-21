<?php
require_once 'config.php';

$brands = [
    'Toyota' => [
        ['model' => 'Hilux', 'category' => 'Camioneta', 'price' => 45000, 'year' => 2024, 'engine' => '2.8L', 'traction' => '4x4', 'fuel' => 'Diesel'],
        ['model' => 'Corolla', 'category' => 'Sedan', 'price' => 25000, 'year' => 2023, 'engine' => '1.8L', 'traction' => '4x2', 'fuel' => 'Gasolina']
    ],
    'Hyundai' => [
        ['model' => 'Tucson', 'category' => 'SUV', 'price' => 32000, 'year' => 2024, 'engine' => '2.0L', 'traction' => 'AWD', 'fuel' => 'Gasolina'],
        ['model' => 'Elantra', 'category' => 'Sedan', 'price' => 22000, 'year' => 2022, 'engine' => '1.6L', 'traction' => '4x2', 'fuel' => 'Gasolina']
    ],
    'Kia' => [
        ['model' => 'Sportage', 'category' => 'SUV', 'price' => 31000, 'year' => 2024, 'engine' => '2.0L', 'traction' => 'AWD', 'fuel' => 'Gasolina'],
        ['model' => 'Picanto', 'category' => 'Hatchback', 'price' => 14000, 'year' => 2023, 'engine' => '1.2L', 'traction' => '4x2', 'fuel' => 'Gasolina']
    ],
    'Chevrolet' => [
        ['model' => 'Tahoe', 'category' => 'SUV', 'price' => 75000, 'year' => 2023, 'engine' => '5.3L V8', 'traction' => '4x4', 'fuel' => 'Gasolina'],
        ['model' => 'Spark', 'category' => 'Hatchback', 'price' => 12500, 'year' => 2021, 'engine' => '1.2L', 'traction' => '4x2', 'fuel' => 'Gasolina']
    ],
    'Suzuki' => [
        ['model' => 'Jimny', 'category' => 'Camioneta', 'price' => 28000, 'year' => 2024, 'engine' => '1.5L', 'traction' => '4x4', 'fuel' => 'Gasolina'],
        ['model' => 'Dzire', 'category' => 'Sedan', 'price' => 16000, 'year' => 2022, 'engine' => '1.2L', 'traction' => '4x2', 'fuel' => 'Gasolina']
    ],
    'Ford' => [
        ['model' => 'Ranger', 'category' => 'Camioneta', 'price' => 48000, 'year' => 2024, 'engine' => '3.0L V6', 'traction' => '4x4', 'fuel' => 'Diesel'],
        ['model' => 'Mustang', 'category' => 'Deportivo', 'price' => 55000, 'year' => 2020, 'engine' => '5.0L V8', 'traction' => '4x2', 'fuel' => 'Gasolina']
    ],
    'Mitsubishi' => [
        ['model' => 'L200', 'category' => 'Camioneta', 'price' => 42000, 'year' => 2024, 'engine' => '2.4L', 'traction' => '4x4', 'fuel' => 'Diesel'],
        ['model' => 'Montero Sport', 'category' => 'SUV', 'price' => 49000, 'year' => 2023, 'engine' => '3.0L V6', 'traction' => '4x4', 'fuel' => 'Gasolina']
    ],
    'Nissan' => [
        ['model' => 'Frontier', 'category' => 'Camioneta', 'price' => 38000, 'year' => 2023, 'engine' => '2.5L', 'traction' => '4x4', 'fuel' => 'Diesel'],
        ['model' => 'Versa', 'category' => 'Sedan', 'price' => 19000, 'year' => 2022, 'engine' => '1.6L', 'traction' => '4x2', 'fuel' => 'Gasolina']
    ]
];

$sql = "INSERT INTO cars (
    make, model, year, price, mileage, description, image_path, show_price, category, status_label, 
    traction, engine_displacement, cylinders, fuel_type, color_exterior, color_interior, passengers, doors, steering,
    feature_electric_windows, feature_ac
) VALUES (
    :make, :model, :year, :price, :mileage, :description, :image_path, 1, :category, :status_label,
    :traction, :engine, :cylinders, :fuel, :color_ext, :color_int, :passengers, :doors, 'Hidráulica',
    1, 1
)";

$stmt = $pdo->prepare($sql);

function executeInsert($stmt, $make, $data, $status) {
    global $pdo; // Fix Scope
    $mileage = ($status == 'Nuevo') ? 0 : rand(10000, 80000);
    $cylinders = strpos($data['engine'], 'V8') !== false ? 8 : (strpos($data['engine'], 'V6') !== false ? 6 : 4);
    $colors_ext = ['Blanco', 'Negro', 'Gris', 'Rojo', 'Azul', 'Plata'];
    $colors_int = ['Negro', 'Beige', 'Gris'];
    
    // Using a safe placeholder or user-provided image if existent, for now default to a known file or valid path 
    // to avoid broken images in UI. 'assets/images/hero-bg.jpg' exists.
    $image_path = 'assets/images/hero-bg.jpg'; 

    $params = [
        ':make' => $make,
        ':model' => $data['model'],
        ':year' => $data['year'],
        ':price' => $data['price'],
        ':mileage' => $mileage,
        ':description' => "Excelente estado. " . $data['model'] . " año " . $data['year'] . " con motor " . $data['engine'] . ".",
        ':image_path' => $image_path,
        ':category' => $data['category'],
        ':status_label' => $status,
        ':traction' => $data['traction'],
        ':engine' => $data['engine'],
        ':cylinders' => $cylinders,
        ':fuel' => $data['fuel'],
        ':color_ext' => $colors_ext[array_rand($colors_ext)],
        ':color_int' => $colors_int[array_rand($colors_int)],
        ':passengers' => 5,
        ':doors' => 4 
    ];
    
    try {
        $stmt->execute($params);
        
        $stmt_cat = $pdo->prepare("INSERT IGNORE INTO categories (name) VALUES (:name)");
        $stmt_cat->execute([':name' => $data['category']]);
        
        $stmt_brand = $pdo->prepare("INSERT IGNORE INTO brands (name) VALUES (:name)");
        $stmt_brand->execute([':name' => $make]);
    } catch (PDOException $e) {
        echo "Error inserting $make: " . $e->getMessage() . "\n";
    }
}

foreach ($brands as $make => $models) {
    if (isset($models[0])) {
        executeInsert($stmt, $make, $models[0], 'Nuevo');
        echo "Inserted {$make} {$models[0]['model']} (Nuevo)\n";
    }
    if (isset($models[1])) {
        executeInsert($stmt, $make, $models[1], 'Oferta');
        echo "Inserted {$make} {$models[1]['model']} (Oferta)\n";
    }
}

echo "Database Seeded Successfully!";
?>
