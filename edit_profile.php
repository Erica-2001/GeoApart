<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db_connect.php';

// Fetch current user details
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = '$user_id'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// Update Profile
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $mobile = mysqli_real_escape_string($conn, $_POST['mobile']);

    // Update password only if provided
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $update_query = "UPDATE users SET name='$name', email='$email', mobile='$mobile', password='$password' WHERE id='$user_id'";
    } else {
        $update_query = "UPDATE users SET name='$name', email='$email', mobile='$mobile' WHERE id='$user_id'";
    }

    if (mysqli_query($conn, $update_query)) {
        echo "<script>alert('Profile updated successfully!'); window.location.href='tenant_dashboard.php';</script>";
    } else {
        echo "<script>alert('Error updating profile.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
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
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .profile-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
            text-align: center;
        }
        h2 {
            color: #007bff;
            margin-bottom: 15px;
        }
        input {
            width: 90%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .save-btn {
            background: #007bff;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s;
            width: 100%;
            font-size: 16px;
        }
        .save-btn:hover {
            background: #0056b3;
        }
        .cancel-btn {
            display: block;
            margin-top: 10px;
            text-decoration: none;
            color: white;
            background: #dc3545;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
        }
        .cancel-btn:hover {
            background: #b02a37;
        }
    </style>
</head>
<body>

    <div class="profile-container">
        <h2>Edit Profile</h2>
        <form action="edit_profile.php" method="POST">
            <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            <input type="text" name="mobile" value="<?php echo htmlspecialchars($user['mobile']); ?>" required>
            <input type="password" name="password" placeholder="New Password (Optional)">
            <button type="submit" class="save-btn">Save Changes</button>
        </form>
        <a href="tenant_dashboard.php" class="cancel-btn">Cancel</a>
    </div>

</body>
</html>
