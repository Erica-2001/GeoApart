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

$apartment_query = "SELECT * FROM apartments WHERE landlord_id = $user_id";
$apartments = mysqli_query($conn, $apartment_query);

// Notifications
$notifications_query = "SELECT id, message, created_at FROM notifications WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 5";
$notifications = mysqli_query($conn, $notifications_query);

// Get apartments with at least one AVAILABLE unit
$apartment_query = "
  SELECT DISTINCT a.* FROM apartments a
  JOIN apartment_units au ON a.id = au.apartment_id
  WHERE a.landlord_id = $user_id
";
$apartments = mysqli_query($conn, $apartment_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>GeoApart - My Properties</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }

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

    .button-container {
      display: flex;
      justify-content: center;
      gap: 16px;
      margin-bottom: 30px;
      flex-wrap: wrap;
    }

    .top-btn {
      padding: 12px 24px;
      border-radius: 30px;
      font-size: 15px;
      font-weight: 600;
      color: white;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      transition: 0.3s ease;
    }

    .top-btn:hover {
      transform: translateY(-2px);
      opacity: 0.95;
    }

    .btn-blue { background-color: #007bff; }
    .btn-green { background-color: #28a745; }

    h2 {
      font-size: 22px;
      color: #28a745;
      text-align: center;
      margin-bottom: 30px;
    }

    .property {
      background: white;
      padding: 20px;
      border-radius: 12px;
      margin-bottom: 30px;
      max-width: 1000px;
      margin-left: auto;
      margin-right: auto;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .property h3 {
      font-size: 18px;
      color: #333;
      text-align: center;
      margin-bottom: 15px;
    }

    .property small {
      display: block;
      font-size: 13px;
      margin-top: 5px;
      color: #666;
    }

    .legend {
      display: flex;
      justify-content: center;
      flex-wrap: wrap;
      gap: 10px;
      margin-bottom: 15px;
    }

    .legend span {
      display: flex;
      align-items: center;
      font-size: 12px;
      gap: 5px;
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
      grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
      gap: 15px;
    }

    .unit {
      background: #fff;
      border: 1px solid #ccc;
      padding: 12px;
      border-radius: 10px;
      font-weight: 500;
      font-size: 13px;
      text-align: left;
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
      font-size: 14px;
      margin-bottom: 5px;
      font-weight: bold;
    }

    .unit span {
      font-size: 12px;
      display: block;
    }

    @media (max-width: 768px) {
      .main-content {
        margin-left: 80px;
        padding: 15px;
      }

      .top-btn {
        font-size: 14px;
        padding: 10px 20px;
      }

    }

    .notif-icon {
      position: fixed;
      top: 20px;
      right: 30px;
      font-size: 24px;
      color: #007bff;
      cursor: pointer;
      z-index: 999;
    }
    .notif-dropdown {
      display: none;
      position: absolute;
      top: 60px;
      right: 30px;
      width: 260px;
      background: #fff;
      border: 1px solid #ccc;
      border-radius: 10px;
      box-shadow: 0px 2px 6px rgba(0, 0, 0, 0.2);
      z-index: 1000;
    }
    .notif-dropdown h4 {
      background: #007bff;
      color: white;
      padding: 10px;
      margin: 0;
      border-top-left-radius: 10px;
      border-top-right-radius: 10px;
    }
    .notif-item {
      padding: 10px;
      border-bottom: 1px solid #eee;
      font-size: 14px;
    }
    .notif-item time {
      display: block;
      font-size: 12px;
      color: #666;
    }
  </style>
</head>
<body>
<br>
<div class="main-content">
    <!-- NOTIFICATION ICON -->
<div class="notif-icon" onclick="toggleDropdown()"><i class="fas fa-bell"></i></div>
<br>

<!-- NOTIFICATION DROPDOWN -->
<?php include 'notification.php'; ?>

<script>
function toggleDropdown() {
  const dropdown = document.getElementById("notifDropdown");
  dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
}
</script>
<br>

  <!-- BUTTONS -->
  <div class="button-container">
    <a href="my_properties.php" class="top-btn btn-blue"><i class="fas fa-folder-open"></i> My Properties</a>

  </div>

  <h2>My Active Listings</h2>

  <?php if (mysqli_num_rows($apartments) > 0): ?>
    <?php while ($apartment = mysqli_fetch_assoc($apartments)): ?>
      <div class="property">
        <h3><?= htmlspecialchars($apartment['name']) ?><small>(<?= htmlspecialchars($apartment['location']) ?>)</small></h3>

        <div class="legend">
          <span><div class="legend-box vacant"></div> Available</span>
          <span><div class="legend-box pending"></div> Pending</span>
          <span><div class="legend-box occupied"></div> Occupied</span>
        </div>

        <div class="units">
          <?php
          $apt_id = $apartment['id'];
          $units = mysqli_query($conn, "SELECT * FROM apartment_units WHERE apartment_id = $apt_id");

          while ($unit = mysqli_fetch_assoc($units)):
            $unit_id = $unit['id'];
            $status_class = strtolower($unit['unit_status']);

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
          ?>
          <div class="unit <?= $status_class ?>">
            <div class="unit-title">Unit <?= htmlspecialchars($unit['unit_number']) ?></div>
            <span>Status: <?= htmlspecialchars($unit['unit_status']) ?></span>
            <?php if ($tenant): ?>
              <span>Tenant: <?= htmlspecialchars($tenant['name']) ?></span>
            <?php endif; ?>
            <span>Pending Payments: <?= $pending['pending_count'] ?></span>
          </div>
          <?php endwhile; ?>
        </div>

      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <p style="text-align:center; color:#888; font-style:italic;">No active listings found.</p>
  <?php endif; ?>

</div>

</body>
</html>
