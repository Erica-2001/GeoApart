<?php
include 'db_connect.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $account_type = mysqli_real_escape_string($conn, $_POST['account_type']);

    if ($account_type === 'Admin') {
        // Admin login - no status checking needed
        $query = "SELECT * FROM admin_users WHERE email='$email'";
    } else {
        // Tenant or Landlord - need to check status too
        $query = "SELECT * FROM users WHERE email='$email' AND user_type='$account_type'";
    }

    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user['password'])) {
            if ($account_type !== 'Admin') {
                // Check if user is Approved
                if ($user['status'] !== 'Approved') {
                    echo "<script>alert('Your account is still pending approval by Admin. Please wait.'); window.location.href='login.php';</script>";
                    exit();
                }
            }

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_type'] = $account_type;

            // Redirect based on account type
            if ($account_type === 'Tenant') {
                echo "<script>alert('Login Successful! Redirecting to Tenant Dashboard...'); window.location.href='tenant_dashboard.php';</script>";
            } elseif ($account_type === 'Landlord') {
                echo "<script>alert('Login Successful! Redirecting to Landlord Dashboard...'); window.location.href='landlord/landlord_dashboard.php';</script>";
            } elseif ($account_type === 'Admin') {
                echo "<script>alert('Login Successful! Redirecting to Admin Dashboard...'); window.location.href='admin/admin_dashboard.php';</script>";
            } else {
                echo "<script>alert('Invalid user type.'); window.location.href='login.php';</script>";
            }
            exit();
        } else {
            echo "<script>alert('Incorrect password. Please try again.');</script>";
        }
    } else {
        echo "<script>alert('No account found. Please check your email or account type.');</script>";
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
        .input-group input, .input-group select {
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

   <form action="login.php" method="POST" class="login-container" id="loginForm">
    <h2>Login</h2>

    <div class="input-group">
        <i class="fas fa-user"></i>
        <select name="account_type" id="accountType" required>
            <option value="" disabled selected>Select Account Type</option>
            <option value="Tenant">Tenant</option>
            <option value="Landlord">Landlord</option>
            <option value="Admin">Admin</option>
        </select>
    </div>

        <div class="input-group">
            <i class="fas fa-envelope"></i>
            <input type="email" name="email" placeholder="Email" required>
        </div>

       <div class="input-group">
    <i class="fas fa-lock"></i>
    <input type="password" id="password" name="password" placeholder="Password" required>
    <i class="fas fa-eye password-toggle" onclick="togglePassword('password', this)"></i>
</div>

        <div class="remember-me">
            <label><input type="checkbox"> Remember me</label>
            <a href="#">Forgot password?</a>
        </div>

        <button type="submit" class="login-btn">LOGIN</button>

        <div class="signup">
            Don’t have an account? <a href="signup.php">Sign Up</a>
        </div>
    </form>

</body>
</html>

<script>
    document.getElementById("accountType").addEventListener("change", function() {
        if (this.value === "Admin") {
            window.location.href = "admin/admin_login.php";
        }
    });
</script>



<script>
    function togglePassword(fieldId, icon) {
        let passwordField = document.getElementById(fieldId);
        if (passwordField.type === "password") {
            passwordField.type = "text";
            icon.classList.remove("fa-eye");
            icon.classList.add("fa-eye-slash");
        } else {
            passwordField.type = "password";
            icon.classList.remove("fa-eye-slash");
            icon.classList.add("fa-eye");
        }
    }
</script>
