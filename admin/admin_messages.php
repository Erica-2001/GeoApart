<?php
session_start();
if (!isset($_SESSION["admin_id"])) {
    header("Location: admin_login.php");
    exit();
}
include("../db_connect.php");

// Send message
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['send_message'])) {
    $sender_id = $_SESSION["admin_id"];
    $receiver_id = intval($_POST["tenant_id"]);
    $message_text = trim($_POST["message_text"]);

    if (!empty($message_text)) {
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message_text) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $sender_id, $receiver_id, $message_text);
        $stmt->execute();
        $stmt->close();

        header("Location: admin_messages.php?tenant_id=$receiver_id");
        exit;
    } else {
        echo "<script>alert('Message cannot be empty.');</script>";
    }
}

// Fetch tenants
$tenants = $conn->query("SELECT id, name FROM users WHERE user_type = 'Tenant'");

// Fetch chat history if tenant is selected
$messages = [];
if (isset($_GET['tenant_id'])) {
    $tenant_id = intval($_GET['tenant_id']);
    $stmt = $conn->prepare("SELECT m.*, u.name AS sender_name FROM messages m JOIN users u ON m.sender_id = u.id WHERE (m.sender_id = ? OR m.receiver_id = ?) AND (m.sender_id = ? OR m.receiver_id = ?) ORDER BY m.sent_at ASC");
    $stmt->bind_param("iiii", $_SESSION['admin_id'], $_SESSION['admin_id'], $tenant_id, $tenant_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Messages | GeoApart</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f4f4f4; padding: 30px; }
        .container { max-width: 800px; margin: auto; background: #fff; padding: 25px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #007bff; margin-bottom: 20px; }
        form { display: flex; flex-direction: column; gap: 15px; margin-top: 20px; }
        select, textarea, button {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
        }
        button {
            background-color: #007bff;
            color: white;
            font-weight: bold;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .chat-box {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 10px;
            height: 300px;
            overflow-y: auto;
            background: #f9f9f9;
            margin-top: 20px;
        }
        .message {
            margin-bottom: 12px;
            max-width: 70%;
            padding: 10px 15px;
            border-radius: 15px;
            clear: both;
        }
        .admin-msg {
            background-color: #007bff;
            color: white;
            float: right;
        }
        .tenant-msg {
            background-color: #e9ecef;
            float: left;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>📨 Admin Messaging</h2>

    <form method="GET">
        <label for="tenant_id">Select Tenant:</label>
        <select name="tenant_id" id="tenant_id" onchange="this.form.submit()" required>
            <option value="">-- Choose Tenant --</option>
            <?php while ($row = $tenants->fetch_assoc()): ?>
                <option value="<?= $row['id'] ?>" <?= (isset($_GET['tenant_id']) && $_GET['tenant_id'] == $row['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($row['name']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </form>

    <?php if (isset($_GET['tenant_id'])): ?>
        <div class="chat-box">
            <?php foreach ($messages as $msg): ?>
                <div class="message <?= $msg['sender_id'] == $_SESSION['admin_id'] ? 'admin-msg' : 'tenant-msg' ?>">
                    <strong><?= $msg['sender_name'] ?>:</strong><br>
                    <?= htmlspecialchars($msg['message_text']) ?><br>
                    <small><?= date("M d, Y H:i", strtotime($msg['sent_at'])) ?></small>
                </div>
            <?php endforeach; ?>
        </div>

        <form method="POST">
            <input type="hidden" name="tenant_id" value="<?= intval($_GET['tenant_id']) ?>">
            <label>Message:</label>
            <textarea name="message_text" rows="4" placeholder="Type your message here..." required></textarea>
            <button type="submit" name="send_message"><i class="fas fa-paper-plane"></i> Send Message</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
