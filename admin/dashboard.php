<?php
session_start();
if (!isset($_SESSION["admin_id"])) {
    header("Location: admin_login.php");
    exit();
}

include("config.php");

// Fetch Data for Dashboard Statistics
try {
    $totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $totalApartments = $pdo->query("SELECT COUNT(*) FROM apartments")->fetchColumn();
    $totalTenants = $pdo->query("SELECT COUNT(*) FROM users WHERE user_type = 'Tenant'")->fetchColumn();
    $totalPayments = $pdo->query("SELECT COUNT(*) FROM payments")->fetchColumn();

    // Fetch Latest Payments (Limit to 5 for quick view)
    $payments = $pdo->query("
        SELECT payments.*, users.name AS tenant, apartments.name AS apartment 
        FROM payments
        JOIN users ON payments.user_id = users.id 
        JOIN apartments ON payments.apartment_id = apartments.id 
        ORDER BY payment_date DESC LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | GeoApart</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        body {
            background: #f4f4f4;
        }
        nav {
            background: #007bff;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
        }
        nav h2 {
            margin-left: 15px;
        }
        nav a {
            color: white;
            text-decoration: none;
            margin-right: 15px;
            padding: 8px 12px;
            background: #0056b3;
            border-radius: 5px;
            transition: 0.3s;
        }
        nav a:hover {
            background: #003f7f;
        }
        .dashboard-container {
            max-width: 1100px;
            margin: 50px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .dashboard-container h1 {
            color: #333;
            margin-bottom: 20px;
        }
        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .stat-card {
            background: #007bff;
            padding: 20px;
            border-radius: 8px;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 18px;
            font-weight: bold;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.2);
        }
        .stat-card i {
            font-size: 24px;
        }
        .payments-section {
            margin-top: 30px;
            text-align: left;
        }
        .payments-section h3 {
            color: #007bff;
            border-bottom: 2px solid #007bff;
            padding-bottom: 8px;
        }
        .payment-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .payment-table th, .payment-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        .payment-table th {
            background: #007bff;
            color: white;
        }
        .payment-status {
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
        }
        .pending {
            background: #ffc107;
            color: black;
        }
        .paid {
            background: #28a745;
            color: white;
        }
        .overdue {
            background: #dc3545;
            color: white;
        }
        .view-payments-btn {
            display: block;
            width: 200px;
            text-align: center;
            margin: 20px auto;
            background: #007bff;
            color: white;
            padding: 10px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: 0.3s;
        }
        .view-payments-btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>

    <nav>
        <h2>GeoApart Admin</h2>
        <div>
            <a href="manage_users.php"><i class="fas fa-users"></i> Users</a>
            <a href="manage_apartments.php"><i class="fas fa-building"></i> Apartments</a>
            <a href="manage_tenants.php"><i class="fas fa-user-friends"></i> Tenants</a>
            <a href="manage_payments.php"><i class="fas fa-money-bill-wave"></i> Payments</a>
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </nav>

    <div class="dashboard-container">
        <h1>Welcome, <?= htmlspecialchars($_SESSION["admin_name"]); ?>!</h1>
        <div class="dashboard-stats">
            <div class="stat-card">
                <i class="fas fa-user"></i> Total Users: <?= $totalUsers; ?>
            </div>
            <div class="stat-card">
                <i class="fas fa-building"></i> Total Apartments: <?= $totalApartments; ?>
            </div>
            <div class="stat-card">
                <i class="fas fa-user-friends"></i> Total Tenants: <?= $totalTenants; ?>
            </div>
            <div class="stat-card">
                <i class="fas fa-dollar-sign"></i> Total Payments: <?= $totalPayments; ?>
            </div>
        </div>

        <!-- RECENT PAYMENTS SECTION -->
        <div class="payments-section">
            <h3>Recent Payments</h3>
            <table class="payment-table">
                <tr>
                    <th>Tenant</th>
                    <th>Apartment</th>
                    <th>Amount</th>
                    <th>Status</th>
                </tr>
                <?php foreach ($payments as $payment): ?>
                    <tr>
                        <td><?= htmlspecialchars($payment['tenant']); ?></td>
                        <td><?= htmlspecialchars($payment['apartment']); ?></td>
                        <td>₱<?= number_format($payment['total_amount'], 2); ?></td>
                        <td>
                            <span class="payment-status 
                                <?= $payment['payment_status'] === 'Pending' ? 'pending' : ($payment['payment_status'] === 'Paid' ? 'paid' : 'overdue'); ?>">
                                <?= $payment['payment_status']; ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <a href="manage_payments.php" class="view-payments-btn">View All Payments</a>
        </div>
    </div>

</body>
</html>
