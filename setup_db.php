<?php
// setup_db.php - Run this to initialize the database
$host = 'localhost';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = file_get_contents(__DIR__ . '/database.sql');
    
    $pdo->exec($sql);
    
    echo "Database and tables created successfully. Default admin user created.";
    echo "<br>Username: admin";
    echo "<br>Password: admin123";
    
} catch (PDOException $e) {
    die("DB ERROR: " . $e->getMessage());
}
?>
