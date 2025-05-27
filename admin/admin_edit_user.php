<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

include '../db_connect.php';

// Get user ID from URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('Invalid user ID.'); window.location.href='admin_manage_users.php';</script>";
    exit();
}

$user_id = intval($_GET['id']);

// Fetch user details
$query = "SELECT * FROM users WHERE id = '$user_id'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    echo "<script>alert('User not found.'); window.location.href='admin_manage_users.php';</script>";
    exit();
}

// Update User
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_user'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $mobile = mysqli_real_escape_string($conn, $_POST['mobile']);
    $user_type = mysqli_real_escape_string($conn, $_POST['user_type']);

    // Check if password change is requested
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $update_query = "UPDATE users SET name='$name', email='$email', mobile='$mobile', user_type='$user_type', password='$password' WHERE id='$user_id'";
    } else {
        $update_query = "UPDATE users SET name='$name', email='$email', mobile='$mobile', user_type='$user_type' WHERE id='$user_id'";
    }

    if (mysqli_query($conn, $update_query)) {
        echo "<script>alert('User updated successfully!'); window.location.href='admin_manage_users.php';</script>";
    } else {
        echo "<script>alert('Error updating user.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        body {
            background: #f4f4f4;
            text-align: center;
            padding: 20px;
        }
        .profile-container {
            max-width: 500px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #007bff;
            margin-bottom: 15px;
        }
        input, select {
            width: 90%;
            padding: 8px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .update-btn {
            background: #007bff;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s;
        }
        .update-btn:hover {
            background: #0056b3;
        }
        .back-btn {
            display: inline-block;
            margin-top: 15px;
            text-decoration: none;
            color: white;
            background: #dc3545;
            padding: 10px;
            border-radius: 5px;
        }
        .back-btn:hover {
            background: #b02a37;
        }
    </style>
</head>
<body>

    <div class="profile-container">
        <h2>Edit User</h2>
        <form action="admin_edit_user.php?id=<?= $user_id ?>" method="POST">
            <input type="text" name="name" value="<?= htmlspecialchars($user['name']); ?>" required placeholder="Full Name">
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']); ?>" required placeholder="Email">
            <input type="text" name="mobile" value="<?= htmlspecialchars($user['mobile']); ?>" required placeholder="Mobile Number">
            <select name="user_type" required>
                <option value="Tenant" <?= $user['user_type'] == 'Tenant' ? 'selected' : '' ?>>Tenant</option>
                <option value="Landlord" <?= $user['user_type'] == 'Landlord' ? 'selected' : '' ?>>Landlord</option>
            </select>
            <input type="password" name="password" placeholder="New Password (Optional)">
            <button type="submit" name="update_user" class="update-btn">Update User</button>
        </form>
        <a href="admin_manage_users.php" class="back-btn">Back to Manage Users</a>
    </div>

</body>
</html>
