<?php
require_once 'config.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS site_settings (
        id INT PRIMARY KEY DEFAULT 1,
        site_name VARCHAR(100) DEFAULT 'AutoSales',
        navbar_title VARCHAR(100) DEFAULT 'AUTOSALES',
        hero_title VARCHAR(255) DEFAULT 'Encuentra tu Auto Ideal',
        hero_subtitle VARCHAR(255) DEFAULT 'Calidad, Confianza y los Mejores Precios del Mercado',
        logo_path VARCHAR(255),
        contact_phone VARCHAR(50) DEFAULT '+504 9999-9999',
        contact_email VARCHAR(100) DEFAULT 'info@autosales.com',
        contact_address VARCHAR(255) DEFAULT 'Av. Circunvalación, San Pedro Sula',
        whatsapp_number VARCHAR(50) DEFAULT '50499999999'
    ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";

    $pdo->exec($sql);
    
    // Insert default row if not exists
    $stmt = $pdo->query("SELECT COUNT(*) FROM site_settings");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO site_settings (id) VALUES (1)");
    }

    echo "Table 'site_settings' created and initialized successfully.";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
