<?php
session_start();
include '../db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'Landlord') {
    header("Location: ../login.php");
    exit();
}

$landlord_id = $_SESSION['user_id'];
$tenant_id = isset($_GET['tenant_id']) ? intval($_GET['tenant_id']) : 0;

// Fetch tenants under this landlord
$tenants = $conn->query("SELECT DISTINCT u.id, u.name FROM tenant_rentals tr JOIN users u ON tr.tenant_id = u.id WHERE tr.landlord_id = $landlord_id AND tr.status = 'Active'");

// Fetch messages with sender/receiver names
$messages = [];
if ($tenant_id) {
    $query = "SELECT m.*, s.name AS sender_name, r.name AS receiver_name
              FROM messages m
              JOIN users s ON m.sender_id = s.id
              JOIN users r ON m.receiver_id = r.id
              WHERE (m.sender_id = $landlord_id AND m.receiver_id = $tenant_id) OR (m.sender_id = $tenant_id AND m.receiver_id = $landlord_id)
              ORDER BY m.sent_at ASC";
    $result = $conn->query($query);
    if ($result) {
        $messages = $result->fetch_all(MYSQLI_ASSOC);
    }
}

// Handle message send
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $message_text = trim($_POST['message_text']);
    if (!empty($message_text) && $tenant_id) {
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message_text) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $landlord_id, $tenant_id, $message_text);
        $stmt->execute();
        header("Location: landlord_messages.php?tenant_id=$tenant_id");
        exit();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Landlord Messages | GeoApart</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f4f4f4; padding: 30px; }
        .container { max-width: 800px; margin: auto; background: #fff; padding: 25px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #007bff; }
        select, textarea, button {
            width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc;
            border-radius: 6px; font-size: 15px;
        }
        .chat-box {
            max-height: 350px; overflow-y: auto; padding: 10px;
            background: #f9f9f9; border-radius: 8px; margin-top: 20px;
            display: flex; flex-direction: column; gap: 10px;
        }
        .message {
            padding: 10px; border-radius: 10px; max-width: 75%; font-size: 14px;
            display: inline-block; position: relative;
        }
        .sent {
            background: #007bff; color: #fff;
            align-self: flex-start;
            text-align: left;
        }
        .received {
            background: #e0e0e0; color: #000;
            align-self: flex-end;
            text-align: left;
        }
        .sender-label {
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 4px;
            display: block;
        }
        .timestamp {
            font-size: 11px;
            margin-top: 5px;
            display: block;
            text-align: right;
        }
        .send-btn {
            background-color: #007bff; color: white;
            font-weight: bold; cursor: pointer;
            border: none;
        }
        .send-btn:hover { background-color: #0056b3; }
    </style>
</head>
<body>


<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

 <div class="back">
        <a href="landlord_dashboard.php"><i class="fa-solid fa-arrow-left"></i></a>
    </div>
	<br>
	
    <h2>📨 Message Tenants</h2>
    <form method="GET" action="">
        <label>Select Tenant:</label>
        <select name="tenant_id" onchange="this.form.submit()" required>
            <option value="">-- Select Tenant --</option>
            <?php while ($row = $tenants->fetch_assoc()): ?>
                <option value="<?= $row['id'] ?>" <?= $row['id'] == $tenant_id ? 'selected' : '' ?>><?= htmlspecialchars($row['name']) ?></option>
            <?php endwhile; ?>
        </select>
    </form>

    <?php if ($tenant_id): ?>
        <div class="chat-box">
            <?php if (!empty($messages)): ?>
                <?php foreach ($messages as $msg): ?>
                    <div class="message <?= $msg['sender_id'] == $landlord_id ? 'sent' : 'received' ?>">
                        <span class="sender-label">From: <?= htmlspecialchars($msg['sender_name']) ?></span>
                        <?= htmlspecialchars($msg['message_text']) ?>
                        <span class="timestamp"><?= date('M d, Y h:i A', strtotime($msg['sent_at'])) ?></span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No messages yet. Start the conversation.</p>
            <?php endif; ?>
        </div>

        <form method="POST">
            <textarea name="message_text" rows="4" placeholder="Type your message..." required></textarea>
            <button type="submit" name="send_message" class="send-btn"><i class="fas fa-paper-plane"></i> Send</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
