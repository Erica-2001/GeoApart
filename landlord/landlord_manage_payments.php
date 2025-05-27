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
    // Add notification for the tenant
    $notif_msg = "📬 New billing statement has been sent for your unit. Please review and make payment.";
    $notif_stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    $notif_stmt->bind_param("is", $receiver_id, $notif_msg);
    $notif_stmt->execute();
    $notif_stmt->close();

    echo "<script>alert('Bill sent successfully and tenant notified!'); window.location.href='landlord_manage_payments.php';</script>";
}
 else {
        echo "<script>alert('Error: Unable to send bill.');</script>";
    }
    $stmt->close();
}


// Update Payment Status

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_payment_approve'])) {
    $payment_id = intval($_POST['approve_payment_id']);
    $status = 'Paid';
    $query = "UPDATE payments SET payment_status=? WHERE id=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $status, $payment_id);
    if ($stmt->execute()) {
        echo "<script>alert('Payment marked as Paid.'); window.location.href='landlord_manage_payments.php';</script>";
    } else {
        echo "<script>alert('Failed to approve payment.');</script>";
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
    .btn-danger {
  background-color: #dc3545;
  color: #fff;
}
.btn-danger:hover {
  background-color: #c82333;
}
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
    <h3>Send Billing to Tenant</h3>
    <select name="receiver_id" id="receiver_id" required>
        <option value="">Select Tenant</option>
        <?php while ($t = mysqli_fetch_assoc($tenants)): ?>
           <option value="<?= $t['id'] ?>" data-rental='<?= htmlspecialchars(json_encode($rentalData[$t['id']] ?? []), ENT_QUOTES, 'UTF-8') ?>'>
    <?= htmlspecialchars($t['name']) ?>
</option>

        <?php endwhile; ?>
    </select>
    <input type="text" name="apartment_display" id="apartment_display" readonly placeholder="Apartment">
    <input type="text" name="unit_display" id="unit_display" readonly placeholder="Unit">
    <input type="hidden" name="apartment_id" id="apartment_id">
    <input type="hidden" name="unit_id" id="unit_id">
    <input type="number" name="total_amount" id="total_amount" readonly placeholder="Amount">
    <button type="submit" name="send_bill" class="btn btn-send">Send Billing Statement</button>
</form>

<!-- Insert inside your loop -->
<div class="card-list">
  <?php while ($row = mysqli_fetch_assoc($result)): ?>
    <div class="payment-card">
      <p><strong>PID:</strong> #<?= $row['id'] ?></p>
      <p><strong>Sender:</strong> <?= htmlspecialchars($row['sender_name']) ?></p>
      <p><strong>Tenant:</strong> <?= htmlspecialchars($row['receiver_name']) ?></p>
      <p><strong>Apartment:</strong> <?= htmlspecialchars($row['apartment']) ?></p>
      <p><strong>Unit:</strong> <?= htmlspecialchars($row['unit_number']) ?></p>
      <p><strong>Amount:</strong> ₱<?= number_format($row['total_amount'], 2) ?></p>
      <p><strong>Status:</strong> <span class="payment-status <?= strtolower($row['payment_status']) ?>"><?= $row['payment_status'] ?></span></p>

      <?php if (strtolower($row['payment_status']) !== 'paid'): ?>
        <form method="POST">
          <input type="hidden" name="payment_id" value="<?= $row['id'] ?>">
          
        </form>
      <?php endif; ?>

      <?php if (!empty($row['payment_proof'])): ?>
        <button class="btn btn-proof" onclick='showProofModal(<?= json_encode([
          "id" => $row["id"],
          "total_amount" => $row["total_amount"],
          "payment_proof" => $row["payment_proof"]
        ]) ?>)'>View Proof</button>
      <?php endif; ?>
    </div>
  <?php endwhile; ?>
</div>



<div id="proofModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background-color:rgba(0,0,0,0.7); z-index:9999; overflow:auto;">
  <div style="
    background:#fff; 
    margin:5% auto; 
    padding:20px; 
    max-width:600px; 
    max-height:90vh; 
    overflow-y:auto; 
    border-radius:10px; 
    position:relative;">
    
    <span onclick="closeProofModal()" style="position:absolute; top:10px; right:15px; font-size:26px; font-weight:bold; color:#333; cursor:pointer;">&times;</span>
    
    <div id="proofDetails"></div>
    
    <img id="proofImage" src="" alt="Proof of Payment" style="max-width:100%; height:auto; border-radius:10px;"><br><br>

    <!-- Button Area -->
    <div id="modalButtons" style="text-align: right;">
      <form method="POST" style="display:inline;">
        <input type="hidden" name="approve_payment_id" id="approve_payment_id">
        <button type="submit" class="btn btn-update" name="update_payment_approve">Approve</button>
      </form>
      <form method="POST" action="decline_payment.php" style="display:inline;">
        <input type="hidden" name="decline_payment_id" id="decline_payment_id">
        <button type="submit" class="btn btn-danger" name="decline_payment">Decline</button>
      </form>
    </div>
  </div>
</div>







<script>
$('#receiver_id').on('change', function () {
    const selected = $(this).find(':selected');
    let rental = selected.data('rental');

    if (typeof rental === "string") {
        try {
            rental = JSON.parse(rental);
        } catch (e) {
            rental = null;
        }
    }

    if (rental && rental.apartment_name) {
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

</script>

<script>
function showProofModal(data) {
  document.getElementById('proofDetails').innerHTML = `
    <p><strong>Bill ID:</strong> #${data.id}</p>
    <p><strong>Total Amount:</strong> ₱${parseFloat(data.total_amount).toLocaleString(undefined, {minimumFractionDigits: 2})}</p>
  `;
  const proofPath = data.payment_proof.startsWith('proof_') ? `../uploads/payments/${encodeURIComponent(data.payment_proof)}` : data.payment_proof;
  document.getElementById('proofImage').src = proofPath;
  document.getElementById('approve_payment_id').value = data.id;
  document.getElementById('proofModal').style.display = 'block';
}
function closeProofModal() {
  document.getElementById('proofModal').style.display = 'none';
}



</script>

<script>
function showProofModal(data) {
  document.getElementById('proofDetails').innerHTML = `
    <p><strong>Bill ID:</strong> #${data.id}</p>
    <p><strong>Total Amount:</strong> ₱${parseFloat(data.total_amount).toLocaleString(undefined, {minimumFractionDigits: 2})}</p>
  `;
  const proofPath = data.payment_proof.startsWith('proof_') ? `../uploads/payments/${encodeURIComponent(data.payment_proof)}` : data.payment_proof;
  document.getElementById('proofImage').src = proofPath;
  document.getElementById('approve_payment_id').value = data.id;
  document.getElementById('decline_payment_id').value = data.id;

  // Show buttons only if not paid
  if (data.status && data.status.toLowerCase() === 'paid') {
    document.getElementById('modalButtons').style.display = 'none';
  } else {
    document.getElementById('modalButtons').style.display = 'block';
  }

  document.getElementById('proofModal').style.display = 'block';
}

function closeProofModal() {
  document.getElementById('proofModal').style.display = 'none';
}
</script>

</body>
</html>