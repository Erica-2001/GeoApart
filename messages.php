<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'Tenant') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get assigned landlord
$landlord_result = $conn->query("SELECT landlord_id FROM tenant_rentals WHERE tenant_id = $user_id AND status = 'Active' LIMIT 1");
if ($landlord_result && $landlord_result->num_rows > 0) {
    $landlord_id = $landlord_result->fetch_assoc()['landlord_id'];
} else {
    echo "<script>alert('No active rental found.'); window.location.href='tenant_dashboard.php';</script>";
    exit();
}

// Fetch names
$getNames = $conn->query("SELECT id, name FROM users WHERE id IN ($user_id, $landlord_id)");
$nameMap = [];
while ($row = $getNames->fetch_assoc()) {
    $nameMap[$row['id']] = $row['name'];
}

// Send message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    if (!empty($message)) {
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message_text) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $user_id, $landlord_id, $message);
        $stmt->execute();
        $stmt->close();
    }
}

// Fetch message history
$query = "
    SELECT * FROM messages 
    WHERE (sender_id = $user_id AND receiver_id = $landlord_id) 
       OR (sender_id = $landlord_id AND receiver_id = $user_id)
    ORDER BY sent_at ASC
";
$result = $conn->query($query);
$messages = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tenant Messages | GeoApart</title>
    <style>
        body {
            background: #f4f4f4;
            font-family: 'Poppins', sans-serif;
            padding: 20px;
        }
        .chat-container {
            max-width: 700px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0px 4px 12px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #007bff;
            margin-bottom: 20px;
        }
        .message-box {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 20px;
            max-height: 400px;
            overflow-y: auto;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: #f9f9f9;
        }
        .message {
            padding: 12px;
            border-radius: 8px;
            max-width: 65%;
            font-size: 14px;
            position: relative;
        }
        .sent {
            align-self: flex-start;
            background: #007bff;
            color: white;
            border-top-left-radius: 0;
        }
        .received {
            align-self: flex-end;
            background: #e0e0e0;
            color: black;
            border-top-right-radius: 0;
        }
        .message small {
            display: block;
            font-size: 11px;
            margin-top: 5px;
            opacity: 0.8;
        }
        form textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
        }
        .send-btn {
            background: #007bff;
            color: white;
            padding: 10px 16px;
            margin-top: 10px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 15px;
        }
        .send-btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>

<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

 <div class="back">
        <a href="tenant_dashboard.php"><i class="fa-solid fa-arrow-left"></i></a>
    </div>

	
	
    <h2>Chat with Landlord</h2>
    <div class="message-box">
        <?php if (!empty($messages)): ?>
            <?php foreach ($messages as $msg): ?>
                <div class="message <?= $msg['sender_id'] == $user_id ? 'sent' : 'received' ?>">
                    <?= htmlspecialchars($msg['message_text']) ?>
                    <small><?= $nameMap[$msg['sender_id']] ?? 'User' ?> • <?= date("M d, h:i A", strtotime($msg['sent_at'])) ?></small>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No messages yet. Start the conversation!</p>
        <?php endif; ?>
    </div>

    <form method="POST">
        <textarea name="message" rows="3" placeholder="Type your message..." required></textarea>
        <button type="submit" class="send-btn">Send</button>
    </form>
</div>
</body>
</html>
