<?php
session_start();
header('Content-Type: application/json');

include 'db_connect.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$user_id = intval($_SESSION['user_id']);
$user_type = $_SESSION['user_type'];

// Support role-based notifications (e.g., admin, landlord, tenant)
$query = "SELECT id, message, created_at FROM notifications 
          WHERE user_id = ? OR receiver_type = ? 
          ORDER BY created_at DESC LIMIT 10";
$stmt = $conn->prepare($query);
$stmt->bind_param("is", $user_id, $user_type);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];

while ($row = $result->fetch_assoc()) {
    $notifications[] = [
        'id' => $row['id'],
        'message' => $row['message'],
        'created_at' => date("M d, Y h:i A", strtotime($row['created_at']))
    ];
}

echo json_encode(['status' => 'success', 'notifications' => $notifications]);
exit();
