<?php
require_once '../config.php';
session_start();

// Simple auth check (expand if needed)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id']) && isset($_GET['car_id'])) {
    $id = $_GET['id'];
    $car_id = $_GET['car_id'];

    // Get path to delete file
    $stmt = $pdo->prepare("SELECT image_path FROM car_images WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $img = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($img) {
        $filepath = "../" . $img['image_path'];
        if (file_exists($filepath)) {
            unlink($filepath);
        }

        $stmt = $pdo->prepare("DELETE FROM car_images WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }

    header("Location: edit_car.php?id=" . $car_id);
    exit;
} else {
    header("Location: index.php");
    exit;
}
?>
