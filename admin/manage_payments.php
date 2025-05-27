<?php
session_start();
include("config.php");

// Fetch payments with tenant and apartment details
$query = "SELECT payments.*, users.name AS tenant_name, apartments.name AS apartment_name 
          FROM payments 
          JOIN tenants ON payments.tenant_id = tenants.id
          JOIN users ON tenants.user_id = users.id
          JOIN apartments ON payments.apartment_id = apartments.id";
$payments = $pdo->query($query)->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Payments</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h2>Manage Payments</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Tenant</th>
            <th>Apartment</th>
            <th>Amount</th>
            <th>Due Date</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($payments as $payment) : ?>
            <tr>
                <td><?= $payment["id"]; ?></td>
                <td><?= $payment["tenant_name"]; ?></td>
                <td><?= $payment["apartment_name"]; ?></td>
                <td><?= $payment["amount"]; ?></td>
                <td><?= $payment["due_date"]; ?></td>
                <td><?= $payment["status"]; ?></td>
                <td>
                    <?php if ($payment["status"] == "pending") : ?>
                        <a href="mark_paid.php?id=<?= $payment["id"]; ?>">Mark as Paid</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
