<?php
require_once 'config.php';

$username = 'admin';
$password = 'admin123';
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

try {
    // strict check for user existence
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE username = :username");
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':username', $username);
        
        if ($stmt->execute()) {
            echo "Password updated successfully for user: " . $username;
        } else {
            echo "Error updating password.";
        }
    } else {
        // If user doesn't exist, create it (Just in case)
        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $hashed_password);
        if ($stmt->execute()) {
            echo "User 'admin' created with password 'admin123'.";
        }
    }
} catch (PDOException $e) {
    die("ERROR: " . $e->getMessage());
}
?>
