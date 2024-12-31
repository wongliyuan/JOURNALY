<?php
session_start();
include '../config.php';

// Check if an ID is set in the URL
if (isset($_GET['id'])) {
    $userId = $_GET['id'];

    try {
        // Prepare the delete query
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        // Set a success message
        $_SESSION['success_message'] = "User deleted successfully!";
    } catch (PDOException $e) {
        // Set an error message if something goes wrong
        $_SESSION['error_message'] = "Error deleting user: " . $e->getMessage();
    }
}

// Redirect back to the user management page
header("Location: manage-user.php");
exit();
?>
