<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

include '../db_connect.php';

// Fetch Apartments
$result = mysqli_query($conn, "SELECT apartments.*, users.name AS landlord_name, users.email AS landlord_email, users.mobile AS landlord_mobile 
                               FROM apartments 
                               JOIN users ON apartments.landlord_id = users.id 
                               ORDER BY apartments.created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Apartments</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/your-kit-id.js" crossorigin="anonymous"></script> <!-- Replace your-kit-id -->

    <style>
        body {
            background: #f8f9fa;
            font-family: 'Poppins', sans-serif;
            padding: 20px;
        }
        .container-manage {
            max-width: 1100px;
            margin: auto;
        }
        .header-title {
            text-align: center;
            font-weight: 700;
            color: #007bff;
            margin-bottom: 20px;
        }
        .back-btn {
            margin-bottom: 20px;
        }
        .apartment-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .apartment-card {
            background: #ffffff;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0px 4px 12px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }
        .apartment-card:hover {
            transform: translateY(-5px);
            box-shadow: 0px 6px 15px rgba(0,0,0,0.15);
        }
        .apartment-info {
            flex: 1 1 60%;
        }
        .apartment-info h4 {
            color: #007bff;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .apartment-info p {
            margin-bottom: 5px;
            font-size: 15px;
            color: #555;
        }
        .apartment-actions {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-end;
            gap: 10px;
            flex: 1 1 30%;
        }
        .btn-action {
            padding: 8px 15px;
            border-radius: 30px;
            font-size: 14px;
            width: 120px;
            text-align: center;
        }
        .btn-edit {
            background: #ffc107;
            color: black;
            border: none;
        }
        .btn-edit:hover {
            background: #e0a800;
        }
        .btn-delete {
            background: #dc3545;
            color: white;
            border: none;
        }
        .btn-delete:hover {
            background: #bd2130;
        }
        .btn-add-unit {
            background: #28a745;
            color: white;
            border: none;
        }
        .btn-add-unit:hover {
            background: #218838;
        }
    </style>
</head>
<body>

<div class="container-manage">

    <!-- Back Button -->
    <div class="back-btn">
        <a href="admin_dashboard.php" class="btn btn-outline-primary rounded-pill">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <h2 class="header-title">🏢 Manage Apartments</h2>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="apartment-list">
            <?php while ($apartment = mysqli_fetch_assoc($result)): ?>
                <div class="apartment-card">
                    <div class="apartment-info">
                        <h4><i class="fas fa-building"></i> <?= htmlspecialchars($apartment['name']); ?></h4>
                        <p><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($apartment['location']); ?></p>
                        <p><i class="fas fa-door-open"></i> Type: <?= htmlspecialchars($apartment['apartment_type']); ?></p>
                        <p><i class="fas fa-money-bill-wave"></i> Price Range: ₱<?= htmlspecialchars(number_format($apartment['price'], 2)); ?></p>
                        <p><i class="fas fa-user"></i> Landlord: <?= htmlspecialchars($apartment['landlord_name']); ?></p>
                        <p><i class="fas fa-envelope"></i> Email: <?= htmlspecialchars($apartment['landlord_email']); ?></p>
                        <p><i class="fas fa-phone"></i> Contact: <?= htmlspecialchars($apartment['landlord_mobile']); ?></p>
                    </div>
                    <div class="apartment-actions">
                        <a href="admin_edit_apartment.php?id=<?= urlencode($apartment['id']); ?>" class="btn btn-edit btn-action">Edit</a>
                        <a href="admin_manage_apartments.php?delete=<?= urlencode($apartment['id']); ?>" onclick="return confirm('Are you sure you want to delete this apartment?');" class="btn btn-delete btn-action">Delete</a>
                        <a href="add_units.php?id=<?= urlencode($apartment['id']); ?>" class="btn btn-add-unit btn-action">Add Unit</a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p class="text-center text-muted">No apartments found.</p>
    <?php endif; ?>
</div>

</body>
</html>
