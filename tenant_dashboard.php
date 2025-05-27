<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); 
    exit();
}

include 'db_connect.php';
include 'sidebar.php';

$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = '$user_id'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

if ($user['user_type'] !== 'Tenant') {
    echo "<script>alert('Access Denied! Only tenants can access this page.'); window.location.href='login.php';</script>";
    exit();
}

// SELECTED APARTMENT & UNIT
$selected = mysqli_query($conn, "
    SELECT a.name AS apartment_name, a.location, a.price, a.apartment_type, 
           u.name AS landlord_name, u.email AS landlord_email, u.mobile AS landlord_mobile, 
           au.unit_number, au.unit_price, au.unit_features, au.unit_status
    FROM tenant_rentals tr
    JOIN apartments a ON tr.apartment_id = a.id
    JOIN apartment_units au ON tr.unit_id = au.id
    JOIN users u ON a.landlord_id = u.id
    WHERE tr.tenant_id = $user_id AND tr.status = 'Active'
");
$selected_apartment = mysqli_fetch_assoc($selected);

$notifications_query = "SELECT id, message, created_at FROM notifications WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 5";
$notifications = mysqli_query($conn, $notifications_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>GeoApart - Tenant Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
    body { background: #f4f4f4; color: #333; padding: 20px; }

    h2, h3 { color: #007bff; }

    .notif-icon {
        position: fixed; top: 20px; right: 30px; font-size: 24px;
        color: #007bff; cursor: pointer; z-index: 999;
    }

    .notif-dropdown {
        display: none;
        position: absolute;
        top: 50px;
        left: 80px;
        width: 250px;
        background: #fff;
        border: 1px solid #ccc;
        border-radius: 10px;
        box-shadow: 0px 2px 6px rgba(0, 0, 0, 0.2);
        z-index: 1000;
    }
    .notif-dropdown h4 {
        background: #007bff; color: white; padding: 10px; margin: 0;
        border-top-left-radius: 10px; border-top-right-radius: 10px;
    }
    .notif-item {
        padding: 10px; border-bottom: 1px solid #eee; font-size: 14px; position: relative;
    }
    .notif-item time {
        display: block; font-size: 12px; color: #666;
    }

    .section {
        max-width: 1100px;
        margin: 0 auto 40px;
    }

    .selected-box {
        background: #ffffff;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        margin-bottom: 30px;
    }

    .selected-box h2 {
        font-size: 22px;
        font-weight: 600;
        color: #333;
        margin-bottom: 12px;
    }

    .selected-box p {
        font-size: 13px;
        margin-bottom: 6px;
    }

    .selected-box hr {
        border: none;
        border-top: 1px solid #ddd;
        margin: 15px 0;
    }

    .check-btn {
        display: inline-block;
        background: #007bff;
        color: white;
        padding: 12px 20px;
        font-size: 16px;
        font-weight: 600;
        text-decoration: none;
        border-radius: 8px;
        transition: background 0.3s;
    }

    .check-btn:hover {
        background: #0056b3;
    }
    .check-btn.contract {
  background: #28a745;
}

  </style>
</head>
<body>

<div class="main-content" id="mainContent">
 <!-- NOTIFICATION ICON -->
<div class="notif-icon" onclick="toggleDropdown()"><i class="fas fa-bell"></i></div>
<br>

<!-- NOTIFICATION DROPDOWN -->
<?php include 'notification.php'; ?>


  <!-- CURRENT RENTED UNIT -->
  <?php if ($selected_apartment && $selected_apartment['unit_status'] === 'Occupied'): ?>
<div class="section selected-box">
      <h2>🏷️ Unit <?= htmlspecialchars($selected_apartment['unit_number']) ?></h2>

      <p><strong>Apartment:</strong> <?= htmlspecialchars($selected_apartment['apartment_name']) ?></p>
      <p><strong>Location:</strong> <?= htmlspecialchars($selected_apartment['location']) ?></p>
      <p><strong>Type:</strong> <?= htmlspecialchars($selected_apartment['apartment_type']) ?></p>
      <p><strong>Unit Price:</strong> ₱ <?= number_format($selected_apartment['unit_price'], 2) ?></p>
    
      <hr>
      <p><strong>Landlord:</strong> <?= htmlspecialchars($selected_apartment['landlord_name']) ?></p>
      <p><strong>Contact:</strong> <?= htmlspecialchars($selected_apartment['landlord_mobile']) ?></p>
      <p><strong>Email:</strong> <?= htmlspecialchars($selected_apartment['landlord_email']) ?></p>
      <p><strong>Status:</strong> <?= htmlspecialchars($selected_apartment['unit_status']) ?></p>
      
  </div>
  <?php endif; ?>

  <!-- BUTTON TO CHECK AVAILABLE APARTMENTS -->
  <div class="section" style="text-align: center;">
      <a href="apartment_list.php" class="check-btn">
          <i class="fas fa-building"></i> Check Available Apartments
      </a>
  </div>
</div>

<script>
function toggleDropdown() {
    const dropdown = document.getElementById('notifDropdown');
    dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
}

// Sidebar margin observer
const sidebar = document.getElementById('sidebar');
const mainContent = document.getElementById('mainContent');

const observer = new MutationObserver(() => {
  if (sidebar.classList.contains('collapsed')) {
    mainContent.style.marginLeft = '80px';
  } else {
    mainContent.style.marginLeft = '260px';
  }
});

observer.observe(sidebar, { attributes: true, attributeFilter: ['class'] });
</script>

</body>
</html>
