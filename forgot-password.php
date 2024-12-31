<?php
require __DIR__ . '/vendor/autoload.php';
include '../config.php';

use SendGrid\Mail\Mail;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'])) {
    $email = $_POST['email'];

    try {
        // Check if the email exists in the database
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // Generate a reset token and expiration
            $token = bin2hex(random_bytes(32));
            $expires_at = date("Y-m-d H:i:s", strtotime("+1 hour"));

            // Save the token to the database
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt = $pdo->prepare("INSERT INTO password_resets (user_id, token, expires_at, created_at) VALUES (:user_id, :token, :expires_at, NOW())");
            $stmt->bindParam(':user_id', $user['id'], PDO::PARAM_INT);
            $stmt->bindParam(':token', $token, PDO::PARAM_STR);
            $stmt->bindParam(':expires_at', $expires_at, PDO::PARAM_STR);

            if ($stmt->execute()) {
                // Prepare the reset email
                $resetLink = "http://localhost/Journaly/GENERAL/reset-password.php?token=$token";

                // SendGrid email configuration
                $email = new Mail();
                $email->setFrom("journaly82@gmail.com", "Journaly");
                $email->setSubject("Password Reset Request");
                $email->addTo($_POST['email']);
                $email->addContent("text/plain", "Click the link to reset your password: $resetLink");
                $email->addContent("text/html", "Hello,<br><br>Click the link below to reset your password:<br><a href='$resetLink'>$resetLink</a><br><br>This link will expire in 1 hour.");

                // Send the email using SendGrid
                $sendgrid = new \SendGrid('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'); // Replace with your actual API key

                $response = $sendgrid->send($email);

                if ($response->statusCode() == 202) {
                    echo "<script>Swal.fire('Success!', 'Password reset link has been sent to your email.', 'success');</script>";
                } else {
                    echo "<script>Swal.fire('Error!', 'Failed to send email. Please try again later.', 'error');</script>";
                }
            }
        } else {
            echo "<script>Swal.fire('Error!', 'No account found with this email.', 'error');</script>";
        }
    } catch (Exception $e) {
        echo "<script>Swal.fire('Error!', 'An error occurred: {$e->getMessage()}', 'error');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="Gstyle.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://unpkg.com/boxicons@2.1.2/css/boxicons.min.css" rel="stylesheet" />
</head>
<body style="background: url('https://images.unsplash.com/photo-1731955196267-e863d6f39794?q=80&w=2893&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D') no-repeat center center fixed; background-size: cover;">

<?php include 'navbar.php'; ?>

    <!-- Forgot Password Form -->
    <div class="formContainer">
        <form action="forgot-password.php" method="POST">
            <h2>Forgot Password</h2>
            <div class="input_box">
                <input type="email" name="email" placeholder="Enter your email" required>
                <i class='bx bx-envelope email'></i>
            </div>
            <button type="submit" name="submit">Send Reset Link</button>
        </form>
    </div>

</body>
</html>
