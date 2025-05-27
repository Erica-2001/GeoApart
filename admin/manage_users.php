<?php
session_start();
include("config.php");

$users = $pdo->query("SELECT * FROM users")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Users</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h2>Manage Users</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($users as $user) : ?>
            <tr>
                <td><?= $user["id"]; ?></td>
                <td><?= $user["name"]; ?></td>
                <td><?= $user["email"]; ?></td>
                <td><?= $user["role"]; ?></td>
                <td>
                    <a href="edit_user.php?id=<?= $user["id"]; ?>">Edit</a>
                    <a href="delete_user.php?id=<?= $user["id"]; ?>" onclick="return confirm('Are you sure?');">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
