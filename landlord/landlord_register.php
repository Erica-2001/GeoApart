<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $check_landlord = "SELECT * FROM landlords WHERE email='$email'";
    $result = mysqli_query($conn, $check_landlord);

    if (mysqli_num_rows($result) > 0) {
        $message = "Landlord already exists!";
    } else {
        $sql = "INSERT INTO landlords (username, email, password) VALUES ('$username', '$email', '$password')";
        if (mysqli_query($conn, $sql)) {
            echo "<script>alert('Landlord created successfully! Redirecting to login...'); window.location.href='landlord_login.php';</script>";
            exit();
        } else {
            $message = "Error: Unable to create landlord.";
        }
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GeoApart - Admin Registration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: #fff;
            flex-direction: column;
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
            font-size: 22px;
            font-weight: bold;
            margin-top: 5px;
        }
        .signup-container {
            width: 100%;
            max-width: 350px;
            padding: 20px;
            text-align: center;
        }
        .signup-container h2 {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 15px;
            text-align: left;
        }
        .input-group {
            display: flex;
            align-items: center;
            border-bottom: 2px solid #ccc;
            margin-bottom: 15px;
            padding: 8px 5px;
        }
        .input-group input,
        .input-group select {
            border: none;
            outline: none;
            width: 100%;
            font-size: 16px;
            padding: 8px;
        }
        .input-group i {
            margin-right: 10px;
            color: #555;
        }
        .remember-me {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            margin-bottom: 20px;
        }
        .remember-me input {
            margin-right: 5px;
        }
        .signup-btn {
            width: 70%;
    background: #80c5ea;
    border: none;
    padding: 12px;
    font-size: 16px;
    font-weight: bold;
    color: black;
    border-radius: 5px;
    cursor: pointer;
    display: block; /* Ensures it behaves as a block element */
    margin: 0 auto; /* Centers horizontally */
    text-align: center; /* Aligns text inside the button */
        }
        .login-link {
            margin-top: 15px;
            font-size: 14px;
			display: block; /* Ensures it behaves as a block element */
    margin: 0 auto; /* Centers horizontally */
    text-align: center; /* Aligns text inside the button */
        }
        .login-link a {
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

    <form action="landlord_register.php" method="POST" class="login-container">
        <h2>Create Landlord Account</h2>
        <?php if (isset($message)): ?>
            <p style="color: red;"><?php echo $message; ?></p>
        <?php endif; ?>
        <div class="input-group">
            <i class="fas fa-user"></i>
            <input type="text" name="username" placeholder="Username" required>
        </div>
        <div class="input-group">
            <i class="fas fa-envelope"></i>
            <input type="email" name="email" placeholder="Email" required>
        </div>
        <div class="input-group">
            <i class="fas fa-lock"></i>
            <input type="password" name="password" placeholder="Password" required>
        </div>
        <button type="submit" class="signup-btn">SIGN UP</button>
    <div class="login-link"><br>
        Already have an account? <a href="login.php">Login</a>
    </div>
</form>

</body>
</html>
