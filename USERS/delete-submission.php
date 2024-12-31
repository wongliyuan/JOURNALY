<?php
include '../config.php';
session_start();

// Get the submission ID from the URL
$submissionId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get the logged-in user's ID
$userId = $_SESSION['user_id'];

// Check if the submission exists and belongs to the user
$query = "SELECT * FROM submissions WHERE id = :id AND user_id = :user_id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':id', $submissionId, PDO::PARAM_INT);
$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
$stmt->execute();
$submission = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$submission) {
    die("Submission not found or you do not have permission to withdraw this submission.");
}

// Withdraw the submission by deleting it from the database
$deleteQuery = "DELETE FROM submissions WHERE id = :id";
$deleteStmt = $pdo->prepare($deleteQuery);
$deleteStmt->bindParam(':id', $submissionId, PDO::PARAM_INT);
$deleteStmt->execute();

// Redirect to the submission list page
header('Location: my-submission.php');
exit();
