<?php
require_once 'config.php';

try {
    // Check if column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM site_settings LIKE 'footer_text'");
    $exists = $stmt->fetch();

    if (!$exists) {
        $pdo->exec("ALTER TABLE site_settings ADD COLUMN footer_text TEXT");
        echo "Column 'footer_text' added successfully.";
        
        // Set default value
        $default_text = "© 2025 AutoSales. Todos los derechos reservados.";
        $stmt = $pdo->prepare("UPDATE site_settings SET footer_text = :text WHERE id = 1");
        $stmt->execute([':text' => $default_text]);
        echo " Default value set.";
    } else {
        echo "Column 'footer_text' already exists.";
    }

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
