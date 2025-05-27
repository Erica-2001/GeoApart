<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); 
    exit();
}

include 'db_connect.php';

// Fetch user details
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = '$user_id'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// Restrict access to only tenants
if ($user['user_type'] !== 'Tenant') {
    echo "<script>alert('Access Denied! Only tenants can access this page.'); window.location.href='login.php';</script>";
    exit();
}

// Fetch available apartments, ordered by latest first
$apartment_query = "SELECT a.*, u.name AS landlord_name, u.email AS landlord_email 
                    FROM apartments a 
                    JOIN users u ON a.landlord_id = u.id 
                    ORDER BY a.created_at DESC";
$apartments = mysqli_query($conn, $apartment_query);

// Fetch tenant's bills
$bills_query = "SELECT p.*, a.name AS apartment_name 
                FROM payments p 
                JOIN apartments a ON p.apartment_id = a.id 
                WHERE p.receiver_id = '$user_id'";
$bills = mysqli_query($conn, $bills_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GeoApart - Tenant Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        body {
            background: #f4f4f4;
            color: #333;
        }
        .dashboard-container {
            width: 90%;
            max-width: 900px;
            margin: 30px auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .dashboard-container h2 {
            color: #007bff;
            margin-bottom: 10px;
        }
        .menu {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
        }
        .menu a {
            text-decoration: none;
            color: white;
            background: #007bff;
            padding: 12px 20px;
            border-radius: 6px;
            font-size: 16px;
            transition: 0.3s;
        }
        .menu a:hover {
            background: #0056b3;
        }
        .logout-btn {
            margin-top: 20px;
            background: #dc3545;
            padding: 10px 18px;
            border: none;
            border-radius: 6px;
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
        }
        .logout-btn:hover {
            background: #b02a37;
        }
        .section {
            margin-top: 20px;
            text-align: left;
        }
        .section h3 {
            color: #007bff;
            margin-bottom: 10px;
        }
        .apartment-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .apartment-card {
            display: flex;
            align-items: center;
            background: #e0e0e0;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
            transition: 0.3s;
            text-decoration: none;
            color: inherit;
        }
        .apartment-card:hover {
            transform: scale(1.02);
            background: #d4d4d4;
        }
        .apartment-card img {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            object-fit: contain;
            background: white;
            padding: 5px;
            margin-right: 15px;
        }
        .apartment-info h3 {
            font-size: 18px;
            margin-bottom: 5px;
            color: #007bff;
            font-weight: bold;
        }
        .apartment-price {
            font-size: 16px;
            font-weight: bold;
            color: black;
            margin-top: 5px;
        }
        .landlord-info {
            font-size: 12px;
            color: #555;
        }
        .bill-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 10px;
        }
        .bill-card {
            display: flex;
            justify-content: space-between;
            background: #f0f0f0;
            padding: 10px;
            border-radius: 8px;
            transition: 0.3s;
            font-size: 14px;
        }
        .bill-card:hover {
            background: #e0e0e0;
        }
        .bill-status {
            font-weight: bold;
        }
        .status-pending {
            color: #e67e22;
        }
        .status-paid {
            color: #28a745;
        }
        .status-overdue {
            color: #dc3545;
        }
        .pay-btn {
            background: #28a745;
            padding: 5px 10px;
            border-radius: 5px;
            text-decoration: none;
            color: white;
            font-size: 14px;
        }
        .pay-btn:hover {
            background: #218838;
        }
    </style>
</head>
<body>

    <div class="dashboard-container">
        <h2>Welcome, <?php echo htmlspecialchars($user['name']); ?>!</h2>
        <p>Your Email: <?php echo htmlspecialchars($user['email']); ?></p>
        <p>Account Type: <?php echo htmlspecialchars($user['user_type']); ?></p>

        <div class="menu">
            <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
            <a href="messages.php"><i class="fas fa-comments"></i> Messages</a>
            <a href="edit_profile.php" class="edit-profile-btn"><i class="fas fa-edit"></i> Edit Profile</a>
        </div>

        <!-- Available Apartments Section -->
        <div class="section">
            <h3>Available Apartments</h3>
            <div class="apartment-list">
                <?php while ($apartment = mysqli_fetch_assoc($apartments)): ?>
                    <a href="rent.php?id=<?php echo $apartment['id']; ?>" class="apartment-card">
                        <img src="uploads/<?php echo $apartment['image'] ?: 'default_apartment.jpg'; ?>" alt="Apartment">
                        <div class="apartment-info">
                            <h3><?php echo htmlspecialchars($apartment['name']); ?></h3>
                            <p class="apartment-price">₱<?php echo number_format($apartment['price'], 2); ?> / month</p>
                            <p class="landlord-info">Landlord: <?php echo htmlspecialchars($apartment['landlord_name']); ?> (<?php echo htmlspecialchars($apartment['landlord_email']); ?>)</p>
                        </div>
                    </a>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Tenant Bills Section -->
        <div class="section">
            <h3>Your Bills</h3>
            <div class="bill-list">
                <?php while ($bill = mysqli_fetch_assoc($bills)): ?>
                    <div class="bill-card">
                        <span><?php echo htmlspecialchars($bill['apartment_name']); ?> - ₱<?php echo number_format($bill['total_amount'], 2); ?></span>
                        <span class="bill-status <?= 'status-' . strtolower($bill['payment_status']); ?>">
                            <?= htmlspecialchars($bill['payment_status']); ?>
                        </span>
                        <a href="pay_bill.php?bill_id=<?= $bill['id']; ?>" class="pay-btn">Pay Now</a>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

    </div>

</body>
</html>
