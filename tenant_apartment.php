<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle delete rental request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_rental']) && isset($_POST['rental_id']) && isset($_POST['unit_id'])) {
    $rental_id = intval($_POST['rental_id']);
    $unit_id = intval($_POST['unit_id']);

    $delete_stmt = $conn->prepare("DELETE FROM tenant_rentals WHERE id = ? AND tenant_id = ?");
    $delete_stmt->bind_param("ii", $rental_id, $user_id);
    $delete_stmt->execute();

    $update_unit = $conn->prepare("UPDATE apartment_units SET unit_status = 'Available' WHERE id = ?");
    $update_unit->bind_param("i", $unit_id);
    $update_unit->execute();

    echo "<script>alert('Rental request deleted.'); window.location.href='tenant_dashboard.php';</script>";
    exit();
}

// Get latest tenant_rental
$rental_query = "
    SELECT tr.*, a.name AS apartment_name, a.location, a.apartment_type, a.features AS apartment_features,
           a.image AS apartment_image, u.unit_number, u.unit_price, u.unit_features, u.unit_status,
           l.name AS landlord_name, l.email AS landlord_email, l.mobile AS landlord_mobile
    FROM tenant_rentals tr
    JOIN apartments a ON tr.apartment_id = a.id
    JOIN apartment_units u ON tr.unit_id = u.id
    JOIN users l ON tr.landlord_id = l.id
    WHERE tr.tenant_id = ? AND tr.status = 'Active'
    ORDER BY tr.created_at DESC LIMIT 1
";
$stmt = $conn->prepare($rental_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$rental_result = $stmt->get_result();
$rental = $rental_result->fetch_assoc();

if (!$rental) {
    echo "<script>alert('You have no active rental yet.'); window.location.href='tenant_dashboard.php';</script>";
    exit();
}

// Get latest payment for that unit
$payment_query = "
    SELECT * FROM payments 
    WHERE sender_id = ? AND unit_id = ? 
    ORDER BY payment_date DESC LIMIT 1
";
$stmt = $conn->prepare($payment_query);
$stmt->bind_param("ii", $user_id, $rental['unit_id']);
$stmt->execute();
$payment_result = $stmt->get_result();
$payment = $payment_result->fetch_assoc();

// Fetch unit images
$image_stmt = $conn->prepare("SELECT image_path FROM unit_images WHERE unit_id = ?");
$image_stmt->bind_param("i", $rental['unit_id']);
$image_stmt->execute();
$image_result = $image_stmt->get_result();
$unit_images = [];
while ($img = $image_result->fetch_assoc()) {
    $unit_images[] = $img['image_path'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Rented Unit</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
   <style>
  body {
    font-family: 'Poppins', sans-serif;
    background: #f4f4f4;
    padding: 20px;
  }

  .container {
    max-width: 900px;
    margin: auto;
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
  }

  h2 {
    text-align: center;
    color: #007bff;
    margin-bottom: 20px;
    font-size: 24px;
  }

  .section {
    margin-top: 25px;
  }

  .section h3 {
    color: #007bff;
    margin-bottom: 12px;
    font-size: 20px;
  }

  .info-group {
    background: #f9f9f9;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 15px;
  }

  .info-group p {
    margin: 6px 0;
    font-size: 15px;
  }

  .highlight {
    font-weight: bold;
  }

  .unit-images {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin-top: 10px;
    justify-content: flex-start;
  }

  .unit-images img {
    width: 100%;
    max-width: 170px;
    height: auto;
    border-radius: 8px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
  }

  .delete-btn {
    background: #dc3545;
    color: white;
    border: none;
    padding: 10px 16px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    transition: background 0.3s;
  }

  .delete-btn:hover {
    background: #c82333;
  }

  /* ✅ Responsive Adjustments */
  @media (max-width: 768px) {
    body {
      padding: 15px;
    }

    .container {
      padding: 15px;
    }

    h2 {
      font-size: 20px;
    }

    .section h3 {
      font-size: 18px;
    }

    .info-group p {
      font-size: 14px;
    }

    .unit-images {
      justify-content: center;
    }

    .unit-images img {
      max-width: 100%;
    }

    .delete-btn {
      width: 100%;
      padding: 12px;
      font-size: 15px;
    }
  }
</style>

</head>
<body>
<div class="back">
        <a href="tenant_dashboard.php"><i class="fa-solid fa-arrow-left"></i></a>
    </div>
	<br>
    <h2>🏡 My Apartment & Unit Details</h2>

    <div class="section">
        <h3>Apartment Information</h3>
        <div class="info-group">
            <p><span class="highlight">Name:</span> <?= htmlspecialchars($rental['apartment_name']) ?></p>
            <p><span class="highlight">Location:</span> <?= htmlspecialchars($rental['location']) ?></p>
            <p><span class="highlight">Type:</span> <?= htmlspecialchars($rental['apartment_type']) ?></p>
            <p><span class="highlight">Features:</span><br> <?= nl2br(htmlspecialchars($rental['apartment_features'])) ?></p>
        </div>
    </div>

    <div class="section">
        <h3>Unit Details</h3>
        <div class="info-group">
            <p><span class="highlight">Unit Number:</span> <?= htmlspecialchars($rental['unit_number']) ?></p>
            <p><span class="highlight">Monthly Rent:</span> ₱<?= number_format($rental['unit_price'], 2) ?></p>
            <p><span class="highlight">Status:</span> <?= $rental['unit_status'] === 'Occupied' ? '✅ Approved' : ($rental['unit_status'] === 'Pending' ? '🕐 Pending Approval' : '❌ Rejected') ?></p>
            <p><span class="highlight">Features:</span><br> <?= nl2br(htmlspecialchars($rental['unit_features'])) ?></p>

            <?php if ($rental['unit_status'] === 'Available'): ?>
                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this request?');">
                    <input type="hidden" name="delete_rental" value="1">
                    <input type="hidden" name="rental_id" value="<?= $rental['id'] ?>">
                    <input type="hidden" name="unit_id" value="<?= $rental['unit_id'] ?>">
                    <button class="delete-btn"><i class="fas fa-trash-alt"></i> Delete Request</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="section">
        <h3>📸 Unit Images</h3>
        <div class="unit-images">
            <?php if (!empty($unit_images)): ?>
                <?php foreach ($unit_images as $img): ?>
                    <img src="<?= htmlspecialchars($img) ?>" alt="Unit Image">
                <?php endforeach; ?>
            <?php else: ?>
                <p>No images uploaded.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="section">
        <h3>Landlord Info</h3>
        <div class="info-group">
            <p><i class="fas fa-user"></i> <?= htmlspecialchars($rental['landlord_name']) ?></p>
            <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($rental['landlord_email']) ?></p>
            <p><i class="fas fa-phone"></i> <?= htmlspecialchars($rental['landlord_mobile']) ?></p>
        </div>
    </div>

    <div class="section">
        <h3>Latest Billing</h3>
        <div class="info-group">
            <?php if ($payment): ?>
                <p><span class="highlight">Amount:</span> ₱<?= number_format($payment['total_amount'], 2) ?></p>
                <p><span class="highlight">Status:</span> <?= $payment['payment_status'] ?></p>
                <p><span class="highlight">Date:</span> <?= date('M d, Y', strtotime($payment['payment_date'])) ?></p>
            <?php else: ?>
                <p>No billing found yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
