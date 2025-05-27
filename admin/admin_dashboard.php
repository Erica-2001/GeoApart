<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

include 'db_connect.php';
include 'sidebar.php';

function getTotalCount($conn, $table) {
    $query = "SELECT COUNT(*) AS count FROM $table";
    $result = mysqli_query($conn, $query);
    if (!$result) return 0;
    $data = mysqli_fetch_assoc($result);
    return $data['count'] ?? 0;
}

$totalUsers = getTotalCount($conn, "users");
$totalApartments = getTotalCount($conn, "apartments");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }

    body {
      display: flex;
      height: 100vh;
      background-color: #fff;
    }

    .main-content {
      flex: 1;
      padding: 40px 20px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
    }

    .card {
      width: 180px;
      height: 100px;
      border-radius: 20px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      font-size: 18px;
      font-weight: bold;
      color: white;
      margin: 15px 0;
    }

    .card.apartments {
      background-color: #1b254e;
    }

    .card.users {
      background-color: #71c6ec;
    }
  </style>
</head>
<body>

  <div class="main-content">
    <div class="card apartments">
      APARTMENTS: 
      <?= $totalApartments ?>
    </div>
    <div class="card users">
      USERS: 
      <?= $totalUsers ?>
    </div>
  </div>

</body>
</html>
