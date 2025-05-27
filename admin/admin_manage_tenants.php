<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$query = "
    SELECT tr.*, 
           t.name AS tenant_name, 
           l.name AS landlord_name,
           a.name AS apartment_name, 
           u.unit_number,
           p.total_amount,
           p.payment_status,
           p.payment_date
    FROM tenant_rentals tr
    JOIN users t ON tr.tenant_id = t.id
    JOIN users l ON tr.landlord_id = l.id
    JOIN apartments a ON tr.apartment_id = a.id
    JOIN apartment_units u ON tr.unit_id = u.id
    LEFT JOIN payments p ON tr.unit_id = p.unit_id AND tr.tenant_id = p.receiver_id
    WHERE u.unit_status = 'Occupied'
    ORDER BY tr.created_at DESC
";

$result = $conn->query($query);

$rentals = [];
while ($row = $result->fetch_assoc()) {
    $rentals[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Tenants & Payments</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/your-kit-id.js" crossorigin="anonymous"></script> <!-- replace your-kit-id -->

    <style>
        body {
            background: #f8f9fa;
            padding: 20px;
            font-family: 'Poppins', sans-serif;
        }
        .container-manage {
            margin: auto;
            max-width: 900px;
        }
        .header-title {
            font-weight: 700;
            color: #007bff;
            text-align: center;
            margin-bottom: 25px;
        }
        .back-btn {
            margin-bottom: 20px;
        }
        .rental-card {
            background: #ffffff;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        .rental-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.15);
        }
        .badge-status {
            padding: 6px 12px;
            border-radius: 30px;
            font-size: 14px;
        }
        .badge-paid { background: #28a745; color: white; }
        .badge-pending { background: #ffc107; color: black; }
        .badge-overdue { background: #dc3545; color: white; }
        .search-bar {
            margin-bottom: 20px;
            border-radius: 30px;
            padding: 10px 20px;
            border: 1px solid #ced4da;
        }
        .info-line {
            margin-bottom: 8px;
        }
        .info-label {
            font-weight: 600;
        }
        .info-value {
            margin-left: 5px;
        }
        .no-result {
            text-align: center;
            color: #999;
            margin-top: 30px;
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

    <h2 class="header-title">🧾 Manage Tenants & Payments</h2>

    <!-- Search Bar -->
    <input type="text" id="searchInput" onkeyup="searchRentals()" class="form-control search-bar" placeholder="🔎 Search tenants, apartments, landlords...">

    <?php if (empty($rentals)): ?>
        <p class="no-result">No approved tenants yet.</p>
    <?php else: ?>
        <div id="rentalContainer">
            <?php foreach ($rentals as $rental): ?>
                <div class="rental-card" 
                     data-tenant="<?= strtolower($rental['tenant_name']) ?>" 
                     data-landlord="<?= strtolower($rental['landlord_name']) ?>"
                     data-apartment="<?= strtolower($rental['apartment_name']) ?>">
                    <h5 class="mb-3">
                        <i class="fas fa-user"></i> 
                        <?= htmlspecialchars($rental['tenant_name']) ?>
                    </h5>

                    <div class="info-line">
                        <span class="info-label"><i class="fas fa-user-tie"></i> Landlord:</span>
                        <span class="info-value"><?= htmlspecialchars($rental['landlord_name']) ?></span>
                    </div>

                    <div class="info-line">
                        <span class="info-label"><i class="fas fa-building"></i> Apartment:</span>
                        <span class="info-value"><?= htmlspecialchars($rental['apartment_name']) ?></span>
                    </div>

                    <div class="info-line">
                        <span class="info-label"><i class="fas fa-door-open"></i> Unit:</span>
                        <span class="info-value"><?= htmlspecialchars($rental['unit_number']) ?></span>
                    </div>

                    <div class="info-line">
                        <span class="info-label"><i class="fas fa-coins"></i> Total Amount:</span>
                        <span class="info-value"><?= $rental['total_amount'] ? '₱' . number_format($rental['total_amount'], 2) : 'N/A' ?></span>
                    </div>

                    <div class="info-line">
                        <span class="info-label"><i class="fas fa-wallet"></i> Payment Status:</span>
                        <?php if ($rental['payment_status'] == 'Paid'): ?>
                            <span class="badge-status badge-paid">Paid</span>
                        <?php elseif ($rental['payment_status'] == 'Pending'): ?>
                            <span class="badge-status badge-pending">Pending</span>
                        <?php elseif ($rental['payment_status'] == 'Overdue'): ?>
                            <span class="badge-status badge-overdue">Overdue</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">N/A</span>
                        <?php endif; ?>
                    </div>

                    <div class="info-line">
                        <span class="info-label"><i class="fas fa-calendar-check"></i> Payment Date:</span>
                        <span class="info-value"><?= $rental['payment_date'] ? date('M d, Y', strtotime($rental['payment_date'])) : 'N/A' ?></span>
                    </div>

                    <div class="info-line">
                        <span class="info-label"><i class="fas fa-calendar-alt"></i> Rental Date:</span>
                        <span class="info-value"><?= date('M d, Y', strtotime($rental['created_at'])) ?></span>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
// Live Search Function
function searchRentals() {
    const input = document.getElementById("searchInput").value.toLowerCase();
    const cards = document.querySelectorAll("#rentalContainer .rental-card");

    let found = false;
    cards.forEach(card => {
        const tenant = card.getAttribute("data-tenant");
        const landlord = card.getAttribute("data-landlord");
        const apartment = card.getAttribute("data-apartment");

        if (tenant.includes(input) || landlord.includes(input) || apartment.includes(input)) {
            card.style.display = "block";
            found = true;
        } else {
            card.style.display = "none";
        }
    });

    if (!found) {
        document.getElementById("rentalContainer").innerHTML = '<p class="no-result">No matching tenants found.</p>';
    }
}
</script>

</body>
</html>
