<?php
session_start();
include '../db_connect.php';

// Landlord only access
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'Landlord') {
    header("Location: login.php");
    exit();
}

$landlord_id = $_SESSION['user_id'];

// Fetch approved tenant rentals for this landlord
$query = "
    SELECT tr.*, 
           t.name AS tenant_name,
           a.name AS apartment_name, 
           u.unit_number,
           p.total_amount,
           p.payment_status,
           p.payment_date
    FROM tenant_rentals tr
    JOIN users t ON tr.tenant_id = t.id
    JOIN apartments a ON tr.apartment_id = a.id
    JOIN apartment_units u ON tr.unit_id = u.id
    LEFT JOIN payments p ON tr.unit_id = p.unit_id AND tr.tenant_id = p.receiver_id
    WHERE tr.status = 'Active' AND tr.landlord_id = $landlord_id
    ORDER BY tr.created_at DESC
";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Landlord - Manage Tenants</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <style>
    body { font-family: 'Poppins', sans-serif; background: #f4f4f4; padding: 20px; }
    h2 { color: #007bff; text-align: center; margin-bottom: 20px; }

    .tenant-card {
      background: white;
      padding: 15px;
      border-radius: 10px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      margin-bottom: 15px;
    }

    .tenant-card p {
      margin: 6px 0;
      font-size: 14px;
    }

    .label {
      font-weight: 600;
      color: #007bff;
      display: block;
    }

    .status {
      display: inline-block;
      padding: 5px 10px;
      border-radius: 20px;
      font-weight: bold;
      font-size: 13px;
    }
    .Paid { background: #28a745; color: white; }
    .Pending { background: #ffc107; color: black; }
    .Overdue { background: #dc3545; color: white; }
  </style>
</head>
<body>
<div class="back">
        <a href="landlord_dashboard.php"><i class="fa-solid fa-arrow-left"></i></a>
    </div>
  <h2>👥 My Tenants & Payment Info</h2>

  <?php if ($result->num_rows === 0): ?>
    <p style="text-align:center;">You don't have active tenants yet.</p>
  <?php else: ?>
    <?php while ($row = $result->fetch_assoc()): ?>
      <div class="tenant-card">
        <p><span class="label">Tenant</span><?= htmlspecialchars($row['tenant_name']) ?></p>
        <p><span class="label">Apartment</span><?= htmlspecialchars($row['apartment_name']) ?></p>
        <p><span class="label">Unit</span><?= htmlspecialchars($row['unit_number']) ?></p>
        <p><span class="label">Amount</span><?= $row['total_amount'] ? '₱' . number_format($row['total_amount'], 2) : 'N/A' ?></p>
        <p><span class="label">Status</span>
          <span class="status <?= $row['payment_status'] ?>">
            <?= $row['payment_status'] ?? 'N/A' ?>
          </span>
        </p>
        <p><span class="label">Payment Date</span><?= $row['payment_date'] ? date('M d, Y', strtotime($row['payment_date'])) : 'N/A' ?></p>
        <p><span class="label">Rental Date</span><?= date('M d, Y', strtotime($row['created_at'])) ?></p>
      </div>
    <?php endwhile; ?>
  <?php endif; ?>

</body>
</html>
