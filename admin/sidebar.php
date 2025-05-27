<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

include 'db_connect.php';

$admin_id = $_SESSION['admin_id'];
$admin_query = mysqli_query($conn, "SELECT * FROM admin_users WHERE id = $admin_id");
$admin = mysqli_fetch_assoc($admin_query);
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>GeoApart Sidebar</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />

  <style>
    * {
      margin: 0; padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }

    body {
      display: flex;
      background-color: #fff;
    }

    .sidebar {
      width: 260px;
      min-height: 100vh;
      background-color: white;
      border-right: 1px solid #e0e0e0;
      padding: 20px 10px;
      transition: width 0.3s ease;
      position: fixed;
      top: 0;
      left: 0;
      z-index: 999;
    }

    .sidebar.collapsed {
      width: 80px;
    }

    .profile {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 25px;
      padding: 0 5px;
    }

    .profile img {
      width: 48px;
      height: 48px;
      border-radius: 12px;
      background: #fff;
      object-fit: cover;
    }

    .profile span {
      font-size: 14px;
    }

    .sidebar.collapsed .profile span {
      display: none;
    }

    .search-box {
      display: flex;
      align-items: center;
      background: #f1f1f1;
      border-radius: 12px;
      padding: 10px;
      margin-bottom: 20px;
    }

    .search-box input {
      border: none;
      background: transparent;
      outline: none;
      width: 100%;
    }

    .sidebar.collapsed .search-box {
      justify-content: center;
    }

    .sidebar.collapsed .search-box input {
      display: none;
    }

    .nav-links a {
      display: flex;
      align-items: center;
      gap: 14px;
      padding: 12px 16px;
      border-radius: 10px;
      text-decoration: none;
      color: #333;
      margin-bottom: 10px;
      transition: 0.2s;
    }

    .nav-links a:hover {
      background-color: #f5f5f5;
    }

    .nav-links i {
      font-size: 18px;
      min-width: 24px;
      text-align: center;
    }

    .sidebar.collapsed .nav-links span {
      display: none;
    }

    .toggle-btn {
      position: absolute;
      top: 15px;
      right: -15px;
      background: #0B81FE;
      color: white;
      border: none;
      border-radius: 50%;
      width: 30px;
      height: 30px;
      cursor: pointer;
      font-size: 16px;
    }

    .main-content {
      margin-left: 260px;
      padding: 30px;
      flex: 1;
      transition: margin-left 0.3s ease;
    }

    .sidebar.collapsed ~ .main-content {
      margin-left: 80px;
    }

    @media (max-width: 768px) {
      .sidebar {
        position: fixed;
        height: 100vh;
        z-index: 1000;
      }

      .main-content {
        margin-left: 80px;
        padding: 20px;
      }
    }
  </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar collapsed" id="sidebar">
  <div class="profile">
    <img src="img/logo1.png" alt="GeoApart Logo">
    <span>
      <strong><?= htmlspecialchars($admin['username']) ?></strong><br>
<small><?= htmlspecialchars($admin['email']) ?></small>
    </span>
    <button class="toggle-btn" onclick="toggleSidebar()">☰</button>
  </div>

 

<div class="nav-links">
    <a href="admin_dashboard.php"><i class="fas fa-home"></i><span>Dashboard</span></a>
    <a href="admin_account.php"><i class="fas fa-user"></i><span>Admin Account</span></a>
    <a href="admin_manage_users.php"><i class="fas fa-users"></i><span>Manage Users</span></a>
    <a href="admin_user_approvals.php"><i class="fas fa-user-check"></i><span>User Registration</span></a> <!-- ✅ NEW LINK -->
    <!--<a href="admin_manage_tenants.php"><i class="fas fa-users"></i><span>Manage Tenants</span></a>  -->
    <a href="admin_manage_apartments.php"><i class="fas fa-building"></i><span>Manage Apartments</span></a>
    <!-- <a href="admin_unit_approvals.php"><i class="fas fa-check-circle"></i><span>Approved Units</span></a> -->
    <!--<a href="admin_manage_payments.php"><i class="fas fa-money-bill-wave"></i><span>Manage Payments</span></a>
    <!-- <a href="admin_messages.php"><i class="fas fa-comments"></i><span>Messages</span></a> -->
    <a href="admin_logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
</div>
</div>


<!-- Sidebar Toggle Script -->
<script>
  function toggleSidebar() {
    document.getElementById("sidebar").classList.toggle("collapsed");
  }
</script>
