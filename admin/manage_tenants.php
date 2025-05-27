<?php
session_start();
include("config.php");

// Fetch tenants with user and apartment details
$query = "SELECT tenants.*, users.name AS tenant_name, apartments.name AS apartment_name 
          FROM tenants 
          JOIN users ON tenants.user_id = users.id
          JOIN apartments ON tenants.apartment_id = apartments.id";
$tenants = $pdo->query($query)->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Tenants</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h2>Manage Tenants</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Apartment</th>
            <th>Lease Start</th>
            <th>Lease End</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($tenants as $tenant) : ?>
            <tr>
                <td><?= $tenant["id"]; ?></td>
                <td><?= $tenant["tenant_name"]; ?></td>
                <td><?= $tenant["apartment_name"]; ?></td>
                <td><?= $tenant["lease_start"]; ?></td>
                <td><?= $tenant["lease_end"]; ?></td>
                <td><?= $tenant["status"]; ?></td>
                <td>
                    <a href="edit_tenant.php?id=<?= $tenant["id"]; ?>">Edit</a>
                    <a href="delete_tenant.php?id=<?= $tenant["id"]; ?>" onclick="return confirm('Are you sure?');">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
