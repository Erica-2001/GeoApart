<?php
session_start();
include 'db_connect.php';

if (!isset($_GET['unit_id'])) {
    echo "<script>alert('No unit selected.'); window.location.href='my_properties.php';</script>";
    exit();
}

$unit_id = intval($_GET['unit_id']);

// Fetch tenant info for the given unit
$query = "
    SELECT u.name, u.mobile, tr.id as rental_id
    FROM tenant_rentals tr
    JOIN users u ON tr.tenant_id = u.id
    WHERE tr.unit_id = $unit_id AND tr.status = 'Active'
";
$result = mysqli_query($conn, $query);
$tenant = mysqli_fetch_assoc($result);

if (!$tenant) {
    echo "<script>alert('Tenant information not found.'); window.location.href='my_properties.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Tenant Personal Info</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      padding: 20px;
      background: #f8f9fa;
    }
    .container {
      max-width: 500px;
      margin: auto;
      background: #fff;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      text-align: center;
    }
    .photo {
      width: 90px;
      height: 90px;
      background: #0d1b4c;
      color: white;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: auto;
      font-weight: bold;
      font-size: 14px;
    }
    .info p {
      font-size: 14px;
      margin: 10px 0;
    }
    .btn {
      display: inline-block;
      margin-top: 15px;
      padding: 12px 20px;
      background-color: #007bff;
      color: white;
      text-decoration: none;
      border-radius: 8px;
      font-weight: bold;
      transition: 0.2s ease;
    }
    .btn:hover {
      background-color: #0056b3;
    }
  </style>
</head>
<body>
  <div class="container">
            	<div class="back" style="text-align: left; margin-bottom: 10px;">
   <a href="tenant_info.php?unit_id=<?= $unit_id ?>" style="text-decoration: none; font-size: 18px; color: #007bff;">
        <i class="fa-solid fa-arrow-left"></i> Back
    </a>
</div>
    <div class="photo">Photo</div>
    <h2><?= htmlspecialchars($tenant['name']) ?></h2>
    <div class="info">
      <p><strong>Contact Number:</strong> <?= htmlspecialchars($tenant['mobile']) ?></p>
    </div>
    <a href="contract_agreement.php?rental_id=<?= $tenant['rental_id'] ?>" class="btn">View Contract Agreement</a>
  </div>
</body>
</html>
