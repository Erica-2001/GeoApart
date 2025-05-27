<?php include 'db_connect.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>GeoApart Demo</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <!-- Link to your custom CSS -->
  <link rel="stylesheet" href="styles.css" />
  <!-- (Optional) Font Awesome for icons -->
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
  />

  <style>
        /* Basic reset */
        * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: Arial, sans-serif;
      background-color: rgb(255, 255, 255);
    }

    /* HEADER */
    .header {
      display: flex;
      align-items: center; /* Proper vertical alignment */
      background: #F1F1F1;
      padding: 15px 20px;
      backdrop-filter: blur(10px);
      position: fixed;
      width: 100%;
      top: 0;
      left: 0;
      z-index: 1000;
      gap: 0px; /* Creates space between the menu button and logo */
    }

    /* MENU BUTTON */
    .menu-btn {
      background: none;
      border: none;
      cursor: pointer;
      font-size: 1.4rem;
      color: #0B81FE;
    }

    .menu-btn:hover {
      color:rgb(10, 10, 10);
    }

    /* LOGO CONTAINER */
    .logo-container {
      display: flex;
      align-items: center;
      justify-content: flex-start;
      gap: 10px;
      padding-left: 10px; /* Moves the logo slightly right */
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

       /* DARK TRANSLUCENT MENU */
       .menu {
      display: none;
      position: fixed; /* Keeps the menu visible on scroll */
      top: 60px;
      left: 0px;
      background: rgba(0, 0, 0, 0.7); /* Dark translucent background */
      box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.2);
      border-radius: 5px;
      width: 150px;
      padding: 10px;
      backdrop-filter: blur(15px); /* Glass effect */
      z-index: 999; /* Ensures it stays above other content */
    }


    .menu a {
      display: block;
      padding: 12px 15px;
      text-decoration: none;
      color: white; /* White text */
      font-weight: bold;
      border-bottom: 1px solid rgba(255, 255, 255, 0.2);
      transition: all 0.3s ease-in-out;
    }

    .menu a:last-child {
      border-bottom: none;
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

<!-- Dark Translucent Menu -->
<nav class="menu">
  <a href="index.php">Home</a>
   <a href="login.php">Login</a>
 

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
