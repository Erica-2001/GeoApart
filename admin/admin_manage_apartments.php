<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

include '../db_connect.php';

// Handle delete request
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);

    // Delete the apartment
    $delete_stmt = $conn->prepare("DELETE FROM apartments WHERE id = ?");
    $delete_stmt->bind_param("i", $delete_id);

    if ($delete_stmt->execute()) {
        echo "<script>alert('Apartment deleted successfully!'); window.location.href='admin_manage_apartments.php';</script>";
        exit();
    } else {
        echo "<script>alert('Failed to delete apartment.');</script>";
    }

    $delete_stmt->close();
}


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
                       
                        <button type="button" class="btn btn-delete btn-action" onclick="confirmDelete(<?= $apartment['id']; ?>)">Delete</button>

                     
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


<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Confirm Deletion</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Are you sure you want to delete this apartment?
      </div>
      <div class="modal-footer">
        <a id="confirmDeleteBtn" class="btn btn-danger">Delete</a>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </div>
  </div>
</div>

<script>
function confirmDelete(id) {
  document.getElementById('confirmDeleteBtn').href = 'admin_manage_apartments.php?delete=' + encodeURIComponent(id);
  const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
  modal.show();
}
</script>

