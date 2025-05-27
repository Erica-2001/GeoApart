<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo '<div class="notif-item">Please login to see notifications.</div>';
    exit();
}

$user_id = intval($_SESSION['user_id']);

$notifications_query = "SELECT id, message, created_at FROM notifications WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 5";
$notifications = mysqli_query($conn, $notifications_query);
?>

<div class="notif-dropdown" id="notifDropdown">
    <h4>🔔 Notifications</h4>
    <?php if (mysqli_num_rows($notifications) > 0): ?>
        <?php while ($note = mysqli_fetch_assoc($notifications)): ?>
            <div class="notif-item">
                <?= htmlspecialchars($note['message']) ?>
                <time><?= date("M d, Y", strtotime($note['created_at'])) ?></time>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="notif-item">No new notifications.</div>
    <?php endif; ?>
</div>
