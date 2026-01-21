<?php
require_once 'config.php';
try {
    $stmt = $pdo->query("SELECT * FROM site_settings");
    $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<h1>Settings Dump</h1>";
    echo "<pre>";
    print_r($settings);
    echo "</pre>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
