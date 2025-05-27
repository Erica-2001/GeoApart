<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include 'sidebar.php';
include 'db_connect.php';

$user_id = $_SESSION['user_id'];
$query = "SELECT name, email, user_type FROM users WHERE id = '$user_id'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

if ($user['user_type'] !== 'Landlord') {
    echo "<script>alert('Access Denied! Only landlords can access this page.'); window.location.href='login.php';</script>";
    exit();
}

if (!isset($_GET['id'])) {
    echo "<script>alert('No apartment selected.'); window.location.href='my_properties.php';</script>";
    exit();
}

$apartment_id = intval($_GET['id']);

// Fetch only the selected apartment for this landlord
$apartment_query = mysqli_query($conn, "SELECT * FROM apartments WHERE id = $apartment_id AND landlord_id = $user_id");
$apartment = mysqli_fetch_assoc($apartment_query);

if (!$apartment) {
    echo "<script>alert('Apartment not found or access denied.'); window.location.href='my_properties.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>GeoApart - <?= htmlspecialchars($apartment['name']) ?> Units</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <style>
   * {
  margin: 0; padding: 0;
  box-sizing: border-box;
  font-family: 'Poppins', sans-serif;
}

body {
  background-color: #f4f4f4;
  transition: margin-left 0.3s ease;
  font-size: 14px;
}

.main-content {
  margin-left: 260px;
  padding: 20px;
  transition: margin-left 0.3s ease;
}

.sidebar.collapsed ~ .main-content {
  margin-left: 80px;
}

h2 {
  font-size: 20px;
  margin-bottom: 15px;
  color: #007bff;
  text-align: center;
}

.property {
  background: white;
  padding: 15px;
  border-radius: 10px;
  margin-bottom: 20px;
  max-width: 1000px;
  margin-left: auto;
  margin-right: auto;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.property h3 {
  margin-bottom: 10px;
  color: #333;
  font-size: 16px;
}

.legend {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 10px;
  flex-wrap: wrap;
  justify-content: center;
}

.legend span {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 12px;
}

.legend-box {
  width: 14px;
  height: 14px;
  border-radius: 4px;
  border: 1px solid #ccc;
}

.vacant { background: white; border: 1px solid #000; }
.occupied { background: #007bff; }
.pending { background: #ffc107; }

.units {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
  gap: 8px;
}

.unit {
  border-radius: 8px;
  padding: 10px;
  font-weight: 500;
  text-align: left;
  font-size: 12px;
  border: 1px solid #ccc;
  background: #fff;
}

.unit.occupied {
  background: #007bff;
  color: white;
}

.unit.pending {
  background: #ffc107;
  color: #000;
}

.unit.available {
  background: #fff;
  color: #000;
}

.unit-title {
  font-size: 13px;
  margin-bottom: 4px;
  font-weight: 600;
}

.unit span {
  display: block;
  font-size: 11.5px;
  font-weight: normal;
}

@media (max-width: 768px) {
  .main-content {
    margin-left: 80px;
    padding: 15px;
  }

  .sidebar.collapsed ~ .main-content {
    margin-left: 80px;
  }

  h2 {
    font-size: 18px;
  }

  .property {
    padding: 10px;
  }

  .unit {
    padding: 8px;
  }
}
  </style>
</head>
<body>

<div class="main-content">
  <h2><?= htmlspecialchars($apartment['name']) ?> Units</h2>

  <div class="property">
    <div class="legend">
      <span><div class="legend-box vacant"></div> Available</span>
      <span><div class="legend-box pending"></div> Pending</span>
      <span><div class="legend-box occupied"></div> Occupied</span>
    </div>

   <div class="units">
  <?php
    $units = mysqli_query($conn, "SELECT * FROM apartment_units WHERE apartment_id = $apartment_id");
    while ($unit = mysqli_fetch_assoc($units)):
      $unit_id = $unit['id'];
      $status = strtolower($unit['unit_status']);
      $status_class = $status;

      $tenant_query = mysqli_query($conn, "
        SELECT u.name FROM tenant_rentals tr 
        JOIN users u ON tr.tenant_id = u.id 
        WHERE tr.unit_id = $unit_id AND tr.status = 'Active'
      ");
      $tenant = mysqli_fetch_assoc($tenant_query);

      $payment_query = mysqli_query($conn, "
        SELECT COUNT(*) as pending_count FROM payments 
        WHERE unit_id = $unit_id AND payment_status = 'Pending'
      ");
      $pending = mysqli_fetch_assoc($payment_query);

      // Determine link destination based on status
      $link = '';
      if ($status === 'occupied') {
        $link = "tenant_info.php?unit_id=$unit_id";
      } elseif ($status === 'pending') {
        $link = "landlord_unit_approvals.php?unit_id=$unit_id";
      }
  ?>

    <?php if ($link): ?>
      <a href="<?= $link ?>" style="text-decoration: none;">
        <div class="unit <?= $status_class ?>">
          <div class="unit-title">Unit <?= htmlspecialchars($unit['unit_number']) ?></div>
          <span>Status: <?= htmlspecialchars($unit['unit_status']) ?></span>
          <?php if ($tenant): ?>
            <span>Tenant: <?= htmlspecialchars($tenant['name']) ?></span>
          <?php endif; ?>
          <span>Pending Payments: <?= $pending['pending_count'] ?></span>
        </div>
      </a>
    <?php else: ?>
      <div class="unit <?= $status_class ?>">
        <div class="unit-title">Unit <?= htmlspecialchars($unit['unit_number']) ?></div>
        <span>Status: <?= htmlspecialchars($unit['unit_status']) ?></span>
        <?php if ($tenant): ?>
          <span>Tenant: <?= htmlspecialchars($tenant['name']) ?></span>
        <?php endif; ?>
        <span>Pending Payments: <?= $pending['pending_count'] ?></span>
      </div>
    <?php endif; ?>

  <?php endwhile; ?>
</div>

