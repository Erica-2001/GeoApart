<?php
session_start();
include 'db_connect.php';

if (!isset($_GET['unit_id'])) {
    echo "<script>alert('No unit selected.'); window.location.href='my_properties.php';</script>";
    exit();
}

$unit_id = intval($_GET['unit_id']);

// Get the active tenant_id from tenant_rentals
$tenant_q = mysqli_query($conn, "SELECT tenant_id FROM tenant_rentals WHERE unit_id = $unit_id AND status = 'Active'");
$tenant_r = mysqli_fetch_assoc($tenant_q);

if (!$tenant_r) {
    echo "<script>alert('No active tenant found for this unit.'); window.location.href='my_properties.php';</script>";
    exit();
}

$tenant_id = $tenant_r['tenant_id'];

$query = "
  SELECT * FROM payments 
  WHERE unit_id = $unit_id AND receiver_id = $tenant_id AND receiver_type = 'Tenant'
  ORDER BY payment_date DESC
";
$payments = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Tenant Payment Records</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      padding: 20px;
      background: #f8f9fa;
    }

    .container {
      max-width: 600px;
      margin: auto;
      background: #fff;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    h2 {
      text-align: center;
      color: #007bff;
      margin-bottom: 20px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 14px;
    }

    th, td {
      padding: 10px;
      text-align: center;
      border-bottom: 1px solid #ddd;
    }

    th {
      background-color: #f1f1f1;
    }

    .Paid { color: green; font-weight: bold; }
    .Pending { color: orange; font-weight: bold; }
    .Reviewing { color: #ffc107; font-weight: bold; }
    .Overdue, .Pastdue { color: red; font-weight: bold; }

    @media (max-width: 600px) {
      table { font-size: 13px; }
    }
  </style>
</head>
<body>
  <div class="container">
      	<div class="back" style="text-align: left; margin-bottom: 10px;">
   <a href="tenant_info.php?unit_id=<?= $unit_id ?>" style="text-decoration: none; font-size: 18px; color: #007bff;">
        <i class="fa-solid fa-arrow-left"></i> Back
    </a>
</div>
    <h2>Payment Records</h2>
    <?php if (mysqli_num_rows($payments) > 0): ?>
      <table>
        <thead>
          <tr>
            <th>Month</th>
            <th>Amount</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = mysqli_fetch_assoc($payments)): ?>
            <tr>
              <td><?= date("F Y", strtotime($row['payment_date'])) ?></td>
              <td>₱<?= number_format($row['total_amount'], 2) ?></td>
              <td class="<?= htmlspecialchars($row['payment_status']) ?>"><?= htmlspecialchars($row['payment_status']) ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p style="text-align:center; color:#888;">No payment records found.</p>
    <?php endif; ?>
  </div>
</body>
</html>
