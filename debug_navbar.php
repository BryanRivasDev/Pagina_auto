<?php
require_once 'config.php';
try {
    $stmt = $pdo->query("SELECT * FROM navbar_links ORDER BY order_index ASC");
    $links = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "ID | Label | URL | Order | Visible\n";
    foreach ($links as $link) {
        echo "{$link['id']} | {$link['label']} | {$link['url']} | {$link['order_index']} | {$link['is_visible']}\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
