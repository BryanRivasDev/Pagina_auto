<?php
require_once 'config.php';

try {
    echo "Attempting to update database schema...\n";

    $queries = [
        "ALTER TABLE cars ADD COLUMN IF NOT EXISTS traction VARCHAR(50) AFTER mileage",
        "ALTER TABLE cars ADD COLUMN IF NOT EXISTS engine_displacement VARCHAR(50) AFTER traction",
        "ALTER TABLE cars ADD COLUMN IF NOT EXISTS cylinders INT AFTER engine_displacement",
        "ALTER TABLE cars ADD COLUMN IF NOT EXISTS fuel_type VARCHAR(50) AFTER cylinders",
        "ALTER TABLE cars ADD COLUMN IF NOT EXISTS color_exterior VARCHAR(50) AFTER fuel_type",
        "ALTER TABLE cars ADD COLUMN IF NOT EXISTS color_interior VARCHAR(50) AFTER color_exterior",
        "ALTER TABLE cars ADD COLUMN IF NOT EXISTS passengers INT AFTER color_interior",
        "ALTER TABLE cars ADD COLUMN IF NOT EXISTS upholstery VARCHAR(100) AFTER passengers",
        "ALTER TABLE cars ADD COLUMN IF NOT EXISTS airbags INT AFTER upholstery",
        "ALTER TABLE cars ADD COLUMN IF NOT EXISTS locking_system VARCHAR(100) AFTER airbags",
        "ALTER TABLE cars ADD COLUMN IF NOT EXISTS feature_electric_windows TINYINT(1) DEFAULT 0",
        "ALTER TABLE cars ADD COLUMN IF NOT EXISTS feature_sunroof TINYINT(1) DEFAULT 0",
        "ALTER TABLE cars ADD COLUMN IF NOT EXISTS feature_ac TINYINT(1) DEFAULT 0",
        "ALTER TABLE cars ADD COLUMN IF NOT EXISTS feature_digital_ac TINYINT(1) DEFAULT 0",
        "ALTER TABLE cars ADD COLUMN IF NOT EXISTS feature_steering_controls TINYINT(1) DEFAULT 0",
        "ALTER TABLE cars ADD COLUMN IF NOT EXISTS feature_emergency_system TINYINT(1) DEFAULT 0",
        "ALTER TABLE cars ADD COLUMN IF NOT EXISTS feature_backup_camera TINYINT(1) DEFAULT 0",
        "ALTER TABLE cars ADD COLUMN IF NOT EXISTS feature_radio TINYINT(1) DEFAULT 0",
        "ALTER TABLE cars ADD COLUMN IF NOT EXISTS feature_touchscreen TINYINT(1) DEFAULT 0",
        "ALTER TABLE cars ADD COLUMN IF NOT EXISTS feature_carplay_android TINYINT(1) DEFAULT 0",
        "ALTER TABLE cars ADD COLUMN IF NOT EXISTS feature_push_start TINYINT(1) DEFAULT 0"
    ];

    foreach ($queries as $sql) {
        try {
            $pdo->exec($sql);
            echo "Executed: " . substr($sql, 0, 50) . "...\n";
        } catch (PDOException $e) {
            echo "Error executing query: " . $e->getMessage() . "\n";
        }
    }

    echo "Database schema updated successfully.\n";

} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
