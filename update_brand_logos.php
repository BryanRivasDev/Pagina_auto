<?php
require_once 'config.php';

// MAPPING: Brand -> Logo File
$logos = [
    'Toyota' => 'assets/images/logo_toyota.png',
    'Hyundai' => 'assets/images/logo_hyundai.png',
    'Kia' => 'assets/images/logo_kia.png',
    'Chevrolet' => 'assets/images/logo_chevrolet.png',
    'Suzuki' => 'assets/images/logo_suzuki.png',
    'Ford' => 'assets/images/logo_ford.png',
    'Mitsubishi' => 'assets/images/logo_mitsubishi.png',
    'Nissan' => 'assets/images/logo_nissan.png'
];

$sql = "UPDATE brands SET logo_path = :path WHERE name = :name";
$stmt = $pdo->prepare($sql);

foreach ($logos as $brand => $path) {
    // Check if brand exists or just update
    $stmt->execute([':path' => $path, ':name' => $brand]);
    echo "Updated $brand logo to $path ({$stmt->rowCount()} changed)<br>";
}

echo "All Brand Logos Updated!";
?>
