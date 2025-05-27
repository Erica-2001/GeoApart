<?php
session_start();
include("config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST["user_id"];
    $message = $_POST["message"];

    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message, status) VALUES (?, ?, 'unread')");
    $stmt->execute([$user_id, $message]);

    echo "Notification Sent!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Send Notification</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h2>Send Notification</h2>
    <form method="POST">
        <label>User ID:</label>
        <input type="number" name="user_id" required>
        <label>Message:</label>
        <textarea name="message" required></textarea>
        <button type="submit">Send</button>
    </form>
</body>
</html>
