<?php
require_once 'config.php';

// REPLACE 'uploads/' with 'assets/images/' in carousel_slides table
$sql = "UPDATE carousel_slides SET image_path = REPLACE(image_path, 'uploads/', 'assets/images/') WHERE image_path LIKE 'uploads/%'";
$stmt = $pdo->prepare($sql);
$stmt->execute();

echo "Updated carousel_slides table paths: " . $stmt->rowCount() . " rows affected.<br>";
echo "Carousel Video Path Fix Complete!";
?>
