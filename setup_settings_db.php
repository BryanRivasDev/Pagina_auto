<?php
require_once 'config.php';

try {
    $pdo->exec("DROP TABLE IF EXISTS site_settings");
    
    $sql = "CREATE TABLE site_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) UNIQUE NOT NULL,
        setting_value TEXT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    
    // Seed default values if they don't exist
    $defaults = [
        'carousel_1_title' => '¡Encontrá tú próximo vehículo ahora!',
        'carousel_2_title' => 'Nuevos Ingresos',
        'explore_category_title' => '| Explora por categoria',
        'explore_brand_title' => '| Explora por Marca'
    ];
    
    foreach ($defaults as $key => $val) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES (?, ?)");
        $stmt->execute([$key, $val]);
    }
    
    echo "Settings table created and seeded.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
