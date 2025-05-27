<?php
include 'db_connect.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $query = "SELECT * FROM admin_users WHERE email='$email'";
    $result = mysqli_query($conn, $query);
    $admin = mysqli_fetch_assoc($result);

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        echo "<script>alert('Login Successful!'); window.location.href='admin_dashboard.php';</script>";
    } else {
        echo "<script>alert('Invalid email or password!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>GeoApart - Admin Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: #fff;
            flex-direction: column;
        }
        .admin-login-container {
            width: 90%;
            max-width: 400px;
            padding: 25px;
            background: white;
            border-radius: 8px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .admin-login-container h2 {
            font-size: 24px;
            color: #007bff;
            margin-bottom: 20px;
        }
        .input-group {
            display: flex;
            align-items: center;
            border-bottom: 2px solid #ccc;
            margin-bottom: 20px;
            padding: 10px;
            background: #f7f7f7;
            border-radius: 5px;
        }
        .input-group input {
            border: none;
            outline: none;
            width: 100%;
            font-size: 16px;
            padding: 10px;
            background: transparent;
        }
        .input-group i {
            margin-right: 10px;
            color: #555;
        }
        .login-btn {
            width: 100%;
            background: #007bff;
            border: none;
            padding: 14px;
            font-size: 16px;
            font-weight: bold;
            color: white;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s;
        }
        .login-btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>

    <div class="admin-login-container">
        <h2>Admin Login</h2>
        <form action="admin_login.php" method="POST">
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="Email" required>
            </div>
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit" class="login-btn">LOGIN</button>
        </form>
    </div>

</body>
</html>
