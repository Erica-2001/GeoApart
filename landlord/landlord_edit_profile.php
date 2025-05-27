<?php
session_start();
include '../db_connect.php';

// Ensure only landlords can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'Landlord') {
    header("Location: ../login.php");
    exit();
}

$landlord_id = $_SESSION['user_id'];

// Fetch landlord details
$query = "SELECT name, email, mobile FROM users WHERE id = '$landlord_id'";
$result = mysqli_query($conn, $query);
$landlord = mysqli_fetch_assoc($result);

// Update Profile
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $mobile = mysqli_real_escape_string($conn, $_POST['mobile']);

    // Check if password change is requested
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $update_query = "UPDATE users SET name='$name', email='$email', mobile='$mobile', password='$password' WHERE id='$landlord_id'";
    } else {
        $update_query = "UPDATE users SET name='$name', email='$email', mobile='$mobile' WHERE id='$landlord_id'";
    }

    if (mysqli_query($conn, $update_query)) {
        $_SESSION['user_name'] = $name; // Update session with new name
        echo "<script>alert('Profile updated successfully!'); window.location.href='landlord_dashboard.php';</script>";
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
        input {
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

 

<div class="back" style="text-align: left; margin-bottom: 10px;">
    <a href="landlord_dashboard.php" style="text-decoration: none; font-size: 18px; color: #007bff;">
        <i class="fa-solid fa-arrow-left"></i> Back
    </a>
</div>

	
	
        <h2>Edit Profile</h2>
        <form action="landlord_edit_profile.php" method="POST">
            <input type="text" name="name" value="<?= htmlspecialchars($landlord['name']); ?>" required placeholder="Full Name">
            <input type="email" name="email" value="<?= htmlspecialchars($landlord['email']); ?>" required placeholder="Email">
            <input type="text" name="mobile" value="<?= htmlspecialchars($landlord['mobile']); ?>" required placeholder="Mobile Number">
            <input type="password" name="password" placeholder="New Password (Optional)">
            <button type="submit" name="update_profile" class="update-btn">Update Profile</button>
        </form>
       
    </div>

</body>
</html>
