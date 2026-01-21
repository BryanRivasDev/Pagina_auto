<?php
require_once '../config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (isset($input['id']) && isset($input['is_sold'])) {
    try {
        $stmt = $pdo->prepare("UPDATE cars SET is_sold = :is_sold WHERE id = :id");
        $stmt->execute([
            ':is_sold' => $input['is_sold'],
            ':id' => $input['id']
        ]);
        
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
}
?>
