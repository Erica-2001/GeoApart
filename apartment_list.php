<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); 
    exit();
}

include 'db_connect.php';


$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = '$user_id'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

if ($user['user_type'] !== 'Tenant') {
    echo "<script>alert('Access Denied! Only tenants can access this page.'); window.location.href='login.php';</script>";
    exit();
}

$apartment_query = "SELECT 
    a.id, a.name, a.location, a.price, a.features, a.image, a.apartment_type,
    u.name AS landlord_name, u.email AS landlord_email, u.mobile AS landlord_mobile,
    (
        SELECT COUNT(*) 
        FROM apartment_units 
        WHERE apartment_id = a.id AND unit_status = 'Available'
    ) AS available_unit_count
FROM apartments a
JOIN users u ON a.landlord_id = u.id
ORDER BY a.created_at DESC";

$apartments = mysqli_query($conn, $apartment_query);

$notifications_query = "SELECT id, message, created_at FROM notifications WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 5";
$notifications = mysqli_query($conn, $notifications_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GeoApart - Tenant Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: #f9f9f9; color: #333; padding: 20px; }

        a { text-decoration: none; color: inherit; }

        h2, h3 { color: #007bff; }

        

        .notif-icon {
            position: fixed; top: 20px; right: 30px; font-size: 24px;
            color: #007bff; cursor: pointer;
            z-index: 999;
        }

        .notif-dropdown {
            display: none;
            position: absolute;
            top: 50px;
            left: 80px;
            width: 300px;
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
            position: relative;
        }
        .notif-item:last-child { border-bottom: none; }
        .notif-item time {
            display: block;
            font-size: 12px;
            color: #666;
        }
        .notif-item .delete-btn {
            position: absolute; top: 10px; right: 10px;
            background: transparent; border: none;
            color: #dc3545; font-size: 14px; cursor: pointer;
        }

        .dashboard-header {
            text-align: center;
            margin-top: 80px;
            margin-bottom: 30px;
        }

        .menu {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
            margin: 0 auto 40px;
            max-width: 1000px;
        }
        .menu a {
            background: #007bff;
            color: white;
            padding: 18px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0px 3px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        .menu a:hover { background: #0056b3; transform: scale(1.03); }

        .section {
            max-width: 1200px;
            margin: 0 auto;
        }

        .apartment-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .apartment-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 3px 8px rgba(0,0,0,0.1);
            padding: 15px;
            display: flex;
            gap: 15px;
            transition: 0.3s;
        }
        .apartment-card:hover {
            transform: scale(1.02);
            background: #f1f1f1;
        }
        .apartment-card img {
            width: 90px;
            height: 90px;
            object-fit: contain;
            border-radius: 10px;
            background: #fff;
        }
        .apartment-info {
            flex: 1;
            font-size: 14px;
        }
        .apartment-info h3 {
            font-size: 18px;
            color: #007bff;
            margin-bottom: 5px;
        }
        .apartment-price {
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }
        .landlord-info {
            font-size: 12px;
            color: #555;
        }
    </style>
</head>
<body>




<div class="back">
        <a href="tenant_dashboard.php"><i class="fa-solid fa-arrow-left"></i></a>
    </div>
    <br>

<!-- Available Apartments Section -->
<div class="section">
    <h3>Available Apartments</h3>
    <div class="apartment-list">
        <?php
while ($apartment = mysqli_fetch_assoc($apartments)):
    // Fetch first unit image if available
    $apartment_id = $apartment['id'];
    $img_query = mysqli_query($conn, "SELECT image_path FROM apartment_images WHERE apartment_id = $apartment_id ORDER BY uploaded_at ASC LIMIT 1");
    $img_row = mysqli_fetch_assoc($img_query);
    
    // Count apartment units
                $unit_count_query = mysqli_query($conn, "SELECT COUNT(*) AS total FROM apartment_units WHERE apartment_id = $apartment_id");
                $unit_count = mysqli_fetch_assoc($unit_count_query);

    // Fallback image
    $displayImage = $img_row ? $img_row['image_path'] : (
        !empty($apartment['image']) && file_exists("uploads/" . $apartment['image'])
            ? "uploads/" . $apartment['image']
            : "uploads/default_apartment.jpg"
    );
?>
    <a href="apartment_details.php?id=<?= $apartment['id'] ?>" class="apartment-card">
        <img src="<?= $displayImage ?>" alt="Apartment">
        <div class="apartment-info">
            <h3><?= htmlspecialchars($apartment['name']) ?></h3>
           <p class="landlord-info"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($apartment['location']); ?></p>
                        <p class="apartment-price">₱ <?= number_format($apartment['price'], 2); ?> / month</p>
                        <p class="landlord-info"><i class="fas fa-home"></i> Type: <?= htmlspecialchars($apartment['apartment_type']); ?></p>
                        <p class="landlord-info"><i class="fas fa-door-open"></i> Total Units: <?= $unit_count['total']; ?></p>
                        <br>
                        <p class="landlord-info"><i class="fas fa-door-open"></i> Available Units: <?= $apartment['available_unit_count']; ?></p>
        </div>
    </a>
<?php endwhile; ?>
    </div>
</div>

<script>
    function toggleDropdown() {
        const dropdown = document.getElementById('notifDropdown');
        dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
    }
</script>

</body>
</html>
