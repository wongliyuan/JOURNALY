<?php
session_start();
include '../config.php';

// Default values
$profilePicture = 'default.jpg'; // Default profile picture
$username = 'Guest';
$role = 'N/A';
$unreadNotifications = 0;
$unreadChats = 0;

if (isset($_SESSION['user_id'])) {
    try {
        // Fetch user details
        $stmt = $pdo->prepare("SELECT username, role, profile_picture FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $username = $result['username'];
            $role = $result['role'];
            if (!empty($result['profile_picture'])) {
                $profilePicture = $result['profile_picture'];
            }
        }

        // Fetch unread notifications count
        $notificationStmt = $pdo->prepare("SELECT COUNT(*) AS unread FROM notifications WHERE user_id = ? AND is_read = 0");
        $notificationStmt->execute([$_SESSION['user_id']]);
        $notificationResult = $notificationStmt->fetch(PDO::FETCH_ASSOC);
        $unreadNotifications = $notificationResult['unread'] ?? 0;

        // Fetch unread chats count
        $chatStmt = $pdo->prepare("
            SELECT COUNT(*) AS unread 
            FROM chats 
            WHERE receiver_id = ? AND is_read = 0
        ");
        $chatStmt->execute([$_SESSION['user_id']]);
        $chatResult = $chatStmt->fetch(PDO::FETCH_ASSOC);
        $unreadChats = $chatResult['unread'] ?? 0;
    } catch (PDOException $e) {
        // Log error
        error_log("Error fetching user or unread counts: " . $e->getMessage());
    }
}
?>

<!-- navbar.php -->
<nav>
    <div class="sidebar">
        <div class="logo">
            <i class='bx bxs-graduation'></i>
            <span class="logo-name">Journaly</span>
        </div>

        <div class="sidebar-content">
            <ul class="lists">
                <li class="list">
                    <a href="../USERS/my-submission.php" class="nav-link">
                        <i class='bx bx-book icon'></i>
                        <span class="link">My Submissions</span>
                    </a>
                </li>
                <li class="list">
                    <a href="../USERS/my-review.php" class="nav-link">
                        <i class='bx bx-comment-detail icon'></i>
                        <span class="link">My Reviews</span>
                    </a>
                </li>
                <li class="list">
                    <a href="../USERS/my-notification.php" class="nav-link">
                        <i class="bx bx-bell icon"></i>
                        <span class="link">My Notifications</span>
                        <?php if ($unreadNotifications > 0): ?>
                            <span class="badge"><?= htmlspecialchars($unreadNotifications) ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="list">
                    <a href="../USERS/my-chat.php" class="nav-link">
                        <i class="bx bx-message-rounded icon"></i>
                        <span class="link">My Chats</span>
                        <?php if ($unreadChats > 0): ?>
                            <span class="badge"><?= htmlspecialchars($unreadChats) ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="list">
                    <a href="../USERS/my-profile.php" class="nav-link">
                        <i class='bx bx-user icon'></i>
                        <span class="link">My Profile</span>
                    </a>
                </li>
            </ul>

            <div class="bottom-content">
                <li class="list">
                    <a href="../GENERAL/homepage.php" class="nav-link">
                        <i class="bx bx-log-out icon"></i>
                        <div class="user-card">
                            <img src="../uploads/<?= htmlspecialchars($profilePicture) ?>" alt="Profile" class="sidebar-profile" />
                            <div class="user-meta">
                                <span class="username"><?= htmlspecialchars($username) ?></span>
                                <span class="role"><?= htmlspecialchars($role) ?></span>
                            </div>
                        </div>
                    </a>
                </li>
            </div>
        </div>
    </div>
</nav>
