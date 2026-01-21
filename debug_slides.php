<?php
require_once 'config.php';
$slides = $pdo->query("SELECT * FROM carousel_slides")->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($slides);
echo "</pre>";
foreach($slides as $slide) {
    if(file_exists($slide['image_path'])) {
        echo "File exists: " . $slide['image_path'] . "<br>";
    } else {
        echo "File MISSING: " . $slide['image_path'] . "<br>";
    }
}
?>
