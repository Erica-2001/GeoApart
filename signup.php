<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include 'db_connect.php'; // Database connection

    $mobile = mysqli_real_escape_string($conn, $_POST['mobile']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $user_type = mysqli_real_escape_string($conn, $_POST['user_type']);

    // Handle file upload
    $proofImageName = '';
    if (isset($_FILES['proof_image']) && $_FILES['proof_image']['error'] === 0) {
        $targetDir = "uploads/proofs/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $fileName = time() . '_' . basename($_FILES['proof_image']['name']);
        $targetFile = $targetDir . $fileName;

        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
        $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        if (in_array($fileType, $allowedTypes)) {
            if (move_uploaded_file($_FILES['proof_image']['tmp_name'], $targetFile)) {
                $proofImageName = $targetFile;
            } else {
                echo "<script>alert('Error uploading file.');</script>";
                exit();
            }
        } else {
            echo "<script>alert('Invalid file type. Only JPG, PNG, GIF, or PDF allowed.');</script>";
            exit();
        }
    } else {
        echo "<script>alert('Proof image is required.');</script>";
        exit();
    }

    // Check if email already exists
    $check_email = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($conn, $check_email);

    if (mysqli_num_rows($result) > 0) {
        echo "<script>alert('Email already exists! Please use another.');</script>";
    } else {
        // Insert user
        $sql = "INSERT INTO users (mobile, name, email, password, user_type, proof_image, status)
                VALUES ('$mobile', '$name', '$email', '$password', '$user_type', '$proofImageName', 'Pending')";

        if (mysqli_query($conn, $sql)) {
            echo "<script>alert('Sign-up successful! Waiting for admin approval.'); window.location.href='login.php';</script>";
        } else {
            echo "<script>alert('Error: Unable to sign up. Try again later.');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GeoApart - Sign Up</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
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
        }
        .login-link {
            margin-top: 15px;
            font-size: 14px;
        }
        .login-link a {
            color: #007bff;
            font-weight: bold;
            text-decoration: none;
        }
        .note {
            font-size: 12px;
            text-align: left;
            margin-top: -10px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<div class="back">
    <a href="login.php"><i class="fa-solid fa-arrow-left"></i></a>
</div>
<br><br><br><br><br><br><br><br><br><br>
<div class="logo">
    <img src="img/logo1.png" alt="GeoApart Logo">
</div>

<!-- SIGN-UP FORM -->
<form action="signup.php" method="POST" enctype="multipart/form-data" class="signup-container" onsubmit="return validatePasswords()">
    <h2>Create an account</h2>
    
      <!-- User Type Select -->
    <div class="input-group">
        <select name="user_type" id="user_type" onchange="updateNote()" required>
            <option value="" disabled selected>Type of user</option>
            <option value="Tenant">Tenant</option>
            <option value="Landlord">Landlord</option>
        </select>
    </div>
    
    <div class="input-group">
        <i class="fas fa-user"></i>
        <input type="text" name="name" placeholder="Full Name" required>
    </div>

    <div class="input-group">
        <i class="fas fa-phone"></i>
        <input type="text" name="mobile" placeholder="Mobile Number" required>
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

    <div class="input-group">
        <i class="fas fa-lock"></i>
        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
        <i class="fas fa-eye password-toggle" onclick="togglePassword('confirm_password', this)"></i>
    </div>

  

    <!-- Upload Proof -->
    <div class="input-group">
        <i class="fas fa-file-upload"></i>
        <input type="file" name="proof_image" id="proof_image" required>
    </div>

    <!-- Dynamic Note -->
    <p id="noteText" class="note"><strong>Note:</strong> Upload Business Permit (Landlord) or Valid ID (Tenant).</p>

    <button type="submit" class="signup-btn">SIGN UP</button>
    <div class="login-link">
        Already have an account? <a href="login.php">Login</a>
    </div>
</form>


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

    function validatePasswords() {
        let password = document.getElementById("password").value;
        let confirmPassword = document.getElementById("confirm_password").value;
        if (password !== confirmPassword) {
            alert("Passwords do not match!");
            return false;
        }
        return true;
    }

    function updateNote() {
        let userType = document.getElementById('user_type').value;
        let note = document.getElementById('noteText');
        if (userType === 'Tenant') {
            note.innerHTML = "<strong>Note:</strong> Upload Valid ID (Tenant).";
        } else if (userType === 'Landlord') {
            note.innerHTML = "<strong>Note:</strong> Upload Business Permit (Landlord).";
        } else {
            note.innerHTML = "<strong>Note:</strong> Upload Business Permit (Landlord) or Valid ID (Tenant).";
        }
    }
</script>


</body>
</html>
