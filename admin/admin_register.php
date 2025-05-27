<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Encrypt password

    // Check if admin already exists
    $check_admin = "SELECT * FROM admin_users WHERE email='$email'";
    $result = mysqli_query($conn, $check_admin);

    if (mysqli_num_rows($result) > 0) {
        $message = "Admin already exists!";
    } else {
        $sql = "INSERT INTO admin_users (username, email, password) VALUES ('$username', '$email', '$password')";
        if (mysqli_query($conn, $sql)) {
            $message = "Admin created successfully! Redirecting...";
            echo "<script>
                    alert('$message'); 
                    window.location.href='admin_login.php';
                  </script>";
            exit();
        } else {
            $message = "Error: Unable to create admin.";
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
        .register-container {
            width: 100%;
            max-width: 360px;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .register-container h2 {
            font-size: 26px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #111;
        }
        .input-group {
            display: flex;
            align-items: center;
            border-bottom: 2px solid #ccc;
            margin-bottom: 15px;
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
        .message {
            font-size: 14px;
            color: red;
            margin-bottom: 10px;
        }
        .register-btn {
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
        .register-btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>

    <div class="back">
        <a href="admin_login.php"><i class="fa-solid fa-arrow-left"></i></a>
    </div>

    <div class="logo">
        <img src="img/logo1.png" alt="GeoApart Logo">
    </div>

    <form action="admin_register.php" method="POST" class="register-container">
        <h2>Create Admin Account</h2>

        <?php if (isset($message)): ?>
            <p class="message"><?php echo $message; ?></p>
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

        <button type="submit" class="register-btn">Create Admin</button>
    </form>

</body>
</html>
