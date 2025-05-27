<?php
include 'db_connect.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $query = "SELECT * FROM landlords WHERE email='$email'";
    $result = mysqli_query($conn, $query);
    $landlord = mysqli_fetch_assoc($result);

    if ($landlord && password_verify($password, $landlord['password'])) {
        $_SESSION['landlord_id'] = $landlord['id'];
        $_SESSION['landlord_username'] = $landlord['username'];
        echo "<script>alert('Login Successful!'); window.location.href='landlord_dashboard.php';</script>";
    } else {
        echo "<script>alert('Invalid email or password!');</script>";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GeoApart - Login</title>
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
            padding: 20px;
        }
        .back {
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 24px;
            cursor: pointer;
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo img {
            width: 110px;
        }
        .logo h1 {
            font-size: 24px;
            font-weight: bold;
            margin-top: 5px;
            color: #333;
        }
        .login-container {
            width: 100%;
            max-width: 360px;
            padding: 20px;
            text-align: center;
        }
        .login-container h2 {
            font-size: 26px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #111;
        }
        .input-group {
            display: flex;
            align-items: center;
            border-bottom: 2px solid #ccc;
            margin-bottom: 20px;
            padding: 10px 5px;
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
        .remember-me {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            margin-bottom: 25px;
        }
        .remember-me label {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .login-btn {
            width: 70%;
            background: #80c5ea;
            border: none;
            padding: 14px;
            font-size: 16px;
            font-weight: bold;
            color: black;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s;
        }
        .login-btn:hover {
            background: #5eb1d7;
        }
        .signup {
            margin-top: 15px;
            font-size: 14px;
        }
        .signup a {
            color: #007bff;
            font-weight: bold;
            text-decoration: none;
        }
    </style>
</head>
<body>

    <div class="back">
        <a href="index.php"><i class="fa-solid fa-arrow-left"></i></a>
    </div>

    <div class="logo">
        <img src="img/logo1.png" alt="GeoApart Logo">
    </div>

    <form action="landlord_login.php" method="POST" class="login-container">
        <h2>Landlord Login</h2>
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

</body>
</html>
