<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include 'sidebar.php';
include 'db_connect.php';

if (!isset($_GET['unit_id'])) {
    echo "<script>alert('No unit selected.'); window.location.href='my_properties.php';</script>";
    exit();
}

$unit_id = intval($_GET['unit_id']);

// Fetch tenant info
$query = "
  SELECT u.name, au.unit_number 
  FROM tenant_rentals tr
  JOIN users u ON tr.tenant_id = u.id
  JOIN apartment_units au ON tr.unit_id = au.id
  WHERE tr.unit_id = $unit_id AND tr.status = 'Active'
";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) === 0) {
    echo "<script>alert('No active tenant found for this unit.'); window.location.href='my_properties.php';</script>";
    exit();
}

$tenant = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Tenant Info - Unit <?= htmlspecialchars($tenant['unit_number']) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <style>
    body {
      margin: 0;
      font-family: 'Poppins', sans-serif;
      background-color: #f9f9f9;
    }

    .main-content {
      margin-left: 260px;
      padding: 40px 20px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
    }

    .wrapper {
      background: white;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.08);
      width: 100%;
      max-width: 400px;
      text-align: center;
    }

    h2 {
      font-size: 20px;
      color: #333;
      margin-bottom: 10px;
    }

    .tenant-name {
      font-size: 24px;
      font-weight: bold;
      color: #007bff;
      margin-bottom: 20px;
    }

    .btn {
      display: block;
      width: 100%;
      padding: 12px;
      margin: 10px 0;
      font-size: 15px;
      font-weight: 600;
      text-align: center;
      text-decoration: none;
      border: none;
      border-radius: 8px;
      background-color: #f5f5f5;
      color: #333;
      box-shadow: 1px 2px 4px rgba(0,0,0,0.1);
      transition: background-color 0.3s;
    }

    .btn:hover {
      background-color: #e9e9e9;
    }

    @media (max-width: 768px) {
      .main-content {
        margin-left: 80px;
        padding: 20px;
      }
    }
  </style>
</head>
<body>
  <div class="main-content">
    <div class="wrapper">
      <h2>UNIT <?= htmlspecialchars($tenant['unit_number']) ?></h2>
      <div class="tenant-name"><?= htmlspecialchars($tenant['name']) ?></div>

      <a href="tenant_personal_info.php?unit_id=<?= $unit_id ?>" class="btn">
        Personal Information
      </a>
      <a href="tenant_payment_records.php?unit_id=<?= $unit_id ?>" class="btn">
        Payment Records
      </a>
    </div>
  </div>
</body>
</html>
