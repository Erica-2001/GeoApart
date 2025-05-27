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

// Fetch tenants and rentals for this landlord
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

// Fetch payments
$query = "SELECT p.*, sender.name AS sender_name, receiver.name AS receiver_name, 
                 a.name AS apartment, u.unit_number
          FROM payments p
          JOIN users sender ON p.sender_id = sender.id
          JOIN users receiver ON p.receiver_id = receiver.id
          JOIN apartments a ON p.apartment_id = a.id
          JOIN apartment_units u ON p.unit_id = u.id
          WHERE p.sender_id = $landlord_id AND p.sender_type = 'Landlord'";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Landlord Manage Payments</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body { background: #f4f4f4; padding: 20px; font-family: 'Poppins', sans-serif; }
        .container { max-width: 1100px; margin: auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #007bff; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
        th { background: #007bff; color: white; }
        .btn { padding: 8px 12px; border-radius: 5px; font-size: 14px; cursor: pointer; color: white; border: none; }
        .btn-send { background: #28a745; }
        .btn-update { background: #ffc107; color: black; }
        .send-bill-form { margin-top: 20px; background: #e9ecef; padding: 20px; border-radius: 10px; }
        select, input { padding: 8px; margin: 5px; border: 1px solid #ccc; border-radius: 5px; font-size: 14px; }
    </style>
</head>
<body>
<div class="container">
    <h2>Landlord Manage Payments</h2>
    <form method="POST" class="send-bill-form">
        <h3>Send Bill</h3>
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

    <table>
        <tr><th>PID</th><th>Sender</th><th>Tenant</th><th>Apartment</th><th>Unit</th><th>Amount</th><th>Status</th><th>Action</th></tr>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td>#<?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['sender_name']) ?></td>
                <td><?= htmlspecialchars($row['receiver_name']) ?></td>
                <td><?= htmlspecialchars($row['apartment']) ?></td>
                <td><?= htmlspecialchars($row['unit_number']) ?></td>
                <td>₱<?= number_format($row['total_amount'], 2) ?></td>
                <td><?= $row['payment_status'] ?></td>
                <td>
                    <form method="POST">
                        <input type="hidden" name="payment_id" value="<?= $row['id'] ?>">
                        <select name="status">
                            <option value="Pending" <?= $row['payment_status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="Reviewing" <?= $row['payment_status'] == 'Reviewing' ? 'selected' : '' ?>>Reviewing</option>
                            <option value="Paid" <?= $row['payment_status'] == 'Paid' ? 'selected' : '' ?>>Paid</option>
                        </select>
                        <button type="submit" name="update_payment" class="btn btn-update">Update</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
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
</body>
</html>