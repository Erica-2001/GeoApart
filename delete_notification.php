<?php
session_start();
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notif_id'])) {
    $notif_id = intval($_POST['notif_id']);
    $user_id = $_SESSION['user_id'];

    // Double check if the notification belongs to the logged-in user
    $check_query = "SELECT * FROM notifications WHERE id = ? AND user_id = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("ii", $notif_id, $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        $delete_stmt = $conn->prepare("DELETE FROM notifications WHERE id = ?");
        $delete_stmt->bind_param("i", $notif_id);
        $delete_stmt->execute();

        echo "<script>window.location.href='tenant_dashboard.php';</script>";
    } else {
        echo "<script>alert('Notification not found or unauthorized.'); window.location.href='tenant_dashboard.php';</script>";
    }
} else {
    echo "<script>alert('Invalid request.'); window.location.href='tenant_dashboard.php';</script>";
}
?>
