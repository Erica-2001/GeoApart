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
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>GeoApart - My Properties</title>
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
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
      transition: 0.3s ease;
    }

    .top-btn:hover {
      transform: translateY(-2px);
      opacity: 0.95;
    }

    .btn-blue {
      background-color: #007bff;
    }

    .btn-green {
      background-color: #28a745;
    }

    h2 {
      font-size: 22px;
      color: #007bff;
      text-align: center;
      margin-bottom: 25px;
    }

    .property-list {
      max-width: 900px;
      margin: 0 auto;
      display: grid;
      gap: 20px;
    }

    .apartment-card {
      background: white;
      border-radius: 12px;
      padding: 20px;
      box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
      transition: 0.3s;
      text-align: center;
      text-decoration: none;
      color: #333;
    }

    .apartment-card:hover {
      transform: scale(1.02);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.12);
    }

    .apartment-card h3 {
      margin-bottom: 8px;
      font-size: 18px;
      color: #007bff;
    }

    .apartment-card p {
      font-size: 14px;
    }

    @media (max-width: 768px) {
      .main-content {
        margin-left: 80px;
        padding: 15px;
      }

      .apartment-card h3 {
        font-size: 16px;
      }

      .apartment-card p {
        font-size: 13px;
      }
    }
  </style>
</head>
<body>

<div class="main-content">
  <!-- BUTTONS -->
  <div class="button-container">

    <a href="active_listings.php" class="top-btn btn-green"><i class="fas fa-bullhorn"></i> Active Listings</a>
  </div>

  <h2>My Properties</h2>

  <div class="property-list">
    <?php while ($apartment = mysqli_fetch_assoc($apartments)): ?>
      <?php
        $apt_id = $apartment['id'];
        $available_units_query = mysqli_query($conn, "SELECT COUNT(*) as available_count FROM apartment_units WHERE apartment_id = $apt_id AND unit_status = 'Available'");
        $available_data = mysqli_fetch_assoc($available_units_query);
        $available_count = $available_data['available_count'];
      ?>
      <a href="view_units.php?id=<?= $apt_id ?>" class="apartment-card">
        <h3><?= htmlspecialchars($apartment['name']) ?></h3>
        <p><?= htmlspecialchars($apartment['location']) ?></p>
        <p>Availble Units: <strong><?= $available_count ?></strong></p>
      </a>
    <?php endwhile; ?>
  </div>
</div>

</body>
</html>
