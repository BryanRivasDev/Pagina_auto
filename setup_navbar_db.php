<?php
require_once 'config.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS navbar_links (
        id INT AUTO_INCREMENT PRIMARY KEY,
        label VARCHAR(100) NOT NULL,
        url VARCHAR(255) NOT NULL,
        order_index INT DEFAULT 0,
        is_visible TINYINT DEFAULT 1
    )";
    $pdo->exec($sql);

    // Seed default links if empty
    $count = $pdo->query("SELECT COUNT(*) FROM navbar_links")->fetchColumn();
    if ($count == 0) {
        $defaults = [
            ['Inicio', 'index.php', 1],
            ['Nosotros', 'nosotros.php', 2],
            ['Contacto', 'contacto.php', 3]
        ];
        $stmt = $pdo->prepare("INSERT INTO navbar_links (label, url, order_index) VALUES (?, ?, ?)");
        foreach ($defaults as $link) {
            $stmt->execute($link);
        }
        echo "Navbar links seeded.\n";
    }
    
    echo "Navbar table checked/created.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
