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
    .container {
      max-width: 1100px;
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
    .btn-update { background: #ffc107; color: black; }
    .btn-update:hover { background: #e0a800; }
    .btn-proof { background: #17a2b8; }
    .btn-proof:hover { background: #138496; }
    .total-summary {
      font-weight: bold;
      margin-top: 15px;
      font-size: 16px;
      text-align: right;
    }
    .card-list {
      display: flex;
      flex-direction: column;
      gap: 15px;
      margin-top: 20px;
    }
    .payment-card {
  background: #fff;
  padding: 10px;
  border-radius: 12px;
  box-shadow: 0 2px 8px #138496;
  font-size: 14px;
}

.payment-card p {
  margin: 10px 0;
  line-height: 1.5;
}

.payment-card span.label {
  display: block;
  font-weight: 600;
  color: #007bff;
  margin-bottom: 2px;
}

.payment-status {
  font-weight: bold;
  display: inline-block;
}

.payment-status.pending {
  color: #e67e22;
}

.payment-status.reviewing {
  color: #17a2b8;
}

.payment-status.paid {
  color: #28a745;
}

.btn-action {
  background-color: #28a745;
  color: #fff;
  padding: 8px 18px;
  border: none;
  border-radius: 6px;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  margin-top: 10px;
  transition: background 0.3s;
}
.btn-action:hover {
  background-color: #218838;
}

</style>

</head>
<body>

<div class="back" style="text-align: left; margin-bottom: 10px;">
    <a href="landlord_dashboard.php" style="text-decoration: none; font-size: 18px; color: #007bff;">
        <i class="fa-solid fa-arrow-left"></i> Back
    </a>
</div>
    <h2>Landlord Manage Payments</h2>

   

<form method="POST" class="send-bill-form">
    <h3>Send Bill to Tenant</h3>
    <select name="receiver_id" id="receiver_id" required>
        <option value="">Select Tenant</option>
        <?php while ($t = mysqli_fetch_assoc($tenants)): ?>
            <option value="<?= $t['id'] ?>" data-rental='<?= json_encode($rentalData[$t['id']] ?? []) ?>'><?= htmlspecialchars($t['name']) ?></option>
        <?php endwhile; ?>
    </select>
    <input type="text" name="apartment_display" id="apartment_display" readonly placeholder="Apartment">
    <input type="text" name="unit_display" id="unit_display" readonly placeholder="Unit">
    <input type="hidden" name="apartment_id" id="apartment_id">
    <input type="hidden" name="unit_id" id="unit_id">
    <input type="number" name="total_amount" id="total_amount" readonly placeholder="Amount">
    <button type="submit" name="send_bill" class="btn btn-send">Send Bill</button>
</form>

<div class="payment-card">
    <?php while ($row = mysqli_fetch_assoc($result)): ?>
      <div class="payment-card">
        <p><strong>PID:</strong> #<?= $row['id'] ?></p>
        <p><strong>Sender:</strong> <?= htmlspecialchars($row['sender_name']) ?></p>
        <p><strong>Tenant:</strong> <?= htmlspecialchars($row['receiver_name']) ?></p>
        <p><strong>Apartment:</strong> <?= htmlspecialchars($row['apartment']) ?></p>
        <p><strong>Unit:</strong> <?= htmlspecialchars($row['unit_number']) ?></p>
        <p><strong>Amount:</strong> ₱<?= number_format($row['total_amount'], 2) ?></p>
        <p><strong>Status:</strong> <?= $row['payment_status'] ?></p>
        <form method="POST">
          <input type="hidden" name="payment_id" value="<?= $row['id'] ?>">
          <select name="status">
            <option value="Pending" <?= $row['payment_status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
            <option value="Reviewing" <?= $row['payment_status'] == 'Reviewing' ? 'selected' : '' ?>>Reviewing</option>
            <option value="Paid" <?= $row['payment_status'] == 'Paid' ? 'selected' : '' ?>>Paid</option>
          </select>
          <button type="submit" name="update_payment" class="btn btn-update">Update</button>
        </form>
        <?php if (!empty($row['payment_proof'])): ?>
          <button class="btn btn-proof" onclick='showProofModal(<?= json_encode(["id" => $row["id"], "apartment" => $row["apartment"], "unit_number" => $row["unit_number"], "total_amount" => $row["total_amount"], "payment_status" => $row["payment_status"], "payment_proof" => $row["payment_proof"]]) ?>)'>View Proof</button>
        <?php endif; ?>
      </div>
    <?php endwhile; ?>
  </div>
</div>

<div id="proofModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background-color:rgba(0,0,0,0.7); z-index:9999;">
    <div style="background:white; margin:5% auto; padding:20px; max-width:600px; border-radius:10px;">
        <span onclick="closeProofModal()" style="float:right; font-size:24px; cursor:pointer;">&times;</span>
        <div id="proofDetails"></div>
        <img id="proofImage" src="" alt="Proof" style="width:100%; border-radius:10px; margin-top:15px;">
    </div>
</div>



<script>
$(document).ready(function () {
    $('#receiver_id').on('change', function () {
        const selected = $(this).find(':selected');
        const rental = selected.data('rental');

        if (rental) {
            $('#apartment_display').val(rental.apartment_name);
            $('#unit_display').val('Unit ' + rental.unit_number);
            $('#apartment_id').val(rental.apartment_id);
            $('#unit_id').val(rental.unit_id);
            $('#total_amount').val(rental.unit_price);
        } else {
            $('#apartment_display').val('');
            $('#unit_display').val('');
            $('#apartment_id').val('');
            $('#unit_id').val('');
            $('#total_amount').val('');
        }
    });
});
</script>

<script>
function showProofModal(data) {
    document.getElementById('proofDetails').innerHTML = 
        <p><strong>Bill ID:</strong> #${data.id}</p>
        <p><strong>Apartment:</strong> ${data.apartment}</p>
        <p><strong>Unit:</strong> Unit ${data.unit_number}</p>
        <p><strong>Total Amount:</strong> ₱${parseFloat(data.total_amount).toLocaleString(undefined, {minimumFractionDigits: 2})}</p>
        <p><strong>Status:</strong> ${data.payment_status}</p>
    ;
    document.getElementById('proofImage').src = '../uploads/payments/' + encodeURIComponent(data.payment_proof);
    document.getElementById('proofModal').style.display = 'block';
}
function closeProofModal() {
    document.getElementById('proofModal').style.display = 'none';
}
function exportToPDF() {
    import('https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js').then(jsPDF => {
        const { jsPDF: JSPDF } = jsPDF;
        const doc = new JSPDF();
        doc.text("Landlord Payments", 10, 10);
        doc.autoTable({ html: '#paymentsTable' });
        doc.save('payments.pdf');
    });
}
</script>
</body>
</html>