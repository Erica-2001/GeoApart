<?php
session_start();
include("config.php");

// Fetch all apartments with landlord names
$query = "SELECT apartments.*, users.name AS landlord_name FROM apartments 
          JOIN users ON apartments.landlord_id = users.id";
$apartments = $pdo->query($query)->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Apartments</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h2>Manage Apartments</h2>
    <a href="add_apartment.php">+ Add New Apartment</a>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Location</th>
            <th>Price</th>
            <th>Landlord</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($apartments as $apartment) : ?>
            <tr>
                <td><?= $apartment["id"]; ?></td>
                <td><?= $apartment["name"]; ?></td>
                <td><?= $apartment["location"]; ?></td>
                <td><?= $apartment["price"]; ?></td>
                <td><?= $apartment["landlord_name"]; ?></td>
                <td><?= $apartment["status"]; ?></td>
                <td>
                    <a href="edit_apartment.php?id=<?= $apartment["id"]; ?>">Edit</a>
                    <a href="delete_apartment.php?id=<?= $apartment["id"]; ?>" onclick="return confirm('Are you sure?');">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
