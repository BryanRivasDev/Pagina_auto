<?php
require_once 'auth.php';
checkLoggedIn();
require_once '../config.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // First get image path to delete file
    $stmt = $pdo->prepare("SELECT image_path FROM cars WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $car = $stmt->fetch();
    
    if ($car && !empty($car['image_path'])) {
        $file_path = "../" . $car['image_path'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }
    
    // Delete record
    $sql = "DELETE FROM cars WHERE id = :id";
    if ($stmt = $pdo->prepare($sql)) {
        $stmt->bindParam(':id', $id);
        if ($stmt->execute()) {
            header("location: index.php");
            exit;
        } else {
            echo "Error deleting record.";
        }
    }
} else {
    header("location: index.php");
    exit;
}
?>
