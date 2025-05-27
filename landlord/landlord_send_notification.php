<?php
session_start();
include '../db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'Landlord') {
    header("Location: ../login.php");
    exit();
}

$landlord_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    // Send to all tenants
    if (isset($_POST['send_all']) && $_POST['send_all'] === 'yes') {
        $all_tenants_query = "SELECT DISTINCT u.id FROM tenant_rentals r 
                              JOIN users u ON r.tenant_id = u.id 
                              WHERE r.landlord_id = '$landlord_id' AND u.user_type = 'Tenant'";
        $all_tenants = mysqli_query($conn, $all_tenants_query);

        while ($tenant = mysqli_fetch_assoc($all_tenants)) {
            $tenant_id = $tenant['id'];
            mysqli_query($conn, "INSERT INTO notifications (user_id, message) VALUES ('$tenant_id', '$message')");
        }
        echo "<script>alert('Notification sent to all your tenants successfully!');</script>";

    } elseif (!empty($_POST['tenant_id'])) {
        $tenant_id = intval($_POST['tenant_id']);
        $insert = mysqli_query($conn, "INSERT INTO notifications (user_id, message) VALUES ('$tenant_id', '$message')");

        echo $insert ? "<script>alert('Notification sent successfully!');</script>"
                     : "<script>alert('Failed to send notification.');</script>";
    }
}

// Get list of tenants
$tenant_query = "SELECT DISTINCT u.id, u.name, u.email 
                 FROM tenant_rentals r 
                 JOIN users u ON r.tenant_id = u.id 
                 WHERE r.landlord_id = '$landlord_id' AND u.user_type = 'Tenant'";
$tenants = mysqli_query($conn, $tenant_query);

// Get notifications sent by landlord
$reminders_query = "
    SELECT n.message, n.created_at, u.name AS tenant_name 
    FROM notifications n
    JOIN users u ON u.id = n.user_id
    JOIN tenant_rentals r ON r.tenant_id = u.id
    WHERE r.landlord_id = '$landlord_id'
    ORDER BY n.created_at DESC
";
$reminders = mysqli_query($conn, $reminders_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Notification to Tenants</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            padding: 40px;
            background: #f5f5f5;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #007bff;
            margin-bottom: 20px;
        }
        select, textarea {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
        }
        button {
            margin-top: 15px;
            padding: 12px 20px;
            background: #007bff;
            border: none;
            color: white;
            font-weight: bold;
            border-radius: 8px;
            cursor: pointer;
        }
        button:hover {
            background: #0056b3;
        }
        label {
            font-weight: bold;
        }
        .checkbox-container {
            margin-top: 15px;
        }
        .reminder-list {
            margin-top: 40px;
            background: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        .reminder {
            border-bottom: 1px solid #eee;
            padding: 10px 0;
        }
        .reminder:last-child {
            border-bottom: none;
        }
        .reminder strong {
            color: #333;
        }
        .reminder time {
            font-size: 12px;
            color: #777;
        }
    </style>
</head>
<body>

<div class="back" style="text-align: left; margin-bottom: 10px;">
    <a href="landlord_dashboard.php" style="text-decoration: none; font-size: 18px; color: #007bff;">
        ← Back
    </a>
</div>


    <h2>Send Reminder</h2>
    <form method="POST">
        <label for="tenant_id">Select Tenant:</label>
        <select name="tenant_id" id="tenant_id">
            <option value="">Choose Tenant (or check Send to All)</option>
            <?php while ($tenant = mysqli_fetch_assoc($tenants)): ?>
                <option value="<?= $tenant['id'] ?>">
                    <?= htmlspecialchars($tenant['name']) ?> (<?= htmlspecialchars($tenant['email']) ?>)
                </option>
            <?php endwhile; ?>
        </select>

        <div class="checkbox-container">
            <label><input type="checkbox" name="send_all" value="yes"> Send to All Tenants</label>
        </div>

        <label for="message">Message:</label>
        <textarea name="message" id="message" rows="5" placeholder="Enter your message here..." required></textarea>

        <button type="submit">Send Notification</button>
    </form>
</div>

<?php if (mysqli_num_rows($reminders) > 0): ?>

    <h3 style="text-align:center; color:#28a745;">📋 Sent Reminders</h3>
    <?php while ($row = mysqli_fetch_assoc($reminders)): ?>
        <div class="reminder">
            <strong>To:</strong> <?= htmlspecialchars($row['tenant_name']) ?><br>
            <strong>Message:</strong> <?= htmlspecialchars($row['message']) ?><br>
            <time>Sent: <?= date('M d, Y h:i A', strtotime($row['created_at'])) ?></time>
        </div>
    <?php endwhile; ?>
</div>
<?php endif; ?>

</body>
</html>
