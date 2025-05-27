<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION['user_type'] !== 'Landlord') {
    header("Location: login.php");
    exit();
}

include("../db_connect.php");

$landlord_id = $_SESSION['user_id'];

// Handle New Bill Creation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['send_bill'])) {
    $receiver_id = intval($_POST['receiver_id']);
    $apartment_id = intval($_POST['apartment_id']);
    $unit_id = intval($_POST['unit_id']);
    $total_amount = floatval($_POST['total_amount']);
    $sender_type = "Landlord";

    $query = "INSERT INTO payments (sender_id, sender_type, receiver_id, apartment_id, unit_id, total_amount, payment_status) 
              VALUES (?, ?, ?, ?, ?, ?, 'Pending')";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("issiid", $landlord_id, $sender_type, $receiver_id, $apartment_id, $unit_id, $total_amount);

    if ($stmt->execute()) {
        echo "<script>alert('Bill sent successfully!'); window.location.href='landlord_manage_payments.php';</script>";
    } else {
        echo "<script>alert('Error: Unable to send bill.');</script>";
    }
    $stmt->close();
}

// Update Payment Status
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_payment'])) {
    $payment_id = intval($_POST['payment_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    $query = "UPDATE payments SET payment_status=? WHERE id=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $status, $payment_id);

    if ($stmt->execute()) {
        echo "<script>alert('Payment updated successfully!'); window.location.href='landlord_manage_payments.php';</script>";
    } else {
        echo "<script>alert('Error: Unable to update payment.');</script>";
    }
    $stmt->close();
}

// Fetch tenants for this landlord
$rentalData = [];
$tenants = mysqli_query($conn, "
    SELECT DISTINCT u.id, u.name
    FROM tenant_rentals tr
    JOIN users u ON tr.tenant_id = u.id
    WHERE tr.status = 'Active' AND tr.landlord_id = $landlord_id
");

$rentals = mysqli_query($conn, "
    SELECT tr.tenant_id, tr.apartment_id, tr.unit_id, a.name AS apartment_name, u.unit_number, u.unit_price 
    FROM tenant_rentals tr
    JOIN apartments a ON tr.apartment_id = a.id
    JOIN apartment_units u ON tr.unit_id = u.id
    WHERE tr.status = 'Active' AND tr.landlord_id = $landlord_id");
while ($r = mysqli_fetch_assoc($rentals)) {
    $rentalData[$r['tenant_id']] = $r;
}

$whereClause = "WHERE p.sender_id = $landlord_id AND p.sender_type = 'Landlord'";
if (!empty($_GET['filter_apartment'])) {
    $filter_apartment = intval($_GET['filter_apartment']);
    $whereClause .= " AND p.apartment_id = $filter_apartment";
}
if (!empty($_GET['filter_unit'])) {
    $filter_unit = intval($_GET['filter_unit']);
    $whereClause .= " AND p.unit_id = $filter_unit";
}

$query = "SELECT p.*, sender.name AS sender_name, receiver.name AS receiver_name, 
                 a.name AS apartment, u.unit_number
          FROM payments p
          JOIN users sender ON p.sender_id = sender.id
          JOIN users receiver ON p.receiver_id = receiver.id
          JOIN apartments a ON p.apartment_id = a.id
          JOIN apartment_units u ON p.unit_id = u.id
          $whereClause
          ORDER BY p.payment_date DESC";
$result = mysqli_query($conn, $query);

$apartments = mysqli_query($conn, "SELECT DISTINCT id, name FROM apartments WHERE landlord_id = $landlord_id");
$units = mysqli_query($conn, "SELECT DISTINCT id, unit_number FROM apartment_units WHERE apartment_id IN (SELECT id FROM apartments WHERE landlord_id = $landlord_id)");

$totalRevenueQuery = "SELECT SUM(total_amount) AS total FROM payments WHERE sender_id = $landlord_id AND payment_status = 'Paid'";
$totalResult = mysqli_query($conn, $totalRevenueQuery);
$totalRevenue = mysqli_fetch_assoc($totalResult)['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Landlord Manage Payments</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    body {
      background: #f4f4f4;
      padding: 20px;
      font-family: 'Poppins', sans-serif;
    }
    h2 {
      text-align: center;
      color: #007bff;
      margin-bottom: 20px;
    }
    .filters,
    .send-bill-form {
      margin-top: 20px;
      background: #e9ecef;
      padding: 20px;
      border-radius: 10px;
    }
    .filters select,
    .send-bill-form select,
    .send-bill-form input {
      padding: 12px;
      margin: 8px 0;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 15px;
      width: 100%;
    }
    .btn {
      padding: 10px 16px;
      border-radius: 6px;
      font-size: 14px;
      cursor: pointer;
      color: white;
      border: none;
      transition: background 0.3s;
      margin: 4px 2px;
    }
    .btn-send { background: #28a745; }
    .btn-send:hover { background: #218838; }
    .btn-proof { background: #17a2b8; }
    .btn-proof:hover { background: #138496; }
    .total-summary {
      font-weight: bold;
      margin-top: 15px;
      font-size: 16px;
      text-align: right;
    }
    .payment-card {
      background: #fff;
      padding: 15px;
      border-radius: 12px;
      margin-bottom: 15px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      font-size: 14px;
    }
    .payment-card p {
      margin: 6px 0;
      line-height: 1.4;
    }
  </style>
</head>
<body>

<div class="back" style="text-align: left; margin-bottom: 10px;">
    <a href="landlord_dashboard.php" style="text-decoration: none; font-size: 18px; color: #007bff;">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
</div>
<h2>Payments Reports</h2>

<form method="GET" class="filters">
    <label>Filter by Apartment:</label>
    <select name="filter_apartment">
        <option value="">All</option>
        <?php while ($a = mysqli_fetch_assoc($apartments)): ?>
            <option value="<?= $a['id'] ?>" <?= (isset($_GET['filter_apartment']) && $_GET['filter_apartment'] == $a['id']) ? 'selected' : '' ?>><?= htmlspecialchars($a['name']) ?></option>
        <?php endwhile; ?>
    </select>
    <label>Unit:</label>
    <select name="filter_unit">
        <option value="">All</option>
        <?php while ($u = mysqli_fetch_assoc($units)): ?>
            <option value="<?= $u['id'] ?>" <?= (isset($_GET['filter_unit']) && $_GET['filter_unit'] == $u['id']) ? 'selected' : '' ?>>Unit <?= htmlspecialchars($u['unit_number']) ?></option>
        <?php endwhile; ?>
    </select>
    <button type="submit" class="btn btn-send">Filter</button>
    <button type="button" onclick="window.print()" class="btn btn-proof">Export to PDF</button>
    <div class="total-summary">Total Revenue: ₱<?= number_format($totalRevenue, 2) ?></div>
</form>

<?php while ($row = mysqli_fetch_assoc($result)): ?>
  <div class="payment-card">
    <p><strong>PID:</strong> #<?= $row['id'] ?></p>
    <p><strong>Sender:</strong> <?= htmlspecialchars($row['sender_name']) ?></p>
    <p><strong>Tenant:</strong> <?= htmlspecialchars($row['receiver_name']) ?></p>
    <p><strong>Apartment:</strong> <?= htmlspecialchars($row['apartment']) ?></p>
    <p><strong>Unit:</strong> <?= htmlspecialchars($row['unit_number']) ?></p>
    <p><strong>Amount:</strong> ₱<?= number_format($row['total_amount'], 2) ?></p>
    <p><strong>Status:</strong> <?= htmlspecialchars($row['payment_status']) ?></p>
  </div>
<?php endwhile; ?>

</body>
</html>
