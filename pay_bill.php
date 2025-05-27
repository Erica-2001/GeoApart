<?php 
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'Tenant') {
    header("Location: login.php");
    exit();
}

include("db_connect.php");

$user_id = $_SESSION['user_id'];

if (!isset($_GET['bill_id']) || !is_numeric($_GET['bill_id'])) {
    die("Invalid bill ID.");
}

$bill_id = intval($_GET['bill_id']);

// Fetch bill details
$query = "SELECT payments.*, apartments.name AS apartment_name, apartment_units.unit_number
          FROM payments 
          JOIN apartments ON payments.apartment_id = apartments.id
          JOIN apartment_units ON payments.unit_id = apartment_units.id
          WHERE payments.id = ? AND payments.receiver_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $bill_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Bill not found or access denied.");
}

$bill = $result->fetch_assoc();

// Handle payment upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay_now'])) {
    if (isset($_FILES['proof']) && $_FILES['proof']['error'] === 0) {
        $target_dir = "uploads/payments/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

        $filename = "proof_" . $bill_id . "_" . time() . "_" . basename($_FILES['proof']['name']);
        $target_file = $target_dir . $filename;
        $filetype = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $allowed_types = ['jpg', 'jpeg', 'png', 'pdf'];
        if (!in_array($filetype, $allowed_types)) {
            echo "<script>alert('Invalid file type. Only JPG, PNG, and PDF allowed.');</script>";
        } elseif (move_uploaded_file($_FILES['proof']['tmp_name'], $target_file)) {
            // ✅ Set payment status to Reviewing
            $update = $conn->prepare("UPDATE payments SET payment_status = 'Reviewing', payment_proof = ? WHERE id = ? AND receiver_id = ?");
            $update->bind_param("sii", $filename, $bill_id, $user_id);

            if ($update->execute()) {
                echo "<script>alert('Proof of payment submitted successfully. Status: Reviewing'); window.location.href='tenant_bills.php';</script>";
            } else {
                echo "<script>alert('Error saving your payment. Please try again.');</script>";
            }
            $update->close();
        } else {
            echo "<script>alert('Failed to upload file. Please try again.');</script>";
        }
    } else {
        echo "<script>alert('Please upload a valid proof of payment.');</script>";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pay Bill | GeoApart</title>
    <style>
        body { background: #f4f4f4; padding: 20px; font-family: 'Poppins', sans-serif; }
        .container { max-width: 600px; margin: auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #007bff; margin-bottom: 20px; }
        .details p { margin: 10px 0; font-size: 16px; }
        .btn-pay { background: #28a745; color: white; padding: 10px 20px; border: none; font-size: 16px; border-radius: 5px; cursor: pointer; }
        .btn-pay:hover { background: #218838; }
        .file-label { margin: 10px 0; }
    </style>
</head>
<body>
<div class="back" style="text-align: left; margin-bottom: 10px;">
    <a href="tenant_bills.php" style="text-decoration: none; font-size: 18px; color: #007bff;">
        <i class="fa-solid fa-arrow-left"></i> Back
    </a>
</div>
    <h2>Pay Your Bill</h2>
    <div class="details">
        <p><strong>Bill ID:</strong> #<?= htmlspecialchars($bill['id']) ?></p>
        <p><strong>Apartment:</strong> <?= htmlspecialchars($bill['apartment_name']) ?></p>
        <p><strong>Unit:</strong> Unit <?= htmlspecialchars($bill['unit_number']) ?></p>
        <p><strong>Total Amount:</strong> ₱<?= number_format($bill['total_amount'], 2) ?></p>
        <p><strong>Status:</strong> <?= htmlspecialchars($bill['payment_status']) ?></p>
    </div>
    <?php if ($bill['payment_status'] === 'Pending'): ?>
        <form method="POST" enctype="multipart/form-data">
            <label class="file-label">Upload Proof of Payment (JPG, PNG, PDF):</label><br>
            <input type="file" name="proof" accept=".jpg,.jpeg,.png,.pdf" required><br><br>
            <button type="submit" name="pay_now" class="btn-pay">Send</button>
        </form>
    <?php else: ?>
        <p style="color: #17a2b8; font-weight: bold;">
            <?= $bill['payment_status'] === 'Reviewing' ? '⏳ Your payment is under review.' : '✔ Already Paid' ?>
        </p>
        <?php if (!empty($bill['payment_proof'])): ?>
    <div style="margin-top: 20px; text-align: center;">
        <h3>Submitted Proof of Payment</h3>
        <?php
        $proof_path = "uploads/payments/" . htmlspecialchars($bill['payment_proof']);
        $extension = strtolower(pathinfo($proof_path, PATHINFO_EXTENSION));
        if (in_array($extension, ['jpg', 'jpeg', 'png'])):
        ?>
            <img src="<?= $proof_path ?>" alt="Proof of Payment" style="max-width: 100%; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.2);">
        <?php elseif ($extension === 'pdf'): ?>
            <a href="<?= $proof_path ?>" target="_blank" style="text-decoration: none; color: #007bff; font-weight: bold;">
                📄 View Uploaded PDF
            </a>
        <?php else: ?>
            <p>No valid preview available for this file.</p>
        <?php endif; ?>
    </div>
<?php endif; ?>

    <?php endif; ?>
</div>
</body>
</html>
