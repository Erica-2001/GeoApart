<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

include '../db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_admin'])) {
    $id = intval($_POST['id']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    $query = "UPDATE admin_users SET username=?, email=? WHERE id=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssi", $username, $email, $id);

    if ($stmt->execute()) {
        echo "<script>alert('Admin updated successfully!'); window.location.href='admin_manage.php';</script>";
    } else {
        echo "<script>alert('Error: Unable to update admin.');</script>";
    }
}
?>
