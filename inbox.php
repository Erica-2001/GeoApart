<?php
session_start();
include 'db_connect.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$query = "SELECT DISTINCT users.id, users.name FROM messages 
          JOIN users ON (messages.sender_id = users.id OR messages.receiver_id = users.id) 
          WHERE (messages.sender_id = '$user_id' OR messages.receiver_id = '$user_id') AND users.id != '$user_id'";
$users = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inbox</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            background: #f4f4f4;
            text-align: center;
            padding: 20px;
        }
        .inbox-container {
            max-width: 600px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }
        .user-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 20px;
        }
        .user-link {
            text-decoration: none;
            color: white;
            background: #007bff;
            padding: 10px;
            border-radius: 5px;
            font-size: 16px;
            transition: 0.3s;
        }
        .user-link:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>

    <div class="inbox-container">
        <h2>Inbox</h2>

        <div class="user-list">
            <?php while ($user = mysqli_fetch_assoc($users)): ?>
                <a href="messages.php?receiver_id=<?= $user['id'] ?>" class="user-link">
                    Chat with <?= htmlspecialchars($user['name']) ?>
                </a>
            <?php endwhile; ?>
        </div>
    </div>

</body>
</html>
