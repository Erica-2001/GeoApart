<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'Landlord') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['tenant_id'])) {
    echo "<script>alert('No tenant selected.'); window.location.href='landlord_dashboard.php';</script>";
    exit();
}

$tenant_id = intval($_GET['tenant_id']);

// Fetch tenant details
$tenant_query = "SELECT name, email FROM users WHERE id = ?";
$stmt = $conn->prepare($tenant_query);
$stmt->bind_param("i", $tenant_id);
$stmt->execute();
$tenant_result = $stmt->get_result();
$tenant = $tenant_result->fetch_assoc();

if (!$tenant) {
    echo "<script>alert('Tenant not found.'); window.location.href='landlord_dashboard.php';</script>";
    exit();
}

// Fetch tenant payment + rental history
$history_query = "
    SELECT p.*, a.name AS apartment_name, au.unit_number,
           tr.rental_start_date, tr.rental_end_date, tr.status AS rental_status
    FROM payments p
    JOIN apartments a ON p.apartment_id = a.id
    JOIN apartment_units au ON p.unit_id = au.id
    LEFT JOIN tenant_rentals tr ON tr.unit_id = p.unit_id AND tr.tenant_id = p.sender_id
    WHERE p.receiver_id = ?
    ORDER BY p.payment_date DESC
";
$stmt = $conn->prepare($history_query);
$stmt->bind_param("i", $tenant_id);
$stmt->execute();
$history_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant History</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f4f4f4; padding: 20px; }
        .container { max-width: 950px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #007bff; margin-bottom: 10px; }
        .tenant-info { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 15px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
        th { background: #007bff; color: white; }
        .status.Paid { background: #28a745; color: white; padding: 5px 10px; border-radius: 5px; }
        .status.Pending { background: #ffc107; color: black; padding: 5px 10px; border-radius: 5px; }
        .status.Overdue { background: #dc3545; color: white; padding: 5px 10px; border-radius: 5px; }
        .back-btn { display: inline-block; margin-top: 20px; text-decoration: none; background: #007bff; color: white; padding: 10px 16px; border-radius: 5px; }
        .back-btn:hover { background: #0056b3; }
    </style>
</head>
<body>
<div class="container">
    <h2>🧾 Tenant Payment & Rental History</h2>
    <div class="tenant-info">
        <p><strong>Name:</strong> <?= htmlspecialchars($tenant['name']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($tenant['email']) ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Apartment</th>
                <th>Unit</th>
                <th>Amount</th>
                <th>Rental Start</th>
                <th>Rental End</th>
                <th>Rental Status</th>
                <th>Payment Status</th>
                <th>Payment Date</th>
            </tr>
        </thead>
        <tbody>
            <?php $count = 1; while ($row = $history_result->fetch_assoc()): ?>
                <tr>
                    <td><?= $count++ ?></td>
                    <td><?= htmlspecialchars($row['apartment_name']) ?></td>
                    <td><?= htmlspecialchars($row['unit_number']) ?></td>
                    <td>₱<?= number_format($row['total_amount'], 2) ?></td>
                    <td><?= $row['rental_start_date'] ?? '—' ?></td>
                    <td><?= $row['rental_end_date'] ?? '—' ?></td>
                    <td><?= $row['rental_status'] ?? '—' ?></td>
                    <td><span class="status <?= $row['payment_status'] ?>"><?= $row['payment_status'] ?></span></td>
                    <td><?= date('M d, Y', strtotime($row['payment_date'])) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div style="text-align:center;">
        <a href="landlord_dashboard.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </div>
</div>
</body>
</html>
