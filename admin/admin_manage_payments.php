<?php
session_start();
if (!isset($_SESSION["admin_id"])) {
    header("Location: admin_login.php");
    exit();
}

include("../db_connect.php");

// Fetch Payments (No send bill, just manage payments)
$query = "SELECT payments.*, sender.name AS sender_name, receiver.name AS receiver_name, 
                 apartments.name AS apartment, apartment_units.unit_number
          FROM payments 
          JOIN users AS sender ON payments.sender_id = sender.id 
          JOIN users AS receiver ON payments.receiver_id = receiver.id 
          JOIN apartments ON payments.apartment_id = apartments.id
          JOIN apartment_units ON payments.unit_id = apartment_units.id
          ORDER BY payments.payment_date DESC";
$result = mysqli_query($conn, $query);

// Total Revenue
$total_query = "SELECT SUM(total_amount) AS total FROM payments WHERE payment_status = 'Paid'";
$total_result = mysqli_query($conn, $total_query);
$total_data = mysqli_fetch_assoc($total_result);
$total_revenue = $total_data['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Apartments</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/your-kit-id.js" crossorigin="anonymous"></script> <!-- Replace your-kit-id -->

    <style>
        body {
            background: #f8f9fa;
            font-family: 'Poppins', sans-serif;
            padding: 20px;
        }
        .container-manage {
            max-width: 1100px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0px 5px 15px rgba(0,0,0,0.1);
        }
        .header-title {
            text-align: center;
            font-weight: 700;
            color: #007bff;
            margin-bottom: 20px;
        }
        .back-btn {
            margin-bottom: 20px;
        }
        .payment-card {
            background: #ffffff;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0px 4px 12px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        .payment-card:hover {
            transform: translateY(-3px);
            box-shadow: 0px 6px 15px rgba(0,0,0,0.15);
        }
        .payment-info p {
            margin-bottom: 6px;
            font-size: 15px;
            color: #555;
        }
        .btn-action {
            padding: 6px 12px;
            font-size: 14px;
            border-radius: 20px;
        }
        .badge-status {
            padding: 5px 12px;
            border-radius: 30px;
            font-size: 13px;
        }
        .badge-paid { background: #28a745; color: white; }
        .badge-pending { background: #ffc107; color: black; }
        .badge-reviewing { background: #17a2b8; color: white; }
    </style>
</head>
<body>

<div class="container-manage">

    <div class="back-btn">
        <a href="admin_dashboard.php" class="btn btn-outline-primary rounded-pill">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <h2 class="header-title">🧾 Manage Payments</h2>

    <div class="mb-3">
        <strong>Total Revenue (Paid): ₱<?= number_format($total_revenue, 2) ?></strong>
        <a href="export_pdf.php" class="btn btn-success btn-sm float-end">Export to PDF</a>
    </div>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <div class="payment-card">
                <div class="payment-info">
                    <p><strong>Bill ID:</strong> #<?= $row['id'] ?></p>
                    <p><strong>Sender:</strong> <?= htmlspecialchars($row['sender_name']) ?></p>
                    <p><strong>Tenant:</strong> <?= htmlspecialchars($row['receiver_name']) ?></p>
                    <p><strong>Apartment:</strong> <?= htmlspecialchars($row['apartment']) ?> (Unit <?= htmlspecialchars($row['unit_number']) ?>)</p>
                    <p><strong>Amount:</strong> ₱<?= number_format($row['total_amount'], 2) ?></p>
                    <p><strong>Status:</strong> 
                        <?php if ($row['payment_status'] == 'Paid'): ?>
                            <span class="badge-status badge-paid">Paid</span>
                        <?php elseif ($row['payment_status'] == 'Pending'): ?>
                            <span class="badge-status badge-pending">Pending</span>
                        <?php elseif ($row['payment_status'] == 'Reviewing'): ?>
                            <span class="badge-status badge-reviewing">Reviewing</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Unknown</span>
                        <?php endif; ?>
                    </p>
                </div>

                <form method="POST" class="d-flex align-items-center gap-2 mt-2">
                    <input type="hidden" name="payment_id" value="<?= $row['id'] ?>">
                    <select name="status" class="form-control form-control-sm" required>
                        <option value="Pending" <?= $row['payment_status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="Reviewing" <?= $row['payment_status'] == 'Reviewing' ? 'selected' : '' ?>>Reviewing</option>
                        <option value="Paid" <?= $row['payment_status'] == 'Paid' ? 'selected' : '' ?>>Paid</option>
                    </select>
                    <button type="submit" name="update_payment" class="btn btn-warning btn-action">Update</button>
                    <?php if (!empty($row['payment_proof'])): ?>
                        <a href="../uploads/payments/<?= urlencode($row['payment_proof']) ?>" target="_blank" class="btn btn-info btn-action">Proof</a>
                    <?php endif; ?>
                </form>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p class="text-center text-muted">No payments found.</p>
    <?php endif; ?>

</div>

</body>
</html>
