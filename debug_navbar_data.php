<?php
require_once 'config.php';

try {
    $stmt = $pdo->query("SELECT * FROM navbar_links");
    $links = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($links);
    echo "</pre>";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
