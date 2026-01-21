<?php
require 'config.php';
$stmt = $pdo->query("DESCRIBE cars");
$columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
print_r($columns);
?>
