<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db_connect.php';

$user_id = $_SESSION['user_id'];
$query = "SELECT name, email, user_type FROM users WHERE id = '$user_id'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

if ($user['user_type'] !== 'Landlord') {
    echo "<script>alert('Access Denied! Only landlords can access this page.'); window.location.href='login.php';</script>";
    exit();
}

// Fetch apartment filter options
$apartment_filter_query = "SELECT id, name FROM apartments WHERE landlord_id = '$user_id'";
$apartment_filter_result = mysqli_query($conn, $apartment_filter_query);

// Handle filter
$filter_apartment_id = $_GET['apartment_id'] ?? '';
$filter_condition = $filter_apartment_id ? "AND apartments.id = '$filter_apartment_id'" : '';

$payments_query = "
    SELECT 
        payments.id, users.id AS tenant_id, users.name AS tenant_name, apartments.name AS apartment,
        apartments.id AS apartment_id, apartment_units.unit_number, payments.total_amount, 
        payments.payment_status, payments.payment_date
    FROM payments
    JOIN users ON payments.receiver_id = users.id
    JOIN apartments ON payments.apartment_id = apartments.id
    JOIN apartment_units ON payments.unit_id = apartment_units.id
    WHERE apartments.landlord_id = '$user_id' $filter_condition
    ORDER BY payments.payment_date DESC
";
$payments_result = mysqli_query($conn, $payments_query);

$total_revenue = 0;
$all_payments = [];
while ($row = mysqli_fetch_assoc($payments_result)) {
    if ($row['payment_status'] === 'Paid') {
        $total_revenue += $row['total_amount'];
    }
    $all_payments[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Landlord Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f4f4f4; padding: 20px; }
        .container { max-width: 1100px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        h2 { color: #007bff; text-align: center; margin-bottom: 20px; }
        .filters, .actions { display: flex; justify-content: space-between; flex-wrap: wrap; gap: 10px; margin-bottom: 20px; }
        select, button { padding: 8px 12px; border-radius: 5px; border: 1px solid #ccc; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: center; font-size: 14px; }
        th { background: #007bff; color: white; }
        .payment-status.Paid { background: #28a745; color: white; padding: 5px 10px; border-radius: 5px; }
        .payment-status.Pending { background: #ffc107; color: black; padding: 5px 10px; border-radius: 5px; }
        .payment-status.Overdue { background: #dc3545; color: white; padding: 5px 10px; border-radius: 5px; }
        .total-revenue { font-weight: bold; font-size: 16px; margin-bottom: 10px; color: green; text-align: right; }
    </style>
</head>
<body>
<div class="container">
    <h2>📊 Tenant Payment Dashboard</h2>

    <div class="filters">
        <form method="GET">
            <label for="apartment_id">Filter by Apartment:</label>
            <select name="apartment_id" onchange="this.form.submit()">
                <option value="">All Apartments</option>
                <?php while ($apt = mysqli_fetch_assoc($apartment_filter_result)): ?>
                    <option value="<?= $apt['id'] ?>" <?= $filter_apartment_id == $apt['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($apt['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </form>

        <div class="actions">
            <button onclick="window.print()"><i class="fas fa-print"></i> Print</button>
            <button onclick="exportToPDF()"><i class="fas fa-file-pdf"></i> Export PDF</button>
        </div>
    </div>

    <div class="total-revenue">
        Total Revenue Collected: ₱<?= number_format($total_revenue, 2) ?>
    </div>

    <div id="payment-table">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tenant</th>
                    <th>Apartment</th>
                    <th>Unit</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>History</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($all_payments as $index => $payment): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= htmlspecialchars($payment['tenant_name']) ?></td>
                        <td><?= htmlspecialchars($payment['apartment']) ?></td>
                        <td><?= htmlspecialchars($payment['unit_number']) ?></td>
                        <td>₱<?= number_format($payment['total_amount'], 2) ?></td>
                        <td><span class="payment-status <?= $payment['payment_status'] ?>"><?= $payment['payment_status'] ?></span></td>
                        <td><?= date('M d, Y', strtotime($payment['payment_date'])) ?></td>
                        <td><a href="tenant_history.php?tenant_id=<?= $payment['tenant_id'] ?>">View</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function exportToPDF() {
    const element = document.getElementById("payment-table");
    html2pdf().from(element).save("payment_records.pdf");
}
</script>
</body>
</html>
