<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION['user_type'] !== 'Tenant') {
    header("Location: login.php");
    exit();
}

include("db_connect.php");

$tenant_id = $_SESSION['user_id'];

// Fetch reminders for tenant
$reminders = mysqli_query($conn, "
    SELECT * FROM notifications
    WHERE user_id = $tenant_id
    ORDER BY created_at DESC
");

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tenant Reminders</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <style>
    * {
      margin: 0; padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }
    body {
      background: #f4f4f4;
      padding: 20px;
    }
    .container {
      max-width: 800px;
      margin: auto;
      background: white;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    h2 {
      text-align: center;
      color: #007bff;
      margin-bottom: 20px;
    }
    .reminder-card {
      background: #f8f9fa;
      border: 1px solid #ddd;
      border-radius: 8px;
      padding: 15px;
      margin-bottom: 15px;
      box-shadow: 0px 2px 6px rgba(0,0,0,0.05);
    }
    .reminder-card p {
      font-size: 14px;
      margin-bottom: 8px;
    }
    .reminder-time {
      font-size: 12px;
      color: #666;
      text-align: right;
    }
    .back {
      margin-bottom: 20px;
    }
    .back a {
      text-decoration: none;
      font-size: 16px;
      color: #007bff;
    }
  </style>
</head>
<body>

<div class="back">
  <a href="tenant_dashboard.php"><i class="fa-solid fa-arrow-left"></i> Back</a>
</div>

<div class="container">
  <h2>Tenant Reminders</h2>

  <?php if (mysqli_num_rows($reminders) > 0): ?>
    <?php while ($reminder = mysqli_fetch_assoc($reminders)): ?>
      <div class="reminder-card">
        <p><i class="fa-solid fa-bell"></i> <?= htmlspecialchars($reminder['message']) ?></p>
        <div class="reminder-time">
          <?= date("M d, Y h:i A", strtotime($reminder['created_at'])) ?>
        </div>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <p style="text-align:center; color: #666;">No reminders yet.</p>
  <?php endif; ?>

</div>

</body>
</html>
