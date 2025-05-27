<?php include 'db_connect.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>GeoApart Demo</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  
  <!-- Link to your custom CSS -->
  <link rel="stylesheet" href="styles.css" />

  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />

  <style>
    /* Basic reset */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: Arial, sans-serif;
      background-color: #fff;
    }

    /* HEADER */
    .header {
      display: flex;
      align-items: center;
      background: #F1F1F1;
      padding: 10px 20px;
      backdrop-filter: blur(10px);
      position: fixed;
      width: 100%;
      top: 0;
      left: 0;
      z-index: 1000;
    }

    /* MENU BUTTON */
    .menu-btn {
      background: none;
      border: none;
      cursor: pointer;
      font-size: 1.4rem;
      color: #0B81FE;
      margin-right: 10px;
    }

    /* LOGO CONTAINER */
    .logo-container {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .logo {
      width: 40px;
      height: 40px;
    }

    .app-title {
      font-size: 1.5rem;
      font-weight: bold;
      color: #0B81FE;
    }

    /* TRANSLUCENT MENU */
    .menu {
      display: none;
      position: fixed;
      top: 60px;
      left: 0;
      background: rgba(0, 0, 0, 0.7);
      width: 220px;
      padding: 10px;
      z-index: 999;
    }

    .menu a {
      display: block;
      padding: 12px 15px;
      text-decoration: none;
      color: white;
      font-weight: bold;
      border-bottom: 1px solid rgba(255, 255, 255, 0.2);
      transition: all 0.3s ease-in-out;
    }

    .menu a:hover {
      background: rgba(255, 255, 255, 0.2);
      border-radius: 5px;
    }

    .show-menu {
      display: block;
    }
  </style>
</head>
<body>

<!-- HEADER -->
<header class="header">
  <button class="menu-btn" aria-label="Menu">
    <i class="fas fa-bars"></i>
  </button>

  <div class="logo-container">
    <img src="img/logo.png" alt="GeoApart Logo" class="logo">
    <h1 class="app-title">GeoApart</h1>
  </div>
</header>

<!-- TRANSLUCENT MENU -->
<nav class="menu">
  <a href="admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
  <a href="admin_account.php"><i class="fas fa-user"></i> Admin Account</a> 
  <a href="admin_manage_users.php"><i class="fas fa-users"></i> Manage Users</a>
   <a href="admin_manage_tenants.php"><i class="fas fa-users"></i> Manage Tenants</a>
  <a href="admin_manage_apartments.php"><i class="fas fa-building"></i> Manage Apartments</a>
  <a href="admin_unit_approvals.php"><i class="fas fa-building"></i> Approved Unit Apartments</a>
  <a href="admin_manage_payments.php"><i class="fas fa-money-bill-wave"></i> Manage Payments</a>
  <a href="admin_messages.php"><i class="fas fa-comments"></i> Messages</a>
  <a href="admin_logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
</nav>

<script>
  document.addEventListener("DOMContentLoaded", function () {
    const menuBtn = document.querySelector(".menu-btn");
    const menu = document.querySelector(".menu");

    menuBtn.addEventListener("click", function () {
      menu.classList.toggle("show-menu");
    });
  });
</script>

</body>
</html>
<br><br><br><br>